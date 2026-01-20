<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnsplashService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.unsplash.com';

    public function __construct()
    {
        // Get Unsplash API key from database
        $credential = ApiCredential::where('category', 'stock_photos')
            ->where('provider', 'unsplash')
            ->where('is_active', true)
            ->first();

        if ($credential) {
            $this->apiKey = $credential->api_key;
        }
    }

    /**
     * Search photos on Unsplash
     *
     * @param string $query Search query
     * @param int $page Page number (default: 1)
     * @param int $perPage Results per page (default: 20, max: 30)
     * @return array
     */
    public function searchPhotos(string $query, int $page = 1, int $perPage = 20)
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'Unsplash API key not configured'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $this->apiKey,
                'Accept-Version' => 'v1'
            ])->get($this->baseUrl . '/search/photos', [
                'query' => $query,
                'page' => $page,
                'per_page' => min($perPage, 30),
                'orientation' => 'landscape' // Optional: prefer landscape
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'total' => $data['total'] ?? 0,
                    'total_pages' => $data['total_pages'] ?? 0,
                    'results' => $this->formatResults($data['results'] ?? [])
                ];
            }

            Log::error('Unsplash API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to fetch photos from Unsplash'
            ];

        } catch (\Exception $e) {
            Log::error('Unsplash search exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Trigger download tracking (required by Unsplash API guidelines)
     *
     * @param string $downloadLocation Download location URL from photo object
     * @return bool
     */
    public function triggerDownload(string $downloadLocation)
    {
        if (!$this->apiKey) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $this->apiKey,
            ])->get($downloadLocation);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Unsplash download trigger failed', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Format Unsplash API results for frontend
     *
     * @param array $results
     * @return array
     */
    protected function formatResults(array $results)
    {
        return array_map(function ($photo) {
            return [
                'id' => $photo['id'],
                'description' => $photo['description'] ?? $photo['alt_description'] ?? 'Unsplash photo',
                'alt_description' => $photo['alt_description'] ?? '',
                'urls' => [
                    'raw' => $photo['urls']['raw'] ?? '',
                    'full' => $photo['urls']['full'] ?? '',
                    'regular' => $photo['urls']['regular'] ?? '',
                    'small' => $photo['urls']['small'] ?? '',
                    'thumb' => $photo['urls']['thumb'] ?? '',
                ],
                'links' => [
                    'download_location' => $photo['links']['download_location'] ?? '',
                    'html' => $photo['links']['html'] ?? '',
                ],
                'user' => [
                    'name' => $photo['user']['name'] ?? 'Unknown',
                    'username' => $photo['user']['username'] ?? '',
                    'profile_url' => $photo['user']['links']['html'] ?? '',
                ],
                'width' => $photo['width'] ?? 0,
                'height' => $photo['height'] ?? 0,
            ];
        }, $results);
    }
}
