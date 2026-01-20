<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of API credentials.
     */
    public function index(): View
    {
        $credentials = ApiCredential::all();

        return view('admin.api-keys.index', [
            'credentials' => $credentials,
        ]);
    }

    /**
     * Show the form for creating a new API credential.
     */
    public function create(): View
    {
        $categories = [
            'article_generation' => 'Article Generation',
            'image_generation' => 'Image Generation',
            'stock_photos' => 'Stock Photos',
            'news_insight' => 'News Insight',
            'deep_research' => 'Deep Research',
            'rapid_api' => 'RapidAPI Services',
            'web_scraping' => 'Web Scraping',
            'pagespeed-test' => 'PageSpeed Test',
        ];

        $providers = [
            'article_generation' => [
                'chatgpt' => 'OpenAI ChatGPT',
                'gemini' => 'Google Gemini',
                'claude' => 'Anthropic Claude',
                'deepseek' => 'DeepSeek',
                'perplexity' => 'Perplexity AI',
                'x-ai' => 'X.ai (Grok)',
            ],
            'image_generation' => [
                'flux' => 'Flux (Black Forest Labs)',
                'gemini' => 'Google Gemini Vision',
                'dall-e' => 'OpenAI DALL-E',
                'midjourney' => 'Midjourney',
                'x-ai' => 'X.ai (Grok Vision)',
            ],
            'stock_photos' => [
                'unsplash' => 'Unsplash',
                'pexels' => 'Pexels',
                'pixabay' => 'Pixabay',
            ],
            'news_insight' => [
                'serper' => 'Serper.dev (Google Search API)',
            ],
            'deep_research' => [
                'dataforseo' => 'DataForSEO (SERP, PAA, Keywords)',
                'rapidapi_keyword' => 'RapidAPI Keyword Insight (Volume, Intent)',
            ],
            'rapid_api' => [
                'rapidapi.com' => 'RapidAPI.com (TikTok, Twitter, Trends, etc)',
            ],
            'web_scraping' => [
                'firecrawl' => 'Firecrawl API',
            ],
            'pagespeed-test' => [
                'google' => 'Google PageSpeed Insights',
            ],
        ];

        return view('admin.api-keys.create', [
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    /**
     * Store a newly created API credential in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // DEBUGGING
        file_put_contents(
            storage_path('logs/api-key-debug.log'),
            "[" . date('Y-m-d H:i:s') . "] POST received\n" .
            "All inputs: " . json_encode($request->all()) . "\n" .
            "---\n",
            FILE_APPEND
        );

        // Log incoming request for debugging (mask real api_key)
        try {
            // Normalize checkbox input to real boolean so validation 'boolean' accepts it
            $request->merge(['is_active' => $request->boolean('is_active', false)]);

            // Normalize provider to lowercase so uniqueness checks match stored values
            if ($request->filled('provider')) {
                $request->merge(['provider' => strtolower($request->input('provider'))]);
            }

            $rawKey = (string) $request->input('api_key', '');
            $masked = $rawKey ? (substr($rawKey, 0, 4) . str_repeat('•', max(0, strlen($rawKey) - 8)) . substr($rawKey, -4)) : '';
        } catch (\Exception $e) {
            $masked = 'error_masking_key';
        }

        \Log::info('admin.api-keys.store attempt', [
            'user_id' => auth()->id(),
            'category' => $request->input('category'),
            'provider' => $request->input('provider'),
            'api_key_masked' => $masked,
            'ip' => $request->ip(),
        ]);

        try {
            $validated = $request->validate([
                'category' => 'required|string|in:article_generation,image_generation,stock_photos,news_insight,deep_research,rapid_api,web_scraping,pagespeed-test',
                'provider' => [
                    'required', 'string', 'min:3',
                    Rule::unique('ane_api_credentials')->where(function ($query) use ($request) {
                        return $query->where('category', $request->input('category'))
                                     ->where('provider', $request->input('provider'));
                    }),
                ],
                'api_key' => 'required|string|min:5',
                'api_secret' => 'nullable|string|min:5',
                'display_name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ]);
            
            // Log validation result
            file_put_contents(
                storage_path('logs/api-key-debug.log'),
                "Validation passed\n" .
                "Validated: " . json_encode($validated) . "\n" .
                "---\n",
                FILE_APPEND
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            file_put_contents(
                storage_path('logs/api-key-debug.log'),
                "VALIDATION FAILED\n" .
                "Errors: " . json_encode($e->errors()) . "\n" .
                "---\n",
                FILE_APPEND
            );
            throw $e;
        }

        try {
            $created = ApiCredential::create([
                'category' => $validated['category'],
                'provider' => strtolower($validated['provider']),
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'] ?? null,
                'display_name' => $validated['display_name'] ?? ucfirst($validated['provider']) . ' API',
                'description' => $validated['description'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            \Log::info('admin.api-keys.store success', [
                'created_id' => $created->id,
                'category' => $created->category,
                'provider' => $created->provider,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.api-keys.index')
                            ->with('success', "API credential for {$validated['provider']} ({$validated['category']}) has been added.");
        } catch (\Exception $e) {
            \Log::error('admin.api-keys.store failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'category' => $validated['category'] ?? null,
                'provider' => $validated['provider'] ?? null,
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to save API credential. Check logs.');
        }
    }

    /**
     * Show the form for editing the specified API credential.
     */
    public function edit(ApiCredential $apiKey): View
    {
        return view('admin.api-keys.edit', [
            'credential' => $apiKey,
        ]);
    }

    /**
     * Update the specified API credential in storage.
     */
    public function update(Request $request, ApiCredential $apiKey): RedirectResponse
    {
        // Normalize checkbox input to boolean before validation
        $request->merge(['is_active' => $request->boolean('is_active', false)]);

        $validated = $request->validate([
            'api_key' => 'nullable|string|min:10',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'display_name' => $validated['display_name'] ?? $apiKey->display_name,
            'description' => $validated['description'] ?? $apiKey->description,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Only update api_key when a new key is provided
        if (!empty($validated['api_key'])) {
            $updateData['api_key'] = $validated['api_key'];
        }

        $apiKey->update($updateData);

        return redirect()->route('admin.api-keys.index')
                        ->with('success', 'API credential has been updated successfully.');
    }

    /**
     * Remove the specified API credential from storage.
     */
    public function destroy(ApiCredential $apiKey): RedirectResponse
    {
        $provider = $apiKey->provider;
        $apiKey->delete();

        return redirect()->route('admin.api-keys.index')
                        ->with('success', "API credential for {$provider} has been deleted.");
    }

    /**
     * Toggle activation status of an API credential.
     */
    public function toggleActive(ApiCredential $apiKey): RedirectResponse
    {
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        $status = $apiKey->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.api-keys.index')
                        ->with('success', "API credential for {$apiKey->provider} has been {$status}.");
    }
}
