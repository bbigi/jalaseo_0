<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\GSCConnection;
use App\Models\GA4Connection;
use App\Models\CompetitorProfile;
use App\Models\CompetitorRankedKeyword;
use App\Models\GSCKeyword;
use App\Models\PageSpeedRun;
use App\Models\GapRecommendation;
use App\Services\AIService;
use App\Services\GoogleSearchConsoleService;
use App\Services\GoogleAnalytics4Service;
use App\Jobs\SyncGSCKeywords;
use App\Jobs\SyncGA4Traffic;
use App\Jobs\SyncGA4Referrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WebsiteAnalyticsController extends Controller
{
    use AuthorizesRequests;
    /**
     * Show analytics dashboard for a website
     */
    public function index(Website $website)
    {
        Gate::authorize('view', $website);

        $gscConnection = GSCConnection::where('website_id', $website->id)->first();
        $ga4Connection = GA4Connection::where('website_id', $website->id)->first();

        // Load GSC keywords (aggregated by keyword text)
        $topPerformingKeywords = collect();
        $keywordOpportunities = collect();
        $keywordStats = null; // For summary stats (all keywords)
        $gscChartData = null; // For performance chart
        $allKeywords = null;
        $savedKeywords = collect();
        $trafficByDevice = null;
        $topCountries = null;
        $positionDistribution = null;
        $lowHangingFruits = collect();

        if ($gscConnection) {
            // Get aggregated stats for ALL keywords (for summary cards)
            $keywordStats = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('SUM(clicks) as total_clicks, SUM(impressions) as total_impressions')
                ->first();

            // Daily aggregated data for chart (last 30 days)
            $gscChartData = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('date, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
                
            // Traffic by Device (Mobile, Desktop, Tablet)
            $trafficByDevice = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('device, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('device')
                ->orderByDesc('total_clicks')
                ->get();
                
            // Top Countries by traffic
            $topCountries = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('country, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('country')
                ->orderByDesc('total_clicks')
                ->limit(10)
                ->get();

            // Position Distribution (count keywords by position range)
            $positionDistribution = [
                'top3' => \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                    ->selectRaw('keyword')
                    ->groupBy('keyword')
                    ->havingRaw('AVG(position) <= 3')
                    ->get()->count(),
                'top10' => \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                    ->selectRaw('keyword')
                    ->groupBy('keyword')
                    ->havingRaw('AVG(position) > 3 AND AVG(position) <= 10')
                    ->get()->count(),
                'top20' => \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                    ->selectRaw('keyword')
                    ->groupBy('keyword')
                    ->havingRaw('AVG(position) > 10 AND AVG(position) <= 20')
                    ->get()->count(),
                'beyond20' => \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                    ->selectRaw('keyword')
                    ->groupBy('keyword')
                    ->havingRaw('AVG(position) > 20')
                    ->get()->count(),
            ];
            
            // Low Hanging Fruits (position 11-20 with high impressions - easy to push to page 1)
            $lowHangingFruits = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('keyword, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('keyword')
                ->havingRaw('AVG(position) BETWEEN 11 AND 20')
                ->havingRaw('SUM(impressions) > 50')
                ->orderByDesc('total_impressions')
                ->limit(10)
                ->get();

            // Top 15 Keywords (aggregated, sorted by total clicks)
            $topPerformingKeywords = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('keyword, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('keyword')
                ->orderByDesc('total_clicks')
                ->limit(15)
                ->get();

            // Keyword Opportunities (high impressions, low CTR, position 5-20)
            $keywordOpportunities = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('keyword, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('keyword')
                ->havingRaw('SUM(impressions) > 100')  // High impressions
                ->havingRaw('AVG(ctr) < 0.10')          // Low CTR (< 10%)
                ->havingRaw('AVG(position) BETWEEN 5 AND 20')  // Position 5-20
                ->orderByDesc('total_impressions')
                ->limit(15)
                ->get();
                
            // All Keywords (load all, pagination will be done client-side)
            $allKeywords = \App\Models\GSCKeyword::where('connection_id', $gscConnection->id)
                ->selectRaw('keyword, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('keyword')
                ->orderByDesc('total_impressions')
                ->get();
                
            // Saved/Tracking Keywords (from ane_keywords)
            $savedKeywords = \App\Models\Keyword::where('website_id', $website->id)
                ->where('tracking_status', 'active')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Load GSC pages if connection exists (aggregated by page_url)
        $gscPages = null;
        if ($gscConnection) {
            $gscPages = \App\Models\GSCPage::where('connection_id', $gscConnection->id)
                ->selectRaw('page_url, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(ctr) as avg_ctr, AVG(position) as avg_position')
                ->groupBy('page_url')
                ->orderByDesc('total_clicks')
                ->limit(50)
                ->get();
        }

        // Load GA4 traffic data if connection exists
        $ga4Traffic = null;
        $ga4Overview = null;
        $ga4ChartData = null;
        if ($ga4Connection) {
            $ga4Traffic = \App\Models\GA4TrafficData::where('connection_id', $ga4Connection->id)
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get();
            
            // GA4 Overview - Aggregate totals
            $ga4Overview = \App\Models\GA4TrafficData::where('connection_id', $ga4Connection->id)
                ->selectRaw('
                    SUM(users) as total_users,
                    SUM(new_users) as total_new_users,
                    SUM(sessions) as total_sessions,
                    SUM(page_views) as total_page_views,
                    AVG(bounce_rate) as avg_bounce_rate,
                    AVG(engagement_rate) as avg_engagement_rate,
                    AVG(avg_session_duration) as avg_session_duration,
                    SUM(conversions) as total_conversions
                ')
                ->first();
            
            // GA4 Chart Data (last 30 days for trend)
            $ga4ChartData = \App\Models\GA4TrafficData::where('connection_id', $ga4Connection->id)
                ->select('date', 'users', 'sessions', 'page_views', 'new_users')
                ->orderBy('date', 'asc')
                ->get();
        }

        // Load GA4 referral sources if connection exists (aggregated)
        $ga4Referrals = null;
        $topReferrals = null;
        if ($ga4Connection) {
            $ga4Referrals = \App\Models\GA4ReferralSource::where('connection_id', $ga4Connection->id)
                ->orderBy('sessions', 'desc')
                ->limit(50)
                ->get();
            
            // Top Referral Sources (aggregated by source)
            $topReferrals = \App\Models\GA4ReferralSource::where('connection_id', $ga4Connection->id)
                ->selectRaw('source, medium, SUM(users) as total_users, SUM(sessions) as total_sessions, SUM(page_views) as total_page_views, AVG(bounce_rate) as avg_bounce_rate')
                ->groupBy('source', 'medium')
                ->orderByDesc('total_sessions')
                ->limit(10)
                ->get();
        }

        // Competitors section (for Competitive Gap)
        $competitorsGap = \App\Models\CompetitorProfile::with('latestAdvancedSession')
            ->where('website_id', $website->id)
            ->orderByDesc('is_primary')
            ->orderBy('domain')
            ->get()
            ->map(function ($competitor) {
                $session = $competitor->latestAdvancedSession;
                $metrics = $session?->summary_metrics ?? [];
                $organic = $metrics['organic'] ?? $metrics;

                $keywordsCount = $organic['count'] ?? null;
                $trafficEtv = $organic['etv'] ?? null;
                $avgRank = $organic['avg_rank'] ?? null;

                $status = 'not_analyzed';
                if ($session) {
                    if ($session->status === 'completed') {
                        $status = 'active';
                    } elseif ($session->status === 'failed') {
                        $status = 'error';
                    } else {
                        $status = 'processing';
                    }
                }

                return [
                    'id' => $competitor->id,
                    'domain' => $competitor->domain,
                    'website_name' => $competitor->website->name ?? $competitor->website->display_name ?? $competitor->domain,
                    'is_primary' => (bool) $competitor->is_primary,
                    'keywords' => $keywordsCount,
                    'traffic' => $trafficEtv,
                    'avg_rank' => $avgRank,
                    'status' => $status,
                    'last_analyzed' => $session?->finished_at ?? $session?->updated_at ?? $competitor->last_analyzed_at,
                ];
            });

        // Latest PageSpeed run (any strategy, newest)
        $latestPageSpeedRun = PageSpeedRun::where('website_id', $website->id)
            ->latest('created_at')
            ->first();

        return view('website-analytics.index', compact(
            'website',
            'gscConnection',
            'ga4Connection',
            'topPerformingKeywords',
            'keywordOpportunities',
            'keywordStats',
            'gscChartData',
            'allKeywords',
            'savedKeywords',
            'trafficByDevice',
            'topCountries',
            'positionDistribution',
            'lowHangingFruits',
            'gscPages',
            'ga4Traffic',
            'ga4Overview',
            'ga4ChartData',
            'ga4Referrals',
            'topReferrals',
            'competitorsGap',
            'latestPageSpeedRun'
        ));
    }

    /**
     * Competitive Gap Analysis page
     */
    public function analyticsGaps(Website $website)
    {
        Gate::authorize('view', $website);

        $filter = request()->get('filter', 'all');
        $searchRaw = request()->get('q', '');
        $requestedCompetitorId = request()->integer('competitor_id');

        $data = $this->buildAnalyticsGapData($website, $requestedCompetitorId, $filter, $searchRaw);

        if ($data['competitors']->isEmpty()) {
            return view('website-analytics.analytics-gaps', [
                'website' => $website,
                'competitors' => collect(),
                'selectedCompetitor' => null,
                'rows' => [],
                'stats' => [],
                'filter' => 'all',
                'search' => '',
            ]);
        }

        return view('website-analytics.analytics-gaps', [
            'website' => $website,
            'competitors' => $data['competitors'],
            'selectedCompetitor' => $data['selectedCompetitor'],
            'rows' => $data['rows'],
            'stats' => $data['stats'],
            'filter' => $filter,
            'search' => $searchRaw,
            'latestRecommendation' => $this->getLatestGapRecommendation($website->id, $data['selectedCompetitor']->id ?? null, app()->getLocale(), $filter, $searchRaw),
        ]);
    }

    /**
     * Generate AI recommendations for Competitive Gap page
     */
    public function generateGapRecommendations(Request $request, Website $website, AIService $aiService)
    {
        Gate::authorize('view', $website);

        $validated = $request->validate([
            'competitor_id' => 'nullable|integer',
            'filter' => 'nullable|string|max:50',
            'q' => 'nullable|string|max:200',
        ]);

        $filter = $validated['filter'] ?? 'all';
        $search = $validated['q'] ?? '';
        $requestedCompetitorId = $validated['competitor_id'] ?? null;

        $data = $this->buildAnalyticsGapData($website, $requestedCompetitorId, $filter, $search);

        if ($data['competitors']->isEmpty()) {
            return response()->json(['message' => 'No competitors found for this website.'], 422);
        }

        if (empty($data['rows'])) {
            return response()->json(['message' => 'No keywords available to generate recommendations.'], 422);
        }

        $rows = $data['rows'];
        usort($rows, function ($a, $b) {
            $etvDiff = ($b['etv'] ?? 0) <=> ($a['etv'] ?? 0);
            if ($etvDiff !== 0) {
                return $etvDiff;
            }
            return ($b['search_volume'] ?? 0) <=> ($a['search_volume'] ?? 0);
        });

        $top = array_slice($rows, 0, 10);
        $locale = app()->getLocale() ?? 'en';
        $prompt = $this->buildGapRecommendationPrompt(
            $website,
            $data['selectedCompetitor'],
            $data['stats'],
            $top,
            $locale
        );

        $result = $aiService->generate($prompt, 'article_generation', [
            'max_tokens' => 3200,
            'temperature' => 0.65,
        ]);

        $recommendation = GapRecommendation::create([
            'website_id' => $website->id,
            'competitor_id' => $data['selectedCompetitor']->id ?? null,
            'locale' => $locale,
            'filter' => $filter,
            'search' => $search,
            'content' => $result['content'] ?? '',
            'provider' => $result['provider'] ?? null,
            'model' => $result['model'] ?? null,
            'tokens_used' => $result['tokens_used'] ?? null,
        ]);

        $this->trimGapRecommendations($website->id, $data['selectedCompetitor']->id ?? null, $locale);

        return response()->json([
            'content' => $result['content'] ?? '',
            'provider' => $result['provider'] ?? null,
            'model' => $result['model'] ?? null,
            'saved_id' => $recommendation->id,
        ]);
    }

    /**
     * Prepare analytics gap data (shared by page + AI generation)
     */
    protected function buildAnalyticsGapData(Website $website, ?int $requestedCompetitorId, string $filter, string $searchRaw): array
    {
        $competitors = CompetitorProfile::with('latestAdvancedSession')
            ->where('website_id', $website->id)
            ->orderByDesc('is_primary')
            ->orderBy('domain')
            ->get();

        if ($competitors->isEmpty()) {
            return [
                'competitors' => collect(),
                'selectedCompetitor' => null,
                'rows' => [],
                'stats' => [],
            ];
        }

        $selectedCompetitor = $competitors->firstWhere('id', $requestedCompetitorId)
            ?? $competitors->firstWhere('is_primary', true)
            ?? $competitors->first();

        $normalize = function (?string $text): string {
            return strtolower(preg_replace('/\s+/', ' ', trim($text ?? '')));
        };

        // Our GSC keywords (normalized)
        $gscRows = GSCKeyword::forWebsite($website->id)
            ->lastDays(90)
            ->select('keyword', DB::raw('AVG(position) as avg_position'), DB::raw('SUM(impressions) as impressions'), DB::raw('SUM(clicks) as clicks'))
            ->groupBy('keyword')
            ->get();

        $gscIndex = [];
        foreach ($gscRows as $row) {
            $key = $normalize($row->keyword);
            if ($key === '') {
                continue;
            }
            $gscIndex[$key] = [
                'position' => $row->avg_position ? round($row->avg_position, 1) : null,
                'impressions' => (int) $row->impressions,
                'clicks' => (int) $row->clicks,
            ];
        }

        // Competitor ranked keywords
        $compKeywords = CompetitorRankedKeyword::where('competitor_id', $selectedCompetitor->id)
            ->orderByDesc('search_volume')
            ->limit(200)
            ->get();

        $classificationCounts = [
            'we_win' => 0,
            'opportunity' => 0,
            'content_gap' => 0,
            'low_priority' => 0,
        ];
        $rows = [];
        $deltas = [];

        foreach ($compKeywords as $kw) {
            $keywordNorm = $normalize($kw->keyword);
            if ($keywordNorm === '') {
                continue;
            }

            $our = $gscIndex[$keywordNorm] ?? null;
            $ourRank = $our['position'] ?? null;
            $competitorRank = $kw->rank_group ?? $kw->rank_absolute;
            $delta = ($ourRank !== null && $competitorRank !== null)
                ? round($ourRank - $competitorRank, 1)
                : null;

            // Classification
            if ($ourRank === null) {
                if ($competitorRank !== null && $competitorRank <= 30) {
                    $classification = 'opportunity';
                } elseif ($competitorRank !== null && $competitorRank <= 50) {
                    $classification = 'content_gap';
                } else {
                    $classification = 'low_priority';
                }
            } elseif ($competitorRank === null) {
                $classification = 'we_win';
            } elseif ($ourRank < $competitorRank) {
                $classification = 'we_win';
            } elseif ($competitorRank <= 30) {
                $classification = 'opportunity';
            } elseif ($delta !== null && abs($delta) <= 2) {
                $classification = 'low_priority';
            } else {
                $classification = 'opportunity';
            }

            $classificationCounts[$classification] = ($classificationCounts[$classification] ?? 0) + 1;
            if ($delta !== null) {
                $deltas[] = $delta;
            }

            $rows[] = [
                'keyword' => $kw->keyword,
                'search_volume' => $kw->search_volume,
                'competitor_rank' => $competitorRank,
                'competitor_url' => $kw->url,
                'etv' => $kw->etv,
                'our_rank' => $ourRank,
                'delta' => $delta,
                'classification' => $classification,
            ];
        }

        // Stats
        $total = count($rows);
        sort($deltas);
        $medianDelta = null;
        if (count($deltas) > 0) {
            $mid = intdiv(count($deltas), 2);
            $medianDelta = count($deltas) % 2 === 0
                ? ($deltas[$mid - 1] + $deltas[$mid]) / 2
                : $deltas[$mid];
        }

        $winsBase = $classificationCounts['we_win'] + $classificationCounts['opportunity'] + $classificationCounts['content_gap'];
        $winRate = $winsBase > 0 ? round(($classificationCounts['we_win'] / $winsBase) * 100, 1) : null;

        $stats = [
            'total' => $total,
            'win_rate' => $winRate,
            'median_delta' => $medianDelta,
        ] + $classificationCounts;

        // Filtering
        $search = $normalize($searchRaw);
        $filteredRows = array_values(array_filter($rows, function ($row) use ($filter, $search) {
            if ($filter !== 'all' && $row['classification'] !== $filter) {
                return false;
            }
            if ($search && !str_contains(strtolower($row['keyword']), $search)) {
                return false;
            }
            return true;
        }));

        return [
            'competitors' => $competitors,
            'selectedCompetitor' => $selectedCompetitor,
            'rows' => $filteredRows,
            'stats' => $stats,
        ];
    }

    /**
     * Build prompt for AI recommendation on gap analysis
     */
    protected function buildGapRecommendationPrompt(Website $website, ?CompetitorProfile $competitor, array $stats, array $keywords, string $locale): string
    {
        $competitorDomain = $competitor?->domain ?? '-';
        $keywordPayload = array_map(function ($row) {
            return [
                'keyword' => $row['keyword'],
                'search_volume' => $row['search_volume'],
                'our_rank' => $row['our_rank'],
                'competitor_rank' => $row['competitor_rank'],
                'delta' => $row['delta'],
                'etv' => $row['etv'],
                'classification' => $row['classification'],
                'competitor_url' => $row['competitor_url'],
            ];
        }, $keywords);

        $keywordsJson = json_encode($keywordPayload, JSON_PRETTY_PRINT);
        $winRate = $stats['win_rate'] ?? null;
        $medianDelta = $stats['median_delta'] ?? null;
        $summaryStats = [
            'total_keywords' => $stats['total'] ?? 0,
            'win_rate' => $winRate,
            'median_delta' => $medianDelta,
            'we_win' => $stats['we_win'] ?? 0,
            'opportunity' => $stats['opportunity'] ?? 0,
            'content_gap' => $stats['content_gap'] ?? 0,
            'low_priority' => $stats['low_priority'] ?? 0,
        ];

        $statsJson = json_encode($summaryStats, JSON_PRETTY_PRINT);
        $langInstruction = "Use the user's language/locale: {$locale}. If locale is 'id', answer in Bahasa Indonesia; otherwise answer in the matching language.";

        return <<<PROMPT
You are an SEO strategist. {$langInstruction}

Return clean HTML only (no JSON). Use Tailwind utility classes similar to our Deep Research cards. Keep sentences short, avoid Markdown tables, NEVER wrap output in code fences/backticks, and keep total length under 450 words so it fits on screen. Do not render more than 3 cards total.

Context:
- Website: {$website->name} ({$website->url})
- Competitor: {$competitorDomain}
- Summary stats (JSON): {$statsJson}
- Top keywords to analyze (JSON): {$keywordsJson}

Output structure in HTML (use Tailwind classes like bg-*, border, rounded-2xl, p-4, flex, gap-3, text-slate-800, etc.):
<div class="space-y-4 text-slate-900">
  <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
    <h3 class="text-lg font-bold">What this data means</h3>
    <p class="text-sm">Explain volume, our vs competitor position, delta, ETV, and why it matters.</p>
    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">2-3 bullets that connect to business impact.</ul>
  </div>

  <div class="space-y-3">
    <!-- Create up to 3 cards total; mix Quick Win, Money, Content Gap -->
    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
          <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-white text-emerald-700 text-xs font-semibold border border-emerald-200">Quick Win</div>
          <h4 class="text-base font-bold">keyword</h4>
          <p class="text-sm text-slate-700">One-line why this matters.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs">
          <span class="px-2 py-1 bg-white border border-emerald-200 rounded-full font-semibold text-slate-800">Vol: ...</span>
          <span class="px-2 py-1 bg-white border border-emerald-200 rounded-full font-semibold text-slate-800">Competitor: #..</span>
          <span class="px-2 py-1 bg-white border border-emerald-200 rounded-full font-semibold text-slate-800">ETV: ..</span>
        </div>
      </div>
      <ul class="list-disc list-inside text-sm text-slate-800 mt-3 space-y-1">
        <li><strong>Action:</strong> what to do</li>
        <li><strong>Format:</strong> article type/angle</li>
        <li><strong>Outcome:</strong> expected gain</li>
      </ul>
      <ol class="list-decimal list-inside text-sm text-slate-900 mt-2 space-y-1">
        <li>Recommended title 1</li>
        <li>Recommended title 2</li>
        <li>Recommended title 3</li>
      </ol>
    </div>
  </div>

  <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
    <h3 class="text-lg font-bold">Action plan</h3>
    <ol class="list-decimal list-inside text-sm text-slate-800 space-y-1">5-8 steps mixing content, on-page, internal links, backlinking, tracking.</ol>
  </div>

  <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
    <h3 class="text-lg font-bold">2-week schedule</h3>
    <table class="w-full text-sm text-slate-800 border-collapse mt-2">
      <tr class="border-b border-slate-200">
        <th class="text-left font-semibold py-2 pr-2">Week</th>
        <th class="text-left font-semibold py-2 pr-2">Focus</th>
        <th class="text-left font-semibold py-2 pr-2">Keywords</th>
        <th class="text-left font-semibold py-2">Actions</th>
      </tr>
      <!-- Provide 4 rows covering Week 1-2 -->
    </table>
  </div>

  <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
    <h3 class="text-lg font-bold">How the app helps</h3>
    <ul class="list-disc list-inside text-sm text-slate-800 space-y-1">3-5 bullets (generate articles, bookmark/track, duplicate via competitor URL, schedule content).</ul>
  </div>
</div>
PROMPT;
    }

    protected function getLatestGapRecommendation(int $websiteId, ?int $competitorId, string $locale, string $filter, string $searchRaw)
    {
        return GapRecommendation::where('website_id', $websiteId)
            ->when($competitorId, fn($q) => $q->where('competitor_id', $competitorId))
            ->where('locale', $locale)
            ->where(function ($q) use ($filter) {
                $q->whereNull('filter')->orWhere('filter', $filter);
            })
            ->where(function ($q) use ($searchRaw) {
                $q->whereNull('search')->orWhere('search', $searchRaw);
            })
            ->latest('created_at')
            ->first();
    }

    protected function trimGapRecommendations(int $websiteId, ?int $competitorId, string $locale, int $keep = 5): void
    {
        $idsToKeep = GapRecommendation::where('website_id', $websiteId)
            ->when($competitorId, fn($q) => $q->where('competitor_id', $competitorId))
            ->where('locale', $locale)
            ->latest('created_at')
            ->limit($keep)
            ->pluck('id')
            ->all();

        GapRecommendation::where('website_id', $websiteId)
            ->when($competitorId, fn($q) => $q->where('competitor_id', $competitorId))
            ->where('locale', $locale)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    /**
     * Initiate GSC OAuth flow
     */
    public function connectGSC(Website $website, GoogleSearchConsoleService $gscService)
    {
        Gate::authorize('view', $website);

        $authUrl = $gscService->getAuthorizationUrl($website->id);

        return redirect($authUrl);
    }

    /**
     * Handle GSC OAuth callback
     */
    public function callbackGSC(Request $request, GoogleSearchConsoleService $gscService)
    {
        $code = $request->input('code');
        $state = json_decode($request->input('state'), true);
        $websiteId = $state['website_id'];

        $website = Website::findOrFail($websiteId);
        Gate::authorize('view', $website);

        // First, exchange code for tokens
        $token = $gscService->exchangeCodeForToken($code);

        // Get available properties from GSC
        $properties = $gscService->getAvailableProperties($token['access_token'], $token['refresh_token']);

        // Try to find matching property for this website
        $websiteHost = parse_url($website->url, PHP_URL_HOST);
        $propertyUrl = null;

        // Priority: exact match > www variant > sc-domain
        foreach ($properties as $prop) {
            if (str_contains($prop, $websiteHost)) {
                $propertyUrl = $prop;
                break;
            }
        }

        // Fallback to first property if no match
        if (!$propertyUrl && count($properties) > 0) {
            $propertyUrl = $properties[0];
        }

        if (!$propertyUrl) {
            return redirect()->route('website-analytics.index', $website)
                ->with('error', 'No properties found in your Google Search Console account.');
        }

        $connection = $gscService->handleCallbackWithToken($token, $websiteId, $propertyUrl);

        // Dispatch sync job
        SyncGSCKeywords::dispatch($connection, 30);

        return redirect()->route('website-analytics.index', $website)
            ->with('success', 'Google Search Console connected! Syncing data in background...');
    }

    /**
     * Initiate GA4 OAuth flow
     */
    public function connectGA4(Website $website, GoogleAnalytics4Service $ga4Service)
    {
        Gate::authorize('view', $website);

        $authUrl = $ga4Service->getAuthorizationUrl($website->id);

        return redirect($authUrl);
    }

    /**
     * Handle GA4 OAuth callback
     */
    public function callbackGA4(Request $request, GoogleAnalytics4Service $ga4Service)
    {
        $code = $request->input('code');
        $state = json_decode($request->input('state'), true);
        $websiteId = $state['website_id'];

        $website = Website::findOrFail($websiteId);
        Gate::authorize('view', $website);

        // Store connection WITHOUT property ID (will be set in next step)
        $connection = $ga4Service->handleCallback($code, $websiteId, '');

        // Redirect to analytics page, modal will auto-open via session flag
        return redirect()->route('website-analytics.index', $website)
            ->with('success', 'OAuth successful! Please enter your GA4 Property ID to complete setup.');
    }

    /**
     * Show GA4 Property ID setup form
     */
    public function setupGA4Property(Website $website)
    {
        Gate::authorize('view', $website);

        $connection = GA4Connection::where('website_id', $website->id)->firstOrFail();

        return view('website-analytics.ga4-setup', compact('website', 'connection'));
    }

    /**
     * Save GA4 Property ID and start sync
     */
    public function saveGA4Property(Request $request, Website $website)
    {
        Gate::authorize('view', $website);

        $validated = $request->validate([
            'property_id' => 'required|string|regex:/^[0-9]+$/',
        ]);

        $connection = GA4Connection::where('website_id', $website->id)->firstOrFail();
        $connection->update([
            'property_id' => $validated['property_id'],
        ]);

        // Now dispatch sync jobs
        SyncGA4Traffic::dispatch($connection, 30);
        SyncGA4Referrals::dispatch($connection, 30);

        return redirect()->route('website-analytics.index', $website)
            ->with('success', 'GA4 Property ID saved! Syncing data in background...');
    }

    /**
     * Trigger manual sync for GSC
     */
    public function syncGSC(Website $website)
    {
        Gate::authorize('view', $website);

        $connection = GSCConnection::where('website_id', $website->id)->firstOrFail();

        SyncGSCKeywords::dispatch($connection, 30);

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'GSC sync started in background!'
            ]);
        }

        return redirect()->back()
            ->with('success', 'GSC sync started in background!');
    }

    /**
     * Trigger manual sync for GA4
     */
    public function syncGA4(Website $website)
    {
        Gate::authorize('view', $website);

        $connection = GA4Connection::where('website_id', $website->id)->firstOrFail();

        SyncGA4Traffic::dispatch($connection, 30);
        SyncGA4Referrals::dispatch($connection, 30);

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'GA4 sync started in background!'
            ]);
        }

        return redirect()->back()
            ->with('success', 'GA4 sync started in background!');
    }

    /**
     * Disconnect GSC
     */
    public function disconnectGSC(Website $website)
    {
        Gate::authorize('view', $website);

        GSCConnection::where('website_id', $website->id)->delete();

        return redirect()->back()
            ->with('success', 'Google Search Console disconnected');
    }

    /**
     * Disconnect GA4
     */
    public function disconnectGA4(Website $website)
    {
        Gate::authorize('view', $website);

        GA4Connection::where('website_id', $website->id)->delete();

        return redirect()->back()
            ->with('success', 'Google Analytics 4 disconnected');
    }

    /**
     * Save keyword to user's saved keywords
     */
    public function saveKeyword(Request $request, Website $website)
    {
        Gate::authorize('view', $website);

        $validated = $request->validate([
            'keyword' => 'required|string|max:500',
            'position' => 'required|numeric',
            'target_position' => 'nullable|integer|min:1|max:100',
            'priority' => 'nullable|in:high,medium,low,none',
        ]);

        try {
            \App\Models\Keyword::updateOrCreate(
                [
                    'website_id' => $website->id,
                    'keyword' => $validated['keyword'],
                ],
                [
                    'source' => 'gsc_sync',
                    'tracking_status' => 'active',
                    'current_position' => $validated['position'],
                    'target_position' => $validated['target_position'] ?? null,
                    'priority' => $validated['priority'] ?? 'medium',
                    'created_by' => auth()->id(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Keyword tracked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status for polling (GSC & GA4)
     */
    public function getSyncStatus(Website $website)
    {
        $this->authorize('view', $website);

        $gscConnection = $website->gscConnection;
        $ga4Connection = $website->ga4Connection;

        $response = [
            'gsc' => [
                'is_syncing' => false,
                'status' => null,
                'last_sync_at' => null,
                'error' => null,
            ],
            'ga4' => [
                'is_syncing' => false,
                'status' => null,
                'last_sync_at' => null,
                'error' => null,
            ],
        ];

        // Check GSC sync status
        if ($gscConnection) {
            $latestGSCJob = $gscConnection->syncJobs()->latest()->first();

            $response['gsc'] = [
                'is_syncing' => in_array($gscConnection->sync_status, ['processing', 'syncing']),
                'status' => $gscConnection->sync_status,
                'last_sync_at' => $gscConnection->last_sync_at?->format('Y-m-d H:i:s'),
                'error' => $gscConnection->sync_error,
                'job' => $latestGSCJob ? [
                    'id' => $latestGSCJob->id,
                    'status' => $latestGSCJob->job_status,
                    'keywords_synced' => $latestGSCJob->keywords_synced,
                    'pages_synced' => $latestGSCJob->pages_synced,
                ] : null,
            ];
        }

        // Check GA4 sync status
        if ($ga4Connection) {
            $response['ga4'] = [
                'is_syncing' => in_array($ga4Connection->sync_status, ['processing', 'syncing']),
                'status' => $ga4Connection->sync_status,
                'last_sync_at' => $ga4Connection->last_sync_at?->format('Y-m-d H:i:s'),
                'error' => $ga4Connection->sync_error,
            ];
        }

        return response()->json($response);
    }
}
