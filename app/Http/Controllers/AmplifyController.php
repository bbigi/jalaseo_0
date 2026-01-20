<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ContentDNAService;
use App\Services\SocialCopyService;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AmplifyController extends Controller
{
    use AuthorizesRequests;

    protected $dnaService;
    protected $copyService;
    protected $schedulingService;

    public function __construct(
        ContentDNAService $dnaService,
        SocialCopyService $copyService,
        SchedulingService $schedulingService
    ) {
        $this->dnaService = $dnaService;
        $this->copyService = $copyService;
        $this->schedulingService = $schedulingService;
    }

    /**
     * Generate DNA + social copies for a post
     */
    public function generate(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'branding_id' => 'required|exists:ane_user_brandings,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:instagram,facebook,twitter,linkedin',
        ]);

        try {
            // Get branding & validate ownership
            $branding = \App\Models\UserBranding::with(['defaultTemplate', 'logo', 'website'])
                ->findOrFail($validated['branding_id']);

            if ($branding->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: You do not own this branding preset'
                ], 403);
            }

            // Get template from branding or fallback
            $template = $branding->defaultTemplate ?? \App\Models\Template::where('is_default', 1)
                ->where('template_type', 'article')
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => 'No template available. Please set a template in branding settings.'
                ], 400);
            }

            // Prepare variables
            $websiteUrl = '';
            if ($branding->website && !empty($branding->website->url)) {
                $url = parse_url($branding->website->url, PHP_URL_HOST);
                $websiteUrl = $url ?? $branding->website->url;
            }

            $variables = [
                'brand_logo' => $branding->logo?->full_url ?? '',
                'brand_color_primary' => $branding->primary_color,
                'brand_color_secondary' => $branding->secondary_color,
                'brand_font' => $branding->default_font,
                'website_url' => $websiteUrl,
                'article_title' => $post->title,
                'article_excerpt' => \Str::limit(strip_tags($post->content), 150),
                'article_author' => $post->owner->name,
                'featured_image' => $post->featuredMedia?->full_url ?? '',
            ];

            // Replace variables in template
            $canvasJSON = $this->replaceVariables($template->canvas_json, $variables);

            // Extract DNA
            $dna = $this->dnaService->extractDNA($post);

            // Generate copies + canvas for selected channels
            $assets = [];
            foreach ($validated['channels'] as $channel) {
                $asset = $this->copyService->generateCopy($dna, $channel);

                // Add canvas_json to asset
                $asset->update(['canvas_json' => $canvasJSON]);

                $assets[] = $asset;
            }

            // Auto-schedule if requested
            $schedules = [];
            if ($request->boolean('auto_schedule')) {
                $assetIds = collect($assets)->pluck('id')->toArray();
                $schedules = $this->schedulingService->autoSchedule($dna, $assetIds);
            }

            // Get scheduling recommendations
            $recommendations = $this->schedulingService->getRecommendations($dna);

            return response()->json([
                'success' => true,
                'dna' => $dna,
                'assets' => $assets,
                'schedules' => $schedules,
                'recommendations' => $recommendations,
                'message' => count($assets) . ' social assets generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function replaceVariables($canvasJSON, array $variables)
    {
        foreach ($canvasJSON['children'] as &$layer) {
            foreach ($layer['children'] as &$obj) {
                // Replace text variables (isVariable contains the variable name)
                if ($obj['className'] === 'Text' && isset($obj['attrs']['isVariable'])) {
                    $varName = $obj['attrs']['isVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['text'] = $variables[$varName];
                    }
                }

                // Replace image variables (isVariable contains the variable name)
                if ($obj['className'] === 'Image' && isset($obj['attrs']['isVariable'])) {
                    $varName = $obj['attrs']['isVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['image'] = $variables[$varName];

                        // Fix logo size: remove scale and set proper dimensions
                        if ($varName === 'brand_logo') {
                            // Get aspect ratio from actual logo image file
                            $aspectRatio = 0.3843; // Default fallback (98/255 from logo file)

                            // Try to get real dimensions from image URL
                            $logoUrl = $variables[$varName];
                            if ($logoUrl && filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                                // Extract path from URL
                                $path = parse_url($logoUrl, PHP_URL_PATH);
                                $path = ltrim($path, '/');

                                // Check if it's a storage path
                                if (strpos($path, 'storage/') === 0) {
                                    $filePath = str_replace('storage/', '', $path);
                                    $fullPath = storage_path('app/public/' . $filePath);

                                    if (file_exists($fullPath)) {
                                        $size = @getimagesize($fullPath);
                                        if ($size) {
                                            $aspectRatio = $size[1] / $size[0]; // height / width
                                        }
                                    }
                                }
                            }

                            // Set fixed width and proportional height based on actual logo file
                            $obj['attrs']['width'] = 300;
                            $obj['attrs']['height'] = 300 * $aspectRatio;

                            // Remove scale attributes completely
                            if (isset($obj['attrs']['scaleX'])) {
                                unset($obj['attrs']['scaleX']);
                            }
                            if (isset($obj['attrs']['scaleY'])) {
                                unset($obj['attrs']['scaleY']);
                            }
                        }
                    }
                }

                // Replace color variables (colorVariable contains the variable name)
                if (isset($obj['attrs']['colorVariable'])) {
                    $varName = $obj['attrs']['colorVariable'];
                    if (isset($variables[$varName])) {
                        $obj['attrs']['fill'] = $variables[$varName];
                    }
                }

                // Replace font family for ALL text (automatic)
                if ($obj['className'] === 'Text' && isset($variables['brand_font'])) {
                    $obj['attrs']['fontFamily'] = $variables['brand_font'];
                }
            }
        }

        return $canvasJSON;
    }

    /**
     * Preview generated DNA + social copies
     */
    public function preview(Post $post)
    {
        $this->authorize('view', $post);

        $dna = $post->contentDNA;
        $assets = $post->socialAssets()->with('schedule')->get();

        return response()->json([
            'success' => true,
            'dna' => $dna,
            'assets' => $assets->groupBy('channel'),
            'post' => $post->only(['id', 'title', 'article_type']),
        ]);
    }
}
