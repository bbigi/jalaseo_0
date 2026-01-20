<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateCategoryController extends Controller
{
    public function index()
    {
        $categories = TemplateCategory::orderBy('sort_order')->get();
        return view('admin.template-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.template-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:ane_template_categories,name',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        TemplateCategory::create($validated);

        return redirect()->route('admin.template-categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit(TemplateCategory $templateCategory)
    {
        return view('admin.template-categories.edit', compact('templateCategory'));
    }

    public function update(Request $request, TemplateCategory $templateCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:ane_template_categories,name,' . $templateCategory->id,
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $templateCategory->update($validated);

        return redirect()->route('admin.template-categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        // Check if category has templates
        if ($templateCategory->templates()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing templates');
        }

        $templateCategory->delete();

        return redirect()->route('admin.template-categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
