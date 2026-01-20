@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('insights.social.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            {{ __('insights.social.create.back') }}
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('insights.social.create.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('insights.social.create.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Search Form (2/3) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg p-8">
                <form method="POST" action="{{ route('insights.social.search') }}" class="space-y-6">
                    @csrf

                    <!-- Platform Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-3">
                            {{ __('insights.social.create.platform_label') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($platforms as $platform)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="platform" value="{{ $platform['id'] }}" class="peer sr-only" required>
                                <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition">
                                    <div class="text-center">
                                        <x-dynamic-component :component="'heroicon-o-' . $platform['icon']" class="w-8 h-8 text-gray-600 peer-checked:text-blue-600 mx-auto mb-2" />
                                        <p class="font-semibold text-gray-900">{{ $platform['name'] }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $platform['description'] }}</p>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Keyword (Optional) -->
                    <div>
                        <label for="keyword" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.social.create.keyword_label') }}
                        </label>
                        <input type="text"
                               name="keyword"
                               id="keyword"
                               placeholder="{{ __('insights.social.create.keyword_placeholder') }}"
                               class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-600 mt-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 inline" />
                            {{ __('insights.social.create.keyword_help') }}
                        </p>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="countrySearch" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('insights.social.create.country_label') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="countrySearch"
                                   placeholder="{{ __('insights.social.create.country_placeholder') }}"
                                   autocomplete="off"
                                   value="🇮🇩 Indonesia"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="hidden" name="country_code" id="countryValue" value="ID" required>
                            <input type="hidden" name="language" id="languageValue" value="id">

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

                    <!-- Submit Button -->
                    <button type="submit" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 inline mr-2" />
                        {{ __('insights.social.create.submit') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('insights.social.create.stats_sidebar.title') }}</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ __('insights.social.create.stats_sidebar.total_searches') }}</span>
                        <span class="font-bold text-gray-900">{{ number_format($stats['total_searches'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ __('insights.social.create.stats_sidebar.insights_created') }}</span>
                        <span class="font-bold text-gray-900">{{ number_format($stats['insights_created'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Platform Info -->
            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('insights.social.create.tips.title') }}</h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 mt-0.5">•</span>
                        <span>{{ __('insights.social.create.tips.items.tiktok') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 mt-0.5">•</span>
                        <span>{{ __('insights.social.create.tips.items.twitter') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 mt-0.5">•</span>
                        <span>{{ __('insights.social.create.tips.items.google') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 mt-0.5">•</span>
                        <span>{{ __('insights.social.create.tips.items.cache') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Country autocomplete
const countrySearch = document.getElementById('countrySearch');
const countryDropdown = document.getElementById('countryDropdown');
const countryValue = document.getElementById('countryValue');
const languageValue = document.getElementById('languageValue');
const languageMap = (() => {
    const entries = @json($languages ?? []);
    const map = {};
    entries.forEach(item => {
        const parts = (item.code || '').split('_');
        if (parts.length === 2) {
            map[parts[1].toUpperCase()] = parts[0]; // country part -> lang prefix
        }
    });
    return map;
})();

// set default language based on initial country
languageValue.value = languageMap[countryValue.value] || languageValue.value;

countrySearch.addEventListener('focus', () => {
    countryDropdown.classList.remove('hidden');
});

countrySearch.addEventListener('blur', () => {
    setTimeout(() => countryDropdown.classList.add('hidden'), 200);
});

countrySearch.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const options = document.querySelectorAll('.country-option');

    options.forEach(option => {
        const name = option.dataset.name;
        if (name.includes(query)) {
            option.classList.remove('hidden');
        } else {
            option.classList.add('hidden');
        }
    });
});

function selectCountry(code, name, flag) {
    countrySearch.value = flag + ' ' + name;
    countryValue.value = code;
    languageValue.value = languageMap[code] || languageValue.value;
    countryDropdown.classList.add('hidden');
}
</script>
@endsection
