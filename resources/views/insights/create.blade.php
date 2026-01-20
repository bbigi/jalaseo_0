@extends('layouts.dashboard')

@section('title', __('insights.create.title') . ' - AneWP')

@section('content')
<form action="{{ route('insights.store') }}" method="POST" id="insightForm">
    @csrf
    <input type="hidden" name="source_type" value="manual">

    <div class="flex h-screen overflow-hidden">
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto bg-white content-no-scroll">
            <div class="max-w-4xl mx-auto p-8">
                <div class="mb-8">
                    <a href="{{ route('insights.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        {{ __('insights.create.back') }}
                    </a>
                    <h1 class="text-3xl font-semibold text-gray-900 mt-4">{{ __('insights.create.heading') }}</h1>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2">{{ __('insights.create.topic') }} *</label>
                    <input type="text" name="topic" value="{{ old('topic') }}" placeholder="{{ __('insights.create.topic_placeholder') }}"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                           required>
                    @error('topic')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2">{{ __('insights.create.description') }}</label>
                    <textarea name="description" rows="6"
                              placeholder="{{ __('insights.create.description_placeholder') }}"
                              class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Source Information -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-bold mb-4">{{ __('insights.create.source_info') }}</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">{{ __('insights.create.source_name') }}</label>
                        <input type="text" name="source" value="{{ old('source', 'Manual Entry') }}"
                               placeholder="{{ __('insights.create.source_name_placeholder') }}"
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        @error('source')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">{{ __('insights.create.source_url') }}</label>
                        <input type="url" name="source_url" value="{{ old('source_url') }}"
                               placeholder="{{ __('insights.create.source_url_placeholder') }}"
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        @error('source_url')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="w-80 space-y-6 h-screen overflow-y-auto p-6 sidebar-no-scroll">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    {{ __('insights.create.quick_actions.title') }}
                </h3>
                <p class="text-xs text-gray-500 mb-3">{{ __('insights.create.quick_actions.subtitle') }}</p>
                <button type="button" class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    {{ __('insights.create.quick_actions.button') }}
                </button>
            </div>

            <!-- Website Context -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    {{ __('insights.create.website.title') }}
                </h3>
                <select name="website_id" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">{{ __('insights.create.website.placeholder') }}</option>
                    @foreach(\App\Models\Website::where('created_by', auth()->id())->get() as $website)
                        <option value="{{ $website->id }}" {{ old('website_id') == $website->id ? 'selected' : '' }}>
                            {{ $website->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-2">{{ __('insights.create.website.help') }}</p>
            </div>

            <!-- Status Info -->
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                <h3 class="text-sm font-bold mb-2 text-blue-900 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('insights.create.about.title') }}
                </h3>
                <p class="text-xs text-blue-800">{{ __('insights.create.about.body') }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button type="submit"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('insights.create.actions.create') }}
                </button>
                <a href="{{ route('insights.index') }}"
                   class="block w-full px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-lg transition text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('insights.create.actions.cancel') }}
                </a>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
    /* Hide sidebar scrollbar */
    .sidebar-no-scroll {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }

    .sidebar-no-scroll::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }

    /* Hide content area scrollbar */
    .content-no-scroll {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }

    .content-no-scroll::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
</style>
@endpush
@endsection
