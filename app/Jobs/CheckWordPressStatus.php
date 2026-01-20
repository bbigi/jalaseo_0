<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\PostTarget;
use App\Services\WordPressPublishService;
use Illuminate\Support\Facades\Log;

class CheckWordPressStatus implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $targetId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $target = PostTarget::with('website')->find($this->targetId);

        if (!$target) {
            Log::warning("PostTarget #{$this->targetId} not found");
            return;
        }

        if ($target->status !== 'scheduled') {
            return;
        }

        if (!$target->wp_post_id) {
            Log::warning("PostTarget #{$this->targetId} has no wp_post_id");
            return;
        }

        try {
            $wpService = new WordPressPublishService();
            $wpStatus = $wpService->checkPostStatus($target);

            if ($wpStatus === 'publish') {
                $target->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);

                Log::info("PostTarget #{$this->targetId} updated to 'published'");
            }
        } catch (\Exception $e) {
            Log::error("Failed to check WordPress status for PostTarget #{$this->targetId}: " . $e->getMessage());
            throw $e;
        }
    }
}
