<?php

namespace App\Services\SocialPublishers;

use App\Models\DistributionSchedule;
use App\Models\SocialAccount;
use App\Models\SocialAsset;
use App\Models\OAuthService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MetaPublisher implements SocialPublisherInterface
{
    private string $graphBase;

    public function __construct()
    {
        $graphVersion = config('services.meta.graph_version', 'v19.0');
        $this->graphBase = rtrim(config('services.meta.graph_base', 'https://graph.facebook.com'), '/') . '/' . $graphVersion;
    }

    public function supports(string $provider): bool
    {
        return in_array($provider, ['meta_facebook', 'meta_instagram', 'meta_threads'], true);
    }

    public function publish(SocialAsset $asset, DistributionSchedule $schedule, SocialAccount $account): PublishResult
    {
        $token = $this->decryptToken($account->access_token_enc);

        if (!$token) {
            return PublishResult::failure('token_missing');
        }

        return match ($account->provider) {
            'meta_facebook' => $this->publishFacebook($asset, $token, $account),
            'meta_instagram' => $this->publishInstagram($asset, $token, $account),
            'meta_threads' => PublishResult::failure('threads_not_enabled'),
            default => PublishResult::failure('unsupported_provider'),
        };
    }

    private function publishFacebook(SocialAsset $asset, string $token, SocialAccount $account): PublishResult
    {
        $pageId = $account->page_id ?? $account->account_id;

        if (!$pageId) {
            return PublishResult::failure('page_missing', ['account_id' => $account->id]);
        }

        $message = $asset->copy_text;
        $visualPath = $this->resolveVisualPath($asset);

        if ($visualPath) {
            $response = Http::asMultipart()
                ->withToken($token)
                ->attach('source', fopen($visualPath, 'r'), basename($visualPath))
                ->post("{$this->graphBase}/{$pageId}/photos", [
                    'caption' => $message,
                ]);
        } else {
            $response = Http::withToken($token)->post("{$this->graphBase}/{$pageId}/feed", [
                'message' => $message,
            ]);
        }

        if (!$response->successful()) {
            $this->logError('meta_facebook', $response->json(), $account, $asset);
            return PublishResult::failure('meta_facebook_error', $response->json() ?? []);
        }

        $body = $response->json();
        $platformId = $body['post_id'] ?? $body['id'] ?? null;

        return PublishResult::success($platformId, $body);
    }

    private function publishInstagram(SocialAsset $asset, string $token, SocialAccount $account): PublishResult
    {
        $igBusinessId = $account->ig_business_account_id;
        $visualUrl = $this->resolveVisualUrl($asset);
        $caption = $asset->copy_text;

        if (!$igBusinessId || !$visualUrl) {
            return PublishResult::failure('instagram_media_required', [
                'ig_business_account_id' => $igBusinessId,
                'visual_url' => $visualUrl,
            ]);
        }

        $container = Http::withToken($token)->post("{$this->graphBase}/{$igBusinessId}/media", [
            'image_url' => $visualUrl,
            'caption' => $caption,
        ]);

        if (!$container->successful()) {
            $this->logError('meta_instagram_container', $container->json(), $account, $asset);
            return PublishResult::failure('instagram_container_error', $container->json() ?? []);
        }

        $containerId = $container->json()['id'] ?? null;

        if (!$containerId) {
            return PublishResult::failure('instagram_container_missing_id', $container->json() ?? []);
        }

        $publish = Http::withToken($token)->post("{$this->graphBase}/{$igBusinessId}/media_publish", [
            'creation_id' => $containerId,
        ]);

        if (!$publish->successful()) {
            $this->logError('meta_instagram_publish', $publish->json(), $account, $asset);
            return PublishResult::failure('instagram_publish_error', $publish->json() ?? []);
        }

        $body = $publish->json();
        $platformId = $body['id'] ?? null;

        return PublishResult::success($platformId, $body);
    }

    private function decryptToken(?string $encrypted): ?string
    {
        if (!$encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            Log::warning('meta_token_decrypt_failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Optional helper if provider needs app credentials (fallback to config/services.php).
     */
    private function getAppCredentials(): array
    {
        $config = OAuthService::getConfig('meta');

        return [
            'app_id' => $config['client_id'] ?? config('services.meta.app_id'),
            'app_secret' => $config['client_secret'] ?? config('services.meta.app_secret'),
            'redirect_uri' => $config['redirect_uri'] ?? config('services.meta.redirect_uri'),
        ];
    }

    private function resolveVisualPath(SocialAsset $asset): ?string
    {
        if (!$asset->visual_path) {
            return null;
        }

        // If external URL (Unsplash, etc), download temporarily
        if ($this->isExternalUrl($asset->visual_path)) {
            return $this->downloadTemporary($asset->visual_path);
        }

        // Local storage path
        $path = Storage::disk('public')->path($asset->visual_path);

        return file_exists($path) ? $path : null;
    }

    private function resolveVisualUrl(SocialAsset $asset): ?string
    {
        if (!$asset->visual_path) {
            return null;
        }

        // If external URL, return directly
        if ($this->isExternalUrl($asset->visual_path)) {
            return $asset->visual_path;
        }

        // Local storage path - convert to full URL
        return asset('storage/' . ltrim($asset->visual_path, '/'));
    }

    private function isExternalUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function downloadTemporary(string $url): ?string
    {
        try {
            $tempPath = sys_get_temp_dir() . '/meta-publish-' . md5($url) . '.jpg';

            // Download image
            $imageContent = Http::timeout(30)->get($url)->body();

            if (empty($imageContent)) {
                return null;
            }

            file_put_contents($tempPath, $imageContent);

            return $tempPath;
        } catch (\Throwable $e) {
            Log::warning('meta_download_external_image_failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function logError(string $stage, ?array $body, SocialAccount $account, SocialAsset $asset): void
    {
        Log::warning('meta_publish_failed', [
            'stage' => $stage,
            'account_id' => $account->id,
            'provider' => $account->provider,
            'asset_id' => $asset->id,
            'response' => $body,
        ]);
    }
}
