<?php

namespace App\Http\Controllers;

use App\Models\DistributionSchedule;
use App\Models\PostTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CalendarController extends Controller
{
    use AuthorizesRequests;

    /**
     * Channel color mapping
     */
    protected array $channelColors = [
        'instagram' => '#E1306C', // Instagram pink
        'facebook' => '#1877F2',   // Facebook blue
        'twitter' => '#000000',    // X black
        'linkedin' => '#0A66C2',   // LinkedIn blue
    ];

    /**
     * Display calendar view
     */
    public function index()
    {
        // Load user's websites for WordPress filter dropdown
        $websites = \App\Models\Website::where('created_by', auth()->id())->get();

        return view('calendar.index', compact('websites'));
    }

    /**
     * Get events for FullCalendar (API endpoint)
     */
    public function getEvents(Request $request)
    {
        $userTimezone = auth()->user()->timezone ?? 'UTC';

        // Debug logging
        Log::info('Calendar getEvents called', [
            'user_id' => auth()->id(),
            'timezone' => $userTimezone,
            'start' => $request->start,
            'end' => $request->end,
            'channel' => $request->channel,
            'status' => $request->status,
        ]);

        $query = DistributionSchedule::where('created_by', auth()->id())
            ->with(['asset.post:id,title', 'asset:id,post_id,channel,asset_type,copy_text,status'])
            ->whereNotNull('scheduled_at');

        // Filter by date range (FullCalendar sends start/end params)
        if ($request->filled('start')) {
            $query->where('scheduled_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->where('scheduled_at', '<=', $request->end);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('channel', $request->channel);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('publish_status', $request->status);
        }

        $schedules = $query->get();

        Log::info('Calendar query result', [
            'count' => $schedules->count(),
            'user_id' => auth()->id(),
        ]);

        // Transform to FullCalendar event format
        // Send UTC data, let FullCalendar handle timezone conversion
        $events = $schedules->map(function ($schedule) use ($userTimezone) {
            $asset = $schedule->asset;
            $post = $asset->post ?? null;

            // Base color from channel (always use channel color)
            $baseColor = $this->channelColors[$asset->channel] ?? '#6366f1';

            return [
                'id' => $schedule->id,
                'title' => $post->title ?? 'Untitled',
                // Send UTC ISO string, FullCalendar will convert based on timeZone setting
                'start' => $schedule->scheduled_at->toISOString(),
                'backgroundColor' => $baseColor,
                'borderColor' => $baseColor,
                'classNames' => ['calendar-event-compact'], // Custom CSS class
                'extendedProps' => [
                    'channel' => $asset->channel,
                    'asset_type' => $asset->asset_type,
                    'status' => $schedule->publish_status,
                    'post_id' => $post->id ?? null,
                    'copy_preview' => \Illuminate\Support\Str::limit($asset->copy_text, 100),
                    'timezone' => $userTimezone,
                    'utc_time' => $schedule->scheduled_at->toISOString(),
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Get WordPress scheduled posts for calendar
     * NEW METHOD - WordPress events as separate source
     */
    public function getWordPressEvents(Request $request)
    {
        $userTimezone = auth()->user()->timezone ?? 'UTC';

        Log::info('Calendar getWordPressEvents called', [
            'user_id' => auth()->id(),
            'timezone' => $userTimezone,
            'start' => $request->start,
            'end' => $request->end,
            'website' => $request->website,
        ]);

        // Query PostTargets with scheduled AND published status (untuk dokumentasi)
        $query = \App\Models\PostTarget::whereHas('post', function($q) {
                $q->where('created_by', auth()->id());
            })
            ->with(['post:id,title', 'website:id,name'])
            ->whereIn('status', ['scheduled', 'published'])
            ->whereNotNull('scheduled_at');

        // Filter by date range (FullCalendar sends start/end params)
        if ($request->filled('start')) {
            $query->where('scheduled_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->where('scheduled_at', '<=', $request->end);
        }

        // Filter by website (dropdown)
        if ($request->filled('website')) {
            $query->where('website_id', $request->website);
        }

        $targets = $query->get();

        Log::info('Calendar WordPress query result', [
            'count' => $targets->count(),
            'user_id' => auth()->id(),
        ]);

        // Transform to FullCalendar event format
        $events = $targets->map(function ($target) use ($userTimezone) {
            $post = $target->post;
            $website = $target->website;

            // Color based on status: Scheduled = Green, Published = WordPress Blue
            $baseColor = $target->status === 'published' ? '#3858e9' : '#10B981';

            // IMPORTANT: Database stores UTC, explicitly set timezone to UTC
            $utcTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $target->scheduled_at->format('Y-m-d H:i:s'), 'UTC');
            $localTime = $utcTime->copy()->setTimezone($userTimezone);
            $formattedStart = $localTime->format('Y-m-d\TH:i:s');

            Log::info('WordPress event formatted', [
                'target_id' => $target->id,
                'raw_db' => $target->scheduled_at,
                'utc_parsed' => $utcTime->toIso8601String(),
                'local_time' => $localTime->format('Y-m-d H:i:s T'),
                'formatted_start' => $formattedStart,
            ]);

            return [
                'id' => 'wp-' . $target->id, // Prefix to distinguish from social events
                'title' => $post->title ?? 'Untitled',
                // CRITICAL: Send WITHOUT timezone offset so FullCalendar uses timeZone config
                // Format: "2026-01-08T08:00:00" (user's local time, no offset)
                'start' => $formattedStart,
                'backgroundColor' => $baseColor,
                'borderColor' => $baseColor,
                'classNames' => ['calendar-event-compact'],
                'extendedProps' => [
                    'type' => 'wordpress',
                    'post_id' => $post->id ?? null,
                    'website_id' => $target->website_id,
                    'website_name' => $website->name ?? 'Unknown',
                    'wp_post_id' => $target->wp_post_id,
                    'status' => $target->status,
                    'timezone' => $userTimezone,
                    'utc_time' => $utcTime->toISOString(),
                    'local_time' => $localTime->format('Y-m-d H:i:s'),
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Reschedule a single event (drag & drop)
     */
    public function reschedule(Request $request, DistributionSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $oldTime = $schedule->scheduled_at->copy();

        $schedule->update([
            'scheduled_at' => $validated['scheduled_at'], // Already in UTC from frontend
            'queued_at' => null, // Reset queue flag
            'publish_status' => 'pending', // Reset status if was failed
            'last_error' => null,
        ]);

        Log::info('Schedule rescheduled via calendar', [
            'schedule_id' => $schedule->id,
            'old_time' => $oldTime->toISOString(),
            'new_time' => $schedule->scheduled_at->toISOString(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'scheduled_at' => $schedule->scheduled_at->toISOString(),
            ],
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy(DistributionSchedule $schedule)
    {
        $this->authorize('delete', $schedule);

        Log::info('Schedule deleted via calendar', [
            'schedule_id' => $schedule->id,
            'user_id' => auth()->id(),
        ]);

        $schedule->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Bulk reschedule multiple events
     */
    public function bulkReschedule(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'exists:ane_distribution_schedule,id',
            'action' => 'required|in:delay,advance',
            'hours' => 'required|integer|min:1|max:168', // Max 1 week
        ]);

        $schedules = DistributionSchedule::whereIn('id', $validated['schedule_ids'])
            ->where('created_by', auth()->id())
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No schedules found or access denied',
            ], 403);
        }

        $modifier = $validated['action'] === 'delay' ? 1 : -1;
        $hours = $validated['hours'] * $modifier;

        foreach ($schedules as $schedule) {
            $newTime = $schedule->scheduled_at->copy()->addHours($hours);

            $schedule->update([
                'scheduled_at' => $newTime,
                'queued_at' => null,
                'publish_status' => 'pending',
                'last_error' => null,
            ]);
        }

        Log::info('Bulk reschedule via calendar', [
            'count' => $schedules->count(),
            'action' => $validated['action'],
            'hours' => $validated['hours'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'updated_count' => $schedules->count(),
        ]);
    }

    /**
     * Get calendar statistics
     */
    public function getStats(Request $request)
    {
        $userId = auth()->id();
        $startDate = $request->get('start', now()->startOfMonth()->toDateTimeString());
        $endDate = $request->get('end', now()->endOfMonth()->toDateTimeString());

        $schedules = DistributionSchedule::where('created_by', $userId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with('asset:id,channel')
            ->get();

        $byChannel = $schedules->groupBy('asset.channel')->map->count()->toArray();
        $byStatus = $schedules->groupBy('publish_status')->map->count()->toArray();
        $byDayOfWeek = $schedules->groupBy(function ($s) {
            return $s->scheduled_at->dayOfWeek; // 0=Sun, 6=Sat
        })->map->count()->sortKeys()->toArray();

        $hourCounts = $schedules->groupBy(function ($s) {
            return $s->scheduled_at->hour;
        })->map->count()->sortByDesc(function ($count) {
            return $count;
        });

        $stats = [
            'total' => $schedules->count(),
            'by_channel' => $byChannel,
            'by_status' => $byStatus,
            'by_day_of_week' => $byDayOfWeek,
            'peak_hour' => $hourCounts->keys()->first(),
        ];

        return response()->json($stats);
    }

    /**
     * Reschedule WordPress post from calendar
     */
    public function rescheduleWordPress(Request $request, PostTarget $target)
    {
        // Authorization: User must own the post
        $this->authorize('update', $target->post);

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        // Update scheduled_at (already in UTC from frontend)
        $target->update([
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        Log::info('WordPress post rescheduled via calendar', [
            'target_id' => $target->id,
            'post_id' => $target->post_id,
            'new_scheduled_at' => $validated['scheduled_at'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
        ]);
    }
}
