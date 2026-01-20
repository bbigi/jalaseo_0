<?php

namespace App\Http\Controllers;

use App\Models\ApiCredential;
use App\Models\GeneralSetting;
use App\Models\Media;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImageGenerationController extends Controller
{
    /**
     * Show image generation form
     */
    public function create(): View
    {
        // Get available image AI providers
        $providers = ApiCredential::where('category', 'image_generation')
            ->where('is_active', true)
            ->get(['provider', 'display_name'])
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider),
                ];
            });

        return view('image-generation.create', compact('providers'));
    }

    /**
     * Show modal version (no dashboard layout)
     */
    public function createModal(): View
    {
        // Get available image AI providers
        $providers = ApiCredential::where('category', 'image_generation')
            ->where('is_active', true)
            ->get(['provider', 'display_name'])
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider),
                ];
            });

        return view('image-generation.create-modal', compact('providers'));
    }

    /**
     * Store generation request in session and redirect to progress
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:3000',
            'language' => 'nullable|string|max:10',
            'provider' => 'nullable|string',
            'image_style' => 'required|string',
            'image_size' => 'required|string',
            'context_title' => 'nullable|string|max:255',
        ]);

        // Check quota
        $user = auth()->user();
        if ($user->api_quota_used >= $user->api_quota_limit) {
            return back()->with('error', 'API quota exceeded. Please upgrade your plan.');
        }

        // Store in session
        session(['image_generation_data' => $validated]);

        return redirect()->route('image-generation.progress');
    }

    /**
     * Show progress page
     */
    public function progress(): View
    {
        $data = session('image_generation_data');

        if (!$data) {
            return redirect()->route('image-generation.create')
                ->with('error', 'No generation data found. Please try again.');
        }

        return view('image-generation.progress', [
            'prompt' => $data['prompt'],
            'provider' => $data['provider'] ?? 'auto',
            'image_style' => $data['image_style'],
            'image_size' => $data['image_size'],
        ]);
    }

    /**
     * Generate image via AJAX
     */
    public function generate(Request $request)
    {
        try {
            $data = session('image_generation_data');

            if (!$data) {
                return response()->json(['success' => false, 'error' => 'No generation data found'], 400);
            }

            // Parse image size
            [$width, $height] = $this->parseImageSize($data['image_size']);

            // Build enhanced prompt with style
            $enhancedPrompt = $this->buildPrompt($data['prompt'], $data['image_style'], $data['image_size']);

            // Call AI Service
            $aiService = new AIService();
            $result = $aiService->generateImage($enhancedPrompt, [
                'provider' => $data['provider'] ?? null,
                'width' => $width,
                'height' => $height,
            ]);

            // Download image from URL or decode base64 (for Gemini)
            $imageUrl = $result['image_url'];

            if (str_starts_with($imageUrl, 'data:image/')) {
                // Base64 data URL (Gemini Imagen)
                $imageContent = base64_decode($result['image_data'] ?? '');
            } else {
                // HTTP URL (Flux, X.ai)
                $imageContent = file_get_contents($imageUrl);
            }

            if (!$imageContent) {
                throw new \Exception('Failed to download generated image');
            }

            // Generate filename
            $filename = 'generated-' . time() . '-' . uniqid() . '.png';
            $path = 'media/' . $filename;

            // Store in storage
            Storage::disk('public')->put($path, $imageContent);

            // Save to media table
            $media = Media::create([
                'user_id' => auth()->id(),
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => 'image',
                'mime_type' => 'image/png',
                'file_size' => strlen($imageContent),
                'ai_prompt' => $data['prompt'],
                'source_type' => 'ai_generated',
            ]);

            // Generate metadata with AI (async, don't block response)
            try {
                $language = $data['language'] ?? 'en_US';
                Log::info('Image metadata generation', ['language' => $language, 'data' => $data]);
                $metadata = $this->generateImageMetadata($data['prompt'], $language);
                $media->update([
                    'title' => substr($metadata['title'], 0, 255),
                    'alt_text' => substr($metadata['alt_text'], 0, 255),
                    'caption' => substr($metadata['caption'] ?? $metadata['title'], 0, 255),
                    'description' => $metadata['description'],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to generate image metadata: ' . $e->getMessage());
                // Fallback to basic metadata
                $media->update([
                    'title' => substr($data['prompt'], 0, 100),
                    'alt_text' => substr($data['prompt'], 0, 150),
                ]);
            }

            // Increment quota
            auth()->user()->increment('api_quota_used');

            // Clear session
            session()->forget('image_generation_data');

            return response()->json([
                'success' => true,
                'media_id' => $media->id,
                'image_url' => asset('storage/' . $path),
                'provider' => $result['provider'],
                'title' => $media->title,
                'alt_text' => $media->alt_text,
                'description' => $media->description,
            ]);

        } catch (\Exception $e) {
            Log::error('Image generation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse image size string to width and height
     */
    private function parseImageSize(string $size): array
    {
        // Format: "960x768 (5:4)" -> [960, 768]
        preg_match('/(\d+)x(\d+)/', $size, $matches);

        return [
            (int) ($matches[1] ?? 1024),
            (int) ($matches[2] ?? 1024),
        ];
    }

    /**
     * Build enhanced prompt with style
     */
    private function buildPrompt(string $basePrompt, string $style, string $size = ''): string
    {
        $stylePrompts = [
            'Photo' => 'photorealistic, high quality photo, professional photography',
            'Cartoon' => 'cartoon style, colorful, animated, fun illustration',
            'Cubism' => 'cubism art style, geometric shapes, abstract, Pablo Picasso inspired',
            'Expressionism' => 'expressionism art, emotional, bold colors, dramatic',
            'Cyberpunk' => 'cyberpunk aesthetic, neon lights, futuristic, sci-fi, dark urban',
            'Fantasy' => 'fantasy art, magical, mythical, epic, ethereal',
            'Cinematic' => 'cinematic lighting, movie quality, dramatic composition, film-like',
            'Abstract' => 'abstract art, non-representational, modern, artistic',
            'Impressionism' => 'impressionism painting, soft brush strokes, Monet style, artistic',
            'Surrealism' => 'surrealism art, dreamlike, Salvador Dali inspired, imaginative',
            'Anime' => 'anime style, manga art, Japanese animation, vibrant colors',
            'Comic' => 'comic book style, bold lines, pop art, graphic novel illustration',
        ];

        $styleModifier = $stylePrompts[$style] ?? '';

        $sizeDescriptor = '';
        if (!empty($size)) {
            // Add aspect ratio hint to prompt
            $sizeDescriptor = "aspect ratio {$size}";
        }

        return trim("{$basePrompt}, {$styleModifier} {$sizeDescriptor}");
    }

    /**
     * Generate image prompt from article content using AI
     */
    public function generatePrompt(Request $request)
    {
        try {
            $content = $request->input('content', '');

            // Check if content is empty
            if (empty(trim($content))) {
                $userInput = $request->input('user_input', '');

                if (empty(trim($userInput))) {
                    return response()->json([
                        'success' => false,
                        'needs_input' => true,
                        'message' => 'Please describe what image you want to create'
                    ]);
                }

                // Use user input as content
                $content = $userInput;
            }

            // Use AI to generate prompt
            $aiService = new AIService();

            $systemPrompt = "You are an AI that generates image prompts for illustration. Based on the article content provided, create a detailed, descriptive prompt that would make a good illustration image. The prompt should be visual, descriptive, and suitable for AI image generation. Keep it concise (100-150 words max). CRITICAL: Return ONLY the image prompt itself, without any introduction, explanation, or meta-text like 'Here is...' or 'This prompt...'. Start directly with the visual description.";

            $userPrompt = "Article content:\n\n{$content}\n\nGenerate ONLY the image prompt (no introduction or explanation). Start directly with the visual description. Keep it 100-150 words.";

            $result = $aiService->generate(
                $userPrompt,
                'article_generation',
                ['system_prompt' => $systemPrompt, 'max_tokens' => 800]
            );

            // Clean up the response - remove common intro phrases
            $generatedPrompt = trim($result['content']);
            $cleanupPatterns = [
                '/^Here\'s a detailed image prompt[^:]*:\s*/i',
                '/^Here is a detailed image prompt[^:]*:\s*/i',
                '/^This prompt[^:]*:\s*/i',
                '/^Image prompt[^:]*:\s*/i',
                '/^The prompt[^:]*:\s*/i',
                '/^Prompt[^:]*:\s*/i',
            ];

            foreach ($cleanupPatterns as $pattern) {
                $generatedPrompt = preg_replace($pattern, '', $generatedPrompt);
            }

            return response()->json([
                'success' => true,
                'prompt' => trim($generatedPrompt),
                'provider' => $result['provider'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Prompt generation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate image metadata (title, alt_text, description) using AI
     */
    private function generateImageMetadata(string $prompt, string $language = 'en_US'): array
    {
        // Map language codes to names
        $languageMap = [
            'id_ID' => 'Indonesian',
            'en_US' => 'English',
            'en_GB' => 'English',
            'ar_SA' => 'Arabic',
            'ms_MY' => 'Malay',
            'zh_CN' => 'Chinese',
            'ja_JP' => 'Japanese',
            'ko_KR' => 'Korean',
            'es_ES' => 'Spanish',
            'fr_FR' => 'French',
            'de_DE' => 'German',
            'pt_BR' => 'Portuguese',
            'ru_RU' => 'Russian',
            'tr_TR' => 'Turkish',
            'hi_IN' => 'Hindi',
        ];

        $languageName = $languageMap[$language] ?? 'English';

        // Build metadata generation prompt
        $metadataPrompt = "Create SEO-optimized metadata in {$languageName} for an AI-generated image.

Image prompt: \"{$prompt}\"

CRITICAL RULES:
1. ALL text MUST be in {$languageName} language
2. Make it SEO-friendly with relevant keywords
3. Title: 5-10 words, descriptive and keyword-rich
4. Alt text: 10-15 words, accessible and descriptive
5. Caption: 1-2 sentences, engaging
6. Description: 2-3 sentences, detailed and SEO-optimized

Return ONLY valid JSON (no markdown, no code blocks, no extra text):
{
  \"title\": \"short descriptive title\",
  \"alt_text\": \"SEO-friendly alt text for accessibility\",
  \"caption\": \"engaging caption for readers\",
  \"description\": \"detailed SEO-optimized description with keywords\"
}";

        // Call AI Service (use article_generation category instead of image_metadata)
        $aiService = new AIService();
        $result = $aiService->generate($metadataPrompt, 'article_generation', [
            'provider' => 'gemini',
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);

        // Parse JSON response
        $content = trim($result['content']);

        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        $metadata = json_decode($content, true);

        if (!$metadata || !isset($metadata['title'], $metadata['alt_text'], $metadata['description'])) {
            throw new \Exception('Invalid metadata response from AI');
        }

        // Add caption if not exists (fallback)
        if (!isset($metadata['caption'])) {
            $metadata['caption'] = $metadata['title'];
        }

        return $metadata;
    }

    /**
     * Detect language from text (simple detection)
     */
    private function detectLanguage(string $text): string
    {
        if (empty($text)) {
            return 'en'; // Default to English
        }

        // Common Indonesian words
        $indonesianWords = ['dan', 'yang', 'untuk', 'dengan', 'adalah', 'atau', 'pada', 'dari', 'ini', 'itu', 'akan', 'dapat'];

        $textLower = strtolower($text);
        $indonesianCount = 0;

        foreach ($indonesianWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $textLower)) {
                $indonesianCount++;
            }
        }

        // If 2 or more Indonesian words found, assume Indonesian
        return $indonesianCount >= 2 ? 'id' : 'en';
    }
}
