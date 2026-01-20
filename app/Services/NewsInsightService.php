<?php

namespace App\Services;

use App\Models\NewsSearch;
use App\Models\NewsResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class NewsInsightService
{
    private $serperService;

    public function __construct()
    {
        $this->serperService = new SerperSearchService();
    }

    /**
     * Perform search and save to database
     */
    public function performSearch(
        int $userId,
        string $source,
        ?string $theme,
        string $timeRange,
        string $language = 'id',
        string $country = 'id',
        ?string $location = null
    ): array {
        try {
            DB::beginTransaction();

            // Search via Serper API
            $results = $this->serperService->searchNews($source, $theme, $timeRange, $language, $country, $location);

            // Save search record
            $search = NewsSearch::create([
                'created_by' => $userId,
                'source' => $source,
                'theme' => $theme,
                'time_range' => $timeRange,
                'language' => $language,
                'country' => $country,
                'results_count' => count($results),
            ]);

            // Save results
            foreach ($results as $result) {
                NewsResult::create([
                    'search_id' => $search->id,
                    'title' => $result['title'],
                    'url' => $result['url'],
                    'snippet' => $result['snippet'],
                    'publish_date' => $result['publish_date'],
                    'position' => $result['position'],
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
            Log::error('NewsInsightService::performSearch Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user's search history
     */
    public function getUserSearchHistory(int $userId, int $limit = 10): array
    {
        $searches = NewsSearch::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $searches->toArray();
    }

    /**
     * Get search results by ID
     */
    public function getSearchResults(int $searchId): array
    {
        $search = NewsSearch::with('results')->find($searchId);

        if (!$search) {
            throw new Exception('Search not found');
        }

        return [
            'search' => $search->toArray(),
            'results' => $search->results->toArray(),
        ];
    }

    /**
     * Get user statistics
     */
    public function getStats(int $userId): array
    {
        $totalSearches = NewsSearch::where('created_by', $userId)->count();

        // Count insights created from news searches
        $insightsCreated = \App\Models\Insight::where('created_by', $userId)
            ->where('source_type', 'news_search')
            ->count();

        $topSources = NewsSearch::where('created_by', $userId)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        return [
            'total_searches' => $totalSearches,
            'insights_created' => $insightsCreated,
            'top_sources' => $topSources,
        ];
    }
}
