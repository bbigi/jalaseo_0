<?php

namespace App\Services;

use App\Helpers\CountryHelper;
use App\Models\AiGenerateTemplate;

class AiGeneratePromptBuilder
{
    public function build(AiGenerateTemplate $template, array $context): string
    {
        $language = $this->getLanguageName($template->language ?? 'id_ID');
        $tone = $template->tone ?? 'Professional';
        $pov = $template->point_of_view ?? 'Third Person';
        $articleType = $template->article_type ?? 'blog';
        $wordCount = $this->getTargetWordCount($template->article_size ?? 'medium');
        $charRange = $this->getTargetCharacterRange($wordCount);
        $readability = $template->text_readability ?? '';
        $targetCountry = $template->target_country ?? '';
        $cleaning = (bool) ($template->ai_content_cleaning ?? false);

        $sourceBlock = $this->buildSourceBlock($context);
        $referenceGuidance = $this->buildReferenceGuidance($context);
        $toneInstruction = $this->getToneInstruction($tone);
        $povInstruction = $this->getPointOfViewInstruction($pov);
        $articleTypeInstruction = $this->getArticleTypeInstruction($articleType);
        $countryInstruction = $this->getTargetCountryInstruction($targetCountry);

        $prompt = <<<PROMPT
You are a professional content writer. Create a high-quality article based on the inputs below.

=== CONTENT SETTINGS ===
Language: {$language}
Article type: {$articleType}
Target length: {$wordCount}+ words (~{$charRange['min']}-{$charRange['max']} characters)
Tone: {$tone}
Tone guidance: {$toneInstruction}
Point of view: {$pov}
Point of view guidance: {$povInstruction}
{$this->formatOptional("Article type guidance", $articleTypeInstruction)}
{$this->formatOptional("Target country", $targetCountry)}
{$this->formatOptional("Target country guidance", $countryInstruction)}
{$this->formatOptional("Readability", $this->getReadabilityInstruction($readability))}
{$this->formatOptional("Content cleaning", $this->getContentCleaningInstruction($cleaning))}

=== SOURCE CONTEXT ===
Title/Topic: {$context['title']}
{$sourceBlock}

=== INSTRUCTIONS ===
1. Write an original, well-structured article with clear headings.
2. Use HTML tags for formatting: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>.
3. Keep paragraphs short and readable (3-4 sentences).
4. Make the article informative and actionable.
5. Ensure the content matches the selected tone, point of view, and article type guidance.
6. Use the provided Title/Topic as the primary keyword; set focus_keyword to that exact phrase.
7. Meta description must be a unique 120-160 char summary, not a copy of the title, and must include the primary keyword.
8. Ensure the article reaches the target length and ends with a clear conclusion; do not stop mid-sentence.
9. Avoid any markdown syntax; return clean HTML only inside JSON fields.
10. Generate 3-5 tags: First tag MUST be the focus keyword. Other tags from main article themes and mentioned personalities. All tags must be short (1-2 words) in {$language}.
{$referenceGuidance}

=== OUTPUT FORMAT (JSON ONLY) ===
{
  "title": "SEO-friendly title",
  "content": "<h2>...</h2><p>...</p>",
  "meta_description": "Meta description (120-160 chars)",
  "focus_keyword": "main keyword",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "featured_image_prompt": "short image prompt"
}

Return ONLY valid JSON with no markdown or code fences. Escape any double quotes and line breaks inside JSON strings (use \\n).
PROMPT;

        return $this->normalizePrompt($prompt);
    }

    public function getMaxTokens(string $articleSize): int
    {
        return match ($articleSize) {
            'x-small' => 1200,
            'small' => 2000,
            'medium' => 3000,
            'large' => 5000,
            'extra_large' => 8000,
            default => 3000,
        };
    }

    private function buildSourceBlock(array $context): string
    {
        $generationType = $context['generation_type'] ?? 'from_title';
        $lines = [];

        if ($generationType === 'from_reference' || $generationType === 'from_insight') {
            if (!empty($context['topic'])) {
                $lines[] = "Reference topic:\n" . $context['topic'];
            }
            if (!empty($context['description'])) {
                $lines[] = "Reference description:\n" . $context['description'];
            }
            if (!empty($context['url'])) {
                $lines[] = "Reference URL: " . $context['url'];
            }
            if (!empty($context['url_content'])) {
                $lines[] = "Reference content:\n" . $context['url_content'];
            }
        } elseif ($generationType === 'from_text') {
            if (!empty($context['reference_text'])) {
                $lines[] = "Reference text:\n" . $context['reference_text'];
            }
            if (!empty($context['source_name'])) {
                $lines[] = "Source name: " . $context['source_name'];
            }
        }

        if (!$lines) {
            return "Source: Title only.";
        }

        return implode("\n", $lines);
    }

    private function buildReferenceGuidance(array $context): string
    {
        $generationType = $context['generation_type'] ?? '';
        if (!in_array($generationType, ['from_reference', 'from_text', 'from_insight'], true)) {
            return '';
        }

        $lines = [
            '7. Treat the Title/Topic as the main topic only (not the final headline). Create a NEW title.',
            '8. Do not copy sentences; paraphrase and expand with original wording.',
        ];

        if ($generationType === 'from_reference' || $generationType === 'from_insight') {
            $sourceDomain = trim((string) ($context['source_domain'] ?? ''));
            if ($sourceDomain !== '') {
                $lines[] = "9. Mention the source in the first paragraph using this format: \"Dilansir dari {$sourceDomain}, ...\"";
                $lines[] = "10. Add a source credit at the end: <p><em>Sumber: {$sourceDomain}</em></p>";
            }
        }

        if ($generationType === 'from_text') {
            $sourceName = trim((string) ($context['source_name'] ?? ''));
            if ($sourceName !== '') {
                $lines[] = "9. Mention the source in the first paragraph using this format: \"Dilansir dari {$sourceName}, ...\"";
                $lines[] = "10. Add a source credit at the end: <p><em>Sumber: {$sourceName}</em></p>";
            }
        }

        return "\n" . implode("\n", $lines);
    }

    private function getLanguageName(string $code): string
    {
        foreach (CountryHelper::getLanguages() as $lang) {
            if ($lang['code'] === $code) {
                return $lang['name'];
            }
        }

        return $code;
    }

    private function getTargetWordCount(string $size): int
    {
        return match ($size) {
            'x-small' => 400,
            'small' => 650,
            'large' => 1600,
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
            'empathetic' => 'Understanding tone that acknowledges reader concerns.',
            default => 'Clear, professional tone.',
        };
    }

    private function getPointOfViewInstruction(string $pov): string
    {
        return match (strtolower($pov)) {
            'first person' => 'Use first-person voice (I, we) consistently.',
            'second person' => 'Address the reader directly (you).',
            'third person' => 'Write in third person (they, the reader).',
            'mixed' => 'Blend POVs only when it improves clarity.',
            default => 'Write in third person by default.',
        };
    }

    private function getArticleTypeInstruction(string $type): string
    {
        return match (strtolower($type)) {
            'news article' => 'Use inverted pyramid: key facts first, then details.',
            'how-to guide' => 'Provide step-by-step instructions with clear H2/H3.',
            'listicle' => 'Use numbered sections and concise takeaways.',
            'product review' => 'Cover features, pros/cons, and a balanced verdict.',
            'case study' => 'Describe context, approach, results, and lessons learned.',
            'tutorial' => 'Explain process step-by-step with practical tips.',
            'opinion piece' => 'State a clear stance and support with arguments.',
            'blog post' => 'Use a structured intro, body, and conclusion.',
            default => 'Use a structured intro, body, and conclusion.',
        };
    }

    private function getTargetCountryInstruction(string $country): string
    {
        if (trim($country) === '') {
            return '';
        }

        return "Use local spelling, units, currency, and examples relevant to {$country}.";
    }

    private function formatOptional(string $label, string $value): string
    {
        if (trim($value) === '') {
            return '';
        }

        return "{$label}: {$value}";
    }

    private function normalizePrompt(string $prompt): string
    {
        return preg_replace("/\n{3,}/", "\n\n", trim($prompt));
    }
}
