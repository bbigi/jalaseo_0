<?php

namespace App\Http\Controllers;

use App\Jobs\RunHealthCheckJob;
use App\Models\HealthSession;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteHealthController extends Controller
{
    public function run(Request $request, Website $website)
    {
        $session = $website->healthSessions()->create([
            'status' => 'processing',
        ]);

        RunHealthCheckJob::dispatch($session->id);

        return response()->json([
            'session_id' => $session->id,
            'status' => $session->status,
            'queued' => true,
        ]);
    }

    public function latest(Website $website)
    {
        $session = $website->healthSessions()
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$session) {
            return response()->json(['session' => null, 'pages' => []]);
        }

        $pages = $session->pageMetrics()->get();

        return response()->json([
            'session' => $session,
            'pages' => $pages,
        ]);
    }

    public function status(HealthSession $session)
    {
        return response()->json($session);
    }
}

