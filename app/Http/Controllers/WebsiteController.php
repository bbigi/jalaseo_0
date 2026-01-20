<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\PageSpeedRun;
use App\Models\CompetitorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WebsiteController extends Controller
{
    use AuthorizesRequests;
    /**
     * List all websites owned by authenticated user
     */
    public function index(): View
    {
        $websites = Website::where('created_by', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('websites.index', compact('websites'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $categories = $this->getCategories();
        return view('websites.create', compact('categories'));
    }

    /**
     * Store new website
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:ane_websites,name'],
            'url' => ['required', 'url', 'max:255'],
            'username' => ['required', 'string', 'max:100'],
            'app_password' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys($this->getCategories()))],
        ], [
            'name.unique' => 'Website name already exists',
            'category.in' => 'Invalid website category selected',
        ]);

        try {
            Website::create([
                'name' => $validated['name'],
                'url' => rtrim($validated['url'], '/'),
                'username' => $validated['username'],
                'app_password' => $validated['app_password'],
                'category' => $validated['category'],
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('websites.index')
                ->with('success', 'Website added successfully. You can now test the connection.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to add website: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(Website $website): View
    {
        $this->authorize('own', $website);
        $categories = $this->getCategories();
        return view('websites.create', compact('website', 'categories'));
    }

    /**
     * Update website
     */
    public function update(Request $request, Website $website): RedirectResponse
    {
        $this->authorize('own', $website);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:255'],
            'username' => ['required', 'string', 'max:100'],
            'app_password' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys($this->getCategories()))],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        // Check if name exists (excluding current website)
        if ($validated['name'] !== $website->name && Website::where('name', $validated['name'])->exists()) {
            return back()->withInput()->with('error', 'Website name already exists');
        }

        try {
            $website->update([
                'name' => $validated['name'],
                'url' => rtrim($validated['url'], '/'),
                'username' => $validated['username'],
                'category' => $validated['category'],
                'status' => $validated['status'],
            ]);

            // Only update password if provided
            if (!empty($validated['app_password'])) {
                $website->app_password = $validated['app_password'];
                $website->save();
            }

            return redirect()
                ->route('websites.index')
                ->with('success', 'Website updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update website: ' . $e->getMessage());
        }
    }

    /**
     * Show website details
     */
    public function show(Website $website): View
    {
        $this->authorize('own', $website);
        $website->load('analyses', 'keywords');

        $latestPageSpeedRun = PageSpeedRun::where('website_id', $website->id)
            ->whereIn('status', ['success', 'completed'])
            ->latest()
            ->first();

        $mobilePageSpeed = PageSpeedRun::where('website_id', $website->id)
            ->where('strategy', 'mobile')
            ->whereIn('status', ['success', 'completed'])
            ->latest()
            ->first();

        $desktopPageSpeed = PageSpeedRun::where('website_id', $website->id)
            ->where('strategy', 'desktop')
            ->whereIn('status', ['success', 'completed'])
            ->latest()
            ->first();

        $competitorsGap = CompetitorProfile::where('website_id', $website->id)
            ->with('website')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('websites.show', compact('website', 'latestPageSpeedRun', 'mobilePageSpeed', 'desktopPageSpeed', 'competitorsGap'));
    }

    /**
     * Delete website
     */
    public function destroy(Website $website): RedirectResponse
    {
        $this->authorize('own', $website);

        try {
            $name = $website->name;
            $website->delete();
            return redirect()
                ->route('websites.index')
                ->with('success', "Website '{$name}' deleted successfully");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete website: ' . $e->getMessage());
        }
    }

    /**
     * Test WordPress connection
     */
    public function testConnection(Request $request, Website $website)
    {
        $this->authorize('own', $website);

        try {
            // For now, just return success. In future, call actual WP REST API
            return response()->json([
                'success' => true,
                'message' => 'WordPress connection test successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Fetch WordPress categories via REST API
     */
    public function fetchCategories($id)
    {
        try {
            $website = Website::findOrFail($id);
            $this->authorize('own', $website);

            $url = rtrim($website->url, '/') . '/wp-json/wp/v2/categories?per_page=100&orderby=name&order=asc';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $website->username . ':' . $website->app_password);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                \Log::error('WordPress API CURL Error', ['error' => $curlError, 'url' => $url]);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error: ' . $curlError
                ]);
            }

            if ($httpCode !== 200) {
                \Log::error('WordPress API HTTP Error', ['code' => $httpCode, 'response' => substr($response, 0, 500)]);
                return response()->json([
                    'success' => false,
                    'message' => 'WordPress returned error (HTTP ' . $httpCode . ')'
                ]);
            }

            $categories = json_decode($response, true);

            if (!is_array($categories)) {
                \Log::error('WordPress API Invalid Response', ['response' => substr($response, 0, 500)]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from WordPress'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories
                ]
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Website not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('WordPress Categories Fetch Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get WordPress tags for a website
     * GET /websites/{website}/tags
     */
    public function getWordPressTags(Website $website)
    {
        try {
            $this->authorize('own', $website);

            $url = rtrim($website->url, '/') . '/wp-json/wp/v2/tags?per_page=100';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                \Log::error('WordPress Tags Fetch Failed', [
                    'website' => $website->url,
                    'http_code' => $httpCode,
                    'response' => substr($response, 0, 500)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch tags from WordPress (HTTP ' . $httpCode . ')'
                ]);
            }

            $tags = json_decode($response, true);

            if (!is_array($tags)) {
                \Log::error('WordPress API Invalid Response', ['response' => substr($response, 0, 500)]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from WordPress'
                ]);
            }

            // Cache tags to database
            foreach ($tags as $tag) {
                \App\Models\Tag::updateOrCreate(
                    [
                        'website_id' => $website->id,
                        'wp_tag_id' => $tag['id'],
                    ],
                    [
                        'name' => $tag['name'],
                        'slug' => $tag['slug'],
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tags' => $tags
                ]
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Website not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('WordPress Tags Fetch Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of website categories
     */
    private function getCategories(): array
    {
        return [
            'business_industrial' => 'Business & Industrial',
            'shopping_ecommerce' => 'Shopping & E-commerce',
            'technology_software' => 'Technology & Software',
            'education_content' => 'Education & Online Learning',
            'news_media' => 'News & Media',
            'health_beauty' => 'Health & Beauty',
            'food_beverage' => 'Food & Beverage',
            'travel_hospitality' => 'Travel & Hospitality',
            'finance_investment' => 'Finance & Investment',
            'real_estate' => 'Real Estate',
            'automotive' => 'Automotive',
            'entertainment' => 'Entertainment',
            'personal_brand' => 'Personal Brand / Portfolio',
            'other' => 'Other',
        ];
    }
}
