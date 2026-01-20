<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ApiCredential;

/**
 * KeywordInsightService
 * 
 * Wrapper for Google Keyword Insight API via RapidAPI
 * Provides keyword suggestions, search volume, competition, and SEO metrics
 * 
 * Features:
 * - Keyword suggestions with volume & intent
 * - Opportunity score calculation
 * - Intent detection (informational, commercial, transactional, navigational)
 * - Priority scoring
 * - Difficulty estimation
 */
class KeywordInsightService
{
    protected ?string $apiKey = null;
    protected string $baseUrl = 'https://google-keyword-insight1.p.rapidapi.com';
    protected string $host = 'google-keyword-insight1.p.rapidapi.com';
    
    // Rate limiting
    protected int $requestDelay = 2; // seconds between requests
    protected float $lastRequestTime = 0;

    public function __construct()
    {
        $this->loadCredentials();
    }

    protected function loadCredentials(): void
    {
        $cred = ApiCredential::where('category', 'rapid_api')
            ->where('provider', 'rapidapi.com')
            ->where('is_active', true)
            ->first();

        if ($cred) {
            $this->apiKey = $cred->api_key;
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Enforce rate limiting between API requests
     */
    protected function enforceRateLimit(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastRequestTime;
        
        if ($elapsed < $this->requestDelay) {
            $sleepMs = (int)(($this->requestDelay - $elapsed) * 1000000);
            usleep($sleepMs);
        }
        
        $this->lastRequestTime = microtime(true);
    }

    /**
     * Get keyword suggestions with full metrics
     */
    public function getKeywordSuggestions(string $keyword, string $location = 'ID', string $lang = 'id'): array
    {
        if (!$this->isConfigured()) {
            Log::warning('KeywordInsight: API not configured');
            return [];
        }

        try {
            $this->enforceRateLimit();
            
            Log::info("KeywordInsight: Fetching suggestions for: {$keyword}");
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-host' => $this->host,
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/keysuggest", [
                    'keyword' => $keyword,
                    'location' => $location,
                    'lang' => $lang,
                ]);

            if ($response->successful()) {
                $rawData = $this->fixMalformedJson($response->body());
                $parsed = $this->parseKeywordSuggestions($rawData);
                Log::info("KeywordInsight: Got " . count($parsed) . " keywords for '{$keyword}'");
                return $parsed;
            }

            Log::error('KeywordInsight API error: ' . $response->status() . ' - ' . $response->body());
            return [];

        } catch (\Exception $e) {
            Log::error('KeywordInsight exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top opportunity keywords
     */
    public function getTopKeywords(string $keyword, string $location = 'ID', string $lang = 'id', int $num = 20): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $this->enforceRateLimit();
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-host' => $this->host,
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/topkeys", [
                    'keyword' => $keyword,
                    'location' => $location,
                    'lang' => $lang,
                    'num' => $num,
                ]);

            if ($response->successful()) {
                $rawData = $this->fixMalformedJson($response->body());
                return $this->parseKeywordSuggestions($rawData);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('KeywordInsight topkeys error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get keywords from competitor URL
     */
    public function getKeywordsFromUrl(string $url, string $location = 'ID', string $lang = 'id'): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $this->enforceRateLimit();
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-host' => $this->host,
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/urlkeysuggest", [
                    'url' => $url,
                    'location' => $location,
                    'lang' => $lang,
                ]);

            if ($response->successful()) {
                $rawData = $this->fixMalformedJson($response->body());
                return $this->parseKeywordSuggestions($rawData);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('KeywordInsight URL error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Batch get keywords for multiple seeds (with rate limiting)
     */
    public function batchGetKeywords(array $keywords, string $location = 'ID', string $lang = 'id'): array
    {
        $allKeywords = [];
        $seen = [];
        
        Log::info("KeywordInsight: Batch fetching " . count($keywords) . " keywords");

        foreach ($keywords as $index => $keyword) {
            try {
                $suggestions = $this->getKeywordSuggestions($keyword, $location, $lang);
                
                foreach ($suggestions as $item) {
                    $hash = md5(strtolower(trim($item['keyword'])));
                    if (!isset($seen[$hash])) {
                        $seen[$hash] = true;
                        $allKeywords[] = $item;
                    }
                }
                
            } catch (\Exception $e) {
                Log::warning("KeywordInsight batch error for '{$keyword}': " . $e->getMessage());
                continue;
            }
        }

        Log::info("KeywordInsight: Batch complete, got " . count($allKeywords) . " unique keywords");
        return $allKeywords;
    }

    /**
     * Fix malformed JSON from API (concatenated objects without array wrapper)
     */
    protected function fixMalformedJson(string $body): array
    {
        $body = trim($body);
        
        // Check if starts with { instead of [
        if (str_starts_with($body, '{')) {
            $body = '[' . $body . ']';
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('KeywordInsight JSON parse error: ' . json_last_error_msg());
            return [];
        }
        
        return $data ?? [];
    }

    /**
     * Parse keyword suggestions to standardized format with all metrics
     */
    protected function parseKeywordSuggestions(array $data): array
    {
        $keywords = [];
        
        // Handle different response structures
        $items = $data['keywords'] ?? $data['data'] ?? $data ?? [];
        
        // Check if numeric array (direct items)
        if (!empty($items) && array_keys($items) === range(0, count($items) - 1)) {
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                
                $parsed = $this->parseKeywordItem($item);
                if ($parsed) {
                    $keywords[] = $parsed;
                }
            }
        }
        
        return $keywords;
    }

    /**
     * Parse single keyword item with all calculated metrics
     */
    protected function parseKeywordItem(array $item): ?array
    {
        $keywordText = $item['text'] ?? $item['keyword'] ?? null;
        if (!$keywordText) return null;
        
        $volume = (int)($item['volume'] ?? $item['search_volume'] ?? 0);
        $competitionLevel = $item['competition_level'] ?? $item['competition'] ?? null;
        $competitionScore = $this->normalizeCompetitionToScore($competitionLevel);
        $cpc = (float)($item['high_bid'] ?? $item['cpc'] ?? $item['low_bid'] ?? 0);
        $trend = (float)($item['trend'] ?? 0);
        
        // Detect intent if not provided
        $intent = $item['intent'] ?? $this->detectIntent($keywordText);
        
        // Calculate all scores
        $difficultyScore = $this->calculateDifficultyScore($volume, $competitionScore);
        $opportunityScore = $this->calculateOpportunityScore($volume, $difficultyScore);
        $priorityScore = $this->calculatePriorityScore($volume, $competitionScore, $intent, $trend);
        
        return [
            'keyword' => $keywordText,
            'volume' => $volume,
            'competition_level' => $this->normalizeCompetitionLevel($competitionLevel),
            'competition_score' => $competitionScore,
            'cpc' => $cpc,
            'trend' => $trend,
            'intent' => $intent,
            'difficulty_score' => $difficultyScore,
            'opportunity_score' => $opportunityScore,
            'priority_score' => $priorityScore,
            'keyword_type' => $this->classifyKeywordType($keywordText),
            'keyword_hash' => md5(strtolower(trim($keywordText))),
        ];
    }

    /**
     * Normalize competition level to string
     */
    protected function normalizeCompetitionLevel(?string $competition): ?string
    {
        if (!$competition) return null;
        
        $competition = strtoupper(trim($competition));
        
        return match($competition) {
            'L', 'LOW' => 'LOW',
            'M', 'MEDIUM', 'MED' => 'MEDIUM',
            'H', 'HIGH' => 'HIGH',
            default => null,
        };
    }

    /**
     * Convert competition level to numeric score (0-100)
     */
    protected function normalizeCompetitionToScore(?string $competition): int
    {
        if (!$competition) return 50; // Default medium
        
        $competition = strtoupper(trim($competition));
        
        return match($competition) {
            'L', 'LOW' => 25,
            'M', 'MEDIUM', 'MED' => 50,
            'H', 'HIGH' => 75,
            default => 50,
        };
    }

    /**
     * Detect search intent from keyword
     */
    public function detectIntent(string $keyword): string
    {
        $keyword = strtolower($keyword);
        
        // Transactional indicators (Indonesian + English)
        $transactional = ['beli', 'jual', 'harga', 'murah', 'diskon', 'promo', 'order', 'buy', 'purchase', 'price', 'discount', 'coupon', 'deal', 'shop'];
        foreach ($transactional as $word) {
            if (str_contains($keyword, $word)) {
                return 'transactional';
            }
        }
        
        // Commercial indicators (Indonesian + English)
        $commercial = ['terbaik', 'rekomendasi', 'review', 'ulasan', 'perbandingan', 'vs', 'pilihan', 'best', 'top', 'compare', 'alternative', 'cheap', 'affordable', 'rating'];
        foreach ($commercial as $word) {
            if (str_contains($keyword, $word)) {
                return 'commercial';
            }
        }
        
        // Navigational indicators
        $navigational = ['login', 'sign in', 'official', 'website', 'download', 'app'];
        foreach ($navigational as $word) {
            if (str_contains($keyword, $word)) {
                return 'navigational';
            }
        }
        
        // Informational indicators (Indonesian + English)
        $informational = ['cara', 'apa itu', 'bagaimana', 'mengapa', 'kapan', 'dimana', 'tutorial', 'panduan', 'tips', 'belajar', 'arti', 'how', 'what', 'why', 'when', 'where', 'guide', 'learn', 'meaning', 'adalah'];
        foreach ($informational as $word) {
            if (str_contains($keyword, $word)) {
                return 'informational';
            }
        }
        
        // Default to informational
        return 'informational';
    }

    /**
     * Calculate difficulty score (0-100, higher = harder to rank)
     */
    public function calculateDifficultyScore(int $volume, int $competitionScore): int
    {
        // Base difficulty from competition
        $difficulty = $competitionScore;
        
        // High volume keywords are usually more competitive
        if ($volume > 10000) {
            $difficulty += 20;
        } elseif ($volume > 5000) {
            $difficulty += 15;
        } elseif ($volume > 1000) {
            $difficulty += 10;
        } elseif ($volume > 500) {
            $difficulty += 5;
        }
        
        return min(100, max(0, $difficulty));
    }

    /**
     * Calculate opportunity score (0-100)
     * Formula: (volume / difficulty) normalized
     * Higher volume + lower difficulty = higher opportunity
     */
    public function calculateOpportunityScore(int $volume, int $difficulty): float
    {
        if ($volume <= 0) return 0;
        if ($difficulty <= 0) $difficulty = 1;
        
        // Formula: volume weight / difficulty weight
        $volumeWeight = log10($volume + 1) * 20; // Log scale for volume
        $difficultyPenalty = $difficulty * 0.5;
        
        $score = $volumeWeight - $difficultyPenalty + 50; // Base 50
        
        return round(min(100, max(0, $score)), 1);
    }

    /**
     * Calculate priority score (0-100)
     * Combines volume, competition, intent, and trend
     */
    public function calculatePriorityScore(int $volume, int $competitionScore, string $intent, float $trend = 0): int
    {
        $score = 50; // Base score
        
        // Volume factor (0-30 points)
        if ($volume > 10000) $score += 30;
        elseif ($volume > 5000) $score += 25;
        elseif ($volume > 1000) $score += 20;
        elseif ($volume > 500) $score += 15;
        elseif ($volume > 100) $score += 10;
        elseif ($volume > 0) $score += 5;
        
        // Competition factor (0-15 points, lower is better)
        if ($competitionScore <= 25) $score += 15;
        elseif ($competitionScore <= 50) $score += 10;
        elseif ($competitionScore <= 75) $score += 5;
        
        // Intent factor (0-15 points)
        $score += match($intent) {
            'transactional' => 15,
            'commercial' => 12,
            'informational' => 8,
            'navigational' => 3,
            default => 5,
        };
        
        // Trend bonus (0-10 points)
        if ($trend > 1.5) $score += 10;
        elseif ($trend > 1.0) $score += 5;
        
        return min(100, max(0, $score));
    }

    /**
     * Classify keyword by word count
     */
    public function classifyKeywordType(string $keyword): string
    {
        $wordCount = str_word_count($keyword);
        
        if ($wordCount <= 2) return 'short';
        if ($wordCount <= 4) return 'medium';
        return 'long';
    }

    /**
     * Get recommended action based on keyword metrics
     */
    public function getRecommendedAction(array $keyword): string
    {
        $priority = $keyword['priority_score'] ?? 0;
        $intent = $keyword['intent'] ?? 'informational';
        $difficulty = $keyword['difficulty_score'] ?? 50;
        $volume = $keyword['volume'] ?? 0;
        
        // High priority transactional = pillar page
        if ($priority >= 80 && in_array($intent, ['transactional', 'commercial'])) {
            return 'Create comprehensive buying guide or product comparison';
        }
        
        // High priority informational = pillar content
        if ($priority >= 80 && $intent === 'informational') {
            return 'Build cluster-leading pillar page with subtopics';
        }
        
        // Low difficulty + decent volume = quick win
        if ($difficulty <= 40 && $volume >= 500) {
            return 'Quick win - create targeted content immediately';
        }
        
        // Commercial intent = review/comparison
        if ($intent === 'commercial') {
            return 'Create detailed review or comparison article';
        }
        
        // Default
        return 'Create informational blog post with examples';
    }

    /**
     * Calculate all metrics for a keyword (used when saving to DB)
     * Legacy method for backward compatibility
     */
    public function calculatePriority(array $keyword): int
    {
        $volume = (int)($keyword['volume'] ?? 0);
        $competition = $keyword['competition_level'] ?? $keyword['competition'] ?? null;
        $competitionScore = $this->normalizeCompetitionToScore($competition);
        $intent = $keyword['intent'] ?? $this->detectIntent($keyword['keyword'] ?? '');
        $trend = (float)($keyword['trend'] ?? 0);
        
        return $this->calculatePriorityScore($volume, $competitionScore, $intent, $trend);
    }

    /**
     * Enrich keyword with all calculated metrics
     */
    public function enrichKeyword(array $keyword): array
    {
        $text = $keyword['keyword'] ?? '';
        $volume = (int)($keyword['volume'] ?? 0);
        $competition = $keyword['competition_level'] ?? $keyword['competition'] ?? null;
        $competitionScore = $this->normalizeCompetitionToScore($competition);
        $trend = (float)($keyword['trend'] ?? 0);
        
        $intent = $keyword['intent'] ?? $this->detectIntent($text);
        $difficulty = $this->calculateDifficultyScore($volume, $competitionScore);
        $opportunity = $this->calculateOpportunityScore($volume, $difficulty);
        $priority = $this->calculatePriorityScore($volume, $competitionScore, $intent, $trend);
        
        return array_merge($keyword, [
            'intent' => $intent,
            'competition_level' => $this->normalizeCompetitionLevel($competition),
            'competition_score' => $competitionScore,
            'difficulty_score' => $difficulty,
            'opportunity_score' => $opportunity,
            'priority_score' => $priority,
            'keyword_type' => $this->classifyKeywordType($text),
            'recommended_action' => $this->getRecommendedAction([
                'priority_score' => $priority,
                'intent' => $intent,
                'difficulty_score' => $difficulty,
                'volume' => $volume,
            ]),
        ]);
    }
}
