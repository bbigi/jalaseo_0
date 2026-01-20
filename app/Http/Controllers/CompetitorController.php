<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeCompetitor;
use App\Models\CompetitorAnalysisSession;
use App\Models\CompetitorProfile;
use App\Models\CompetitorKeyword;
use App\Models\Keyword;
use App\Models\GSCConnection;
use App\Models\GSCKeyword;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompetitorController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $competitors = CompetitorProfile::query()
            ->select('ane_competitor_profiles.*')
            ->when(($user->role ?? 'user') !== 'super_admin', function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->when(request('website_id'), function ($query) {
                $query->where('website_id', request('website_id'));
            })
            ->when(request('is_primary') !== null && request('is_primary') !== 'all', function ($query) {
                $query->where('is_primary', request('is_primary') === 'yes' ? 1 : 0);
            })
            ->addSelect([
                'latest_analysis_status' => CompetitorAnalysisSession::select('status')
                    ->whereColumn('competitor_id', 'ane_competitor_profiles.id')
                    ->latest()
                    ->limit(1),
                'latest_progress' => CompetitorAnalysisSession::select('progress')
                    ->whereColumn('competitor_id', 'ane_competitor_profiles.id')
                    ->latest()
                    ->limit(1),
            ])
            ->with('website')
            ->orderByRaw('last_analyzed_at IS NULL, last_analyzed_at DESC')
            ->get();

        $websites = ($user->role ?? 'user') === 'super_admin'
            ? Website::orderBy('name')->get()
            : Website::where('created_by', $user->id)->orderBy('name')->get();

        $countries = \App\Helpers\CountryHelper::getCountries();
        $languages = \App\Helpers\CountryHelper::getLanguages();

        return view('competitors.index', compact('competitors', 'websites', 'countries', 'languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:ane_websites,id',
            'domain' => 'required|string|max:255',
            'country' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $domain = $this->normalizeDomain($validated['domain']);
        if (!$domain) {
            return back()->withInput()->with('error', 'Invalid domain format.');
        }

        $this->authorizeWebsiteSelection($validated['website_id']);

        try {
            CompetitorProfile::create([
                'website_id' => $validated['website_id'],
                'created_by' => auth()->id(),
                'domain' => $domain,
                'added_by' => 'manual',
                'country' => $validated['country'] ?? 'Indonesia',
                'language' => $validated['language'] ?? 'id',
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('competitors.index')
                ->with('success', 'Competitor added successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to add competitor: ' . $e->getMessage());
        }
    }

    public function show(CompetitorProfile $competitor)
    {
        $this->authorizeCompetitor($competitor);

        // Get GSC keywords first for filtering
        $gscConnection = GSCConnection::where('website_id', $competitor->website_id)->first();
        $gscKeywordsList = [];
        if ($gscConnection) {
            $gscKeywordsList = GSCKeyword::query()
                ->where('connection_id', $gscConnection->id)
                ->select('keyword')
                ->distinct()
                ->pluck('keyword')
                ->map(fn($k) => strtolower(trim($k)))
                ->toArray();
        }

        $keywordsQuery = CompetitorKeyword::query()
            ->where('competitor_id', $competitor->id);

        $intent = request('intent');
        if (!empty($intent) && $intent !== 'all') {
            $keywordsQuery->where('intent', $intent);
        }

        $keywordType = request('type');
        if (!empty($keywordType) && $keywordType !== 'all') {
            $keywordsQuery->where('keyword_type', $keywordType);
        }

        $contentFormat = request('format');
        if (!empty($contentFormat) && $contentFormat !== 'all') {
            $keywordsQuery->where('content_format', $contentFormat);
        }

        $gscQuery = request('gsc_query');
        if (!empty($gscQuery) && $gscQuery !== 'all' && !empty($gscKeywordsList)) {
            if ($gscQuery === 'yes') {
                $keywordsQuery->whereIn(DB::raw('LOWER(TRIM(keyword))'), $gscKeywordsList);
            } elseif ($gscQuery === 'no') {
                $keywordsQuery->whereNotIn(DB::raw('LOWER(TRIM(keyword))'), $gscKeywordsList);
            }
        }

        $keywords = $keywordsQuery
            ->orderByDesc('search_volume')
            ->paginate(10)
            ->withQueryString();

        // Advanced ranked keywords (DataForSEO)
        $latestAdvancedSession = \App\Models\CompetitorAdvancedSession::where('competitor_id', $competitor->id)
            ->orderByDesc('created_at')
            ->first();

        $rankedKeywords = \App\Models\CompetitorRankedKeyword::query()
            ->where('competitor_id', $competitor->id)
            ->orderByDesc('search_volume')
            ->paginate(10)
            ->withQueryString();

        $trackedKeywords = Keyword::query()
            ->where('website_id', $competitor->website_id)
            ->get(['id', 'keyword', 'priority', 'target_position'])
            ->keyBy('keyword');

        // Reuse GSC connection from above
        $gscQueriedKeywords = collect();
        if ($gscConnection) {
            $gscQueriedKeywords = GSCKeyword::query()
                ->where('connection_id', $gscConnection->id)
                ->selectRaw('keyword, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position')
                ->groupBy('keyword')
                ->get()
                ->keyBy('keyword');
        }

        $filterOptions = [
            'intents' => CompetitorKeyword::where('competitor_id', $competitor->id)
                ->whereNotNull('intent')
                ->select('intent')
                ->distinct()
                ->orderBy('intent')
                ->pluck('intent')
                ->toArray(),
            'types' => CompetitorKeyword::where('competitor_id', $competitor->id)
                ->whereNotNull('keyword_type')
                ->select('keyword_type')
                ->distinct()
                ->orderBy('keyword_type')
                ->pluck('keyword_type')
                ->toArray(),
            'formats' => CompetitorKeyword::where('competitor_id', $competitor->id)
                ->whereNotNull('content_format')
                ->select('content_format')
                ->distinct()
                ->orderBy('content_format')
                ->pluck('content_format')
                ->toArray(),
        ];

        $competitor->load('website');

        return view('competitors.show', [
            'competitor' => $competitor,
            'keywords' => $keywords,
            'filterOptions' => $filterOptions,
            'trackedKeywords' => $trackedKeywords,
            'gscQueriedKeywords' => $gscQueriedKeywords,
            'latestAdvancedSession' => $latestAdvancedSession,
            'rankedKeywords' => $rankedKeywords,
        ]);
    }

    public function destroy(CompetitorProfile $competitor)
    {
        $this->authorizeCompetitor($competitor);

        $competitor->delete();

        return redirect()
            ->route('competitors.index')
            ->with('success', 'Competitor deleted successfully.');
    }

    public function analyzeStart(Request $request)
    {
        $validated = $request->validate([
            'competitor_id' => 'required|integer|exists:ane_competitor_profiles,id',
        ]);

        $competitor = CompetitorProfile::findOrFail($validated['competitor_id']);
        $this->authorizeCompetitor($competitor);

        $session = CompetitorAnalysisSession::create([
            'session_id' => 'comp_' . Str::uuid(),
            'competitor_id' => $competitor->id,
            'website_id' => $competitor->website_id,
            'created_by' => auth()->id(),
            'status' => 'processing',
            'progress' => 0,
            'current_step' => 'Initializing...',
        ]);

        AnalyzeCompetitor::dispatch($session->id);

        return response()->json([
            'success' => true,
            'session_id' => $session->session_id,
        ]);
    }

    public function analyzePoll(string $sessionId)
    {
        $session = CompetitorAnalysisSession::where('session_id', $sessionId)->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        $competitor = CompetitorProfile::find($session->competitor_id);
        if ($competitor) {
            $this->authorizeCompetitor($competitor);
        }

        return response()->json([
            'success' => true,
            'status' => $session->status,
            'progress' => $session->progress,
            'current_step' => $session->current_step,
            'error_message' => $session->error_message,
        ]);
    }

    private function authorizeCompetitor(CompetitorProfile $competitor): void
    {
        $user = auth()->user();
        if (($user->role ?? 'user') === 'super_admin') {
            return;
        }

        abort_unless($competitor->created_by === $user->id, 403);
    }

    private function authorizeWebsiteSelection(int $websiteId): void
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

    private function normalizeDomain(string $input): ?string
    {
        $input = trim(Str::lower($input));
        if ($input === '') {
            return null;
        }

        if (!str_starts_with($input, 'http://') && !str_starts_with($input, 'https://')) {
            $input = 'https://' . $input;
        }

        $host = parse_url($input, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        return preg_replace('/^www\./', '', $host);
    }
}
