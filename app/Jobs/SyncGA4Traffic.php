<?php

namespace App\Jobs;

use App\Models\GA4Connection;
use App\Services\GoogleAnalytics4Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncGA4Traffic implements ShouldQueue
{
    use Queueable;

    protected GA4Connection $ga4Connection;
    protected int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(GA4Connection $connection, int $days = 30)
    {
        $this->ga4Connection = $connection;
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleAnalytics4Service $ga4Service): void
    {
        try {
            $this->ga4Connection->update([
                'sync_status' => 'syncing',
            ]);

            // Sync traffic data
            $result = $ga4Service->syncTrafficData($this->ga4Connection, $this->days);

            // Sync page stats
            $pageResult = $ga4Service->syncPageStats($this->ga4Connection, $this->days);

            $this->ga4Connection->update([
                'last_sync_at' => now(),
                'sync_status' => 'completed',
                'sync_error' => null,
            ]);

            Log::info("GA4 traffic sync completed", [
                'connection_id' => $this->ga4Connection->id,
                'traffic_rows' => $result['rows_synced'],
                'pages' => $pageResult['pages_synced'],
            ]);

        } catch (\Exception $e) {
            $this->ga4Connection->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error("GA4 traffic sync failed", [
                'connection_id' => $this->ga4Connection->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
