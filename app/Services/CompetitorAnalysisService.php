<?php

namespace App\Services;

use App\Models\CompetitorAnalysisSession;
use App\Models\CompetitorKeyword;
use App\Models\CompetitorProfile;
use App\Models\CompetitorRaw;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CompetitorAnalysisService
{
    public function __construct(
        private KeywordInsightService $keywordInsight
    ) {}

    public function analyze(CompetitorAnalysisSession $session): void
    {
        $competitor = CompetitorProfile::find($session->competitor_id);
        if (!$competitor) {
            $this->failSession($session, 'Competitor not found.');
            return;
        }

        try {
            if (!$this->keywordInsight->isConfigured()) {
                $this->failSession($session, 'Keyword Insight API not configured.');
                return;
            }

            $this->updateProgress($session, 5, 'Fetching competitor profile...');

            $location = $this->mapCountryToIso($competitor->country ?? 'Indonesia');
            $languageRaw = $competitor->language ?? 'id';
            $language = strtolower(explode('_', $languageRaw)[0]);
            $domain = $competitor->domain;

            $this->updateProgress($session, 15, 'Fetching keywords...');
            $keywords = $this->keywordInsight->getKeywordsFromUrl($domain, $location, $language);

            if (empty($keywords)) {
                $this->failSession($session, 'No keywords returned for this competitor.');
                return;
            }

            CompetitorRaw::create([
                'competitor_id' => $competitor->id,
                'session_id' => $session->session_id,
                'source' => 'keyword_insight',
                'payload' => $keywords,
                'response_time_ms' => null,
            ]);

            $this->updateProgress($session, 35, 'Calculating metrics...');

            $metrics = $this->calculateMetrics($keywords);
            $distributions = $this->calculateDistributions($keywords);
            $topKeywords = $this->getTopKeywords($keywords, 5);

            $competitor->update([
                'total_keywords' => $metrics['total_keywords'],
                'avg_rank' => $metrics['avg_rank'],
                'traffic_estimate' => $metrics['traffic_estimate'],
                'market_share' => $metrics['market_share'],
                'intent_distribution' => $distributions['intent'],
                'keyword_type_distribution' => $distributions['keyword_type'],
                'content_format_distribution' => $distributions['content_format'],
                'top_keywords' => $topKeywords,
                'top_urls' => [],
                'last_analyzed_at' => now(),
            ]);

            $this->updateProgress($session, 65, 'Saving keywords...');
            CompetitorKeyword::where('competitor_id', $competitor->id)->delete();
            $this->storeKeywords($competitor->id, $session->session_id, $keywords);

            $this->updateProgress($session, 90, 'Finalizing analysis...');
            $session->update([
                'status' => 'completed',
                'progress' => 100,
                'current_step' => 'Analysis completed.',
                'analysis_summary' => [
                    'total_keywords' => $metrics['total_keywords'],
                    'top_keywords' => $topKeywords,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('[CompetitorAnalysis] Error: ' . $e->getMessage());
            $this->failSession($session, $e->getMessage());
        }
    }

    private function updateProgress(CompetitorAnalysisSession $session, int $progress, string $step): void
    {
        $session->update([
            'status' => 'processing',
            'progress' => $progress,
            'current_step' => $step,
        ]);
    }

    private function failSession(CompetitorAnalysisSession $session, string $message): void
    {
        $session->update([
            'status' => 'failed',
            'error_message' => $message,
            'current_step' => 'Failed',
        ]);
    }

    private function calculateMetrics(array $keywords): array
    {
        $total = count($keywords);
        $trafficEstimate = 0;

        foreach ($keywords as $kw) {
            $trafficEstimate += (int) ($kw['volume'] ?? 0);
        }

        return [
            'total_keywords' => $total,
            'avg_rank' => null,
            'traffic_estimate' => $trafficEstimate > 0 ? (int) round($trafficEstimate * 0.05) : null,
            'market_share' => null,
        ];
    }

    private function calculateDistributions(array $keywords): array
    {
        $intent = ['informational' => 0, 'commercial' => 0, 'transactional' => 0, 'navigational' => 0];
        $keywordType = ['short' => 0, 'medium' => 0, 'long' => 0];
        $contentFormat = [];

        foreach ($keywords as $kw) {
            $intentKey = $kw['intent'] ?? 'informational';
            if (isset($intent[$intentKey])) {
                $intent[$intentKey]++;
            }

            $type = $kw['keyword_type'] ?? $this->inferKeywordType($kw['keyword'] ?? '');
            if (isset($keywordType[$type])) {
                $keywordType[$type]++;
            }

            $format = $this->inferContentFormat($kw['keyword'] ?? '');
            $contentFormat[$format] = ($contentFormat[$format] ?? 0) + 1;
        }

        return [
            'intent' => $this->toPercent($intent),
            'keyword_type' => $this->toPercent($keywordType),
            'content_format' => $this->toPercent($contentFormat),
        ];
    }

    private function storeKeywords(int $competitorId, string $sessionId, array $keywords): void
    {
        $rows = [];
        foreach ($keywords as $kw) {
            $rows[] = [
                'competitor_id' => $competitorId,
                'session_id' => $sessionId,
                'keyword' => $kw['keyword'] ?? '',
                'position' => 0,
                'search_volume' => $kw['volume'] ?? null,
                'cpc' => $kw['cpc'] ?? null,
                'competition' => $kw['competition_score'] ?? null,
                'traffic_share' => null,
                'url' => null,
                'intent' => $kw['intent'] ?? null,
                'keyword_type' => $kw['keyword_type'] ?? $this->inferKeywordType($kw['keyword'] ?? ''),
                'content_format' => $this->inferContentFormat($kw['keyword'] ?? ''),
                'source' => 'dataforseo',
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            CompetitorKeyword::insert($chunk);
        }
    }

    private function getTopKeywords(array $keywords, int $limit): array
    {
        usort($keywords, function ($a, $b) {
            return ($b['volume'] ?? 0) <=> ($a['volume'] ?? 0);
        });

        $top = array_slice($keywords, 0, $limit);

        return array_map(function ($kw) {
            return [
                'keyword' => $kw['keyword'] ?? '',
                'volume' => $kw['volume'] ?? 0,
                'position' => $kw['position'] ?? null,
                'intent' => $kw['intent'] ?? null,
            ];
        }, $top);
    }

    private function inferKeywordType(string $keyword): string
    {
        $count = str_word_count($keyword);
        if ($count <= 2) {
            return 'short';
        }
        if ($count <= 4) {
            return 'medium';
        }
        return 'long';
    }

    private function inferContentFormat(string $keyword): string
    {
        $keyword = Str::lower($keyword);
        if (str_contains($keyword, 'how') || str_contains($keyword, 'cara')) {
            return 'how-to';
        }
        if (str_contains($keyword, 'vs') || str_contains($keyword, 'perbandingan')) {
            return 'comparison';
        }
        if (str_contains($keyword, 'review') || str_contains($keyword, 'ulasan')) {
            return 'review';
        }
        if (str_contains($keyword, 'list') || str_contains($keyword, 'top')) {
            return 'listicle';
        }

        return 'general';
    }

    private function toPercent(array $counts): array
    {
        $total = array_sum($counts);
        if ($total <= 0) {
            return [];
        }

        foreach ($counts as $key => $value) {
            $counts[$key] = round(($value / $total) * 100, 1);
        }

        return $counts;
    }

    private function mapCountryToIso(string $country): string
    {
        $country = trim($country);
        if (strlen($country) === 2) {
            return strtoupper($country);
        }

        return match (strtolower($country)) {
            'indonesia' => 'ID',
            'united states', 'usa', 'us' => 'US',
            'united kingdom', 'uk' => 'GB',
            'singapore' => 'SG',
            'malaysia' => 'MY',
            default => 'ID',
        };
    }
}
