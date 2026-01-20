<?php

namespace App\Http\Controllers;

use App\Models\SocialAsset;
use App\Services\SocialCopyService;
use Illuminate\Http\Request;

class SocialAssetController extends Controller
{
    protected $copyService;

    public function __construct(SocialCopyService $copyService)
    {
        $this->copyService = $copyService;
    }

    /**
     * Update social asset (copy text and status)
     */
    public function update(Request $request, SocialAsset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'copy_text' => 'required|string',
            'status' => 'nullable|in:draft,ready,scheduled,published',
        ]);

        $asset->update([
            'copy_text' => $validated['copy_text'],
            'status' => $validated['status'] ?? $asset->status,
        ]);

        return response()->json([
            'success' => true,
            'asset' => $asset,
        ]);
    }

    /**
     * Regenerate social asset (new version with A/B testing)
     */
    public function regenerate(SocialAsset $asset)
    {
        $this->authorize('update', $asset);

        try {
            $newAsset = $this->copyService->regenerateCopy($asset);

            return response()->json([
                'success' => true,
                'asset' => $newAsset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
