<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PostTarget;
use App\Services\WordPressPublishService;

class SyncWordPressStatus extends Command
{
    protected $signature = 'wordpress:sync-status';
    protected $description = 'Sync scheduled post status from WordPress';

    public function handle()
    {
        $this->info('Dispatching WordPress status check jobs...');

        $count = 0;

        PostTarget::where('status', 'scheduled')
            ->whereNotNull('wp_post_id')
            ->whereNotNull('scheduled_at')
            ->chunkById(50, function ($targets) use (&$count) {
                foreach ($targets as $target) {
                    \App\Jobs\CheckWordPressStatus::dispatch($target->id);
                    $this->line("Dispatched job for Post Target #{$target->id}");
                    $count++;
                }
            });

        $this->info("Dispatched {$count} jobs to queue.");
        $this->comment("Run 'php artisan queue:work' to process jobs.");

        return Command::SUCCESS;
    }
}
