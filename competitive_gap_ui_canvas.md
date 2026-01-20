# Goal

Add a new **“Your Competitors – Competitive Gap Analysis”** section at the bottom of **Website Analytics** and a new **Analytics Gaps** page that compares **Your website (GSC)** vs **Primary competitor (DataForSEO ranked keywords)**.

---

## 1) Website Analytics page: add new section

**Page:** `GET /websites/{website}/analytics`

### UI (matches screenshot)

Add a full-width card section at the very bottom:

**Title:** `Your Competitors`
**Subtitle:** `Competitive Gap Analysis`
**CTA (top-right):** `View Analytics Gaps` → `/websites/{website}/analytics-gaps`

**Table columns:**

* Domain
* Website
* Status (Active / Not analyzed / Error)
* Primary (badge + toggle)
* Keywords (from `advanced_sessions.summary_metrics.count`)
* Traffic Est. (from `advanced_sessions.summary_metrics.etv`)
* Avg Rank (use `-` for now)
* Last Analyzed (humanized)

### Suggested Blade snippet

> Put this inside your website analytics Blade near the bottom.

```blade
{{-- Your Competitors – Competitive Gap Analysis --}}
<div class="mt-10">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Your Competitors</h2>
      <p class="text-sm text-slate-500">Competitive Gap Analysis</p>
    </div>

    <a href="{{ route('websites.analytics.gaps', $website) }}"
       class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
      View Analytics Gaps
    </a>
  </div>

  <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-semibold">Domain</th>
            <th class="px-4 py-3 font-semibold">Website</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Primary</th>
            <th class="px-4 py-3 font-semibold">Keywords</th>
            <th class="px-4 py-3 font-semibold">Traffic Est.</th>
            <th class="px-4 py-3 font-semibold">Avg Rank</th>
            <th class="px-4 py-3 font-semibold">Last Analyzed</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($competitors as $c)
            @php
              $latest = $c->advancedSessions->sortByDesc('created_at')->first();
              $metrics = $latest?->summary_metrics ?? [];
              $keywordsCount = data_get($metrics, 'count');
              $trafficEtv = data_get($metrics, 'etv');
              $status = $latest?->status ?? 'not_analyzed';
            @endphp
            <tr>
              <td class="px-4 py-3 font-medium text-slate-900">{{ $c->domain }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $c->name ?? '-' }}</td>
              <td class="px-4 py-3">
                @if($status === 'completed')
                  <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                @elseif($status === 'failed')
                  <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">Error</span>
                @else
                  <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Not analyzed</span>
                @endif
              </td>
              <td class="px-4 py-3">
                @if($c->is_primary)
                  <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">Primary</span>
                @else
                  <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">—</span>
                @endif
              </td>
              <td class="px-4 py-3 text-slate-700">{{ $keywordsCount ? number_format($keywordsCount) : '-' }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $trafficEtv ? number_format($trafficEtv) : '-' }}</td>
              <td class="px-4 py-3 text-slate-700">-</td>
              <td class="px-4 py-3 text-slate-500">{{ $latest?->created_at?->diffForHumans() ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-4 py-6 text-center text-slate-500">No competitors yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
```

### Controller data needed

Make sure the analytics controller loads competitors + latest advanced session.

```php
$competitors = $website->competitors()
  ->with(['advancedSessions' => fn($q) => $q->latest()])
  ->orderByDesc('is_primary')
  ->get();
```

---

## 2) New page: Analytics Gaps

**Route:** `GET /websites/{website}/analytics-gaps`

* Optional query: `?competitor_id=` (default = primary)

### Header UX

* Title: **Analytics Gaps**
* Sub: “Compare your GSC positions with competitor ranked keywords.”
* Dropdown competitor (defaults to primary)
* Filter pills:

  * Opportunities
  * Content Gaps
  * We Win
  * Low Priority
* Search input

### Scoreboard (4 cards)

Use these metrics for “kita unggul/keteteran”:

1. **We Win** (count)
2. **Opportunities** (count)
3. **Content Gaps** (count)
4. **Win Rate** = `we_win / (we_win + opportunities + content_gaps)` (percentage)

> (Optional) also show **Median Delta** in a tooltip or secondary line.

---

## 3) Gap Logic (final)

Definitions:

* `competitor_rank` = `rank_group` (required)
* `our_rank` = GSC avg position (nullable)
* `delta = our_rank - competitor_rank`

  * **delta > 0** → **Behind** (kita kalah)
  * **delta < 0** → **Ahead** (kita unggul)

Classification:

* **Opportunity**:

  * `(our_rank !== null && competitor_rank < our_rank && competitor_rank <= 30)`
  * OR `(our_rank === null && competitor_rank <= 30)`
* **We Win**:

  * `(our_rank !== null && our_rank < competitor_rank)`
* **Content Gap**:

  * `(our_rank === null && competitor_rank <= 50)`
* **Low Priority**:

  * `(our_rank === null && competitor_rank > 50)` OR `(abs(delta) <= 2)` OR “everything else”

---

## 4) Join key normalization

Create a shared normalize helper used by both datasets:

```php
function normalizeKeyword(string $k): string {
  $k = mb_strtolower($k);
  $k = trim($k);
  $k = preg_replace('/\s+/u', ' ', $k);
  return $k;
}
```

Store `keyword_normalized` in memory for join; optional: add a DB column later if you want fast joins.

---

## 5) Data mapping from DataForSEO JSON

For each `item` in `result[0].items`:

* `keyword` = `keyword_data.keyword`
* `volume` = `keyword_data.keyword_info.search_volume`
* `competitor_rank` = `ranked_serp_element.serp_item.rank_group`
* `competitor_rank_absolute` = `ranked_serp_element.serp_item.rank_absolute` (nullable)
* `competitor_url` = `ranked_serp_element.serp_item.url`
* `competitor_etv` = `ranked_serp_element.serp_item.etv`
* `is_new` = `ranked_serp_element.serp_item.rank_changes.is_new`

---

## 6) GSC data required (our positions)

You need a map: `normalized_query => avg_position`

Example service return shape:

```php
[
  'rest area' => 12.4,
  'arab saudi' => 7.9,
  // ...
]
```

If you already store GSC queries in DB, just query and map. Otherwise call GSC API and cache.

---

## 7) Controller skeleton: AnalyticsGapsController

**File:** `app/Http/Controllers/WebsiteAnalyticsGapsController.php`

```php
public function show(Request $request, Website $website)
{
  $competitors = $website->competitors()->orderByDesc('is_primary')->get();

  $competitorId = $request->integer('competitor_id')
    ?: optional($competitors->firstWhere('is_primary', true))->id
    ?: optional($competitors->first())->id;

  abort_unless($competitorId, 404);

  $competitor = $competitors->firstWhere('id', $competitorId);

  // Latest completed session
  $session = $competitor->advancedSessions()
    ->where('status', 'completed')
    ->latest()
    ->first();

  // If no session, show empty state + CTA to run
  $ranked = collect();
  if ($session) {
    $ranked = $competitor->rankedKeywords()
      ->where('session_id', $session->id)
      ->select(['keyword','keyword_normalized','search_volume','rank_group','url','etv'])
      ->orderByDesc('search_volume')
      ->limit(500) // guardrail
      ->get();
  }

  // Our GSC positions map
  $ourMap = app(GscQueryPositionService::class)
    ->getAvgPositionsMap($website); // normalized => float

  // Build gaps rows
  $rows = $ranked->map(function($r) use ($ourMap) {
    $ourRank = $ourMap[$r->keyword_normalized] ?? null;
    $compRank = (int) $r->rank_group;
    $delta = $ourRank === null ? null : ($ourRank - $compRank);

    $type = 'low_priority';

    if ($ourRank === null && $compRank <= 30) {
      $type = 'opportunity';
    } elseif ($ourRank !== null && $compRank < $ourRank && $compRank <= 30) {
      $type = 'opportunity';
    } elseif ($ourRank !== null && $ourRank < $compRank) {
      $type = 'we_win';
    } elseif ($ourRank === null && $compRank <= 50) {
      $type = 'content_gap';
    }

    if ($delta !== null && abs($delta) <= 2) {
      $type = 'low_priority';
    }

    return [
      'keyword' => $r->keyword,
      'volume' => (int) $r->search_volume,
      'our_rank' => $ourRank,
      'competitor_rank' => $compRank,
      'delta' => $delta,
      'competitor_url' => $r->url,
      'etv' => $r->etv,
      'type' => $type,
    ];
  });

  // Filters
  $filter = $request->string('filter')->toString();
  if (in_array($filter, ['opportunity','content_gap','we_win','low_priority'], true)) {
    $rows = $rows->where('type', $filter)->values();
  }

  $search = trim((string) $request->get('q', ''));
  if ($search !== '') {
    $s = mb_strtolower($search);
    $rows = $rows->filter(fn($x) => str_contains(mb_strtolower($x['keyword']), $s))->values();
  }

  // Scoreboard
  $weWin = $rows->where('type','we_win')->count();
  $opp = $rows->where('type','opportunity')->count();
  $gap = $rows->where('type','content_gap')->count();
  $den = max(1, ($weWin + $opp + $gap));
  $winRate = $weWin / $den;

  $deltas = $rows->pluck('delta')->filter(fn($d) => $d !== null)->sort()->values();
  $medianDelta = $deltas->count() ? $deltas->get((int) floor(($deltas->count()-1)/2)) : null;

  // Default sort: volume desc then delta desc (behind biggest first)
  $rows = $rows->sortBy([
    fn($a) => -$a['volume'],
    fn($a) => $a['delta'] === null ? -999999 : -$a['delta'],
  ])->values();

  return view('websites.analytics-gaps', compact(
    'website','competitors','competitor','session','rows','filter','search',
    'weWin','opp','gap','winRate','medianDelta'
  ));
}
```

> Notes:

* Add DB column `keyword_normalized` to `ane_competitor_ranked_keywords` (recommended) so you don’t normalize on every request.
* `limit(500)` is a safe guardrail. Later you can add pagination.

---

## 8) Blade: `resources/views/websites/analytics-gaps.blade.php`

Core layout:

* Header + dropdown competitor
* Filter pills + search
* Scoreboard cards
* Opportunity table (works for all filters)

Delta badge rendering:

* `delta === null` → `—`
* `delta > 0` → `Behind +{delta}` (orange/red)
* `delta < 0` → `Ahead {delta}` (green)

Action column:

* `Save Target`
* `Generate`

---

## 9) Routes

```php
Route::get('/websites/{website}/analytics-gaps', [WebsiteAnalyticsGapsController::class, 'show'])
  ->name('websites.analytics.gaps');
```

---

## 10) Empty states

If competitor has no completed session:

* Show message: “Primary competitor hasn’t been analyzed yet.”
* Provide CTA to run advanced analysis (existing endpoint):

  * `POST /competitors/{competitor}/advanced-ranked-keywords/run`

---

## 11) Next performance upgrades (optional)

* Cache `ourMap` per website for 1–6 hours.
* Store `keyword_normalized` in DB.
* Materialize “gap rows” into a table if you want super fast UI and trending deltas over time.

---

## What you implement now (minimum viable)

1. Add “Your Competitors” section to website analytics.
2. Add route + controller + view for analytics gaps.
3. Join by normalized keyword.
4. Scoreboard + table + filters + search.
