<?php

namespace App\Http\Controllers;

use App\Models\AiGenerateTemplate;
use App\Models\Website;
use App\Models\Keyword;
use App\Models\Insight;
use App\Jobs\GenerateArticleJob;
use App\Services\SerperSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class GenerateController extends Controller
{
    /**
     * Show AI article generation form
     */
    public function index(Request $request)
    {
        // Load user's websites
        $websites = Website::where('created_by', auth()->id())
            ->where('status', 'active')
            ->get();

        // Load user's templates only
        $templates = AiGenerateTemplate::where('created_by', auth()->id())
            ->where('is_active', 1)
            ->get();

        $insights = Insight::where('created_by', auth()->id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByDesc('id')
            ->get();

        // Prepare context from query params
        $context = [
            'source' => $request->get('source'), // 'keyword', 'deep_research', 'insight'
            'source_id' => $request->get('source_id'),
            'keyword' => $request->get('keyword'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'url' => $request->get('url'),
            'website_id' => $request->get('website_id'),
            'generation_source' => $request->get('generation_source'),
            'template_id' => $request->get('template_id'),
            'insight_id' => $request->get('insight_id'),
        ];

        // Load related data based on source
        if ($context['source'] === 'keyword' && $context['source_id']) {
            $keyword = Keyword::where('id', $context['source_id'])
                ->where('created_by', auth()->id())
                ->first();

            if ($keyword) {
                $context['keyword'] = $keyword->keyword;
                $context['title'] = $context['title'] ?? $keyword->keyword;
                $context['website_id'] = $keyword->website_id;
            }
        }

        if ($context['source'] === 'insight' && $context['source_id']) {
            $insight = Insight::where('id', $context['source_id'])
                ->where('created_by', auth()->id())
                ->first();

            if ($insight) {
                $context['title'] = $context['title'] ?? $insight->topic;
                $context['description'] = $context['description'] ?? $insight->description;
                $context['generation_source'] = 'insight';
                $context['insight_id'] = $insight->id;
            }
        }

        return view('generate.index', compact('websites', 'templates', 'insights', 'context'));
    }

    /**
     * Generate article (background job)
     */
    public function generateArticle(Request $request)
    {
        $validated = $request->validate([
            'generation_source' => 'required|in:title,reference,text,insight',
            'title' => 'required|string|max:255',
            'template_id' => 'required|exists:ane_ai_generate_templates,id',
            'website_id' => 'nullable|exists:ane_websites,id',
            'insight_id' => 'nullable|required_if:generation_source,insight|exists:ane_insights,id',

            // Conditional (if source = reference)
            'ref_topic' => 'nullable|string',
            'ref_description' => 'nullable|string',
            'ref_url' => 'nullable|url',

            // Conditional (if source = text)
            'ref_text' => 'nullable|string',
            'ref_source_name' => 'nullable|string',
        ]);

        // Verify website ownership
        if (!empty($validated['website_id'])) {
            Website::where('id', $validated['website_id'])
                ->where('created_by', auth()->id())
                ->firstOrFail();
        }

        $insight = null;
        if ($validated['generation_source'] === 'insight') {
            $insight = Insight::where('id', $validated['insight_id'])
                ->where('created_by', auth()->id())
                ->firstOrFail();

            $insight->update([
                'status' => 'in_progress',
            ]);
        }

        // Generate job ID
        $jobId = Str::uuid()->toString();

        // Initialize progress tracking
        Cache::put("generate_job:{$jobId}", [
            'status' => 'queued',
            'progress' => 0,
            'step' => 'Initializing...',
            'result' => null,
        ], now()->addMinutes(10));

        // Dispatch background job
        GenerateArticleJob::dispatch([
            'job_id' => $jobId,
            'user_id' => auth()->id(),
            'generation_source' => $validated['generation_source'],
            'title' => $insight?->topic ?? $validated['title'],
            'template_id' => $validated['template_id'],
            'website_id' => $validated['website_id'] ?? null,
            'insight_id' => $insight?->id,
            'references' => [
                'topic' => $validated['ref_topic'] ?? null,
                'description' => $validated['ref_description'] ?? null,
                'url' => $validated['ref_url'] ?? null,
                'text' => $validated['ref_text'] ?? null,
                'source_name' => $validated['ref_source_name'] ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'job_id' => $jobId,
        ]);
    }

    /**
     * Get generation progress
     */
    public function getProgress(string $jobId)
    {
        $progress = Cache::get("generate_job:{$jobId}");

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'progress' => 0,
                'step' => 'Job not found',
            ], 404);
        }

        return response()->json($progress);
    }

    /**
     * Show generation progress page
     */
    public function progressPage(Request $request)
    {
        $templateId = $request->get('template_id');
        $template = null;

        if ($templateId) {
            $template = AiGenerateTemplate::where('id', $templateId)
                ->where('created_by', auth()->id())
                ->first();
        }

        $title = $request->get('title') ?? 'Untitled';
        $keyword = $request->get('keyword') ?? $title;
        $jobId = $request->get('job_id');

        return view('generate.progress', [
            'template' => $template,
            'title' => $title,
            'keyword' => $keyword,
            'jobId' => $jobId,
        ]);
    }

    /**
     * Get saved keywords for a website (AJAX)
     */
    public function getKeywordsForWebsite(int $websiteId)
    {
        $website = Website::where('id', $websiteId)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $keywords = Keyword::where('website_id', $website->id)
            ->orderBy('keyword')
            ->limit(500)
            ->get(['id', 'keyword']);

        return response()->json([
            'success' => true,
            'keywords' => $keywords,
        ]);
    }

    /**
     * Autocomplete keywords (saved + Serper suggestions)
     */
    public function autocompleteKeywords(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        if ($query === '') {
            return response()->json(['success' => true, 'suggestions' => []]);
        }

        $websiteId = $request->get('website_id');
        $saved = [];
        if (!empty($websiteId)) {
            $website = Website::where('id', $websiteId)
                ->where('created_by', auth()->id())
                ->first();

            if ($website) {
                $saved = Keyword::where('website_id', $website->id)
                    ->where('keyword', 'like', '%' . $query . '%')
                    ->orderBy('keyword')
                    ->limit(10)
                    ->get(['keyword'])
                    ->pluck('keyword')
                    ->toArray();
            }
        }

        $savedItems = array_map(function ($kw) {
            return [
                'value' => $kw,
                'type' => 'saved',
            ];
        }, $saved);

        $liveItems = [];
        try {
            $serper = new SerperSearchService();
            $suggestions = $serper->getAutocompleteSuggestions($query, 'id', 'id');
            foreach ($suggestions as $item) {
                if (in_array($item, $saved, true)) {
                    continue;
                }
                $liveItems[] = [
                    'value' => $item,
                    'type' => 'live',
                ];
                if (count($liveItems) >= 8) {
                    break;
                }
            }
        } catch (\Exception $e) {
            // Ignore Serper errors, return saved keywords only
        }

        return response()->json([
            'success' => true,
            'suggestions' => array_merge($savedItems, $liveItems),
        ]);
    }

    /**
     * Search reference URLs via Serper
     */
    public function searchReferences(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
        ]);

        $keyword = trim($validated['keyword']);
        $cacheKey = 'generate:serp:' . auth()->id() . ':' . md5($keyword);

        $results = Cache::remember($cacheKey, now()->addHours(24), function () use ($keyword) {
            $serper = new SerperSearchService();
            return $serper->searchWeb($keyword, 'id', 'id', 20);
        });

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Store selected reference in cache (24h)
     */
    public function selectReference(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'title' => 'required|string|max:500',
            'url' => 'required|url|max:1000',
            'snippet' => 'nullable|string|max:2000',
        ]);

        $cacheKey = 'generate:serp:selected:' . auth()->id() . ':' . md5($validated['keyword']);
        Cache::put($cacheKey, [
            'title' => $validated['title'],
            'url' => $validated['url'],
            'snippet' => $validated['snippet'] ?? '',
            'selected_at' => now()->toDateTimeString(),
        ], now()->addHours(24));

        return response()->json(['success' => true]);
    }
}
