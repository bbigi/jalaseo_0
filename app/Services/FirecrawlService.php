<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Log;

class FirecrawlService
{
    private $apiKey;
    private $apiUrl = 'https://api.firecrawl.dev/v2/scrape';

    public function __construct()
    {
        // Get API key from database
        $credential = ApiCredential::where('category', 'web_scraping')
            ->where('provider', 'firecrawl')
            ->where('is_active', true)
            ->first();

        $this->apiKey = $credential ? $credential->api_key : null;

        if (empty($this->apiKey)) {
            Log::error('Firecrawl API Key is not configured');
        }
    }

    /**
     * Fetch clean content from URL using Firecrawl
     *
     * @param string $url URL to scrape
     * @param int $timeout Timeout in seconds
     * @param int $retries Number of retry attempts
     * @return array ['success' => bool, 'content' => string|null, 'error' => string|null]
     */
    public function fetchUrlContent(string $url, int $timeout = 45, int $retries = 2): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'Firecrawl API Key not configured'
            ];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'content' => null,
                'error' => 'Invalid URL provided'
            ];
        }

        $payload = [
            'url' => $url,
            'onlyMainContent' => true,
            'formats' => ['markdown'],
            'waitFor' => 3000,
            'parsers' => []
        ];

        Log::info('Firecrawl scraping URL', ['url' => $url, 'timeout' => $timeout]);

        $lastError = null;

        // Retry logic
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            if ($attempt > 1) {
                Log::info("Firecrawl retry attempt {$attempt}/{$retries}", ['url' => $url]);
                sleep(2); // Wait 2 seconds before retry
            }

            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Handle cURL errors (timeout, connection issues)
            if ($curlError) {
                $lastError = "Connection error: {$curlError}";
                Log::error('Firecrawl cURL error', [
                    'url' => $url,
                    'error' => $curlError,
                    'attempt' => $attempt
                ]);
                continue; // Retry
            }

            // Handle HTTP errors
            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? $errorData['error'] ?? "HTTP {$httpCode}";
                $lastError = $errorMessage;

                Log::error('Firecrawl HTTP error', [
                    'url' => $url,
                    'code' => $httpCode,
                    'response' => substr($response, 0, 500),
                    'attempt' => $attempt
                ]);

                // Don't retry on 4xx errors (client errors)
                if ($httpCode >= 400 && $httpCode < 500) {
                    break;
                }
                continue; // Retry on 5xx errors
            }

            // Parse response
            $data = json_decode($response, true);

            if (!$data || !isset($data['data']['markdown'])) {
                $lastError = 'Invalid response from Firecrawl';
                Log::error('Firecrawl invalid response', [
                    'url' => $url,
                    'response' => substr($response, 0, 500),
                    'attempt' => $attempt
                ]);
                continue; // Retry
            }

            // Success!
            $content = $data['data']['markdown'];

            Log::info('Firecrawl success', [
                'url' => $url,
                'content_length' => strlen($content),
                'attempt' => $attempt
            ]);

            return [
                'success' => true,
                'content' => $content,
                'error' => null
            ];
        }

        // All retries failed
        Log::error('Firecrawl failed after all retries', [
            'url' => $url,
            'retries' => $retries,
            'last_error' => $lastError
        ]);

        return [
            'success' => false,
            'content' => null,
            'error' => $lastError ?? 'Unknown error after retries'
        ];
    }
}
