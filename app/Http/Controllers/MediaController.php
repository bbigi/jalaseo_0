<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::where('user_id', auth()->id())
            ->orWhereNull('user_id');

        // Filter by website
        if ($request->website) {
            $query->where(function($q) use ($request) {
                $q->where('website_id', $request->website)
                  ->orWhereNull('website_id');
            });
        }

        // Filter by source
        if ($request->source) {
            $query->where('source_type', $request->source);
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('file_name', 'like', '%' . $request->search . '%');
            });
        }

        $media = $query->latest('created_at')->paginate(24);
        $websites = Website::where('created_by', auth()->id())->get();

        return view('media.index', compact('media', 'websites'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'media_file' => 'required|image|max:10240',
            'website_id' => 'nullable|exists:ane_websites,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('media_file');
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('media', $filename, 'public');

        $media = Media::create([
            'user_id' => auth()->id(),
            'website_id' => $request->website_id,
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'title' => $request->title,
            'description' => $request->description,
            'source_type' => 'manual',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'full_url' => asset('storage/' . $media->file_path),
                ]
            ]);
        }

        return redirect()->route('media.index')->with('success', 'Media uploaded successfully');
    }

    public function show($id)
    {
        $media = Media::findOrFail($id);

        // Check access
        if ($media->user_id && $media->user_id !== auth()->id()) {
            abort(403);
        }

        $media->load('user', 'website');

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'file_type' => $media->file_type,
                'file_size' => $media->file_size,
                'title' => $media->title,
                'alt_text' => $media->alt_text,
                'description' => $media->description,
                'caption' => $media->caption,
                'source_type' => $media->source_type,
                'ai_prompt' => $media->ai_prompt,
                'full_url' => $media->full_url, // Use accessor for both local and Unsplash
                'created_at' => $media->created_at,
                'created_by_name' => $media->user ? $media->user->name : 'System',
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $media = Media::findOrFail($id);

        if ($media->user_id && $media->user_id !== auth()->id()) {
            abort(403);
        }

        $media->update($request->only(['title', 'alt_text', 'caption', 'description']));

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $media = Media::findOrFail($id);

        if ($media->user_id && $media->user_id !== auth()->id()) {
            abort(403);
        }

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return redirect()->route('media.index')->with('success', 'Media deleted successfully');
    }

    public function apiList(Request $request)
    {
        $query = Media::where('user_id', auth()->id())
            ->orWhereNull('user_id');

        if ($request->website_id) {
            $query->where(function($q) use ($request) {
                $q->where('website_id', $request->website_id)
                  ->orWhereNull('website_id');
            });
        }

        $media = $query->latest('created_at')->limit(100)->get();

        return response()->json([
            'success' => true,
            'media' => $media->map(function($item) {
                return [
                    'id' => $item->id,
                    'file_name' => $item->file_name,
                    'file_type' => $item->file_type,
                    'file_size' => $item->file_size,
                    'title' => $item->title,
                    'alt_text' => $item->alt_text,
                    'description' => $item->description,
                    'caption' => $item->caption,
                    'source_type' => $item->source_type,
                    'full_url' => $item->full_url, // Use accessor for both local and Unsplash
                    'created_at' => $item->created_at,
                ];
            })
        ]);
    }

    public function apiSearch(Request $request)
    {
        $query = Media::where('user_id', auth()->id())
            ->orWhereNull('user_id');

        if ($request->q) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('file_name', 'like', '%' . $request->q . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->q . '%');
            });
        }

        $media = $query->latest('created_at')->limit(100)->get();

        return response()->json([
            'success' => true,
            'media' => $media->map(function($item) {
                return [
                    'id' => $item->id,
                    'file_name' => $item->file_name,
                    'file_type' => $item->file_type,
                    'file_size' => $item->file_size,
                    'title' => $item->title,
                    'alt_text' => $item->alt_text,
                    'description' => $item->description,
                    'caption' => $item->caption,
                    'source_type' => $item->source_type,
                    'full_url' => $item->full_url, // Use accessor for both local and Unsplash
                    'created_at' => $item->created_at,
                ];
            })
        ]);
    }

    public function updateMetadata(Request $request, $id)
    {
        $media = Media::findOrFail($id);

        // Check access
        if ($media->user_id && $media->user_id !== auth()->id()) {
            abort(403);
        }

        $media->update($request->only(['title', 'alt_text', 'caption', 'description']));

        return response()->json(['success' => true, 'media' => $media]);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('media', $filename, 'public');

        $media = Media::create([
            'user_id' => auth()->id(),
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => 'image',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'source_type' => 'manual',
        ]);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'file_type' => $media->file_type,
                'file_size' => $media->file_size,
                'title' => $media->title,
                'alt_text' => $media->alt_text,
                'description' => $media->description,
                'caption' => $media->caption,
                'full_url' => asset('storage/' . $media->file_path),
                'created_at' => $media->created_at,
            ]
        ]);
    }

    public function apiDestroy($id)
    {
        $media = Media::findOrFail($id);

        // Check access
        if ($media->user_id && $media->user_id !== auth()->id()) {
            abort(403);
        }

        // Only delete physical file if it's a local file (not Unsplash or external URL)
        if ($media->source_type !== 'unsplash' && !str_starts_with($media->file_path, 'http')) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }
}
