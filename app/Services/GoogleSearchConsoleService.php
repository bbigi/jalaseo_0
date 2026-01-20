<?php

namespace App\Services;

use App\Models\GSCConnection;
use App\Models\GSCKeyword;
use App\Models\GSCPage;
use App\Models\GSCSyncJob;
use App\Models\OAuthService;
use Google\Client as GoogleClient;
use Google\Service\SearchConsole;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    protected GoogleClient $client;

    public function __construct()
    {
        // Load Google OAuth config from database (with fallback to .env)
        $googleConfig = OAuthService::getConfig('google');

        $this->client = new GoogleClient();
        $this->client->setClientId($googleConfig['client_id']);
        $this->client->setClientSecret($googleConfig['client_secret']);

        // Dynamic redirect URI (works for local and production)
        $redirectUri = config('app.url') . '/oauth/google/gsc/callback';
        $this->client->setRedirectUri($redirectUri);

        $this->client->addScope(SearchConsole::WEBMASTERS_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Get OAuth authorization URL
     */
    public function getAuthorizationUrl(int $websiteId): string
    {
        $this->client->setState(json_encode([
            'website_id' => $websiteId,
            'service' => 'gsc',
        ]));

        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): array
    {
        // Ensure correct redirect_uri for GSC (dynamic)
        $redirectUri = config('app.url') . '/oauth/google/gsc/callback';
        $this->client->setRedirectUri($redirectUri);

        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            \Log::error('GSC OAuth error', [
                'error' => $token['error'],
                'description' => $token['error_description'] ?? 'No description',
                'full_response' => $token
            ]);
            throw new \Exception('OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        return $token;
    }

    /**
     * Handle OAuth callback with pre-exchanged token
     */
    public function handleCallbackWithToken(array $token, int $websiteId, string $propertyUrl): GSCConnection
    {
        $connection = GSCConnection::updateOrCreate(
            ['website_id' => $websiteId],
            [
                'property_url' => $propertyUrl,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($token['expires_in']),
                'verified' => true,
                'sync_status' => 'pending',
            ]
        );

        return $connection;
    }

    /**
     * Handle OAuth callback and store tokens (legacy method)
     */
    public function handleCallback(string $code, int $websiteId, string $propertyUrl): GSCConnection
    {
        $token = $this->exchangeCodeForToken($code);

        if (isset($token['error'])) {
            \Log::error('GSC OAuth error', [
                'error' => $token['error'],
                'description' => $token['error_description'] ?? 'No description',
                'website_id' => $websiteId,
                'full_response' => $token
            ]);
            throw new \Exception('OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $connection = GSCConnection::updateOrCreate(
            ['website_id' => $websiteId],
            [
                'property_url' => $propertyUrl,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($token['expires_in']),
                'verified' => true,
                'sync_status' => 'pending',
            ]
        );

        return $connection;
    }

    /**
     * Get list of available properties for the authenticated user
     */
    public function getAvailableProperties(string $accessToken, string $refreshToken): array
    {
        $this->client->setAccessToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

        $service = new SearchConsole($this->client);
        $siteList = $service->sites->listSites();

        $properties = [];
        foreach ($siteList->getSiteEntry() as $site) {
            $properties[] = $site->getSiteUrl();
        }

        return $properties;
    }

    /**
     * Refresh access token if expired
     */
    public function refreshTokenIfNeeded(GSCConnection $connection): void
    {
        if ($connection->needsRefresh() && $connection->refresh_token) {
            $this->client->setAccessToken([
                'access_token' => $connection->access_token,
                'refresh_token' => $connection->refresh_token,
                'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
            ]);

            $this->client->fetchAccessTokenWithRefreshToken($connection->refresh_token);
            $newToken = $this->client->getAccessToken();

            $connection->update([
                'access_token' => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);
        }
    }

    /**
     * Sync keyword data from GSC API
     */
    public function syncKeywordData(GSCConnection $connection, int $days = 30): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        $service = new SearchConsole($this->client);

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->subDay()->format('Y-m-d');

        // Create sync job for this sync cycle
        $syncJob = GSCSyncJob::create([
            'connection_id' => $connection->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'job_status' => 'processing',
            'started_at' => now(),
        ]);

        // Auto-delete old data based on sync cycles (keep last 2 syncs only)
        // Strategy: Keep data for 2 sync cycles, delete older ones
        $allCompletedSyncJobs = GSCSyncJob::where('connection_id', $connection->id)
            ->where('job_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->pluck('id');

        // Keep first 2 (latest), delete the rest
        $oldSyncJobs = $allCompletedSyncJobs->skip(2);

        $deletedCount = 0;
        if ($oldSyncJobs->isNotEmpty()) {
            $deletedCount = GSCKeyword::whereIn('sync_job_id', $oldSyncJobs)->delete();

            // Delete the old sync jobs too
            GSCSyncJob::whereIn('id', $oldSyncJobs)->delete();

            if ($deletedCount > 0) {
                Log::info("GSC old keywords deleted", [
                    'website_id' => $connection->website_id,
                    'deleted_count' => $deletedCount,
                    'old_sync_jobs_deleted' => $oldSyncJobs->count(),
                ]);
            }
        }

        $request = new SearchConsole\SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['query', 'date', 'country', 'device']);
        $request->setRowLimit(25000); // GSC API max limit is 25,000 rows

        try {
            $response = $service->searchanalytics->query($connection->property_url, $request);
            $rows = $response->getRows() ?? [];

            $keywordsSynced = 0;

            foreach ($rows as $row) {
                $dimensions = $row->getKeys();
                $keywordText = $dimensions[0] ?? '';
                $position = $row->getPosition() ?? 0;

                GSCKeyword::updateOrCreate(
                    [
                        'website_id' => $connection->website_id,
                        'connection_id' => $connection->id,
                        'keyword' => $keywordText,
                        'date' => $dimensions[1] ?? $startDate,
                        'country' => $dimensions[2] ?? 'ALL',
                        'device' => $dimensions[3] ?? 'ALL',
                    ],
                    [
                        'sync_job_id' => $syncJob->id,
                        'clicks' => $row->getClicks() ?? 0,
                        'impressions' => $row->getImpressions() ?? 0,
                        'ctr' => $row->getCtr() ?? 0,
                        'position' => $position,
                    ]
                );

                // Update ane_keywords if keyword is being tracked
                $trackedKeyword = \App\Models\Keyword::where('website_id', $connection->website_id)
                    ->where('keyword', $keywordText)
                    ->where('tracking_status', 'active')
                    ->first();

                if ($trackedKeyword) {
                    $trackedKeyword->update([
                        'current_position' => $position,
                        'volume' => $row->getImpressions() ?? 0,
                    ]);

                    // Save history to ane_keyword_traces
                    \App\Models\KeywordTrace::create([
                        'keyword_id' => $trackedKeyword->id,
                        'position' => $position,
                        'impressions' => $row->getImpressions() ?? 0,
                        'clicks' => $row->getClicks() ?? 0,
                        'ctr' => $row->getCtr() ?? 0,
                        'date' => $dimensions[1] ?? $startDate,
                    ]);
                }

                $keywordsSynced++;
            }

            // Update sync job status
            $syncJob->update([
                'job_status' => 'completed',
                'keywords_synced' => $keywordsSynced,
                'completed_at' => now(),
            ]);

            $connection->update([
                'last_sync_at' => now(),
                'sync_status' => 'completed',
                'sync_error' => null,
            ]);

            Log::info("GSC Keywords synced", [
                'website_id' => $connection->website_id,
                'sync_job_id' => $syncJob->id,
                'keywords_synced' => $keywordsSynced,
                'deleted_old_data' => $deletedCount,
            ]);

            return [
                'success' => true,
                'sync_job_id' => $syncJob->id,
                'keywords_synced' => $keywordsSynced,
                'old_data_deleted' => $deletedCount,
                'date_range' => [$startDate, $endDate],
            ];

        } catch (\Exception $e) {
            $syncJob->update([
                'job_status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $connection->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error("GSC sync failed", [
                'website_id' => $connection->website_id,
                'sync_job_id' => $syncJob->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync page data from GSC API
     * Note: Uses the same sync_job_id from keyword sync if called together
     */
    public function syncPageData(GSCConnection $connection, int $days = 30, ?int $syncJobId = null): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        $service = new SearchConsole($this->client);

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->subDay()->format('Y-m-d');

        // Create sync job if not provided (standalone page sync)
        if (!$syncJobId) {
            $syncJob = GSCSyncJob::create([
                'connection_id' => $connection->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'job_status' => 'processing',
                'started_at' => now(),
            ]);
            $syncJobId = $syncJob->id;
        } else {
            $syncJob = GSCSyncJob::find($syncJobId);
        }

        // Auto-delete old data based on sync cycles (keep last 2 syncs only)
        $allCompletedSyncJobs = GSCSyncJob::where('connection_id', $connection->id)
            ->where('job_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->pluck('id');

        // Keep first 2 (latest), delete the rest
        $oldSyncJobs = $allCompletedSyncJobs->skip(2);

        $deletedCount = 0;
        if ($oldSyncJobs->isNotEmpty()) {
            $deletedCount = GSCPage::whereIn('sync_job_id', $oldSyncJobs)->delete();

            if ($deletedCount > 0) {
                Log::info("GSC old pages deleted", [
                    'website_id' => $connection->website_id,
                    'deleted_count' => $deletedCount,
                    'old_sync_jobs_deleted' => $oldSyncJobs->count(),
                ]);
            }
        }

        $request = new SearchConsole\SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['page', 'date']);
        $request->setRowLimit(25000); // GSC API max limit is 25,000 rows

        try {
            $response = $service->searchanalytics->query($connection->property_url, $request);
            $rows = $response->getRows() ?? [];

            $pagesSynced = 0;

            foreach ($rows as $row) {
                $dimensions = $row->getKeys();

                GSCPage::updateOrCreate(
                    [
                        'website_id' => $connection->website_id,
                        'connection_id' => $connection->id,
                        'page_url' => $dimensions[0] ?? '',
                        'date' => $dimensions[1] ?? $startDate,
                    ],
                    [
                        'sync_job_id' => $syncJobId, // Link to sync job
                        'clicks' => $row->getClicks() ?? 0,
                        'impressions' => $row->getImpressions() ?? 0,
                        'ctr' => $row->getCtr() ?? 0,
                        'position' => $row->getPosition() ?? 0,
                    ]
                );

                $pagesSynced++;
            }

            // Update sync job
            $syncJob->update([
                'pages_synced' => $pagesSynced,
            ]);

            Log::info("GSC Pages synced", [
                'website_id' => $connection->website_id,
                'sync_job_id' => $syncJobId,
                'pages_synced' => $pagesSynced,
                'deleted_old_data' => $deletedCount,
            ]);

            return [
                'success' => true,
                'sync_job_id' => $syncJobId,
                'pages_synced' => $pagesSynced,
                'old_data_deleted' => $deletedCount,
                'date_range' => [$startDate, $endDate],
            ];

        } catch (\Exception $e) {
            if ($syncJob) {
                $syncJob->update([
                    'job_status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error("GSC page sync failed", [
                'website_id' => $connection->website_id,
                'sync_job_id' => $syncJobId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
