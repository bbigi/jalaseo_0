<?php

namespace App\Http\Controllers;

use App\Models\SocialAsset;
use App\Models\ContentDNA;
use App\Models\UserBranding;
use App\Services\VisualTemplateService;
use App\Services\TemplateLayoutEngine;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class VisualController extends Controller
{
    use AuthorizesRequests;

    protected $visualService;
    protected $layoutEngine;

    public function __construct(VisualTemplateService $visualService, TemplateLayoutEngine $layoutEngine)
    {
        $this->visualService = $visualService;
        $this->layoutEngine = $layoutEngine;
    }

    /**
     * Get template data for client-side rendering
     */
    public function getTemplateData(Request $request, SocialAsset $asset)
    {
        $this->authorize('view', $asset);

        $validated = $request->validate([
            'template_id' => 'required|string',
            'size' => 'required|in:4:5,9:16,1:1,16:9,4:3',
            'brandLogo' => 'nullable|string',
            'branding_id' => 'nullable|exists:ane_user_brandings,id',
        ]);

        $dna = $asset->post->contentDNA;

        if (!$dna) {
            return response()->json([
                'success' => false,
                'error' => 'Content DNA not found',
            ], 404);
        }

        // Load universal template
        $templatesPath = storage_path('app/templates/universal-templates.json');
        if (!file_exists($templatesPath)) {
            return response()->json([
                'success' => false,
                'error' => 'Template file not found',
            ], 500);
        }

        $templates = json_decode(file_get_contents($templatesPath), true);

        $template = collect($templates)->firstWhere('id', $validated['template_id']);

        if (!$template) {
            return response()->json([
                'success' => false,
                'error' => 'Template not found',
            ], 404);
        }

        // Get user branding from request (user just changed dropdown) or saved metadata
        // Priority order:
        // 1. branding_id from request (user just changed dropdown in visual editor) - HIGHEST PRIORITY
        // 2. Saved branding_id from asset metadata (previous selection)
        // 3. Auto-select branding for post's website (if post has website_id)
        // 4. Fallback: Use first available branding for this user
        $requestBrandingId = $validated['branding_id'] ?? null;
        $savedBrandingId = $asset->visual_metadata['branding_id'] ?? null;

        if ($requestBrandingId) {
            // User just changed branding dropdown - use request value
            $branding = UserBranding::with(['logo', 'website'])->find($requestBrandingId);
        } elseif ($savedBrandingId) {
            // Use previously saved branding
            $branding = UserBranding::with(['logo', 'website'])->find($savedBrandingId);
        } elseif ($asset->post->website_id) {
            // Auto-select branding for post's website
            $branding = UserBranding::where('user_id', auth()->id())
                ->where('website_id', $asset->post->website_id)
                ->with(['logo', 'website'])
                ->first();
        } else {
            // CRITICAL: Always provide branding for templates that need it (News Template 1, etc.)
            $branding = UserBranding::where('user_id', auth()->id())
                ->with(['logo', 'website'])
                ->first();
        }

        // Get custom settings from asset metadata
        $customSettings = $asset->visual_metadata['custom_settings'] ?? [];

        // Prepare template variables
        $customSettings['featuredImage'] = $asset->visual_metadata['featured_image'] ?? ($dna->featured_image_url ?? asset('images/placeholder.jpg'));
        $customSettings['headline'] = $asset->visual_metadata['headline'] ?? ($dna->headline ?? $asset->post->title);
        $customSettings['subheadline'] = $asset->visual_metadata['subheadline'] ?? ($dna->subheadline ?? '');

        // Brand logo from request (manual picker) or from branding
        if (!empty($validated['brandLogo'])) {
            $customSettings['brandLogo'] = $validated['brandLogo'];
        } elseif ($branding && $branding->logo) {
            $customSettings['brandLogo'] = asset('storage/' . $branding->logo->file_path);
        }

        // Website URL from branding (for footer in News Template 1)
        // Priority: 1) Branding website URL, 2) Fallback to webane.com
        if ($branding && $branding->website && !empty($branding->website->url)) {
            // Extract domain from URL (remove https://)
            $url = parse_url($branding->website->url, PHP_URL_HOST);
            $customSettings['websiteUrl'] = $url ?? $branding->website->url;
        } else {
            $customSettings['websiteUrl'] = 'webane.com'; // Fallback if no branding
        }

        // Calculate layout using engine
        $layoutData = $this->layoutEngine->calculateLayout(
            $template,
            $validated['size'],
            $customSettings,
            $branding
        );

        $canvasState = $asset->visual_metadata['canvas_state'] ?? null;

        // Invalidate canvas_state if branding changed
        $savedBrandingIdInMeta = $asset->visual_metadata['branding_id'] ?? null;
        $currentBrandingId = $branding ? $branding->id : null;

        if ($savedBrandingIdInMeta != $currentBrandingId) {
            $canvasState = null; // Force re-render
        }

        return response()->json([
            'success' => true,
            'templateData' => [
                'template' => $template,
                'dimensions' => $layoutData['dimensions'],
                'objects' => $layoutData['objects'],
                'settings' => $layoutData['settings'],
            ],
            'canvas_state' => $canvasState,
            'branding' => $branding,
        ]);
    }

    /**
     * Upload generated PNG from client
     */
    public function uploadVisual(Request $request, SocialAsset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'image' => 'required|file|mimes:png|max:5120', // Max 5MB
            'template_id' => 'required|string',
            'canvas_state' => 'nullable|string',
            'headline' => 'nullable|string',
            'subheadline' => 'nullable|string',
            'featured_image_url' => 'nullable|string',
            'custom_settings' => 'nullable|json',
            'ratio' => 'nullable|in:1:1,4:5,16:9,4:3,9:16',
            'branding_id' => 'nullable|exists:ane_user_brandings,id',
        ]);

        try {
            $path = $this->visualService->saveGeneratedVisual(
                $asset,
                $request->file('image'),
                $validated['featured_image_url'] ?? null,
                $validated['canvas_state'] ?? null,
                $validated['headline'] ?? null,
                $validated['subheadline'] ?? null,
                $validated['ratio'] ?? null,
                $validated['custom_settings'] ? json_decode($validated['custom_settings'], true) : []
            );

            // Add branding_id to metadata if provided (service doesn't handle this)
            if (!empty($validated['branding_id'])) {
                $metadata = $asset->fresh()->visual_metadata ?? [];
                $metadata['branding_id'] = $validated['branding_id'];
                $asset->update(['visual_metadata' => $metadata]);
            }

            return response()->json([
                'success' => true,
                'visual_path' => $path,
                'visual_url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get template list from JS files (dynamic)
     */
    public function getTemplateList(Request $request)
    {
        $templates = json_decode(file_get_contents(storage_path('app/templates/universal-templates.json')), true);

        return response()->json([
            'success' => true,
            'templates' => array_map(fn($t) => ['id' => $t['id'], 'name' => $t['name']], $templates),
        ]);
    }

    /**
     * Get available templates for asset
     */
    public function getTemplates(Request $request, SocialAsset $asset)
    {
        $this->authorize('view', $asset);

        // Safe fallback if $request is somehow missing
        $size = ($request ? $request->query('size') : null) ?? ($asset->visual_metadata['ratio'] ?? $this->determineSizeForAsset($asset));
        $selectedTemplate = $asset->visual_metadata['template_id'] ?? null;

        // Load universal templates
        $templatesPath = storage_path('app/templates/universal-templates.json');
        if (!file_exists($templatesPath)) {
            return response()->json([
                'success' => false,
                'error' => 'Template file not found',
            ], 500);
        }
        $allTemplates = json_decode(file_get_contents($templatesPath), true);

        // Filter templates that support the current size
        $templates = collect($allTemplates)->filter(function ($template) use ($size) {
            return in_array($size, $template['supportedRatios']);
        })->values()->all();

        return response()->json([
            'success' => true,
            'templates' => $templates,
            'size' => $size,
            'selected_template' => $selectedTemplate,
            'available_ratios' => ['1:1', '4:5', '16:9', '9:16'],
        ]);
    }

    /**
     * Determine default size for asset based on channel and type
     */
    protected function determineSizeForAsset(SocialAsset $asset): string
    {
        if ($asset->channel === 'instagram' && $asset->asset_type === 'feed') {
            return '4:5';
        } elseif ($asset->channel === 'instagram' && $asset->asset_type === 'story') {
            return '9:16';
        }

        return '1:1';
    }

}
