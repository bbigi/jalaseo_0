@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('deep-research.title') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('deep-research.subtitle') }}</p>
        </div>
    </div>

    @if(!$isConfigured)
    <!-- API Not Configured Warning -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 flex-shrink-0" />
            <div>
                <h3 class="font-medium text-yellow-800">{{ __('deep-research.not_configured.title') }}</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    {{ __('deep-research.not_configured.body') }}
                </p>
                <a href="{{ route('admin.api-keys.create') }}" class="inline-flex items-center gap-1 text-sm text-yellow-800 font-medium mt-2 hover:underline">
                    {{ __('deep-research.not_configured.cta') }}
                </a>
                <p class="text-xs text-yellow-600 mt-2">
                    {{ __('deep-research.not_configured.category') }}: <code class="bg-yellow-100 px-1 rounded">deep_research</code> | 
                    {{ __('deep-research.not_configured.provider') }}: <code class="bg-yellow-100 px-1 rounded">dataforseo</code>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Start Research Form -->
    <div class="bg-white rounded-lg p-6 mb-6 {{ !$isConfigured ? 'opacity-50 pointer-events-none' : '' }}">
        <h2 class="text-lg font-semibold mb-4">{{ __('deep-research.form.title') }}</h2>
        <form id="startResearchForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Website Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deep-research.form.website') }}</label>
                    <div class="custom-select-wrapper" data-name="website_id">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value placeholder">{{ __('deep-research.form.website_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('deep-research.form.website_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($websites as $website)
                                <div class="custom-select-option" data-value="{{ $website->id }}">{{ $website->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="website_id" value="" required>
                    </div>
                </div>
                
                <!-- Seed Keyword -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deep-research.form.seed_keyword') }}</label>
                    <div class="relative">
                        <input type="text" name="seed_keyword" id="seedKeyword" required 
                               placeholder="{{ __('deep-research.form.seed_placeholder') }}" {{ !$isConfigured ? 'disabled' : '' }}
                               class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                               autocomplete="off">
                        <div id="autocompleteDropdown" class="absolute z-10 w-full bg-white border rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-auto"></div>
                    </div>
                </div>
                
                <!-- Start Button -->
                <div class="flex items-end">
                    <button type="submit" id="startBtn" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" {{ !$isConfigured ? 'disabled' : '' }}>
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        {{ __('deep-research.form.start') }}
                    </button>
                </div>
            </div>
            
            <!-- Location & Language Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Location Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deep-research.form.location') }}</label>
                    <div class="custom-select-wrapper" data-name="location" data-value="ID">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">🇮🇩 Indonesia</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('deep-research.form.location_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($countries as $country)
                                <div class="custom-select-option {{ $country['code'] === 'ID' ? 'selected' : '' }}" data-value="{{ $country['code'] }}">
                                    <span class="mr-2">{{ $country['flag'] }}</span>{{ $country['name'] }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="location" value="ID">
                    </div>
                </div>
                
                <!-- Language Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deep-research.form.language') }}</label>
                    <div class="custom-select-wrapper" data-name="language" data-value="id_ID">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">🇮🇩 Bahasa Indonesia</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('deep-research.form.language_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($languages as $lang)
                                <div class="custom-select-option {{ $lang['code'] === 'id_ID' ? 'selected' : '' }}" data-value="{{ $lang['code'] }}">
                                    <span class="mr-2">{{ $lang['flag'] }}</span>{{ $lang['name'] }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="language" value="id_ID">
                    </div>
                </div>
            </div>
        </form>

        <!-- Progress Bar (hidden by default) -->
        <div id="progressSection" class="hidden mt-6">
            <div class="flex items-center justify-between mb-2">
                <span id="progressStep" class="text-sm text-gray-600">{{ __('deep-research.form.progress_initial') }}</span>
                <span id="progressPercent" class="text-sm font-medium text-blue-600">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Recent Sessions -->
    <div class="bg-white rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">{{ __('deep-research.recent.title') }}</h2>
        </div>
        @if($sessions->count() > 0)
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.keyword') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.website') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.keywords') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.recent.table.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($sessions as $session)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-sm text-gray-900">{{ $session->seed_keyword }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $session->website->name ?? '-' }}</td>
                    <td class="px-6 py-4 font-sm">
                        @if($session->status === 'completed')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('deep-research.recent.statuses.completed') }}</span>
                        @elseif($session->status === 'processing')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('deep-research.recent.statuses.processing') }}</span>
                        @elseif($session->status === 'failed')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('deep-research.recent.statuses.failed') }}</span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ __('deep-research.recent.statuses.other') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ number_format($session->keywords_count) }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $session->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <div class="inline-flex items-center gap-2">
                            @if($session->status === 'completed')
                            <a href="{{ route('deep-research.show', $session->session_id) }}"
                               class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded hover:bg-blue-700"
                               title="{{ __('deep-research.recent.view') }}">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </a>
                            @else
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-400 rounded cursor-not-allowed" title="{{ __('deep-research.recent.view_unavailable') }}">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </span>
                            @endif
                            <form action="{{ route('deep-research.destroy', $session->session_id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ __('deep-research.recent.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded hover:text-red-700" title="Delete">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p-6 text-center text-gray-500">
            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto text-gray-300 mb-3" />
            <p>{{ __('deep-research.recent.empty') }}</p>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.custom-select-wrapper { position: relative; }
.custom-select-trigger {
    width: 100%; padding: 0.625rem 0.75rem; background: white;
    border: 1px solid #d1d5db; border-radius: 0.5rem;
    display: flex; justify-content: space-between; align-items: center;
    cursor: pointer; transition: all 0.2s;
}
.custom-select-trigger:hover { border-color: #9ca3af; }
.custom-select-wrapper.active .custom-select-trigger {
    border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.custom-select-value { color: #111827; font-size: 0.875rem; }
.custom-select-value.placeholder { color: #9ca3af; }
.custom-select-dropdown {
    position: absolute; top: calc(100% + 0.25rem); left: 0; right: 0;
    background: white; border: 1px solid #e5e7eb; border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 50;
    display: none; max-height: 300px; overflow: hidden;
}
.custom-select-wrapper.active .custom-select-dropdown { display: block; }
.custom-select-search { padding: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.custom-select-search-input {
    width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db;
    border-radius: 0.375rem; font-size: 0.875rem; outline: none;
}
.custom-select-search-input:focus {
    border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.custom-select-options { max-height: 200px; overflow-y: auto; }
.custom-select-option {
    padding: 0.625rem 0.75rem; cursor: pointer; font-size: 0.875rem;
    color: #374151; transition: background-color 0.15s;
}
.custom-select-option:hover { background-color: #f3f4f6; }
.custom-select-option.selected { background-color: #eff6ff; color: #2563eb; font-weight: 500; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('startResearchForm');
    const seedInput = document.getElementById('seedKeyword');
    const dropdown = document.getElementById('autocompleteDropdown');
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const progressStep = document.getElementById('progressStep');
    const progressPercent = document.getElementById('progressPercent');
    const startBtn = document.getElementById('startBtn');
    const textStart = @json(__('deep-research.form.start'));
    const textStartSpinner = '<svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> ';
    const textStartResearching = textStartSpinner + textStart;
    const textStartFailed = @json(__('deep-research.alerts.start_failed'));
    const textErrorPrefix = @json(__('deep-research.alerts.error'));
    const textResearchFailed = @json(__('deep-research.alerts.research_failed'));
    
    let debounceTimer;
    let pollingInterval;

    // Initialize Custom Selects
    const selects = document.querySelectorAll('.custom-select-wrapper');
    selects.forEach(select => {
        const trigger = select.querySelector('.custom-select-trigger');
        const valueDisplay = select.querySelector('.custom-select-value');
        const searchInput = select.querySelector('.custom-select-search-input');
        const options = select.querySelectorAll('.custom-select-option');
        const hiddenInput = select.querySelector('input[type="hidden"]');

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            selects.forEach(s => { if (s !== select) s.classList.remove('active'); });
            select.classList.toggle('active');
            if (select.classList.contains('active') && searchInput) {
                searchInput.focus();
                searchInput.value = '';
                options.forEach(opt => opt.style.display = 'block');
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                options.forEach(opt => {
                    opt.style.display = opt.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                });
            });
        }

        options.forEach(option => {
            option.addEventListener('click', function() {
                hiddenInput.value = this.dataset.value;
                valueDisplay.textContent = this.textContent;
                valueDisplay.classList.remove('placeholder');
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                select.classList.remove('active');
            });
        });
    });

    // Close all dropdowns on outside click
    document.addEventListener('click', (e) => {
        selects.forEach(s => s.classList.remove('active'));
        if (!seedInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Seed Keyword Autocomplete
    seedInput.addEventListener('click', (e) => e.stopPropagation());
    seedInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const keyword = this.value.trim();
        
        if (keyword.length < 2) {
            dropdown.classList.add('hidden');
            return;
        }

        // Get current location and language from hidden inputs
        const locationCode = document.querySelector('input[name="location"]').value || 'ID';
        const langCode = document.querySelector('input[name="language"]').value || 'id_ID';
        const gl = locationCode.toLowerCase();
        const hl = langCode.split('_')[0]; // 'id_ID' → 'id'

        debounceTimer = setTimeout(() => {
            fetch('{{ route("deep-research.autocomplete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ keyword, gl, hl })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.suggestions.length) {
                    dropdown.innerHTML = data.suggestions.map(s => 
                        `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">${s}</div>`
                    ).join('');
                    dropdown.classList.remove('hidden');
                    
                    dropdown.querySelectorAll('div').forEach(item => {
                        item.addEventListener('click', () => {
                            seedInput.value = item.textContent;
                            dropdown.classList.add('hidden');
                        });
                    });
                } else {
                    dropdown.classList.add('hidden');
                }
            });
        }, 300);
    });

    // Start Research
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        startBtn.disabled = true;
        startBtn.innerHTML = textStartResearching;

        fetch('{{ route("deep-research.start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                progressSection.classList.remove('hidden');
                startPolling(data.session_id);
            } else {
                alert(data.error || textStartFailed);
                resetButton();
            }
        })
        .catch(err => {
            alert(textErrorPrefix + ' ' + err.message);
            resetButton();
        });
    });

    function startPolling(sessionId) {
        pollingInterval = setInterval(() => {
            fetch(`/deep-research/status/${sessionId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateProgress(data.progress, data.current_step);
                    
                    if (data.status === 'completed') {
                        clearInterval(pollingInterval);
                        window.location.href = `/deep-research/${sessionId}`;
                    } else if (data.status === 'failed') {
                        clearInterval(pollingInterval);
                        alert(textResearchFailed + ' ' + (data.error || 'Unknown error'));
                        resetButton();
                    }
                }
            });
        }, 2000);
    }

    function updateProgress(percent, step) {
        progressBar.style.width = percent + '%';
        progressPercent.textContent = percent + '%';
        progressStep.textContent = step;
    }

    function resetButton() {
        startBtn.disabled = false;
        startBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> ' + textStart;
        progressSection.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
