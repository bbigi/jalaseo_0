<?php

namespace App\Jobs;

use App\Models\ContentStrategy;
use App\Models\Post;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateContentFromTrending implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes max

    protected $strategyId;
    protected $templateId;

    /**
     * Create a new job instance.
     */
    public function __construct($strategyId, $templateId = null)
    {
        $this->strategyId = $strategyId;
        $this->templateId = $templateId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $strategy = ContentStrategy::findOrFail($this->strategyId);

        try {
            // Update status to generating
            $strategy->update(['status' => 'generating']);

            // Step 1: AI Ranking
            $this->rankTopics($strategy);

            // Step 2: Dispatch child jobs for parallel processing
            $this->dispatchArticleJobs($strategy);

            Log::info('Article generation jobs dispatched', ['strategy_id' => $strategy->id]);

        } catch (\Exception $e) {
            $strategy->update(['status' => 'failed']);
            Log::error('Content generation failed', [
                'strategy_id' => $strategy->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Step 1: Rank trending topics using AI
     */
    protected function rankTopics(ContentStrategy $strategy): void
    {
        $results = $strategy->selectedResults();

        $rankedTopics = $results->map(function ($result) {
            // Calculate engagement score (40%)
            $engagementScore = (
                ($result->engagement_likes * 1) +
                ($result->engagement_comments * 5) +
                ($result->engagement_shares * 10)
            );

            // Normalize engagement score to 0-100
            $maxEngagement = 200000; // Estimated max
            $engagementNormalized = min(100, ($engagementScore / $maxEngagement) * 100);

            // Evergreen score (30%) - Simple heuristic for now
            // TODO: Use AI semantic analysis
            $evergreenScore = $this->calculateEvergreenScore($result->content);

            // SEO score (20%) - Based on content length and hashtags
            // TODO: Use Google Trends API
            $seoScore = $this->calculateSEOScore($result);

            // Monetization score (10%) - Category-based
            $monetizationScore = $this->calculateMonetizationScore($result->content);

            // Weighted total score
            $totalScore = (
                ($engagementNormalized * 0.4) +
                ($evergreenScore * 0.3) +
                ($seoScore * 0.2) +
                ($monetizationScore * 0.1)
            );

            return [
                'id' => $result->id,
                'score' => round($totalScore, 2),
                'engagement_score' => round($engagementNormalized, 2),
                'evergreen_score' => round($evergreenScore, 2),
                'seo_score' => round($seoScore, 2),
                'monetization_score' => round($monetizationScore, 2),
                'tier' => $totalScore >= 80 ? 1 : ($totalScore >= 60 ? 2 : 3),
                'reasoning' => $this->generateReasoning($result, $totalScore),
            ];
        })->sortByDesc('score')->values()->toArray();

        // Save ranked topics
        $strategy->update(['ranked_topics' => $rankedTopics]);

        Log::info('Topics ranked', [
            'strategy_id' => $strategy->id,
            'top_score' => $rankedTopics[0]['score'] ?? 0
        ]);
    }

    /**
     * Calculate evergreen score (simple heuristic)
     */
    protected function calculateEvergreenScore(string $content): float
    {
        $evergreenKeywords = [
            'cara', 'tips', 'tutorial', 'panduan', 'how to', 'guide', 'step',
            'belajar', 'mudah', 'simple', 'lengkap', 'pemula', 'dasar'
        ];

        $tempKeywords = [
            'viral', 'trending', 'terbaru', 'latest', 'news', 'breaking',
            'drama', 'gossip', 'hari ini', 'minggu ini', '2024', '2025'
        ];

        $contentLower = strtolower($content);
        $evergreenCount = 0;
        $tempCount = 0;

        foreach ($evergreenKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $evergreenCount++;
            }
        }

        foreach ($tempKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $tempCount++;
            }
        }

        // Score: +10 per evergreen keyword, -15 per temp keyword
        $score = ($evergreenCount * 10) - ($tempCount * 15);
        return max(0, min(100, 50 + $score)); // Baseline 50, adjust +/-
    }

    /**
     * Calculate SEO score
     */
    protected function calculateSEOScore($result): float
    {
        $score = 50; // Baseline

        // Content length (longer = better for SEO)
        $contentLength = strlen($result->content);
        if ($contentLength > 100) $score += 20;
        elseif ($contentLength > 50) $score += 10;

        // Has hashtags
        if (!empty($result->hashtags) && count($result->hashtags) > 3) {
            $score += 20;
        }

        // Has author (credibility)
        if (!empty($result->author)) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Calculate monetization score
     */
    protected function calculateMonetizationScore(string $content): float
    {
        $highMonetizationCategories = [
            'travel', 'jalan', 'liburan', 'wisata', 'hotel', 'tiket',
            'keuangan', 'investasi', 'saham', 'trading', 'bisnis',
            'teknologi', 'gadget', 'smartphone', 'laptop', 'review',
            'kesehatan', 'fitness', 'diet', 'olahraga', 'suplemen'
        ];

        $contentLower = strtolower($content);
        $matchCount = 0;

        foreach ($highMonetizationCategories as $category) {
            if (str_contains($contentLower, $category)) {
                $matchCount++;
            }
        }

        return min(100, $matchCount * 25); // 0, 25, 50, 75, 100
    }

    /**
     * Generate reasoning text
     */
    protected function generateReasoning($result, float $score): string
    {
        $tier = $score >= 80 ? 'High' : ($score >= 60 ? 'Medium' : 'Low');
        $engagement = number_format($result->engagement_likes + $result->engagement_comments + $result->engagement_shares);

        return "{$tier} potential. Total engagement: {$engagement}. Score: " . round($score, 1) . "/100";
    }

    /**
     * Step 2: Dispatch child jobs for parallel processing
     */
    protected function dispatchArticleJobs(ContentStrategy $strategy): void
    {
        $rankedResults = collect($strategy->ranked_topics);

        // Load template from templateId if provided, otherwise use default
        if ($this->templateId) {
            $template = \App\Models\AiGenerateTemplate::find($this->templateId);
            if (!$template) {
                Log::warning('Template not found, using default', ['template_id' => $this->templateId]);
                $template = $this->getDefaultTemplate($strategy->created_by);
            }
        } else {
            $template = $this->getDefaultTemplate($strategy->created_by);
        }

        $expectedArticles = 0;

        switch ($strategy->strategy_type) {
            case 'roundup':
                // 1 roundup article
                GenerateSingleArticle::dispatch($strategy->id, 'roundup', null, $template->id ?? null);
                $expectedArticles = 1;
                break;

            case 'hybrid':
                // 1 roundup + 3 deep dive articles
                GenerateSingleArticle::dispatch($strategy->id, 'roundup', null, $template->id ?? null);

                foreach ($rankedResults->take(3) as $rank) {
                    GenerateSingleArticle::dispatch($strategy->id, 'deep_dive', $rank['id'], $template->id ?? null);
                }

                $expectedArticles = 4;
                break;

            case 'deep_dive_all':
                // N deep dive articles (one per selected topic)
                foreach ($rankedResults as $rank) {
                    GenerateSingleArticle::dispatch($strategy->id, 'deep_dive', $rank['id'], $template->id ?? null);
                }

                $expectedArticles = $rankedResults->count();
                break;
        }

        // Update expected articles count for progress tracking
        $strategy->update(['expected_articles' => $expectedArticles]);

        Log::info('Dispatched article generation jobs', [
            'strategy_id' => $strategy->id,
            'expected_articles' => $expectedArticles
        ]);
    }

    /**
     * Get default AI template or create fallback
     */
    protected function getDefaultTemplate($userId)
    {
        // Try to get user's first template
        $template = \App\Models\AiGenerateTemplate::where('created_by', $userId)->first();

        // Fallback: use system default template (created_by = 1, admin user)
        if (!$template) {
            $template = \App\Models\AiGenerateTemplate::where('created_by', 1)->first();
        }

        // If still no template, create temporary object with defaults
        if (!$template) {
            $template = (object) [
                'ai_provider' => 'gemini',
                'ai_model' => null,
                'language' => 'id_ID',
                'article_type' => 'Blog Article',
                'tone' => 'casual',
                'point_of_view' => 'third_person',
            ];
        }

        return $template;
    }

    /**
     * Generate roundup article
     */
    protected function generateRoundupArticle(ContentStrategy $strategy, $rankedResults, AIService $aiService, $template): void
    {
        $selectedResults = $strategy->selectedResults();
        $platform = $strategy->search->platform;
        $currentMonth = now()->format('F Y');
        $language = $template->language === 'id_ID' ? 'Indonesian' : 'English';

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

        // Map template settings to AI instructions
        $languageName = $language === 'Indonesian' ? 'bahasa Indonesia' : 'English';
        $targetWordCount = $this->getWordCountFromSize($template->article_size ?? 'medium');
        $targetCountry = $template->target_country ?? 'Indonesia';
        $articleTypeInstruction = $this->getArticleTypeInstruction($template->article_type ?? 'Blog Article', 'roundup');

        // Gabungkan Tone + POV + Readability jadi satu "Writing Style" yang kohesif
        $writingStyle = $this->getWritingStyle(
            $template->tone ?? 'Professional',
            $template->point_of_view ?? 'third_person',
            $template->text_readability ?? 'general'
        );

        $prompt = <<<PROMPT
Kamu adalah penulis artikel profesional SEO yang HARUS menulis artikel roundup berkualitas tinggi.

DATA TRENDING {$platform} {$targetCountry}:
{$topicsSummary}

INSTRUKSI TEMPLATE:
- Bahasa: {$languageName}
- Target kata: MINIMAL {$targetWordCount} kata (lebih banyak lebih baik)
- Article Type: {$articleTypeInstruction}
- Target Country: {$targetCountry}
- Bulan: {$currentMonth}

GAYA PENULISAN (PENTING - IKUTI DENGAN KETAT):
{$writingStyle}

TARGET KAMU: Buat artikel roundup yang merangkum semua trending topics di atas.

ATURAN WAJIB:
1. BUAT JUDUL yang menarik dan SEO-friendly:
   - Harus mengandung bulan "{$currentMonth}"
   - Contoh: "4 Tren {$platform} Indonesia Paling Viral di {$currentMonth}: Jangan Sampai Ketinggalan!"
   - Maksimal 60 karakter untuk SEO

2. MINIMAL {$targetWordCount} kata - artikel harus PANJANG dan DETAIL

3. Struktur artikel:
   - <h2>Pendahuluan</h2> (2-3 paragraf) - jelaskan kenapa tren ini penting dan viral
   - Untuk setiap trending topic:
     * <h2>Nomor. Judul Trend (Kreatif & Menarik)</h2>
     * 2-3 paragraf menjelaskan konten trend (minimal 80-100 kata)
     * Sertakan data engagement: <p><strong>Engagement:</strong> X likes, Y comments, Z shares</p>
     * Tampilkan hashtags: <p><strong>Hashtags:</strong> #tag1, #tag2, #tag3</p>
     * Kenapa penting: 1-2 kalimat
   - <h2>Kesimpulan</h2> (2 paragraf) - rangkuman dan CTA untuk follow trend

4. Setiap paragraf minimal 3-4 kalimat

5. Output dalam format JSON:
   {
     "title": "Judul artikel yang catchy dengan bulan {$currentMonth}",
     "content": "<h2>Pendahuluan</h2><p>...</p>...",
     "focus_keyword": "trending {$platform} indonesia",
     "meta_description": "Deskripsi meta SEO (120-160 karakter)",
     "keywords": ["trending {$platform}", "viral indonesia", "{$currentMonth}", "tiktok viral", "trending topics"]
   }

6. Content harus HTML MURNI yang bersih:
   - Tag yang BOLEH: <h2>, <h3>, <p>, <strong>, <em>, <ul>, <ol>, <li>
   - JANGAN pakai markdown syntax: **bold**, _italic_, [link](url), ## heading, * list
   - JANGAN pakai backticks atau code blocks
   - Bold = <strong>teks</strong>, BUKAN **teks**
   - Italic = <em>teks</em>, BUKAN _teks_ atau *teks*
   - List = <ul><li>item</li></ul>, BUKAN * item

7. JANGAN pakai ```json, ```html, atau markdown code blocks apapun

8. Output WAJIB JSON valid dengan key 'title', 'content', 'focus_keyword', 'meta_description', 'keywords'

9. JANGAN awali response dengan text apapun, LANGSUNG mulai dengan kurung kurawal {

PENTING:
- Jika artikel kurang dari {$targetWordCount} kata, tambahkan lebih banyak detail, contoh, dan analisis!
- Return ONLY the JSON object, no explanation, no markdown blocks

Mulai sekarang!
PROMPT;

        try {
            $response = $aiService->generate($prompt, 'article_generation', [
                'provider' => $template->ai_provider ?? 'gemini',
                'model' => $template->ai_model ?? null,
                'max_tokens' => 2000,
            ]);

            // Parse JSON response
            $article = $this->parseAIResponse($response['content']);

            // Apply content cleaning if enabled
            $content = $article['content'] ?? $response['content'];
            if ($template->ai_content_cleaning ?? false) {
                $content = $this->cleanAIContent($content);
            }

            // Create post
            Post::create([
                'content_strategy_id' => $strategy->id,
                'article_role' => 'roundup',
                'title' => $article['title'] ?? "Trending {$platform} Indonesia {$currentMonth}",
                'content' => $content,
                'article_type' => 'blog',
                'seo_focus_keyword' => $article['focus_keyword'] ?? $article['keywords'][0] ?? "trending {$platform} indonesia",
                'seo_meta_description' => $article['meta_description'] ?? '',
                'source_type' => 'ai_generated',
                'created_by' => $strategy->created_by,
            ]);

            Log::info('Roundup article generated', ['strategy_id' => $strategy->id]);

        } catch (\Exception $e) {
            Log::error('Roundup generation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate deep dive articles
     */
    protected function generateDeepDiveArticles(ContentStrategy $strategy, $rankedResults, AIService $aiService, $template): void
    {
        $selectedResults = $strategy->selectedResults()->keyBy('id');
        $language = $template->language === 'id_ID' ? 'Indonesian' : 'English';

        // Map template settings to AI instructions
        $languageName = $language === 'Indonesian' ? 'bahasa Indonesia' : 'English';
        $targetWordCount = $this->getWordCountFromSize($template->article_size ?? 'large'); // Deep dive default to large
        $targetCountry = $template->target_country ?? 'Indonesia';
        $articleTypeInstruction = $this->getArticleTypeInstruction($template->article_type ?? 'Guide', 'deep_dive');

        // Gabungkan Tone + POV + Readability jadi satu "Writing Style" yang kohesif
        $writingStyle = $this->getWritingStyle(
            $template->tone ?? 'Professional',
            $template->point_of_view ?? 'third_person',
            $template->text_readability ?? 'general'
        );

        foreach ($rankedResults as $rank) {
            $result = $selectedResults->get($rank['id']);
            if (!$result) continue;

            $prompt = <<<PROMPT
Kamu adalah penulis artikel deep-dive profesional SEO yang HARUS menulis artikel panduan lengkap berkualitas tinggi.

DATA TRENDING TOPIC:
- Konten: {$result->content}
- Author: {$result->author}
- Engagement: {$result->engagement_likes} likes, {$result->engagement_comments} comments, {$result->engagement_shares} shares
- Hashtags: {$this->formatHashtags($result->hashtags ?? [])}
- AI Score: {$rank['score']}/100 ({$rank['reasoning']})

INSTRUKSI TEMPLATE:
- Bahasa: {$languageName}
- Target kata: MINIMAL {$targetWordCount} kata (lebih banyak lebih baik)
- Article Type: {$articleTypeInstruction}
- Target Country: {$targetCountry}

GAYA PENULISAN (PENTING - IKUTI DENGAN KETAT):
{$writingStyle}

TARGET KAMU: Buat artikel deep-dive yang mendalam dan actionable tentang trending topic di atas.

ATURAN WAJIB:
1. BUAT JUDUL yang SEO-optimized:
   - Harus mengandung tahun "2025"
   - Contoh: "Panduan Lengkap [Topic] 2025: Tips dan Trik Terbukti"
   - Maksimal 60 karakter untuk SEO

2. MINIMAL {$targetWordCount} kata - artikel harus SANGAT PANJANG dan DETAIL

3. Struktur artikel:
   - <h2>Pendahuluan</h2> (2-3 paragraf) - pain point, janji, kenapa trending
   - <h2>Latar Belakang</h2> (2-3 paragraf) - kenapa topic ini penting sekarang
   - <h2>Subjudul 1 (topik utama)</h2> (3-4 paragraf dengan contoh dan tips)
   - <h2>Subjudul 2</h2> (3-4 paragraf)
   - <h2>Subjudul 3</h2> (3-4 paragraf)
   - <h2>Subjudul 4</h2> (3-4 paragraf)
   - <h2>Subjudul 5</h2> (3-4 paragraf)
   - <h2>FAQ (Pertanyaan Umum)</h2> (5-7 Q&A menggunakan <h3> untuk setiap pertanyaan)
   - <h2>Kesimpulan</h2> (2 paragraf) - ringkasan + CTA
   - Tambahkan link internal: <p><em>Untuk lebih banyak trending topics, cek roundup mingguan kami</em></p>

4. Setiap paragraf minimal 4-5 kalimat

5. Output dalam format JSON:
   {
     "title": "Judul SEO-optimized dengan tahun 2025",
     "content": "<h2>Pendahuluan</h2><p>...</p>...",
     "focus_keyword": "keyword utama SEO (1-3 kata)",
     "meta_description": "Deskripsi meta SEO (120-160 karakter)",
     "keywords": ["keyword1", "keyword2", "keyword3", "keyword4", "keyword5"]
   }

6. Content harus HTML MURNI yang bersih:
   - Tag yang BOLEH: <h2>, <h3>, <p>, <strong>, <em>, <ul>, <ol>, <li>
   - JANGAN pakai markdown syntax: **bold**, _italic_, [link](url), ## heading, * list
   - JANGAN pakai backticks atau code blocks
   - Bold = <strong>teks</strong>, BUKAN **teks**
   - Italic = <em>teks</em>, BUKAN _teks_ atau *teks*
   - List = <ul><li>item</li></ul>, BUKAN * item

7. JANGAN pakai ```json, ```html, atau markdown code blocks apapun

8. Output WAJIB JSON valid dengan key 'title', 'content', 'focus_keyword', 'meta_description', 'keywords'

9. JANGAN awali response dengan text apapun, LANGSUNG mulai dengan kurung kurawal {

10. Artikel harus ACTIONABLE dan VALUABLE, bukan sekedar merangkum trend. Berikan tips, trik, panduan praktis!

PENTING:
- Jika artikel kurang dari {$targetWordCount} kata, tambahkan lebih banyak detail, contoh, dan analisis!
- Return ONLY the JSON object, no explanation, no markdown blocks

Mulai sekarang!
PROMPT;

            try {
                $response = $aiService->generate($prompt, 'article_generation', [
                    'provider' => $template->ai_provider ?? 'gemini',
                    'model' => $template->ai_model ?? null,
                    'max_tokens' => 3000,
                ]);

                // Parse JSON response
                $article = $this->parseAIResponse($response['content']);

                // Apply content cleaning if enabled
                $content = $article['content'] ?? $response['content'];
                if ($template->ai_content_cleaning ?? false) {
                    $content = $this->cleanAIContent($content);
                }

                // Create post
                Post::create([
                    'content_strategy_id' => $strategy->id,
                    'article_role' => 'deep_dive',
                    'source_topic_id' => $result->id,
                    'title' => $article['title'] ?? $result->content,
                    'content' => $content,
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

            } catch (\Exception $e) {
                Log::error('Deep dive generation failed', [
                    'topic_id' => $result->id,
                    'error' => $e->getMessage()
                ]);
                // Continue with next article instead of failing entire job
                continue;
            }
        }
    }

    /**
     * Extract title from markdown content
     */
    protected function extractTitle(string $content): string
    {
        // Try to find H1 heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback: first line or default
        $lines = explode("\n", trim($content));
        return $lines[0] ?? 'Untitled Article';
    }

    /**
     * Extract focus keyword from content
     */
    protected function extractKeyword(string $content): string
    {
        // Simple extraction: take first 3-5 words
        $words = explode(' ', trim(strip_tags($content)));
        return implode(' ', array_slice($words, 0, min(5, count($words))));
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
     * Map article size to word count (based on UI ranges)
     * - Small: 300-600 words → target 500
     * - Medium: 600-1200 words → target 900
     * - Large: 1200-2000 words → target 1600
     */
    protected function getWordCountFromSize(string $size): int
    {
        return match($size) {
            'small' => 500,      // 300-600 range
            'medium' => 900,     // 600-1200 range
            'large' => 1600,     // 1200-2000 range
            default => 900,      // Default to medium
        };
    }

    /**
     * Gabungkan Tone + POV + Readability jadi satu instruksi kohesif dengan contoh konkret
     */
    protected function getWritingStyle(string $tone, string $pov, string $readability): string
    {
        $toneLower = strtolower($tone);
        $povLower = strtolower($pov);

        // 1. Dapatkan base tone instruction
        $toneGuide = match(true) {
            str_contains($toneLower, 'casual') || str_contains($toneLower, 'santai') =>
                'bahasa sehari-hari, boleh pakai slang ringan (guys, nih, banget), hindari kata formal (sebagaimana, berdasarkan)',

            str_contains($toneLower, 'professional') || str_contains($toneLower, 'formal') =>
                'bahasa baku dan formal, gunakan istilah bisnis/akademis, hindari slang dan emoji',

            str_contains($toneLower, 'friendly') || str_contains($toneLower, 'ramah') =>
                'ramah seperti ngobrol dengan teman, boleh pakai pertanyaan retoris, tetap sopan',

            str_contains($toneLower, 'enthusiastic') || str_contains($toneLower, 'semangat') =>
                'ekspresif dan energik, gunakan kata-kata positif (luar biasa, keren banget, wajib coba), banyak tanda seru',

            str_contains($toneLower, 'humorous') || str_contains($toneLower, 'lucu') =>
                'sisipkan humor ringan, wordplay, analogi lucu (kayak lagi diet tapi ngemil terus)',

            default => "tone {$tone}",
        };

        // 2. Dapatkan POV instruction dengan contoh
        $povGuide = match(true) {
            str_contains($povLower, 'first') || $pov === 'first_person' =>
                'SELALU gunakan kata ganti ORANG PERTAMA: "saya", "kami", "kita". Contoh: "Jadi saya pikir trend ini keren banget!", "Kami menemukan bahwa...", "Menurut saya..."',

            str_contains($povLower, 'second') || $pov === 'second_person' =>
                'SELALU gunakan kata ganti ORANG KEDUA: "kamu", "Anda", "kalian". Contoh: "Kamu pasti penasaran kan?", "Anda akan menemukan...", "Yuk kita bahas!"',

            str_contains($povLower, 'third') || $pov === 'third_person' =>
                'SELALU gunakan kata ganti ORANG KETIGA (netral/objektif): "pengguna", "pembaca", "mereka". Contoh: "Pengguna dapat mencoba...", "Trending topic ini menunjukkan..."',

            default => 'gunakan perspektif netral',
        };

        // 3. Dapatkan readability guide (normalize variations)
        $readabilityLower = strtolower($readability ?? 'general');
        $readabilityGuide = match(true) {
            str_contains($readabilityLower, 'simple') || str_contains($readabilityLower, 'low') =>
                'Kalimat PENDEK (max 15 kata/kalimat). Hindari jargon teknis. Target: pemula.',
            str_contains($readabilityLower, 'general') || str_contains($readabilityLower, 'medium') =>
                'Kalimat jelas (15-20 kata/kalimat). Boleh istilah umum.',
            str_contains($readabilityLower, 'advanced') || str_contains($readabilityLower, 'high') =>
                'Boleh kalimat kompleks. Target: profesional/expert.',
            default => 'Kalimat mudah dipahami.',
        };

        // 4. Gabungkan semua jadi satu instruksi yang kuat
        return <<<STYLE
Tone: Gunakan {$toneGuide}
Point of View: {$povGuide}
Readability: {$readabilityGuide}

CONTOH PENERAPAN (wajib ikuti):
- Jika tone=casual + POV=first_person: "Jadi saya pikir ini keren banget! Saya udah coba dan hasilnya mantap."
- Jika tone=professional + POV=third_person: "Penelitian menunjukkan bahwa trending topic ini mengalami peningkatan signifikan."
- Jika tone=friendly + POV=second_person: "Kamu pasti penasaran kan kenapa ini viral? Yuk kita bahas bareng!"

PENTING: Gunakan POV yang sama di SELURUH artikel (jangan campur "saya" dengan "kamu" atau "mereka").
STYLE;
    }

    /**
     * Get tone instruction for AI with concrete examples
     */
    protected function getToneInstruction(string $tone): string
    {
        $toneLower = strtolower($tone);

        return match(true) {
            str_contains($toneLower, 'casual') || str_contains($toneLower, 'santai') =>
                'Tone: Casual/Santai. Gunakan bahasa sehari-hari, boleh pakai slang ringan, emoji sesekali. Contoh: "Guys, ini keren banget!", "Wajib coba nih!". Hindari kata formal seperti "sebagaimana", "berdasarkan", "menunjukkan".',

            str_contains($toneLower, 'professional') || str_contains($toneLower, 'formal') =>
                'Tone: Professional/Formal. Gunakan bahasa baku, hindari slang, hindari emoji. Contoh: "Data menunjukkan bahwa...", "Berdasarkan analisis...". Gunakan istilah bisnis/akademis yang tepat.',

            str_contains($toneLower, 'friendly') || str_contains($toneLower, 'ramah') =>
                'Tone: Friendly/Ramah. Seperti ngobrol dengan teman, tapi tetap sopan. Contoh: "Pasti kamu penasaran kan?", "Yuk kita bahas!". Boleh pakai pertanyaan retoris.',

            str_contains($toneLower, 'enthusiastic') || str_contains($toneLower, 'semangat') =>
                'Tone: Enthusiastic/Semangat. Ekspresif, energik, pakai kata-kata positif. Contoh: "Luar biasa!", "Ini breakthrough banget!", "Wajib dicoba sekarang!". Gunakan tanda seru lebih sering.',

            str_contains($toneLower, 'humorous') || str_contains($toneLower, 'lucu') =>
                'Tone: Humorous/Lucu. Sisipkan humor ringan, wordplay, analogi lucu. Contoh: "Kayak lagi diet tapi ngemil terus", "Plot twist yang nggak terduga!". Jangan terlalu serius.',

            default =>
                "Tone: {$tone}. Sesuaikan gaya bahasa dengan tone ini secara konsisten di seluruh artikel.",
        };
    }

    /**
     * Get readability instruction for AI
     */
    protected function getReadabilityInstruction(string $readability): string
    {
        return match($readability) {
            'simple' => 'Gunakan kalimat pendek dan sederhana (max 15 kata per kalimat). Hindari jargon teknis. Target pembaca: umum/pemula.',
            'general' => 'Gunakan kalimat yang jelas dan mudah dipahami (15-20 kata per kalimat). Boleh gunakan istilah umum.',
            'advanced' => 'Boleh gunakan kalimat kompleks dan istilah teknis. Target pembaca: profesional/expert.',
            default => 'Gunakan kalimat yang jelas dan mudah dipahami.',
        };
    }

    /**
     * Get point of view instruction for AI
     */
    protected function getPointOfViewInstruction(string $pov): string
    {
        return match($pov) {
            'first_person' => 'Tulis dengan sudut pandang orang pertama (saya/kami). Contoh: "Saya akan membahas...", "Kami menemukan..."',
            'second_person' => 'Tulis dengan sudut pandang orang kedua (kamu/Anda). Contoh: "Anda akan menemukan...", "Kamu bisa mencoba..."',
            'third_person' => 'Tulis dengan sudut pandang orang ketiga (netral/objektif). Contoh: "Pengguna dapat...", "Trending topic ini menunjukkan..."',
            default => 'Tulis dengan sudut pandang orang ketiga (netral/objektif).',
        };
    }

    /**
     * Get article type instruction for AI (normalize variations from database)
     */
    protected function getArticleTypeInstruction(string $articleType, string $context): string
    {
        $typeLower = strtolower($articleType);

        $baseInstruction = match(true) {
            str_contains($typeLower, 'news') =>
                'ARTICLE TYPE: News/Berita (Profesional & Faktual)
Gaya: Objektif, informatif, to-the-point. Hindari opini pribadi.

PENTING - SITASI SUMBER WAJIB:
- Setiap fakta/data WAJIB sebut sumbernya dengan jelas
- Gunakan frasa: "Menurut [sumber]...", "Dilansir dari [sumber]...", "Berdasarkan laporan [sumber]..."
- Contoh BENAR: "Menurut laporan Detik.com, jumlah pengguna meningkat 50%"
- Contoh SALAH: "Data menunjukkan peningkatan 50%" (sumber mana? tidak kredibel!)
- Setiap paragraf harus minimal 1 sitasi sumber
- Jangan buat klaim tanpa atribusi yang jelas',

            str_contains($typeLower, 'blog') || str_contains($typeLower, 'opini') || str_contains($typeLower, 'opinion') =>
                'ARTICLE TYPE: Blog/Opini (Personal & Engaging)
Gaya: Casual, personal, boleh ada opini dan storytelling.

PENTING - OPINI + REFERENSI:
- Boleh tambahkan pendapat pribadi, TAPI tetap harus referensi fakta dari sumber
- Pisahkan FAKTA vs OPINI dengan jelas dalam penulisan
- Contoh FAKTA: "Menurut Kompas.com, trending ini mencapai 1 juta views dalam 24 jam"
- Contoh OPINI: "Menurut saya, ini menunjukkan pergeseran minat pengguna ke konten visual..."
- Jangan membuat klaim faktual tanpa dasar/referensi
- Tetap profesional dan kredibel meski gaya casual',

            str_contains($typeLower, 'guide') || str_contains($typeLower, 'tutorial') =>
                'Struktur: step-by-step, actionable tips, practical examples. Harus ada tutorial/panduan jelas.',

            str_contains($typeLower, 'listicle') || str_contains($typeLower, 'list') =>
                'Format: numbered list dengan subheading untuk setiap poin. Setiap poin harus lengkap dan berdiri sendiri.',

            str_contains($typeLower, 'review') =>
                'Struktur: overview, pros/cons, rating, recommendation. Harus objektif dan detail.',

            default => 'Gaya penulisan: informatif dan engaging.',
        };

        // Add context-specific adjustment
        if ($context === 'roundup') {
            return $baseInstruction . ' Format roundup: rangkum semua trending topics dengan equal weight untuk setiap item.';
        } elseif ($context === 'deep_dive') {
            return $baseInstruction . ' Format deep-dive: analisis mendalam satu topic dengan multiple sections dan FAQ.';
        }

        return $baseInstruction;
    }

    /**
     * Clean AI-generated content (remove hallucinations, repetitions, etc)
     */
    protected function cleanAIContent(string $content): string
    {
        // 1. Remove repetitive sentences (same sentence appearing 2+ times)
        $sentences = preg_split('/(?<=[.!?])\s+/', $content);
        $uniqueSentences = [];
        $seen = [];

        foreach ($sentences as $sentence) {
            $normalized = strtolower(trim($sentence));
            if (!isset($seen[$normalized]) && !empty($normalized)) {
                $uniqueSentences[] = $sentence;
                $seen[$normalized] = true;
            }
        }

        $content = implode(' ', $uniqueSentences);

        // 2. Remove common AI hallucination phrases
        $hallucinations = [
            '/sebagai AI language model/i',
            '/saya tidak bisa memberikan/i',
            '/I cannot provide/i',
            '/I am an AI/i',
            '/sebagai asisten AI/i',
            '/\[Insert .+?\]/i',
            '/\[Your .+?\]/i',
            '/\[Link to .+?\]/i',
        ];

        foreach ($hallucinations as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // 3. Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/(<\/p>)\s+(<p>)/', '$1$2', $content); // Remove space between paragraphs

        return trim($content);
    }

    /**
     * Parse AI response (JSON format)
     */
    protected function parseAIResponse($response)
    {
        // Log raw response for debugging
        Log::info('Raw AI response', ['response' => substr($response, 0, 500)]);

        // Try to extract JSON from response
        $cleaned = trim($response);

        // Remove markdown code blocks
        $cleaned = preg_replace('/^```json\s*/i', '', $cleaned);
        $cleaned = preg_replace('/^```\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/i', '', $cleaned);

        // Try to find JSON object in response (even if wrapped in text)
        if (preg_match('/\{[\s\S]*"title"[\s\S]*\}/i', $cleaned, $matches)) {
            $cleaned = $matches[0];
        }

        $data = json_decode($cleaned, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            Log::info('JSON parsed successfully', ['title' => $data['title'] ?? 'N/A']);
            return $data;
        }

        // Log parsing error
        Log::error('JSON parse failed', [
            'error' => json_last_error_msg(),
            'cleaned' => substr($cleaned, 0, 300)
        ]);

        // Fallback if not JSON
        return [
            'title' => 'Generated Article',
            'content' => $response,
            'meta_description' => substr(strip_tags($response), 0, 160),
            'keywords' => [],
        ];
    }
}
