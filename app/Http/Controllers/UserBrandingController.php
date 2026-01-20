<?php

namespace App\Http\Controllers;

use App\Models\UserBranding;
use Illuminate\Http\Request;

class UserBrandingController extends Controller
{
    public function show(Request $request)
    {
        $websiteId = $request->get('website_id');

        $branding = UserBranding::where('user_id', auth()->id())
            ->where('website_id', $websiteId)
            ->with('logo')
            ->first();

        if (!$branding) {
            $branding = UserBranding::create([
                'user_id' => auth()->id(),
                'website_id' => $websiteId,
                'primary_color' => '#dc2626',
                'secondary_color' => '#2563eb',
                'default_font' => 'Inter',
            ]);
        }

        return response()->json([
            'success' => true,
            'branding' => $branding,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:ane_websites,id',
            'logo_media_id' => 'nullable|exists:ane_media,id',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_font' => 'nullable|string|max:50',
        ]);

        $branding = UserBranding::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'website_id' => $validated['website_id'],
            ],
            $validated
        );

        return response()->json([
            'success' => true,
            'branding' => $branding->load('logo'),
        ]);
    }
}
