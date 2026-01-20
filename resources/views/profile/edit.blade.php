@extends('layouts.dashboard')

@section('title', 'Profile - Preferences')

@section('content')
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('profile.title') }}</h1>
            <p class="text-gray-600 mt-1 text-sm">{{ __('profile.subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="locale_display" class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <input
                            id="locale_display"
                            name="locale_display"
                            list="locale-options"
                            class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                            value=""
                            placeholder="{{ __('profile.language_placeholder') }}"
                            data-pref-input
                        >
                        <input type="hidden" id="locale" name="locale" value="{{ old('locale', $user->locale ?? 'en') }}">
                        <datalist id="locale-options">
                            @foreach($languages as $lang)
                                <option value="{{ $lang['name'] }}" data-code="{{ $lang['code'] }}">{{ $lang['flag'] }} {{ $lang['name'] }}</option>
                            @endforeach
                        </datalist>
                        @error('locale')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('profile.timezone') }}</label>
                        <input
                            id="timezone"
                            name="timezone"
                            list="timezone-options"
                            class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                            value="{{ old('timezone', $user->timezone ?? 'UTC') }}"
                            placeholder="{{ __('profile.timezone_placeholder') }}"
                            data-pref-input
                        >
                        <datalist id="timezone-options">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </datalist>
                        @error('timezone')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="md:w-1/2">
                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('profile.country') }}</label>
                    <input
                        id="country_display"
                        name="country_display"
                        list="country-options"
                        class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                        value=""
                        placeholder="{{ __('profile.country_placeholder') }}"
                        data-pref-input
                    >
                    <input type="hidden" id="country_code" name="country_code" value="{{ old('country_code', $user->country_code) }}">
                    <datalist id="country-options">
                        <option value="">--</option>
                        @foreach($countries as $country)
                            <option value="{{ $country['name'] }}" data-code="{{ $country['code'] }}">{{ $country['flag'] }} {{ $country['name'] }}</option>
                        @endforeach
                    </datalist>
                    @error('country_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        {{ __('profile.save') }}
                    </button>
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 transition">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Prefill display language with readable name
    const languages = @json($languages);
    const languageMapFull = languages.reduce((acc, l) => ({ ...acc, [l.code]: l.name }), {});
    // Preserve the first occurrence for each short code (en -> English US, etc.)
    const languageMapShort = languages.reduce((acc, l) => {
        const short = l.code.substring(0,2);
        if (!acc[short]) acc[short] = l.name;
        return acc;
    }, {});
    const countries = @json($countries);
    const countryMap = countries.reduce((acc, c) => ({ ...acc, [c.code]: c.name }), {});
    const countryHidden = document.getElementById('country_code');
    const countryDisplay = document.getElementById('country_display');

    const localeHidden = document.getElementById('locale');
    const localeDisplay = document.getElementById('locale_display');
    const currentCode = localeHidden.value;
    if (languageMapFull[currentCode]) {
        localeDisplay.value = languageMapFull[currentCode];
    } else if (languageMapShort[currentCode]) {
        localeDisplay.value = languageMapShort[currentCode];
    }

    // Sync display -> hidden code
    localeDisplay.addEventListener('change', () => {
        const value = localeDisplay.value;
        const fullMatch = Object.entries(languageMapFull).find(([, name]) => name === value);
        const shortMatch = Object.entries(languageMapShort).find(([, name]) => name === value);
        if (fullMatch) {
            localeHidden.value = fullMatch[0].substring(0,2);
        } else if (shortMatch) {
            localeHidden.value = shortMatch[0];
        }
    });

    // Prefill country display
    if (countryHidden.value && countryMap[countryHidden.value]) {
        countryDisplay.value = countryMap[countryHidden.value];
    }

    countryDisplay.addEventListener('change', () => {
        const value = countryDisplay.value;
        const match = Object.entries(countryMap).find(([, name]) => name === value);
        if (match) {
            countryHidden.value = match[0];
        }
    });

    document.querySelectorAll('[data-pref-input]').forEach((input) => {
        input.addEventListener('focus', () => {
            input.select();
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            }
        });
    });
});
</script>
@endpush
