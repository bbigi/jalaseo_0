@extends('layouts.dashboard')

@section('title', isset($website) ? __('websites.form.title_edit') . ' - AneWP' : __('websites.form.title_add') . ' - AneWP')

@section('content')
<!-- Top Bar -->
<div class="bg-white border-b border-gray-200 px-8 py-4">
    <h2 class="text-2xl font-bold text-gray-900">{{ isset($website) ? __('websites.form.title_edit') : __('websites.form.title_add') }}</h2>
    <p class="text-gray-600 text-sm mt-1">{{ isset($website) ? __('websites.form.subtitle_edit') : __('websites.form.subtitle_add') }}</p>
</div>

<!-- Content -->
<div class="p-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form (2 columns) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
                        <h6 class="font-semibold mb-2">{{ __('websites.form.errors') }}</h6>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ isset($website) ? route('websites.update', $website) : route('websites.store') }}">
                    @csrf
                    @if (isset($website))
                        @method('PUT')
                    @endif

                    <!-- Website Name -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            {{ __('websites.form.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $website->name ?? '') }}"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                               placeholder="{{ __('websites.form.name_placeholder') }}"
                               required>
                        <p class="text-gray-600 text-sm mt-2">{{ __('websites.form.name_help') }}</p>
                    </div>

                    <!-- WordPress URL -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            {{ __('websites.form.url') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="url"
                               name="url"
                               value="{{ old('url', $website->url ?? '') }}"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                               placeholder="{{ __('websites.form.url_placeholder') }}"
                               required>
                        <p class="text-gray-600 text-sm mt-2">{{ __('websites.form.url_help') }}</p>
                    </div>

                    <!-- WordPress Username -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            {{ __('websites.form.username') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="username"
                               value="{{ old('username', $website->username ?? '') }}"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                               placeholder="{{ __('websites.form.username_placeholder') }}"
                               required>
                        <p class="text-gray-600 text-sm mt-2">{{ __('websites.form.username_help') }}</p>
                    </div>

                    <!-- Application Password -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            {{ __('websites.form.password') }}
                            @if (!isset($website))<span class="text-red-500">*</span>@endif
                        </label>
                        <input type="password"
                               name="app_password"
                               class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                               placeholder="{{ __('websites.form.password_placeholder') }}"
                               @if (!isset($website)) required @endif>
                        <p class="text-gray-600 text-sm mt-2">
                            @if (isset($website))
                                {{ __('websites.form.password_help_edit') }}
                            @else
                                {{ __('websites.form.password_help_new') }}
                            @endif
                        </p>
                    </div>

                    <!-- Website Category -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            {{ __('websites.form.category') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="category"
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                                required>
                            <option value="">{{ __('websites.form.category_placeholder') }}</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $website->category ?? '') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status (only on edit) -->
                    @if (isset($website))
                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">
                                {{ __('websites.form.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                                    required>
                                <option value="active" @selected(old('status', $website->status) === 'active')>{{ __('websites.form.status_active') }}</option>
                                <option value="inactive" @selected(old('status', $website->status) === 'inactive')>{{ __('websites.form.status_inactive') }}</option>
                            </select>
                        </div>

                        <!-- Test Connection Button -->
                        <div class="mb-6">
                            <button type="button"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition inline-flex items-center gap-2"
                                    onclick="testConnection({{ $website->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                </svg>
                                {{ __('websites.form.test_connection') }}
                            </button>
                            <span id="connectionStatus-{{ $website->id }}" class="ml-3"></span>
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="flex gap-3">
                        <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
                            {{ isset($website) ? __('websites.form.actions.save_edit') : __('websites.form.actions.save_add') }}
                        </button>
                        <a href="{{ route('websites.index') }}"
                           class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition">
                            {{ __('common.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Guide (1 column) -->
        <div class="space-y-6">
            <!-- Setup Guide -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-gray-900">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    {{ __('websites.form.guide.title') }}
                </h3>
                <h4 class="font-semibold mb-2 text-sm text-gray-900">{{ __('websites.form.guide.subtitle') }}</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 mb-4">
                    @foreach(__('websites.form.guide.steps') as $step)
                        <li>{!! $step !!}</li>
                    @endforeach
                </ol>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h4 class="font-semibold mb-2 text-sm text-gray-900">{{ __('websites.form.guide.notes_title') }}</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        @foreach(__('websites.form.guide.notes') as $note)
                            <li>✓ {{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Help Card -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-gray-900">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('websites.form.guide.help_title') }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    <strong class="text-gray-900">{{ __('websites.form.guide.help_title') }}</strong> {{ __('websites.form.guide.help_wp') }}
                </p>
                <p class="text-sm text-gray-600">
                    {{ __('websites.form.guide.help_api') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testConnection(websiteId) {
    const button = event.target.closest('button');
    const statusSpan = document.getElementById('connectionStatus-' + websiteId);

    button.disabled = true;
    statusSpan.innerHTML = '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm">⏳ Testing...</span>';

    fetch(`/websites/${websiteId}/test-connection`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSpan.innerHTML = '<span class="px-3 py-1 bg-green-100 text-green-700 rounded text-sm">✓ Connected</span>';
        } else {
            statusSpan.innerHTML = '<span class="px-3 py-1 bg-red-100 text-red-700 rounded text-sm">✗ Failed: ' + data.message + '</span>';
        }
    })
    .catch(error => {
        statusSpan.innerHTML = '<span class="px-3 py-1 bg-red-100 text-red-700 rounded text-sm">✗ Error: ' + error.message + '</span>';
    })
    .finally(() => {
        button.disabled = false;
    });
}
</script>
@endpush
@endsection
