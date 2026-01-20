<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\NewsSearch;
use App\Models\NewsResult;
use App\Models\NewsSource;
use App\Models\CompetitorProfile;
use App\Models\Post;
use App\Services\NewsInsightService;
use App\Services\SerperSearchService;
use App\Services\FirecrawlService;
use App\Services\AIService;
use App\Helpers\CountryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsInsightController extends Controller
{
    private $newsService;

    public function __construct()
    {
        $this->newsService = new NewsInsightService();
    }

    /**
     * Show news insight search page
     * GET /insights/news
     */
    public function index()
    {
        $userId = auth()->id();

        // Get user's search history
        $history = $this->newsService->getUserSearchHistory($userId, 20);

        // Get stats
        $stats = $this->newsService->getStats($userId);

        return view('insights.news.index', [
            'history' => $history,
            'stats' => $stats,
        ]);
    }

    /**
     * Show search form
     * GET /insights/news/create
     */
    public function create()
    {
        $userId = auth()->id();

        // Get popular sources
        $suggestedSources = SerperSearchService::getPopularSources();

        // Custom sources from DB
        $customSources = NewsSource::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn($s) => [$s->domain => $s->name])
            ->toArray();

        // Competitor domains as sources
        $competitorSources = CompetitorProfile::where('created_by', $userId)
            ->orderBy('domain')
            ->get()
            ->mapWithKeys(fn($c) => [$c->domain => $c->domain])
            ->toArray();

        // Get stats
        $stats = $this->newsService->getStats($userId);

        // Language & Country options (Serper expects 2-letter codes)
        $languages = collect(CountryHelper::getLanguages())
            ->map(function ($lang) {
                $parts = explode('_', $lang['code']);
                return [
                    'code' => strtolower($parts[0] ?? ''),
                    'name' => $lang['name'],
                    'flag' => $lang['flag'],
                ];
            })
            ->unique('code')
            ->values()
            ->all();

        $countries = collect(CountryHelper::getCountries())
            ->map(fn($c) => [
                'code' => strtolower($c['code']),
                'name' => $c['name'],
                'flag' => $c['flag'],
            ])
            ->values()
            ->all();

        return view('insights.news.create', [
            'suggestedSources' => $suggestedSources,
            'customSources' => $customSources,
            'competitorSources' => $competitorSources,
            'stats' => $stats,
            'languages' => $languages,
            'countries' => $countries,
        ]);
    }

    /**
     * Store custom news source (AJAX)
     */
    public function storeSource(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:news_sources,domain',
        ]);

        $source = NewsSource::create([
            'name' => $data['name'],
            'domain' => strtolower(trim($data['domain'])),
            'type' => 'custom',
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'source' => $source,
            'message' => 'Source added',
        ]);
    }

    /**
     * Search news and redirect to results page
     * POST /insights/news/search
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string',
            'theme' => 'nullable|string',
            'time_range' => 'required|in:today,week,month,year,all',
            'language' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:2',
            'location' => 'nullable|string|max:100',
        ]);

        try {
            $searchData = $this->newsService->performSearch(
                auth()->id(),
                $validated['source'],
                $validated['theme'] ?? null,
                $validated['time_range'],
                $validated['language'] ?? 'id',
                $validated['country'] ?? 'id',
                $validated['location'] ?? null
            );

            return redirect()->route('insights.news.results', ['id' => $searchData['search_id']])
                ->with('success', 'Pencarian berhasil! Ditemukan ' . $searchData['results_count'] . ' hasil.');

        } catch (\Exception $e) {
            Log::error('News search error: ' . $e->getMessage());
            return redirect()->route('insights.news.create')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show search results
     * GET /insights/news/results/{id}
     */
    public function results($id)
    {
        try {
            $data = $this->newsService->getSearchResults($id);

            // Check ownership
            if ($data['search']['created_by'] !== auth()->id()) {
                abort(403, 'Unauthorized');
            }

            // Load user's templates
            $templates = \App\Models\AiGenerateTemplate::where('created_by', auth()->id())->get();

            return view('insights.news.results', [
                'search' => $data['search'],
                'results' => $data['results'],
                'templates' => $templates,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('insights.news.index')
                ->with('error', 'Pencarian tidak ditemukan');
        }
    }

    /**
     * Get search history (AJAX)
     * GET /insights/news/history
     */
    public function history()
    {
        $history = $this->newsService->getUserSearchHistory(auth()->id(), 20);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Save selected news as insights
     * POST /insights/news/save-as-insights
     */
    public function saveAsInsights(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
            'items.*.snippet' => 'nullable|string',
            'items.*.url' => 'required|url',
        ]);

        try {
            DB::beginTransaction();

            $createdCount = 0;
            $userId = auth()->id();

            foreach ($validated['items'] as $item) {
                // Extract domain from URL for source name
                $parsedUrl = parse_url($item['url']);
                $sourceName = $parsedUrl['host'] ?? 'Unknown Source';

                // Create insight
                Insight::create([
                    'topic' => $item['title'],
                    'description' => $item['snippet'] ?? null, // Snippet from news result
                    'source' => $sourceName,
                    'source_url' => $item['url'],
                    'source_type' => 'news_search',
                    'status' => 'pending',
                    'article_count' => 0,
                    'created_by' => $userId,
                    'website_id' => null,
                ]);

                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$createdCount} insight",
                'redirect' => route('insights.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save as insights error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show progress page for article generation
     * GET /insights/news/generate-progress
     */
    public function generateProgress(Request $request)
    {
        $resultId = $request->input('result_id');
        $templateId = $request->input('template_id');
        $title = $request->input('title');
        $url = $request->input('url');

        // Get template and result
        $template = \App\Models\AiGenerateTemplate::findOrFail($templateId);
        $newsResult = NewsResult::findOrFail($resultId);

        return view('insights.news.progress', [
            'result_id' => $resultId,
            'template_id' => $templateId,
            'template' => $template,
            'title' => $title,
            'url' => $url,
            'keyword' => $newsResult->snippet ?? $title,
        ]);
    }

    /**
     * Generate article from news
     * POST /insights/news/generate-article
     */
    public function generateArticle(Request $request)
    {
        $validated = $request->validate([
            'result_id' => 'required|integer|exists:ane_news_results,id',
            'title' => 'required|string',
            'url' => 'required|url',
            'template_id' => 'required|integer|exists:ane_ai_generate_templates,id',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();
            $resultId = $validated['result_id'];

            // Get news result
            $newsResult = NewsResult::findOrFail($resultId);

            // Load template
            $template = \App\Models\AiGenerateTemplate::findOrFail($validated['template_id']);

            // Check template ownership
            if ($template->created_by !== $userId) {
                throw new \Exception('Unauthorized template access');
            }

            // 1. Scrape URL with Firecrawl
            $firecrawlService = new FirecrawlService();
            $scrapeResult = $firecrawlService->fetchUrlContent($validated['url']);

            if (!$scrapeResult['success']) {
                throw new \Exception('Gagal scrape URL: ' . $scrapeResult['error']);
            }

            $scrapedContent = $scrapeResult['content'];

            // 2. Create insight from news
            $parsedUrl = parse_url($validated['url']);
            $sourceName = $parsedUrl['host'] ?? 'Unknown Source';

            $insight = Insight::create([
                'topic' => $validated['title'],
                'description' => $newsResult->snippet,
                'source' => $sourceName,
                'source_url' => $validated['url'],
                'source_type' => 'news_search',
                'status' => 'in_progress',
                'article_count' => 0,
                'created_by' => $userId,
                'website_id' => null,
            ]);

            // 3. Build dynamic prompt based on template settings
            $aiService = new AIService();

            // Map article size to word count
            $wordCountMap = [
                'x-small' => 400,   // UI: 300-500 words
                'small' => 650,     // UI: 500-800 words
                'medium' => 1000,   // UI: 800-1200 words
                'large' => 1600,    // UI: 1200-2000 words
            ];
            $targetWordCount = $wordCountMap[$template->article_size] ?? 1000;

            // Map language to language name
            $languageMap = [
                'id' => 'bahasa Indonesia',
                'en_US' => 'English',
                'en_GB' => 'British English',
            ];
            $languageName = $languageMap[$template->language] ?? 'bahasa Indonesia';
            $targetCountry = $template->target_country ? strtoupper($template->target_country) : null;

            // Map tone to writing style instructions
            $toneInstructions = [
                'professional' => 'Gunakan bahasa formal dan profesional. Hindari slang atau bahasa kasual.',
                'casual' => 'Gunakan bahasa santai dan mudah dipahami. Boleh gunakan istilah sehari-hari.',
                'friendly' => 'Gunakan bahasa ramah dan hangat seperti berbicara dengan teman.',
                'authoritative' => 'Gunakan bahasa yang kuat dan meyakinkan. Tunjukkan keahlian dan kredibilitas.',
                'conversational' => 'Gunakan gaya percakapan yang natural seperti berbicara langsung dengan pembaca.',
            ];
            $toneInstruction = $toneInstructions[$template->tone] ?? 'Gunakan bahasa formal dan profesional.';

            // Map point of view to perspective instructions
            $povInstructions = [
                'first_person' => 'Tulis dari sudut pandang orang pertama (saya/kami).',
                'second_person' => 'Tulis dari sudut pandang orang kedua, berbicara langsung kepada pembaca (kamu/Anda).',
                'third_person' => 'Tulis dari sudut pandang orang ketiga yang objektif.',
            ];
            $povInstruction = $povInstructions[$template->point_of_view] ?? 'Tulis dari sudut pandang orang ketiga yang objektif.';

            // Readability instructions
            $readabilityInstructions = [
                'easy' => 'Pastikan keterbacaan mudah (setara kelas 8), kalimat singkat, paragraf pendek.',
                'medium' => 'Keterbacaan menengah, tetap jelas, hindari jargon berlebihan.',
                'hard' => 'Gaya lebih mendalam dan formal, boleh gunakan istilah teknis jika relevan.',
            ];
            $readabilityInstruction = $readabilityInstructions[$template->text_readability] ?? null;

            // Content cleaning preference
            $cleaningInstruction = $template->ai_content_cleaning
                ? 'Bersihkan hasil dari kalimat berulang atau filler, pastikan struktur rapi.'
                : null;

            // Article type hint
            $articleTypeHint = $template->article_type ? "Jenis artikel: {$template->article_type}." : null;

            // Get source name for news attribution
            $parsedUrlForSource = parse_url($validated['url']);
            $sourceName = $parsedUrlForSource['host'] ?? 'sumber';
            $sourceName = str_replace('www.', '', $sourceName); // Remove www.

            // Build the AI prompt
            $prompt = "Kamu adalah penulis artikel BERITA profesional SEO yang HARUS menulis artikel berkualitas tinggi.\n\n";
            $prompt .= "REFERENSI BERITA:\n";
            $prompt .= "Sumber: {$sourceName}\n";
            $prompt .= "URL: {$validated['url']}\n";
            $prompt .= "Judul Asli: {$validated['title']}\n\n";
            $prompt .= "Konten:\n{$scrapedContent}\n\n";
            $prompt .= "---\n\n";
            $prompt .= "INSTRUKSI TEMPLATE:\n";
            $prompt .= "- Bahasa: {$languageName}\n";
            $prompt .= "- Target kata: MINIMAL {$targetWordCount} kata (lebih banyak lebih baik)\n";
            $prompt .= "- Tone: {$template->tone} - {$toneInstruction}\n";
            $prompt .= "- Sudut pandang: {$povInstruction}\n\n";
            if ($targetCountry) {
                $prompt .= "- Target negara/pembaca: {$targetCountry}\n";
            }
            if ($articleTypeHint) {
                $prompt .= "- {$articleTypeHint}\n";
            }
            if ($readabilityInstruction) {
                $prompt .= "- Keterbacaan: {$readabilityInstruction}\n";
            }
            if ($cleaningInstruction) {
                $prompt .= "- Kebersihan konten: {$cleaningInstruction}\n";
            }
            $prompt .= "TARGET KAMU: Buat artikel BERITA ORIGINAL {$languageName} dengan judul BARU yang berbeda dari aslinya.\n\n";
            $prompt .= "ATURAN WAJIB:\n";
            $prompt .= "1. BUAT JUDUL BARU yang BERBEDA TOTAL dari judul asli, tapi tetap SEO-friendly dan menarik. Judul harus:\n";
            $prompt .= "   - Mengandung keyword utama dari topik\n";
            $prompt .= "   - Lebih menarik dan clickable\n";
            $prompt .= "   - Maksimal 60 karakter untuk SEO\n";
            $prompt .= "   - WAJIB berbeda dari judul asli\n\n";
            $prompt .= "2. MINIMAL {$targetWordCount} kata - artikel harus PANJANG dan DETAIL\n\n";
            $prompt .= "3. Tulis ORIGINAL - parafrase dan rangkum dengan bahasa sendiri\n\n";
            $prompt .= "4. JANGAN copy-paste dari referensi\n\n";
            $prompt .= "5. Pertahankan FAKTA tapi sampaikan dengan gaya berbeda\n\n";
            $prompt .= "6. Struktur artikel BERITA (piramida terbalik):\n";
            $prompt .= "   - <h2>Pendahuluan</h2> (2-3 paragraf) - WAJIB dimulai dengan 'Dilansir dari {$sourceName}, ...' atau 'Dikutip dari {$sourceName}, ...'\n";
            $prompt .= "   - <h2>Pembahasan</h2> (4-5 <h3> subjudul dengan masing-masing 2-3 paragraf)\n";
            $prompt .= "   - <h2>Kesimpulan</h2> (2 paragraf)\n";
            $prompt .= "   - Akhiri dengan: <p><em>Sumber: {$sourceName}</em></p>\n\n";
            $prompt .= "7. Setiap paragraf minimal 3-4 kalimat\n\n";
            $prompt .= "8. Gunakan analisis dan insight untuk memperpanjang konten\n\n";
            $prompt .= "9. Output dalam format JSON:\n";
            $prompt .= "   {\n";
            $prompt .= "     \"title\": \"Judul artikel baru yang BERBEDA dari asli\",\n";
            $prompt .= "     \"content\": \"<h2>Pendahuluan</h2><p>Dilansir dari {$sourceName}, ...</p>...\",\n";
            $prompt .= "     \"focus_keyword\": \"keyword utama SEO (1-3 kata)\",\n";
            $prompt .= "     \"meta_description\": \"Deskripsi meta SEO (120-160 karakter)\",\n";
            $prompt .= "     \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],\n";
            $prompt .= "     \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"]\n";
            $prompt .= "   }\n\n";
            $prompt .= "10. Content harus HTML MURNI yang bersih:\n";
            $prompt .= "    - Tag yang BOLEH: <h2>, <h3>, <p>, <strong>, <em>, <ul>, <ol>, <li>, <a>\n";
            $prompt .= "    - JANGAN pakai markdown syntax: **bold**, _italic_, [link](url), ## heading, * list\n";
            $prompt .= "    - JANGAN pakai backticks atau code blocks\n";
            $prompt .= "    - Bold = <strong>teks</strong>, BUKAN **teks**\n";
            $prompt .= "    - Italic = <em>teks</em>, BUKAN _teks_ atau *teks*\n";
            $prompt .= "    - Link = <a href=\"url\">teks</a>, BUKAN [teks](url)\n\n";
            $prompt .= "11. JANGAN pakai ```html, ```json, atau markdown code blocks\n\n";
            $prompt .= "12. {$toneInstruction}\n\n";
            $prompt .= "13. {$povInstruction}\n\n";
            $prompt .= "ATURAN KHUSUS BERITA:\n";
            $prompt .= "14. Paragraf PERTAMA harus dimulai dengan attribution: 'Dilansir dari {$sourceName}, [ringkasan berita]...'\n";
            $prompt .= "15. Gunakan struktur piramida terbalik (informasi penting di awal)\n";
            $prompt .= "16. Paragraf pertama harus menjawab 5W+1H (What, Who, When, Where, Why, How)\n";
            $prompt .= "17. Akhiri artikel dengan credit: <p><em>Sumber: {$sourceName}</em></p>\n\n";
            $prompt .= "ATURAN TAGS:\n";
            $prompt .= "18. Generate 3-5 tags untuk artikel ini\n";
            $prompt .= "19. Focus keyword WAJIB menjadi salah satu tag (tag pertama)\n";
            $prompt .= "20. Tag lainnya harus berdasarkan:\n";
            $prompt .= "    - Tema utama artikel\n";
            $prompt .= "    - Tokoh/personalitas yang disebutkan (jika ada)\n";
            $prompt .= "    - Topik terkait yang relevan\n";
            $prompt .= "21. Tags harus singkat (1-2 kata per tag)\n";
            $prompt .= "22. Semua tags dalam bahasa yang sama dengan artikel ({$languageName})\n\n";
            $prompt .= "PENTING: \n";
            $prompt .= "- Judul WAJIB berbeda dari '{$validated['title']}'\n";
            $prompt .= "- Paragraf pertama WAJIB dimulai dengan 'Dilansir dari {$sourceName},' atau 'Dikutip dari {$sourceName},'\n";
            $prompt .= "- JANGAN gunakan markdown syntax apapun, hanya HTML murni\n";
            $prompt .= "- Jika artikel kurang dari {$targetWordCount} kata, tambahkan lebih banyak detail, contoh, dan analisis!\n";
            $prompt .= "- Output WAJIB JSON valid dengan key 'title' dan 'content'\n\n";
            $prompt .= "MULAI MENULIS (OUTPUT JSON):\n";

            // Calculate max_tokens based on target word count (approximately 1.3x for safety)
            $maxTokens = (int) ($targetWordCount * 1.3 * 1.5); // 1.5 multiplier for Indonesian to English ratio
            $maxTokens = max(8000, min($maxTokens, 16000)); // Between 8000-16000 tokens

            $aiResult = $aiService->generate($prompt, 'article_generation', [
                'max_tokens' => $maxTokens,
                'provider' => $template->ai_provider ?: null,
                'model' => $template->ai_model ?: null,
            ]);

            if (!isset($aiResult['content'])) {
                throw new \Exception('AI gagal generate artikel');
            }

            // Parse JSON response from AI
            $aiOutput = $aiResult['content'];

            // Clean markdown code blocks if any
            $aiOutput = preg_replace('/```json\s*/i', '', $aiOutput);
            $aiOutput = preg_replace('/```\s*$/i', '', $aiOutput);
            $aiOutput = trim($aiOutput);

            // Parse JSON
            $parsedResult = json_decode($aiOutput, true);

            if (!$parsedResult || !isset($parsedResult['title']) || !isset($parsedResult['content'])) {
                // Fallback: if JSON parsing fails, treat entire output as content
                Log::warning('AI did not return valid JSON, using fallback', ['output' => substr($aiOutput, 0, 200)]);
                $generatedTitle = $validated['title']; // Use original title as fallback
                $content = $aiOutput;
                $focusKeyword = null;
                $metaDescription = substr($newsResult->snippet ?? '', 0, 160);
                $keywords = [];
                $tags = [];
            } else {
                $generatedTitle = $parsedResult['title'];
                $content = $parsedResult['content'];
                $focusKeyword = $parsedResult['focus_keyword'] ?? null;
                $metaDescription = $parsedResult['meta_description'] ?? substr($newsResult->snippet ?? '', 0, 160);
                $keywords = $parsedResult['keywords'] ?? [];
                $tags = $parsedResult['tags'] ?? [];

                // Validate tags: focus keyword must be first tag
                if (!empty($tags) && $focusKeyword) {
                    // Ensure focus keyword is in tags (case-insensitive check)
                    $focusKeywordLower = strtolower($focusKeyword);
                    $tagsLower = array_map('strtolower', $tags);

                    if (!in_array($focusKeywordLower, $tagsLower)) {
                        // Add focus keyword as first tag if missing
                        array_unshift($tags, $focusKeyword);
                        Log::info('NewsInsight: Focus keyword added to tags', ['focus_keyword' => $focusKeyword]);
                    }
                }
            }

            // Aggressive markdown cleaning
            // Remove markdown bold: **text** or __text__ -> <strong>text</strong>
            $content = preg_replace('/\*\*([^\*]+)\*\*/', '<strong>$1</strong>', $content);
            $content = preg_replace('/__([^_]+)__/', '<strong>$1</strong>', $content);

            // Remove markdown italic: _text_ or *text* -> <em>text</em>
            $content = preg_replace('/\b_([^_]+)_\b/', '<em>$1</em>', $content);
            $content = preg_replace('/\b\*([^\*]+)\*\b/', '<em>$1</em>', $content);

            // Remove markdown links: [text](url) -> <a href="url">text</a>
            $content = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $content);

            // Remove markdown headings: ## text -> already in HTML, but clean any leftover
            $content = preg_replace('/^#{1,6}\s+(.+)$/m', '$1', $content);

            // Remove markdown lists: * item or - item -> <li>item</li>
            $content = preg_replace('/^\*\s+(.+)$/m', '<li>$1</li>', $content);
            $content = preg_replace('/^-\s+(.+)$/m', '<li>$1</li>', $content);

            // Remove code blocks/backticks: `code` or ```code```
            $content = preg_replace('/`([^`]+)`/', '$1', $content);
            $content = preg_replace('/```[a-z]*\n?/', '', $content);

            // Remove horizontal rules: --- or ***
            $content = preg_replace('/^(-{3,}|\*{3,})$/m', '', $content);

            // Clean up multiple empty lines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            // Trim
            $content = trim($content);

            // 4. Save to posts
            $post = Post::create([
                'insight_id' => $insight->id,
                'title' => $generatedTitle,
                'content' => $content,
                'article_type' => 'news',
                'seo_focus_keyword' => $focusKeyword,
                'seo_meta_description' => $metaDescription,
                'ai_generated_tags' => $tags ?? [],
                'featured_media_id' => null,
                'source_type' => 'ai_generated',
                'created_by' => $userId,
            ]);

            // Update insight with generated title and article count
            $insight->update([
                'topic' => $generatedTitle, // Use AI-generated title
                'article_count' => 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'post_id' => $post->id,
                'title' => $generatedTitle,
                'content' => $content,
                'message' => 'Artikel berhasil digenerate',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate article error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete search record
     * DELETE /insights/news/{id}
     */
    public function destroy($id)
    {
        try {
            $search = NewsSearch::findOrFail($id);

            // Check ownership
            if ($search->created_by !== auth()->id()) {
                abort(403, 'Unauthorized');
            }

            $search->delete();

            return redirect()->route('insights.news.index')
                ->with('success', 'Riwayat pencarian berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->route('insights.news.index')
                ->with('error', 'Gagal menghapus riwayat pencarian');
        }
    }
}
