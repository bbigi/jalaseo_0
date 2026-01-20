@extends('layouts.dashboard')

@section('title', __('speed-insights.title') . ' - ' . $website->name)

@php
    $formatMs = function ($value) {
        if ($value === null) return '—';
        return number_format($value / 1000, 2) . 's';
    };
    $formatScore = fn ($value) => $value !== null ? number_format($value) : '—';
    $formatSaving = function ($item) use ($formatMs) {
        $ms = $item['savings_ms'] ?? null;
        if ($ms !== null && $ms > 10) { // >10ms to avoid “0.00s”
            return $formatMs($ms);
        }
        $bytes = $item['savings_bytes'] ?? null;
        if ($bytes && $bytes > 1024) {
            return $bytes > 1048576
                ? number_format($bytes / 1048576, 2) . ' MB'
                : number_format($bytes / 1024, 1) . ' KB';
        }
        return '—';
    };
    $savingType = function ($item) {
        $ms = $item['savings_ms'] ?? null;
        if ($ms !== null && $ms > 10) return 'TIME';
        $bytes = $item['savings_bytes'] ?? null;
        if ($bytes && $bytes > 0) return 'BYTES';
        return 'INFO';
    };
    $priorityLabel = function ($item) use ($savingType) {
        $type = $savingType($item);
        $ms = $item['savings_ms'] ?? 0;
        $bytes = $item['savings_bytes'] ?? 0;
        if ($type === 'TIME') {
            if ($ms >= 1000) return 'High';
            if ($ms >= 300) return 'Medium';
            return 'Low';
        }
        if ($type === 'BYTES') {
            if ($bytes >= 500 * 1024) return 'High';
            if ($bytes >= 100 * 1024) return 'Medium';
            return 'Low';
        }
        return 'Low';
    };
    $areaLabel = function ($item) {
        $id = $item['id'] ?? '';
        $title = strtolower($item['title'] ?? '');
        $frontendIds = ['unused-javascript', 'render-blocking-resources', 'dom-size'];
        $serverIds = ['server-response-time', 'redirects'];
        $assetIds = ['modern-image-formats', 'offscreen-images', 'uses-webp-images', 'uses-optimized-images', 'efficient-animated-content'];

        if (in_array($id, $frontendIds) || str_contains($title, 'javascript') || str_contains($title, 'css')) return 'Frontend';
        if (in_array($id, $serverIds) || str_contains($title, 'response time')) return 'Server';
        if (in_array($id, $assetIds) || str_contains($title, 'image')) return 'Assets';
        return 'General';
    };
    $badgeImpactClass = function ($value) {
        return match ($value) {
            'High' => 'bg-red-100 text-red-700',
            'Medium' => 'bg-amber-100 text-amber-700',
            default => 'bg-gray-100 text-gray-600',
        };
    };
    $badgeEffortClass = function ($value) {
        return match ($value) {
            'Low' => 'bg-emerald-100 text-emerald-700',
            'High' => 'bg-purple-100 text-purple-700',
            default => 'bg-blue-100 text-blue-700',
        };
    };
@endphp

@section('content')
<div class="p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ __('speed-insights.title') }}</p>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $website->name }}</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('website-analytics.index', $website) }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                ← {{ __('speed-insights.breadcrumb.back_analytics') }}
            </a>
            <a href="{{ url('/websites/'.$website->id) }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                ← {{ __('speed-insights.breadcrumb.back_website') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Run form (single, chooses strategy via dropdown) --}}
    <div class="bg-white rounded-xl p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('speed-insights.run.heading') }}</h2>
                <p class="text-sm text-gray-500">{{ __('speed-insights.run.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span class="px-2 py-1 rounded bg-gray-100">{{ __('speed-insights.run.using', ['strategy' => strtoupper($strategy)]) }}</span>
            </div>
        </div>
        <form action="{{ route('website-analytics.pagespeed.run', $website) }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <div class="md:col-span-3">
                <label class="text-xs text-gray-600 font-medium mb-1 block">{{ __('speed-insights.run.url_label') }}</label>
                <input type="url" name="url" value="{{ old('url', $url) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="{{ __('speed-insights.run.url_placeholder') }}">
                @error('url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-xs text-gray-600 font-medium mb-1 block">{{ __('speed-insights.run.strategy_label') }}</label>
                <select name="strategy" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="mobile" {{ $strategy === 'mobile' ? 'selected' : '' }}>{{ __('speed-insights.run.strategy_mobile') }}</option>
                    <option value="desktop" {{ $strategy === 'desktop' ? 'selected' : '' }}>{{ __('speed-insights.run.strategy_desktop') }}</option>
                </select>
                @error('strategy')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                    {{ __('speed-insights.run.submit') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Strategy tabs for viewing results only --}}
    <div class="flex items-center justify-between">
        <div class="flex bg-gray-100 rounded-lg text-sm overflow-hidden">
            <a href="{{ route('website-analytics.speed-insights', ['website' => $website, 'strategy' => 'mobile']) }}"
               class="px-3 py-2 {{ $strategy === 'mobile' ? 'bg-white text-blue-700 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                {{ __('speed-insights.tabs.mobile') }}
            </a>
            <a href="{{ route('website-analytics.speed-insights', ['website' => $website, 'strategy' => 'desktop']) }}"
               class="px-3 py-2 {{ $strategy === 'desktop' ? 'bg-white text-blue-700 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                {{ __('speed-insights.tabs.desktop') }}
            </a>
        </div>
        <span class="text-xs text-gray-500">{{ __('speed-insights.tabs.note') }}</span>
    </div>

    {{-- Latest run overview --}}
    <div class="bg-white rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('speed-insights.latest.title', ['strategy' => strtoupper($strategy)]) }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $latestRun ? $latestRun->url : __('speed-insights.latest.no_runs') }}
                </p>
                @if($latestRun && $latestRun->error_message)
                    <p class="text-xs text-red-600 mt-1">{{ __('speed-insights.latest.error_prefix') }} {{ $latestRun->error_message }}</p>
                @endif
            </div>
            @if($latestRun)
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    @if($latestRun->status === 'success') bg-green-50 text-green-700
                    @elseif($latestRun->status === 'running' || $latestRun->status === 'queued') bg-blue-50 text-blue-700
                    @else bg-red-50 text-red-700 @endif">
                    {{ ucfirst($latestRun->status) }}
                </span>
            @endif
        </div>

        @if(!$latestRun)
            <div class="text-center py-10 text-gray-500">{{ __('speed-insights.latest.no_runs') }}</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.metrics.performance') }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $formatScore($latestRun->scores['performance'] ?? null) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.metrics.lcp_lab') }}</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $formatMs($latestRun->lab_metrics['lcp_ms'] ?? null) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.metrics.inp_lab') }}</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $formatMs($latestRun->lab_metrics['inp_ms'] ?? null) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.metrics.cls_lab') }}</p>
                    <p class="text-xl font-semibold text-gray-900">
                        {{ isset($latestRun->lab_metrics['cls']) ? number_format($latestRun->lab_metrics['cls'], 3) : '—' }}
                    </p>
                </div>
            </div>

            {{-- Field data --}}
            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-900 mb-2">{{ __('speed-insights.latest.field_data') }}</h3>
                @if(!$latestRun->field_metrics)
                    <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">{{ __('speed-insights.latest.field_empty') }}</div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.field.lcp') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $formatMs($latestRun->field_metrics['lcp_p75_ms'] ?? null) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.field.inp') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $formatMs($latestRun->field_metrics['inp_p75_ms'] ?? null) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">{{ __('speed-insights.latest.field.cls') }}</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ isset($latestRun->field_metrics['cls_p75']) ? number_format($latestRun->field_metrics['cls_p75'], 3) : '—' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Opportunities (raw) --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-md font-semibold text-gray-900">{{ __('speed-insights.latest.opportunities.title') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('speed-insights.latest.opportunities.subtitle') }}</p>
                </div>
                @php
                    $opps = collect($latestRun->opportunities ?? [])->sort(function ($a, $b) {
                        $aMs = $a['savings_ms'] ?? 0;
                        $bMs = $b['savings_ms'] ?? 0;
                        $aBytes = $a['savings_bytes'] ?? 0;
                        $bBytes = $b['savings_bytes'] ?? 0;

                        $aType = $aMs > 10 ? 2 : ($aBytes > 0 ? 1 : 0); // 2=time,1=bytes,0=info
                        $bType = $bMs > 10 ? 2 : ($bBytes > 0 ? 1 : 0);

                        if ($aType !== $bType) return $bType <=> $aType; // type priority desc

                        $aVal = $aType === 2 ? $aMs : ($aType === 1 ? $aBytes : 0);
                        $bVal = $bType === 2 ? $bMs : ($bType === 1 ? $bBytes : 0);

                        return $bVal <=> $aVal; // value desc
                    })->take(8);
                @endphp
                @if($opps->isEmpty())
                    <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">{{ __('speed-insights.latest.opportunities.empty') }}</div>
                @else
                    <div class="overflow-hidden border border-gray-100 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('speed-insights.latest.opportunities.headers.item') }}</th>
                                    <th class="px-4 py-3 text-left w-32">{{ __('speed-insights.latest.opportunities.headers.savings') }}</th>
                                    <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.latest.opportunities.headers.priority') }}</th>
                                    <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.latest.opportunities.headers.area') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('speed-insights.latest.opportunities.headers.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($opps as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item['title'] ?? $item['id'] }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-900">{{ $formatSaving($item) }}</span>
                                                @php
                                                    $type = $savingType($item);
                                                    $typeLabel = [
                                                        'TIME' => __('speed-insights.latest.opportunities.badges.time'),
                                                        'BYTES' => __('speed-insights.latest.opportunities.badges.bytes'),
                                                        'INFO' => __('speed-insights.latest.opportunities.badges.info'),
                                                    ][$type] ?? $type;
                                                @endphp
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold
                                                    @if($type === 'TIME') bg-blue-100 text-blue-700
                                                    @elseif($type === 'BYTES') bg-purple-100 text-purple-700
                                                    @else bg-gray-100 text-gray-600 @endif">
                                                    {{ $typeLabel }}
                                                </span>
                                            </div>
                                        </td>
                                        @php
                                            $priority = $priorityLabel($item);
                                            $priorityLabelText = [
                                                'High' => __('speed-insights.latest.opportunities.badges.priority.high'),
                                                'Medium' => __('speed-insights.latest.opportunities.badges.priority.medium'),
                                                'Low' => __('speed-insights.latest.opportunities.badges.priority.low'),
                                            ][$priority] ?? $priority;
                                        @endphp
                                        <td class="px-4 py-3 text-gray-700">
                                            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold
                                                @if($priority === 'High') bg-red-100 text-red-700
                                                @elseif($priority === 'Medium') bg-amber-100 text-amber-700
                                                @else bg-gray-100 text-gray-600 @endif">
                                                {{ $priorityLabelText }}
                                            </span>
                                        </td>
                                        @php
                                            $area = $areaLabel($item);
                                            $areaMap = [
                                                'Frontend' => __('speed-insights.latest.opportunities.badges.areas.frontend'),
                                                'Server' => __('speed-insights.latest.opportunities.badges.areas.server'),
                                                'Assets' => __('speed-insights.latest.opportunities.badges.areas.assets'),
                                                'General' => __('speed-insights.latest.opportunities.badges.areas.general'),
                                            ];
                                            $areaText = $areaMap[$area] ?? $area;
                                        @endphp
                                        <td class="px-4 py-3 text-gray-700">
                                            <span class="text-xs text-gray-600">{{ $areaText }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $item['description'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        @endif
    </div>

    {{-- Insights card (separate from results per tab) --}}
    @if($latestRun && $insights)
        <div class="bg-white rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('speed-insights.insights.title', ['strategy' => strtoupper($strategy)]) }}</h3>
                    <p class="text-xs text-gray-500">{{ __('speed-insights.insights.subtitle') }}</p>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-2">{{ __('speed-insights.insights.summary_title') }}</h4>
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800">
                    {{ $insights['summary_text'] ?? '—' }}
                </div>
            </div>

            @if(!empty($insights['bottlenecks']))
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">{{ __('speed-insights.insights.bottlenecks_title') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($insights['bottlenecks'] as $item)
                            <div class="bg-white border border-gray-100 rounded-lg p-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ __('speed-insights.insights.impact') }}: {{ $item['impact'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($insights['recommendations']))
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-900">{{ __('speed-insights.insights.recommendations_title') }}</h4>
                        <p class="text-xs text-gray-500">{{ __('speed-insights.insights.recommendations_subtitle') }}</p>
                    </div>
                    <div class="space-y-3">
                        @foreach($insights['recommendations'] as $rec)
                            <div class="bg-white border border-gray-100 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-900">{{ $rec['title'] }}</p>
                                    @php $saving = $formatSaving($rec); @endphp
                                    @if($saving !== '—')
                                        @php $type = $savingType($rec); @endphp
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ __('speed-insights.insights.saved', ['value' => $saving]) }}</span>
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold
                                                @if($type === 'TIME') bg-blue-100 text-blue-700
                                                @elseif($type === 'BYTES') bg-purple-100 text-purple-700
                                                @else bg-gray-100 text-gray-600 @endif">
                                                {{ $type }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 mt-1">{{ __('speed-insights.insights.why') }}: {{ $rec['why'] }}</p>
                                @php
                                    $action = $rec['action'] ?? '';
                                    $generic = str_contains(strtolower($action), 'prioritize this item');
                                    $displayAction = $generic
                                        ? __('speed-insights.insights.generic_action')
                                        : $action;
                                @endphp
                                <p class="text-xs text-gray-600 mt-1">{{ __('speed-insights.insights.next') }}: {{ $displayAction }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($insights['action_plan']['items']))
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900">{{ __('speed-insights.insights.action_plan_title') }}</p>
                        <p class="text-xs text-gray-500">{{ __('speed-insights.insights.action_plan_subtitle') }}</p>
                    </div>
                    <div class="space-y-2">
                        @foreach($insights['action_plan']['items'] as $index => $item)
                            <div class="bg-white rounded-lg p-3 border border-gray-100">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $index + 1 }}. {{ $item['title'] }}</p>
                                        @php
                                            $rm = strtoupper($item['related_metric'] ?? '');
                                            $why = $item['why'] ?? null;
                                            $whyLower = strtolower($why ?? '');
                                            $mentionsInp = str_contains($whyLower, 'inp') || str_contains($whyLower, 'tbt') || str_contains($whyLower, 'interaction');
                                            $safeWhy = $why;
                                            if ($why && $mentionsInp && $rm && $rm !== 'INP') {
                                                $safeWhy = "Helps improve {$rm} by reducing main-thread and render overhead.";
                                            }
                                        @endphp
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ __('speed-insights.insights.why') }}: {{ $safeWhy ?? __('speed-insights.insights.default_why') }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ __('speed-insights.insights.next') }}: {{ $item['what_to_do'] ?? __('speed-insights.insights.default_next') }}</p>
                                        @if(!empty($item['related_metric']))
                                            <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                                {{ __('speed-insights.insights.related', ['metric' => strtoupper($item['related_metric'])]) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-1 text-xs">
                                        @php $saving = $formatSaving($item); $type = $savingType($item); @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $type === 'TIME' ? 'bg-blue-100 text-blue-700' : ($type === 'BYTES' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $type }}
                                            </span>
                                            <span class="text-gray-800 font-semibold">{{ $saving }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeImpactClass($item['impact'] ?? 'Low') }}">
                                                {{ $item['impact'] ?? 'Low' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeEffortClass($item['effort'] ?? 'Medium') }}">
                                                {{ __('speed-insights.insights.effort', ['effort' => $item['effort'] ?? 'Medium']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $areaTop = collect($insights['action_plan']['items'])->reduce(function ($carry, $item) use ($areaLabel) {
                        $area = $areaLabel($item);
                        if (!isset($carry[$area])) {
                            $carry[$area] = $item;
                        }
                        return $carry;
                    }, []);
                @endphp
                @if(!empty($areaTop))
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($areaTop as $area => $o)
                            <div class="bg-white border border-gray-100 rounded-lg p-3">
                                @php
                                    $areaMap = [
                                        'Frontend' => __('speed-insights.latest.opportunities.badges.areas.frontend'),
                                        'Server' => __('speed-insights.latest.opportunities.badges.areas.server'),
                                        'Assets' => __('speed-insights.latest.opportunities.badges.areas.assets'),
                                        'General' => __('speed-insights.latest.opportunities.badges.areas.general'),
                                    ];
                                    $areaTopLabel = $areaMap[$area] ?? $area;
                                @endphp
                                <p class="text-xs uppercase text-gray-500">{{ $areaTopLabel }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $o['title'] ?? '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if($comparisonInsight)
                @php
                    $deltaParts = [];
                    if(!is_null($comparisonInsight['performance_delta'])) {
                        $deltaParts[] = 'ΔPerf ' . number_format($comparisonInsight['performance_delta']) . ' pts';
                    }
                    if(!is_null($comparisonInsight['lcp_delta_ms'])) {
                        $deltaParts[] = 'ΔLCP ' . $formatMs($comparisonInsight['lcp_delta_ms']);
                    }
                    if(!is_null($comparisonInsight['inp_delta_ms'])) {
                        $deltaParts[] = 'ΔINP ' . $formatMs($comparisonInsight['inp_delta_ms']);
                    }
                    if(isset($comparisonInsight['cls_delta'])) {
                        $deltaParts[] = 'ΔCLS ' . number_format($comparisonInsight['cls_delta'], 3);
                    }
                    if(empty($deltaParts)) {
                        $deltaParts[] = '—';
                    }
                    $filterFixList = function($arr) {
                        return collect($arr ?? [])->reject(function($x) {
                            $t = strtolower(trim($x));
                            return str_contains($t, 'was short')
                                || str_contains($t, 'no issue')
                                || str_contains($t, 'passed')
                                || str_contains($t, 'is fast')
                                || str_contains($t, 'is good');
                        })->values()->all();
                    };
                    $sameFixes = $filterFixList($comparisonInsight['overlap']['same'] ?? []);
                    $mobileOnlyFixes = $filterFixList($comparisonInsight['overlap']['mobile_only'] ?? []);
                    $desktopOnlyFixes = $filterFixList($comparisonInsight['overlap']['desktop_only'] ?? []);
                @endphp
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">{{ __('speed-insights.insights.comparison_title') }}</h4>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-900">
                                @if($comparisonInsight['winner_strategy'])
                                    {{ __('speed-insights.insights.comparison_lead', ['strategy' => ucfirst($comparisonInsight['winner_strategy'])]) }}
                                @else
                                    {{ __('speed-insights.insights.comparison_tie') }}
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ implode(' · ', $deltaParts) }}
                            </span>
                        </div>
                        @if(!empty($comparisonInsight['insight']))
                            <p>{{ $comparisonInsight['insight'] }}</p>
                        @endif
                        @if(!empty($comparisonInsight['reasons']))
                            <ul class="list-disc list-inside text-gray-700 space-y-1">
                                @foreach($comparisonInsight['reasons'] as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="text-xs text-gray-600">{{ __('speed-insights.insights.main_driver', ['driver' => $comparisonInsight['main_driver'] ?? '—']) }}</p>
                        @if(!empty($comparisonInsight['overlap']))
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs text-gray-700">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('speed-insights.insights.same_fixes') }}</p>
                                    <ul class="list-disc list-inside">
                                        @forelse($sameFixes as $item)
                                            <li>{{ $item }}</li>
                                        @empty
                                            <li>—</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('speed-insights.insights.mobile_only') }}</p>
                                    <ul class="list-disc list-inside">
                                        @forelse($mobileOnlyFixes as $item)
                                            <li>{{ $item }}</li>
                                        @empty
                                            <li>—</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ __('speed-insights.insights.desktop_only') }}</p>
                                    <ul class="list-disc list-inside">
                                        @forelse($desktopOnlyFixes as $item)
                                            <li>{{ $item }}</li>
                                        @empty
                                            <li>—</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($trend))
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">{{ __('speed-insights.insights.trend_title', ['strategy' => ucfirst($strategy)]) }}</h4>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 space-y-2">
                        <p class="font-semibold text-gray-900">{{ __('speed-insights.insights.trend_delta', ['label' => ucfirst($trend['label']), 'delta' => number_format($trend['delta'])]) }}</p>
                        <p>{{ $trend['message'] }}</p>
                        <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                            @foreach($trend['series'] ?? [] as $point)
                                <span class="px-2 py-1 rounded bg-white border border-gray-100">{{ __('speed-insights.insights.trend_series', ['date' => $point['date'] ?? '', 'score' => $point['score'] ?? '—']) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- History card (outside tabs/results) --}}
    <div class="bg-white rounded-xl p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-md font-semibold text-gray-900">{{ __('speed-insights.history.title') }}</h3>
            <p class="text-xs text-gray-500">{{ __('speed-insights.history.subtitle', ['count' => count($history)]) }}</p>
        </div>
        @if($history->isEmpty())
            <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">{{ __('speed-insights.history.empty') }}</div>
        @else
            <div class="overflow-hidden border border-gray-100 rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('speed-insights.history.headers.url') }}</th>
                        <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.history.headers.strategy') }}</th>
                        <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.history.headers.perf') }}</th>
                        <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.history.headers.lcp') }}</th>
                        <th class="px-4 py-3 text-left w-32">{{ __('speed-insights.history.headers.status') }}</th>
                        <th class="px-4 py-3 text-left w-32">{{ __('speed-insights.history.headers.when') }}</th>
                        <th class="px-4 py-3 text-left w-24">{{ __('speed-insights.history.headers.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($history as $run)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900 truncate" title="{{ $run->url }}">{{ $run->url }}</td>
                                <td class="px-4 py-3 text-gray-600 capitalize">{{ $run->strategy }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $formatScore($run->scores['performance'] ?? null) }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $formatMs($run->lab_metrics['lcp_ms'] ?? null) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($run->status === 'success') bg-green-50 text-green-700
                                        @elseif($run->status === 'running' || $run->status === 'queued') bg-blue-50 text-blue-700
                                        @else bg-red-50 text-red-700 @endif">
                                        {{ ucfirst($run->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $run->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('website-analytics.pagespeed.destroy', ['website' => $website, 'run' => $run]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="strategy" value="{{ $strategy }}">
                                        <button type="submit"
                                                onclick="return confirm('{{ __('speed-insights.history.delete_confirm') }}');"
                                                class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition">
                                            {{ __('speed-insights.history.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
