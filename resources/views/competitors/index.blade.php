@extends('layouts.dashboard')

@section('title', __('competitors.title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('competitors.title') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('competitors.subtitle') }}</p>
        </div>
        <button id="openAddModal" class="cursor-pointer px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            {{ __('competitors.actions.add') }}
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <form method="GET" action="{{ route('competitors.index') }}" class="bg-white rounded-lg border border-gray-100 p-4 mb-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.filters.website_label') }}</label>
                <select name="website_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">{{ __('competitors.filters.website_all') }}</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}" {{ request('website_id') == $website->id ? 'selected' : '' }}>
                            {{ $website->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('competitors.filters.primary_label') }}</label>
                <select name="is_primary" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="all">{{ __('competitors.filters.primary_all') }}</option>
                    <option value="yes" {{ request('is_primary') === 'yes' ? 'selected' : '' }}>{{ __('competitors.filters.primary_yes') }}</option>
                    <option value="no" {{ request('is_primary') === 'no' ? 'selected' : '' }}>{{ __('competitors.filters.primary_no') }}</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('competitors.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                {{ __('competitors.actions.reset') }}
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                {{ __('competitors.actions.apply') }}
            </button>
        </div>
    </form>

    <div class="bg-white rounded-lg border border-gray-100">
        @if ($competitors->isEmpty())
            <div class="p-10 text-center text-gray-500">
                {{ __('competitors.empty') }}
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('competitors.table.domain') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('competitors.table.website') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('competitors.table.status') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('competitors.table.primary') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('competitors.table.keywords') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('competitors.table.traffic') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('competitors.table.avg_rank') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('competitors.table.last_analyzed') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('competitors.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($competitors as $comp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $comp->domain }}
                                    @if ($comp->added_by === 'auto_discovery')
                                        <span class="ml-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ __('competitors.table.auto') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $comp->website->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusLabels = [
                                            'active' => __('competitors.status.active'),
                                            'paused' => __('competitors.status.paused'),
                                            'processing' => __('competitors.status.processing'),
                                            'failed' => __('competitors.status.failed'),
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded-full
                                        @if ($comp->status === 'active') bg-green-100 text-green-700
                                        @elseif ($comp->status === 'paused') bg-yellow-100 text-yellow-700
                                        @elseif ($comp->status === 'processing') bg-amber-100 text-amber-700
                                        @elseif ($comp->status === 'failed') bg-red-100 text-red-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ $statusLabels[$comp->status] ?? __('competitors.status.unknown') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($comp->is_primary)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ __('competitors.badges.primary') }}</span>
                                    @else
                                        <button class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded set-primary-btn"
                                                data-url="{{ route('competitors.set-primary', $comp) }}">
                                            {{ __('competitors.actions.set_primary') }}
                                        </button>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $comp->total_keywords ? number_format($comp->total_keywords) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $comp->traffic_estimate ? number_format($comp->traffic_estimate) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $comp->avg_rank ? number_format($comp->avg_rank, 1) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $comp->last_analyzed_at ? $comp->last_analyzed_at->diffForHumans() : __('competitors.table.never') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($comp->latest_analysis_status === 'processing')
                                        <span class="inline-flex items-center gap-1 text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded">
                                            <span class="inline-block w-3 h-3 border-2 border-yellow-300 border-t-yellow-700 rounded-full animate-spin"></span>
                                            {{ __('competitors.actions.processing') }}{{ $comp->latest_progress !== null ? ' ' . (int) $comp->latest_progress . '%' : '' }}
                                        </span>
                                    @elseif ($comp->latest_analysis_status === 'failed')
                                        <button class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded inline-flex items-center gap-1"
                                                onclick="analyzeCompetitor({{ $comp->id }}, '{{ $comp->domain }}', this)">
                                            <span class="analyze-label">{{ __('competitors.actions.retry') }}</span>
                                            <span class="analyze-spinner hidden w-3 h-3 border-2 border-red-200 border-t-red-700 rounded-full animate-spin"></span>
                                        </button>
                                    @else
                                        <div class="inline-flex items-center gap-1">
                                            <button class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded inline-flex items-center gap-1"
                                                    onclick="analyzeCompetitor({{ $comp->id }}, '{{ $comp->domain }}', this)">
                                                <span class="analyze-label">{{ __('competitors.actions.analyze') }}</span>
                                                <span class="analyze-spinner hidden w-3 h-3 border-2 border-blue-200 border-t-blue-700 rounded-full animate-spin"></span>
                                            </button>
                                            <a href="{{ route('competitors.show', $comp) }}"
                                               class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                                {{ __('competitors.actions.view') }}
                                            </a>
                                            <form method="POST" action="{{ route('competitors.destroy', $comp) }}"
                                                  onsubmit="return confirm('{{ __('competitors.messages.delete_confirm') }}');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">
                                                    {{ __('competitors.actions.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Add Competitor Modal -->
<div id="addCompetitorModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">{{ __('competitors.modals.add_title') }}</h3>
            <button id="closeAddModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form method="POST" action="{{ route('competitors.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('competitors.modals.website_label') }}</label>
                <select name="website_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                    <option value="">{{ __('competitors.modals.website_placeholder') }}</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}">{{ $website->name }} ({{ $website->url }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('competitors.modals.domain_label') }}</label>
                <input type="text" name="domain" placeholder="{{ __('competitors.modals.domain_placeholder') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('competitors.modals.country_label') }}</label>
                    <div class="custom-select-wrapper" data-name="country" data-value="{{ old('country', 'Indonesia') }}">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('competitors.modals.country_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('competitors.modals.country_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($countries ?? [] as $country)
                                    <div class="custom-select-option" data-value="{{ $country['name'] }}">
                                        <span class="mr-2">{{ $country['flag'] }}</span>{{ $country['name'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="country" value="{{ old('country', 'Indonesia') }}">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('competitors.modals.language_label') }}</label>
                    <div class="custom-select-wrapper" data-name="language" data-value="{{ old('language', 'id_ID') }}">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('competitors.modals.language_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('competitors.modals.language_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($languages ?? [] as $lang)
                                    <div class="custom-select-option" data-value="{{ $lang['code'] }}">
                                        <span class="mr-2">{{ $lang['flag'] }}</span>{{ $lang['name'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="language" value="{{ old('language', 'id_ID') }}">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('competitors.modals.notes_label') }}</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cancelAddModal" class="px-4 py-2 border border-gray-300 rounded-lg">
                    {{ __('competitors.modals.cancel') }}
                </button>
                <button type="submit" class="cursor-pointer px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    {{ __('competitors.modals.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const competitorI18n = {!! json_encode([
    'analyzing' => __('competitors.actions.analyzing'),
    'analyzing_percent' => __('competitors.actions.analyzing_percent', ['percent' => ':percent']),
    'analyze' => __('competitors.actions.analyze'),
    'done' => __('competitors.actions.done'),
    'retry' => __('competitors.actions.retry'),
    'processing' => __('competitors.actions.processing'),
    'processing_percent' => __('competitors.actions.processing_percent', ['percent' => ':percent']),
    'set_primary' => __('competitors.actions.set_primary'),
    'setting' => __('competitors.actions.setting'),
    'analyze_error' => __('competitors.messages.analyze_error', ['message' => ':message']),
    'analysis_failed' => __('competitors.messages.analysis_failed', ['message' => ':message']),
    'start_failed' => __('competitors.messages.start_failed'),
    'status_failed' => __('competitors.messages.status_failed'),
    'set_primary_failed' => __('competitors.messages.set_primary_failed'),
    'set_primary_error' => __('competitors.messages.set_primary_error'),
    'unknown_error' => __('competitors.messages.unknown_error'),
]) !!};
const addModal = document.getElementById('addCompetitorModal');
document.getElementById('openAddModal').addEventListener('click', () => {
    addModal.classList.remove('hidden');
    addModal.classList.add('flex');
});
document.getElementById('closeAddModal').addEventListener('click', () => {
    addModal.classList.add('hidden');
    addModal.classList.remove('flex');
});
document.getElementById('cancelAddModal').addEventListener('click', () => {
    addModal.classList.add('hidden');
    addModal.classList.remove('flex');
});

function setAnalyzeButtonLoading(button, isLoading, label) {
    if (!button) return;
    const spinner = button.querySelector('.analyze-spinner');
    const labelEl = button.querySelector('.analyze-label');

    if (labelEl && label) labelEl.textContent = label;
    if (spinner) spinner.classList.toggle('hidden', !isLoading);

    button.disabled = isLoading;
    button.classList.toggle('opacity-60', isLoading);
    button.classList.toggle('cursor-not-allowed', isLoading);
}

function analyzeCompetitor(competitorId, domain, buttonEl) {
    setAnalyzeButtonLoading(buttonEl, true, competitorI18n.analyzing);

    fetch('{{ route('competitors.analyze.start') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ competitor_id: competitorId })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || competitorI18n.start_failed);
        }
        pollAnalysis(data.session_id, buttonEl);
    })
    .catch(err => {
        setAnalyzeButtonLoading(buttonEl, false, competitorI18n.analyze);
        alert(competitorI18n.analyze_error.replace(':message', err.message));
    });
}

function pollAnalysis(sessionId, buttonEl) {
    fetch('{{ route('competitors.analyze.poll', 'SESSION_ID') }}'.replace('SESSION_ID', sessionId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || competitorI18n.status_failed);
        }

        if (data.status === 'completed') {
            setAnalyzeButtonLoading(buttonEl, false, competitorI18n.done);
            location.reload();
            return;
        }
        if (data.status === 'failed') {
            setAnalyzeButtonLoading(buttonEl, false, competitorI18n.retry);
            alert(competitorI18n.analysis_failed.replace(':message', data.error_message || competitorI18n.unknown_error));
            return;
        }

        if (buttonEl && typeof data.progress !== 'undefined' && data.progress !== null) {
            setAnalyzeButtonLoading(buttonEl, true, competitorI18n.analyzing_percent.replace(':percent', parseInt(data.progress, 10)));
        }

        setTimeout(() => pollAnalysis(sessionId, buttonEl), 2000);
    })
    .catch(err => {
        setAnalyzeButtonLoading(buttonEl, false, competitorI18n.analyze);
        alert(competitorI18n.analyze_error.replace(':message', err.message));
    });
}

// Set primary competitor
document.querySelectorAll('.set-primary-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const url = btn.dataset.url;
        btn.disabled = true;
        btn.textContent = competitorI18n.setting;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ is_primary: true })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                btn.disabled = false;
                btn.textContent = competitorI18n.set_primary;
                alert(data.message || competitorI18n.set_primary_failed);
                return;
            }
            window.location.reload();
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = competitorI18n.set_primary;
            alert(competitorI18n.set_primary_error);
        });
    });
});
</script>
@push('styles')
<style>
.custom-select-wrapper {
    position: relative;
}

.custom-select-trigger {
    width: 100%;
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.custom-select-trigger:hover {
    border-color: #9ca3af;
}

.custom-select-wrapper.active .custom-select-trigger {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.custom-select-value {
    color: #111827;
}

.custom-select-value.placeholder {
    color: #9ca3af;
}

.custom-select-dropdown {
    position: absolute;
    top: calc(100% + 0.25rem);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    display: none;
    max-height: 260px;
    overflow: hidden;
}

.custom-select-wrapper.active .custom-select-dropdown {
    display: block;
}

.custom-select-search {
    padding: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.custom-select-search-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    outline: none;
}

.custom-select-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.custom-select-options {
    max-height: 200px;
    overflow-y: auto;
}

.custom-select-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
    transition: background-color 0.15s;
}

.custom-select-option:hover {
    background-color: #f3f4f6;
}

.custom-select-option.selected {
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 500;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.custom-select-wrapper');

    selects.forEach(select => {
        const trigger = select.querySelector('.custom-select-trigger');
        const valueDisplay = select.querySelector('.custom-select-value');
        const searchInput = select.querySelector('.custom-select-search-input');
        const options = select.querySelectorAll('.custom-select-option');
        const hiddenInput = select.querySelector('input[type="hidden"]');
        const initialValue = select.dataset.value;

        if (initialValue) {
            const matchedOption = Array.from(options).find(opt => opt.dataset.value === initialValue);
            if (matchedOption) {
                valueDisplay.textContent = matchedOption.textContent;
                valueDisplay.classList.remove('placeholder');
                matchedOption.classList.add('selected');
            } else {
                valueDisplay.textContent = initialValue;
                valueDisplay.classList.remove('placeholder');
            }
            hiddenInput.value = initialValue;
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            selects.forEach(s => { if (s !== select) s.classList.remove('active'); });
            select.classList.toggle('active');
            if (select.classList.contains('active')) {
                searchInput.focus();
            }
        });

        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            options.forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
            });
        });

        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                hiddenInput.value = value;
                valueDisplay.textContent = this.textContent;
                valueDisplay.classList.remove('placeholder');
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                select.classList.remove('active');
                searchInput.value = '';
                options.forEach(opt => opt.style.display = 'block');
            });
        });
    });

    document.addEventListener('click', () => {
        selects.forEach(select => select.classList.remove('active'));
    });
});
</script>
@endpush
@endsection
