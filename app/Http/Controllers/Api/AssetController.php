<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Get asset details for editor
     */
    public function show(SocialAsset $asset)
    {
        // Authorization check
        if ($asset->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
            'asset' => [
                'id' => $asset->id,
                'post_id' => $asset->post_id,
                'channel' => $asset->channel,
                'asset_type' => $asset->asset_type,
                'copy_text' => $asset->copy_text,
                'canvas_json' => $asset->canvas_json,
                'visual_path' => $asset->visual_path,
                'visual_url' => $visualUrl,
            ]
        ]);
    }

    /**
     * Update asset visual from editor
     */
    public function updateVisual(Request $request, SocialAsset $asset)
    {
        // Authorization check
        if ($asset->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'canvas_json' => 'required|array',
            'image_data' => 'required|string', // base64 image
        ]);

        // Save canvas JSON
        $asset->canvas_json = $validated['canvas_json'];

        // Decode and save base64 image
        $imageData = $validated['image_data'];

        // Remove data:image/png;base64, prefix if present
        if (strpos($imageData, 'data:image') === 0) {
            $imageData = explode(',', $imageData)[1];
        }

        $imageContent = base64_decode($imageData);

        // Generate filename
        $filename = 'social-assets/' . $asset->channel . '-' . $asset->asset_type . '-' . uniqid() . '.png';

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('social-assets');

        // Save file
        Storage::disk('public')->put($filename, $imageContent);

        // Update asset
        $asset->visual_path = $filename;
        $asset->save();

        return response()->json([
            'success' => true,
            'message' => 'Asset updated successfully',
            'visual_url' => asset('storage/' . $filename),
        ]);
    }
}
