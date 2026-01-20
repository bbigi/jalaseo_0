@extends('layouts.dashboard')

@section('title', __('insights.news.create.title') . ' - AneWP')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('insights.news.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            {{ __('insights.news.create.back') }}
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('insights.news.create.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('insights.news.create.subtitle') }}</p>
    </div>

    @php
        $defaultLanguage = collect($languages)->firstWhere('code', 'id') ?? ($languages[0] ?? ['code' => '', 'name' => '', 'flag' => '']);
        $defaultCountry = collect($countries)->firstWhere('code', 'id') ?? ($countries[0] ?? ['code' => '', 'name' => '', 'flag' => '']);
        $sourceGroups = [
            'Custom' => $customSources ?? [],
            'Competitors' => $competitorSources ?? [],
            'Suggested' => ($suggestedSources['international'] ?? []) + ($suggestedSources['indonesia'] ?? []),
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Search Form (2/3) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg p-8">
                <form method="POST" action="{{ route('insights.news.search') }}" class="space-y-6">
                    @csrf

                    <!-- Source (Autocomplete) -->
                    <div>
                        <label for="sourceSearch" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.news.create.form.source_label') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="sourceSearch"
                                   placeholder="{{ __('insights.news.create.form.source_placeholder') }}"
                                   autocomplete="off"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="hidden" name="source" id="sourceValue">

                            <button type="button"
                                    id="openSourceModal"
                                    class="absolute right-2 top-2 px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100">
                                {{ __('insights.news.create.form.source_add') }}
                            </button>

                            <!-- Dropdown -->
                            <div id="sourceDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto">
                                <div class="p-2">
                                    @foreach($sourceGroups as $groupName => $groupItems)
                                        @if(!empty($groupItems))
                                        <div class="mb-2">
                                            <p class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase">{{ $groupName }}</p>
                                            @foreach($groupItems as $domain => $name)
                                            <button type="button"
                                                    onclick="selectSource('{{ $domain }}', '{{ $name }}')"
                                                    class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md transition source-option"
                                                    data-text="{{ strtolower($domain . ' ' . $name) }}">
                                                <p class="font-medium text-gray-900">{{ $name }}</p>
                                                <p class="text-sm text-gray-600">{{ $domain }}</p>
                                            </button>
                                            @endforeach
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ __('insights.news.create.form.source_help') }}</p>
                    </div>

                    <!-- Language (Autocomplete with Flag) -->
                    <div>
                        <label for="languageSearch" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.news.create.form.language_label') }}
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="languageSearch"
                                   placeholder="{{ __('insights.news.create.form.language_placeholder') }}"
                                   autocomplete="off"
                                   value="{{ ($defaultLanguage['flag'] ?? '') . ' ' . ($defaultLanguage['name'] ?? '') }}"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="hidden" name="language" id="languageValue" value="{{ $defaultLanguage['code'] ?? '' }}">

                            <div id="languageDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <div class="p-2">
                                    @foreach($languages as $lang)
                                    <button type="button"
                                            onclick="selectLanguage('{{ $lang['code'] }}', '{{ $lang['name'] }}', '{{ $lang['flag'] }}')"
                                            class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md transition language-option"
                                            data-name="{{ strtolower($lang['name']) }}">
                                        <span class="text-lg mr-2">{{ $lang['flag'] }}</span>
                                        <span class="font-medium text-gray-900">{{ $lang['name'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Country (Autocomplete with Flag) -->
                    <div>
                        <label for="countrySearch" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.news.create.form.country_label') }}
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="countrySearch"
                                   placeholder="{{ __('insights.news.create.form.country_placeholder') }}"
                                   autocomplete="off"
                                   value="{{ ($defaultCountry['flag'] ?? '') . ' ' . ($defaultCountry['name'] ?? '') }}"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="hidden" name="country" id="countryValue" value="{{ $defaultCountry['code'] ?? '' }}">

                            <div id="countryDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <div class="p-2">
                                    @foreach($countries as $country)
                                    <button type="button"
                                            onclick="selectCountry('{{ $country['code'] }}', '{{ $country['name'] }}', '{{ $country['flag'] }}')"
                                            class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md transition country-option"
                                            data-name="{{ strtolower($country['name']) }}">
                                        <span class="text-lg mr-2">{{ $country['flag'] }}</span>
                                        <span class="font-medium text-gray-900">{{ $country['name'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Theme -->
                    <div>
                        <label for="theme" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.news.create.form.theme_label') }} <span class="text-gray-500">{{ __('insights.news.create.form.theme_optional') }}</span>
                        </label>
                        <input type="text"
                               name="theme"
                               id="theme"
                               class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="{{ __('insights.news.create.form.theme_placeholder') }}">
                        <p class="flex items-center gap-1.5 text-sm text-gray-600 mt-2">
                            <x-heroicon-o-light-bulb class="w-4 h-4" />
                            {{ __('insights.news.create.form.theme_help') }}
                        </p>
                    </div>

                    <!-- Time Range (Dropdown) -->
                    <div>
                        <label for="timeRange" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.news.create.form.time_range') }}
                        </label>
                        <select name="time_range"
                                id="timeRange"
                                class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="today">{{ __('insights.news.create.form.time_options.today') }}</option>
                            <option value="week" selected>{{ __('insights.news.create.form.time_options.week') }}</option>
                            <option value="month">{{ __('insights.news.create.form.time_options.month') }}</option>
                            <option value="year">{{ __('insights.news.create.form.time_options.year') }}</option>
                            <option value="all">{{ __('insights.news.create.form.time_options.all') }}</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        {{ __('insights.news.create.form.submit') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Sidebar (1/3) -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('insights.news.create.stats_sidebar.total_searches') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_searches'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('insights.news.create.stats_sidebar.insights_created') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['insights_created'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            @if(!empty($stats['top_sources']))
            <div class="bg-white rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-heroicon-o-trophy class="w-5 h-5 text-yellow-500" />
                    {{ __('insights.news.create.stats_sidebar.top_sources') }}
                </h3>
                <div class="space-y-2">
                    @foreach(array_slice($stats['top_sources'], 0, 5) as $source)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 truncate">{{ $source['source'] }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $source['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Source Autocomplete
const sourceSearch = document.getElementById('sourceSearch');
const sourceValue = document.getElementById('sourceValue');
const sourceDropdown = document.getElementById('sourceDropdown');
const openSourceModalBtn = document.getElementById('openSourceModal');

sourceSearch.addEventListener('focus', () => {
    sourceDropdown.classList.remove('hidden');
    filterSourceOptions('');
});

sourceSearch.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    sourceValue.value = this.value;
    filterSourceOptions(query);
});

function filterSourceOptions(query) {
    const options = sourceDropdown.querySelectorAll('.source-option');
    let visibleCount = 0;
    const maxVisible = 5;

    options.forEach(opt => {
        const text = opt.getAttribute('data-text') || opt.textContent.toLowerCase();
        const matches = text.includes(query);

        if (matches && (query.length > 0 || visibleCount < maxVisible)) {
            opt.style.display = 'block';
            if (query.length === 0) visibleCount++;
        } else {
            opt.style.display = 'none';
        }
    });
}

function selectSource(domain, name) {
    sourceSearch.value = name + ' (' + domain + ')';
    sourceValue.value = domain;
    sourceDropdown.classList.add('hidden');
}

// Add Source Modal
const sourceModal = document.createElement('div');
sourceModal.className = 'fixed inset-0 z-50 hidden';
sourceModal.innerHTML = `
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeSourceModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
            <button class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" onclick="closeSourceModal()">&times;</button>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tambah Sumber Berita</h3>
            <form id="sourceForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sumber</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                    <input type="text" name="domain" placeholder="contoh: techcrunch.com" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeSourceModal()" class="px-4 py-2 text-gray-700 border rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
`;
document.body.appendChild(sourceModal);

openSourceModalBtn.addEventListener('click', () => {
    sourceModal.classList.remove('hidden');
});

function closeSourceModal() {
    sourceModal.classList.add('hidden');
}

document.getElementById('sourceForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    try {
        const resp = await fetch('{{ route('insights.news.sources.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await resp.json();
        if (!resp.ok || !data.success) {
            throw new Error(data.message || 'Failed to add source');
        }
        // add to dropdown
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <button type="button"
                onclick="selectSource('${data.source.domain}', '${data.source.name}')"
                class="w-full text-left px-3 py-2 hover:bg-gray-100 rounded-md transition source-option"
                data-text="${data.source.domain.toLowerCase()} ${data.source.name.toLowerCase()}">
                <p class="font-medium text-gray-900">${data.source.name}</p>
                <p class="text-sm text-gray-600">${data.source.domain}</p>
            </button>
        `;
        sourceDropdown.querySelector('.p-2').prepend(wrapper.firstElementChild);
        closeSourceModal();
        form.reset();
    } catch (error) {
        alert(error.message);
    }
});

// Language Autocomplete
const languageSearch = document.getElementById('languageSearch');
const languageValue = document.getElementById('languageValue');
const languageDropdown = document.getElementById('languageDropdown');

languageSearch.addEventListener('focus', () => {
    languageDropdown.classList.remove('hidden');
    filterLanguageOptions('');
});

languageSearch.addEventListener('input', function() {
    filterLanguageOptions(this.value.toLowerCase());
});

function filterLanguageOptions(query) {
    const options = languageDropdown.querySelectorAll('.language-option');
    let visibleCount = 0;
    const maxVisible = 5;

    options.forEach(opt => {
        const name = opt.getAttribute('data-name');
        const matches = name.includes(query);

        if (matches && (query.length > 0 || visibleCount < maxVisible)) {
            opt.style.display = 'block';
            if (query.length === 0) visibleCount++;
        } else {
            opt.style.display = 'none';
        }
    });
}

function selectLanguage(code, name, flag) {
    languageSearch.value = flag + ' ' + name;
    languageValue.value = code;
    languageDropdown.classList.add('hidden');
}

// Country Autocomplete
const countrySearch = document.getElementById('countrySearch');
const countryValue = document.getElementById('countryValue');
const countryDropdown = document.getElementById('countryDropdown');

countrySearch.addEventListener('focus', () => {
    countryDropdown.classList.remove('hidden');
    filterCountryOptions('');
});

countrySearch.addEventListener('input', function() {
    filterCountryOptions(this.value.toLowerCase());
});

function filterCountryOptions(query) {
    const options = countryDropdown.querySelectorAll('.country-option');
    let visibleCount = 0;
    const maxVisible = 5;

    options.forEach(opt => {
        const name = opt.getAttribute('data-name');
        const matches = name.includes(query);

        if (matches && (query.length > 0 || visibleCount < maxVisible)) {
            opt.style.display = 'block';
            if (query.length === 0) visibleCount++;
        } else {
            opt.style.display = 'none';
        }
    });
}

function selectCountry(code, name, flag) {
    countrySearch.value = flag + ' ' + name;
    countryValue.value = code;
    countryDropdown.classList.add('hidden');
}

// Hide dropdowns on click outside
document.addEventListener('click', (e) => {
    if (!sourceSearch.contains(e.target) && !sourceDropdown.contains(e.target)) {
        sourceDropdown.classList.add('hidden');
    }
    if (!languageSearch.contains(e.target) && !languageDropdown.contains(e.target)) {
        languageDropdown.classList.add('hidden');
    }
    if (!countrySearch.contains(e.target) && !countryDropdown.contains(e.target)) {
        countryDropdown.classList.add('hidden');
    }
});
</script>
@endsection
