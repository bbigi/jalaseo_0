<?php

namespace App\Http\Controllers;

use App\Models\SocialSearch;
use App\Models\SocialResult;
use App\Services\SocialInsightService;
use App\Helpers\CountryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialInsightController extends Controller
{
    private $socialService;

    public function __construct()
    {
        $this->socialService = new SocialInsightService();
    }

    /**
     * Show social insight search page
     * GET /insights/social
     */
    public function index()
    {
        $userId = auth()->id();

        // Get stats
        $stats = $this->socialService->getStats($userId);

        // Get recent searches with results count
        $searches = SocialSearch::where('created_by', $userId)
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('insights.social.index', [
            'stats' => $stats,
            'searches' => $searches,
        ]);
    }

    /**
     * Show search form
     * GET /insights/social/create
     */
    public function create()
    {
        $userId = auth()->id();

        // Get stats
        $stats = $this->socialService->getStats($userId);

        // Platform options
        $platforms = [
            [
                'id' => 'tiktok',
                'name' => 'TikTok',
                'description' => 'Trending videos from TikTok',
                'icon' => 'musical-note'
            ],
            [
                'id' => 'twitter',
                'name' => 'Twitter/X',
                'description' => 'Trending topics on Twitter',
                'icon' => 'chat-bubble-left-right'
            ],
            [
                'id' => 'google_trends',
                'name' => 'Google Trends',
                'description' => 'Trending searches on Google',
                'icon' => 'chart-bar'
            ],
        ];

        // Country & language options (single source of truth)
        $countries = CountryHelper::getCountries();
        $languages = CountryHelper::getLanguages();

        return view('insights.social.create', [
            'stats' => $stats,
            'platforms' => $platforms,
            'countries' => $countries,
            'languages' => $languages,
        ]);
    }

    /**
     * Search social media trending topics
     * POST /insights/social/search
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:tiktok,twitter,google_trends',
            'keyword' => 'nullable|string|max:255',
            'country_code' => 'required|string|max:5',
            'language' => 'nullable|string|max:2',
        ]);

        try {
            $searchData = $this->socialService->performSearch(
                auth()->id(),
                $validated['platform'],
                $validated['keyword'] ?? null,
                $validated['country_code'],
                $validated['language'] ?? 'id'
            );

            return redirect()->route('insights.social.results', ['id' => $searchData['search_id']])
                ->with('success', 'Pencarian berhasil! Ditemukan ' . $searchData['results_count'] . ' hasil trending.');

        } catch (\Exception $e) {
            Log::error('Social search error: ' . $e->getMessage());
            return redirect()->route('insights.social.create')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show search results
     * GET /insights/social/results/{id}
     */
    public function results($id)
    {
        $search = SocialSearch::with('results')->findOrFail($id);

        // Authorization check
        if ($search->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Get stats
        $stats = $this->socialService->getStats(auth()->id());

        return view('insights.social.results', [
            'search' => $search,
            'results' => $search->results,
            'stats' => $stats,
        ]);
    }

    /**
     * Show template selection page
     * GET /insights/social/choose-template
     */
    public function chooseTemplate(Request $request)
    {
        $validated = $request->validate([
            'search_id' => 'required|exists:ane_social_searches,id',
            'selected_topics' => 'required|string',
            'strategy' => 'required|in:roundup,hybrid,deep_dive_all',
        ]);

        $search = SocialSearch::findOrFail($validated['search_id']);

        // Authorization check
        if ($search->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $selectedTopicIds = json_decode($validated['selected_topics']);

        // Get user's templates
        $templates = \App\Models\AiGenerateTemplate::where('created_by', auth()->id())->get();

        return view('insights.social.choose-template', [
            'search' => $search,
            'selectedTopicIds' => $selectedTopicIds,
            'strategy' => $validated['strategy'],
            'templates' => $templates,
        ]);
    }

    /**
     * Show progress page for content generation
     * GET /insights/social/generate-progress
     */
    public function generateProgress(Request $request)
    {
        $strategyId = $request->input('strategy_id');

        $strategy = \App\Models\ContentStrategy::findOrFail($strategyId);

        // Authorization check
        if ($strategy->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        return view('insights.social.progress', [
            'strategy' => $strategy,
        ]);
    }

    /**
     * Generate content from selected trending topics
     * POST /insights/social/generate-content
     */
    public function generateContent(Request $request)
    {
        $validated = $request->validate([
            'search_id' => 'required|exists:ane_social_searches,id',
            'selected_topics' => 'required|json',
            'strategy' => 'required|in:roundup,hybrid,deep_dive_all',
            'template_id' => 'required|exists:ane_ai_generate_templates,id',
        ]);

        $search = SocialSearch::findOrFail($validated['search_id']);

        // Authorization check
        if ($search->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $selectedTopicIds = json_decode($validated['selected_topics']);

        // Validate selected topic IDs exist and belong to this search
        $validTopics = \App\Models\SocialResult::whereIn('id', $selectedTopicIds)
            ->where('search_id', $search->id)
            ->pluck('id')
            ->toArray();

        if (count($validTopics) !== count($selectedTopicIds)) {
            return redirect()->route('insights.social.results', $search->id)
                ->with('error', 'Some selected topics are invalid.');
        }

        // Load template for validation
        $template = \App\Models\AiGenerateTemplate::findOrFail($validated['template_id']);

        // Check template ownership
        if ($template->created_by !== auth()->id()) {
            abort(403, 'Unauthorized template access');
        }

        // Create content strategy record
        $strategy = \App\Models\ContentStrategy::create([
            'created_by' => auth()->id(),
            'search_id' => $search->id,
            'strategy_type' => $validated['strategy'],
            'selected_topics' => $selectedTopicIds,
            'content_template_id' => $validated['template_id'],
            'status' => 'pending',
        ]);

        // Dispatch job untuk AI ranking & content generation
        \App\Jobs\GenerateContentFromTrending::dispatch($strategy->id, $validated['template_id']);

        // Redirect to progress page
        return redirect()->route('insights.social.generate-progress', ['strategy_id' => $strategy->id]);
    }

    /**
     * Get content strategy status (for AJAX polling)
     * GET /api/content-strategy-status/{id}
     */
    public function getStrategyStatus($id)
    {
        $strategy = \App\Models\ContentStrategy::findOrFail($id);

        // Authorization check
        if ($strategy->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Count generated articles
        $articlesCount = \App\Models\Post::where('content_strategy_id', $strategy->id)->count();

        // Fallback expected count (in case older strategies were created before expected_articles was set)
        $expectedArticles = (int) ($strategy->expected_articles ?? 0);
        if ($expectedArticles === 0) {
            $expectedArticles = match ($strategy->strategy_type) {
                'roundup' => 1,
                'hybrid' => 4,
                'deep_dive_all' => is_array($strategy->selected_topics) ? count($strategy->selected_topics) : 0,
                default => 0,
            };
        }

        // Repair: if posts were created without content_strategy_id, attach recent orphan posts.
        if (
            $expectedArticles > 0
            && $articlesCount < $expectedArticles
            && in_array($strategy->status, ['pending', 'generating'], true)
        ) {
            $missingCount = $expectedArticles - $articlesCount;

            $orphanPosts = \App\Models\Post::query()
                ->whereNull('content_strategy_id')
                ->where('created_by', $strategy->created_by)
                ->whereIn('source_type', ['ai_generated', 'google_trends', 'social_insight', 'news_search'])
                ->where('created_at', '>=', $strategy->created_at)
                ->orderBy('created_at')
                ->limit($missingCount)
                ->get(['id']);

            if ($orphanPosts->isNotEmpty()) {
                $ids = $orphanPosts->pluck('id')->all();
                \App\Models\Post::whereIn('id', $ids)->update(['content_strategy_id' => $strategy->id]);

                // Best-effort role assignment (used only for internal grouping; safe if left null)
                if ($strategy->strategy_type === 'roundup') {
                    \App\Models\Post::whereIn('id', $ids)->update(['article_role' => 'roundup']);
                } elseif ($strategy->strategy_type === 'deep_dive_all') {
                    \App\Models\Post::whereIn('id', $ids)->update(['article_role' => 'deep_dive']);
                } elseif ($strategy->strategy_type === 'hybrid') {
                    $ordered = \App\Models\Post::query()
                        ->where('content_strategy_id', $strategy->id)
                        ->orderBy('created_at')
                        ->limit($expectedArticles)
                        ->pluck('id')
                        ->all();

                    if (!empty($ordered)) {
                        $first = array_shift($ordered);
                        if ($first) {
                            \App\Models\Post::where('id', $first)->update(['article_role' => 'roundup']);
                        }
                        if (!empty($ordered)) {
                            \App\Models\Post::whereIn('id', $ordered)->update(['article_role' => 'deep_dive']);
                        }
                    }
                }

                $articlesCount = \App\Models\Post::where('content_strategy_id', $strategy->id)->count();
            }
        }

        // Self-heal completion state to prevent infinite polling loops
        if (
            $expectedArticles > 0
            && $articlesCount >= $expectedArticles
            && in_array($strategy->status, ['pending', 'generating'], true)
        ) {
            $strategy->update(['status' => 'completed']);
            $strategy->refresh();
        }

        return response()->json([
            'status' => $strategy->status,
            'articles_count' => $articlesCount,
            'expected_articles' => $expectedArticles,
            'strategy_type' => $strategy->strategy_type,
        ]);
    }

    /**
     * Get post IDs for content strategy (for bulk review)
     * GET /api/content-strategy-posts/{id}
     */
    public function getStrategyPosts($id)
    {
        $strategy = \App\Models\ContentStrategy::findOrFail($id);

        // Authorization check
        if ($strategy->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Best-effort repair for older posts created without content_strategy_id
        $expectedArticles = (int) ($strategy->expected_articles ?? 0);
        if ($expectedArticles === 0) {
            $expectedArticles = match ($strategy->strategy_type) {
                'roundup' => 1,
                'hybrid' => 4,
                'deep_dive_all' => is_array($strategy->selected_topics) ? count($strategy->selected_topics) : 0,
                default => 0,
            };
        }

        $currentCount = \App\Models\Post::where('content_strategy_id', $strategy->id)->count();
        if ($expectedArticles > 0 && $currentCount < $expectedArticles) {
            $missingCount = $expectedArticles - $currentCount;
            $orphanIds = \App\Models\Post::query()
                ->whereNull('content_strategy_id')
                ->where('created_by', $strategy->created_by)
                ->whereIn('source_type', ['ai_generated', 'google_trends', 'social_insight', 'news_search'])
                ->where('created_at', '>=', $strategy->created_at)
                ->orderBy('created_at')
                ->limit($missingCount)
                ->pluck('id')
                ->all();

            if (!empty($orphanIds)) {
                \App\Models\Post::whereIn('id', $orphanIds)->update(['content_strategy_id' => $strategy->id]);
            }
        }

        // Get post IDs
        $postIds = \App\Models\Post::where('content_strategy_id', $strategy->id)
            ->pluck('id')
            ->toArray();

        return response()->json([
            'post_ids' => $postIds,
        ]);
    }

    /**
     * Search news from Google Trends keyword
     * POST /insights/social/search-news-from-trend
     */
    public function searchNewsFromTrend(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'country_code' => 'required|string|max:5',
            'language' => 'nullable|string|max:2',
        ]);

        try {
            // Use SerperSearchService to search news
            $serperService = new \App\Services\SerperSearchService();

            $newsResults = $serperService->searchNewsByKeyword(
                keyword: $validated['keyword'],
                timeRange: 'week',
                language: $validated['language'] ?? 'id',
                country: strtolower($validated['country_code']),
                maxResults: 20
            );

            if (empty($newsResults)) {
                return redirect()->back()
                    ->with('error', 'No news found for keyword: ' . $validated['keyword']);
            }

            // Create new search record with platform = 'google_trends_news'
            $search = SocialSearch::create([
                'created_by' => auth()->id(),
                'keyword' => $validated['keyword'],
                'platform' => 'google_trends_news', // Different platform to distinguish
                'country_code' => $validated['country_code'],
                'language' => $validated['language'] ?? 'id',
                'total_results' => count($newsResults),
                'status' => 'completed',
            ]);

            // Save news as social results
            foreach ($newsResults as $news) {
                SocialResult::create([
                    'search_id' => $search->id,
                    'platform' => 'google_trends_news',
                    'content' => $news['title'],
                    'author' => 'Google News',
                    'link' => $news['url'],
                    'engagement_likes' => 0,
                    'engagement_comments' => 0,
                    'engagement_shares' => 0,
                    'sentiment' => 'neutral',
                    'hashtags' => [$validated['keyword']],
                    'is_trending' => true,
                ]);
            }

            return redirect()->route('insights.social.results', ['id' => $search->id])
                ->with('success', 'Found ' . count($newsResults) . ' news articles about: ' . $validated['keyword']);

        } catch (\Exception $e) {
            Log::error('Search news from trend error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to search news: ' . $e->getMessage());
        }
    }

    /**
     * Generate articles from multiple Google Trends news
     * POST /insights/social/generate-batch-news
     */
    public function generateBatchNews(Request $request)
    {
        $validated = $request->validate([
            'search_id' => 'required|exists:ane_social_searches,id',
            'selected_news' => 'required|json',
            'template_id' => 'required|exists:ane_ai_generate_templates,id',
        ]);

        $search = SocialSearch::findOrFail($validated['search_id']);

        // Authorization check
        if ($search->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $selectedIds = json_decode($validated['selected_news']);

        // Validate selected news exist
        $newsArticles = \App\Models\SocialResult::whereIn('id', $selectedIds)
            ->where('search_id', $search->id)
            ->get();

        if ($newsArticles->count() !== count($selectedIds)) {
            return redirect()->back()
                ->with('error', 'Some selected news are invalid.');
        }

        // Load template
        $template = \App\Models\AiGenerateTemplate::findOrFail($validated['template_id']);

        // Check template ownership
        if ($template->created_by !== auth()->id()) {
            abort(403, 'Unauthorized template access');
        }

        // Create content strategy record for tracking
        // Use 'deep_dive_all' since each news = 1 article (similar to deep dive)
        $strategy = \App\Models\ContentStrategy::create([
            'created_by' => auth()->id(),
            'search_id' => $search->id,
            'strategy_type' => 'deep_dive_all',
            'selected_topics' => $selectedIds,
            'expected_articles' => count($selectedIds),
            'status' => 'pending',
        ]);

        // Dispatch batch job (similar to News Insight flow)
        \App\Jobs\GenerateBatchNewsArticles::dispatch($search->id, $selectedIds, $template->id, auth()->id(), $strategy->id);

        // Redirect to progress page
        return redirect()->route('insights.social.generate-progress', ['strategy_id' => $strategy->id])
            ->with('success', 'Generating ' . count($selectedIds) . ' articles...');
    }

    /**
     * Delete search and all results
     * DELETE /insights/social/{id}
     */
    public function destroy($id)
    {
        $search = SocialSearch::findOrFail($id);

        // Authorization check
        if ($search->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $platform = ucfirst($search->platform);
        $resultsCount = $search->total_results;

        // Delete search (cascade will delete results)
        $search->delete();

        return redirect()->route('insights.social.index')
            ->with('success', "Pencarian {$platform} dengan {$resultsCount} hasil berhasil dihapus.");
    }
}
