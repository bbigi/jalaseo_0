<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Http;

class SitemapService
{
    public function getTotalUrls(Website $website): ?int
    {
        $sitemapPaths = [
            '/sitemap.xml',
            '/sitemap_index.xml',
            '/wp-sitemap.xml',
        ];

        foreach ($sitemapPaths as $path) {
            $url = rtrim($website->url, '/') . $path;

            try {
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    $xml = simplexml_load_string($response->body());

                    if ($xml !== false) {
                        $count = $this->countUrlsInSitemap($xml);
                        if ($count > 0) {
                            return $count;
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    protected function countUrlsInSitemap(\SimpleXMLElement $xml): int
    {
        $count = 0;

        // Check for sitemap index (contains other sitemaps)
        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemap) {
                $sitemapUrl = (string) $sitemap->loc;
                try {
                    $response = Http::timeout(10)->get($sitemapUrl);
                    if ($response->successful()) {
                        $subXml = simplexml_load_string($response->body());
                        if ($subXml !== false) {
                            $count += $this->countUrlsInSitemap($subXml);
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Count URLs in current sitemap
        if (isset($xml->url)) {
            $count += count($xml->url);
        }

        return $count;
    }
}
