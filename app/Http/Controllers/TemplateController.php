<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TemplateController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $templates = Template::accessible(auth()->id())
            ->with(['creator:id,name', 'category'])
            ->orderBy('is_public', 'desc')
            ->orderBy('name')
            ->paginate(24);

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'template_type' => 'required|in:article,standalone',
            'canvas_json' => 'required|array',
            'thumbnail' => 'nullable|string',
            'canvas_width' => 'required|integer',
            'canvas_height' => 'required|integer',
            'category_id' => 'nullable|exists:ane_template_categories,id',
            'is_public' => 'nullable|boolean',
        ]);

        $isAdmin = auth()->user()->role === 'super_admin';

        // Determine category and visibility based on user role
        $categoryId = null;
        $isPublic = false;

        if ($isAdmin) {
            // Admin can set category and make public
            $categoryId = $validated['category_id'] ?? null;
            $isPublic = $validated['is_public'] ?? false;
        }
        // Regular users: no category, always private (defaults already set)

        $template = Template::create([
            'name' => $validated['name'],
            'category_id' => $categoryId,
            'template_type' => $validated['template_type'],
            'canvas_json' => $validated['canvas_json'],
            'canvas_width' => $validated['canvas_width'],
            'canvas_height' => $validated['canvas_height'],
            'is_public' => $isPublic,
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        // Save thumbnail if provided
        if ($request->has('thumbnail')) {
            $thumbnailData = $request->input('thumbnail');

            // Extract base64 data
            if (preg_match('/^data:image\/(\w+);base64,/', $thumbnailData, $matches)) {
                $imageType = $matches[1];
                $thumbnailData = substr($thumbnailData, strpos($thumbnailData, ',') + 1);
                $thumbnailData = base64_decode($thumbnailData);

                // Generate filename
                $filename = 'template-' . $template->id . '-' . time() . '.' . $imageType;
                $path = 'templates/' . $filename;

                // Save to storage/app/public/templates/
                \Storage::disk('public')->put($path, $thumbnailData);

                // Update template with thumbnail_path
                $template->update(['thumbnail_path' => $path]);
            }
        }

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    public function edit(Template $template)
    {
        $this->authorize('update', $template);

        // Redirect to image editor with template_id parameter
        return redirect('/image-editor?template_id=' . $template->id);
    }

    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'template_type' => 'required|in:article,standalone',
            'canvas_json' => 'required|array',
            'thumbnail' => 'nullable|string',
            'canvas_width' => 'required|integer',
            'canvas_height' => 'required|integer',
            'category_id' => 'nullable|exists:ane_template_categories,id',
            'is_public' => 'nullable|boolean',
        ]);

        $isAdmin = auth()->user()->role === 'super_admin';

        // Determine category and visibility based on user role
        $categoryId = null;
        $isPublic = false;

        if ($isAdmin) {
            // Admin can set category and make public
            $categoryId = $validated['category_id'] ?? null;
            $isPublic = $validated['is_public'] ?? false;
        }
        // Regular users: no category, always private (defaults already set)

        $template->update([
            'name' => $validated['name'],
            'category_id' => $categoryId,
            'template_type' => $validated['template_type'],
            'canvas_json' => $validated['canvas_json'],
            'canvas_width' => $validated['canvas_width'],
            'canvas_height' => $validated['canvas_height'],
            'is_public' => $isPublic,
        ]);

        // Save thumbnail if provided
        if ($request->has('thumbnail')) {
            $thumbnailData = $request->input('thumbnail');

            // Extract base64 data
            if (preg_match('/^data:image\/(\w+);base64,/', $thumbnailData, $matches)) {
                $imageType = $matches[1];
                $thumbnailData = substr($thumbnailData, strpos($thumbnailData, ',') + 1);
                $thumbnailData = base64_decode($thumbnailData);

                // Delete old thumbnail if exists
                if ($template->thumbnail_path) {
                    \Storage::disk('public')->delete($template->thumbnail_path);
                }

                // Generate filename
                $filename = 'template-' . $template->id . '-' . time() . '.' . $imageType;
                $path = 'templates/' . $filename;

                // Save to storage/app/public/templates/
                \Storage::disk('public')->put($path, $thumbnailData);

                // Update template with thumbnail_path
                $template->update(['thumbnail_path' => $path]);
            }
        }

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
