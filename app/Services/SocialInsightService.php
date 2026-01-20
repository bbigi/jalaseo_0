<?php

namespace App\Services;

use App\Models\SocialSearch;
use App\Models\SocialResult;
use App\Models\ApiCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class SocialInsightService
{
    private $rapidApiKey;

    public function __construct()
    {
        // Get RapidAPI key from database (one key for all RapidAPI services)
        $credential = ApiCredential::where('category', 'rapid_api')
            ->where('provider', 'rapidapi.com')
            ->where('is_active', true)
            ->first();

        $this->rapidApiKey = $credential ? $credential->api_key : null;
    }

    /**
     * Perform social media trending search
     */
    public function performSearch(
        int $userId,
        string $platform, // 'tiktok', 'twitter', 'google_trends'
        ?string $keyword = null,
        string $countryCode = 'ID',
        string $language = 'id'
    ): array {
        if (!$this->rapidApiKey) {
            throw new Exception('RapidAPI key not configured. Please add it in Admin API Keys.');
        }

        try {
            DB::beginTransaction();

            // Check cache first (30 minute TTL)
            $cacheKey = $this->getCacheKey($platform, $keyword, $countryCode);
            $cachedResults = Cache::get($cacheKey);

            if ($cachedResults) {
                Log::info('Social Insights: Using cached results for ' . $platform);
                $results = $cachedResults;
            } else {
                // Fetch from API based on platform
                $results = $this->fetchFromAPI($platform, $keyword, $countryCode, $language);

                // Cache for 30 minutes
                Cache::put($cacheKey, $results, now()->addMinutes(30));
            }

            // Save search record
            $search = SocialSearch::create([
                'created_by' => $userId,
                'keyword' => $keyword,
                'platform' => $platform,
                'country_code' => $countryCode,
                'language' => $language,
                'total_results' => count($results),
                'status' => 'completed',
            ]);

            // Save results
            foreach ($results as $result) {
                SocialResult::create([
                    'search_id' => $search->id,
                    'platform' => $platform,
                    'content' => $result['content'],
                    'author' => $result['author'] ?? null,
                    'link' => $result['link'] ?? null,
                    'engagement_likes' => $result['likes'] ?? 0,
                    'engagement_comments' => $result['comments'] ?? 0,
                    'engagement_shares' => $result['shares'] ?? 0,
                    'sentiment' => $result['sentiment'] ?? 'neutral',
                    'hashtags' => $result['hashtags'] ?? null,
                    'is_trending' => $result['is_trending'] ?? true,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'search_id' => $search->id,
                'results_count' => count($results),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SocialInsightService::performSearch Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch trending topics from API
     */
    private function fetchFromAPI(string $platform, ?string $keyword, string $countryCode, string $language): array
    {
        switch ($platform) {
            case 'tiktok':
                return $this->fetchTikTokTrending($countryCode);
            case 'twitter':
                return $this->fetchTwitterTrending($countryCode);
            case 'google_trends':
                return $this->fetchGoogleTrends($keyword, $countryCode);
            default:
                throw new Exception('Unsupported platform: ' . $platform);
        }
    }

    /**
     * Fetch TikTok trending topics via RapidAPI
     */
    private function fetchTikTokTrending(string $countryCode): array
    {
        // Use /feed/list for trending (not /feed/trending)
        $url = 'https://tiktok-scraper7.p.rapidapi.com/feed/list';

        $params = [
            'region' => strtoupper($countryCode),
            'count' => 20
        ];

        $response = $this->makeRapidAPIRequest($url, $params, 'tiktok-scraper7.p.rapidapi.com');

        // Handle response structure - can be data.videos or just data
        $videos = [];
        if (isset($response['data']['videos']) && is_array($response['data']['videos'])) {
            $videos = $response['data']['videos'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            $videos = $response['data'];
        }

        if (empty($videos)) {
            return [];
        }

        $results = [];
        foreach ($videos as $video) {
            $title = $video['title'] ?? $video['desc'] ?? '';
            $author = $video['author']['unique_id'] ?? $video['author']['uniqueId'] ?? 'unknown';
            $videoId = $video['video_id'] ?? $video['id'] ?? $video['aweme_id'] ?? '';

            // Get stats from multiple possible locations
            $stats = $video['stats'] ?? [];
            $musicInfo = $video['music_info'] ?? [];

            $likes = $stats['digg_count'] ?? $stats['diggCount'] ??
                    $musicInfo['digg_count'] ?? $video['digg_count'] ?? 0;

            $comments = $stats['comment_count'] ?? $stats['commentCount'] ??
                        $musicInfo['comment_count'] ?? $video['comment_count'] ?? 0;

            $shares = $stats['share_count'] ?? $stats['shareCount'] ??
                    $musicInfo['share_count'] ?? $video['share_count'] ?? 0;

            $videoUrl = !empty($videoId) && !empty($author)
                ? "https://www.tiktok.com/@{$author}/video/{$videoId}"
                : '';

            $results[] = [
                'content' => substr($title, 0, 100),
                'author' => "@{$author}",
                'link' => $videoUrl,
                'likes' => (int)$likes,
                'comments' => (int)$comments,
                'shares' => (int)$shares,
                'hashtags' => $this->extractHashtags($title),
                'is_trending' => true,
                'sentiment' => 'neutral',
            ];

            if (count($results) >= 20) break;
        }

        return $results;
    }

    /**
     * Fetch Twitter trending topics via RapidAPI
     */
    private function fetchTwitterTrending(string $countryCode): array
    {
        // Use Twitter AIO API (not twitter-api45)
        $woeid = $this->getTwitterWOEID($countryCode);
        $url = "https://twitter-aio.p.rapidapi.com/trends/{$woeid}";

        $response = $this->makeRapidAPIRequest($url, [], 'twitter-aio.p.rapidapi.com');

        // Handle response structure
        $trendsArray = [];
        if (isset($response[0]['trends']) && is_array($response[0]['trends'])) {
            $trendsArray = $response[0]['trends'];
        } elseif (isset($response['trends']) && is_array($response['trends'])) {
            $trendsArray = $response['trends'];
        }

        if (empty($trendsArray)) {
            return [];
        }

        $results = [];
        foreach ($trendsArray as $item) {
            $name = $item['name'] ?? '';
            $url = $item['url'] ?? '';
            $volume = $item['tweet_volume'] ?? 0;

            $results[] = [
                'content' => $name,
                'author' => 'Trending Twitter ' . $countryCode,
                'link' => $url,
                'likes' => $volume,
                'comments' => 0,
                'shares' => 0,
                'hashtags' => [substr($name, 0, 1) === '#' ? substr($name, 1) : $name],
                'is_trending' => true,
                'sentiment' => 'neutral',
            ];

            if (count($results) >= 20) break;
        }

        return $results;
    }

    /**
     * Fetch Google Trends via RapidAPI
     */
    private function fetchGoogleTrends(?string $keyword, string $countryCode): array
    {
        // Use Google Trends API4 (matching anewp)
        $apiHost = 'google-trends-api4.p.rapidapi.com';
        $endpoint = '/api/v3/trends/nows';
        $url = "https://{$apiHost}{$endpoint}";

        $params = [
            'geo' => strtoupper($countryCode)
        ];

        $response = $this->makeRapidAPIRequest($url, $params, $apiHost);

        // Parse response structure
        $parsed = $this->parseGoogleTrendsResponse($response);

        return $parsed;
    }

    /**
     * Parse Google Trends API response
     */
    private function parseGoogleTrendsResponse(array $res): array
    {
        $data = [];

        // Handle different response structures
        // API4 returns {"trendingNows": [...]}
        $trends = [];
        if (isset($res['trendingNows']) && is_array($res['trendingNows'])) {
            $trends = $res['trendingNows'];
        } elseif (isset($res['data']) && is_array($res['data'])) {
            $trends = $res['data'];
        } elseif (isset($res['trends']) && is_array($res['trends'])) {
            $trends = $res['trends'];
        } elseif (isset($res['storySummaries']) && is_array($res['storySummaries'])) {
            $trends = $res['storySummaries'];
        } elseif (is_array($res)) {
            $trends = $res;
        }

        foreach ($trends as $item) {
            // Extract keyword from API4 response structure
            $keyword = $item['keyword'] ??
                      $item['title'] ??
                      $item['query'] ??
                      ($item['entityNames'][0] ?? '');

            if (empty($keyword)) continue;

            // Extract searchVolume from API4 response
            $traffic = $item['searchVolume'] ??
                      $item['traffic'] ??
                      $item['formattedTraffic'] ??
                      $item['approx_traffic'] ??
                      0;

            // Clean traffic if it's string like "200K+"
            if (is_string($traffic)) {
                $traffic = intval(str_replace(['+', 'K', 'M', ','], '', $traffic)) * 1000;
            }

            // Extract related keywords if available
            $relatedKeywords = $item['relatedKeywords'] ?? [];
            $hashtags = is_array($relatedKeywords) && !empty($relatedKeywords)
                ? array_slice($relatedKeywords, 0, 5)
                : explode(' ', $keyword);

            $data[] = [
                'content' => $keyword,
                'author' => 'Google Trends',
                'link' => 'https://trends.google.com/trends/explore?q=' . urlencode($keyword),
                'likes' => (int)$traffic,
                'comments' => 0,
                'shares' => 0,
                'hashtags' => $hashtags,
                'is_trending' => true,
                'sentiment' => 'neutral',
            ];

            if (count($data) >= 20) break;
        }

        return $data;
    }

    /**
     * Make RapidAPI HTTP request
     */
    private function makeRapidAPIRequest(string $url, array $params, string $host): array
    {
        if (!empty($params)) {
            $queryString = http_build_query($params);
            $fullUrl = $url . '?' . $queryString;
        } else {
            $fullUrl = $url;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'x-rapidapi-host: ' . $host,
                'x-rapidapi-key: ' . $this->rapidApiKey
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('RapidAPI cURL error', [
                'url' => $fullUrl,
                'error' => $error
            ]);
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200 || !$response) {
            Log::error('RapidAPI request failed', [
                'url' => $fullUrl,
                'http_code' => $httpCode,
                'response' => substr($response, 0, 500)
            ]);
            throw new Exception('API request failed with code ' . $httpCode);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('RapidAPI JSON decode error', [
                'url' => $fullUrl,
                'json_error' => json_last_error_msg(),
                'response_sample' => substr($response, 0, 500)
            ]);
            throw new Exception('Invalid JSON response');
        }

        return $decoded ?? [];
    }

    /**
     * Get cache key for results
     */
    private function getCacheKey(string $platform, ?string $keyword, string $countryCode): string
    {
        return 'social_insights_' . $platform . '_' . $countryCode . '_' . md5($keyword ?? '');
    }

    /**
     * Extract hashtags from text
     */
    private function extractHashtags(string $text): array
    {
        preg_match_all('/#(\w+)/', $text, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Get Twitter WOEID (Where On Earth ID) for country
     */
    private function getTwitterWOEID(string $countryCode): int
    {
        $woeids = [
            'ID' => 23424846, // Indonesia
            'US' => 23424977, // United States
            'GB' => 23424975, // United Kingdom
            'AU' => 23424748, // Australia
            'CA' => 23424775, // Canada
            'IN' => 23424848, // India
            'CN' => 23424781, // China
            'JP' => 23424856, // Japan
            'KR' => 23424868, // South Korea
            'MY' => 23424901, // Malaysia
            'SG' => 23424948, // Singapore
            'TH' => 23424960, // Thailand
            'VN' => 23424984, // Vietnam
            'PH' => 23424934, // Philippines
            'BR' => 23424768, // Brazil
            'MX' => 23424900, // Mexico
            'AR' => 23424747, // Argentina
            'CL' => 23424782, // Chile
            'CO' => 23424787, // Colombia
            'DE' => 23424829, // Germany
            'FR' => 23424819, // France
            'IT' => 23424853, // Italy
            'ES' => 23424950, // Spain
            'NL' => 23424909, // Netherlands
            'PL' => 23424923, // Poland
            'TR' => 23424969, // Turkey
            'SA' => 23424938, // Saudi Arabia
            'AE' => 23424738, // United Arab Emirates
            'EG' => 23424802, // Egypt
            'ZA' => 23424942, // South Africa
            'NG' => 23424908, // Nigeria
            'NZ' => 23424916, // New Zealand
            'SE' => 23424954, // Sweden
            'NO' => 23424910, // Norway
            'DK' => 23424796, // Denmark
            'FI' => 23424812, // Finland
            'PT' => 23424925, // Portugal
            'GR' => 23424833, // Greece
            'CZ' => 23424810, // Czech Republic
            'RO' => 23424933, // Romania
            'HU' => 23424844, // Hungary
        ];

        return $woeids[strtoupper($countryCode)] ?? 1; // Default to Worldwide if unknown
    }

    /**
     * Get statistics for user
     */
    public function getStats(int $userId): array
    {
        $totalSearches = SocialSearch::where('created_by', $userId)->count();

        $insightsCreated = \App\Models\Insight::where('created_by', $userId)
            ->where('source_type', 'social_insight')
            ->count();

        $topPlatforms = SocialSearch::where('created_by', $userId)
            ->select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get()
            ->toArray();

        return [
            'total_searches' => $totalSearches,
            'insights_created' => $insightsCreated,
            'top_platforms' => $topPlatforms,
        ];
    }
}
