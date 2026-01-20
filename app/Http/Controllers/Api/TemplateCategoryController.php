<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateCategoryController extends Controller
{
    public function index()
    {
        $categories = TemplateCategory::active()->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        // Only admin can create categories via API
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:ane_template_categories,name',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $category = TemplateCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'icon' => $validated['icon'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 999,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }
}
