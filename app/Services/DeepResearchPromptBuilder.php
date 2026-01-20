<?php

namespace App\Services;

use App\Models\AiGenerateTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Deep Research Prompt Builder
 * 
 * Builds optimized prompts for article generation based on Deep Research insights.
 * Incorporates: keyword strategy, content gap, competitor analysis, supporting keywords.
 */
class DeepResearchPromptBuilder
{
    /**
     * Build comprehensive prompt from Deep Research data + AI Template settings
     */
    public function build(array $context, AiGenerateTemplate $template): string
    {
        // Extract context
        $keyword = $context['keyword'];
        $title = $context['title'];
        $seedKeyword = $context['seed_keyword'] ?? $keyword;
        $aiRecommendations = $context['ai_recommendations'] ?? [];
        $brandContext = $context['brand_context'] ?? '';
        $additionalNotes = $context['additional_notes'] ?? '';
        
        // Extract AI insights
        $strategySummary = $aiRecommendations['strategy_summary'] ?? '';
        $contentGap = $aiRecommendations['content_gap'] ?? '';
        $competitorStrategy = $aiRecommendations['competitor_strategy'] ?? '';
        $globalAnalysis = $aiRecommendations['global_analysis'] ?? [];
        $recommendedFormats = $aiRecommendations['recommended_formats'] ?? [];
        $informationalKeywords = $this->extractInformationalKeywords($aiRecommendations);
        
        // Template settings (align with /generate)
        $language = $this->getLanguageName($template->language ?? 'id_ID');
        $tone = $template->tone ?? 'Professional';
        $pov = $template->point_of_view ?? 'third_person';
        $wordCount = $this->getTargetWordCount($template->article_size ?? 'medium');
        $charRange = $this->getTargetCharacterRange($wordCount);
        $articleType = $template->article_type ?? 'blog';
        $readability = $template->text_readability ?? '';
        $targetCountry = $template->target_country ?? '';
        $cleaning = (bool) ($template->ai_content_cleaning ?? false);
        $articleTypeInstruction = $this->getArticleTypeInstruction($articleType);
        $toneInstruction = $this->getToneInstruction($tone);
        $povInstruction = $this->getPointOfViewInstruction($pov);
        $countryInstruction = $this->getTargetCountryInstruction($targetCountry);
        
        // Build prompt
        $prompt = $this->buildMainPrompt(
            keyword: $keyword,
            title: $title,
            seedKeyword: $seedKeyword,
            language: $language,
            tone: $tone,
            pov: $pov,
            wordCount: $wordCount,
            charRange: $charRange,
            articleType: $articleType,
            readability: $readability,
            targetCountry: $targetCountry,
            cleaning: $cleaning,
            toneInstruction: $toneInstruction,
            povInstruction: $povInstruction,
            articleTypeInstruction: $articleTypeInstruction,
            countryInstruction: $countryInstruction,
            strategySummary: $strategySummary,
            contentGap: $contentGap,
            competitorStrategy: $competitorStrategy,
            globalAnalysis: $globalAnalysis,
            recommendedFormats: $recommendedFormats,
            informationalKeywords: $informationalKeywords,
            brandContext: $brandContext,
            additionalNotes: $additionalNotes
        );
        
        Log::info("[DeepResearchPrompt] Built prompt for: {$keyword}, length: " . strlen($prompt));
        
        return $prompt;
    }
    
    /**
     * Main prompt builder
     */
    private function buildMainPrompt(
        string $keyword,
        string $title,
        string $seedKeyword,
        string $language,
        string $tone,
        string $pov,
        int $wordCount,
        array $charRange,
        string $articleType,
        string $readability,
        string $targetCountry,
        bool $cleaning,
        string $toneInstruction,
        string $povInstruction,
        string $articleTypeInstruction,
        string $countryInstruction,
        string $strategySummary,
        string $contentGap,
        string $competitorStrategy,
        array $globalAnalysis,
        array $recommendedFormats,
        array $informationalKeywords,
        string $brandContext,
        string $additionalNotes
    ): string {
        // Format supporting keywords (limit to first 4 for keywords array)
        $supportingKeywordsText = !empty($informationalKeywords)
            ? implode(', ', array_slice($informationalKeywords, 0, 8))
            : '';

        // Prepare suggested keywords array (primary + 4 supporting)
        $suggestedKeywords = [$keyword]; // Primary keyword first
        if (!empty($informationalKeywords)) {
            $suggestedKeywords = array_merge(
                $suggestedKeywords,
                array_slice($informationalKeywords, 0, 4)
            );
        }
        $keywordsListText = implode('", "', $suggestedKeywords);
        
        // Format recommended formats
        $formatsText = !empty($recommendedFormats)
            ? implode(', ', $recommendedFormats)
            : 'How-to Guide, Listicle';
            
        // Build strategic context block
        $strategicContext = $this->buildStrategicContext(
            $strategySummary,
            $contentGap,
            $competitorStrategy,
            $globalAnalysis
        );
        
        // Build brand context block
        $brandBlock = '';
        if (!empty($brandContext)) {
            $brandBlock = <<<BRAND

**BRAND/PRODUCT CONTEXT:**
{$brandContext}
- The brand above MUST be mentioned explicitly in the article content at least once, and should be made a main focus where possible.
- Integrate the brand naturally, but do not omit it under any circumstances.
- Position the brand as a solution or key example when addressing user problems or recommendations.
BRAND;
        }
        
        // Build additional notes block
        $notesBlock = '';
        if (!empty($additionalNotes)) {
            $notesBlock = <<<NOTES

**ADDITIONAL REQUIREMENTS:**
{$additionalNotes}
NOTES;
        }

        $prompt = <<<PROMPT
You are an expert SEO content strategist and professional writer. Create a comprehensive, high-quality article based on deep keyword research insights.

=== ARTICLE SPECIFICATIONS ===
**Target Keyword:** {$keyword}
**Suggested Title:** {$title}
**Seed Topic:** {$seedKeyword}
**Language:** {$language}
**Tone:** {$tone} ({$toneInstruction})
**Point of View:** {$povInstruction}
**Target Length:** {$wordCount}+ words (~{$charRange['min']}-{$charRange['max']} characters)
**Article Type:** {$articleType}
{$this->formatOptional("Article type guidance", $articleTypeInstruction)}
{$this->formatOptional("Target country", $targetCountry)}
{$this->formatOptional("Target country guidance", $countryInstruction)}
{$this->formatOptional("Readability", $this->getReadabilityInstruction($readability))}
{$this->formatOptional("Content cleaning", $this->getContentCleaningInstruction($cleaning))}
**Recommended Format Style:** {$formatsText}

=== DEEP RESEARCH INSIGHTS ===
{$strategicContext}

=== SUPPORTING KEYWORDS TO INCORPORATE ===
{$supportingKeywordsText}
(Naturally weave these keywords into the content to improve topical authority)
{$brandBlock}
{$notesBlock}

=== WRITING GUIDELINES ===
1. **CRITICAL: Use the EXACT suggested title - DO NOT modify it in any way**
2. Ensure the article reaches the target length and ends with a clear conclusion; do not stop mid-sentence.
3. **Apply content gap insights** - cover what competitors miss
4. **Match user intent** - provide exactly what searchers need
5. **Structure for readability** - use H2, H3, bullet points, short paragraphs
6. **Include supporting keywords naturally** - don't keyword stuff
7. **Add practical value** - examples, tips, actionable advice
8. **Write for humans first, SEO second**
9. Avoid any markdown syntax; return clean HTML only inside JSON fields.

=== KEYWORD STRATEGY ===
**Primary Keyword:** {$keyword}
**Supporting Keywords:** {$supportingKeywordsText}

You MUST use these EXACT keywords in the JSON output:
["{$keywordsListText}"]

=== OUTPUT FORMAT (JSON) ===
Return ONLY a valid JSON object with these fields:
{
  "title": "{$title}",
  "content": "<p>Full HTML formatted article content...</p>",
  "meta_description": "120-160 chars, include '{$keyword}' naturally, not a copy of the title",
  "focus_keyword": "{$keyword}",
  "keywords": ["{$keywordsListText}"],
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "featured_image_prompt": "short image prompt"
}

**CRITICAL REQUIREMENTS:**
1. Title field MUST be EXACTLY: "{$title}" (no changes allowed)
2. Meta description MUST include the primary keyword: "{$keyword}" and be unique (not the title).
3. Keywords array MUST be EXACTLY: ["{$keywordsListText}"]
4. focus_keyword MUST be "{$keyword}"
5. Tags array: Generate 3-5 tags. First tag MUST be "{$keyword}". Other tags from main article themes and mentioned personalities. All tags must be short (1-2 words) in {$language}.
6. Return ONLY the JSON object. No markdown code blocks. No explanations. Escape line breaks as \\n inside JSON strings.
PROMPT;

        return $prompt;
    }
    
    /**
     */
    private function buildStrategicContext(
        string $strategySummary,
        string $contentGap,
        string $competitorStrategy,
        array $globalAnalysis
    ): string {
        $sections = [];
        
        if (!empty($globalAnalysis)) {
            $intent = $globalAnalysis['primary_intent'] ?? '';
            $difficulty = $globalAnalysis['market_difficulty'] ?? '';
            $recommendation = $globalAnalysis['recommendation_summary'] ?? '';
            
            if ($intent || $difficulty || $recommendation) {
                $sections[] = "**Market Analysis:**
- Primary Intent: {$intent}
- Market Difficulty: {$difficulty}
- Strategic Direction: {$recommendation}";
            }
        }
        
        if (!empty($strategySummary)) {
            $sections[] = "**Content Strategy:**
{$strategySummary}";
        }
        
        if (!empty($contentGap)) {
            $sections[] = "**Content Gap Opportunity (What competitors DON'T cover):**
{$contentGap}
→ This is your competitive advantage. Make sure to address this gap comprehensively.";
        }
        
        if (!empty($competitorStrategy)) {
            $sections[] = "**Competitor Approach:**
{$competitorStrategy}
→ Differentiate by providing unique value and deeper coverage.";
        }
        
        return !empty($sections) ? implode("\n\n", $sections) : 'Focus on creating comprehensive, user-focused content.';
    }
    
    /**
     * Extract informational keywords from AI recommendations
     */
    private function extractInformationalKeywords(array $aiRecommendations): array
    {
        $keywords = [];
        $informational = $aiRecommendations['informational'] ?? [];
        foreach ($informational as $item) {
            if (is_array($item) && !empty($item['keyword'])) {
                $keywords[] = $item['keyword'];
            } elseif (is_string($item)) {
                $keywords[] = $item;
            }
        }
        return $keywords;
    }

    /**
     * Get language name from code
     */
    private function getLanguageName(?string $code): string
    {
        $map = [
            'id_ID' => 'Indonesian (Bahasa Indonesia)',
            'en_US' => 'English (US)',
            'en_GB' => 'English (UK)',
            'id' => 'Indonesian',
            'en' => 'English',
        ];

        return $map[$code] ?? $code;
    }

    private function getTargetWordCount(string $size): int
    {
        return match ($size) {
            'x-small' => 400,
            'small', 'short' => 650,
            'large', 'long' => 1600,
            'very_long', 'extra_large' => 2500,
            default => 1000,
        };
    }

    private function getTargetCharacterRange(int $wordCount): array
    {
        $min = (int) round($wordCount * 5.0);
        $max = (int) round($wordCount * 7.0);

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    private function getReadabilityInstruction(string $level): string
    {
        return match (strtolower($level)) {
            'elementary' => 'Child-friendly language. Short sentences, simple words, explain terms plainly.',
            'middle' => 'Middle school level. Clear sentences, minimal jargon, brief explanations.',
            'high' => 'High school level. Moderate complexity, introduce terms with short definitions.',
            'college' => 'College level. Advanced vocabulary allowed, but keep clarity and structure.',
            'graduate' => 'Graduate level. Dense, precise language with deeper analysis.',
            'technical' => 'Technical/professional audience. Use domain terms, avoid oversimplifying.',
            'academic' => 'Academic/research style. Formal tone, evidence-focused, structured arguments.',
            'general' => 'General adult audience. Plain language, balanced detail, no heavy jargon.',
            default => '',
        };
    }

    private function getContentCleaningInstruction(bool $enabled): string
    {
        if (!$enabled) {
            return '';
        }

        return 'Remove AI-specific phrases (for example: "as an AI", "in conclusion"). Avoid filler, repetition, and robotic wording; keep a natural human tone.';
    }

    private function getToneInstruction(string $tone): string
    {
        return match (strtolower($tone)) {
            'professional' => 'Clear, confident, business-ready tone. Avoid slang.',
            'casual' => 'Relaxed and simple tone. Use everyday language.',
            'friendly' => 'Warm and approachable tone. Use inclusive wording.',
            'formal' => 'Formal tone with structured sentences and precise wording.',
            'informative' => 'Fact-focused tone with concise explanations.',
            'persuasive' => 'Convincing tone with clear benefits and gentle CTAs.',
            'educational' => 'Teaching tone with step-by-step clarity.',
            'conversational' => 'Natural dialogue tone, but still structured.',
            'technical' => 'Technical tone with accurate terminology.',
            'humorous' => 'Light humor used sparingly and appropriately.',
            'inspirational' => 'Motivational tone with uplifting language.',
            'authoritative' => 'Expert voice with confident statements.',
            default => 'Clear, confident, business-ready tone. Avoid slang.',
        };
    }

    private function getPointOfViewInstruction(string $pov): string
    {
        return match (strtolower(str_replace(' ', '_', $pov))) {
            'first_person' => 'First person (saya/kami/I/we)',
            'second_person' => 'Second person (Anda/kamu/you)',
            'third_person' => 'Third person (objective/neutral)',
            default => 'Third person (objective/neutral)',
        };
    }

    private function getArticleTypeInstruction(string $type): string
    {
        $type = strtolower($type);
        return match ($type) {
            'news' => 'Timely, factual updates with clear context and source attribution.',
            'blog', 'article' => 'Informative, well-structured article with headings and actionable tips.',
            'review' => 'Balanced review with pros/cons, clear verdict, and recommendations.',
            default => '',
        };
    }

    private function getTargetCountryInstruction(string $country): string
    {
        if (!$country) {
            return '';
        }

        return "Localize examples, spelling, and context for {$country}. Reference local nuances when relevant.";
    }

    private function formatOptional(string $label, string $value): string
    {
        if (trim($value) === '') {
            return '';
        }

        return "{$label}: {$value}";
    }

    /**
     * Get max tokens for AI request
     */
    public function getMaxTokens(?string $size): int
    {
        return match ($size) {
            'x-small' => 1200,
            'small', 'short' => 2000,
            'medium' => 3000,
            'large', 'long' => 5000,
            'very_long', 'extra_large' => 8000,
            default => 3000,
        };
    }
}
