<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Website;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    /**
     * Display keyword dashboard
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $websiteId = $request->get('website_id');

        $query = Keyword::with('website')
            ->byUserWebsites()
            ->orderBy('priority', 'desc')
            ->orderBy('volume', 'desc');

        // Filter by website
        if ($websiteId) {
            $query->where('website_id', $websiteId);
        }

        // Apply filters based on tab
        switch ($tab) {
            case 'high_priority':
                $query->highPriority();
                break;
            case 'active_tracking':
                $query->activeTracking();
                break;
            case 'in_google':
                $query->inGoogle();
                break;
            case 'targets':
                $query->fromSource('deep_research')
                      ->whereNull('current_position');
                break;
        }

        $keywords = $query->paginate(20);

        // Stats (apply website filter)
        $statsQuery = Keyword::byUserWebsites();
        if ($websiteId) {
            $statsQuery = $statsQuery->where('website_id', $websiteId);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'high_priority' => (clone $statsQuery)->highPriority()->count(),
            'active_tracking' => (clone $statsQuery)->activeTracking()->count(),
            'in_google' => (clone $statsQuery)->inGoogle()->count(),
            'targets' => (clone $statsQuery)->fromSource('deep_research')->whereNull('current_position')->count(),
        ];

        $websites = Website::where('created_by', auth()->id())->get();

        return view('keywords.index', compact('keywords', 'stats', 'tab', 'websites'));
    }

    /**
     * Update keyword priority
     */
    public function updatePriority(Request $request, Keyword $keyword)
    {
        // Authorization check
        if ($keyword->website->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'priority' => 'required|in:high,medium,low,none',
        ]);

        $keyword->update(['priority' => $validated['priority']]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully',
        ]);
    }

    /**
     * Update tracking status
     */
    public function updateTrackingStatus(Request $request, Keyword $keyword)
    {
        // Authorization check
        if ($keyword->website->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'tracking_status' => 'required|in:active,paused,archived',
        ]);

        $keyword->update(['tracking_status' => $validated['tracking_status']]);

        return response()->json([
            'success' => true,
            'message' => 'Tracking status updated successfully',
        ]);
    }

    /**
     * Update target position
     */
    public function updateTargetPosition(Request $request, Keyword $keyword)
    {
        // Authorization check
        if ($keyword->website->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'target_position' => 'required|integer|min:1|max:100',
        ]);

        $keyword->update(['target_position' => $validated['target_position']]);

        return response()->json([
            'success' => true,
            'message' => 'Target position updated successfully',
        ]);
    }

    /**
     * Delete keyword
     */
    public function destroy(Keyword $keyword)
    {
        // Authorization check
        if ($keyword->website->created_by !== auth()->id()) {
            abort(403);
        }

        $keyword->delete();

        return redirect()->route('keywords.index')
            ->with('success', 'Keyword deleted successfully');
    }

    /**
     * Save keywords from Deep Research
     */
    public function saveFromDeepResearch(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|array',
            'keywords.*.keyword' => 'required|string',
            'keywords.*.website_id' => 'required|exists:ane_websites,id',
            'keywords.*.volume' => 'nullable|integer',
            'keywords.*.difficulty' => 'nullable|integer',
            'keywords.*.intent' => 'nullable|string',
            'keywords.*.competition_index' => 'nullable|integer',
            'keywords.*.priority' => 'nullable|in:high,medium,low',
            'keywords.*.target_position' => 'nullable|integer|min:1|max:100',
        ]);

        $saved = 0;
        $updated = 0;

        foreach ($validated['keywords'] as $keywordData) {
            // Verify website ownership
            $website = Website::find($keywordData['website_id']);
            if (!$website || $website->created_by !== auth()->id()) {
                continue;
            }

            // Check if keyword already exists
            $existing = Keyword::where('website_id', $keywordData['website_id'])
                ->where('keyword', $keywordData['keyword'])
                ->first();

            if ($existing) {
                // Update existing keyword
                $existing->update([
                    'has_deep_research_data' => true,
                    'priority' => $keywordData['priority'] ?? $existing->priority,
                    'target_position' => $keywordData['target_position'] ?? $existing->target_position,
                    'volume' => $keywordData['volume'] ?? $existing->volume,
                    'difficulty' => $keywordData['difficulty'] ?? $existing->difficulty,
                    'intent' => $keywordData['intent'] ?? $existing->intent,
                    'competition_index' => $keywordData['competition_index'] ?? $existing->competition_index,
                ]);
                $updated++;
            } else {
                // Create new keyword
                Keyword::create([
                    'website_id' => $keywordData['website_id'],
                    'keyword' => $keywordData['keyword'],
                    'source' => 'deep_research',
                    'volume' => $keywordData['volume'] ?? 0,
                    'difficulty' => $keywordData['difficulty'] ?? 0,
                    'intent' => $keywordData['intent'] ?? null,
                    'competition_index' => $keywordData['competition_index'] ?? null,
                    'priority' => $keywordData['priority'] ?? 'high',
                    'target_position' => $keywordData['target_position'] ?? 10,
                    'tracking_status' => 'active',
                    'has_deep_research_data' => true,
                    'first_tracked_at' => now(),
                ]);
                $saved++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Saved {$saved} new keywords, updated {$updated} existing keywords",
            'saved' => $saved,
            'updated' => $updated,
        ]);
    }
}
