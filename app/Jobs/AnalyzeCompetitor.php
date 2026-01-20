<?php

namespace App\Jobs;

use App\Models\CompetitorAnalysisSession;
use App\Services\CompetitorAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeCompetitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public int $sessionId
    ) {}

    public function handle(CompetitorAnalysisService $service): void
    {
        $session = CompetitorAnalysisSession::find($this->sessionId);
        if (!$session) {
            Log::warning('[AnalyzeCompetitor] Session not found: ' . $this->sessionId);
            return;
        }

        $service->analyze($session);
    }
}
