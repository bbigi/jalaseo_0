<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\PublishScheduledAssetJob;
use App\Models\PostTarget;
use App\Models\DistributionSchedule;
use App\Services\WordPressPublishService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check WordPress scheduled posts status every 5 minutes
// WordPress handles the actual scheduling/publishing (status='future')
// This job only checks if WordPress has auto-published the post
Schedule::call(function () {
    PostTarget::where('status', 'scheduled')
        ->whereNotNull('wp_post_id')
        ->whereNotNull('scheduled_at')
        ->chunkById(50, function ($targets) {
            foreach ($targets as $target) {
                \App\Jobs\CheckWordPressStatus::dispatch($target->id);
            }
        });
})->everyFiveMinutes()->name('check-wordpress-status');

// Dispatch social autoshare jobs every minute for due schedules
Schedule::call(function () {
    $now = now();

    DistributionSchedule::where('publish_status', 'pending')
        ->whereNull('queued_at')
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', $now)
        ->orderBy('id')
        ->chunkById(50, function ($schedules) {
            foreach ($schedules as $schedule) {
                if ($schedule->queued_at) {
                    continue;
                }

                $schedule->forceFill(['queued_at' => now()])->save();

                PublishScheduledAssetJob::dispatch($schedule->id)->onQueue('social-publish');
            }
        });
})->everyMinute()->name('dispatch-social-autoshare');
