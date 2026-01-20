<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageEditorController extends Controller
{
    public function index()
    {
        return view('image-editor.index');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $path = $request->file('image')->store('editor-uploads', 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'filename' => $request->file('image')->getClientOriginalName(),
        ]);
    }

    public function removeBackground(Request $request)
    {
        try {
            $request->validate([
                'image_data' => 'required|string', // base64 image data
            ]);

            $imageData = $request->input('image_data');

            // Call AI Service (pass base64 directly)
            $aiService = new AIService();
            $result = $aiService->removeBackground($imageData);

            // Download result image
            $resultContent = file_get_contents($result['image_url']);

            if (!$resultContent) {
                throw new \Exception('Failed to download processed image');
            }

            // Save result
            $filename = 'rembg-' . time() . '-' . uniqid() . '.png';
            $path = 'editor-uploads/' . $filename;
            Storage::disk('public')->put($path, $resultContent);

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'provider' => $result['provider'],
            ]);

        } catch (\Exception $e) {
            Log::error('Remove background error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveDesign(Request $request)
    {
        $request->validate([
            'design_data' => 'required|json',
            'preview_image' => 'required|string', // base64 image
        ]);

        // TODO: Save design JSON + preview to database
        // For POC, just return success

        return response()->json([
            'success' => true,
            'message' => 'Design saved successfully',
        ]);
    }
}
