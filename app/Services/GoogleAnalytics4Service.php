<?php

namespace App\Services;

use App\Models\GA4Connection;
use App\Models\GA4TrafficData;
use App\Models\GA4ReferralSource;
use App\Models\GA4PageStat;
use App\Models\OAuthService;
use Google\Client as GoogleClient;
use Google\Service\AnalyticsData;
use Illuminate\Support\Facades\Log;

class GoogleAnalytics4Service
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
        $redirectUri = config('app.url') . '/oauth/google/ga4/callback';
        $this->client->setRedirectUri($redirectUri);

        $this->client->addScope('https://www.googleapis.com/auth/analytics.readonly');
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
            'service' => 'ga4',
        ]));

        return $this->client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and store tokens
     */
    public function handleCallback(string $code, int $websiteId, string $propertyId): GA4Connection
    {
        // Ensure correct redirect_uri for GA4 (dynamic)
        $redirectUri = config('app.url') . '/oauth/google/ga4/callback';
        $this->client->setRedirectUri($redirectUri);

        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \Exception('OAuth error: ' . $token['error_description']);
        }

        $connection = GA4Connection::updateOrCreate(
            ['website_id' => $websiteId],
            [
                'property_id' => $propertyId,
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
     * Refresh access token if expired
     */
    public function refreshTokenIfNeeded(GA4Connection $connection): void
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
     * Sync traffic data from GA4 API
     */
    public function syncTrafficData(GA4Connection $connection, int $days = 30): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        $analytics = new AnalyticsData($this->client);

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->subDay()->format('Y-m-d');

        $request = new AnalyticsData\RunReportRequest();
        $request->setDateRanges([
            new AnalyticsData\DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])
        ]);
        $request->setDimensions([
            new AnalyticsData\Dimension(['name' => 'date']),
        ]);
        $request->setMetrics([
            new AnalyticsData\Metric(['name' => 'totalUsers']),
            new AnalyticsData\Metric(['name' => 'newUsers']),
            new AnalyticsData\Metric(['name' => 'sessions']),
            new AnalyticsData\Metric(['name' => 'screenPageViews']),
            new AnalyticsData\Metric(['name' => 'averageSessionDuration']),
            new AnalyticsData\Metric(['name' => 'bounceRate']),
            new AnalyticsData\Metric(['name' => 'engagementRate']),
            new AnalyticsData\Metric(['name' => 'conversions']),
        ]);

        try {
            $response = $analytics->properties->runReport(
                'properties/' . $connection->property_id,
                $request
            );

            $rowsSynced = 0;

            foreach ($response->getRows() ?? [] as $row) {
                $date = $row->getDimensionValues()[0]->getValue();
                $metrics = $row->getMetricValues();

                GA4TrafficData::updateOrCreate(
                    [
                        'website_id' => $connection->website_id,
                        'connection_id' => $connection->id,
                        'date' => \DateTime::createFromFormat('Ymd', $date)->format('Y-m-d'),
                    ],
                    [
                        'users' => (int) $metrics[0]->getValue(),
                        'new_users' => (int) $metrics[1]->getValue(),
                        'sessions' => (int) $metrics[2]->getValue(),
                        'page_views' => (int) $metrics[3]->getValue(),
                        'avg_session_duration' => (float) $metrics[4]->getValue(),
                        'bounce_rate' => (float) $metrics[5]->getValue(),
                        'engagement_rate' => (float) $metrics[6]->getValue(),
                        'conversions' => (int) $metrics[7]->getValue(),
                    ]
                );

                $rowsSynced++;
            }

            $connection->update([
                'last_sync_at' => now(),
                'sync_status' => 'completed',
                'sync_error' => null,
            ]);

            Log::info("GA4 Traffic synced", [
                'website_id' => $connection->website_id,
                'rows_synced' => $rowsSynced,
            ]);

            return [
                'success' => true,
                'rows_synced' => $rowsSynced,
                'date_range' => [$startDate, $endDate],
            ];

        } catch (\Exception $e) {
            $connection->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error("GA4 traffic sync failed", [
                'website_id' => $connection->website_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync referral sources (FREE backlink alternative!)
     */
    public function syncReferralSources(GA4Connection $connection, int $days = 30): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        $analytics = new AnalyticsData($this->client);

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->subDay()->format('Y-m-d');

        $request = new AnalyticsData\RunReportRequest();
        $request->setDateRanges([
            new AnalyticsData\DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])
        ]);
        $request->setDimensions([
            new AnalyticsData\Dimension(['name' => 'date']),
            new AnalyticsData\Dimension(['name' => 'sessionSource']),
            new AnalyticsData\Dimension(['name' => 'sessionMedium']),
        ]);
        $request->setMetrics([
            new AnalyticsData\Metric(['name' => 'totalUsers']),
            new AnalyticsData\Metric(['name' => 'sessions']),
            new AnalyticsData\Metric(['name' => 'screenPageViews']),
            new AnalyticsData\Metric(['name' => 'averageSessionDuration']),
            new AnalyticsData\Metric(['name' => 'bounceRate']),
        ]);

        try {
            $response = $analytics->properties->runReport(
                'properties/' . $connection->property_id,
                $request
            );

            $sourcesSynced = 0;

            foreach ($response->getRows() ?? [] as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $date = $dimensions[0]->getValue();
                $source = $dimensions[1]->getValue();
                $medium = $dimensions[2]->getValue();

                GA4ReferralSource::updateOrCreate(
                    [
                        'website_id' => $connection->website_id,
                        'connection_id' => $connection->id,
                        'date' => \DateTime::createFromFormat('Ymd', $date)->format('Y-m-d'),
                        'source' => $source,
                        'medium' => $medium,
                    ],
                    [
                        'users' => (int) $metrics[0]->getValue(),
                        'sessions' => (int) $metrics[1]->getValue(),
                        'page_views' => (int) $metrics[2]->getValue(),
                        'avg_session_duration' => (float) $metrics[3]->getValue(),
                        'bounce_rate' => (float) $metrics[4]->getValue(),
                    ]
                );

                $sourcesSynced++;
            }

            Log::info("GA4 Referral Sources synced", [
                'website_id' => $connection->website_id,
                'sources_synced' => $sourcesSynced,
            ]);

            return [
                'success' => true,
                'sources_synced' => $sourcesSynced,
                'date_range' => [$startDate, $endDate],
            ];

        } catch (\Exception $e) {
            Log::error("GA4 referral sync failed", [
                'website_id' => $connection->website_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync page statistics
     */
    public function syncPageStats(GA4Connection $connection, int $days = 30): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        $analytics = new AnalyticsData($this->client);

        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->subDay()->format('Y-m-d');

        $request = new AnalyticsData\RunReportRequest();
        $request->setDateRanges([
            new AnalyticsData\DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])
        ]);
        $request->setDimensions([
            new AnalyticsData\Dimension(['name' => 'date']),
            new AnalyticsData\Dimension(['name' => 'pagePath']),
            new AnalyticsData\Dimension(['name' => 'pageTitle']),
        ]);
        $request->setMetrics([
            new AnalyticsData\Metric(['name' => 'screenPageViews']),
            new AnalyticsData\Metric(['name' => 'uniquePageViews']),
            new AnalyticsData\Metric(['name' => 'averageTimeOnPage']),
            new AnalyticsData\Metric(['name' => 'bounceRate']),
            new AnalyticsData\Metric(['name' => 'exitRate']),
        ]);

        try {
            $response = $analytics->properties->runReport(
                'properties/' . $connection->property_id,
                $request
            );

            $pagesSynced = 0;

            foreach ($response->getRows() ?? [] as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $date = $dimensions[0]->getValue();
                $pagePath = $dimensions[1]->getValue();
                $pageTitle = $dimensions[2]->getValue();

                GA4PageStat::updateOrCreate(
                    [
                        'website_id' => $connection->website_id,
                        'connection_id' => $connection->id,
                        'date' => \DateTime::createFromFormat('Ymd', $date)->format('Y-m-d'),
                        'page_path' => $pagePath,
                    ],
                    [
                        'page_title' => $pageTitle,
                        'page_views' => (int) $metrics[0]->getValue(),
                        'unique_page_views' => (int) $metrics[1]->getValue(),
                        'avg_time_on_page' => (float) $metrics[2]->getValue(),
                        'bounce_rate' => (float) $metrics[3]->getValue(),
                        'exit_rate' => (float) $metrics[4]->getValue(),
                    ]
                );

                $pagesSynced++;
            }

            Log::info("GA4 Page Stats synced", [
                'website_id' => $connection->website_id,
                'pages_synced' => $pagesSynced,
            ]);

            return [
                'success' => true,
                'pages_synced' => $pagesSynced,
                'date_range' => [$startDate, $endDate],
            ];

        } catch (\Exception $e) {
            Log::error("GA4 page stats sync failed", [
                'website_id' => $connection->website_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get available GA4 properties for the authenticated user
     */
    public function getAvailableProperties(GA4Connection $connection): array
    {
        $this->refreshTokenIfNeeded($connection);

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        // Note: This requires Google Analytics Admin API
        // For now, return empty array - property selection should be done during OAuth
        return [];
    }
}
