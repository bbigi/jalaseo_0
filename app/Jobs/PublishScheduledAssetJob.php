<?php

namespace App\Jobs;

use App\Models\DistributionSchedule;
use App\Models\SocialAccount;
use App\Models\SocialAsset;
use App\Services\SocialPublishers\PublishResult;
use App\Services\SocialPublishers\SocialPublisherManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledAssetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        private readonly int $scheduleId
    ) {
    }

    public function handle(SocialPublisherManager $publisherManager): void
    {
        $schedule = DistributionSchedule::with(['asset'])->find($this->scheduleId);

        if (!$schedule || !$schedule->isPending() || !$schedule->asset) {
            return;
        }

        $asset = $schedule->asset;
        $provider = $this->resolveProviderFromChannel($asset);
        $account = $this->resolveAccount($schedule, $provider);

        if (!$provider || !$account) {
            $this->markFailed($schedule, 'social_account_missing', ['channel' => $asset->channel]);
            return;
        }

        $publisher = $publisherManager->resolve($provider);

        if (!$publisher) {
            $this->markFailed($schedule, 'publisher_not_configured', ['provider' => $provider]);
            return;
        }

        $schedule->increment('attempt_count');
        $schedule->refresh();

        try {
            $result = $publisher->publish($asset, $schedule, $account);
        } catch (\Throwable $e) {
            Log::warning('PublishScheduledAssetJob error', [
                'schedule_id' => $schedule->id,
                'asset_id' => $asset->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            $this->markFailed($schedule, 'exception', ['message' => $e->getMessage()]);
            return;
        }

        $this->persistResult($schedule, $result);
    }

    private function resolveProviderFromChannel(SocialAsset $asset): ?string
    {
        return match ($asset->channel) {
            'instagram' => 'meta_instagram',
            'facebook' => 'meta_facebook',
            'twitter' => 'x',
            'linkedin' => 'linkedin',
            default => null,
        };
    }

    private function resolveAccount(DistributionSchedule $schedule, ?string $provider): ?SocialAccount
    {
        if (!$provider) {
            return null;
        }

        return SocialAccount::where('user_id', $schedule->created_by)
            ->where('provider', $provider)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();
    }

    private function persistResult(DistributionSchedule $schedule, PublishResult $result): void
    {
        if ($result->success) {
            $schedule->update([
                'publish_status' => 'success',
                'platform_post_id' => $result->platformPostId,
                'publish_response' => $result->response,
                'last_error' => null,
                'publish_payload' => [
                    'channel' => $schedule->asset?->channel,
                    'attempt_count' => $schedule->attempt_count,
                ],
                'published_at' => now(),
            ]);

            return;
        }

        $this->markFailed($schedule, $result->error ?? 'publish_failed', $result->response);
    }

    private function markFailed(DistributionSchedule $schedule, string $error, array $context = []): void
    {
        $schedule->update([
            'publish_status' => 'failed',
            'last_error' => $error,
            'publish_response' => $context,
            'publish_payload' => [
                'channel' => $schedule->asset?->channel,
                'attempt_count' => $schedule->attempt_count,
            ],
        ]);
    }
}
