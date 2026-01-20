<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DataForSEORankedKeywordService
{
    private ?string $apiUser = null;
    private ?string $apiPassword = null;
    private string $baseUrl = 'https://api.dataforseo.com/v3';
    private int $timeout = 120;
    private bool $configured = false;

    public function __construct()
    {
        $cred = ApiCredential::where('category', 'deep_research')
            ->where('provider', 'dataforseo')
            ->where('is_active', true)
            ->first();

        if ($cred) {
            $this->apiUser = $cred->api_key;
            $this->apiPassword = $cred->api_secret ?? '';
            $this->configured = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function fetchRankedKeywords(string $domain, int $locationCode = 2360, string $languageCode = 'id', int $limit = 50): array
    {
        $this->ensureConfigured();

        $payload = [[
            'target' => $domain,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'historical_serp_mode' => 'live',
            'ignore_synonyms' => false,
            'include_clickstream_data' => false,
            'item_types' => ['organic'],
            'load_rank_absolute' => false,
            'limit' => $limit,
            'order_by' => ['keyword_data.keyword_info.search_volume,desc'],
        ]];

        $response = Http::timeout($this->timeout)
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post("{$this->baseUrl}/dataforseo_labs/google/ranked_keywords/live", $payload);

        if (!$response->successful()) {
            throw new Exception("DataForSEO API error: HTTP {$response->status()}");
        }

        $data = $response->json();
        $statusCode = (int) ($data['status_code'] ?? 0);
        $statusMessage = $data['status_message'] ?? 'Unknown';
        if ($statusCode !== 20000) {
            throw new Exception("DataForSEO error: {$statusMessage}");
        }

        $task = $data['tasks'][0]['result'][0] ?? [];
        if (empty($task)) {
            throw new Exception('DataForSEO error: empty result payload');
        }

        return $task;
    }

    private function ensureConfigured(): void
    {
        if (!$this->configured) {
            throw new Exception('DataForSEO not configured. Add API key (category: deep_research, provider: dataforseo).');
        }
    }
}
