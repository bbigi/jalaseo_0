@extends('admin.layout')

@section('content')
<div class="max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-100">General Settings</h1>
        <p class="text-gray-400 mt-2">Configure default AI providers and fallback chains for text and image generation</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <!-- Default Text AI (Article Generation) -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-100 mb-1">
                    <x-heroicon-o-document-text class="w-5 h-5 inline text-blue-400" />
                    Default Text AI (Article Generation)
                </h3>
                <p class="text-sm text-gray-400">
                    Drag to reorder. First provider will be used by default, others are fallbacks if the first one fails or is busy.
                </p>
            </div>

            <div id="textAIList" class="space-y-2">
                @forelse($defaultTextAI as $index => $provider)
                    @php
                        $providerData = $allTextAIProviders->firstWhere('value', $provider);
                    @endphp
                    @if($providerData)
                    <div class="flex items-center gap-3 p-3 bg-gray-700 rounded-lg cursor-move hover:bg-gray-600 transition"
                         data-provider="{{ $provider }}">
                        <x-heroicon-o-bars-3 class="w-5 h-5 text-gray-400" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-100">{{ $providerData['label'] }}</p>
                            <p class="text-xs text-gray-400">Provider: {{ $provider }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-blue-900 text-blue-200 text-xs font-semibold rounded">
                                {{ $index === 0 ? 'Primary' : 'Fallback ' . $index }}
                            </span>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center py-8 bg-gray-700 rounded-lg border-2 border-dashed border-gray-600">
                        <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-yellow-500 mx-auto mb-3" />
                        <p class="text-gray-300 font-medium mb-1">No Text AI Providers Configured</p>
                        <p class="text-gray-400 text-sm mb-4">Add API credentials in API Keys menu first</p>
                        <a href="{{ route('admin.api-keys.create') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Add API Key
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Hidden inputs for text AI -->
            <div id="textAIInputs"></div>
        </div>

        <!-- Default Image AI -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-100 mb-1">
                    <x-heroicon-o-photo class="w-5 h-5 inline text-purple-400" />
                    Default Image AI (Image Generation)
                </h3>
                <p class="text-sm text-gray-400">
                    Drag to reorder. First provider will be used by default, others are fallbacks if the first one fails or is busy.
                </p>
            </div>

            <div id="imageAIList" class="space-y-2">
                @forelse($defaultImageAI as $index => $provider)
                    @php
                        $providerData = $allImageAIProviders->firstWhere('value', $provider);
                    @endphp
                    @if($providerData)
                    <div class="flex items-center gap-3 p-3 bg-gray-700 rounded-lg cursor-move hover:bg-gray-600 transition"
                         data-provider="{{ $provider }}">
                        <x-heroicon-o-bars-3 class="w-5 h-5 text-gray-400" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-100">{{ $providerData['label'] }}</p>
                            <p class="text-xs text-gray-400">Provider: {{ $provider }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-purple-900 text-purple-200 text-xs font-semibold rounded">
                                {{ $index === 0 ? 'Primary' : 'Fallback ' . $index }}
                            </span>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center py-8 bg-gray-700 rounded-lg border-2 border-dashed border-gray-600">
                        <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-yellow-500 mx-auto mb-3" />
                        <p class="text-gray-300 font-medium mb-1">No Image AI Providers Configured</p>
                        <p class="text-gray-400 text-sm mb-4">Add API credentials in API Keys menu first</p>
                        <a href="{{ route('admin.api-keys.create') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Add API Key
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Hidden inputs for image AI -->
            <div id="imageAIInputs"></div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
            <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition shadow-lg">
                <x-heroicon-o-check class="w-5 h-5 inline" />
                Save Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Text AI Sortable
    const textAIList = document.getElementById('textAIList');
    new Sortable(textAIList, {
        animation: 150,
        handle: '.cursor-move',
        onEnd: function() {
            updateTextAIInputs();
            updateLabels(textAIList);
        }
    });

    // Image AI Sortable
    const imageAIList = document.getElementById('imageAIList');
    new Sortable(imageAIList, {
        animation: 150,
        handle: '.cursor-move',
        onEnd: function() {
            updateImageAIInputs();
            updateLabels(imageAIList);
        }
    });

    // Update labels (Primary, Fallback 1, Fallback 2, etc.)
    function updateLabels(container) {
        const items = container.querySelectorAll('[data-provider]');
        items.forEach((item, index) => {
            const label = item.querySelector('.px-2');
            if (index === 0) {
                label.textContent = 'Primary';
                label.className = 'px-2 py-1 bg-blue-900 text-blue-200 text-xs font-semibold rounded';
            } else {
                label.textContent = 'Fallback ' + index;
                label.className = 'px-2 py-1 bg-gray-600 text-gray-300 text-xs font-semibold rounded';
            }
        });
    }

    // Update hidden inputs for text AI
    function updateTextAIInputs() {
        const container = document.getElementById('textAIInputs');
        container.innerHTML = '';

        const items = textAIList.querySelectorAll('[data-provider]');
        items.forEach((item) => {
            const provider = item.getAttribute('data-provider');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'default_text_ai[]';
            input.value = provider;
            container.appendChild(input);
        });
    }

    // Update hidden inputs for image AI
    function updateImageAIInputs() {
        const container = document.getElementById('imageAIInputs');
        container.innerHTML = '';

        const items = imageAIList.querySelectorAll('[data-provider]');
        items.forEach((item) => {
            const provider = item.getAttribute('data-provider');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'default_image_ai[]';
            input.value = provider;
            container.appendChild(input);
        });
    }

    // Initialize inputs on page load
    updateTextAIInputs();
    updateImageAIInputs();
    updateLabels(textAIList);
    updateLabels(imageAIList);
});
</script>
@endpush
@endsection
