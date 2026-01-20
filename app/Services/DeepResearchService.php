<?php

namespace App\Services;

use App\Models\DeepResearchSession;
use App\Models\DeepResearchKeyword;
use App\Models\DeepResearchCompetitor;
use App\Models\SavedKeyword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

/**
 * DeepResearchService
 * 
 * Comprehensive keyword research with:
 * - Serper: Autocomplete suggestions
 * - DataForSEO: SERP, PAA, Related Searches
 * - RapidAPI Keyword Insight: Volume, Intent, Priority
 * 
 * Cost: ~$0.01/session
 */
class DeepResearchService
{
    private SerperSearchService $serper;
    private DataForSeoService $dataForSeo;
    private KeywordInsightService $keywordInsight;

    public function __construct(
        SerperSearchService $serper,
        DataForSeoService $dataForSeo,
        KeywordInsightService $keywordInsight
    ) {
        $this->serper = $serper;
        $this->dataForSeo = $dataForSeo;
        $this->keywordInsight = $keywordInsight;
    }

    /**
     * Get autocomplete suggestions
     */
    public function getAutocomplete(string $keyword, string $country = 'id', string $lang = 'id'): array
    {
        try {
            return $this->serper->getAutocompleteSuggestions($keyword, $country, $lang);
        } catch (Exception $e) {
            Log::error("Autocomplete error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Start new research session
     */
    public function startSession(int $websiteId, string $seedKeyword, int $userId, string $country = 'ID', string $language = 'id'): DeepResearchSession
    {
        // Check for existing active session
        $existing = DeepResearchSession::where('website_id', $websiteId)
            ->where('seed_keyword_hash', md5(strtolower(trim($seedKeyword))))
            ->whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '>', now()->subMinutes(10))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DeepResearchSession::create([
            'website_id' => $websiteId,
            'created_by' => $userId,
            'seed_keyword' => $seedKeyword,
            'location' => $country,
            'language' => $language,
            'status' => 'pending',
            'progress' => 0,
            'current_step' => 'Initializing research...',
        ]);
    }

    /**
     * Process research session (run as Job)
     */
    public function processSession(DeepResearchSession $session): void
    {
        try {
            $session->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Get location/language from session
            $location = $session->location ?? 'ID';
            $language = $session->language ?? 'id';

            // Step 1: Get autocomplete (10%)
            $this->updateProgress($session, 10, 'Getting autocomplete suggestions...');
            $autocomplete = $this->getAutocomplete($session->seed_keyword, strtolower($location), $language);
            $session->update(['autocomplete_results' => $autocomplete]);

            // Step 2: SERP + PAA for seed keyword (30%)
            $this->updateProgress($session, 30, 'Analyzing SERP results...');
            $serpData = $this->dataForSeo->getSerpOrganic($session->seed_keyword, $location, $language);
            $session->update([
                'serp_snapshot' => $serpData['organic_results'],
                'paa_data' => $serpData['people_also_ask'],
                'related_searches' => $serpData['related_searches'],
            ]);

            // Step 3: Extract competitors (50%)
            $this->updateProgress($session, 50, 'Identifying competitors...');
            $competitors = $this->dataForSeo->extractCompetitors($serpData);
            $session->update([
                'competitors' => $competitors,
                'total_competitors_found' => count($competitors),
            ]);

            // Save competitors to DB
            foreach ($competitors as $comp) {
                DeepResearchCompetitor::create([
                    'deep_research_id' => $session->id,
                    'competitor_domain' => $comp['domain'],
                    'competitor_type' => 'direct',
                    'appears_in_keywords' => 1,
                    'avg_rank' => $comp['rank'],
                ]);
            }

            // Step 4: Get keyword data from RapidAPI (60%)
            $this->updateProgress($session, 60, 'Getting keyword volume & intent...');
            $keywordData = $this->keywordInsight->getKeywordSuggestions($session->seed_keyword, $location, $language);
            
            // Build lookup map for volume/intent by keyword text (already enriched with all metrics)
            $keywordMap = [];
            foreach ($keywordData as $kd) {
                $hash = md5(strtolower(trim($kd['keyword'])));
                $keywordMap[$hash] = $kd;
            }

            // Step 5: Collect all keywords (70%)
            $this->updateProgress($session, 70, 'Collecting keywords...');
            
            // Prepare keywords from different sources
            $fromAutocomplete = array_map(fn($s) => ['keyword' => $s, 'source' => 'autocomplete'], $autocomplete);
            $fromPaa = array_map(fn($p) => ['keyword' => $p['question'], 'source' => 'paa'], $serpData['people_also_ask']);
            $fromRelated = array_map(fn($r) => ['keyword' => $r, 'source' => 'related'], $serpData['related_searches']);
            
            // Keywords from Keyword Insight already have all metrics (volume, intent, priority, opportunity, difficulty)
            $fromKeywordInsight = array_map(fn($k) => array_merge($k, ['source' => 'keyword_insight']), $keywordData);
            
            $allKeywords = array_merge($fromAutocomplete, $fromPaa, $fromRelated, $fromKeywordInsight);

            // Deduplicate by keyword hash, prefer keyword_insight source (has full metrics)
            $seen = [];
            $uniqueKeywords = [];
            foreach ($allKeywords as $kw) {
                $hash = md5(strtolower(trim($kw['keyword'])));
                if (!isset($seen[$hash])) {
                    $seen[$hash] = true;
                    $uniqueKeywords[] = $kw;
                } elseif (($kw['source'] ?? '') === 'keyword_insight') {
                    // Replace with keyword_insight version (has full metrics)
                    foreach ($uniqueKeywords as $i => $uk) {
                        if (md5(strtolower(trim($uk['keyword']))) === $hash) {
                            $uniqueKeywords[$i] = $kw;
                            break;
                        }
                    }
                }
            }

            // Step 6: Enrich and save keywords with all metrics (80%)
            $this->updateProgress($session, 80, 'Analyzing & saving keyword data...');
            $autoSavedCount = 0;
            
            foreach ($uniqueKeywords as $kw) {
                $hash = md5(strtolower(trim($kw['keyword'])));
                $keywordText = $kw['keyword'];
                $source = $kw['source'] ?? 'manual';
                
                // Keywords from keyword_insight already have all metrics
                if ($source === 'keyword_insight') {
                    $enriched = $kw; // Already has all data from parseKeywordItem()
                } else {
                    // Try to get data from keywordMap (if this keyword was in API results)
                    $enriched = $keywordMap[$hash] ?? null;
                    
                    if (!$enriched) {
                        // Enrich keywords that don't have data (from autocomplete, paa, related)
                        $enriched = $this->keywordInsight->enrichKeyword([
                            'keyword' => $keywordText,
                            'volume' => $kw['volume'] ?? 0,
                            'competition_level' => $kw['competition_level'] ?? $kw['competition'] ?? null,
                            'trend' => $kw['trend'] ?? 0,
                        ]);
                    }
                }
                
                Log::debug("DeepResearch saving keyword: {$keywordText}", [
                    'source' => $source,
                    'volume' => $enriched['volume'] ?? null,
                    'difficulty' => $enriched['difficulty_score'] ?? null,
                    'opportunity' => $enriched['opportunity_score'] ?? null,
                    'priority' => $enriched['priority_score'] ?? null,
                ]);
                
                // Determine auto-save status
                $saveStatus = 'not_saved';
                $saveReason = null;
                $priority = $enriched['priority_score'] ?? 0;
                $difficulty = $enriched['difficulty_score'] ?? 50;
                $volume = $enriched['volume'] ?? 0;
                
                // Auto-save high priority keywords
                if ($priority >= 70) {
                    $saveStatus = 'auto_saved';
                    $saveReason = 'ai_high_priority';
                    $autoSavedCount++;
                } elseif ($difficulty <= 40 && $volume >= 500) {
                    // Low hanging fruit: low difficulty + decent volume
                    $saveStatus = 'auto_saved';
                    $saveReason = 'low_hanging_fruit';
                    $autoSavedCount++;
                }

                DeepResearchKeyword::updateOrCreate(
                    [
                        'deep_research_id' => $session->id,
                        'keyword_hash' => $hash,
                    ],
                    [
                        'keyword' => $keywordText,
                        'source' => $kw['source'],
                        'volume' => $enriched['volume'] ?? null,
                        'intent' => $enriched['intent'] ?? null,
                        'competition_level' => $enriched['competition_level'] ?? null,
                        'competition_index' => $enriched['competition_score'] ?? null,
                        'difficulty_score' => $enriched['difficulty_score'] ?? null,
                        'opportunity_score' => $enriched['opportunity_score'] ?? null,
                        'ai_priority_score' => $enriched['priority_score'] ?? null,
                        'ai_recommended_action' => $enriched['recommended_action'] ?? null,
                        'cpc' => $enriched['cpc'] ?? null,
                        'save_status' => $saveStatus,
                        'save_reason' => $saveReason,
                    ]
                );
            }
            
            $session->update([
                'keywords_count' => count($uniqueKeywords),
                'auto_saved_keywords_count' => $autoSavedCount,
            ]);

            // Step 7: Generate AI Recommendations (90%)
            $this->updateProgress($session, 90, 'AI analyzing keyword strategy...');
            
            try {
                $aiExpansion = app(AIKeywordExpansionService::class);
                $aiRecommendations = $aiExpansion->generateRecommendations(
                    $session->seed_keyword,
                    $uniqueKeywords,
                    $serpData['people_also_ask'] ?? [],
                    $serpData['related_searches'] ?? [],
                    $competitors
                );
                
                $session->update([
                    'ai_recommendations' => $aiRecommendations,
                ]);
                
                Log::info("DeepResearch AI recommendations generated", [
                    'main_targets' => count($aiRecommendations['main_targets'] ?? []),
                    'informational' => count($aiRecommendations['informational'] ?? []),
                ]);
            } catch (Exception $e) {
                Log::warning("AI recommendations failed: " . $e->getMessage());
            }

            // Complete
            $this->updateProgress($session, 95, 'Finalizing research...');
            
            $session->update([
                'status' => 'completed',
                'progress' => 100,
                'current_step' => 'Research completed!',
                'completed_at' => now(),
            ]);

        } catch (Exception $e) {
            Log::error("DeepResearch error: " . $e->getMessage());
            $session->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * AI analysis of keywords
     */
    private function analyzeWithAI(DeepResearchSession $session): array
    {
        $keywords = $session->keywords()->limit(50)->pluck('keyword')->toArray();
        
        $prompt = "Analyze these keywords for SEO strategy:\n" . 
                  "Seed: {$session->seed_keyword}\n" .
                  "Keywords: " . implode(', ', $keywords) . "\n\n" .
                  "Return JSON with:\n" .
                  "- top_opportunities: [{keyword, priority_score, intent, recommendation}]\n" .
                  "- content_gaps: [string]\n" .
                  "- quick_wins: [{keyword, reason}]";

        try {
            $result = $this->ai->generate($prompt, 'article_generation', ['provider' => 'gemini']);
            $response = $result['content'] ?? '';
            return json_decode($response, true) ?? ['raw' => $response];
        } catch (Exception $e) {
            Log::warning("AI analysis failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate content blueprint
     */
    private function generateBlueprint(DeepResearchSession $session): array
    {
        $paa = collect($session->paa_data ?? [])->pluck('question')->take(5)->toArray();
        
        $prompt = "Create content blueprint for: {$session->seed_keyword}\n" .
                  "PAA questions: " . implode(', ', $paa) . "\n\n" .
                  "Return JSON with:\n" .
                  "- title_suggestions: [string]\n" .
                  "- outline: [{heading, subpoints: []}]\n" .
                  "- target_word_count: number";

        try {
            $result = $this->ai->generate($prompt, 'article_generation', ['provider' => 'gemini']);
            $response = $result['content'] ?? '';
            return json_decode($response, true) ?? ['raw' => $response];
        } catch (Exception $e) {
            Log::warning("Blueprint generation failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update session progress
     */
    private function updateProgress(DeepResearchSession $session, int $progress, string $step): void
    {
        $session->update([
            'progress' => $progress,
            'current_step' => $step,
        ]);
    }

    /**
     * Save selected keywords
     */
    public function saveKeywords(DeepResearchSession $session, array $keywordIds, string $reason = 'user_selected'): int
    {
        $saved = 0;
        $keywords = DeepResearchKeyword::whereIn('id', $keywordIds)
            ->where('deep_research_id', $session->id)
            ->get();

        foreach ($keywords as $kw) {
            SavedKeyword::updateOrCreate(
                [
                    'website_id' => $session->website_id,
                    'keyword_hash' => $kw->keyword_hash,
                ],
                [
                    'keyword_text' => $kw->keyword,
                    'search_volume' => $kw->volume,
                    'competition_level' => $kw->competition_level,
                    'intent' => $kw->intent,
                    'opportunity_score' => $kw->opportunity_score,
                    'saved_from' => 'research',
                    'notes' => "From Deep Research: {$session->seed_keyword}",
                    'created_by' => $session->created_by,
                ]
            );
            
            $kw->update(['save_status' => 'user_saved', 'save_reason' => $reason]);
            $saved++;
        }

        return $saved;
    }

    /**
     * Get session status for polling
     */
    public function getStatus(string $sessionId): array
    {
        $session = DeepResearchSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return ['success' => false, 'error' => 'Session not found'];
        }

        return [
            'success' => true,
            'status' => $session->status,
            'progress' => $session->progress,
            'current_step' => $session->current_step,
            'error' => $session->error_message,
        ];
    }

    /**
     * Normalize intent value from various API formats
     * RapidAPI returns: 'I', 'N', 'C', 'T' or full names
     */
    private function normalizeIntent(?string $intent): ?string
    {
        if (!$intent) return null;
        
        $intent = strtoupper(trim($intent));
        
        return match($intent) {
            'I', 'INFORMATIONAL' => 'informational',
            'N', 'NAVIGATIONAL' => 'navigational',
            'C', 'COMMERCIAL' => 'commercial',
            'T', 'TRANSACTIONAL' => 'transactional',
            default => strtolower($intent),
        };
    }

    /**
     * Normalize competition level from various API formats
     */
    private function normalizeCompetition(?string $competition): ?string
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
}
