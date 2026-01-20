<?php

namespace App\Jobs;

use App\Models\HealthPageMetric;
use App\Models\HealthSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class RunHealthCheckJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $healthSessionId;

    public function __construct(int $healthSessionId)
    {
        $this->healthSessionId = $healthSessionId;
    }

    public function handle(): void
    {
        $session = HealthSession::find($this->healthSessionId);

        if (!$session) {
            return;
        }

        if (in_array($session->status, ['completed', 'failed'], true)) {
            return;
        }

        if ($session->status !== 'processing') {
            $session->update(['status' => 'processing']);
        }

        $website = $session->website;

        if (!$website) {
            throw new \Exception('Website not found for health session');
        }

        // =============================
        // 1. SITEMAP & INDEXING CALCULATION
        // =============================
        $sitemapService = app(\App\Services\SitemapService::class);
        $sitemapUrlCount = $sitemapService->getTotalUrls($website);

        $gscPageCount = 0;
        if (class_exists(\App\Models\GSCPage::class)) {
            $gscPageCount = \App\Models\GSCPage::where('website_id', $website->id)
                ->distinct('page_url')
                ->count('page_url');
        }

        if ($sitemapUrlCount && $sitemapUrlCount > 0) {
            $indexedRatio = ($gscPageCount / $sitemapUrlCount) * 100;

            $indexingScore = match (true) {
                $indexedRatio >= 98 => 40,
                $indexedRatio >= 95 => 32,
                $indexedRatio >= 90 => 24,
                default => 16,
            };

            $indexingSource = 'sitemap_vs_gsc';
            $indexingData = [
                'sitemap_urls' => $sitemapUrlCount,
                'gsc_pages' => $gscPageCount,
                'ratio' => round($indexedRatio, 2),
            ];
        } else {
            $indexingScore = 16;
            $indexingSource = 'no_sitemap_detected';
            $indexingData = [
                'sitemap_urls' => null,
                'gsc_pages' => $gscPageCount,
                'ratio' => null,
            ];
        }

        // =============================
        // 2. URL SELECTION (HS-V1-06)
        // =============================
        $selectedUrls = [];
        $selectionReason = [];

        $homepage = rtrim($website->url, '/');
        $selectedUrls[] = $homepage;
        $selectionReason['homepage'] = 'Always included';

        if (class_exists(\App\Models\GSCPage::class)) {
            $topPages = \App\Models\GSCPage::where('website_id', $website->id)
                ->select('page_url', DB::raw('SUM(impressions) as total_impressions'))
                ->groupBy('page_url')
                ->orderByDesc('total_impressions')
                ->limit(5)
                ->pluck('page_url')
                ->toArray();

            if (!empty($topPages)) {
                $selectedUrls = array_merge($selectedUrls, $topPages);
                $selectionReason['gsc_top_pages'] = count($topPages) . ' pages by impressions';
            } else {
                $selectionReason['gsc_top_pages'] = 'No GSC data available';
            }
        } else {
            $selectionReason['gsc_top_pages'] = 'GSCPage model not found';
        }

        $selectedUrls = array_values(array_unique($selectedUrls));
        $selectedUrls = array_slice($selectedUrls, 0, 6);

        // =============================
        // 3. PERFORMANCE FROM HOMEPAGE SPEED TEST
        // =============================
        // Prioritize mobile (Google mobile-first indexing), fallback to desktop
        $speedRun = \App\Models\PageSpeedRun::where('website_id', $website->id)
            ->where('url', $homepage)
            ->where('strategy', 'mobile')
            ->latest()
            ->first();

        if (!$speedRun) {
            $speedRun = \App\Models\PageSpeedRun::where('website_id', $website->id)
                ->where('url', $homepage)
                ->where('strategy', 'desktop')
                ->latest()
                ->first();
        }

        if (!$speedRun) {
            throw new \Exception('No speed test data for homepage. Run speed test first at: ' . route('website-analytics.speed-insights', $website));
        }

        $scores = $speedRun->scores ?? [];
        $avgPerformance = $scores['performance'] ?? 0;
        $labMetrics = $speedRun->lab_metrics ?? [];
        $opportunities = $speedRun->opportunities ?? [];

        HealthPageMetric::create([
            'session_id' => $session->id,
            'url' => $homepage,
            'performance_score' => $avgPerformance,
            'lcp_ms' => $labMetrics['lcp_ms'] ?? null,
            'inp_ms' => $labMetrics['inp_ms'] ?? null,
            'cls' => $labMetrics['cls'] ?? null,
            'ttfb_ms' => null,
            'issues_json' => array_slice($opportunities, 0, 8),
        ]);

        $avgLcp = $labMetrics['lcp_ms'] ?? 0;
        $avgInp = $labMetrics['inp_ms'] ?? 0;
        $avgCls = $labMetrics['cls'] ?? 0;
        $avgTtfb = 0;

        // =============================
        // 4. HEALTH SCORE CALCULATION
        // =============================

        // Performance score (40%)
        $performanceScore = match (true) {
            $avgPerformance >= 90 => 40,
            $avgPerformance >= 80 => 32,
            $avgPerformance >= 70 => 24,
            $avgPerformance >= 50 => 16,
            default => 8,
        };

        // Indexing score already calculated above (40%)

        // Page Experience score (20%) – default v1
        $pageExperienceScore = 20;

        $healthScore = $indexingScore + $performanceScore + $pageExperienceScore;

        // Determine health status label and color
        $healthStatus = match (true) {
            $healthScore >= 90 => ['label' => 'excellent', 'color' => 'green'],
            $healthScore >= 75 => ['label' => 'good', 'color' => 'blue'],
            $healthScore >= 60 => ['label' => 'needs_improvement', 'color' => 'yellow'],
            default => ['label' => 'critical', 'color' => 'red'],
        };

        // =============================
        // 4. SAVE SESSION SNAPSHOT & COMPLETE
        // =============================

        // Read existing summary_json
        $existingSummary = is_array($session->summary_json)
            ? $session->summary_json
            : (json_decode($session->summary_json ?? '[]', true) ?: []);

        // Merge new data
        $updatedSummary = array_merge($existingSummary, [
            'indexing' => $indexingData,
            'performance' => [
                'homepage_url' => $homepage,
                'score' => $avgPerformance,
                'lcp_ms' => $avgLcp,
                'inp_ms' => $avgInp,
                'cls' => $avgCls,
            ],
            'health' => [
                'status' => $healthStatus,
                'indexing_score' => $indexingScore,
                'performance_score' => $performanceScore,
                'page_experience_score' => $pageExperienceScore,
                'health_score' => $healthScore,
                'indexing_source' => $indexingSource,
            ],
        ]);

        $session->update([
            'status' => 'completed',
            'indexing_score' => $indexingScore,
            'performance_score' => $performanceScore,
            'page_experience_score' => $pageExperienceScore,
            'health_score' => $healthScore,
            'summary_json' => $updatedSummary,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $session = HealthSession::find($this->healthSessionId);

        if (!$session) {
            return;
        }

        $summary = is_array($session->summary_json)
            ? $session->summary_json
            : (json_decode($session->summary_json ?? '[]', true) ?: []);

        $summary['error'] = [
            'message' => $exception->getMessage(),
            'type' => get_class($exception),
            'at' => now()->toISOString(),
        ];

        $session->update([
            'status' => 'failed',
            'summary_json' => $summary,
        ]);
    }
}
