<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * DataForSeoService
 * 
 * SERP, PAA, Related Searches API
 * Cost: ~$0.001 per SERP task
 */
class DataForSeoService
{
    private ?string $apiUser = null;
    private ?string $apiPassword = null;
    private string $baseUrl = 'https://api.dataforseo.com/v3';
    private int $timeout = 120;
    private bool $isConfigured = false;

    // DataForSEO location codes mapping
    private array $locationCodes = [
        'ID' => 2360, 'US' => 2840, 'GB' => 2826, 'CA' => 2124, 'AU' => 2036,
        'DE' => 2276, 'FR' => 2250, 'IT' => 2380, 'ES' => 2724, 'NL' => 2528,
        'BR' => 2076, 'MX' => 2484, 'JP' => 2392, 'KR' => 2410, 'IN' => 2356,
        'SG' => 2702, 'MY' => 2458, 'TH' => 2764, 'VN' => 2704, 'PH' => 2608,
        'TR' => 2792, 'SA' => 2682, 'AE' => 2784, 'ZA' => 2710, 'NG' => 2566,
    ];

    public function __construct()
    {
        $credential = ApiCredential::where('category', 'deep_research')
            ->where('provider', 'dataforseo')
            ->where('is_active', true)
            ->first();

        if ($credential) {
            $this->apiUser = $credential->api_key;
            $this->apiPassword = $credential->api_secret ?? '';
            $this->isConfigured = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured) {
            throw new Exception('DataForSEO API not configured. Add in Admin > API Keys (category: deep_research, provider: dataforseo).');
        }
    }

    /**
     * Get SERP organic with PAA
     * @param string $keyword - Search keyword
     * @param string $location - Country code (e.g., 'ID', 'US') or numeric location code
     * @param string $languageCode - Language code (e.g., 'id', 'en')
     */
    public function getSerpOrganic(string $keyword, string $location = 'ID', string $languageCode = 'id', int $depth = 10): array
    {
        $this->ensureConfigured();
        
        // Convert country code to location code if string
        $locationCode = is_numeric($location) ? (int)$location : ($this->locationCodes[strtoupper($location)] ?? 2360);
        
        $payload = [[
            'keyword' => $keyword,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'device' => 'desktop',
            'os' => 'windows',
            'depth' => $depth,
            'people_also_ask_click_depth' => 1,
            'calculate_rectangles' => false
        ]];

        $response = $this->makeRequest('/serp/google/organic/live/advanced', $payload);
        return $this->parseSerpResponse($response);
    }

    /**
     * Batch SERP for multiple keywords
     */
    public function batchGetSerpOrganic(array $keywords, int $locationCode = 2360, string $languageCode = 'id'): array
    {
        $this->ensureConfigured();
        
        $tasks = array_map(fn($keyword) => [
            'keyword' => $keyword,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'device' => 'desktop',
            'depth' => 10,
            'people_also_ask_click_depth' => 1,
        ], $keywords);

        $response = $this->makeRequest('/serp/google/organic/live/advanced', $tasks);
        
        $results = [];
        foreach ($response['tasks'] ?? [] as $task) {
            foreach ($task['result'] ?? [] as $result) {
                $kw = $result['keyword'] ?? '';
                $results[$kw] = $this->parseSingleSerpResult($result);
            }
        }
        return $results;
    }

    /**
     * Extract competitors from SERP
     */
    public function extractCompetitors(array $serpData, int $topN = 10): array
    {
        $competitors = [];
        foreach (array_slice($serpData['organic_results'] ?? [], 0, $topN) as $result) {
            if ($domain = $result['domain'] ?? null) {
                $competitors[] = [
                    'domain' => $domain,
                    'rank' => $result['position'] ?? 0,
                    'title' => $result['title'] ?? '',
                    'url' => $result['url'] ?? '',
                ];
            }
        }
        return $competitors;
    }

    /**
     * Aggregate competitors from multiple SERPs
     */
    public function aggregateCompetitors(array $serpResults): array
    {
        $map = [];
        foreach ($serpResults as $serpData) {
            foreach ($serpData['organic_results'] ?? [] as $result) {
                $domain = $result['domain'] ?? '';
                if (!$domain) continue;
                
                if (!isset($map[$domain])) {
                    $map[$domain] = [
                        'domain' => $domain,
                        'appears_in_keywords' => 0,
                        'total_rank' => 0,
                        'best_rank' => 999,
                    ];
                }
                $rank = $result['position'] ?? 999;
                $map[$domain]['appears_in_keywords']++;
                $map[$domain]['total_rank'] += $rank;
                $map[$domain]['best_rank'] = min($map[$domain]['best_rank'], $rank);
            }
        }

        foreach ($map as &$data) {
            $data['avg_rank'] = $data['total_rank'] / max(1, $data['appears_in_keywords']);
        }

        usort($map, fn($a, $b) => 
            $b['appears_in_keywords'] <=> $a['appears_in_keywords'] ?: $a['avg_rank'] <=> $b['avg_rank']
        );

        return array_values($map);
    }

    private function parseSerpResponse(array $response): array
    {
        return $this->parseSingleSerpResult($response['tasks'][0]['result'][0] ?? []);
    }

    private function parseSingleSerpResult(array $result): array
    {
        $organic = [];
        $paa = [];
        $related = [];

        foreach ($result['items'] ?? [] as $item) {
            $type = $item['type'] ?? '';
            
            if ($type === 'organic') {
                $organic[] = [
                    'position' => $item['rank_group'] ?? 0,
                    'title' => $item['title'] ?? '',
                    'url' => $item['url'] ?? '',
                    'domain' => $item['domain'] ?? '',
                    'description' => $item['description'] ?? '',
                ];
            } elseif ($type === 'people_also_ask') {
                foreach ($item['items'] ?? [] as $paaItem) {
                    $paa[] = [
                        'question' => $paaItem['title'] ?? '',
                        'answer' => $paaItem['expanded_element'][0]['description'] ?? '',
                        'source_url' => $paaItem['expanded_element'][0]['url'] ?? '',
                    ];
                }
            } elseif (in_array($type, ['related_searches', 'people_also_search'])) {
                foreach ($item['items'] ?? [] as $kw) {
                    if (is_string($kw) && trim($kw)) {
                        $related[] = trim($kw);
                    }
                }
            }
        }

        return [
            'keyword' => $result['keyword'] ?? '',
            'organic_results' => $organic,
            'people_also_ask' => $paa,
            'related_searches' => array_unique($related),
            'total_results' => $result['total_count'] ?? 0,
        ];
    }

    private function makeRequest(string $endpoint, array $payload): array
    {
        $auth = base64_encode("{$this->apiUser}:{$this->apiPassword}");
        
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => "Basic {$auth}",
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . $endpoint, $payload);

        if (!$response->successful()) {
            throw new Exception("DataForSEO API error: HTTP {$response->status()}");
        }

        $data = $response->json();
        if (($data['status_code'] ?? 0) !== 20000) {
            throw new Exception("DataForSEO error: " . ($data['status_message'] ?? 'Unknown'));
        }

        return $data;
    }
}
