<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InsightController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $insights = Insight::where('created_by', auth()->id())
            ->with('website')
            ->latest()
            ->paginate(20);

        return view('insights.index', compact('insights'));
    }

    public function create()
    {
        return view('insights.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:100',
            'source_url' => 'nullable|url|max:1000',
            'source_type' => 'nullable|in:manual,excel,ai_generated,google_trends,news_search,social_insight',
            'website_id' => 'nullable|exists:ane_websites,id',
        ]);

        $insight = Insight::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('insights.index')->with('success', 'Insight created successfully.');
    }

    public function show(Insight $insight)
    {
        $this->authorize('view', $insight);

        $insight->load('posts', 'website');

        return view('insights.show', compact('insight'));
    }

    public function edit(Insight $insight)
    {
        $this->authorize('update', $insight);

        return view('insights.edit', compact('insight'));
    }

    public function update(Request $request, Insight $insight)
    {
        $this->authorize('update', $insight);

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:100',
            'source_url' => 'nullable|url|max:1000',
        ]);

        $insight->update($validated);

        return redirect()->route('insights.index')->with('success', 'Insight updated successfully.');
    }

    public function destroy(Insight $insight)
    {
        $this->authorize('delete', $insight);

        $insight->delete();

        return redirect()->route('insights.index')->with('success', 'Insight deleted successfully.');
    }

    /**
     * Bulk delete insights (JSON)
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:ane_insights,id',
        ]);

        $userId = auth()->id();
        $insights = Insight::whereIn('id', $validated['ids'])->get();

        $deleted = 0;
        foreach ($insights as $insight) {
            if ($insight->created_by === $userId) {
                $insight->delete();
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
