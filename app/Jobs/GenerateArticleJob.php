<?php

namespace App\Jobs;

use App\Models\AiGenerateTemplate;
use App\Services\AIService;
use App\Models\Post;
use App\Services\FirecrawlService;
use App\Models\Insight;
use App\Models\PostTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max

    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobId = $this->data['job_id'];

        try {
            $this->updateProgress(10, 'Initializing...');

            // 1. Load template
            $template = AiGenerateTemplate::findOrFail($this->data['template_id']);
            $this->updateProgress(20, 'Loading template...');

            // 2. Prepare context for AI
            $context = $this->prepareContext();
            $this->updateProgress(30, 'Preparing content strategy...');

            // 3. Build AI prompt from template
            $prompt = $this->buildPrompt($template, $context);
            $this->updateProgress(40, 'Contacting AI service...');

            Log::info("[GenerateArticle] Calling AI for job: {$jobId}");

            // 4. Generate article with AIService
            $aiService = app(AIService::class);
            $promptBuilder = app(\App\Services\AiGeneratePromptBuilder::class);
            $options = [
                'max_tokens' => $promptBuilder->getMaxTokens($template->article_size ?? 'medium'),
                'temperature' => 0.7,
            ];
            if (!empty($template->ai_provider)) {
                $options['provider'] = $template->ai_provider;
            }
            if (!empty($template->ai_model)) {
                $options['model'] = $template->ai_model;
            }
            $result = $aiService->generate($prompt, 'article_generation', $options);

            $this->updateProgress(70, 'Processing AI response...');

            // 5. Parse result
            $article = $this->parseArticle($result['content']);
            $article['meta_description'] = $this->resolveMetaDescription($article);
            $article['focus_keyword'] = $this->resolveFocusKeyword($article);
            $this->updateProgress(85, 'Generating SEO metadata...');

            // Validate tags: focus keyword must be first tag
            $tags = $article['tags'] ?? [];
            if (!empty($tags) && !empty($article['focus_keyword'])) {
                // Ensure focus keyword is in tags (case-insensitive check)
                $focusKeywordLower = strtolower($article['focus_keyword']);
                $tagsLower = array_map('strtolower', $tags);

                if (!in_array($focusKeywordLower, $tagsLower)) {
                    // Add focus keyword as first tag if missing
                    array_unshift($tags, $article['focus_keyword']);
                    Log::info('GenerateArticleJob: Focus keyword added to tags', ['focus_keyword' => $article['focus_keyword']]);
                }
            }

            // 6. Save to posts
            $this->updateProgress(90, 'Saving article...');
            $post = Post::create([
                'title' => $article['title'],
                'content' => $article['content'],
                'article_type' => $this->resolveArticleType($template->article_type ?? 'blog'),
                'seo_focus_keyword' => $article['focus_keyword'] ?: null,
                'seo_meta_description' => $this->truncateMeta($article['meta_description'] ?? ''),
                'ai_generated_tags' => $tags,
                'source_type' => 'ai_generated',
                'created_by' => $this->data['user_id'] ?? null,
                'insight_id' => $this->data['insight_id'] ?? null,
            ]);

            if (!empty($this->data['website_id'])) {
                PostTarget::create([
                    'post_id' => $post->id,
                    'website_id' => $this->data['website_id'],
                    'status' => 'draft',
                ]);
            }

            if (!empty($this->data['insight_id'])) {
                Insight::where('id', $this->data['insight_id'])
                    ->where('created_by', $this->data['user_id'] ?? null)
                    ->update([
                        'status' => 'in_progress',
                        'article_count' => DB::raw('article_count + 1'),
                    ]);
            }

            // 7. Finalize
            $article['provider'] = $result['provider'] ?? 'unknown';
            $article['tokens_used'] = $result['tokens_used'] ?? 0;
            $article['post_id'] = $post->id;

            $this->updateProgress(100, 'Complete!', $article);

            Log::info("[GenerateArticle] Success for job: {$jobId}");

        } catch (\Exception $e) {
            Log::error("[GenerateArticle] Error in job {$jobId}: " . $e->getMessage());

            Cache::put("generate_job:{$jobId}", [
                'status' => 'failed',
                'progress' => 0,
                'step' => 'Generation failed',
                'error' => $e->getMessage(),
            ], now()->addMinutes(10));
        }
    }

    /**
     * Prepare context based on generation source
     */
    protected function prepareContext(): array
    {
        $source = $this->data['generation_source'];
        $refs = $this->data['references'];

        if ($source === 'title') {
            return [
                'title' => $this->data['title'],
                'generation_type' => 'from_title',
            ];
        }

        if ($source === 'reference') {
            $context = [
                'title' => $this->data['title'],
                'topic' => $refs['topic'] ?? '',
                'description' => $refs['description'] ?? '',
                'generation_type' => 'from_reference',
            ];

            if (!empty($refs['url'])) {
                $context['url'] = $refs['url'];
                $context['source_domain'] = parse_url($refs['url'], PHP_URL_HOST) ?: '';
                try {
                    $firecrawl = app(FirecrawlService::class);
                    $scrape = $firecrawl->fetchUrlContent($refs['url']);
                    if (!empty($scrape['success']) && !empty($scrape['content'])) {
                        $context['url_content'] = $scrape['content'];
                    }
                } catch (\Exception $e) {
                    Log::warning('[GenerateArticle] Firecrawl failed', [
                        'url' => $refs['url'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $context;
        }

        if ($source === 'insight') {
            $insightId = $this->data['insight_id'] ?? null;
            if ($insightId) {
                $insight = Insight::where('id', $insightId)
                    ->where('created_by', $this->data['user_id'] ?? null)
                    ->first();

                if ($insight) {
                    $context = [
                        'title' => $insight->topic,
                        'topic' => $insight->topic,
                        'description' => $insight->description ?? '',
                        'generation_type' => 'from_insight',
                    ];

                    if (!empty($insight->source_url)) {
                        $context['url'] = $insight->source_url;
                        $context['source_domain'] = parse_url($insight->source_url, PHP_URL_HOST) ?: '';
                        try {
                            $firecrawl = app(FirecrawlService::class);
                            $scrape = $firecrawl->fetchUrlContent($insight->source_url);
                            if (!empty($scrape['success']) && !empty($scrape['content'])) {
                                $context['url_content'] = $scrape['content'];
                            }
                        } catch (\Exception $e) {
                            Log::warning('[GenerateArticle] Firecrawl failed', [
                                'url' => $insight->source_url,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    return $context;
                }
            }
        }

        if ($source === 'text') {
            return [
                'title' => $this->data['title'],
                'reference_text' => $refs['text'] ?? '',
                'source_name' => $refs['source_name'] ?? 'reference source',
                'generation_type' => 'from_text',
            ];
        }

        return [];
    }

    /**
     * Build prompt by replacing placeholders in template
     */
    protected function buildPrompt(AiGenerateTemplate $template, array $context): string
    {
        $prompt = $template->prompt_template;

        if (trim($prompt) === '') {
            $builder = app(\App\Services\AiGeneratePromptBuilder::class);
            return $builder->build($template, $context);
        }

        // Replace placeholders
        foreach ($context as $key => $value) {
            $prompt = str_replace("{{" . $key . "}}", $value, $prompt);
        }

        // Replace language placeholder
        $prompt = str_replace("{{language}}", $template->language, $prompt);

        return $prompt;
    }

    /**
     * Parse AI response (expects JSON or HTML)
     */
    protected function parseArticle(string $content): array
    {
        $cleaned = $this->stripCodeFences(trim($content));
        $decoded = json_decode($this->escapeNewlinesInJsonStrings($cleaned), true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['title'], $decoded['content'])) {
            // DEBUG: Log tags from AI response
            Log::info('[GenerateArticleJob] Tags from AI response', [
                'tags' => $decoded['tags'] ?? null,
                'has_tags_key' => isset($decoded['tags']),
                'title' => $decoded['title'] ?? 'N/A',
            ]);

            return [
                'title' => $decoded['title'],
                'content' => $decoded['content'],
                'meta_description' => $decoded['meta_description'] ?? '',
                'focus_keyword' => $decoded['focus_keyword'] ?? '',
                'tags' => $decoded['tags'] ?? [],
                'featured_image_prompt' => $decoded['featured_image_prompt'] ?? '',
            ];
        }

        $loose = $this->extractLooseJsonFields($cleaned);
        if ($loose !== null) {
            return $loose;
        }

        return [
            'title' => $this->data['title'],
            'content' => $cleaned,
            'meta_description' => '',
            'focus_keyword' => '',
            'tags' => [],
            'featured_image_prompt' => '',
        ];
    }

    /**
     * Update job progress in cache
     */
    protected function updateProgress(int $percentage, string $step, ?array $result = null): void
    {
        Cache::put("generate_job:{$this->data['job_id']}", [
            'status' => $percentage === 100 ? 'completed' : 'processing',
            'progress' => $percentage,
            'step' => $step,
            'result' => $result,
        ], now()->addMinutes(10));
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

    private function stripCodeFences(string $raw): string
    {
        $raw = preg_replace('/^\s*```(?:json)?\s*\n?/im', '', $raw);
        $raw = preg_replace('/\n?```\s*$/im', '', $raw);
        $raw = preg_replace('/^\s*json\s*/i', '', $raw);

        return trim($raw);
    }

    private function extractLooseJsonFields(string $raw): ?array
    {
        if (!str_contains($raw, '"title"') || !str_contains($raw, '"content"')) {
            return null;
        }

        $title = $this->data['title'];
        if (preg_match('/"title"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $match)) {
            $title = $this->decodeJsonString($match[1]);
        }

        $content = $this->extractContentValue($raw);
        if ($content === null) {
            return null;
        }

        $metaDescription = '';
        if (preg_match('/"meta_description"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $match)) {
            $metaDescription = $this->decodeJsonString($match[1]);
        }

        $focusKeyword = '';
        if (preg_match('/"focus_keyword"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $match)) {
            $focusKeyword = $this->decodeJsonString($match[1]);
        }

        $featuredImagePrompt = '';
        if (preg_match('/"featured_image_prompt"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $match)) {
            $featuredImagePrompt = $this->decodeJsonString($match[1]);
        }

        return [
            'title' => $title,
            'content' => $content,
            'meta_description' => $metaDescription,
            'focus_keyword' => $focusKeyword,
            'featured_image_prompt' => $featuredImagePrompt,
        ];
    }

    private function extractContentValue(string $raw): ?string
    {
        $contentPos = strpos($raw, '"content"');
        if ($contentPos === false) {
            return null;
        }

        $colonPos = strpos($raw, ':', $contentPos);
        if ($colonPos === false) {
            return null;
        }

        $startQuote = strpos($raw, '"', $colonPos + 1);
        if ($startQuote === false) {
            return null;
        }

        $end = null;
        $keys = ['meta_description', 'focus_keyword', 'featured_image_prompt', 'keywords', 'title'];
        foreach ($keys as $key) {
            $pos = strpos($raw, "\"{$key}\"", $startQuote + 1);
            if ($pos !== false) {
                $end = $end === null ? $pos : min($end, $pos);
            }
        }

        if ($end === null) {
            $end = strrpos($raw, '"');
        }

        if ($end === false || $end <= $startQuote) {
            $end = strlen($raw);
        }

        $length = $end - $startQuote - 1;
        if ($length < 0) {
            return null;
        }

        $value = substr($raw, $startQuote + 1, $length);
        $value = preg_replace('/"\s*,?\s*$/', '', $value);
        $value = preg_replace('/\s*}\s*$/', '', $value);

        return $this->decodeJsonString($value);
    }

    private function decodeJsonString(string $value): string
    {
        $decoded = str_replace(['\\n', '\\r', '\\t'], ["\n", "\r", "\t"], $value);
        $decoded = str_replace('\\\\', '\\', $decoded);
        $decoded = str_replace('\\"', '"', $decoded);

        return $decoded;
    }

    private function resolveArticleType(string $type): string
    {
        $type = strtolower($type);
        if (str_contains($type, 'news')) {
            return 'news';
        }

        return 'blog';
    }

    private function truncateMeta(string $meta): string
    {
        $meta = trim($meta);
        if ($meta === '') {
            return '';
        }

        return mb_substr($meta, 0, 255);
    }

    private function resolveMetaDescription(array $article): string
    {
        $meta = trim((string) ($article['meta_description'] ?? ''));
        $title = trim((string) ($article['title'] ?? ''));

        if ($meta !== '' && !$this->isMetaTooSimilar($meta, $title)) {
            return $meta;
        }

        $plain = trim(strip_tags((string) ($article['content'] ?? '')));
        if ($plain === '') {
            return '';
        }

        if ($title !== '') {
            $plainLower = mb_strtolower($plain);
            $titleLower = mb_strtolower($title);
            if (str_starts_with($plainLower, $titleLower)) {
                $plain = trim(mb_substr($plain, mb_strlen($title)));
            }
        }

        $plain = preg_replace('/\s+/', ' ', $plain);

        return mb_substr($plain, 0, 160);
    }

    private function resolveFocusKeyword(array $article): string
    {
        $userKeyword = trim((string) ($this->data['title'] ?? ''));
        if ($userKeyword !== '') {
            return $userKeyword;
        }

        $keyword = trim((string) ($article['focus_keyword'] ?? ''));
        if ($keyword !== '') {
            return $keyword;
        }

        $title = trim((string) ($article['title'] ?? ''));
        if ($title === '') {
            return '';
        }

        $words = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]+/u', '', $title));
        $words = array_filter($words);
        $words = array_slice($words, 0, 3);

        return trim(implode(' ', $words));
    }

    private function isMetaTooSimilar(string $meta, string $title): bool
    {
        if ($meta === '' || $title === '') {
            return false;
        }

        $metaLower = mb_strtolower($meta);
        $titleLower = mb_strtolower($title);

        if ($metaLower === $titleLower) {
            return true;
        }

        return str_contains($metaLower, $titleLower) || str_contains($titleLower, $metaLower);
    }
}
