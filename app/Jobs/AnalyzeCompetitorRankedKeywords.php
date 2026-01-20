<?php

namespace App\Jobs;

use App\Models\CompetitorAdvancedSession;
use App\Models\CompetitorProfile;
use App\Models\CompetitorRankedKeyword;
use App\Services\DataForSEORankedKeywordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeCompetitorRankedKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 240;

    public function __construct(
        public int $sessionId
    ) {}

    public function handle(DataForSEORankedKeywordService $service): void
    {
        $session = CompetitorAdvancedSession::find($this->sessionId);
        if (!$session) {
            Log::warning('[AdvancedRanked] Session not found', ['session_id' => $this->sessionId]);
            return;
        }

        $competitor = CompetitorProfile::find($session->competitor_id);
        if (!$competitor) {
            $session->update([
                'status' => 'failed',
                'error_message' => 'Competitor not found',
                'progress' => 100,
                'finished_at' => now(),
            ]);
            return;
        }

        try {
            $session->update([
                'status' => 'processing',
                'progress' => 10,
                'started_at' => now(),
            ]);

            $result = $service->fetchRankedKeywords($competitor->domain, 2360, 'id', 50);

            // Temporary insight to verify payload structure (will log only minimal info)
            Log::info('[AdvancedRanked] DataForSEO result snapshot', [
                'session_id' => $session->id,
                'items_count' => isset($result['items']) ? count($result['items']) : 0,
                'sample_keyword' => $result['items'][0]['keyword_data']['keyword'] ?? null,
                'sample_rank_group' => $result['items'][0]['ranked_serp_element']['rank_group'] ?? null,
            ]);

            $metrics = $result['metrics']['organic'] ?? [];
            $items = $result['items'] ?? [];

            DB::transaction(function () use ($session, $competitor, $metrics, $items) {
                // Store summary metrics directly from API
                $session->update([
                    'summary_metrics' => $metrics,
                    'progress' => 40,
                ]);

                // Clear previous ranked keywords for this competitor
                CompetitorRankedKeyword::where('competitor_id', $competitor->id)->delete();

                $rows = [];
                foreach ($items as $item) {
                    $keywordMeta = $item['keyword_data'] ?? [];
                    $keywordInfo = $keywordMeta['keyword_info'] ?? [];
                    $rankData = $item['ranked_serp_element'] ?? [];
                    $serpItem = $rankData['serp_item'] ?? [];

                    $rows[] = [
                        'competitor_id' => $competitor->id,
                        'session_id' => $session->id,
                        'keyword' => $keywordMeta['keyword'] ?? ($item['keyword'] ?? ''),
                        'search_volume' => $keywordInfo['search_volume'] ?? null,
                        'cpc' => $keywordInfo['cpc'] ?? null,
                        'competition_level' => $keywordInfo['competition_level'] ?? null,
                        'rank_group' => $serpItem['rank_group'] ?? null,
                        'rank_absolute' => $serpItem['rank_absolute'] ?? null,
                        'url' => $serpItem['url'] ?? null,
                        'title' => $serpItem['title'] ?? null,
                        'etv' => $serpItem['etv'] ?? null,
                        'is_new' => (bool) ($item['is_new'] ?? false),
                        'source' => 'dataforseo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($rows)) {
                    foreach (array_chunk($rows, 500) as $chunk) {
                        CompetitorRankedKeyword::insert($chunk);
                    }
                }

                $session->update([
                    'progress' => 100,
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('[AdvancedRanked] Failed', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
            ]);
            $session->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'progress' => 100,
                'finished_at' => now(),
            ]);
        }
    }
}
