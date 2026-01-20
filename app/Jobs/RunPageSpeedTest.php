<?php

namespace App\Jobs;

use App\Models\PageSpeedRun;
use App\Services\PageSpeedService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunPageSpeedTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public PageSpeedRun $run;

    public function __construct(PageSpeedRun $run)
    {
        $this->run = $run;
    }

    public function handle(PageSpeedService $service, \App\Services\PageSpeedInterpreter $interpreter): void
    {
        $this->run->update([
            'status' => 'running',
            'progress' => 25,
        ]);

        try {
            $data = $service->run($this->run->url, $this->run->strategy);

            $payload = [
                'status' => 'success',
                'progress' => 100,
                'lighthouse_version' => $data['lighthouse_version'] ?? null,
                'fetch_time' => $data['fetch_time'] ?? null,
                'scores' => $data['scores'] ?? null,
                'lab_metrics' => $data['lab_metrics'] ?? null,
                'field_metrics' => $data['field_metrics'] ?? null,
                'opportunities' => $data['opportunities'] ?? null,
                'diagnostics' => $data['diagnostics'] ?? null,
                'resources_summary' => $data['resources_summary'] ?? null,
                'error_message' => null,
            ];

            $tempRun = $this->run->replicate()->fill($payload);
            $insights = $interpreter->build($tempRun);

            $this->run->update($payload + [
                'summary_text' => $insights['summary_text'] ?? null,
                'insights' => $insights,
            ]);
        } catch (\Throwable $e) {
            Log::error('[PageSpeed] Run failed', [
                'run_id' => $this->run->id,
                'message' => $e->getMessage(),
            ]);

            $this->run->update([
                'status' => 'error',
                'progress' => 100,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
