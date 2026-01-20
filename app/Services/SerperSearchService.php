<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Log;
use Exception;

class SerperSearchService
{
    private $apiKey;
    private $apiUrl = 'https://google.serper.dev/search';
    private $newsUrl = 'https://google.serper.dev/news';
    private $timeout = 30;

    public function __construct()
    {
        // Get API key from database
        $credential = ApiCredential::where('category', 'news_insight')
            ->where('provider', 'serper')
            ->where('is_active', true)
            ->first();

        if (!$credential) {
            throw new Exception('Serper API key not configured. Please add it in Admin > API Keys.');
        }

        $this->apiKey = $credential->api_key; // Auto-decrypts via model
    }

    /**
     * Search news by source domain and theme
     */
    public function searchNews(
        string $source,
        ?string $theme = null,
        string $timeRange = 'week',
        string $language = 'id',
        string $country = 'id',
        ?string $location = null
    ): array {
        try {
            // Build query
            $query = $this->buildQuery($source, $theme);

            // Build payload
            $payload = [
                'q' => $query,
                'num' => 20, // Max results
                'gl' => $country, // Geo-location (country code)
                'hl' => $language, // Language
            ];

            // Add location if provided
            if ($location) {
                $payload['location'] = $location;
            }

            // Add time range
            if ($timeRange !== 'all') {
                $payload['tbs'] = $this->getTimeRangeFilter($timeRange);
            }

            // Make API call
            $response = $this->makeRequest($this->newsUrl, $payload);

            // Parse results
            return $this->parseResults($response);

        } catch (Exception $e) {
            Log::error('SerperSearchService Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build search query
     */
    private function buildQuery(string $source, ?string $theme): string
    {
        $query = "site:{$source}";

        if ($theme) {
            $query .= " {$theme}";
        }

        return $query;
    }

    /**
     * Get time range filter for Google Search
     */
    private function getTimeRangeFilter(string $range): string
    {
        return match($range) {
            'today' => 'qdr:d', // Past day
            'week' => 'qdr:w', // Past week
            'month' => 'qdr:m', // Past month
            'year' => 'qdr:y', // Past year
            default => '',
        };
    }

    /**
     * Make HTTP request to Serper API
     */
    private function makeRequest(string $url, array $payload): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }

        if ($httpCode !== 200) {
            $errorMsg = "Serper API returned HTTP {$httpCode}";
            if ($response) {
                $decoded = json_decode($response, true);
                $errorMsg .= ": " . ($decoded['message'] ?? 'Unknown error');
            }
            throw new Exception($errorMsg);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Serper API');
        }

        return $decoded;
    }

    /**
     * Parse API response
     */
    private function parseResults(array $response): array
    {
        $results = [];
        $items = $response['news'] ?? [];

        foreach ($items as $index => $item) {
            $results[] = [
                'title' => $item['title'] ?? '',
                'url' => $item['link'] ?? '',
                'snippet' => $item['snippet'] ?? '',
                'publish_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : null,
                'position' => $index + 1,
            ];
        }

        return $results;
    }

    /**
     * Search news by keyword (for Google Trends integration)
     */
    public function searchNewsByKeyword(
        string $keyword,
        string $timeRange = 'week',
        string $language = 'id',
        string $country = 'id',
        int $maxResults = 20
    ): array {
        try {
            // Build payload
            $payload = [
                'q' => $keyword,
                'num' => $maxResults,
                'gl' => $country,
                'hl' => $language,
            ];

            // Add time range
            if ($timeRange !== 'all') {
                $payload['tbs'] = $this->getTimeRangeFilter($timeRange);
            }

            // Make API call
            $response = $this->makeRequest($this->newsUrl, $payload);

            // Parse results
            return $this->parseResults($response);

        } catch (Exception $e) {
            Log::error('SerperSearchService::searchNewsByKeyword Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search web results by keyword (general Google Search)
     */
    public function searchWeb(
        string $keyword,
        string $language = 'id',
        string $country = 'id',
        int $maxResults = 10
    ): array {
        try {
            $payload = [
                'q' => $keyword,
                'num' => $maxResults,
                'gl' => $country,
                'hl' => $language,
            ];

            $response = $this->makeRequest($this->apiUrl, $payload);

            $items = $response['organic'] ?? [];
            $results = [];
            foreach ($items as $index => $item) {
                $url = $item['link'] ?? '';
                $results[] = [
                    'title' => $item['title'] ?? '',
                    'url' => $url,
                    'snippet' => $item['snippet'] ?? '',
                    'domain' => $url ? (parse_url($url, PHP_URL_HOST) ?: '') : '',
                    'position' => $index + 1,
                ];
            }

            return $results;
        } catch (Exception $e) {
            Log::error('SerperSearchService::searchWeb Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get popular news sources
     */
    public static function getPopularSources(): array
    {
        return [
            'indonesia' => [
                'detik.com' => 'Detik.com',
                'kompas.com' => 'Kompas.com',
                'tempo.co' => 'Tempo.co',
                'tribunnews.com' => 'Tribun News',
                'liputan6.com' => 'Liputan6',
                'cnnindonesia.com' => 'CNN Indonesia',
            ],
            'international' => [
                'techcrunch.com' => 'TechCrunch',
                'bbc.com' => 'BBC News',
                'reuters.com' => 'Reuters',
                'theverge.com' => 'The Verge',
                'wired.com' => 'Wired',
                'arstechnica.com' => 'Ars Technica',
            ],
        ];
    }

    /**
     * Get autocomplete suggestions from Google via Serper
     */
    public function getAutocompleteSuggestions(string $keyword, string $country = 'id', string $language = 'id'): array
    {
        try {
            $payload = [
                'q' => $keyword,
                'gl' => $country,
                'hl' => $language,
            ];

            $response = $this->makeRequest('https://google.serper.dev/autocomplete', $payload);
            
            return array_column($response['suggestions'] ?? [], 'value');
        } catch (Exception $e) {
            Log::error('Serper Autocomplete Error: ' . $e->getMessage());
            return [];
        }
    }
}
