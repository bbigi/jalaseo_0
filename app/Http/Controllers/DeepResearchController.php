<?php

namespace App\Http\Controllers;

use App\Models\DeepResearchSession;
use App\Models\Website;
use App\Models\AiGenerateTemplate;
use App\Models\Post;
use App\Models\Insight;
use App\Services\DeepResearchService;
use App\Services\DataForSeoService;
use App\Services\DeepResearchPromptBuilder;
use App\Services\AIService;
use App\Jobs\ProcessDeepResearch;
use App\Helpers\CountryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeepResearchController extends Controller
{
    public function __construct(
        private DeepResearchService $service,
        private DataForSeoService $dataForSeo
    ) {}

    /**
     * Index - Dashboard
     */
    public function index()
    {
        $userId = auth()->id();
        $userRole = auth()->user()->role ?? 'author';

        $websites = $userRole === 'super_admin' 
            ? Website::orderBy('name')->get()
            : Website::where('created_by', $userId)->get();

        $sessions = DeepResearchSession::with('website')
            ->when($userRole !== 'super_admin', fn($q) => $q->where('created_by', $userId))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        
        $isConfigured = $this->dataForSeo->isConfigured();
        $countries = CountryHelper::getCountries();
        $languages = CountryHelper::getLanguages();
        
        return view('deep-research.index', compact('websites', 'sessions', 'isConfigured', 'countries', 'languages'));
    }

    /**
     * Start research (AJAX)
     */
    public function start(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:ane_websites,id',
            'seed_keyword' => 'required|string|max:255',
            'location' => 'nullable|string|max:5',
            'language' => 'nullable|string|max:10',
        ]);

        $this->authorizeWebsite((int) $request->website_id);

        // Extract language code (e.g., 'id_ID' -> 'id', 'en_US' -> 'en')
        $langCode = $request->language ? explode('_', $request->language)[0] : 'id';
        $locationCode = $request->location ?? 'ID';

        try {
            $session = $this->service->startSession(
                websiteId: $request->website_id,
                seedKeyword: $request->seed_keyword,
                userId: auth()->id(),
                country: $locationCode,
                language: $langCode
            );

            // Dispatch job if new session
            if ($session->status === 'pending') {
                ProcessDeepResearch::dispatch($session);
            }

            return response()->json([
                'success' => true,
                'session_id' => $session->session_id,
                'is_existing' => $session->status === 'processing',
                'progress' => $session->progress,
            ]);
        } catch (\Exception $e) {
            Log::error("DeepResearch start error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get session status (polling)
     */
    public function status(string $sessionId)
    {
        $session = $this->findSessionForUser($sessionId);

        return response()->json($this->service->getStatus($session->session_id));
    }

    /**
     * Autocomplete suggestions (AJAX)
     */
    public function autocomplete(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $country = $request->input('gl', 'id');
        $lang = $request->input('hl', 'id');

        if (empty($keyword)) {
            return response()->json(['success' => false, 'error' => 'Keyword required']);
        }

        $suggestions = $this->service->getAutocomplete($keyword, $country, $lang);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
            'count' => count($suggestions),
        ]);
    }

    /**
     * Show session results
     */
    public function show(string $sessionId)
    {
        $session = $this->findSessionForUser($sessionId, ['keywords', 'competitorAnalysis', 'website']);

        // Get user's AI templates for article generation
        $templates = AiGenerateTemplate::where('created_by', auth()->id())
            ->orderBy('name')
            ->get();

        // Get user's websites for keyword saving
        $websites = \App\Models\Website::where('created_by', auth()->id())
            ->orderBy('name')
            ->get();

        return view('deep-research.show', compact('session', 'templates', 'websites'));
    }

    /**
     * Save selected keywords
     */
    public function saveKeywords(Request $request, string $sessionId)
    {
        $request->validate([
            'keyword_ids' => 'required|array',
            'keyword_ids.*' => 'integer',
            'reason' => 'nullable|string',
        ]);

        $session = $this->findSessionForUser($sessionId);
        
        $saved = $this->service->saveKeywords(
            $session, 
            $request->keyword_ids, 
            $request->reason ?? 'user_selected'
        );

        return response()->json([
            'success' => true,
            'saved_count' => $saved,
        ]);
    }

    /**
     * Delete session
     */
    public function destroy(string $sessionId)
    {
        $session = $this->findSessionForUser($sessionId);

        $session->delete();

        return redirect()->route('deep-research.index')
            ->with('success', 'Research session deleted.');
    }

    /**
     * Re-enrich keywords with calculated scores (AJAX)
     */
    public function reEnrich(string $sessionId)
    {
        $session = $this->findSessionForUser($sessionId, ['keywords']);
        
        $keywordInsight = app(\App\Services\KeywordInsightService::class);
        $updated = 0;
        
        foreach ($session->keywords as $keyword) {
            // Enrich with calculated scores
            $enriched = $keywordInsight->enrichKeyword([
                'keyword' => $keyword->keyword,
                'volume' => $keyword->volume ?? 0,
                'competition_level' => $keyword->competition_level,
                'trend' => 0,
            ]);
            
            $keyword->update([
                'intent' => $enriched['intent'] ?? $keyword->intent,
                'difficulty_score' => $enriched['difficulty_score'],
                'opportunity_score' => $enriched['opportunity_score'],
                'ai_priority_score' => $enriched['priority_score'],
                'ai_recommended_action' => $enriched['recommended_action'] ?? null,
            ]);
            
            $updated++;
        }
        
        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => "Re-enriched {$updated} keywords with scores.",
        ]);
    }

    /**
     * Regenerate AI Recommendations (AJAX)
     */
    public function regenerateAI(string $sessionId)
    {
        // Increase timeout for AI generation (60 seconds)
        set_time_limit(60);

        $session = $this->findSessionForUser($sessionId, ['keywords', 'competitorAnalysis']);

        try {
            \Log::info("[RegenerateAI] Starting for session: {$sessionId}");

            $aiExpansion = app(\App\Services\AIKeywordExpansionService::class);

            // Prepare data
            $keywords = $session->keywords->map(function ($kw) {
                return [
                    'keyword' => $kw->keyword,
                    'volume' => $kw->volume,
                    'competition_level' => $kw->competition_level,
                    'ai_priority_score' => $kw->ai_priority_score,
                    'intent' => $kw->intent,
                ];
            })->toArray();

            $paa = $session->paa_data ?? [];
            $related = $session->related_searches ?? [];
            $competitors = $session->competitorAnalysis->toArray();

            \Log::info("[RegenerateAI] Calling AI with " . count($keywords) . " keywords");

            // Generate AI recommendations with language context
            $aiRecommendations = $aiExpansion->generateRecommendations(
                $session->seed_keyword,
                $keywords,
                $paa,
                $related,
                $competitors,
                $session->language,  // Pass language from session
                $session->location   // Pass location from session
            );

            \Log::info("[RegenerateAI] AI returned " . count($aiRecommendations['main_targets'] ?? []) . " main targets");
            
            // Save to session
            $session->update([
                'ai_recommendations' => $aiRecommendations,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'AI recommendations regenerated successfully!',
                'data' => [
                    'main_targets' => count($aiRecommendations['main_targets'] ?? []),
                    'informational' => count($aiRecommendations['informational'] ?? []),
                ],
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Regenerate AI error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show article generation progress page
     */
    public function generateProgress(string $sessionId)
    {
        $session = $this->findSessionForUser($sessionId);
        
        // Get data from session
        $data = session('deep_research_generate_data');
        
        if (!$data || $data['session_id'] !== $sessionId) {
            return redirect()->route('deep-research.show', $sessionId)
                ->with('error', 'No generation data found. Please try again.');
        }
        
        // Get template
        $template = AiGenerateTemplate::findOrFail($data['template_id']);
        
        return view('deep-research.generate-progress', [
            'session' => $session,
            'template' => $template,
            'keyword' => $data['keyword'],
            'title' => $data['title'],
            'brandContext' => $data['brand_context'] ?? '',
            'additionalNotes' => $data['additional_notes'] ?? '',
        ]);
    }

    /**
     * Generate article from Deep Research (AJAX)
     */
    public function generateArticle(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'template_id' => 'required|integer|exists:ane_ai_generate_templates,id',
            'keyword' => 'required|string|max:255',
            'title' => 'required|string|max:500',
            'brand_context' => 'nullable|string|max:1000',
            'additional_notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = auth()->id();
            
            // Get session
            $session = $this->findSessionForUser($validated['session_id']);
            
            // Get template
            $template = AiGenerateTemplate::findOrFail($validated['template_id']);
            
            if ($template->created_by !== $userId) {
                throw new \Exception('Unauthorized template access');
            }
            
            // Build prompt using DeepResearchPromptBuilder
            $promptBuilder = new DeepResearchPromptBuilder();

            $context = [
                'keyword' => $validated['keyword'],
                'title' => $validated['title'],
                'seed_keyword' => $session->seed_keyword,
                'ai_recommendations' => $session->ai_recommendations ?? [],
                'brand_context' => $validated['brand_context'] ?? '',
                'additional_notes' => $validated['additional_notes'] ?? '',
                'research_language' => $session->language ?? null, // Language from Deep Research
                'research_location' => $session->location ?? null, // Target country from Deep Research
            ];

            $prompt = $promptBuilder->build($context, $template);
            
            // Call AI Service
            $aiService = new AIService();
            
            $result = $aiService->generate($prompt, 'article_generation', [
                'provider' => $template->ai_provider ?? 'gemini',
                'model' => $template->ai_model ?? null,
                'max_tokens' => $promptBuilder->getMaxTokens($template->article_size),
                'temperature' => 0.7,
            ]);
            
            $content = $result['content'] ?? '';
            
            // Parse JSON response
            $articleData = $this->parseArticleResponse($content);

            if (!$articleData) {
                throw new \Exception('Failed to parse AI response');
            }

            // Enforce title and keywords from Deep Research (AI sometimes ignores instructions)
            $articleData = $this->enforceDeepResearchData($articleData, $validated, $session);

            // Create insight for reference
            $insight = Insight::create([
                'topic' => $validated['keyword'],
                'description' => 'Generated from Deep Research: ' . $session->seed_keyword,
                'source' => 'deep_research',
                'source_url' => route('deep-research.show', $session->session_id),
                'source_type' => 'deep_research',
                'status' => 'in_progress',
                'article_count' => 1,
                'created_by' => $userId,
                'website_id' => $session->website_id,
            ]);
            
            // Extract and validate tags
            $tags = $articleData['tags'] ?? [];
            if (!empty($tags) && $validated['keyword']) {
                // Ensure focus keyword is in tags (case-insensitive check)
                $focusKeywordLower = strtolower($validated['keyword']);
                $tagsLower = array_map('strtolower', $tags);

                if (!in_array($focusKeywordLower, $tagsLower)) {
                    // Add focus keyword as first tag if missing
                    array_unshift($tags, $validated['keyword']);
                    Log::info('DeepResearch: Focus keyword added to tags', ['focus_keyword' => $validated['keyword']]);
                }
            }

            // Create post
            $post = Post::create([
                'title' => $articleData['title'] ?? $validated['title'],
                'content' => $articleData['content'] ?? '',
                'article_type' => 'blog',
                'source_type' => 'ai_generated',
                'seo_focus_keyword' => $validated['keyword'], // Primary keyword from Deep Research
                'seo_meta_description' => $articleData['meta_description'] ?? '',
                'ai_generated_tags' => $tags,
                'created_by' => $userId,
                'insight_id' => $insight->id,
            ]);
            
            // Increment user quota
            auth()->user()->increment('api_quota_used');
            
            DB::commit();
            
            // Clear session data
            session()->forget('deep_research_generate_data');
            
            return response()->json([
                'success' => true,
                'post_id' => $post->id,
                'title' => $post->title,
                'word_count' => str_word_count(strip_tags($post->content)),
                'redirect_url' => route('posts.edit', $post->id),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[DeepResearch Generate] Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Parse article JSON response from AI
     */
    private function parseArticleResponse(string $content): ?array
    {
        $originalContent = $content;
        $content = trim($content);

        Log::info("[DeepResearch] Parsing AI response, length: " . strlen($content));

        // Remove markdown code blocks if present (```json ... ``` or ``` ... ```)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = trim($matches[1]);
            Log::info("[DeepResearch] Extracted from markdown block");
        }

        // Try to find JSON object in the response
        if (!str_starts_with($content, '{')) {
            // Find first { and last }
            $start = strpos($content, '{');
            $end = strrpos($content, '}');

            if ($start !== false && $end !== false && $end > $start) {
                $content = substr($content, $start, $end - $start + 1);
                Log::info("[DeepResearch] Extracted JSON from position {$start} to {$end}");
            }
        }

        // CRITICAL: Clean control characters BEFORE first parse attempt
        // DeepSeek and other providers may return invisible control characters
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("[DeepResearch] JSON parse error (attempt 1): " . json_last_error_msg());

            // Attempt 2: Fix common JSON issues from AI responses
            $fixed = $this->fixMalformedJson($content);
            $data = json_decode($fixed, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("[DeepResearch] JSON parse error (attempt 2): " . json_last_error_msg());
                Log::warning("[DeepResearch] Raw content (first 1000 chars): " . substr($originalContent, 0, 1000));

                // Attempt 3: Extract content manually as fallback
                $fallback = $this->extractContentFallback($originalContent);
                if ($fallback) {
                    Log::info("[DeepResearch] Using fallback extraction");
                    return $fallback;
                }

                return null;
            } else {
                Log::info("[DeepResearch] JSON fixed and parsed successfully on attempt 2");
            }
        }
        
        // Validate required fields
        if (empty($data['content'])) {
            Log::warning("[DeepResearch] Parsed JSON but missing 'content' field");
            Log::warning("[DeepResearch] Keys found: " . implode(', ', array_keys($data)));
        }
        
        return $data;
    }
    
    /**
     * Fix common malformed JSON issues from AI responses
     *
     * AI often returns JSON with unescaped newlines in HTML content.
     * This method attempts to fix these issues by properly escaping string values.
     */
    private function fixMalformedJson(string $json): string
    {
        // Strategy: Clean control characters and escape newlines/tabs in string values

        // Step 1: Remove/escape control characters (0x00-0x1F except \t, \n, \r)
        // These cause "Control character error" in JSON parsing
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);

        // Step 2: Fix newlines/tabs inside string values using regex
        $fixed = preg_replace_callback(
            '/"(title|content|meta_description)"\s*:\s*"(.*?)(?<!\\\\)"/s',
            function ($matches) {
                $key = $matches[1];
                $value = $matches[2];

                // Escape literal newlines and tabs for JSON
                $value = str_replace(["\r\n", "\r", "\n"], ["\\n", "\\n", "\\n"], $value);
                $value = str_replace("\t", "\\t", $value);

                return "\"{$key}\": \"{$value}\"";
            },
            $json
        );

        return $fixed ?? $json;
    }

    /**
     * Fallback content extraction when JSON parsing fails
     *
     * Attempts to extract title and content from malformed JSON using regex
     */
    private function extractContentFallback(string $raw): ?array
    {
        Log::info("[DeepResearch] Attempting fallback extraction");

        // Clean control characters first
        $raw = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $raw);

        // Try to extract title - match any characters including escaped quotes
        $title = null;
        if (preg_match('/"title"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $m)) {
            $title = $m[1];
            // Unescape the title
            $title = str_replace(['\\"', '\\n', '\\t'], ['"', ' ', ' '], $title);
        }

        // Try to extract content - match everything between "content":" and the next ",
        // This is very lenient and handles multi-line content
        $content = null;
        if (preg_match('/"content"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"\s*[,}]/s', $raw, $m)) {
            $content = $m[1];

            // Unescape common JSON escapes
            $content = str_replace(['\\n', '\\"', '\\/', '\\t'], ["\n", '"', '/', "\t"], $content);

            Log::info("[DeepResearch] Fallback extracted content length: " . strlen($content));
        }

        // Extract meta_description if present
        $metaDesc = '';
        if (preg_match('/"meta_description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $m)) {
            $metaDesc = $m[1];
            $metaDesc = str_replace(['\\"', '\\n'], ['"', ' '], $metaDesc);
        }

        // Extract keywords array if present
        $keywords = [];
        if (preg_match('/"keywords"\s*:\s*\[(.*?)\]/s', $raw, $m)) {
            $keywordsStr = $m[1];
            // Extract individual keywords
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $keywordsStr, $matches)) {
                $keywords = array_map(function($kw) {
                    return str_replace(['\\"', '\\n'], ['"', ' '], $kw);
                }, $matches[1]);
            }
        }

        if ($title && $content) {
            Log::info("[DeepResearch] Fallback extraction successful");
            return [
                'title' => $title,
                'content' => $content,
                'meta_description' => $metaDesc,
                'keywords' => $keywords ?: [],
            ];
        }

        Log::warning("[DeepResearch] Fallback extraction failed - title or content missing");
        Log::warning("[DeepResearch] Title found: " . ($title ? 'yes' : 'no') . ", Content found: " . ($content ? 'yes' : 'no'));
        return null;
    }

    /**
     * Enforce Deep Research data (title and keywords) from AI recommendations
     *
     * AI sometimes ignores instructions, so we force the correct values here.
     */
    private function enforceDeepResearchData(array $articleData, array $validated, $session): array
    {
        // Force exact title from validated input (suggested title from Deep Research)
        $articleData['title'] = $validated['title'];

        // Build keywords array: primary keyword + supporting keywords from informational
        $primaryKeyword = $validated['keyword'];
        $keywords = [$primaryKeyword]; // Primary always first

        // Extract informational keywords from AI recommendations
        $aiRecommendations = $session->ai_recommendations ?? [];
        $informational = $aiRecommendations['informational'] ?? [];

        // Add up to 4 supporting keywords
        $supportingCount = 0;
        foreach ($informational as $item) {
            if ($supportingCount >= 4) break;

            $keyword = is_array($item) ? ($item['keyword'] ?? null) : $item;

            if ($keyword && !in_array($keyword, $keywords)) {
                $keywords[] = $keyword;
                $supportingCount++;
            }
        }

        $articleData['keywords'] = $keywords;

        // Validate meta description includes primary keyword
        $metaDesc = $articleData['meta_description'] ?? '';
        if (!empty($metaDesc) && stripos($metaDesc, $primaryKeyword) === false) {
            // If meta description doesn't include keyword, prepend it naturally
            $articleData['meta_description'] = "Panduan lengkap {$primaryKeyword}: " . $metaDesc;
            Log::info("[DeepResearch] Meta description updated to include primary keyword");
        }

        Log::info("[DeepResearch] Enforced title and keywords", [
            'title' => $articleData['title'],
            'keywords_count' => count($keywords),
            'primary_keyword' => $primaryKeyword,
        ]);

        return $articleData;
    }

    /**
     * Store generation data in session and redirect to progress
     */
    public function prepareGenerate(Request $request, string $sessionId)
    {
        $validated = $request->validate([
            'template_id' => 'required|integer|exists:ane_ai_generate_templates,id',
            'keyword' => 'required|string|max:255',
            'title' => 'required|string|max:500',
            'brand_context' => 'nullable|string|max:1000',
            'additional_notes' => 'nullable|string|max:1000',
        ]);
        
        $this->findSessionForUser($sessionId);

        // Store in session
        session([
            'deep_research_generate_data' => [
                'session_id' => $sessionId,
                'template_id' => $validated['template_id'],
                'keyword' => $validated['keyword'],
                'title' => $validated['title'],
                'brand_context' => $validated['brand_context'] ?? '',
                'additional_notes' => $validated['additional_notes'] ?? '',
            ]
        ]);
        
        return redirect()->route('deep-research.generate-progress', $sessionId);
    }

    /**
     * Find a session visible to the authenticated user (super_admin bypasses).
     */
    private function findSessionForUser(string $sessionId, array $with = [])
    {
        return DeepResearchSession::with($with)
            ->where('session_id', $sessionId)
            ->visibleTo(auth()->user())
            ->firstOrFail();
    }

    /**
     * Ensure the current user can manage a website (owner or super admin).
     */
    private function authorizeWebsite(int $websiteId): void
    {
        $user = auth()->user();
        if (($user->role ?? 'user') === 'super_admin') {
            return;
        }

        $ownsWebsite = Website::where('id', $websiteId)
            ->where('created_by', $user->id)
            ->exists();

        abort_unless($ownsWebsite, 403);
    }
}
