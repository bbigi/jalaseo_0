<?php

namespace App\Jobs;

use App\Models\SocialSearch;
use App\Models\SocialResult;
use App\Models\Post;
use App\Models\Insight;
use App\Models\AiGenerateTemplate;
use App\Services\FirecrawlService;
use App\Services\AIService;
use App\Services\AiGeneratePromptBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateBatchNewsArticles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    protected $searchId;
    protected $newsIds;
    protected $templateId;
    protected $userId;
    protected $strategyId;

    public function __construct(int $searchId, array $newsIds, int $templateId, int $userId, int $strategyId)
    {
        $this->searchId = $searchId;
        $this->newsIds = $newsIds;
        $this->templateId = $templateId;
        $this->userId = $userId;
        $this->strategyId = $strategyId;
    }

    public function handle()
    {
        try {
            $search = SocialSearch::findOrFail($this->searchId);
            $template = AiGenerateTemplate::findOrFail($this->templateId);
            $strategy = \App\Models\ContentStrategy::findOrFail($this->strategyId);

            // Mark strategy as generating
            $strategy->update(['status' => 'generating']);

            // Get news articles
            $newsArticles = SocialResult::whereIn('id', $this->newsIds)
                ->where('search_id', $this->searchId)
                ->get();

            if ($newsArticles->isEmpty()) {
                throw new \Exception('No news articles found');
            }

            // Initialize services
            $firecrawlService = new FirecrawlService();
            $aiService = app(AIService::class);
            $promptBuilder = app(AiGeneratePromptBuilder::class);

            // Process each news article
            foreach ($newsArticles as $news) {
                try {
                    DB::beginTransaction();

                    // 1. Scrape URL with Firecrawl
                    $scrapeResult = $firecrawlService->fetchUrlContent($news->link);

                    if (!$scrapeResult['success']) {
                        Log::warning('Failed to scrape URL: ' . $news->link);
                        DB::rollBack();
                        continue; // Skip this article
                    }

                    $scrapedContent = $scrapeResult['content'];

                    // 2. Create insight from news
                    $parsedUrl = parse_url($news->link);
                    $sourceName = $parsedUrl['host'] ?? 'Unknown Source';

                    $insight = Insight::create([
                        'topic' => $news->content,
                        'description' => '',
                        'source' => $sourceName,
                        'source_url' => $news->link,
                        'source_type' => 'google_trends',
                        'status' => 'in_progress',
                        'article_count' => 0,
                        'created_by' => $this->userId,
                        'website_id' => null,
                    ]);

                    // 3. Build AI prompt (align with /generate standards)
                    $primaryKeyword = trim((string) ($search->keyword ?? ''));
                    if ($primaryKeyword === '') {
                        $primaryKeyword = (string) $news->content;
                    }

                    $prompt = $promptBuilder->build($template, [
                        'generation_type' => 'from_reference',
                        // Use the search keyword as primary focus keyword (matches /generate standard)
                        'title' => $primaryKeyword,
                        'topic' => (string) $news->content,
                        'description' => '',
                        'url' => (string) $news->link,
                        'url_content' => (string) $scrapedContent,
                        'source_domain' => (string) $sourceName,
                    ]);

                    // 4. Generate article with AI
                    $aiResponse = $aiService->generate(
                        prompt: $prompt,
                        category: 'article_generation',
                        options: [
                            'provider' => $template->ai_provider ?: null,
                            'model' => $template->ai_model ?: null,
                            'max_tokens' => $promptBuilder->getMaxTokens($template->article_size ?? 'medium'),
                            'temperature' => 0.7,
                        ]
                    );

                    $generatedContent = $aiResponse['content'];

                    // 5. Parse AI response
                    $articleData = $this->parseAIResponse($generatedContent);

                    // 6. Create post
                    $post = Post::create([
                        'insight_id' => $insight->id,
                        'title' => $articleData['title'] ?? 'Generated Article',
                        'content' => $articleData['content'] ?? '',
                        'article_type' => 'blog',
                        'seo_focus_keyword' => $articleData['focus_keyword']
                            ?? ($articleData['keywords'][0] ?? null),
                        'seo_meta_description' => $articleData['meta_description'] ?? null,
                        'source_type' => 'google_trends',
                        'content_strategy_id' => $this->strategyId,
                        'created_by' => $this->userId,
                    ]);

                    // 7. Update insight status (only 'pending' or 'in_progress' allowed)
                    $insight->update([
                        'status' => 'in_progress',
                        'article_count' => 1,
                    ]);

                    DB::commit();

                    Log::info('Generated article from Google Trends news', [
                        'post_id' => $post->id,
                        'news_id' => $news->id,
                        'title' => $post->title,
                        'strategy_id' => $this->strategyId,
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to generate article from news', [
                        'news_id' => $news->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with next article
                    continue;
                }
            }

            // Mark strategy as completed
            $strategy->update(['status' => 'completed']);

            Log::info('Batch news generation completed', [
                'strategy_id' => $this->strategyId,
                'total_articles' => count($this->newsIds),
            ]);

        } catch (\Exception $e) {
            Log::error('GenerateBatchNewsArticles job failed', [
                'search_id' => $this->searchId,
                'strategy_id' => $this->strategyId,
                'error' => $e->getMessage(),
            ]);

            // Mark strategy as failed
            if (isset($strategy)) {
                $strategy->update(['status' => 'failed']);
            }

            throw $e;
        }
    }

    protected function buildPrompt(string $title, string $scrapedContent, AiGenerateTemplate $template): string
    {
        // Deprecated: kept for backward compatibility, but not used anymore.
        $wordCountMap = [
            'small' => 500,
            'medium' => 1000,
            'large' => 1500,
            'extra_large' => 2000,
        ];
        $targetWordCount = $wordCountMap[$template->article_size] ?? 1000;

        $languageMap = [
            'id' => 'bahasa Indonesia',
            'en_US' => 'English',
            'en_GB' => 'British English',
        ];
        $languageName = $languageMap[$template->language] ?? 'bahasa Indonesia';

        $toneInstructions = [
            'professional' => 'Gunakan bahasa formal dan profesional. Hindari slang atau bahasa kasual.',
            'casual' => 'Gunakan bahasa santai dan mudah dipahami. Boleh gunakan istilah sehari-hari.',
            'friendly' => 'Gunakan bahasa ramah dan hangat seperti berbicara dengan teman.',
            'authoritative' => 'Gunakan bahasa yang kuat dan meyakinkan. Tunjukkan keahlian dan kredibilitas.',
            'conversational' => 'Gunakan gaya percakapan yang natural seperti berbicara langsung dengan pembaca.',
        ];
        $toneInstruction = $toneInstructions[$template->tone] ?? $toneInstructions['professional'];

        $povMap = [
            'first_person' => 'orang pertama (saya/kami)',
            'second_person' => 'orang kedua (Anda/kamu)',
            'third_person' => 'orang ketiga (dia/mereka)',
        ];
        $pov = $povMap[$template->pov] ?? 'orang pertama';

        return <<<PROMPT
Tulis artikel blog yang menarik dan SEO-friendly berdasarkan berita berikut:

**Judul Berita:** {$title}

**Konten Berita yang Sudah Diambil:**
{$scrapedContent}

**Instruksi Penulisan:**
1. Buat judul artikel yang menarik dan berbeda dari judul berita asli
2. Tulis artikel dengan panjang sekitar {$targetWordCount} kata
3. Gunakan bahasa: {$languageName}
4. Gaya penulisan: {$toneInstruction}
5. Sudut pandang: {$pov}
6. Format artikel dalam HTML (gunakan tag <p>, <h2>, <h3>, <ul>, <li>, <strong>, dll)
7. Sertakan 3-5 fokus keyword yang relevan
8. Buat meta description yang menarik (maksimal 160 karakter)

**Output harus dalam format JSON:**
{
  "title": "judul artikel yang menarik",
  "content": "<p>konten artikel dalam HTML...</p>",
  "meta_description": "deskripsi singkat untuk SEO",
  "keywords": ["keyword1", "keyword2", "keyword3"]
}

**PENTING:** Tulis artikel dengan gaya dan struktur kalimat yang BERBEDA dari berita asli. Jangan copy-paste langsung. Tambahkan perspektif, analisis, dan informasi tambahan yang relevan.
PROMPT;
    }

    protected function parseAIResponse(string $response): array
    {
        $cleaned = $this->stripCodeFences(trim($response));

        // Try JSON decode directly
        $data = json_decode($this->escapeNewlinesInJsonStrings($cleaned), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $this->normalizeParsedData($data, $response);
        }

        // Try to extract JSON object substring
        $start = strpos($cleaned, '{');
        $end = strrpos($cleaned, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($cleaned, $start, $end - $start + 1);
            $data = json_decode($this->escapeNewlinesInJsonStrings($json), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $this->normalizeParsedData($data, $response);
            }
        }

        return [
            'title' => 'Generated Article',
            'content' => $response,
            'meta_description' => mb_substr(trim(strip_tags($response)), 0, 160),
            'focus_keyword' => '',
            'keywords' => [],
        ];
    }

    protected function getMaxTokens(string $size): int
    {
        return match($size) {
            'small' => 2000,
            'medium' => 3000,
            'large' => 5000,
            'extra_large' => 8000,
            default => 3000,
        };
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
