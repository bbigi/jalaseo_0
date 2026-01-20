<?php

namespace App\Services;

use App\Models\ContentDNA;
use App\Models\SocialAsset;
use App\Models\DistributionSchedule;
use Carbon\Carbon;

class SchedulingService
{
    /**
     * Optimal posting times per channel (in hour:minute format, 24h)
     */
    protected array $optimalTimes = [
        'instagram' => ['09:00', '12:00', '19:00'], // Morning, Noon, Evening
        'facebook' => ['13:00', '15:00', '19:00'],  // Early afternoon, Mid afternoon, Evening
        'twitter' => ['08:00', '12:00', '17:00'],   // Morning commute, Lunch, Evening commute
        'linkedin' => ['08:00', '12:00', '17:00'],  // Business hours
    ];

    /**
     * Get smart schedule for assets based on DNA intent
     */
    public function scheduleAssets(ContentDNA $dna, array $assetIds, ?Carbon $startDate = null): array
    {
        // Use user's timezone for scheduling
        $userTimezone = auth()->user()->timezone ?? 'UTC';
        $startDate = $startDate ?? now($userTimezone);
        $assets = SocialAsset::whereIn('id', $assetIds)->get();
        $schedules = [];

        // Breaking news: immediate scheduling
        if ($dna->intent === 'breaking') {
            foreach ($assets as $asset) {
                $schedules[] = $this->createSchedule($asset, $startDate);
            }
            return $schedules;
        }

        // Evergreen: stagger across multiple days
        if ($dna->intent === 'evergreen') {
            $dayOffset = 0;
            foreach ($assets as $asset) {
                $scheduleDate = $startDate->copy()->addDays($dayOffset);
                $schedules[] = $this->createSchedule($asset, $scheduleDate);
                $dayOffset++;
            }
            return $schedules;
        }

        // Default: same day, different optimal times
        foreach ($assets as $index => $asset) {
            $schedules[] = $this->createSchedule($asset, $startDate, $index);
        }

        return $schedules;
    }

    /**
     * Create schedule with optimal time for channel
     */
    protected function createSchedule(SocialAsset $asset, Carbon $date, int $timeIndex = 0): DistributionSchedule
    {
        $optimalTime = $this->getOptimalTime($asset->channel, $timeIndex);

        // Create schedule in user's timezone, then convert to UTC for storage
        $userTimezone = auth()->user()->timezone ?? 'UTC';
        $scheduledAt = $date->copy()->setTimeFromTimeString($optimalTime);

        // Convert to UTC for database storage
        $scheduledAtUTC = $scheduledAt->copy()->setTimezone('UTC');

        return DistributionSchedule::create([
            'asset_id' => $asset->id,
            'created_by' => $asset->created_by,
            'scheduled_at' => $scheduledAtUTC,
            'publish_status' => 'pending',
        ]);
    }

    /**
     * Get optimal posting time for channel
     */
    public function getOptimalTime(string $channel, int $index = 0): string
    {
        $times = $this->optimalTimes[$channel] ?? ['12:00'];
        return $times[$index % count($times)];
    }

    /**
     * Get all optimal times for channel (for suggestions)
     */
    public function getSuggestedTimes(string $channel): array
    {
        return $this->optimalTimes[$channel] ?? ['12:00'];
    }

    /**
     * Bulk schedule multiple assets at once
     */
    public function bulkSchedule(array $assetIds, Carbon $scheduledAt): array
    {
        $assets = SocialAsset::whereIn('id', $assetIds)->get();
        $schedules = [];

        foreach ($assets as $asset) {
            $schedules[] = DistributionSchedule::create([
                'asset_id' => $asset->id,
                'created_by' => $asset->created_by,
                'scheduled_at' => $scheduledAt,
                'publish_status' => 'pending',
            ]);
        }

        return $schedules;
    }

    /**
     * Reschedule existing schedule
     */
    public function reschedule(DistributionSchedule $schedule, Carbon $newDate): DistributionSchedule
    {
        $schedule->update([
            'scheduled_at' => $newDate,
            'publish_status' => 'pending',
        ]);

        return $schedule->fresh();
    }

    /**
     * Auto-schedule based on DNA intent and current time
     */
    public function autoSchedule(ContentDNA $dna, array $assetIds): array
    {
        // Use user's timezone for auto-scheduling
        $userTimezone = auth()->user()->timezone ?? 'UTC';
        $now = now($userTimezone);

        // Breaking: Schedule immediately (within next hour)
        if ($dna->intent === 'breaking') {
            return $this->scheduleAssets($dna, $assetIds, $now->addMinutes(30));
        }

        // Evergreen: Schedule starting tomorrow at optimal times
        if ($dna->intent === 'evergreen') {
            return $this->scheduleAssets($dna, $assetIds, $now->addDay()->setTime(9, 0));
        }

        // Default: Schedule today if before 6 PM, otherwise tomorrow
        $startDate = $now->hour < 18 ? $now : $now->addDay()->setTime(9, 0);
        return $this->scheduleAssets($dna, $assetIds, $startDate);
    }

    /**
     * Get scheduling recommendations
     */
    public function getRecommendations(ContentDNA $dna): array
    {
        $userTimezone = auth()->user()->timezone ?? 'UTC';
        $recommendations = [];

        if ($dna->intent === 'breaking') {
            $recommendations[] = [
                'type' => 'immediate',
                'message' => 'Breaking news detected. Schedule for immediate posting (within 30 mins).',
                'suggested_date' => now($userTimezone)->addMinutes(30),
            ];
        }

        if ($dna->intent === 'evergreen') {
            $recommendations[] = [
                'type' => 'staggered',
                'message' => 'Evergreen content. Recommended: Stagger posts across 3-5 days for maximum reach.',
                'suggested_date' => now($userTimezone)->addDay()->setTime(9, 0),
            ];
        }

        return $recommendations;
    }
}
