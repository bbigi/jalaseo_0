@extends('layouts.dashboard')

@section('title', __('competitors.show.title', ['domain' => $competitor->domain]))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $competitor->domain }}</h1>
            <p class="text-gray-600 mt-1">
                {{ __('competitors.show.comparing', ['website' => $competitor->website->name ?? '-']) }}
                @if ($competitor->last_analyzed_at)
                    • {{ __('competitors.show.last_analyzed', ['time' => $competitor->last_analyzed_at->diffForHumans()]) }}
                @endif
            </p>
        </div>
        <a href="{{ route('competitors.index') }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            ← {{ __('competitors.show.back') }}
        </a>
    </div>

    @if (!$competitor->last_analyzed_at)
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
            {{ __('competitors.show.not_analyzed') }}
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4">
                <div class="text-sm text-gray-500">{{ __('competitors.show.stats.total_keywords') }}</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($competitor->total_keywords ?? 0) }}</div>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="text-sm text-gray-500">{{ __('competitors.show.stats.traffic_estimate') }}</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($competitor->traffic_estimate ?? 0) }}</div>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="text-sm text-gray-500">{{ __('competitors.show.stats.average_rank') }}</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($competitor->avg_rank ?? 0, 1) }}</div>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="text-sm text-gray-500">{{ __('competitors.show.stats.market_share') }}</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($competitor->market_share ?? 0, 1) }}%</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('competitors.show.intent_distribution') }}</h3>
                @forelse (($competitor->intent_distribution ?? []) as $intent => $percent)
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="capitalize text-gray-600">{{ $intent }}</span>
                        <span class="font-medium text-gray-900">{{ $percent }}%</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">{{ __('competitors.show.no_data') }}</div>
                @endforelse
            </div>
            <div class="bg-white rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('competitors.show.keyword_type') }}</h3>
                @forelse (($competitor->keyword_type_distribution ?? []) as $type => $percent)
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="capitalize text-gray-600">{{ $type }}</span>
                        <span class="font-medium text-gray-900">{{ $percent }}%</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">{{ __('competitors.show.no_data') }}</div>
                @endforelse
            </div>
            <div class="bg-white rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('competitors.show.content_format') }}</h3>
                @forelse (($competitor->content_format_distribution ?? []) as $format => $percent)
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="capitalize text-gray-600">{{ str_replace('-', ' ', $format) }}</span>
                        <span class="font-medium text-gray-900">{{ $percent }}%</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">{{ __('competitors.show.no_data') }}</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg mb-6">
            <div class="px-4 py-3 text-sm font-semibold text-gray-900">{{ __('competitors.show.top_keywords') }}</div>
            <div class="px-4 pb-4">
                @if (!empty($competitor->top_keywords))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @foreach ($competitor->top_keywords as $kw)
                            <div class="flex items-center justify-between bg-gray-50 rounded p-3">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $kw['keyword'] ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ __('competitors.show.intent_label') }}: {{ $kw['intent'] ?? '-' }}</div>
                                </div>
                                <div class="text-right text-gray-700">
                                    {{ number_format($kw['volume'] ?? 0) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-500">{{ __('competitors.show.no_data') }}</div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg">
            <div class="px-4 py-3 text-sm font-semibold text-gray-900">{{ __('competitors.show.keyword_list') }}</div>

            <form method="GET" class="px-4 pb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.show.filters.intent') }}</label>
                        <select name="intent" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">{{ __('competitors.show.filters.all') }}</option>
                            @foreach(($filterOptions['intents'] ?? []) as $opt)
                                <option value="{{ $opt }}" {{ request('intent') === $opt ? 'selected' : '' }}>
                                    {{ ucfirst($opt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.show.filters.type') }}</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">{{ __('competitors.show.filters.all') }}</option>
                            @foreach(($filterOptions['types'] ?? []) as $opt)
                                <option value="{{ $opt }}" {{ request('type') === $opt ? 'selected' : '' }}>
                                    {{ ucfirst($opt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.show.filters.format') }}</label>
                        <select name="format" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">{{ __('competitors.show.filters.all') }}</option>
                            @foreach(($filterOptions['formats'] ?? []) as $opt)
                                <option value="{{ $opt }}" {{ request('format') === $opt ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('-', ' ', $opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.show.filters.gsc_query') }}</label>
                        <select name="gsc_query" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">{{ __('competitors.show.filters.all') }}</option>
                            <option value="yes" {{ request('gsc_query') === 'yes' ? 'selected' : '' }}>{{ __('competitors.show.filters.yes') }}</option>
                            <option value="no" {{ request('gsc_query') === 'no' ? 'selected' : '' }}>{{ __('competitors.show.filters.no') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3">
                    <div class="text-xs text-gray-500">
                        {{ __('competitors.show.filters.showing', ['count' => $keywords->count(), 'total' => $keywords->total()]) }}
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('competitors.show', $competitor) }}" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                            {{ __('competitors.show.filters.reset') }}
                        </a>
                        <button type="submit" class="px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            {{ __('competitors.show.filters.apply') }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.keyword') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('competitors.show.table.volume') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.intent') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.type') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.format') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.gsc_query') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('competitors.show.table.tracking') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('competitors.show.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($keywords as $keyword)
                            @php
                                $tracked = $trackedKeywords[$keyword->keyword] ?? null;
                                $gsc = $gscQueriedKeywords[$keyword->keyword] ?? null;
                            @endphp
                            <tr>
                                <td class="px-4 py-2">{{ $keyword->keyword }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($keyword->search_volume ?? 0) }}</td>
                                <td class="px-4 py-2">{{ $keyword->intent ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $keyword->keyword_type ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $keyword->content_format ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if($gsc)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">
                                            {{ __('competitors.show.table.yes') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                            {{ __('competitors.show.table.no') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if($tracked)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                            {{ __('competitors.show.table.tracked') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                            {{ __('competitors.show.table.untracked') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <div class="inline-flex items-center gap-2 justify-end">
                                        <button type="button"
                                                class="p-1 bg-gray-100 text-blue-600 rounded hover:text-blue-700 cursor-pointer"
                                                onclick="openTargetKeywordModal({
                                                    keyword: @js($keyword->keyword),
                                                    volume: {{ (int) ($keyword->search_volume ?? 0) }},
                                                    intent: @js($keyword->intent),
                                                    competition_index: {{ (int) ($keyword->competition ?? 0) }},
                                                    gsc_avg_position: {{ $gsc ? (float) ($gsc->avg_position ?? 0) : 0 }}
                                                }, {{ (int) $competitor->website_id }}, @js($competitor->website->name ?? ''))"
                                                title="{{ __('competitors.show.table.save_track') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                            </svg>
                                        </button>
                                        <a class="inline-flex p-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                           href="{{ route('generate.index', ['source' => 'competitor', 'keyword' => $keyword->keyword, 'title' => $keyword->keyword, 'website_id' => $competitor->website_id]) }}"
                                           title="{{ __('competitors.show.table.generate') }}">
                                            <x-heroicon-o-sparkles class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($keywords->isEmpty())
                    <div class="p-4 text-sm text-gray-500">{{ __('competitors.show.table.empty') }}</div>
                @endif
            </div>

            <div class="px-4 py-4">
                {{ $keywords->links() }}
            </div>
        </div>

        @if($competitor->is_primary)
            <div class="mt-8">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-800">Advanced Analysis</h3>
                        <p class="text-sm text-blue-700">{{ __('competitors.show.advanced.subtitle') }}</p>
                    </div>
                    <button id="runAdvancedBtn"
                            data-url="{{ route('competitors.advanced.run', $competitor) }}"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        {{ __('competitors.show.advanced.run') }}
                    </button>
                </div>
                <div id="advancedStatus" class="mt-2 text-sm text-gray-700 hidden"></div>

                @if(isset($latestAdvancedSession) && $latestAdvancedSession->status === 'completed')
                    @php
                        $metrics = $latestAdvancedSession->summary_metrics ?? [];
                        $dist = $metrics['rank_distribution'] ?? [];
                    @endphp
                    <div class="mt-4 bg-white rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500">{{ __('competitors.show.advanced.metrics.total_ranked') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['count'] ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('competitors.show.advanced.metrics.etv') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['etv'] ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('competitors.show.advanced.metrics.rank1') }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $dist['pos_1'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('competitors.show.advanced.metrics.rank2_3') }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $dist['pos_2_3'] ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b text-gray-600">
                                    <tr>
                                        <th class="px-4 py-2 text-left">{{ __('competitors.show.advanced.table.keyword') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('competitors.show.advanced.table.volume') }}</th>
                                        <th class="px-4 py-2 text-center">{{ __('competitors.show.advanced.table.position') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('competitors.show.advanced.table.url') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('competitors.show.advanced.table.etv') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('competitors.show.advanced.table.gsc') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('competitors.show.advanced.table.tracking') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('competitors.show.advanced.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($rankedKeywords as $rk)
                                        @php
                                            $tracked = $trackedKeywords[$rk->keyword] ?? null;
                                            $gsc = $gscQueriedKeywords[$rk->keyword] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2">{{ $rk->keyword }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($rk->search_volume ?? 0) }}</td>
                                            <td class="px-4 py-2 text-center">
                                                @if($rk->rank_group)
                                                    <span class="inline-block px-2 py-1 bg-green-50 text-green-700 rounded text-xs">#{{ $rk->rank_group }}</span>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                @if($rk->url)
                                                    <a href="{{ Str::startsWith($rk->url, ['http://', 'https://']) ? $rk->url : 'https://' . ltrim($rk->url, '/') }}"
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-800 hover:underline break-all">
                                                        {{ Str::limit($rk->url, 50) }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right">{{ number_format($rk->etv ?? 0) }}</td>
                                            <td class="px-4 py-2">
                                                @if($gsc)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">
                                                        {{ __('competitors.show.advanced.table.gsc_badge') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                                        —
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                @if($tracked)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                                        {{ __('competitors.show.advanced.table.tracked') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                                        {{ __('competitors.show.advanced.table.untracked') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <div class="inline-flex items-center gap-2 justify-end">
                                                    <button type="button"
                                                            class="p-1 bg-gray-100 text-blue-600 rounded hover:text-blue-700 cursor-pointer"
                                                            onclick="openTargetKeywordModal({
                                                                keyword: @js($rk->keyword),
                                                                volume: {{ (int) ($rk->search_volume ?? 0) }},
                                                                intent: null,
                                                                competition_index: {{ (int) ($rk->competition_level ?? 0) }},
                                                                gsc_avg_position: {{ $gsc ? (float) ($gsc->avg_position ?? 0) : 0 }}
                                                           }, {{ (int) $competitor->website_id }}, @js($competitor->website->name ?? ''))"
                                                            title="{{ __('competitors.show.table.save_track') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                                        </svg>
                                                    </button>
                                                    <div class="relative inline-block" x-data="{ open: false }">
                                                        <button type="button"
                                                                @click="open = !open"
                                                                class="p-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                                                title="{{ __('competitors.show.table.generate') }}">
                                                            <x-heroicon-o-sparkles class="w-4 h-4" />
                                                        </button>
                                                        <div x-show="open"
                                                             @click.away="open = false"
                                                             x-transition
                                                             class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                                            <a href="{{ route('generate.index', ['source' => 'competitor', 'keyword' => $rk->keyword, 'title' => $rk->keyword, 'website_id' => $competitor->website_id]) }}"
                                                               class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                                                {{ __('competitors.show.advanced.table.generate.using_keyword') }}
                                                            </a>
                                                            <a href="{{ route('generate.index', ['source' => 'competitor', 'generation_source' => 'reference', 'keyword' => $rk->keyword, 'title' => $rk->keyword, 'ref_topic' => $rk->keyword, 'ref_url' => $rk->url, 'website_id' => $competitor->website_id]) }}"
                                                               class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-b-lg border-t border-gray-100">
                                                                {{ __('competitors.show.advanced.table.generate.duplicate') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($rankedKeywords->isEmpty())
                                <div class="p-4 text-sm text-gray-500">{{ __('competitors.show.advanced.table.empty') }}</div>
                            @endif
                        </div>

                        @if($rankedKeywords->hasPages())
                            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    {{ __('competitors.show.advanced.table.pagination', ['from' => $rankedKeywords->firstItem(), 'to' => $rankedKeywords->lastItem(), 'total' => $rankedKeywords->total()]) }}
                                </div>
                                <div>
                                    {{ $rankedKeywords->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
const competitorShowI18n = {!! json_encode([
    'advanced' => [
        'status' => __('competitors.show.advanced.status', ['status' => ':status', 'progress' => ':progress']),
        'completed' => __('competitors.show.advanced.completed'),
        'failed' => __('competitors.show.advanced.failed', ['message' => ':message']),
        'error' => __('competitors.show.advanced.error', ['message' => ':message']),
        'running' => __('competitors.show.advanced.running'),
        'starting' => __('competitors.show.advanced.starting'),
        'processing' => __('competitors.show.advanced.processing'),
        'run' => __('competitors.show.advanced.run'),
    ],
    'target_modal' => [
        'title' => __('competitors.show.target_modal.title'),
        'keyword' => __('competitors.show.target_modal.keyword'),
        'website' => __('competitors.show.target_modal.website'),
        'target_position' => __('competitors.show.target_modal.target_position'),
        'priority' => __('competitors.show.target_modal.priority'),
        'priority_high' => __('competitors.show.target_modal.priority_options.high'),
        'priority_medium' => __('competitors.show.target_modal.priority_options.medium'),
        'priority_low' => __('competitors.show.target_modal.priority_options.low'),
        'cancel' => __('competitors.show.target_modal.cancel'),
        'save' => __('competitors.show.target_modal.save'),
    ],
]) !!};
// Advanced analysis run/poll
const advancedBtn = document.getElementById('runAdvancedBtn');
const advancedStatus = document.getElementById('advancedStatus');

function setAdvancedStatus(text) {
    if (!advancedStatus) return;
    advancedStatus.classList.remove('hidden');
    advancedStatus.textContent = text;
}

function pollAdvancedStatus(url) {
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Status failed');
            setAdvancedStatus(competitorShowI18n.advanced.status.replace(':status', data.status).replace(':progress', data.progress));

            if (data.status === 'completed') {
                setAdvancedStatus(competitorShowI18n.advanced.completed);
                setTimeout(() => window.location.reload(), 1000);
            } else if (data.status === 'failed') {
                setAdvancedStatus(competitorShowI18n.advanced.failed.replace(':message', data.error_message || competitorShowI18n.advanced.error.replace(':message', '')));
                if (advancedBtn) {
                    advancedBtn.disabled = false;
                    advancedBtn.textContent = competitorShowI18n.advanced.run;
                    advancedBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            } else {
                setTimeout(() => pollAdvancedStatus(url), 2000);
            }
        })
        .catch(err => {
            setAdvancedStatus(competitorShowI18n.advanced.error.replace(':message', err.message));
            if (advancedBtn) {
                advancedBtn.disabled = false;
                advancedBtn.textContent = competitorShowI18n.advanced.run;
                advancedBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        });
}

if (advancedBtn) {
    advancedBtn.addEventListener('click', () => {
        const runUrl = advancedBtn.dataset.url;
        advancedBtn.disabled = true;
        advancedBtn.textContent = competitorShowI18n.advanced.running;
        advancedBtn.classList.add('opacity-70', 'cursor-not-allowed');
        setAdvancedStatus(competitorShowI18n.advanced.starting);

        fetch(runUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': @js(csrf_token()),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Run failed');
            setAdvancedStatus(competitorShowI18n.advanced.processing);
            pollAdvancedStatus(@js(route('competitors.advanced.status', $competitor)));
        })
        .catch(err => {
            setAdvancedStatus(competitorShowI18n.advanced.error.replace(':message', err.message));
            advancedBtn.disabled = false;
            advancedBtn.textContent = competitorShowI18n.advanced.run;
            advancedBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        });
    });
}

function openTargetKeywordModal(keywordData, websiteId, websiteName) {
    const modal = document.createElement('div');
    modal.id = 'competitorTargetKeywordModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-96 shadow-xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">${competitorShowI18n.target_modal.title}</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-lg leading-none" onclick="closeCompetitorTargetModal()">&times;</button>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${competitorShowI18n.target_modal.keyword}</label>
                    <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">${escapeHtml(keywordData.keyword)}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${competitorShowI18n.target_modal.website}</label>
                    <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">${escapeHtml(websiteName)}</div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">${competitorShowI18n.target_modal.target_position}</label>
                        <input type="number" id="competitorTargetPosition" min="1" max="100" value="10"
                               class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">${competitorShowI18n.target_modal.priority}</label>
                        <select id="competitorTargetPriority" class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                            <option value="high">${competitorShowI18n.target_modal.priority_high}</option>
                            <option value="medium" selected>${competitorShowI18n.target_modal.priority_medium}</option>
                            <option value="low">${competitorShowI18n.target_modal.priority_low}</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeCompetitorTargetModal()" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs font-medium">
                        ${competitorShowI18n.target_modal.cancel}
                    </button>
                    <button type="button" onclick="saveCompetitorKeywordTarget()" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-medium">
                        ${competitorShowI18n.target_modal.save}
                    </button>
                </div>
            </div>
        </div>
    `;

    modal.dataset.keyword = keywordData.keyword;
    modal.dataset.websiteId = String(websiteId);
    modal.dataset.volume = String(keywordData.volume ?? 0);
    modal.dataset.intent = keywordData.intent ?? '';
    modal.dataset.competitionIndex = String(keywordData.competition_index ?? 0);

    document.body.appendChild(modal);
    const suggested = parseFloat(keywordData.gsc_avg_position || 0);
    if (!Number.isNaN(suggested) && suggested > 0) {
        const suggestedRounded = Math.min(100, Math.max(1, Math.round(suggested)));
        const input = document.getElementById('competitorTargetPosition');
        if (input) input.value = String(suggestedRounded);
    }
    modal.addEventListener('click', (e) => { if (e.target === modal) closeCompetitorTargetModal(); });
}

function closeCompetitorTargetModal() {
    document.getElementById('competitorTargetKeywordModal')?.remove();
}

function saveCompetitorKeywordTarget() {
    const modal = document.getElementById('competitorTargetKeywordModal');
    if (!modal) return;

    const priority = document.getElementById('competitorTargetPriority').value;
    const targetPosition = parseInt(document.getElementById('competitorTargetPosition').value, 10) || 10;

    const payload = {
        keywords: [{
            keyword: modal.dataset.keyword,
            website_id: parseInt(modal.dataset.websiteId, 10),
            volume: parseInt(modal.dataset.volume, 10) || 0,
            difficulty: 0,
            intent: modal.dataset.intent || null,
            competition_index: parseInt(modal.dataset.competitionIndex, 10) || null,
            priority: priority,
            target_position: targetPosition,
        }]
    };

    fetch(@js(route('keywords.save-from-deep-research')), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @js(csrf_token()),
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to save keyword');
        closeCompetitorTargetModal();
        window.location.reload();
    })
    .catch(err => alert('Error: ' + err.message));
}

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>
@endpush
@endsection
