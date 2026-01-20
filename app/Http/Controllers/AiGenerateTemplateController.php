<?php

namespace App\Http\Controllers;

use App\Models\AiGenerateTemplate;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AiGenerateTemplateController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = AiGenerateTemplate::where('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ai-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get active AI providers (for dropdown 1)
        $aiProviders = \App\Models\ApiCredential::where('category', 'article_generation')
            ->where('is_active', true)
            ->get()
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider)
                ];
            });

        // Get all models by provider (for dropdown 2 - cascading)
        $modelsByProvider = \App\Helpers\AiModelHelper::getModelsByProvider();

        // Get countries and languages
        $countries = \App\Helpers\CountryHelper::getCountries();
        $languages = \App\Helpers\CountryHelper::getLanguages();

        return view('ai-templates.create', compact('aiProviders', 'modelsByProvider', 'countries', 'languages'));
    }

    public function createModal()
    {
        // Get active AI providers (for dropdown 1)
        $aiProviders = \App\Models\ApiCredential::where('category', 'article_generation')
            ->where('is_active', true)
            ->get()
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider)
                ];
            });

        // Get all models by provider (for dropdown 2 - cascading)
        $modelsByProvider = \App\Helpers\AiModelHelper::getModelsByProvider();

        // Get countries and languages
        $countries = \App\Helpers\CountryHelper::getCountries();
        $languages = \App\Helpers\CountryHelper::getLanguages();

        return view('ai-templates.create-modal', compact('aiProviders', 'modelsByProvider', 'countries', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_provider' => 'nullable|string|max:50',
            'ai_model' => 'nullable|string|max:255',
            'text_readability' => 'nullable|string|max:255',
            'ai_content_cleaning' => 'nullable|boolean',
            'language' => 'required|string|max:255',
            'article_type' => 'nullable|string|max:255',
            'article_size' => 'required|string|max:255',
            'tone' => 'nullable|string|max:255',
            'point_of_view' => 'nullable|string|max:255',
            'target_country' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['ai_content_cleaning'] = $request->has('ai_content_cleaning');
        $validated['prompt_template'] = '';

        $template = AiGenerateTemplate::create($validated);

        // If modal mode, return JSON for iframe postMessage
        if ($request->has('modal') && $request->get('modal') == '1') {
            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Template created successfully'
            ]);
        }

        return redirect()->route('ai-templates.index')
            ->with('success', 'Template created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AiGenerateTemplate $aiTemplate)
    {
        $this->authorize('update', $aiTemplate);

        // Get active AI providers (for dropdown 1)
        $aiProviders = \App\Models\ApiCredential::where('category', 'article_generation')
            ->where('is_active', true)
            ->get()
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider)
                ];
            });

        // Get all models by provider (for dropdown 2 - cascading)
        $modelsByProvider = \App\Helpers\AiModelHelper::getModelsByProvider();

        // Get countries and languages
        $countries = \App\Helpers\CountryHelper::getCountries();
        $languages = \App\Helpers\CountryHelper::getLanguages();

        return view('ai-templates.edit', compact('aiTemplate', 'aiProviders', 'modelsByProvider', 'countries', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AiGenerateTemplate $aiTemplate)
    {
        $this->authorize('update', $aiTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_provider' => 'nullable|string|max:50',
            'ai_model' => 'nullable|string|max:255',
            'text_readability' => 'nullable|string|max:255',
            'ai_content_cleaning' => 'nullable|boolean',
            'language' => 'required|string|max:255',
            'article_type' => 'nullable|string|max:255',
            'article_size' => 'required|string|max:255',
            'tone' => 'nullable|string|max:255',
            'point_of_view' => 'nullable|string|max:255',
            'target_country' => 'nullable|string|max:255',
        ]);

        $validated['ai_content_cleaning'] = $request->has('ai_content_cleaning');

        $aiTemplate->update($validated);

        return redirect()->route('ai-templates.index')
            ->with('success', 'Template updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AiGenerateTemplate $aiTemplate)
    {
        $this->authorize('delete', $aiTemplate);

        $aiTemplate->delete();

        return redirect()->route('ai-templates.index')
            ->with('success', 'Template deleted successfully');
    }
}
