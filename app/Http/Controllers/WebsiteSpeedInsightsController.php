<?php

namespace App\Http\Controllers;

use App\Jobs\RunPageSpeedTest;
use App\Models\PageSpeedRun;
use App\Models\Website;
use App\Services\PageSpeedInterpreter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class WebsiteSpeedInsightsController extends Controller
{
    public function show(Website $website, PageSpeedInterpreter $interpreter)
    {
        Gate::authorize('view', $website);

        $strategy = request()->get('strategy', 'mobile');
        $latestRun = PageSpeedRun::where('website_id', $website->id)
            ->where('strategy', $strategy)
            ->latest('created_at')
            ->first();

        $history = PageSpeedRun::where('website_id', $website->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $url = old('url', $latestRun->url ?? $website->url);
        $insights = null;
        if ($latestRun) {
            $rebuilt = $interpreter->build($latestRun);
            $insights = array_merge($latestRun->insights ?? [], $rebuilt);
        }

        $otherStrategy = $strategy === 'mobile' ? 'desktop' : 'mobile';
        $comparisonRun = PageSpeedRun::where('website_id', $website->id)
            ->where('strategy', $otherStrategy)
            ->latest('created_at')
            ->first();
        $comparisonInsight = $comparisonRun ? $interpreter->compare($latestRun, $comparisonRun) : null;

        $trendHistory = PageSpeedRun::where('website_id', $website->id)
            ->where('strategy', $strategy)
            ->orderBy('created_at')
            ->get();
        $trend = $interpreter->trend($trendHistory);

        return view('website-analytics.speed-insights', [
            'website' => $website,
            'latestRun' => $latestRun,
            'history' => $history,
            'strategy' => $strategy,
            'url' => $url,
            'insights' => $insights,
            'comparisonInsight' => $comparisonInsight,
            'trend' => $trend,
        ]);
    }

    public function run(Request $request, Website $website)
    {
        Gate::authorize('view', $website);

        $validated = $request->validate([
            'url' => 'required|url|max:2048',
            'strategy' => 'required|in:mobile,desktop',
        ]);

        // Guard: if a run is already in progress for the same url+strategy, reuse it
        $running = PageSpeedRun::where('website_id', $website->id)
            ->where('url', $validated['url'])
            ->where('strategy', $validated['strategy'])
            ->where('status', 'running')
            ->first();

        if ($running) {
            return $this->respond($request, [
                'message' => 'A test is already running for this URL and strategy.',
                'run_id' => $running->id,
            ], $website);
        }

        $run = PageSpeedRun::create([
            'website_id' => $website->id,
            'url' => $validated['url'],
            'strategy' => $validated['strategy'],
            'status' => 'queued',
            'progress' => 0,
        ]);

        $this->trimRuns($website->id, $validated['strategy']);

        RunPageSpeedTest::dispatch($run);

        return $this->respond($request, [
            'message' => 'Speed test started. Results will appear soon.',
            'run_id' => $run->id,
        ], $website);
    }

    public function status(Website $website)
    {
        Gate::authorize('view', $website);

        $runId = request()->integer('run_id');
        $strategy = request()->get('strategy', 'mobile');

        $run = $runId
            ? PageSpeedRun::where('website_id', $website->id)->find($runId)
            : PageSpeedRun::where('website_id', $website->id)
                ->where('strategy', $strategy)
                ->latest('created_at')
                ->first();

        if (!$run) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $run->status,
            'progress' => $run->progress,
            'run_id' => $run->id,
            'scores' => $run->scores,
            'lab_metrics' => $run->lab_metrics,
            'field_metrics' => $run->field_metrics,
            'opportunities' => $run->opportunities,
            'updated_at' => $run->updated_at,
        ]);
    }

    public function destroy(Website $website, PageSpeedRun $run)
    {
        Gate::authorize('view', $website);

        if ($run->website_id !== $website->id) {
            abort(404);
        }

        $strategy = request()->get('strategy', $run->strategy ?? 'mobile');
        $run->delete();

        return Redirect::route('website-analytics.speed-insights', ['website' => $website, 'strategy' => $strategy])
            ->with('success', __('speed-insights.history.deleted'));
    }

    protected function respond(Request $request, array $payload, Website $website)
    {
        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('website-analytics.speed-insights', [
                'website' => $website,
                'strategy' => $request->input('strategy', 'mobile'),
            ])
            ->with('success', $payload['message']);
    }

    protected function trimRuns(int $websiteId, string $strategy): void
    {
        $idsToKeep = PageSpeedRun::where('website_id', $websiteId)
            ->where('strategy', $strategy)
            ->orderByDesc('created_at')
            ->limit(5)
            ->pluck('id')
            ->all();

        PageSpeedRun::where('website_id', $websiteId)
            ->where('strategy', $strategy)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }
}
