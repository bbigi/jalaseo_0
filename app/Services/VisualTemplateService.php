<?php

namespace App\Services;

use App\Models\ContentDNA;
use App\Models\SocialAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VisualTemplateService
{
    /**
     * Generate visual from template + DNA
     *
     * Note: This service prepares the template data for client-side rendering with Fabric.js
     * The actual PNG generation happens in the browser, then uploaded back to server
     */
    public function generateVisual(ContentDNA $dna, string $channel, string $size, string $templateId, ?SocialAsset $asset = null): array
    {
        // Determine featured image: prefer stored metadata, fallback to post featured
        $featuredImageUrl = null;
        if ($asset && isset($asset->visual_metadata['featured_image']) && $asset->visual_metadata['featured_image']) {
            $featuredImageUrl = $asset->visual_metadata['featured_image'];
        } else {
            $featuredImageUrl = $this->getFeaturedImageUrl($dna->post);
        }

        // Build template variables from DNA
        $variables = $this->buildTemplateVariables($dna, $featuredImageUrl);

        // Return template data for client-side rendering
        return [
            'templateId' => $templateId,
            'channel' => $channel,
            'size' => $size,
            'variables' => $variables,
            'dimensions' => $this->getDimensions($size),
            'objects' => [],
        ];
    }

    /**
     * Save uploaded PNG from client-side rendering
     */
    public function saveGeneratedVisual(
        SocialAsset $asset,
        $uploadedFile,
        ?string $featuredImageUrl = null,
        ?string $canvasState = null,
        ?string $headline = null,
        ?string $subheadline = null,
        ?string $ratio = null,
        array $customSettings = []
    ): string
    {
        $userId = $asset->created_by;
        $channel = $asset->channel;
        $assetType = $asset->asset_type;

        // Generate unique filename
        $filename = sprintf(
            '%s_%s_%s_%s.png',
            $channel,
            $assetType,
            $asset->id,
            time()
        );

        // User-scoped storage path
        $path = "users/{$userId}/social/{$channel}/{$filename}";

        // Store file
        Storage::disk('public')->put($path, file_get_contents($uploadedFile));

        // Update asset
        $asset->update([
            'visual_path' => $path,
            'visual_size' => $ratio ?? $this->getSizeFromDimensions($asset),
            'visual_metadata' => [
                'generated_at' => now()->toISOString(),
                'template_id' => request('template_id'),
                'featured_image' => $featuredImageUrl,
                'dimensions' => $ratio ? $this->getDimensions($ratio) : $this->getDimensionsFromChannel($channel),
                'canvas_state' => $canvasState,
                'headline' => $headline,
                'subheadline' => $subheadline,
                'ratio' => $ratio,
                'custom_settings' => $customSettings,
            ],
        ]);

        return $path;
    }

    /**
     * Build template variables from ContentDNA
     */
    protected function buildTemplateVariables(ContentDNA $dna, ?string $featuredImageUrl): array
    {
        return [
            'headline' => $dna->headline,
            'subheadline' => $dna->subheadline ?? '',
            'hotTake' => $dna->hot_take ?? '',
            'cta' => $dna->cta ?? '',
            'keyPoints' => $dna->key_points ?? [],
            'keywords' => $dna->keywords ?? [],
            'summaryShort' => $dna->summary_short,
            'summaryMedium' => $dna->summary_medium,
            'summaryLong' => $dna->summary_long,
            'tone' => $dna->tone,
            'intent' => $dna->intent,
            'featuredImage' => $featuredImageUrl,
        ];
    }

    /**
     * Get featured image URL from post
     */
    protected function getFeaturedImageUrl($post): ?string
    {
        if (!$post || !$post->featuredMedia) {
            return null;
        }

        // Return full URL to featured image
        return asset('storage/' . $post->featuredMedia->file_path);
    }

    /**
     * Get dimensions for size
     */
    protected function getDimensions(string $size): array
    {
        $dimensions = [
            '4:5' => ['width' => 1080, 'height' => 1350],
            '9:16' => ['width' => 1080, 'height' => 1920],
            '1:1' => ['width' => 1080, 'height' => 1080],
        ];

        return $dimensions[$size] ?? ['width' => 1080, 'height' => 1080];
    }

    /**
     * Get dimensions from channel (default sizes)
     */
    protected function getDimensionsFromChannel(string $channel): array
    {
        $map = [
            'instagram' => ['width' => 1080, 'height' => 1350], // 4:5 default
            'facebook' => ['width' => 1080, 'height' => 1080],  // 1:1
            'twitter' => ['width' => 1080, 'height' => 1080],   // 1:1
            'linkedin' => ['width' => 1080, 'height' => 1080],  // 1:1
        ];

        return $map[$channel] ?? ['width' => 1080, 'height' => 1080];
    }

    /**
     * Get size string from dimensions
     */
    protected function getSizeFromDimensions(SocialAsset $asset): string
    {
        if ($asset->channel === 'instagram' && $asset->asset_type === 'feed') {
            return '4:5';
        } elseif ($asset->channel === 'instagram' && $asset->asset_type === 'story') {
            return '9:16';
        }

        return '1:1';
    }

}
