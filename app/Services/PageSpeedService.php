<?php

namespace App\Services;

use App\Models\ApiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class PageSpeedService
{
    public function run(string $url, string $strategy = 'mobile', ?string $apiKey = null): array
    {
        if ($apiKey === null) {
            $credential = ApiCredential::where('category', 'pagespeed-test')
                ->where('provider', 'google')
                ->where('is_active', true)
                ->first();
            $apiKey = $credential?->api_key ?? config('services.google.api_key') ?? env('GOOGLE_API_KEY');
        }

        $locale = $this->mapLocale(app()->getLocale());

        $response = Http::retry(2, 500)
            ->get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
                'url' => $url,
                'strategy' => $strategy,
                'key' => $apiKey,
                'locale' => $locale,
            ])
            ->throw()
            ->json();

        return $this->normalize($response);
    }

    /**
     * Map app locale to PSI-supported locale (fallback to en_US)
     */
    protected function mapLocale(?string $locale): string
    {
        if (!$locale) {
            return 'en_US';
        }

        $normalized = strtolower(str_replace('-', '_', $locale));

        $map = [
            'id' => 'id',
            'id_id' => 'id',
            'en' => 'en_US',
            'en_us' => 'en_US',
            'en_gb' => 'en_GB',
            'fr' => 'fr',
            'de' => 'de',
            'es' => 'es',
            'pt' => 'pt_BR',
            'pt_br' => 'pt_BR',
            'ja' => 'ja',
            'ko' => 'ko',
            'zh' => 'zh_CN',
            'zh_cn' => 'zh_CN',
            'ru' => 'ru',
            'ar' => 'ar',
        ];

        return $map[$normalized] ?? 'en_US';
    }

    protected function normalize(array $payload): array
    {
        $lighthouse = $payload['lighthouseResult'] ?? [];
        $audits = $lighthouse['audits'] ?? [];
        $categories = $lighthouse['categories'] ?? [];

        $scores = [
            'performance' => isset($categories['performance']['score']) ? $categories['performance']['score'] * 100 : null,
            'accessibility' => isset($categories['accessibility']['score']) ? $categories['accessibility']['score'] * 100 : null,
            'best_practices' => isset($categories['best-practices']['score']) ? $categories['best-practices']['score'] * 100 : null,
            'seo' => isset($categories['seo']['score']) ? $categories['seo']['score'] * 100 : null,
            'pwa' => isset($categories['pwa']['score']) ? $categories['pwa']['score'] * 100 : null,
        ];

        $labMetrics = [
            'fcp_ms' => Arr::get($audits, 'first-contentful-paint.numericValue'),
            'lcp_ms' => Arr::get($audits, 'largest-contentful-paint.numericValue'),
            'cls' => Arr::get($audits, 'cumulative-layout-shift.numericValue'),
            'tbt_ms' => Arr::get($audits, 'total-blocking-time.numericValue'),
            'si_ms' => Arr::get($audits, 'speed-index.numericValue'),
            'tti_ms' => Arr::get($audits, 'interactive.numericValue'),
            'inp_ms' => Arr::get($audits, 'experimental-interaction-to-next-paint.numericValue') ?? Arr::get($audits, 'experimental-interactivity.numericValue'),
        ];

        $field = $payload['loadingExperience'] ?? $payload['originLoadingExperience'] ?? [];
        $fieldMetrics = null;
        if ($field) {
            $fieldMetrics = [
                'lcp_p75_ms' => Arr::get($field, 'metrics.LARGEST_CONTENTFUL_PAINT_MS.percentile'),
                'inp_p75_ms' => Arr::get($field, 'metrics.INTERACTION_TO_NEXT_PAINT_MS.percentile'),
                'cls_p75' => Arr::get($field, 'metrics.CUMULATIVE_LAYOUT_SHIFT_SCORE.percentile') !== null
                    ? Arr::get($field, 'metrics.CUMULATIVE_LAYOUT_SHIFT_SCORE.percentile') / 100
                    : null,
                'lcp_good_ratio' => Arr::get($field, 'metrics.LARGEST_CONTENTFUL_PAINT_MS.distributions.0.proportion'),
                'inp_good_ratio' => Arr::get($field, 'metrics.INTERACTION_TO_NEXT_PAINT_MS.distributions.0.proportion'),
                'cls_good_ratio' => Arr::get($field, 'metrics.CUMULATIVE_LAYOUT_SHIFT_SCORE.distributions.0.proportion'),
                'period' => Arr::get($field, 'recordedMetrics') ? '28-day rolling' : null,
            ];
        }

        $opportunities = [];
        $diagnostics = [];

        foreach ($audits as $id => $audit) {
            $details = $audit['details'] ?? null;
            if (!$details || !isset($details['type'])) {
                continue;
            }
            $entry = [
                'id' => $id,
                'title' => $audit['title'] ?? $id,
                'description' => $audit['description'] ?? null,
                'savings_ms' => $audit['details']['overallSavingsMs'] ?? $audit['numericValue'] ?? null,
                'savings_bytes' => $audit['details']['overallSavingsBytes'] ?? null,
            ];
            if ($details['type'] === 'opportunity') {
                $entry['items_count'] = isset($details['items']) ? count($details['items']) : null;
                $entry['top_items'] = array_slice($details['items'] ?? [], 0, 3);
                $opportunities[] = $entry;
            } elseif ($details['type'] === 'diagnostic') {
                $diagnostics[] = $entry;
            }
        }

        $resourceSummary = Arr::get($audits, 'resource-summary.details.items.0', []);

        return [
            'lighthouse_version' => $lighthouse['lighthouseVersion'] ?? null,
            'fetch_time' => $lighthouse['fetchTime'] ?? null,
            'scores' => $scores,
            'lab_metrics' => $labMetrics,
            'field_metrics' => $fieldMetrics,
            'opportunities' => $opportunities,
            'diagnostics' => $diagnostics,
            'resources_summary' => [
                'total_bytes' => $resourceSummary['totalBytes'] ?? null,
                'js_bytes' => $resourceSummary['scriptBytes'] ?? null,
                'css_bytes' => $resourceSummary['stylesheetBytes'] ?? null,
                'img_bytes' => $resourceSummary['imageBytes'] ?? null,
                'font_bytes' => $resourceSummary['fontBytes'] ?? null,
                'third_party_count' => $resourceSummary['thirdPartyResources'] ?? null,
            ],
        ];
    }
}
