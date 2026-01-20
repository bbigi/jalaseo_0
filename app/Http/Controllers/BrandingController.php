<?php

namespace App\Http\Controllers;

use App\Models\UserBranding;
use App\Models\Website;
use App\Models\Media;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brandings = UserBranding::with(['website', 'logo'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('brandings.index', compact('brandings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $websites = Website::where('created_by', auth()->id())->get();
        $templates = Template::articleTemplates()
            ->accessible(auth()->id())
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('brandings.create', compact('websites', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'preset_name' => 'required|string|max:100',
            'website_id' => 'nullable|exists:ane_websites,id',
            'logo_id' => 'nullable|exists:ane_media,id',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'default_font' => 'required|string|max:50',
            'default_template_id' => 'nullable|exists:ane_templates,id',
        ]);

        $branding = UserBranding::create([
            'user_id' => auth()->id(),
            'website_id' => $validated['website_id'] ?? null,
            'preset_name' => $validated['preset_name'],
            'logo_id' => $validated['logo_id'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'default_font' => $validated['default_font'],
            'default_template_id' => $validated['default_template_id'] ?? null,
        ]);

        return redirect()->route('brandings.index')
            ->with('success', 'Branding preset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserBranding $branding)
    {
        $this->authorize('view', $branding);

        $branding->load(['website', 'logo']);

        return view('brandings.show', compact('branding'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserBranding $branding)
    {
        $this->authorize('update', $branding);

        $websites = Website::where('created_by', auth()->id())->get();
        $templates = Template::articleTemplates()
            ->accessible(auth()->id())
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('brandings.edit', compact('branding', 'websites', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserBranding $branding)
    {
        $this->authorize('update', $branding);

        $validated = $request->validate([
            'preset_name' => 'required|string|max:100',
            'website_id' => 'nullable|exists:ane_websites,id',
            'logo_id' => 'nullable|exists:ane_media,id',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'default_font' => 'required|string|max:50',
            'default_template_id' => 'nullable|exists:ane_templates,id',
        ]);

        $branding->update([
            'website_id' => $validated['website_id'] ?? null,
            'preset_name' => $validated['preset_name'],
            'logo_id' => $validated['logo_id'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'default_font' => $validated['default_font'],
            'default_template_id' => $validated['default_template_id'] ?? null,
        ]);

        return redirect()->route('brandings.index')
            ->with('success', 'Branding preset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserBranding $branding)
    {
        $this->authorize('delete', $branding);

        $branding->delete();

        return redirect()->route('brandings.index')
            ->with('success', 'Branding preset deleted successfully.');
    }
}
