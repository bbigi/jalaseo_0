<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $totalUsers = \App\Models\User::where('role', '!=', 'super_admin')->count();
        $activeWebsites = Schema::hasTable('ane_websites') ? \App\Models\Website::where('status', 'active')->count() : 0;
        $totalPosts = Schema::hasTable('ane_posts') ? \App\Models\Post::count() : 0;
        $totalInsights = Schema::hasTable('ane_insights') ? \App\Models\Insight::count() : 0;

        // Get API credential status
        $apiCredentials = Schema::hasTable('ane_api_credentials') ? \App\Models\ApiCredential::all() : collect();
        $configuredProviders = $apiCredentials->where('is_active', true)->count();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'activeWebsites' => $activeWebsites,
            'totalPosts' => $totalPosts,
            'totalInsights' => $totalInsights,
            'apiCredentials' => $apiCredentials,
            'configuredProviders' => $configuredProviders,
        ]);
    }
}
