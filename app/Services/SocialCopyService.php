<?php

namespace App\Services;

use App\Models\ContentDNA;
use App\Models\SocialAsset;
use App\Services\TemplateLayoutEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class SocialCopyService
{
    protected $aiService;
    protected $layoutEngine;

    public function __construct()
    {
        $this->aiService = new AIService();
        $this->layoutEngine = new TemplateLayoutEngine();
    }

    /**
     * Generate social copy for all channels from DNA
     */
    public function generateAllCopies(ContentDNA $dna, array $channels = ['instagram', 'facebook', 'twitter', 'linkedin']): array
    {
        $assets = [];

        foreach ($channels as $channel) {
            $assets[$channel] = $this->generateCopy($dna, $channel);
        }

        return $assets;
    }

    /**
     * Generate social copy for specific channel from DNA
     */
    public function generateCopy(ContentDNA $dna, string $channel, string $assetType = 'feed'): SocialAsset
    {
        // Load post relationship for featured media access
        $dna->load('post.featuredMedia');

        // Build channel-specific prompt
        $prompt = $this->buildChannelPrompt($dna, $channel, $assetType);

        // Call AI service
        $result = $this->aiService->generate($prompt, 'article_generation', [
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ]);

        $copyText = trim($result['content']);

        // Clean up any markdown if present
        $copyText = $this->cleanupCopy($copyText);

        // Default visual from featured image (if available)
        $visualPath = null;
        $visualSize = null;
        $post = $dna->post;
        if ($post && $post->featuredMedia) {
            $visualPath = $post->featuredMedia->file_path;
            $visualSize = 'original';
        }

        // Create social asset
        $asset = SocialAsset::create([
            'post_id' => $dna->post_id,
            'created_by' => $dna->created_by,
            'channel' => $channel,
            'asset_type' => $assetType,
            'copy_text' => $copyText,
            'copy_version' => 1,
            'status' => 'draft',
            'visual_path' => $visualPath,
            'visual_size' => $visualSize,
        ]);

        Log::info("Social copy generated for {$channel}", [
            'asset_id' => $asset->id,
            'post_id' => $dna->post_id,
            'provider' => $result['provider'] ?? 'unknown',
        ]);

        // Auto-generate visual with News Gradient template
        try {
            $this->generateVisualForAsset($asset, $dna);
        } catch (\Exception $e) {
            Log::warning("Failed to auto-generate visual for asset {$asset->id}", [
                'error' => $e->getMessage()
            ]);
        }

        return $asset;
    }

    /**
     * Build platform-specific prompt
     */
    protected function buildChannelPrompt(ContentDNA $dna, string $channel, string $assetType): string
    {
        $baseContext = $this->buildBaseContext($dna);

        switch ($channel) {
            case 'instagram':
                return $this->buildInstagramPrompt($dna, $baseContext, $assetType);
            case 'facebook':
                return $this->buildFacebookPrompt($dna, $baseContext);
            case 'twitter':
                return $this->buildTwitterPrompt($dna, $baseContext, $assetType);
            case 'linkedin':
                return $this->buildLinkedInPrompt($dna, $baseContext);
            default:
                throw new \Exception("Unsupported channel: {$channel}");
        }
    }

    protected function buildBaseContext(ContentDNA $dna): string
    {
        $keyPoints = implode("\n- ", $dna->key_points ?? []);
        $keywords = implode(', ', $dna->keywords ?? []);

        // Use user's locale from auth, fallback to app locale
        $userLocale = auth()->user()->locale ?? app()->getLocale();
        $language = $userLocale === 'id' ? 'Bahasa Indonesia' : 'English';

        // Get full article content
        $post = $dna->post;
        $fullContent = $post ? strip_tags($post->content) : '';
        $articleTitle = $post ? $post->title : '';

        return <<<CONTEXT
**Article Title:** {$articleTitle}

**Full Article Content:**
{$fullContent}

**Content DNA (use this to guide your angle):**
- Headline: {$dna->headline}
- Subheadline: {$dna->subheadline}
- Hot Take: {$dna->hot_take}
- CTA: {$dna->cta}
- Tone: {$dna->tone}
- Intent: {$dna->intent}

**Key Points:**
- {$keyPoints}

**Keywords:** {$keywords}

**Language:** {$language} (write the output FULLY in this language, maintain natural conversational flow)
CONTEXT;
    }

    protected function buildInstagramPrompt(ContentDNA $dna, string $context, string $assetType): string
    {
        if ($assetType === 'story') {
            return <<<PROMPT
Create Instagram Story text overlay for this content.

{$context}

Requirements:
- Max 3 lines of text
- Use emojis strategically
- Include swipe-up CTA if relevant
- Keep it visual and punchy
- Tone: {$dna->tone}

Return only the story text (no quotes, no extra formatting).
PROMPT;
        }

        // Feed post
        return <<<PROMPT
Create an Instagram Feed caption for this content.

{$context}

Requirements:
- Hook in first line (grab attention)
- 3-5 short paragraphs with line breaks
- Use emojis naturally (not excessive)
- Include 5-10 relevant hashtags at the end
- Strong CTA at the end
- Max 2200 characters
- Tone: {$dna->tone}

Return only the caption (no quotes, no extra formatting).
PROMPT;
    }

    protected function buildFacebookPrompt(ContentDNA $dna, string $context): string
    {
        return <<<PROMPT
Create a Facebook Page post for this content.

{$context}

Requirements:
- Start with engaging hook (question or bold statement)
- Write 4-6 paragraphs with natural conversational flow
- Develop the angle deeply - don't just summarize, tell a story or build an argument
- Each paragraph should add new insight or perspective
- Use line breaks between paragraphs for readability
- NO EMOJIS - focus on powerful words instead
- NO HASHTAGS - Facebook doesn't use hashtags effectively
- Strong CTA at the end that encourages discussion or action
- Target length: 1500-2000 characters (substantial, not superficial)
- Tone: {$dna->tone} but natural and human

Think like you're writing a mini-article that people will actually stop scrolling to read.

Return only the post text (no quotes, no extra formatting).
PROMPT;
    }

    protected function buildTwitterPrompt(ContentDNA $dna, string $context, string $assetType): string
    {
        if ($assetType === 'thread') {
            return <<<PROMPT
Create a Twitter/X thread (3-5 tweets) for this content.

{$context}

Requirements:
- Tweet 1: Strong hook + main point (max 280 chars)
- Tweet 2-4: Expand on key points (max 280 chars each)
- Final tweet: CTA + relevant hashtags
- Use emojis sparingly
- Each tweet must be standalone readable
- Include thread numbering (1/, 2/, etc)
- Tone: {$dna->tone}

Return tweets separated by "---" (no quotes, no extra formatting).
Example:
1/ First tweet here...
---
2/ Second tweet here...
PROMPT;
        }

        // Single tweet
        return <<<PROMPT
Create a single Twitter/X post for this content.

{$context}

Requirements:
- Max 280 characters
- Start with strong hook
- Include 1-2 relevant hashtags
- Clear value proposition
- Tone: {$dna->tone}

Return only the tweet text (no quotes, no extra formatting).
PROMPT;
    }

    protected function buildLinkedInPrompt(ContentDNA $dna, string $context): string
    {
        return <<<PROMPT
Create a LinkedIn post for this content.

{$context}

Requirements:
- Professional yet engaging tone - write like a thought leader, not a press release
- Start with thought-provoking question or insight that resonates with professionals
- Write 5-7 paragraphs with natural flow and depth
- Develop the professional angle thoroughly - connect to business impact, career insights, or industry trends
- Each paragraph should build on the previous one
- Use line breaks between paragraphs
- NO EMOJIS - maintain professional credibility
- Include 3-5 industry-relevant hashtags at the very end only
- Strong professional CTA that invites meaningful discussion
- Target length: 2000-2500 characters (substantial thought leadership piece)
- Tone: professional + {$dna->tone} but authentic and human

Think like you're sharing expertise with your professional network, not just posting news.

Return only the post text (no quotes, no extra formatting).
PROMPT;
    }

    protected function cleanupCopy(string $copy): string
    {
        // Remove markdown code blocks
        $copy = preg_replace('/```.*?\n/', '', $copy);
        $copy = preg_replace('/```\s*$/', '', $copy);

        // Remove quotes if AI wrapped in quotes
        $copy = preg_replace('/^["\']|["\']$/', '', $copy);

        // Clean up extra whitespace
        $copy = preg_replace('/\n{3,}/', "\n\n", $copy);

        return trim($copy);
    }

    /**
     * Regenerate copy for specific asset
     */
    public function regenerateCopy(SocialAsset $asset): SocialAsset
    {
        $dna = $asset->post->contentDNA;

        if (!$dna) {
            throw new \Exception("Post has no DNA. Extract DNA first.");
        }

        // Increment version
        $newVersion = $asset->copy_version + 1;

        // Generate new copy
        $prompt = $this->buildChannelPrompt($dna, $asset->channel, $asset->asset_type);
        $result = $this->aiService->generate($prompt, 'article_generation', [
            'max_tokens' => 1000,
            'temperature' => 0.8, // Higher temperature for variation
        ]);

        $copyText = $this->cleanupCopy(trim($result['content']));

        // Update asset
        $asset->update([
            'copy_text' => $copyText,
            'copy_version' => $newVersion,
        ]);

        Log::info("Copy regenerated for {$asset->channel}", [
            'asset_id' => $asset->id,
            'version' => $newVersion,
        ]);

        return $asset;
    }

    /**
     * Auto-generate visual using News Gradient template
     */
    protected function generateVisualForAsset(SocialAsset $asset, ContentDNA $dna): void
    {
        // Load post relationship with featured media
        $asset->load('post.featuredMedia');

        // Determine ratio based on channel
        $ratio = $this->determineRatioForChannel($asset->channel, $asset->asset_type);

        // Load News Gradient template
        $templatesPath = storage_path('app/templates/universal-templates.json');
        if (!file_exists($templatesPath)) {
            throw new \Exception('Template file not found');
        }

        $templates = json_decode(file_get_contents($templatesPath), true);
        $template = collect($templates)->firstWhere('id', 'news-gradient');

        if (!$template) {
            throw new \Exception('News Gradient template not found');
        }

        // Get user branding (auto-select first branding or use post's website branding)
        $branding = null;
        if ($asset->post->website_id) {
            $branding = \App\Models\UserBranding::where('user_id', $asset->created_by)
                ->where('website_id', $asset->post->website_id)
                ->with(['logo', 'website'])
                ->first();
        }

        if (!$branding) {
            $branding = \App\Models\UserBranding::where('user_id', $asset->created_by)
                ->with(['logo', 'website'])
                ->first();
        }

        // Prepare custom settings
        $customSettings = [];

        // Get featured image URL from post's featured media or DNA
        $featuredImageUrl = null;
        if ($asset->post && $asset->post->featuredMedia) {
            // Use full_url accessor which handles external URLs
            $featuredImageUrl = $asset->post->featuredMedia->full_url;
        } elseif ($dna->featured_image_url) {
            $featuredImageUrl = $dna->featured_image_url;
        }

        $customSettings['featuredImage'] = $featuredImageUrl;
        $customSettings['headline'] = $dna->headline ?? $asset->post->title;
        $customSettings['subheadline'] = $dna->subheadline ?? '';

        if ($branding && $branding->logo) {
            // Use full_url accessor which handles external URLs
            $customSettings['brandLogo'] = $branding->logo->full_url;
        }

        if ($branding && $branding->website && !empty($branding->website->url)) {
            $url = parse_url($branding->website->url, PHP_URL_HOST);
            $customSettings['websiteUrl'] = $url ?? $branding->website->url;
        } else {
            $customSettings['websiteUrl'] = 'webane.com';
        }

        // Calculate layout
        $layoutData = $this->layoutEngine->calculateLayout(
            $template,
            $ratio,
            $customSettings,
            $branding
        );

        // Save visual metadata
        $asset->update([
            'visual_metadata' => [
                'template_id' => 'news-gradient',
                'ratio' => $ratio,
                'headline' => $customSettings['headline'],
                'subheadline' => $customSettings['subheadline'],
                'featured_image' => $customSettings['featuredImage'],
                'branding_id' => $branding ? $branding->id : null,
                'custom_settings' => [],
                'canvas_state' => null,
            ],
        ]);

        Log::info("Visual metadata auto-generated for asset {$asset->id}", [
            'template' => 'news-gradient',
            'ratio' => $ratio,
            'channel' => $asset->channel,
        ]);

        // Auto-generate PNG using headless rendering
        Log::info("✅ Visual metadata saved", [
            'asset_id' => $asset->id,
            'channel' => $asset->channel,
            'ratio' => $ratio,
            'template' => 'news-gradient',
        ]);
    }

    /**
     * Determine aspect ratio based on channel
     */
    protected function determineRatioForChannel(string $channel, string $assetType): string
    {
        if ($channel === 'instagram' && $assetType === 'feed') {
            return '4:5';
        } elseif ($channel === 'instagram' && $assetType === 'story') {
            return '9:16';
        } elseif ($channel === 'facebook') {
            return '1:1';
        } elseif ($channel === 'twitter') {
            return '16:9';
        } elseif ($channel === 'linkedin') {
            return '1:1';
        }

        return '1:1'; // Default square
    }
}
