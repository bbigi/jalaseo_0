<?php

namespace App\Services;

use App\Models\Post;
use App\Models\ContentDNA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentDNAService
{
    protected $aiService;

    public function __construct()
    {
        $this->aiService = new AIService();
    }

    public function extractDNA(Post $post): ContentDNA
    {
        $existingDNA = $post->contentDNA;
        if ($existingDNA) {
            Log::info("DNA already exists for post {$post->id}");
            return $existingDNA;
        }

        $prompt = $this->buildExtractionPrompt($post);
        $result = $this->aiService->generate($prompt, 'article_generation', [
            'max_tokens' => 2000,
            'temperature' => 0.3,
        ]);

        $dnaData = $this->parseAIResponse($result['content']);
        $this->validateDNAData($dnaData);

        // Truncate fields to ensure database constraints
        $contentDNA = ContentDNA::create([
            'post_id' => $post->id,
            'created_by' => $post->created_by,
            'headline' => Str::limit($dnaData['headline'], 255, ''),
            'subheadline' => isset($dnaData['subheadline']) ? Str::limit($dnaData['subheadline'], 500, '') : null,
            'hot_take' => $dnaData['hot_take'] ?? null,
            'cta' => isset($dnaData['cta']) ? Str::limit($dnaData['cta'], 300, '') : null,
            'key_points' => $dnaData['key_points'] ?? [],
            'keywords' => $dnaData['keywords'] ?? [],
            'summary_short' => Str::limit($dnaData['summary_short'], 140, ''),
            'summary_medium' => Str::limit($dnaData['summary_medium'], 300, ''),
            'summary_long' => Str::limit($dnaData['summary_long'], 700, ''),
            'tone' => $dnaData['tone'] ?? 'neutral',
            'intent' => $dnaData['intent'] ?? 'evergreen',
        ]);

        Log::info("DNA extracted for post {$post->id}", [
            'dna_id' => $contentDNA->id,
            'provider' => $result['provider'] ?? 'unknown',
        ]);

        return $contentDNA;
    }

    protected function buildExtractionPrompt(Post $post): string
    {
        $title = $post->title;
        $content = strip_tags($post->content);
        $articleType = $post->article_type;

        return <<<PROMPT
You are a content DNA extraction AI. Analyze this article and extract its DNA for social media.

**Article Type:** {$articleType}
**Title:** {$title}
**Content:** {$content}

Extract these DNA components:

1. headline (max 255 chars): Main headline for social media
2. subheadline (max 500 chars, optional): Supporting headline
3. hot_take (optional): Unique angle or opinion
4. cta (max 300 chars, optional): Call-to-action
5. key_points (array of 3): 3 main points
6. keywords (array of 10): 10 keywords/hashtags
7. summary_short (max 140 chars): Twitter summary
8. summary_medium (max 300 chars): Facebook summary
9. summary_long (max 700 chars): LinkedIn summary
10. tone (neutral|informative|urgent|inspiring)
11. intent (breaking|evergreen|refresh)

Return ONLY valid JSON (no markdown, no extra text):
{
  "headline": "...",
  "subheadline": "...",
  "hot_take": "...",
  "cta": "...",
  "key_points": ["point1", "point2", "point3"],
  "keywords": ["kw1", "kw2", "kw3", "kw4", "kw5", "kw6", "kw7", "kw8", "kw9", "kw10"],
  "summary_short": "...",
  "summary_medium": "...",
  "summary_long": "...",
  "tone": "neutral",
  "intent": "evergreen"
}
PROMPT;
    }

    protected function parseAIResponse(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON from AI: ' . json_last_error_msg());
        }

        return $data;
    }

    protected function validateDNAData(array $data): void
    {
        $required = ['headline', 'key_points', 'keywords', 'summary_short', 'summary_medium', 'summary_long', 'tone', 'intent'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing field: {$field}");
            }
        }

        if (!is_array($data['key_points']) || count($data['key_points']) !== 3) {
            throw new \Exception('key_points must be array of 3');
        }

        if (!is_array($data['keywords']) || count($data['keywords']) !== 10) {
            throw new \Exception('keywords must be array of 10');
        }

        $validTones = ['neutral', 'informative', 'urgent', 'inspiring'];
        if (!in_array($data['tone'], $validTones)) {
            throw new \Exception('Invalid tone');
        }

        $validIntents = ['breaking', 'evergreen', 'refresh'];
        if (!in_array($data['intent'], $validIntents)) {
            throw new \Exception('Invalid intent');
        }
    }

    public function reExtractDNA(Post $post): ContentDNA
    {
        if ($post->contentDNA) {
            $post->contentDNA->delete();
        }
        return $this->extractDNA($post);
    }
}
