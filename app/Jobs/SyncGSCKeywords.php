<?php

namespace App\Jobs;

use App\Models\GSCConnection;
use App\Models\GSCSyncJob;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncGSCKeywords implements ShouldQueue
{
    use Queueable;

    protected GSCConnection $gscConnection;
    protected int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(GSCConnection $connection, int $days = 30)
    {
        $this->gscConnection = $connection;
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleSearchConsoleService $gscService): void
    {
        // Create sync job record
        $syncJob = GSCSyncJob::create([
            'connection_id' => $this->gscConnection->id,
            'start_date' => now()->subDays($this->days),
            'end_date' => now()->subDay(),
            'job_status' => 'pending',
        ]);

        // Load connection relationship
        $syncJob->load('connection');

        try {
            $syncJob->markAsProcessing();

            // Sync keyword data
            $result = $gscService->syncKeywordData($this->gscConnection, $this->days);

            $syncJob->update([
                'keywords_synced' => $result['keywords_synced'],
                'current_step' => 'Keywords synced successfully',
            ]);

            // Sync page data
            $pageResult = $gscService->syncPageData($this->gscConnection, $this->days);

            $syncJob->update([
                'pages_synced' => $pageResult['pages_synced'],
                'current_step' => 'Pages synced successfully',
            ]);

            $syncJob->markAsCompleted();

            Log::info("GSC sync completed", [
                'connection_id' => $this->gscConnection->id,
                'keywords' => $result['keywords_synced'],
                'pages' => $pageResult['pages_synced'],
            ]);

        } catch (\Exception $e) {
            $syncJob->markAsFailed($e->getMessage());

            Log::error("GSC sync failed", [
                'connection_id' => $this->gscConnection->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
