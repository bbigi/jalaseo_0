@extends('layouts.dashboard')

@section('title', 'Branding Presets - AneWP')

@section('content')
<!-- Top Bar -->
<div class="px-8 py-4 pl-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Branding Presets</h2>
            <p class="text-gray-600 text-sm mt-1">Manage your brand identities</p>
        </div>
        <a href="{{ route('brandings.create') }}"
           class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
            + Create Preset
        </a>
    </div>
</div>

<!-- Content -->
<div class="p-8">
    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($brandings->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center shadow-sm">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
            <p class="text-gray-700 text-lg mb-4">No branding presets yet</p>
            <p class="text-gray-500 mb-6">Create your first branding preset to maintain consistency!</p>
            <a href="{{ route('brandings.create') }}"
               class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
                + Create First Preset
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($brandings as $branding)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition">
                    <!-- Logo Preview -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-8 flex items-center justify-center h-48">
                        @if ($branding->logo)
                            <img src="{{ asset('storage/' . $branding->logo->file_path) }}"
                                 alt="{{ $branding->preset_name }}"
                                 class="max-h-32 max-w-full object-contain">
                        @else
                            <div class="text-gray-400 text-center">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">No logo</p>
                            </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $branding->preset_name }}</h3>

                        @if ($branding->website)
                            <p class="text-sm text-gray-600 mb-3">
                                <x-heroicon-o-globe-alt class="w-4 h-4 inline" />
                                {{ $branding->website->name }}
                            </p>
                        @endif

                        <!-- Colors -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded border border-gray-300"
                                     style="background-color: {{ $branding->primary_color }}"></div>
                                <span class="text-xs text-gray-500">Primary</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded border border-gray-300"
                                     style="background-color: {{ $branding->secondary_color }}"></div>
                                <span class="text-xs text-gray-500">Secondary</span>
                            </div>
                        </div>

                        <!-- Font -->
                        <p class="text-sm text-gray-600 mb-4">
                            <x-heroicon-o-document-text class="w-4 h-4 inline" />
                            Font: <span class="font-semibold">{{ $branding->default_font }}</span>
                        </p>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('brandings.edit', $branding->id) }}"
                               class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg text-center transition">
                                Edit
                            </a>
                            <form action="{{ route('brandings.destroy', $branding->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this branding preset?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if ($brandings->hasPages())
            <div class="mt-8">
                {{ $brandings->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
