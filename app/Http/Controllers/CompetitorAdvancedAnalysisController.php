<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeCompetitorRankedKeywords;
use App\Models\CompetitorAdvancedSession;
use App\Models\CompetitorProfile;
use Illuminate\Http\Request;

class CompetitorAdvancedAnalysisController extends Controller
{
    public function setPrimary(Request $request, CompetitorProfile $competitor)
    {
        $this->authorizeCompetitor($competitor);

        $setPrimary = $request->boolean('is_primary', true);

        if ($setPrimary && !$competitor->is_primary) {
            $count = CompetitorProfile::where('created_by', auth()->id())
                ->where('is_primary', true)
                ->count();

            if ($count >= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Max 2 primary competitors.',
                ], 422);
            }
        }

        $competitor->update(['is_primary' => $setPrimary]);

        return response()->json([
            'success' => true,
            'is_primary' => $competitor->is_primary,
        ]);
    }

    public function run(Request $request, CompetitorProfile $competitor)
    {
        $this->authorizeCompetitor($competitor);

        if (!$competitor->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'Advanced analysis only for primary competitors.',
            ], 422);
        }

        $existing = CompetitorAdvancedSession::where('competitor_id', $competitor->id)
            ->where('status', 'processing')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis already running.',
                'session_id' => $existing->id,
            ], 409);
        }

        $session = CompetitorAdvancedSession::create([
            'competitor_id' => $competitor->id,
            'user_id' => auth()->id(),
            'status' => 'processing',
            'progress' => 0,
            'started_at' => now(),
        ]);

        AnalyzeCompetitorRankedKeywords::dispatch($session->id);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
        ]);
    }

    public function status(CompetitorProfile $competitor)
    {
        $this->authorizeCompetitor($competitor);

        $session = CompetitorAdvancedSession::where('competitor_id', $competitor->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No session found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'status' => $session->status,
            'progress' => $session->progress,
            'error_message' => $session->error_message,
            'summary_metrics' => $session->summary_metrics,
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
}
