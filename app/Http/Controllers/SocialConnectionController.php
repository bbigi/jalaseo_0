<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\DistributionSchedule;
use Illuminate\Http\Request;

class SocialConnectionController extends Controller
{
    public function index(Request $request)
    {
        $accounts = SocialAccount::where('user_id', $request->user()->id)
            ->orderBy('provider')
            ->orderByDesc('id')
            ->get()
            ->groupBy('provider');

        $recentSchedules = DistributionSchedule::with('asset')
            ->where('created_by', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('social.connections', [
            'accounts' => $accounts,
            'recentSchedules' => $recentSchedules,
        ]);
    }
}
