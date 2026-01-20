<?php

namespace App\Services;

use App\Models\PageSpeedRun;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PageSpeedInterpreter
{
    protected function t(string $key, array $params = [], ?string $fallback = null): string
    {
        $value = __($key, $params);
        if ($value === $key && $fallback !== null) {
            return $fallback;
        }
        return $value;
    }

    public function build(PageSpeedRun $run): array
    {
        $score = Arr::get($run->scores, 'performance');
        $lcp = Arr::get($run->lab_metrics, 'lcp_ms');
        $inp = Arr::get($run->lab_metrics, 'inp_ms');
        $cls = Arr::get($run->lab_metrics, 'cls');
        $opportunities = $run->opportunities ?? [];

        $rating = $this->rateScore($score);
        $summary = $this->summaryText($rating, $score, $lcp, $inp, $cls);
        $bottlenecks = $this->bottlenecks($lcp, $inp, $cls);
        $recommendations = $this->recommendations($opportunities);
        $actionPlan = $this->actionPlan($opportunities);

        return [
            'summary_text' => $summary,
            'bottlenecks' => $bottlenecks,
            'recommendations' => $recommendations,
            'action_plan' => $actionPlan,
        ];
    }

    public function compare(?PageSpeedRun $current, ?PageSpeedRun $other): ?array
    {
        if (!$current || !$other) {
            return null;
        }

        $diff = function ($a, $b) {
            if ($a === null || $b === null) {
                return null;
            }
            return $a - $b;
        };

        $performanceDelta = $diff(Arr::get($current->scores, 'performance'), Arr::get($other->scores, 'performance'));
        $lcpDelta = $diff(Arr::get($current->lab_metrics, 'lcp_ms'), Arr::get($other->lab_metrics, 'lcp_ms'));
        $inpDelta = $diff(Arr::get($current->lab_metrics, 'inp_ms'), Arr::get($other->lab_metrics, 'inp_ms'));
        $clsDelta = $diff(Arr::get($current->lab_metrics, 'cls'), Arr::get($other->lab_metrics, 'cls'));

        // Determine main driver gap
        $drivers = [
            'LCP' => $lcpDelta !== null ? abs($lcpDelta) : 0,
            'INP' => $inpDelta !== null ? abs($inpDelta) : 0,
            'CLS' => $clsDelta !== null ? abs($clsDelta) : 0,
        ];
        arsort($drivers);
        $mainDriver = array_key_first($drivers);

        // Identify winner strategy by performance score
        $winner = null;
        if ($performanceDelta !== null) {
            $winner = $performanceDelta > 0 ? $current->strategy : ($performanceDelta < 0 ? $other->strategy : null);
        }

        // Build reasons
        $reasons = [];
        $nameA = ucfirst($current->strategy);
        $nameB = ucfirst($other->strategy);

        if ($lcpDelta !== null && abs($lcpDelta) > 300) {
            $slow = $lcpDelta > 0 ? $nameA : $nameB;
            $fast = $lcpDelta > 0 ? $nameB : $nameA;
            $reasons[] = $this->t('speed-insights.interpreter.compare.reasons.lcp', [
                'slow' => $slow,
                'fast' => $fast,
                'delta' => $this->formatMs(abs($lcpDelta)),
            ]);
        }
        if ($inpDelta !== null && abs($inpDelta) > 200) {
            $slow = $inpDelta > 0 ? $nameA : $nameB;
            $fast = $inpDelta > 0 ? $nameB : $nameA;
            $reasons[] = $this->t('speed-insights.interpreter.compare.reasons.inp', [
                'slow' => $slow,
                'fast' => $fast,
                'delta' => $this->formatMs(abs($inpDelta)),
            ]);
        }
        if ($clsDelta !== null && abs($clsDelta) > 0.02) {
            $slow = $clsDelta > 0 ? $nameA : $nameB;
            $fast = $clsDelta > 0 ? $nameB : $nameA;
            $reasons[] = $this->t('speed-insights.interpreter.compare.reasons.cls', [
                'slow' => $slow,
                'fast' => $fast,
                'delta' => number_format(abs($clsDelta), 3),
            ]);
        }

        $insight = null;
        if ($performanceDelta !== null) {
            if ($performanceDelta > 5) {
                $insight = $this->t('speed-insights.interpreter.compare.insight.better', [
                    'better' => $nameA,
                    'worse' => $nameB,
                    'delta' => number_format($performanceDelta),
                ]);
            } elseif ($performanceDelta < -5) {
                $insight = $this->t('speed-insights.interpreter.compare.insight.worse', [
                    'worse' => $nameA,
                    'better' => $nameB,
                    'delta' => number_format(abs($performanceDelta)),
                ]);
            }
        }

        // Recommendation overlap
        $overlap = $this->overlap(
            collect($current->opportunities ?? []),
            collect($other->opportunities ?? []),
            $current->strategy,
            $other->strategy
        );

        return [
            'winner_strategy' => $winner,
            'performance_delta' => $performanceDelta,
            'lcp_delta_ms' => $lcpDelta,
            'inp_delta_ms' => $inpDelta,
            'cls_delta' => $clsDelta,
            'main_driver' => $mainDriver,
            'insight' => $insight,
            'reasons' => $reasons,
            'overlap' => $overlap,
        ];
    }

    protected function rateScore(?float $score): string
    {
        if ($score === null) {
            return 'unknown';
        }
        if ($score >= 90) return 'excellent';
        if ($score >= 70) return 'good';
        return 'needs improvement';
    }

    protected function summaryText(string $rating, ?float $score, ?float $lcp, ?float $inp, ?float $cls): string
    {
        $parts = [];
        if ($score !== null) {
            $parts[] = $this->t('speed-insights.interpreter.summary.rating.' . $rating, [
                'rating' => ucfirst($rating),
                'score' => number_format($score),
            ], ucfirst($rating) . ' (' . number_format($score) . ').');
        }

        $lcpText = $this->describeLcp($lcp);
        if ($lcpText) $parts[] = 'LCP ' . $lcpText;

        $inpText = $this->describeInp($inp);
        if ($inpText) $parts[] = 'INP ' . $inpText;

        $clsText = $this->describeCls($cls);
        if ($clsText) $parts[] = 'CLS ' . $clsText;

        // Add clear focus guidance if LCP is slow
        if ($lcp !== null && $lcp > 4000) {
            $parts[] = $this->t(
                'speed-insights.interpreter.summary.focus_lcp',
                [],
                'Focus: fix hero image size/format and server response before other tweaks.'
            );
        }

        return implode(' ', $parts);
    }

    protected function describeLcp(?float $lcpMs): ?string
    {
        if ($lcpMs === null) return null;
        if ($lcpMs <= 2500) return $this->t('speed-insights.interpreter.summary.lcp.fast', ['value' => $this->formatMs($lcpMs)], 'is fast (' . $this->formatMs($lcpMs) . ').');
        if ($lcpMs <= 4000) return $this->t('speed-insights.interpreter.summary.lcp.moderate', ['value' => $this->formatMs($lcpMs)], 'is moderate (' . $this->formatMs($lcpMs) . '); improving hero content load can boost UX.');
        return $this->t('speed-insights.interpreter.summary.lcp.slow', ['value' => $this->formatMs($lcpMs)], 'is slow (' . $this->formatMs($lcpMs) . '); prioritize hero image/server response.');
    }

    protected function describeInp(?float $inpMs): ?string
    {
        if ($inpMs === null) return null;
        if ($inpMs <= 200) return $this->t('speed-insights.interpreter.summary.inp.fast', ['value' => $this->formatMs($inpMs)], 'is responsive (' . $this->formatMs($inpMs) . ').');
        if ($inpMs <= 500) return $this->t('speed-insights.interpreter.summary.inp.moderate', ['value' => $this->formatMs($inpMs)], 'is borderline (' . $this->formatMs($inpMs) . '); reduce long tasks.');
        return $this->t('speed-insights.interpreter.summary.inp.slow', ['value' => $this->formatMs($inpMs)], 'is sluggish (' . $this->formatMs($inpMs) . '); trim JS and long tasks.');
    }

    protected function describeCls(?float $cls): ?string
    {
        if ($cls === null) return null;
        if ($cls <= 0.1) return $this->t('speed-insights.interpreter.summary.cls.stable', ['value' => number_format($cls, 3)], 'is stable (' . number_format($cls, 3) . ').');
        if ($cls <= 0.25) return $this->t('speed-insights.interpreter.summary.cls.moderate', ['value' => number_format($cls, 3)], 'shows some shifts (' . number_format($cls, 3) . '); reserve space for images/ads.');
        return $this->t('speed-insights.interpreter.summary.cls.unstable', ['value' => number_format($cls, 3)], 'is unstable (' . number_format($cls, 3) . '); fix layout shifts.');
    }

    protected function bottlenecks(?float $lcp, ?float $inp, ?float $cls): array
    {
        $candidates = [];

        $push = function (string $id, string $label, string $impact) use (&$candidates) {
            $candidates[] = ['id' => $id, 'title' => $label, 'impact' => $impact];
        };

        if ($lcp !== null && $lcp > 2500) {
            $push(
                'lcp',
                $this->t('speed-insights.interpreter.bottlenecks.lcp.title', [], 'LCP too slow'),
                $this->t('speed-insights.interpreter.bottlenecks.lcp.desc', [], 'Largest Contentful Paint dominates user perception; optimize hero image/server/critical CSS.')
            );
        }
        if ($inp !== null && $inp > 200) {
            $push(
                'inp',
                $this->t('speed-insights.interpreter.bottlenecks.inp.title', [], 'INP latency'),
                $this->t('speed-insights.interpreter.bottlenecks.inp.desc', [], 'Long tasks or heavy JS delay interactions; split tasks and trim JS.')
            );
        }
        if ($cls !== null && $cls > 0.1) {
            $push(
                'cls',
                $this->t('speed-insights.interpreter.bottlenecks.cls.title', [], 'Layout shifts'),
                $this->t('speed-insights.interpreter.bottlenecks.cls.desc', [], 'Unreserved media/ads cause layout jumps; add width/height and preload fonts.')
            );
        }

        return array_slice($candidates, 0, 2);
    }

    protected function recommendations(array $opportunities): array
    {
        $sorted = collect($opportunities)
            ->filter(function ($item) {
                return ($item['savings_ms'] ?? 0) > 0 || ($item['savings_bytes'] ?? 0) > 0;
            })
            ->sort(function ($a, $b) {
                $ams = $a['savings_ms'] ?? 0;
                $bms = $b['savings_ms'] ?? 0;
                if ($ams !== $bms) {
                    return $bms <=> $ams;
                }
                $abytes = $a['savings_bytes'] ?? 0;
                $bbytes = $b['savings_bytes'] ?? 0;
                return $bbytes <=> $abytes;
            })
            ->take(3)
            ->values();

        return $sorted->map(function ($item) {
            $id = $item['id'] ?? '';
            return [
                'title' => $item['title'] ?? $id,
                'savings_ms' => $item['savings_ms'] ?? null,
                'savings_bytes' => $item['savings_bytes'] ?? null,
                'why' => $this->why($id),
                'action' => $this->action($id),
            ];
        })->all();
    }

    protected function actionPlan(array $opportunities): array
    {
        $sorted = collect($opportunities)->sort(function ($a, $b) {
            $ams = $a['savings_ms'] ?? 0;
            $bms = $b['savings_ms'] ?? 0;
            $atype = $ams >= 10 ? 2 : (($a['savings_bytes'] ?? 0) > 0 ? 1 : 0);
            $btype = $bms >= 10 ? 2 : (($b['savings_bytes'] ?? 0) > 0 ? 1 : 0);
            if ($atype !== $btype) return $btype <=> $atype;
            $aVal = $atype === 2 ? $ams : ($a['savings_bytes'] ?? 0);
            $bVal = $btype === 2 ? $bms : ($b['savings_bytes'] ?? 0);
            return $bVal <=> $aVal;
        })->take(5)->values();

        return [
            'items' => $sorted->map(function ($item) {
                $id = $item['id'] ?? '';
                return [
                    'title' => $item['title'] ?? $id,
                    'impact' => $this->impactLabel($item),
                    'effort' => $this->effortLabel($id),
                    'why' => $this->why($id),
                    'what_to_do' => $this->action($id),
                    'savings_ms' => $item['savings_ms'] ?? null,
                    'savings_bytes' => $item['savings_bytes'] ?? null,
                    'related_metric' => $this->relatedMetric($id),
                    'source_audit_key' => $id,
                ];
            })->all(),
        ];
    }

    protected function impactLabel(array $item): string
    {
        $ms = $item['savings_ms'] ?? 0;
        $bytes = $item['savings_bytes'] ?? 0;
        $id = $item['id'] ?? '';

        $highIds = ['server-response-time', 'unused-javascript', 'render-blocking-resources', 'largest-contentful-paint'];
        if ($ms >= 300 || $bytes >= 250 * 1024 || in_array($id, $highIds, true)) {
            return 'High';
        }
        if ($ms >= 100 || $bytes >= 100 * 1024) {
            return 'Medium';
        }
        return 'Low';
    }

    protected function effortLabel(string $id): string
    {
        $low = ['uses-long-cache-ttl', 'uses-optimized-images', 'uses-webp-images', 'offscreen-images', 'uses-responsive-images', 'efficient-animated-content', 'preconnect'];
        $high = ['server-response-time', 'redirects'];
        if (in_array($id, $low, true)) {
            return 'Low';
        }
        if (in_array($id, $high, true)) {
            return 'High';
        }
        return 'Medium';
    }

    protected function relatedMetric(string $id): ?string
    {
        return match ($id) {
            'unused-javascript' => 'INP',
            'render-blocking-resources', 'largest-contentful-paint', 'unused-css-rules', 'unminified-css', 'unminified-javascript' => 'LCP',
            'server-response-time', 'redirects' => 'TTFB',
            default => null,
        };
    }

    protected function overlap(Collection $a, Collection $b, string $strategyA, string $strategyB): array
    {
        $map = function (\Illuminate\Support\Collection $c) {
            return $c->filter(function ($item) {
                return ($item['savings_ms'] ?? 0) > 0 || ($item['savings_bytes'] ?? 0) > 0;
            })->mapWithKeys(function ($item) {
                $id = $item['id'] ?? null;
                if (!$id) return [];
                return [$id => $item['title'] ?? $id];
            });
        };

        $mapA = $map($a);
        $mapB = $map($b);

        $same = array_intersect_key($mapA->all(), $mapB->all());
        $onlyA = array_diff_key($mapA->all(), $mapB->all());
        $onlyB = array_diff_key($mapB->all(), $mapA->all());

        return [
            'same' => array_values($same),
            $strategyA . '_only' => array_values($onlyA),
            $strategyB . '_only' => array_values($onlyB),
        ];
    }

    protected function why(string $id): string
    {
        return match ($id) {
            'unused-javascript' => $this->t('speed-insights.interpreter.why.unused-javascript'),
            'render-blocking-resources' => $this->t('speed-insights.interpreter.why.render-blocking-resources'),
            'unused-css-rules' => $this->t('speed-insights.interpreter.why.unused-css-rules'),
            'unminified-javascript' => $this->t('speed-insights.interpreter.why.unminified-javascript'),
            'unminified-css' => $this->t('speed-insights.interpreter.why.unminified-css'),
            'uses-responsive-images' => $this->t('speed-insights.interpreter.why.uses-responsive-images'),
            'uses-optimized-images' => $this->t('speed-insights.interpreter.why.uses-optimized-images'),
            'uses-text-compression' => $this->t('speed-insights.interpreter.why.uses-text-compression'),
            'server-response-time' => $this->t('speed-insights.interpreter.why.server-response-time'),
            'redirects' => $this->t('speed-insights.interpreter.why.redirects'),
            'largest-contentful-paint' => $this->t('speed-insights.interpreter.why.largest-contentful-paint'),
            default => $this->t('speed-insights.interpreter.why.default'),
        };
    }

    protected function action(string $id): string
    {
        return match ($id) {
            'unused-javascript' => $this->t('speed-insights.interpreter.action.unused-javascript'),
            'render-blocking-resources' => $this->t('speed-insights.interpreter.action.render-blocking-resources'),
            'unused-css-rules' => $this->t('speed-insights.interpreter.action.unused-css-rules'),
            'unminified-javascript' => $this->t('speed-insights.interpreter.action.unminified-javascript'),
            'unminified-css' => $this->t('speed-insights.interpreter.action.unminified-css'),
            'uses-responsive-images' => $this->t('speed-insights.interpreter.action.uses-responsive-images'),
            'uses-optimized-images' => $this->t('speed-insights.interpreter.action.uses-optimized-images'),
            'uses-text-compression' => $this->t('speed-insights.interpreter.action.uses-text-compression'),
            'server-response-time' => $this->t('speed-insights.interpreter.action.server-response-time'),
            'redirects' => $this->t('speed-insights.interpreter.action.redirects'),
            'largest-contentful-paint' => $this->t('speed-insights.interpreter.action.largest-contentful-paint'),
            default => $this->t('speed-insights.interpreter.action.default'),
        };
    }

    protected function formatMs(float $ms): string
    {
        return number_format($ms / 1000, 2) . 's';
    }

    public function trend(Collection $runs): ?array
    {
        if ($runs->count() < 2) {
            return null;
        }

        $sorted = $runs->sortBy('created_at')->values();
        $slice = $sorted->take(-8);

        $first = Arr::get($slice->first()?->scores, 'performance');
        $last = Arr::get($slice->last()?->scores, 'performance');

        if ($first === null || $last === null) {
            return null;
        }

        $delta = $last - $first;
        $label = 'stable';
        if ($delta >= 5) {
            $label = 'improving';
        } elseif ($delta <= -5) {
            $label = 'declining';
        }

        return [
            'label' => $label,
            'delta' => $delta,
            'message' => match ($label) {
                'improving' => $this->t('speed-insights.interpreter.trend.improving', ['delta' => number_format($delta)]),
                'declining' => $this->t('speed-insights.interpreter.trend.declining', ['delta' => number_format(abs($delta))]),
                default => $this->t('speed-insights.interpreter.trend.stable', ['delta' => number_format(abs($delta))]),
            },
            'series' => $slice->map(fn (PageSpeedRun $run) => [
                'date' => $run->created_at?->format('M j, H:i'),
                'score' => Arr::get($run->scores, 'performance'),
            ])->all(),
        ];
    }
}
