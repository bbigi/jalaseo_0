<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        // Get all available AI providers (active credentials)
        $allTextAIProviders = ApiCredential::where('category', 'article_generation')
            ->where('is_active', true)
            ->get(['provider', 'display_name'])
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider),
                ];
            });

        $allImageAIProviders = ApiCredential::where('category', 'image_generation')
            ->where('is_active', true)
            ->get(['provider', 'display_name'])
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider),
                ];
            });

        // Get current settings (saved order)
        $savedTextAI = GeneralSetting::getDefaultTextAI();
        $savedImageAI = GeneralSetting::getDefaultImageAI();

        // Merge: Show saved order first, then add any new providers not in settings
        $defaultTextAI = $this->mergeLists($savedTextAI, $allTextAIProviders);
        $defaultImageAI = $this->mergeLists($savedImageAI, $allImageAIProviders);

        return view('admin.settings.index', compact(
            'allTextAIProviders',
            'allImageAIProviders',
            'defaultTextAI',
            'defaultImageAI'
        ));
    }

    /**
     * Merge saved settings with available providers
     * Returns array of provider names in order (saved first, then new ones)
     */
    private function mergeLists(array $savedProviders, $availableProviders): array
    {
        $availableValues = $availableProviders->pluck('value')->toArray();

        // Start with saved providers that still exist in credentials
        $result = array_intersect($savedProviders, $availableValues);

        // Add new providers not in saved list
        $newProviders = array_diff($availableValues, $savedProviders);
        $result = array_merge(array_values($result), $newProviders);

        return $result;
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_text_ai' => 'required|array|min:1',
            'default_text_ai.*' => 'required|string',
            'default_image_ai' => 'required|array|min:1',
            'default_image_ai.*' => 'required|string',
        ]);

        // Update settings
        GeneralSetting::set(
            'default_text_ai',
            $validated['default_text_ai'],
            'Default AI providers for text generation (fallback chain)'
        );

        GeneralSetting::set(
            'default_image_ai',
            $validated['default_image_ai'],
            'Default AI providers for image generation (fallback chain)'
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'General settings updated successfully');
    }
}
