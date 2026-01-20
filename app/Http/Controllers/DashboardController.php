<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Insight;
use App\Models\Website;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Expect an authenticated user here; no local fallback in production.

        // Fetch statistics (DB uses `created_by` column for ownership)
        $postsCount = Schema::hasTable('ane_posts') ? Post::where('created_by', $user->id)->count() : 0;
        $insightsCount = Schema::hasTable('ane_insights') ? Insight::where('created_by', $user->id)->count() : 0;
        $websitesCount = Schema::hasTable('ane_websites') ? Website::where('created_by', $user->id)->where('status', 'active')->count() : 0;

        return view('dashboard-new', [
            'postsCount' => $postsCount,
            'insightsCount' => $insightsCount,
            'websitesCount' => $websitesCount,
        ]);
    }
}
