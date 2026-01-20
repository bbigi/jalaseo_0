<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TemplateController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = Template::accessible(auth()->id())->with('category');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->template_type) {
            $query->where('template_type', $request->template_type);
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $templates = $query->orderBy('is_public', 'desc')
                           ->orderBy('category_id')
                           ->orderBy('name')
                           ->get()
                           ->map(function ($template) {
                               return [
                                   'id' => $template->id,
                                   'name' => $template->name,
                                   'category' => $template->category?->name ?? null,
                                   'category_id' => $template->category_id,
                                   'template_type' => $template->template_type,
                                   'canvas_width' => $template->canvas_width,
                                   'canvas_height' => $template->canvas_height,
                                   'is_public' => $template->is_public,
                                   'thumbnail_url' => $template->thumbnail_path
                                       ? asset('storage/' . $template->thumbnail_path)
                                       : null,
                               ];
                           });

        return response()->json(['templates' => $templates]);
    }

    public function show(Template $template)
    {
        $this->authorize('view', $template);

        $template->load('category');

        return response()->json([
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category?->name ?? null,
                'category_id' => $template->category_id,
                'template_type' => $template->template_type,
                'canvas_json' => $template->canvas_json,
                'canvas_width' => $template->canvas_width,
                'canvas_height' => $template->canvas_height,
                'is_public' => $template->is_public,
                'thumbnail_url' => $template->thumbnail_path
                    ? asset('storage/' . $template->thumbnail_path)
                    : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:ane_template_categories,id',
            'template_type' => 'required|in:article,standalone',
            'canvas_json' => 'required|array',
            'canvas_width' => 'required|integer|min:100',
            'canvas_height' => 'required|integer|min:100',
            'is_public' => 'boolean',
        ]);

        // Check role: regular users can't set category or make public
        $isAdmin = auth()->user()->role === 'super_admin';

        $template = Template::create([
            'name' => $validated['name'],
            'category_id' => $isAdmin ? ($validated['category_id'] ?? null) : null,
            'template_type' => $validated['template_type'],
            'canvas_json' => $validated['canvas_json'],
            'canvas_width' => $validated['canvas_width'],
            'canvas_height' => $validated['canvas_height'],
            'is_public' => $isAdmin ? ($validated['is_public'] ?? false) : false,
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        return response()->json($template, 201);
    }

    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'category_id' => 'nullable|exists:ane_template_categories,id',
            'template_type' => 'in:article,standalone',
            'canvas_json' => 'array',
            'canvas_width' => 'integer|min:100',
            'canvas_height' => 'integer|min:100',
        ]);

        $template->update($validated);

        return response()->json($template);
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json(null, 204);
    }

    public function duplicate(Template $template)
    {
        $this->authorize('view', $template);

        $duplicate = Template::create([
            'name' => $template->name . ' (Copy)',
            'category_id' => null, // User templates have no category
            'template_type' => $template->template_type,
            'canvas_json' => $template->canvas_json,
            'canvas_width' => $template->canvas_width,
            'canvas_height' => $template->canvas_height,
            'is_public' => false,
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        return response()->json($duplicate, 201);
    }

    public function applyBranding(Template $template, Request $request)
    {
        $this->authorize('view', $template);

        $validated = $request->validate([
            'branding_id' => 'required|exists:ane_user_brandings,id',
        ]);

        $branding = \App\Models\UserBranding::with(['logo', 'website'])
                                            ->findOrFail($validated['branding_id']);

        if ($branding->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $variables = [
            'brand_logo' => $branding->logo?->full_url ?? '',
            'brand_color_primary' => $branding->primary_color,
            'brand_color_secondary' => $branding->secondary_color,
            'brand_font' => $branding->default_font,
            'website_url' => $branding->website?->url ?? '',
        ];

        $canvasJSON = $this->replaceVariables($template->canvas_json, $variables);

        return response()->json(['canvas_json' => $canvasJSON]);
    }

    private function replaceVariables($canvasJSON, array $variables)
    {
        foreach ($canvasJSON['children'] as &$layer) {
            foreach ($layer['children'] as &$obj) {
                // Replace text variables (isVariable contains the variable name)
                if ($obj['className'] === 'Text' && isset($obj['attrs']['isVariable'])) {
                    $varName = $obj['attrs']['isVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['text'] = $variables[$varName];
                    }
                }

                // Replace image variables (isVariable contains the variable name)
                if ($obj['className'] === 'Image' && isset($obj['attrs']['isVariable'])) {
                    $varName = $obj['attrs']['isVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['image'] = $variables[$varName];
                    }
                }

                // Replace color variables (colorVariable contains the variable name)
                if (isset($obj['attrs']['colorVariable'])) {
                    $varName = $obj['attrs']['colorVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['fill'] = $variables[$varName];
                    }
                }

                // Replace font family for ALL text (automatic)
                if ($obj['className'] === 'Text' && isset($variables['brand_font'])) {
                    $obj['attrs']['fontFamily'] = $variables['brand_font'];
                }
            }
        }

        return $canvasJSON;
    }
}
