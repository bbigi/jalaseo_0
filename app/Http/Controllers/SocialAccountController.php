<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialAccountController extends Controller
{
    /**
     * Display all connected social accounts for authenticated user
     */
    public function index()
    {
        $accounts = SocialAccount::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by provider for easier display
        $facebook = $accounts->where('provider', 'meta_facebook');
        $instagram = $accounts->where('provider', 'meta_instagram');
        $threads = $accounts->where('provider', 'meta_threads');

        return view('social-accounts.index', compact('facebook', 'instagram', 'threads'));
    }

    /**
     * Get account data for API (used in asset publish modal)
     */
    public function getAccounts(Request $request)
    {
        $provider = $request->input('provider'); // meta_facebook, meta_instagram, meta_threads

        $query = SocialAccount::where('user_id', auth()->id())
            ->where('status', 'active');

        if ($provider) {
            $query->where('provider', $provider);
        }

        $accounts = $query->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'provider' => $account->provider,
                'username' => $account->username,
                'page_name' => $account->page_name ?? $account->username,
                'page_id' => $account->page_id,
                'account_id' => $account->account_id,
                'ig_business_account_id' => $account->ig_business_account_id,
                'created_at' => $account->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Remove/disconnect a social account
     */
    public function disconnect(SocialAccount $account)
    {
        // Authorization: only owner can disconnect
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        Log::info('social_account_disconnected', [
            'user_id' => auth()->id(),
            'account_id' => $account->id,
            'provider' => $account->provider,
            'page_id' => $account->page_id,
        ]);

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account disconnected successfully',
        ]);
    }

    /**
     * Set account as primary (for default selection)
     */
    public function setPrimary(SocialAccount $account)
    {
        // Authorization
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Update metadata to mark as primary
        $provider = $account->provider;

        // Remove primary flag from all accounts of same provider
        SocialAccount::where('user_id', auth()->id())
            ->where('provider', $provider)
            ->each(function ($acc) {
                $metadata = $acc->metadata ?? [];
                unset($metadata['is_primary']);
                $acc->update(['metadata' => $metadata]);
            });

        // Set this account as primary
        $metadata = $account->metadata ?? [];
        $metadata['is_primary'] = true;
        $account->update(['metadata' => $metadata]);

        return response()->json([
            'success' => true,
            'message' => 'Primary account updated',
        ]);
    }

    /**
     * Refresh account details (fetch latest page name, IG account, etc)
     */
    public function refresh(SocialAccount $account)
    {
        // Authorization
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $token = $account->decryptedToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid access token',
                ], 400);
            }

            // Fetch page details from Meta Graph API
            $pageId = $account->page_id;
            $url = "https://graph.facebook.com/v19.0/{$pageId}?fields=name,instagram_business_account&access_token={$token}";

            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to refresh: ' . $data['error']['message'],
                ], 400);
            }

            // Update account details
            $account->update([
                'page_name' => $data['name'] ?? null,
                'ig_business_account_id' => $data['instagram_business_account']['id'] ?? null,
            ]);

            // If IG account found, auto-create Instagram account record
            if (!empty($data['instagram_business_account']['id'])) {
                $igId = $data['instagram_business_account']['id'];

                // Check if IG account already exists
                $existingIg = SocialAccount::where('user_id', auth()->id())
                    ->where('provider', 'meta_instagram')
                    ->where('account_id', $igId)
                    ->first();

                if (!$existingIg) {
                    // Fetch IG username
                    $igUrl = "https://graph.facebook.com/v19.0/{$igId}?fields=username&access_token={$token}";
                    $igResponse = file_get_contents($igUrl);
                    $igData = json_decode($igResponse, true);

                    SocialAccount::create([
                        'user_id' => auth()->id(),
                        'provider' => 'meta_instagram',
                        'account_id' => $igId,
                        'page_id' => $account->page_id, // Link to parent Facebook page
                        'ig_business_account_id' => $igId,
                        'username' => $igData['username'] ?? 'Instagram',
                        'access_token_enc' => $account->access_token_enc, // Reuse Facebook token
                        'refresh_token_enc' => $account->refresh_token_enc,
                        'status' => 'active',
                        'metadata' => [
                            'source' => 'auto_sync',
                            'parent_facebook_account_id' => $account->id,
                        ],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Account refreshed successfully',
                'account' => [
                    'page_name' => $account->page_name,
                    'ig_business_account_id' => $account->ig_business_account_id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('social_account_refresh_failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refresh failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
