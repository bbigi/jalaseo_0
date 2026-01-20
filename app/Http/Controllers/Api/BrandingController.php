<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBranding;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function index(Request $request)
    {
        $brandings = UserBranding::where('user_id', auth()->id())
            ->with(['logo', 'website'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($branding) {
                return [
                    'id' => $branding->id,
                    'preset_name' => $branding->preset_name,
                    'primary_color' => $branding->primary_color,
                    'secondary_color' => $branding->secondary_color,
                    'default_font' => $branding->default_font,
                    'logo_url' => $branding->logo?->full_url ?? null,
                    'website_url' => $branding->website?->url ?? null,
                ];
            });

        return response()->json(['brandings' => $brandings]);
    }

    public function getUserBranding(Request $request)
    {
        // Get first branding preset for the user (or you can implement "active" preset logic)
        $branding = UserBranding::where('user_id', auth()->id())
            ->with('logo')
            ->first();

        if (!$branding) {
            return response()->json([
                'success' => false,
                'message' => 'No branding found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'branding' => [
                'id' => $branding->id,
                'preset_name' => $branding->preset_name,
                'primary_color' => $branding->primary_color,
                'secondary_color' => $branding->secondary_color,
                'default_font' => $branding->default_font,
                'logo_url' => $branding->logo?->full_url ?? null,
            ]
        ]);
    }
}
