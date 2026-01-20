<?php

namespace App\Services;

use App\Models\ApiCredential;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Generate content using AI provider with auto-fallback
     *
     * @param string $prompt
     * @param string $category article_generation|image_generation
     * @param array $options Additional options (max_tokens, temperature, etc)
     * @return array ['content' => string, 'tokens_used' => int, 'provider' => string]
     */
    public function generate(string $prompt, string $category = 'article_generation', array $options = []): array
    {
        // Get fallback chain from settings
        $fallbackChain = $this->getFallbackChain($category, $options['provider'] ?? null);

        $lastError = null;

        // Try each provider in order
        foreach ($fallbackChain as $provider) {
            try {
                $credential = $this->getCredentialByProvider($category, $provider);

                if (!$credential) {
                    Log::warning("No credential found for provider: {$provider}");
                    continue;
                }

                Log::info("Attempting generation with provider: {$provider}");

                // Prepare provider-specific options (reset model when falling back to a different provider)
                $providerOptions = $options;
                $providerOptions['provider'] = $provider;
                $previousProvider = $options['provider'] ?? null;
                if ($previousProvider !== null && $previousProvider !== $provider) {
                    unset($providerOptions['model']);
                    Log::info("Provider changed from {$previousProvider} to {$provider}, model unset");
                }

                Log::info("Calling {$provider} with model: " . ($providerOptions['model'] ?? 'default'));

                // Route to appropriate provider
                $result = match($provider) {
                    'gemini' => $this->generateWithGemini($prompt, $credential, $providerOptions),
                    'chatgpt' => $this->generateWithChatGPT($prompt, $credential, $providerOptions),
                    'claude' => $this->generateWithClaude($prompt, $credential, $providerOptions),
                    'deepseek' => $this->generateWithDeepSeek($prompt, $credential, $providerOptions),
                    'x-ai' => $this->generateWithXai($prompt, $credential, $providerOptions),
                    default => throw new \Exception("Unsupported provider: {$provider}")
                };

                Log::info("Successfully generated content with provider: {$provider}");
                return $result;

            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Provider {$provider} failed: " . $e->getMessage());
                // Continue to next provider in fallback chain
                continue;
            }
        }

        // All providers failed
        throw new \Exception(
            "All AI providers failed for {$category}. Last error: " .
            ($lastError ? $lastError->getMessage() : 'Unknown error')
        );
    }

    /**
     * Get fallback chain based on category and user preference
     */
    protected function getFallbackChain(string $category, ?string $preferredProvider = null): array
    {
        // Get default chain from settings
        $defaultChain = $category === 'article_generation'
            ? GeneralSetting::getDefaultTextAI()
            : GeneralSetting::getDefaultImageAI();

        // If user specified a provider, prioritize it
        if ($preferredProvider && !in_array($preferredProvider, $defaultChain)) {
            array_unshift($defaultChain, $preferredProvider);
        } elseif ($preferredProvider) {
            // Move preferred provider to front
            $defaultChain = array_diff($defaultChain, [$preferredProvider]);
            array_unshift($defaultChain, $preferredProvider);
        }

        return $defaultChain;
    }

    /**
     * Get credential by specific provider
     */
    protected function getCredentialByProvider(string $category, string $provider): ?ApiCredential
    {
        return ApiCredential::where('category', $category)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();
    }


    /**
     * Generate content with Google Gemini
     */
    protected function generateWithGemini(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key; // Auto-decrypted via accessor
        $model = $options['model'] ?? 'gemini-2.5-flash-lite';
        $maxTokens = $options['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? 0.7;

        $response = Http::timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature' => $temperature,
                ]
            ]);

        if (!$response->successful()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Gemini API request failed: " . $response->body());
        }

        $data = $response->json();
        
        // Extract content
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Extract token usage
        $tokensUsed = ($data['usageMetadata']['promptTokenCount'] ?? 0) + 
                      ($data['usageMetadata']['candidatesTokenCount'] ?? 0);

        return [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => 'gemini',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Generate content with OpenAI ChatGPT
     */
    protected function generateWithChatGPT(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'gpt-4o-mini';
        $maxTokens = $options['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? 0.7;

        $response = Http::timeout(60)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("OpenAI API request failed: " . $response->body());
        }

        $data = $response->json();
        
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;

        return [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => 'chatgpt',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Generate content with Anthropic Claude
     */
    protected function generateWithClaude(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'claude-3-5-sonnet-20241022';
        $maxTokens = $options['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? 0.7;

        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            Log::error('Claude API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Claude API request failed: " . $response->body());
        }

        $data = $response->json();
        
        $content = $data['content'][0]['text'] ?? '';
        $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

        return [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => 'claude',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Generate content with DeepSeek
     */
    protected function generateWithDeepSeek(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'deepseek-chat';
        $maxTokens = $options['max_tokens'] ?? 2000;

        // DeepSeek has a hard limit of 8192 output tokens, cap at 7500 for safety
        if ($maxTokens > 7500) {
            $maxTokens = 7500;
            Log::info('DeepSeek max_tokens capped at 7500 (API limit: 8192)');
        }

        $temperature = $options['temperature'] ?? 0.7;

        $response = Http::timeout(60)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            Log::error('DeepSeek API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("DeepSeek API request failed: " . $response->body());
        }

        $data = $response->json();
        
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;

        return [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => 'deepseek',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Generate content with X.ai Grok
     */
    protected function generateWithXai(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'grok-4-1-fast-reasoning';
        $maxTokens = $options['max_tokens'] ?? 2000;
        $temperature = $options['temperature'] ?? 0.7;

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            Log::error('X.ai Grok API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("X.ai API request failed: " . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;

        return [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => 'x-ai',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Generate image using AI provider with auto-fallback
     *
     * @param string $prompt
     * @param array $options Additional options (width, height, provider, etc)
     * @return array ['image_url' => string, 'provider' => string]
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        // Get fallback chain from settings
        $fallbackChain = $this->getFallbackChain('image_generation', $options['provider'] ?? null);

        $lastError = null;

        // Try each provider in order
        foreach ($fallbackChain as $provider) {
            try {
                $credential = $this->getCredentialByProvider('image_generation', $provider);

                if (!$credential) {
                    Log::warning("No credential found for image provider: {$provider}");
                    continue;
                }

                Log::info("Attempting image generation with provider: {$provider}");

                // Route to appropriate provider
                $result = match($provider) {
                    'flux' => $this->generateImageWithFlux($prompt, $credential, $options),
                    'x-ai' => $this->generateImageWithXai($prompt, $credential, $options),
                    'gemini' => $this->generateImageWithGemini($prompt, $credential, $options),
                    default => throw new \Exception("Unsupported image provider: {$provider}")
                };

                Log::info("Successfully generated image with provider: {$provider}");
                return $result;

            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Image provider {$provider} failed: " . $e->getMessage());
                // Continue to next provider in fallback chain
                continue;
            }
        }

        // All providers failed
        throw new \Exception(
            "All image AI providers failed. Last error: " .
            ($lastError ? $lastError->getMessage() : 'Unknown error')
        );
    }

    /**
     * Generate image with Flux (via Fal.ai)
     */
    protected function generateImageWithFlux(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;

        // Fal.ai Flux endpoint
        $response = Http::timeout(120)
            ->withHeaders(['Authorization' => "Key {$apiKey}"])
            ->post('https://fal.run/fal-ai/flux/schnell', [
                'prompt' => $prompt,
                'image_size' => [
                    'width' => $width,
                    'height' => $height,
                ],
                'num_inference_steps' => 4,
                'num_images' => 1,
            ]);

        if (!$response->successful()) {
            Log::error('Flux API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Flux API request failed: " . $response->body());
        }

        $data = $response->json();

        // Extract image URL from response
        $imageUrl = $data['images'][0]['url'] ?? null;

        if (!$imageUrl) {
            throw new \Exception("No image URL returned from Flux API");
        }

        return [
            'image_url' => $imageUrl,
            'provider' => 'flux',
            'raw_response' => $data
        ];
    }

    /**
     * Generate image with X.ai (Grok Image)
     */
    protected function generateImageWithXai(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'grok-2-image'; // follow x.ai docs default
        $count = (int) ($options['count'] ?? 1);
        if ($count < 1) {
            $count = 1;
        } elseif ($count > 4) {
            $count = 4; // align with typical limits
        }

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.x.ai/v1/images/generations', [
                'model' => $model,
                'prompt' => $prompt,
                'n' => $count,
            ]);

        if (!$response->successful()) {
            Log::error('X.ai Image API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("X.ai image API request failed: " . $response->body());
        }

        $data = $response->json();
        $firstImage = $data['data'][0] ?? [];
        $imageUrl = $firstImage['url'] ?? null;

        if (!$imageUrl) {
            throw new \Exception("No image URL returned from X.ai API");
        }

        return [
            'image_url' => $imageUrl,
            'provider' => 'x-ai',
            'raw_response' => $data
        ];
    }

    protected function generateImageWithGemini(string $prompt, ApiCredential $credential, array $options): array
    {
        $apiKey = $credential->api_key;
        $model = $options['model'] ?? 'imagen-4.0-fast-generate-001';
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;

        // Calculate aspect ratio from width and height
        $aspectRatio = $this->calculateAspectRatio($width, $height);

        $response = Http::timeout(120)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:predict?key={$apiKey}", [
                'instances' => [
                    ['prompt' => $prompt]
                ],
                'parameters' => [
                    'sampleCount' => 1,
                    'aspectRatio' => $aspectRatio,
                    'personGeneration' => 'allow_all',
                ]
            ]);

        if (!$response->successful()) {
            Log::error('Gemini Imagen API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Gemini Imagen API request failed: " . $response->body());
        }

        $data = $response->json();
        $imageData = $data['predictions'][0]['bytesBase64Encoded'] ?? null;

        if (!$imageData) {
            throw new \Exception("No image data returned from Gemini Imagen API");
        }

        // Gemini returns base64, convert to URL or save to storage
        $imageUrl = 'data:image/png;base64,' . $imageData;

        return [
            'image_url' => $imageUrl,
            'image_data' => $imageData,
            'provider' => 'gemini',
            'model' => $model,
            'raw_response' => $data
        ];
    }

    /**
     * Calculate aspect ratio from width and height
     */
    protected function calculateAspectRatio(int $width, int $height): string
    {
        // Common aspect ratios mapping
        $ratios = [
            '1:1' => [1024, 1024],   // Square
            '16:9' => [1920, 1080],  // Landscape
            '9:16' => [1080, 1920],  // Portrait
            '4:3' => [1024, 768],    // Standard
            '3:4' => [768, 1024],    // Portrait standard
            '21:9' => [2560, 1080],  // Ultra wide
        ];

        // Calculate ratio
        $gcd = $this->gcd($width, $height);
        $ratioW = $width / $gcd;
        $ratioH = $height / $gcd;

        // Check if matches common ratios
        foreach ($ratios as $ratio => $dimensions) {
            $rGcd = $this->gcd($dimensions[0], $dimensions[1]);
            $rW = $dimensions[0] / $rGcd;
            $rH = $dimensions[1] / $rGcd;

            if ($ratioW == $rW && $ratioH == $rH) {
                return $ratio;
            }
        }

        // Return calculated ratio
        return "{$ratioW}:{$ratioH}";
    }

    /**
     * Calculate Greatest Common Divisor (GCD)
     */
    protected function gcd(int $a, int $b): int
    {
        return $b ? $this->gcd($b, $a % $b) : $a;
    }

    /**
     * Remove background from image using Fal.ai
     */
    public function removeBackground(string $imageData): array
    {
        // Get Flux credential (same API key for fal.ai services)
        $credential = $this->getCredentialByProvider('image_generation', 'flux');

        if (!$credential) {
            throw new \Exception("No Flux/Fal.ai credential found");
        }

        $apiKey = $credential->api_key;

        // Call fal.ai background removal API with base64
        $response = Http::timeout(60)
            ->withHeaders(['Authorization' => "Key {$apiKey}"])
            ->post('https://fal.run/fal-ai/imageutils/rembg', [
                'image_url' => $imageData, // Accepts data:image/png;base64,...
            ]);

        if (!$response->successful()) {
            Log::error('Fal.ai RemBG API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Background removal failed: " . $response->body());
        }

        $data = $response->json();

        // Extract image URL from response
        $resultImageUrl = $data['image']['url'] ?? null;

        if (!$resultImageUrl) {
            throw new \Exception("No image URL returned from background removal API");
        }

        return [
            'image_url' => $resultImageUrl,
            'provider' => 'flux-rembg',
            'raw_response' => $data
        ];
    }

    /**
     * Estimate tokens for a given text (rough approximation)
     *
     * @param string $text
     * @return int Estimated token count
     */
    public function estimateTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters or 0.75 words
        $charCount = mb_strlen($text);
        $wordCount = str_word_count($text);
        
        // Use average of both methods
        $fromChars = (int) ($charCount / 4);
        $fromWords = (int) ($wordCount / 0.75);
        
        return (int) (($fromChars + $fromWords) / 2);
    }
}
