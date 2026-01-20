<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Models\OAuthService;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaOAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $request->validate([
            'channel' => 'nullable|in:facebook,instagram,threads',
        ]);

        $channel = $request->get('channel', 'facebook');
        $config = $this->getConfig();

        if (!$config['client_id'] || !$config['redirect_uri']) {
            return redirect()->back()->with('error', 'Meta OAuth not configured.');
        }

        $state = base64_encode(json_encode([
            'channel' => $channel,
            'uid' => $request->user()->id,
            'csrf' => Str::random(16),
        ]));

        // Build auth URL with response_type to force fresh page selector
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => implode(',', $config['scopes']),
            'state' => $state,
            'response_type' => 'code',
            'auth_type' => 'rerequest',
        ];

        $authUrl = sprintf(
            'https://www.facebook.com/%s/dialog/oauth?%s',
            config('services.meta.graph_version', 'v19.0'),
            http_build_query($params)
        );

        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        Log::info('meta_oauth_callback_started', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
            'user_id' => $request->user()?->id,
        ]);

        $code = $request->get('code');
        $state = $this->decodeState($request->get('state'));

        if (!$code || !$state || ($request->user()?->id !== ($state['uid'] ?? null))) {
            Log::warning('meta_oauth_callback_validation_failed', [
                'has_code' => !empty($code),
                'has_state' => !empty($state),
                'user_id' => $request->user()?->id,
                'state_uid' => $state['uid'] ?? null,
            ]);
            return redirect()->route('social-accounts.index')->with('error', 'Invalid Meta OAuth response.');
        }

        $config = $this->getConfig();
        if (!$config['client_id'] || !$config['client_secret'] || !$config['redirect_uri']) {
            return redirect()->route('dashboard')->with('error', 'Meta OAuth not configured.');
        }

        $graphBase = rtrim(config('services.meta.graph_base', 'https://graph.facebook.com'), '/') . '/' . config('services.meta.graph_version', 'v19.0');

        $tokenResp = Http::get("{$graphBase}/oauth/access_token", [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'code' => $code,
        ]);

        if (!$tokenResp->successful()) {
            return redirect()->route('dashboard')->with('error', 'Meta OAuth token exchange failed.');
        }

        $accessToken = $tokenResp->json()['access_token'] ?? null;
        $expiresIn = $tokenResp->json()['expires_in'] ?? null;

        // Exchange for long-lived token
        $longLivedResp = Http::get("{$graphBase}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'fb_exchange_token' => $accessToken,
        ]);

        if ($longLivedResp->successful() && ($longLivedResp->json()['access_token'] ?? null)) {
            $accessToken = $longLivedResp->json()['access_token'];
            $expiresIn = $longLivedResp->json()['expires_in'] ?? $expiresIn;
        }

        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'Meta OAuth token missing.');
        }

        $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;

        // Check granted permissions
        $permResp = Http::withToken($accessToken)->get("{$graphBase}/me/permissions");
        $grantedPerms = collect($permResp->json()['data'] ?? [])
            ->filter(fn($p) => $p['status'] === 'granted')
            ->pluck('permission')
            ->toArray();

        Log::info('meta_oauth_granted_permissions', [
            'permissions' => $grantedPerms,
            'has_instagram_basic' => in_array('instagram_basic', $grantedPerms),
            'has_instagram_content_publish' => in_array('instagram_content_publish', $grantedPerms),
        ]);

        // Fetch pages (includes IG business account info)
        $pagesResp = Http::withToken($accessToken)->get("{$graphBase}/me/accounts", [
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);

        Log::info('meta_oauth_fetch_pages_response', [
            'successful' => $pagesResp->successful(),
            'status' => $pagesResp->status(),
            'body' => $pagesResp->json(),
        ]);

        if (!$pagesResp->successful()) {
            Log::error('meta_oauth_fetch_pages_failed', [
                'status' => $pagesResp->status(),
                'error' => $pagesResp->json(),
            ]);
            return redirect()->route('social-accounts.index')->with('error', 'Meta OAuth: cannot fetch pages. Make sure you granted all permissions.');
        }

        $pages = $pagesResp->json()['data'] ?? [];
        $userId = $request->user()->id;

        Log::info('meta_oauth_pages_fetched', [
            'user_id' => $userId,
            'page_count' => count($pages),
            'pages' => array_map(fn($p) => ['id' => $p['id'] ?? null, 'name' => $p['name'] ?? null], $pages),
        ]);

        foreach ($pages as $page) {
            $pageId = $page['id'] ?? null;
            $pageName = $page['name'] ?? null;
            $pageToken = $page['access_token'] ?? null;
            $igBiz = $page['instagram_business_account']['id'] ?? null;

            if (!$pageId || !$pageToken) {
                continue;
            }

            // Facebook Page account entry
            $account = SocialAccount::updateOrCreate(
                [
                    'user_id' => $userId,
                    'provider' => 'meta_facebook',
                    'account_id' => $pageId,
                ],
                [
                    'page_id' => $pageId,
                    'page_name' => $pageName,
                    'username' => $pageName,
                    'access_token_enc' => Crypt::encryptString($pageToken),
                    'refresh_token_enc' => Crypt::encryptString($accessToken),
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'metadata' => [
                        'channel' => 'facebook',
                        'source' => 'oauth_callback',
                    ],
                ]
            );

            Log::info('meta_oauth_account_saved', [
                'account_id' => $account->id,
                'page_id' => $pageId,
                'page_name' => $pageName,
                'user_id' => $userId,
            ]);

            // Instagram Business account entry if available
            if ($igBiz) {
                // Fetch Instagram username
                $igResp = Http::withToken($pageToken)->get("{$graphBase}/{$igBiz}", [
                    'fields' => 'username',
                ]);

                $igUsername = $igResp->successful()
                    ? ($igResp->json()['username'] ?? $pageName)
                    : $pageName;

                SocialAccount::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'provider' => 'meta_instagram',
                        'account_id' => $igBiz,
                    ],
                    [
                        'page_id' => $pageId,
                        'page_name' => $pageName,
                        'ig_business_account_id' => $igBiz,
                        'username' => $igUsername,
                        'access_token_enc' => Crypt::encryptString($pageToken),
                        'refresh_token_enc' => Crypt::encryptString($accessToken),
                        'expires_at' => $expiresAt,
                        'status' => 'active',
                        'metadata' => [
                            'channel' => 'instagram',
                            'page_id' => $pageId,
                            'source' => 'oauth_callback',
                        ],
                    ]
                );
            }
        }

        if (empty($pages)) {
            return redirect()->route('social-accounts.index')->with('warning', 'Connected, but no pages found for this account.');
        }

        $pageCount = count($pages);
        $message = $pageCount === 1
            ? 'Successfully connected 1 Facebook Page!'
            : "Successfully connected {$pageCount} Facebook Pages!";

        return redirect()->route('social-accounts.index')->with('success', $message);
    }

    private function decodeState(?string $state): ?array
    {
        if (!$state) {
            return null;
        }

        try {
            return json_decode(base64_decode($state, true), true) ?: null;
        } catch (\Throwable $e) {
            Log::warning('meta_state_decode_failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getConfig(): array
    {
        $config = OAuthService::getConfig('meta');

        return [
            'client_id' => $config['client_id'] ?? config('services.meta.app_id'),
            'client_secret' => $config['client_secret'] ?? config('services.meta.app_secret'),
            'redirect_uri' => $config['redirect_uri'] ?? config('services.meta.redirect_uri'),
            'scopes' => config('services.meta.scopes', []),
        ];
    }
}
