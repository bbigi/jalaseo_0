@extends('layouts.dashboard')

@section('title', __('analytics-gaps.title') . ' - ' . $website->name)

@php
    use Illuminate\Support\Str;
    $statCards = [
        ['label' => __('analytics-gaps.stats.card_labels.we_win'), 'value' => $stats['we_win'] ?? 0, 'color' => 'text-green-600 bg-green-50'],
        ['label' => __('analytics-gaps.stats.card_labels.opportunity'), 'value' => $stats['opportunity'] ?? 0, 'color' => 'text-amber-600 bg-amber-50'],
        ['label' => __('analytics-gaps.stats.card_labels.content_gap'), 'value' => $stats['content_gap'] ?? 0, 'color' => 'text-blue-600 bg-blue-50'],
        ['label' => __('analytics-gaps.stats.card_labels.low_priority'), 'value' => $stats['low_priority'] ?? 0, 'color' => 'text-gray-600 bg-gray-50'],
    ];
@endphp

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('analytics-gaps.title') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ $website->name }}</p>
        </div>
        <a href="{{ route('websites.show', ['website' => $website->id]) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            ← {{ __('analytics-gaps.back') }}
        </a>
    </div>

    @if($competitors->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-200 p-8 text-center">
            <p class="text-gray-700 font-semibold mb-2">{{ __('analytics-gaps.no_competitors.title') }}</p>
            <p class="text-gray-500 text-sm">{{ __('analytics-gaps.no_competitors.description') }}</p>
        </div>
    @else

    {{-- Controls --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-3">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">{{ __('analytics-gaps.controls.select_competitor') }}</label>
                <select name="competitor_id" onchange="this.form.submit()" class="w-full rounded-lg border py-3 px-3 border-gray-300 focus:ring-blue-500">
                    @foreach($competitors as $comp)
                        <option value="{{ $comp->id }}" {{ $selectedCompetitor && $selectedCompetitor->id === $comp->id ? 'selected' : '' }}>
                            {{ $comp->domain }} {{ $comp->is_primary ? '• '. __('analytics-gaps.controls.primary') : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap md:justify-end gap-2">
                @php
                    $filters = [
                        'all' => __('analytics-gaps.controls.filters.all'),
                        'we_win' => __('analytics-gaps.controls.filters.we_win'),
                        'opportunity' => __('analytics-gaps.controls.filters.opportunity'),
                        'content_gap' => __('analytics-gaps.controls.filters.content_gap'),
                        'low_priority' => __('analytics-gaps.controls.filters.low_priority'),
                    ];
                @endphp
                @foreach($filters as $key => $label)
                    <a href="{{ route('website-analytics.gaps', [$website, 'competitor_id' => $selectedCompetitor->id ?? null, 'filter' => $key, 'q' => request('q')]) }}"
                       class="px-3 py-1 rounded-full text-sm border {{ $filter === $key ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }} @if(isset($stats[$key])) ({{ $stats[$key] }}) @endif
                    </a>
                @endforeach
            </div>

            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">{{ __('analytics-gaps.controls.search_label') }}</label>
                <div class="relative">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('analytics-gaps.controls.search_placeholder') }}" class="w-full rounded-lg border py-3 px-3 border-gray-300 border-gray-300 pl-3 pr-9 focus:ring-blue-500">
                    @if($search)
                        <button type="button" onclick="this.previousElementSibling.value=''; this.form.submit();" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">×</button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">{{ __('analytics-gaps.stats.total_keywords.title') }}</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($stats['total'] ?? 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ __('analytics-gaps.stats.total_keywords.subtitle') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">{{ __('analytics-gaps.stats.win_rate.title') }}</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">
                {{ isset($stats['win_rate']) ? $stats['win_rate'].'%' : '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ __('analytics-gaps.stats.win_rate.subtitle') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">{{ __('analytics-gaps.stats.median_delta.title') }}</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">
                {{ isset($stats['median_delta']) ? number_format($stats['median_delta'], 1) : '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ __('analytics-gaps.stats.median_delta.subtitle') }}</p>
        </div>
        @foreach($statCards as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold {{ $card['color'] }} mt-1">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full" id="gapsTable">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-500 border-b border-gray-200">
                        <th class="px-4 py-3 cursor-pointer select-none" data-sort="keyword">
                            <div class="flex items-center gap-1">
                                <span>{{ __('analytics-gaps.table.keyword') }}</span>
                                <span class="text-gray-300 text-[10px]" data-sort-indicator="keyword">↕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none" data-sort="volume">
                            <div class="flex items-center gap-1">
                                <span>{{ __('analytics-gaps.table.volume') }}</span>
                                <span class="text-gray-300 text-[10px]" data-sort-indicator="volume">↕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none" data-sort="ourPosition">
                            <div class="flex items-center gap-1">
                                <span>{{ __('analytics-gaps.table.our_position') }}</span>
                                <span class="text-gray-300 text-[10px]" data-sort-indicator="ourPosition">↕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none" data-sort="competitorPosition">
                            <div class="flex items-center gap-1">
                                <span>{{ __('analytics-gaps.table.competitor_position') }}</span>
                                <span class="text-gray-300 text-[10px]" data-sort-indicator="competitorPosition">↕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3">{{ __('analytics-gaps.table.delta') }}</th>
                        <th class="px-4 py-3">{{ __('analytics-gaps.table.competitor_url') }}</th>
                        <th class="px-4 py-3 cursor-pointer select-none" data-sort="etv">
                            <div class="flex items-center gap-1">
                                <span>{{ __('analytics-gaps.table.etv') }}</span>
                                <span class="text-gray-300 text-[10px]" data-sort-indicator="etv">↕</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center">{{ __('analytics-gaps.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="gapsTableBody">
                    @forelse($rows as $row)
                        @php
                            $deltaBadge = $row['delta'] === null ? null : ($row['delta'] < 0 ? [__('analytics-gaps.table.badge.ahead'), 'bg-green-50 text-green-700'] : ($row['delta'] > 0 ? [__('analytics-gaps.table.badge.behind'), 'bg-red-50 text-red-700'] : [__('analytics-gaps.table.badge.same'), 'bg-gray-50 text-gray-700']));
                            $classMap = [
                                'we_win' => 'bg-green-50 text-green-700',
                                'opportunity' => 'bg-amber-50 text-amber-700',
                                'content_gap' => 'bg-blue-50 text-blue-700',
                                'low_priority' => 'bg-gray-50 text-gray-600',
                            ];
                            $classificationLabels = [
                                'we_win' => __('analytics-gaps.table.classification.we_win'),
                                'opportunity' => __('analytics-gaps.table.classification.opportunity'),
                                'content_gap' => __('analytics-gaps.table.classification.content_gap'),
                                'low_priority' => __('analytics-gaps.table.classification.low_priority'),
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/60 gap-row"
                            data-keyword="{{ Str::lower($row['keyword']) }}"
                            data-volume="{{ $row['search_volume'] ?? 0 }}"
                            data-our-position="{{ $row['our_rank'] ?? 0 }}"
                            data-competitor-position="{{ $row['competitor_rank'] ?? 0 }}"
                            data-etv="{{ $row['etv'] ?? 0 }}">
                            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $classMap[$row['classification']] ?? 'bg-gray-50 text-gray-600' }}">
                                        {{ $classificationLabels[$row['classification']] ?? str_replace('_', ' ', ucfirst($row['classification'])) }}
                                    </span>
                                    <span>{{ $row['keyword'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-200">{{ $row['search_volume'] ? number_format($row['search_volume']) : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border-b border-gray-200">{{ $row['our_rank'] ? number_format($row['our_rank'], 1) : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 border-b border-gray-200">{{ $row['competitor_rank'] ? '#'.$row['competitor_rank'] : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-200">
                                @if($deltaBadge)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $deltaBadge[1] }}">
                                        {{ $deltaBadge[0] }} {{ $row['delta'] > 0 ? '+' : '' }}{{ number_format($row['delta'], 1) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-600 truncate max-w-xs border-b border-gray-200">
                                @if($row['competitor_url'])
                                    <a href="{{ $row['competitor_url'] }}" target="_blank" class="hover:underline">{{ Str::limit($row['competitor_url'], 60) }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-200">{{ $row['etv'] ? number_format($row['etv'], 1) : '—' }}</td>
                            <td class="px-4 py-3 text-center text-sm border-b border-gray-200">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="relative inline-block" x-data="{ open: false }">
                                        <button type="button"
                                                @click="open = !open"
                                                class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                                title="{{ __('analytics-gaps.table.generate.title') }}"
                                                alt="{{ __('analytics-gaps.table.generate.title') }}">
                                            <x-heroicon-o-sparkles class="w-5 h-5" />
                                        </button>
                                        <div x-show="open"
                                             @click.away="open = false"
                                             x-transition
                                             class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <a href="{{ route('generate.index', ['source' => 'analytics_gap', 'keyword' => $row['keyword'], 'title' => $row['keyword'], 'website_id' => $website->id]) }}"
                                               class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                                {{ __('analytics-gaps.table.generate.using_keyword') }}
                                            </a>
                                            @if($row['competitor_url'])
                                            <a href="{{ route('generate.index', ['source' => 'analytics_gap', 'generation_source' => 'reference', 'keyword' => $row['keyword'], 'title' => $row['keyword'], 'ref_topic' => $row['keyword'], 'ref_url' => $row['competitor_url'], 'website_id' => $website->id]) }}"
                                               class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-b-lg border-t border-gray-100">
                                                {{ __('analytics-gaps.table.generate.duplicate') }}
                                            </a>
                                            @else
                                            <span class="block px-3 py-2 text-xs text-gray-400 rounded-b-lg border-t border-gray-100 cursor-not-allowed">
                                                {{ __('analytics-gaps.table.generate.duplicate') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" class="p-2 bg-gray-100 text-blue-600 rounded-lg hover:text-blue-700 cursor-pointer" title="{{ __('analytics-gaps.table.save_track') }}" alt="{{ __('analytics-gaps.table.save_track') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="gapsEmptyRow">
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500 text-sm">
                                {{ __('analytics-gaps.table.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 py-3 border-t border-gray-200">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <label for="gapsPerPage" class="text-sm text-gray-600">{{ __('analytics-gaps.pagination.per_page') }}</label>
                <select id="gapsPerPage" class="border rounded-md px-2 py-1 text-sm">
                    <option value="10">10</option>
                    <option value="15">15</option>
                </select>
                <span id="gapPaginationInfo" class="text-sm text-gray-600"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="gapPrevPage" class="px-3 py-1 text-sm border rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">{{ __('analytics-gaps.pagination.prev') }}</button>
                <button type="button" id="gapNextPage" class="px-3 py-1 text-sm border rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">{{ __('analytics-gaps.pagination.next') }}</button>
            </div>
        </div>
    </div>

    {{-- AI Recommendations --}}
    <div class="mt-6 bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('analytics-gaps.recommendations.title') }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ __('analytics-gaps.recommendations.subtitle') }}</p>
            </div>
            <button type="button"
                    id="generateGapRecommendations"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    data-url="{{ route('website-analytics.gaps.recommendations', $website) }}"
                    data-competitor-id="{{ $selectedCompetitor->id ?? '' }}"
                    data-filter="{{ $filter }}"
                    data-search="{{ $search }}"
                    data-loading-text="{{ __('analytics-gaps.recommendations.loading') }}"
                    data-error-text="{{ __('analytics-gaps.recommendations.error') }}"
                    data-empty-text="{{ __('analytics-gaps.recommendations.empty') }}">
                <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                <span>{{ __('analytics-gaps.recommendations.button') }}</span>
            </button>
        </div>
        <div id="gapRecommendationsStatus" class="text-sm text-gray-600 mt-3 hidden"></div>
        <div id="gapRecommendationsContent" class="mt-4 space-y-2 {{ $latestRecommendation ? '' : 'hidden' }}">
            @if($latestRecommendation)
                <p class="text-xs text-gray-500">
                    {{ __('analytics-gaps.recommendations.last_generated') }}
                    {{ optional($latestRecommendation->created_at)->format('d M Y H:i') }}
                </p>
                <div id="gapRecommendationsContentInner" class="space-y-4 text-slate-900">{!! $latestRecommendation->content !!}</div>
            @else
                <div id="gapRecommendationsContentInner"></div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('gapsTableBody');
    if (!tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('.gap-row'));
    const emptyRow = document.getElementById('gapsEmptyRow');
    const perPageSelect = document.getElementById('gapsPerPage');
    const prevBtn = document.getElementById('gapPrevPage');
    const nextBtn = document.getElementById('gapNextPage');
    const info = document.getElementById('gapPaginationInfo');
    const sortIndicators = document.querySelectorAll('[data-sort-indicator]');
    const paginationText = {
        showing: @json(__('analytics-gaps.pagination.showing')),
    };

    let currentPage = 1;
    let perPage = parseInt(perPageSelect?.value || '10', 10);
    let sortState = { column: null, direction: 'asc' };

    const numericColumns = ['volume', 'ourPosition', 'competitorPosition', 'etv'];

    function formatInfo(from, to, total) {
        return paginationText.showing
            .replace(':from', from)
            .replace(':to', to)
            .replace(':total', total);
    }

    function updateSortIndicators() {
        sortIndicators.forEach((indicator) => {
            const column = indicator.dataset.sortIndicator;
            if (sortState.column === column) {
                indicator.textContent = sortState.direction === 'asc' ? '▲' : '▼';
                indicator.classList.remove('text-gray-300');
                indicator.classList.add('text-gray-700');
            } else {
                indicator.textContent = '↕';
                indicator.classList.remove('text-gray-700');
                indicator.classList.add('text-gray-300');
            }
        });
    }

    function getSortedRows() {
        if (!sortState.column) return [...rows];
        const dir = sortState.direction === 'asc' ? 1 : -1;
        return [...rows].sort((a, b) => {
            const col = sortState.column;
            if (numericColumns.includes(col)) {
                const aVal = parseFloat(a.dataset[col]) || 0;
                const bVal = parseFloat(b.dataset[col]) || 0;
                return (aVal - bVal) * dir;
            }
            const aVal = (a.dataset[col] || '').toString();
            const bVal = (b.dataset[col] || '').toString();
            return aVal.localeCompare(bVal) * dir;
        });
    }

    function render() {
        const sortedRows = getSortedRows();
        const total = sortedRows.length;

        if (total === 0) {
            tableBody.innerHTML = '';
            if (emptyRow) tableBody.appendChild(emptyRow);
            if (info) info.textContent = formatInfo(0, 0, 0);
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            return;
        }

        const totalPages = Math.max(1, Math.ceil(total / perPage));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const visibleRows = sortedRows.slice(start, start + perPage);

        tableBody.innerHTML = '';
        visibleRows.forEach((row) => tableBody.appendChild(row));

        const from = start + 1;
        const to = start + visibleRows.length;
        if (info) info.textContent = formatInfo(from, to, total);
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
    }

    document.querySelectorAll('[data-sort]').forEach((header) => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            if (sortState.column === column) {
                sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                sortState.column = column;
                sortState.direction = 'asc';
            }
            updateSortIndicators();
            render();
        });
    });

    if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
            perPage = parseInt(perPageSelect.value, 10) || 10;
            currentPage = 1;
            render();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage -= 1;
                render();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentPage += 1;
            render();
        });
    }

    updateSortIndicators();
    render();
});

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('generateGapRecommendations');
    const statusEl = document.getElementById('gapRecommendationsStatus');
    const contentWrapper = document.getElementById('gapRecommendationsContent');
    const contentEl = document.getElementById('gapRecommendationsContentInner');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!btn || !statusEl || !contentEl) return;

    function setStatus(text, isError = false) {
        statusEl.textContent = text;
        statusEl.classList.toggle('hidden', !text);
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-gray-600', !isError);
    }

    btn.addEventListener('click', async () => {
        const url = btn.dataset.url;
        if (!url) return;

        btn.disabled = true;
        setStatus(btn.dataset.loadingText || 'Generating...');
        if (contentWrapper) {
            contentWrapper.classList.add('hidden');
        }
        if (contentEl) {
            contentEl.innerHTML = '';
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    competitor_id: btn.dataset.competitorId || null,
                    filter: btn.dataset.filter || 'all',
                    q: btn.dataset.search || ''
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || btn.dataset.errorText || 'Error');
            }

            if (!data.content) {
                throw new Error(btn.dataset.emptyText || 'No content returned.');
            }

            const cleaned = (data.content || '')
                .replace(/^```(?:html)?\s*/i, '')
                .replace(/```$/i, '')
                .replace(/```/g, '')
                .trim();
            if (contentEl) {
                contentEl.innerHTML = cleaned;
            }
            if (contentWrapper) {
                contentWrapper.classList.remove('hidden');
            }
            setStatus('');
        } catch (error) {
            setStatus(error.message || btn.dataset.errorText || 'Error', true);
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
