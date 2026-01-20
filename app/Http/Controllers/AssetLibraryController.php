<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SocialAsset;
use App\Models\UserBranding;
use App\Models\DistributionSchedule;
use App\Jobs\PublishScheduledAssetJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AssetLibraryController extends Controller
{
    /**
     * Display all social assets for authenticated user
     */
    public function index(Request $request)
    {
        $query = SocialAsset::where('created_by', auth()->id())
            ->with(['post:id,title'])
            ->orderBy('created_at', 'desc');

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by keyword (in copy_text or post title)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('copy_text', 'like', "%{$search}%")
                  ->orWhereHas('post', function ($postQuery) use ($search) {
                      $postQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $assets = $query->paginate(24);

        // Get user branding presets for Visual Editor
        $brandings = UserBranding::where('user_id', auth()->id())
            ->with('logo')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('assets.index', compact('assets', 'brandings'));
    }

    /**
     * Get asset data for API (for AJAX requests)
     */
    public function getData(Request $request)
    {
        $query = SocialAsset::where('created_by', auth()->id())
            ->with(['post:id,title'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('copy_text', 'like', "%{$search}%")
                  ->orWhereHas('post', function ($postQuery) use ($search) {
                      $postQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $assets = $query->paginate(24);

        return response()->json([
            'success' => true,
            'assets' => $assets,
        ]);
    }

    /**
     * Get asset details for popup modal
     */
    public function details(SocialAsset $asset)
    {
        // Authorization check
        if ($asset->created_by !== auth()->id()) {
            abort(403);
        }

        $asset->load('post:id,title');

        $lastSchedule = DistributionSchedule::where('asset_id', $asset->id)
            ->orderByDesc('created_at')
            ->first();

        // Handle external URLs (Unsplash, etc) vs local storage paths
        $visualUrl = null;
        if (!empty($asset->visual_path)) {
            if (str_starts_with($asset->visual_path, 'http://') || str_starts_with($asset->visual_path, 'https://')) {
                // External URL, use directly
                $visualUrl = $asset->visual_path;
            } else {
                // Local storage file
                $visualUrl = asset('storage/' . $asset->visual_path);
            }
        }

        return response()->json([
            'success' => true,
            'asset' => [
                'id' => $asset->id,
                'post_title' => $asset->post->title ?? 'Untitled',
                'copy_text' => $asset->copy_text,
                'hashtags' => $asset->hashtags,
                'channel' => $asset->channel,
                'status' => $asset->status,
                'created_at' => $asset->created_at->diffForHumans(),
                'visual_url' => $visualUrl,
                'last_schedule' => $lastSchedule ? [
                    'id' => $lastSchedule->id,
                    'status' => $lastSchedule->publish_status,
                    'scheduled_at' => optional($lastSchedule->scheduled_at)->toDateTimeString(),
                    'published_at' => optional($lastSchedule->published_at)->toDateTimeString(),
                    'last_error' => $lastSchedule->last_error,
                ] : null,
                'schedule_suggest' => now()->addMinutes(30)->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    /**
     * Update asset (auto-save from modal)
     */
    public function update(Request $request, SocialAsset $asset)
    {
        // Authorization check
        if ($asset->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'copy_text' => 'nullable|string',
            'hashtags' => 'nullable|string',
        ]);

        $asset->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset updated successfully',
        ]);
    }

    /**
     * Create a publish schedule for an asset
     */
    public function schedule(Request $request, SocialAsset $asset)
    {
        if ($asset->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'publish_now' => 'sometimes|boolean',
        ]);

        $publishNow = $validated['publish_now'] ?? false;

        // Convert user's local time to UTC for database storage
        if ($publishNow) {
            $scheduledAt = now();
        } else {
            $userTimezone = auth()->user()->timezone ?? 'UTC';
            $inputTime = $validated['scheduled_at'] ?? now()->addMinutes(5);

            // Parse input as user's timezone, then convert to UTC
            $scheduledAt = \Carbon\Carbon::parse($inputTime, $userTimezone)->setTimezone('UTC');
        }

        $schedule = DistributionSchedule::create([
            'asset_id' => $asset->id,
            'created_by' => $asset->created_by,
            'scheduled_at' => $scheduledAt,
            'publish_status' => 'pending',
        ]);

        // If publish now, dispatch job immediately instead of waiting for scheduler
        if ($publishNow) {
            $schedule->update(['queued_at' => now()]);
            PublishScheduledAssetJob::dispatch($schedule->id)->onQueue('social-publish');
        }

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'scheduled_at' => $schedule->scheduled_at->toDateTimeString(),
                'status' => $schedule->publish_status,
            ],
            'message' => $publishNow ? 'Publishing now...' : 'Schedule created',
        ]);
    }

    /**
     * Delete asset
     */
    public function delete(SocialAsset $asset)
    {
        // Authorization check
        if ($asset->created_by !== auth()->id()) {
            abort(403);
        }

        // Delete visual file if exists
        if ($asset->visual_path && Storage::disk('public')->exists($asset->visual_path)) {
            Storage::disk('public')->delete($asset->visual_path);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully',
        ]);
    }

    /**
     * Export pack for specific post
     */
    public function exportPack(Post $post)
    {
        // Authorization check
        if ($post->created_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Get all assets for this post
        $assets = SocialAsset::where('post_id', $post->id)
            ->where('created_by', auth()->id())
            ->get();

        if ($assets->isEmpty()) {
            return back()->with('error', 'No assets found for this post');
        }

        // Get DNA data
        $dna = $post->contentDNA;

        // Create temp directory
        $tempDir = storage_path('app/temp/' . uniqid('export_'));
        @mkdir($tempDir, 0755, true);

        // Create ZIP file
        $zipFileName = 'social-pack-' . $post->id . '-' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create ZIP file');
        }

        // Add PNGs (all sizes)
        foreach ($assets as $asset) {
            if ($asset->visual_path && Storage::disk('public')->exists($asset->visual_path)) {
                $filePath = Storage::disk('public')->path($asset->visual_path);
                $fileName = sprintf(
                    'visuals/%s-%s.png',
                    $asset->channel,
                    $asset->asset_type
                );
                $zip->addFile($filePath, $fileName);
            }
        }

        // Add captions.txt per channel
        foreach ($assets->groupBy('channel') as $channel => $channelAssets) {
            $captionContent = "=== {$channel} Captions ===\n\n";

            foreach ($channelAssets as $asset) {
                $captionContent .= "--- {$asset->asset_type} (v{$asset->copy_version}) ---\n";
                $captionContent .= $asset->copy_text . "\n\n";
                $captionContent .= "Characters: " . mb_strlen($asset->copy_text) . "\n";
                $captionContent .= "Status: {$asset->status}\n";
                $captionContent .= "---\n\n";
            }

            $zip->addFromString("captions/{$channel}.txt", $captionContent);
        }

        // Add metadata.json (DNA + stats)
        $metadata = [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'created_at' => $post->created_at->toISOString(),
            ],
            'dna' => $dna ? [
                'headline' => $dna->headline,
                'subheadline' => $dna->subheadline,
                'hot_take' => $dna->hot_take,
                'cta' => $dna->cta,
                'key_points' => $dna->key_points,
                'keywords' => $dna->keywords,
                'summary_short' => $dna->summary_short,
                'summary_medium' => $dna->summary_medium,
                'summary_long' => $dna->summary_long,
                'tone' => $dna->tone,
                'intent' => $dna->intent,
            ] : null,
            'assets' => $assets->map(function ($asset) {
                return [
                    'channel' => $asset->channel,
                    'asset_type' => $asset->asset_type,
                    'status' => $asset->status,
                    'copy_length' => mb_strlen($asset->copy_text),
                    'has_visual' => !is_null($asset->visual_path),
                    'visual_size' => $asset->visual_size,
                    'created_at' => $asset->created_at->toISOString(),
                ];
            })->values(),
            'stats' => [
                'total_assets' => $assets->count(),
                'channels' => $assets->pluck('channel')->unique()->values(),
                'total_visuals' => $assets->whereNotNull('visual_path')->count(),
                'exported_at' => now()->toISOString(),
            ],
        ];

        $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

        // Add README
        $readme = "# Social Media Asset Pack\n\n";
        $readme .= "Post: {$post->title}\n";
        $readme .= "Exported: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $readme .= "## Contents\n\n";
        $readme .= "- `/visuals/` - PNG images for each channel\n";
        $readme .= "- `/captions/` - Text captions per channel\n";
        $readme .= "- `metadata.json` - Full DNA data and asset stats\n\n";
        $readme .= "## Channels Included\n\n";
        foreach ($assets->groupBy('channel') as $channel => $channelAssets) {
            $readme .= "- **{$channel}**: {$channelAssets->count()} asset(s)\n";
        }

        $zip->addFromString('README.md', $readme);

        // Close ZIP
        $zip->close();

        // Download and cleanup
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
