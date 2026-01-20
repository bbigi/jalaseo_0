<?php

namespace App\Jobs;

use App\Models\ContentStrategy;
use App\Models\Post;
use App\Models\AiGenerateTemplate;
use App\Services\AIService;
use App\Services\AiGeneratePromptBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSingleArticle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes max per article

    protected $strategyId;
    protected $articleRole; // 'roundup' or 'deep_dive'
    protected $topicId; // null for roundup, set for deep_dive
    protected $templateId;

    /**
     * Create a new job instance.
     */
    public function __construct($strategyId, $articleRole, $topicId = null, $templateId = null)
    {
        $this->strategyId = $strategyId;
        $this->articleRole = $articleRole;
        $this->topicId = $topicId;
        $this->templateId = $templateId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $strategy = ContentStrategy::findOrFail($this->strategyId);

        try {
            // Get template
            $template = $this->getTemplate();

            // Get AI service
            $aiService = app(AIService::class);

            // Generate article based on role
            if ($this->articleRole === 'roundup') {
                $this->generateRoundupArticle($strategy, $aiService, $template);
            } else {
                $this->generateDeepDiveArticle($strategy, $aiService, $template);
            }

            // Check if all articles completed
            $this->checkCompletion($strategy);

        } catch (\Exception $e) {
            Log::error('Single article generation failed', [
                'strategy_id' => $strategy->id,
                'article_role' => $this->articleRole,
                'topic_id' => $this->topicId,
                'error' => $e->getMessage()
            ]);

            // Don't throw - let other jobs continue
            // Just mark this as failed in logs
        }
    }

    /**
     * Get AI template
     */
    protected function getTemplate()
    {
        if ($this->templateId) {
            $template = \App\Models\AiGenerateTemplate::find($this->templateId);
            if ($template) return $template;
        }

        // Fallback: use system default template
        $template = \App\Models\AiGenerateTemplate::where('created_by', 1)->first();

        if (!$template) {
            $template = new AiGenerateTemplate([
                'name' => 'Default Template',
                'ai_provider' => 'gemini',
                'ai_model' => null,
                'language' => 'id_ID',
                'article_type' => 'blog',
                'article_size' => 'medium',
                'tone' => 'professional',
                'point_of_view' => 'third_person',
                'text_readability' => 'general',
                'target_country' => 'Indonesia',
                'ai_content_cleaning' => false,
                'is_active' => true,
            ]);
        }

        return $template;
    }

    /**
     * Generate roundup article
     */
    protected function generateRoundupArticle(ContentStrategy $strategy, AIService $aiService, $template): void
    {
        $selectedResults = $strategy->selectedResults();
        $rankedResults = collect($strategy->ranked_topics);
        $platform = $strategy->search->platform;
        $currentMonth = now()->format('F Y');
        $countryCode = $strategy->search->country_code ?? 'ID';

        // Build trending topics summary
        $topicsSummary = $selectedResults->map(function ($result, $index) use ($rankedResults) {
            $rank = $rankedResults->firstWhere('id', $result->id);
            return sprintf(
                "%d. %s\n   Author: %s\n   Engagement: %s likes, %s comments, %s shares\n   Hashtags: %s\n   Score: %s/100",
                $index + 1,
                $result->content,
                $result->author ?? 'N/A',
                number_format($result->engagement_likes),
                number_format($result->engagement_comments),
                number_format($result->engagement_shares),
                implode(', ', array_slice($result->hashtags ?? [], 0, 5)),
                $rank['score'] ?? 'N/A'
            );
        })->implode("\n\n");

        $basePrompt = app(AiGeneratePromptBuilder::class)->build($template, [
            'generation_type' => 'from_text',
            'title' => "Trending {$platform} {$countryCode} {$currentMonth}",
            'reference_text' => $topicsSummary,
        ]);

        $strategyInstructions = <<<TXT
Create a roundup article summarizing ALL trending topics from the reference text.
- The final headline MUST include "{$currentMonth}".
- Include engagement metrics (likes, comments, shares) for each topic.
- Include top hashtags for each topic in a short list.
- Use <h2> for each topic section and keep paragraphs readable.
TXT;

        $prompt = $this->injectBeforeOutputFormat($basePrompt, $strategyInstructions);

        $response = $aiService->generate($prompt, 'article_generation', [
            'provider' => $template->ai_provider ?? null,
            'model' => $template->ai_model ?? null,
            'max_tokens' => app(AiGeneratePromptBuilder::class)->getMaxTokens($template->article_size ?? 'medium'),
            'temperature' => 0.7,
        ]);

        // Parse JSON response
        $article = $this->parseAIResponse($response['content']);

        // Create post
        Post::create([
            'content_strategy_id' => $strategy->id,
            'article_role' => 'roundup',
            'title' => $article['title'] ?? "Trending {$platform} Indonesia {$currentMonth}",
            'content' => $article['content'] ?? $response['content'],
            'article_type' => 'blog',
            'seo_focus_keyword' => $article['focus_keyword'] ?? $article['keywords'][0] ?? "trending {$platform} {$countryCode}",
            'seo_meta_description' => $article['meta_description'] ?? '',
            'source_type' => 'ai_generated',
            'created_by' => $strategy->created_by,
        ]);

        Log::info('Roundup article generated', ['strategy_id' => $strategy->id]);
    }

    /**
     * Generate deep dive article
     */
    protected function generateDeepDiveArticle(ContentStrategy $strategy, AIService $aiService, $template): void
    {
        $selectedResults = $strategy->selectedResults()->keyBy('id');
        $rankedResults = collect($strategy->ranked_topics);
        $rank = $rankedResults->firstWhere('id', $this->topicId);

        if (!$rank) {
            Log::error('Topic not found in ranked results', ['topic_id' => $this->topicId]);
            return;
        }

        $result = $selectedResults->get($rank['id']);
        if (!$result) {
            Log::error('Result not found', ['topic_id' => $this->topicId]);
            return;
        }

        $referenceText = "Topic: {$result->content}\n"
            . "Author: {$result->author}\n"
            . "Engagement: {$result->engagement_likes} likes, {$result->engagement_comments} comments, {$result->engagement_shares} shares\n"
            . "Hashtags: {$this->formatHashtags($result->hashtags ?? [])}\n"
            . "AI score: {$rank['score']}/100 ({$rank['reasoning']})";

        $basePrompt = app(AiGeneratePromptBuilder::class)->build($template, [
            'generation_type' => 'from_text',
            'title' => (string) $result->content,
            'reference_text' => $referenceText,
        ]);

        $strategyInstructions = <<<TXT
Create a deep-dive article that expands the topic with practical, actionable guidance (not just a summary).
- Include a short section that references why the topic is trending using the engagement metrics provided.
- Include an FAQ section with 5-7 questions (use <h3> per question).
- End with a clear conclusion and a gentle CTA.
TXT;

        $prompt = $this->injectBeforeOutputFormat($basePrompt, $strategyInstructions);

        $response = $aiService->generate($prompt, 'article_generation', [
            'provider' => $template->ai_provider ?? null,
            'model' => $template->ai_model ?? null,
            'max_tokens' => app(AiGeneratePromptBuilder::class)->getMaxTokens($template->article_size ?? 'medium'),
            'temperature' => 0.7,
        ]);

        // Parse JSON response
        $article = $this->parseAIResponse($response['content']);

        // Create post
        Post::create([
            'content_strategy_id' => $strategy->id,
            'article_role' => 'deep_dive',
            'source_topic_id' => $result->id,
            'title' => $article['title'] ?? $result->content,
            'content' => $article['content'] ?? $response['content'],
            'article_type' => 'blog',
            'seo_focus_keyword' => $article['focus_keyword'] ?? $article['keywords'][0] ?? $this->extractKeyword($result->content),
            'seo_meta_description' => $article['meta_description'] ?? '',
            'source_type' => 'ai_generated',
            'created_by' => $strategy->created_by,
        ]);

        Log::info('Deep dive article generated', [
            'strategy_id' => $strategy->id,
            'topic_id' => $result->id
        ]);
    }

    /**
     * Check if all articles completed and update strategy status
     */
    protected function checkCompletion(ContentStrategy $strategy): void
    {
        $articlesCount = Post::where('content_strategy_id', $strategy->id)->count();
        $expectedArticles = $strategy->expected_articles;

        if ($articlesCount >= $expectedArticles) {
            $strategy->update(['status' => 'completed']);
            Log::info('Content strategy completed', [
                'strategy_id' => $strategy->id,
                'articles_count' => $articlesCount
            ]);
        }
    }

    /**
     * Format hashtags for prompt
     */
    protected function formatHashtags(?array $hashtags): string
    {
        if (empty($hashtags)) return 'N/A';
        return implode(', ', array_map(fn($tag) => "#{$tag}", $hashtags));
    }

    /**
     * Extract focus keyword from content
     */
    protected function extractKeyword(string $content): string
    {
        $words = explode(' ', trim(strip_tags($content)));
        return implode(' ', array_slice($words, 0, min(5, count($words))));
    }

    /**
     * Get target word count from article size
     */
    protected function getWordCountFromSize(string $size): int
    {
        return match(strtolower($size)) {
            'small' => 500,
            'medium' => 800,
            'large' => 1200,
            'x-large' => 1500,
            default => 800, // Default to medium
        };
    }

    /**
     * Parse AI response (JSON format)
     */
    protected function parseAIResponse($response)
    {
        $cleaned = $this->stripCodeFences(trim((string) $response));

        $data = json_decode($this->escapeNewlinesInJsonStrings($cleaned), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $this->normalizeParsedData($data, (string) $response);
        }

        $firstBrace = strpos($cleaned, '{');
        $lastBrace = strrpos($cleaned, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $json = substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1);
            $data = json_decode($this->escapeNewlinesInJsonStrings($json), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $this->normalizeParsedData($data, (string) $response);
            }
        }

        return [
            'title' => 'Generated Article',
            'content' => (string) $response,
            'meta_description' => mb_substr(trim(strip_tags((string) $response)), 0, 160),
            'focus_keyword' => '',
            'keywords' => [],
        ];
    }

    private function injectBeforeOutputFormat(string $prompt, string $extra): string
    {
        $marker = "=== OUTPUT FORMAT";
        $pos = strpos($prompt, $marker);
        if ($pos === false) {
            return $prompt . "\n\n=== STRATEGY ADD-ON ===\n" . $extra . "\n";
        }

        return substr($prompt, 0, $pos)
            . "\n\n=== STRATEGY ADD-ON ===\n"
            . $extra
            . "\n\n"
            . substr($prompt, $pos);
    }

    private function stripCodeFences(string $raw): string
    {
        $raw = preg_replace('/^\s*```(?:json)?\s*\n?/im', '', $raw);
        $raw = preg_replace('/\n?```\s*$/im', '', $raw);
        $raw = preg_replace('/^\s*json\s*/i', '', $raw);

        return trim($raw);
    }

    private function escapeNewlinesInJsonStrings(string $json): string
    {
        $result = '';
        $inString = false;
        $escapeNext = false;
        $length = strlen($json);

        for ($i = 0; $i < $length; $i++) {
            $char = $json[$i];

            if ($escapeNext) {
                $result .= $char;
                $escapeNext = false;
                continue;
            }

            if ($char === '\\') {
                $result .= $char;
                $escapeNext = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $result .= $char;
                continue;
            }

            if ($inString && ($char === "\n" || $char === "\r")) {
                $result .= '\\n';
                continue;
            }

            $result .= $char;
        }

        return $result;
    }

    private function normalizeParsedData(array $data, string $rawResponse): array
    {
        $keywords = [];
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            $keywords = array_values(array_filter(array_map('strval', $data['keywords'])));
        }

        return [
            'title' => (string) ($data['title'] ?? 'Generated Article'),
            'content' => (string) ($data['content'] ?? $rawResponse),
            'meta_description' => (string) ($data['meta_description'] ?? ''),
            'focus_keyword' => (string) ($data['focus_keyword'] ?? ''),
            'featured_image_prompt' => (string) ($data['featured_image_prompt'] ?? ''),
            'keywords' => $keywords,
        ];
    }
}
