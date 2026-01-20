<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * AI Keyword Expansion Service
 *
 * Uses AIService (default AI provider) to analyze seed keyword and generate intelligent keyword recommendations
 *
 * Output includes:
 * - Main Targets (2-3 high-priority commercial keywords with suggested titles)
 * - Informational Keywords (10-15 long-tail support keywords)
 * - Global Market Analysis (intent, difficulty, strategic recommendations)
 * - Content Gap Analysis
 * - Competitor Strategy Summary
 */
class AIKeywordExpansionService
{
    private AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate AI-powered keyword recommendations
     *
     * @param string $seedKeyword
     * @param array $keywords Top keywords with metrics
     * @param array $paaQuestions People Also Ask questions
     * @param array $relatedSearches Related searches
     * @param array $competitors Competitor domains
     * @param string|null $language Target language code (e.g., 'id', 'en')
     * @param string|null $location Target country code (e.g., 'ID', 'US')
     * @return array AI recommendations
     */
    public function generateRecommendations(
        string $seedKeyword,
        array $keywords,
        array $paaQuestions = [],
        array $relatedSearches = [],
        array $competitors = [],
        ?string $language = null,
        ?string $location = null
    ): array {
        try {
            Log::info("[AIKeywordExpansion] Generating recommendations for: {$seedKeyword}");

            // Prepare analysis data
            $analysisData = $this->prepareAnalysisData(
                $seedKeyword,
                $keywords,
                $paaQuestions,
                $relatedSearches,
                $competitors
            );

            // Build AI prompt with language context
            $prompt = $this->buildPrompt($seedKeyword, $analysisData, $language, $location);

            // Call AI service (uses default AI from database)
            $result = $this->aiService->generate($prompt, 'article_generation', [
                'max_tokens' => 4096,
                'temperature' => 0.8,
            ]);
            
            $content = $result['content'] ?? '';
            
            // Parse JSON from response
            $response = $this->parseJsonResponse($content);

            if (!$response) {
                Log::warning("[AIKeywordExpansion] AI returned empty/invalid response, using fallback");
                return $this->generateFallbackRecommendations($keywords, $language);
            }

            Log::info("[AIKeywordExpansion] Generated " . count($response['main_targets'] ?? []) . " main targets");

            return $response;

        } catch (Exception $e) {
            Log::error("[AIKeywordExpansion] Error: " . $e->getMessage());
            return $this->generateFallbackRecommendations($keywords, $language);
        }
    }
    
    /**
     * Parse JSON from AI response (handles markdown code blocks)
     */
    private function parseJsonResponse(string $content): ?array
    {
        // Remove markdown code blocks if present
        $content = trim($content);
        
        // Remove ```json ... ``` wrapper
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }
        
        // Try to parse JSON
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("[AIKeywordExpansion] JSON parse error: " . json_last_error_msg());
            Log::debug("[AIKeywordExpansion] Raw content: " . substr($content, 0, 500));
            return null;
        }
        
        return $data;
    }

    /**
     * Prepare structured data for AI analysis
     */
    private function prepareAnalysisData(
        string $seedKeyword,
        array $keywords,
        array $paa,
        array $related,
        array $competitors
    ): array {
        // Top 10 keywords by priority
        $topKeywords = array_slice($keywords, 0, 10);
        $keywordSummary = array_map(function ($kw) {
            return [
                'keyword' => $kw['keyword'] ?? $kw->keyword ?? '',
                'volume' => $kw['volume'] ?? $kw->volume ?? 0,
                'competition' => $kw['competition_level'] ?? $kw->competition_level ?? 'unknown',
                'priority_score' => $kw['ai_priority_score'] ?? $kw->ai_priority_score ?? 0,
                'intent' => $kw['intent'] ?? $kw->intent ?? 'unknown',
            ];
        }, $topKeywords);

        // PAA questions (top 10)
        $paaInsights = array_slice($paa, 0, 10);
        $paaFormatted = array_map(function ($item) {
            if (is_array($item) && isset($item['question'])) {
                return $item['question'];
            }
            return is_string($item) ? $item : '';
        }, $paaInsights);

        // Related searches (top 10)
        $relatedInsights = array_slice($related, 0, 10);

        // Competitor domains
        $competitorDomains = array_slice(array_map(function ($comp) {
            return $comp['competitor_domain'] ?? $comp->competitor_domain ?? $comp['domain'] ?? '';
        }, $competitors), 0, 5);

        return [
            'seed_keyword' => $seedKeyword,
            'top_keywords' => $keywordSummary,
            'paa_questions' => array_filter($paaFormatted),
            'related_searches' => $relatedInsights,
            'competitor_domains' => array_filter($competitorDomains),
            'total_keywords_found' => count($keywords),
        ];
    }

    /**
     * Build AI prompt for keyword recommendations
     */
    private function buildPrompt(string $seedKeyword, array $data, ?string $language = null, ?string $location = null): string
    {
        $topKeywordsJson = json_encode($data['top_keywords'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $paaJson = json_encode($data['paa_questions'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $relatedJson = json_encode($data['related_searches'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $competitorsJson = json_encode($data['competitor_domains'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Determine language instruction
        $languageMap = [
            'id' => 'Indonesian (Bahasa Indonesia)',
            'en' => 'English',
            'en_US' => 'English (US)',
            'en_GB' => 'English (UK)',
        ];
        $targetLanguage = $languageMap[$language] ?? 'Indonesian (Bahasa Indonesia)';
        $targetLocation = $location ?? 'ID';

        // Language-specific instruction block
        $languageInstruction = <<<LANG

**CRITICAL LANGUAGE REQUIREMENT:**
- Target Language: {$targetLanguage}
- Target Country: {$targetLocation}
- ALL keyword recommendations, article titles, and analysis MUST be in {$targetLanguage}
- Do NOT mix languages (e.g., do NOT use "Complete Guide" in Indonesian content)
- Use natural, native {$targetLanguage} phrases and structure
- Suggested article titles should follow {$targetLanguage} content marketing best practices

LANG;

        return <<<PROMPT
You are an expert SEO strategist specializing in keyword research and content strategy.

**SEED KEYWORD:** "{$seedKeyword}"
{$languageInstruction}

**ANALYSIS DATA:**

1. **Top Keywords Found (with metrics):**
{$topKeywordsJson}

2. **People Also Ask Questions:**
{$paaJson}

3. **Related Searches:**
{$relatedJson}

4. **Competitor Domains:**
{$competitorsJson}

**YOUR TASK:**
Analyze the data above and generate smart keyword recommendations for content strategy.

**CATEGORIZE INTO 2 GROUPS:**

1. **Main Targets (2-3 keywords only):**
   - High commercial intent or conversion potential
   - Balance between search volume and competition
   - Should be primary focus keywords for main content
   - **CRITICAL FOR ARTICLE TITLES:**
     * First, deeply analyze the REAL user intent behind the keyword
     * Research what users ACTUALLY want when searching this keyword
     * Study the keyword semantics, context, and searcher's problem
     * Generate 3 article titles that DIRECTLY answer the user's search intent
     * Titles must be SEMANTICALLY RELEVANT - not just pattern-based templates
     * Think: "If I search this keyword, what content would ACTUALLY help me?"
     * Example: For "wahabi dan salafi" → User wants to understand the DIFFERENCE/COMPARISON, not generic guides

2. **Informational Keywords (10-15 keywords):**
   - Long-tail keywords (4+ words preferred)
   - Question-based or how-to keywords
   - Support keywords from PAA and Related Searches
   - Lower competition, specific user intent

**REAL-WORLD TITLE EXAMPLES (Learn from these):**

Keyword: "wahabi dan salafi"
❌ WRONG Pattern-Based:
- "Panduan Lengkap wahabi dan salafi: Tips Terbaik dan Terbaru"
- "wahabi dan salafi: Semua yang Perlu Anda Ketahui"

✅ CORRECT Intent-Based:
- "Perbedaan Wahabi dan Salafi: Sejarah, Ajaran, dan Praktiknya"
- "Wahabi dan Salafi: Apakah Sama atau Berbeda? Analisis Mendalam"
- "Memahami Wahabi dan Salafi: Persamaan, Perbedaan, dan Kontroversinya"

Keyword: "kopi arabika vs robusta"
❌ WRONG:
- "Cara Memilih kopi arabika vs robusta Terbaik: Panduan Praktis"

✅ CORRECT:
- "Perbedaan Kopi Arabika dan Robusta: Rasa, Harga, dan Kualitas"
- "Arabika vs Robusta: Mana yang Lebih Baik untuk Espresso?"
- "Perbandingan Lengkap Kopi Arabika dan Robusta untuk Pemula"

**OUTPUT FORMAT (STRICT JSON - ALL FIELDS REQUIRED):**
{
  "main_targets": [
    {
      "keyword": "example main keyword",
      "reason": "Why this is a main target (1 sentence)",
      "priority_score": 85,
      "volume": 2900,
      "competition": "medium",
      "intent": "commercial",
      "suggested_titles": [
        "Title 1: MUST directly address the searcher's real problem/question",
        "Title 2: MUST match the semantic context of what user ACTUALLY needs",
        "Title 3: MUST be specific and relevant, NOT generic templates"
      ]
    }
  ],
  "informational": [
    {
      "keyword": "example informational keyword",
      "reason": "Why this supports the strategy",
      "category": "how-to",
      "volume": 480,
      "intent": "informational"
    }
  ],
  "strategy_summary": "2-3 sentences explaining the overall content strategy",
  "content_gap": "2-3 sentences explaining what competitors are NOT covering - this is our opportunity",
  "competitor_strategy": "1-2 sentences summarizing competitor approach",
  "global_analysis": {
    "primary_intent": "Main user intent (e.g., 'Commercial Investigation + Informational')",
    "market_difficulty": "Difficulty level with score (e.g., 'Medium - avg competition 55/100')",
    "recommendation_summary": "Strategic recommendation (2-3 sentences)"
  },
  "recommended_formats": ["How-to Guide", "Tutorial", "FAQ", "Comparison", "Review", "Listicle"]
}

**CRITICAL REQUIREMENTS FOR ARTICLE TITLES:**
1. **SEMANTIC ANALYSIS FIRST:** Before generating titles, analyze:
   - What problem does the searcher have?
   - What information are they seeking?
   - What would make them CLICK and READ?

2. **TITLE QUALITY RULES:**
   - ❌ WRONG: Generic templates like "Panduan Lengkap {keyword}"
   - ✅ RIGHT: Specific, intent-matching titles based on deep keyword understanding
   - Example for "wahabi dan salafi":
     * ❌ BAD: "Panduan Lengkap wahabi dan salafi: Tips Terbaik"
     * ✅ GOOD: "Perbedaan Wahabi dan Salafi: Sejarah, Ajaran, dan Praktiknya"
     * ✅ GOOD: "Wahabi dan Salafi: Apakah Sama atau Berbeda? Penjelasan Lengkap"

3. **MANDATORY CHECKS:**
   - ALL fields are MANDATORY - do not omit any field
   - Generate realistic volume estimates if not available
   - Titles MUST solve the searcher's actual problem
   - Return ONLY valid JSON, no markdown, no explanation
   - Think like an SEO content strategist who understands user psychology
PROMPT;
    }

    /**
     * Generate fallback recommendations when AI fails
     */
    private function generateFallbackRecommendations(array $keywords, ?string $language = null): array
    {
        $mainTargets = [];
        $informational = [];

        // Title templates based on language
        $titleTemplates = $this->getTitleTemplates($language);

        $sorted = collect($keywords)->sortByDesc(function ($kw) {
            return $kw['ai_priority_score'] ?? $kw->ai_priority_score ?? 0;
        })->values();

        // Top 3 as main targets
        foreach ($sorted->take(3) as $kw) {
            $keyword = $kw['keyword'] ?? $kw->keyword ?? '';
            $mainTargets[] = [
                'keyword' => $keyword,
                'reason' => 'High priority score based on volume and competition analysis',
                'priority_score' => $kw['ai_priority_score'] ?? $kw->ai_priority_score ?? 70,
                'volume' => $kw['volume'] ?? $kw->volume ?? 0,
                'competition' => $kw['competition_level'] ?? $kw->competition_level ?? 'medium',
                'intent' => $kw['intent'] ?? $kw->intent ?? 'commercial',
                'suggested_titles' => [
                    str_replace('{keyword}', $keyword, $titleTemplates[0]),
                    str_replace('{keyword}', $keyword, $titleTemplates[1]),
                    str_replace('{keyword}', $keyword, $titleTemplates[2]),
                ],
            ];
        }

        // Rest as informational
        foreach ($sorted->skip(3)->take(10) as $kw) {
            $informational[] = [
                'keyword' => $kw['keyword'] ?? $kw->keyword ?? '',
                'reason' => 'Supporting long-tail keyword',
                'category' => 'guide',
                'volume' => $kw['volume'] ?? $kw->volume ?? 0,
                'intent' => $kw['intent'] ?? $kw->intent ?? 'informational',
            ];
        }

        return [
            'main_targets' => $mainTargets,
            'informational' => $informational,
            'strategy_summary' => 'Focus on creating comprehensive content around the main target keywords while building topic authority through informational supporting content.',
            'content_gap' => 'Analysis suggests opportunities in detailed how-to guides and comparison content that competitors may not be fully covering.',
            'competitor_strategy' => 'Competitors appear to focus on general content. Differentiate with in-depth, actionable guides.',
            'global_analysis' => [
                'primary_intent' => 'Mixed (Commercial + Informational)',
                'market_difficulty' => 'Medium - avg competition 50/100',
                'recommendation_summary' => 'Build authority with informational content first, then target commercial keywords. Focus on long-tail variations for quick wins.',
            ],
            'recommended_formats' => ['How-to Guide', 'Tutorial', 'FAQ', 'Listicle'],
        ];
    }

    /**
     * Get language-specific title templates
     */
    private function getTitleTemplates(?string $language): array
    {
        $templates = [
            'id' => [
                'Panduan Lengkap {keyword}: Tips Terbaik dan Terbaru',
                '{keyword}: Semua yang Perlu Anda Ketahui',
                'Cara Memilih {keyword} Terbaik: Panduan Praktis',
            ],
            'en' => [
                'Complete Guide to {keyword}',
                '{keyword}: Everything You Need to Know',
                'Best {keyword} Tips & Strategies',
            ],
        ];

        // Default to Indonesian if language not found
        return $templates[$language] ?? $templates['id'];
    }
}
