<?php

namespace App\Http\Controllers;

use App\Services\UnsplashService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnsplashController extends Controller
{
    protected $unsplashService;

    public function __construct(UnsplashService $unsplashService)
    {
        $this->unsplashService = $unsplashService;
    }

    /**
     * Search photos on Unsplash
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:30',
        ]);

        $result = $this->unsplashService->searchPhotos(
            $validated['query'],
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 20
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'total' => $result['total'],
            'total_pages' => $result['total_pages'],
            'results' => $result['results']
        ]);
    }

    /**
     * Trigger download tracking (required by Unsplash API guidelines)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function download(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'download_location' => 'required|url',
        ]);

        $success = $this->unsplashService->triggerDownload($validated['download_location']);

        return response()->json([
            'success' => $success
        ]);
    }

    /**
     * Save Unsplash photo to local media library (as URL reference)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveToLibrary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unsplash_id' => 'required|string',
            'url' => 'required|url',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'photographer_name' => 'required|string|max:255',
            'photographer_url' => 'required|url',
            'download_location' => 'required|url',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
        ]);

        // Trigger download tracking for Unsplash
        $this->unsplashService->triggerDownload($validated['download_location']);

        // Save to local media library as URL reference
        $media = \App\Models\Media::create([
            'user_id' => auth()->id(),
            'file_name' => $validated['unsplash_id'] . '.jpg',
            'file_path' => $validated['url'], // Store Unsplash URL (hotlink)
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_size' => 0, // Unknown for hotlinked images
            'title' => $validated['title'] ?? 'Unsplash Photo',
            'alt_text' => $validated['alt_text'] ?? '',
            'description' => $validated['description'] ?? '',
            'caption' => 'Photo by ' . $validated['photographer_name'] . ' on Unsplash',
            'source_type' => 'unsplash',
            'ai_prompt' => json_encode([
                'photographer_name' => $validated['photographer_name'],
                'photographer_url' => $validated['photographer_url'],
                'unsplash_id' => $validated['unsplash_id'],
                'width' => $validated['width'] ?? null,
                'height' => $validated['height'] ?? null,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'title' => $media->title,
                'full_url' => $media->file_path, // Returns Unsplash URL
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'source_type' => $media->source_type,
            ]
        ]);
    }
}
