<?php

namespace App\Jobs;

use App\Models\DeepResearchSession;
use App\Services\DeepResearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDeepResearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 2;
    public int $maxExceptions = 1;

    public function __construct(
        public DeepResearchSession $session
    ) {}

    public function handle(DeepResearchService $service): void
    {
        Log::info("[DeepResearch] Processing session: {$this->session->session_id}");
        
        try {
            $service->processSession($this->session);
            Log::info("[DeepResearch] Completed: {$this->session->session_id}");
        } catch (\Exception $e) {
            Log::error("[DeepResearch] Failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->session->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
        ]);
        
        Log::error("[DeepResearch] Job failed for session: {$this->session->session_id}");
    }
}
