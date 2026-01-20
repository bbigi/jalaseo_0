<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OAuthServiceController extends Controller
{
    /**
     * Display OAuth services list
     */
    public function index()
    {
        $services = OAuthService::orderBy('service_name')->get();

        return view('admin.oauth-services.index', compact('services'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.oauth-services.create');
    }

    /**
     * Store new OAuth service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:50|unique:ane_oauth_services,service_name',
            'display_name' => 'required|string|max:100',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'nullable|url|max:500',
            'scopes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Convert scopes to array
        if ($validated['scopes']) {
            $validated['scopes'] = array_map('trim', explode(',', $validated['scopes']));
        }

        OAuthService::create($validated);

        return redirect()->route('admin.oauth-services.index')
            ->with('success', 'OAuth service added successfully');
    }

    /**
     * Show edit form
     */
    public function edit(OAuthService $oauthService)
    {
        return view('admin.oauth-services.edit', compact('oauthService'));
    }

    /**
     * Update OAuth service
     */
    public function update(Request $request, OAuthService $oauthService)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:50|unique:ane_oauth_services,service_name,' . $oauthService->id,
            'display_name' => 'required|string|max:100',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'nullable|url|max:500',
            'scopes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Convert scopes to array
        if ($validated['scopes']) {
            $validated['scopes'] = array_map('trim', explode(',', $validated['scopes']));
        }

        $oauthService->update($validated);

        return redirect()->route('admin.oauth-services.index')
            ->with('success', 'OAuth service updated successfully');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(OAuthService $oauthService)
    {
        $oauthService->update([
            'is_active' => !$oauthService->is_active,
        ]);

        Cache::forget("oauth_service_{$oauthService->service_name}");

        return redirect()->back()
            ->with('success', 'OAuth service status updated');
    }

    /**
     * Delete OAuth service
     */
    public function destroy(OAuthService $oauthService)
    {
        $oauthService->delete();

        return redirect()->route('admin.oauth-services.index')
            ->with('success', 'OAuth service deleted successfully');
    }
}
