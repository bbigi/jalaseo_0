@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">AI Image Generator</h1>
        <p class="text-gray-600 mt-2">Generate high-quality images using AI based on your prompt</p>
    </div>

    <form action="{{ route('image-generation.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Prompt -->
        <div>
            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 inline text-indigo-600" />
                Image Prompt *
            </label>
            <textarea
                name="prompt"
                id="prompt"
                rows="4"
                required
                maxlength="3000"
                placeholder="Describe the image you want to generate... (e.g., 'A futuristic city at sunset with flying cars')"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('prompt') }}</textarea>
            <p class="text-xs text-gray-500 mt-1"><span id="promptCounter">0</span>/3000 characters</p>
            @error('prompt')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- AI Provider -->
        <div>
            <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-cpu-chip class="w-5 h-5 inline text-purple-600" />
                AI Provider
            </label>
            <select
                name="provider"
                id="provider"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Auto (use default)</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider['value'] }}" {{ old('provider') == $provider['value'] ? 'selected' : '' }}>
                        {{ $provider['label'] }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Leave as "Auto" to use the default AI provider from settings</p>
        </div>

        <!-- Image Style -->
        <div>
            <label for="image_style" class="block text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-paint-brush class="w-5 h-5 inline text-pink-600" />
                Image Style *
            </label>
            <select
                name="image_style"
                id="image_style"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="Photo" {{ old('image_style') == 'Photo' ? 'selected' : '' }}>Photo</option>
                <option value="Cartoon" {{ old('image_style') == 'Cartoon' ? 'selected' : '' }}>Cartoon</option>
                <option value="Cubism" {{ old('image_style') == 'Cubism' ? 'selected' : '' }}>Cubism</option>
                <option value="Expressionism" {{ old('image_style') == 'Expressionism' ? 'selected' : '' }}>Expressionism</option>
                <option value="Cyberpunk" {{ old('image_style') == 'Cyberpunk' ? 'selected' : '' }}>Cyberpunk</option>
                <option value="Fantasy" {{ old('image_style') == 'Fantasy' ? 'selected' : '' }}>Fantasy</option>
                <option value="Cinematic" {{ old('image_style') == 'Cinematic' ? 'selected' : '' }}>Cinematic</option>
                <option value="Abstract" {{ old('image_style') == 'Abstract' ? 'selected' : '' }}>Abstract</option>
                <option value="Impressionism" {{ old('image_style') == 'Impressionism' ? 'selected' : '' }}>Impressionism</option>
                <option value="Surrealism" {{ old('image_style') == 'Surrealism' ? 'selected' : '' }}>Surrealism</option>
                <option value="Anime" {{ old('image_style') == 'Anime' ? 'selected' : '' }}>Anime</option>
                <option value="Comic" {{ old('image_style') == 'Comic' ? 'selected' : '' }}>Comic</option>
            </select>
            @error('image_style')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image Size -->
        <div>
            <label for="image_size" class="block text-sm font-medium text-gray-700 mb-2">
                <x-heroicon-o-arrows-pointing-out class="w-5 h-5 inline text-blue-600" />
                Image Size *
            </label>
            <select
                name="image_size"
                id="image_size"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="1080x1350 (4:5 Instagram Portrait)" {{ old('image_size') == '1080x1350 (4:5 Instagram Portrait)' ? 'selected' : '' }}>1080x1350 (4:5 Instagram Portrait)</option>
                <option value="960x768 (5:4)" {{ old('image_size') == '960x768 (5:4)' ? 'selected' : '' }}>960x768 (5:4)</option>
                <option value="1024x640 (8:5)" {{ old('image_size') == '1024x640 (8:5)' ? 'selected' : '' }}>1024x640 (8:5)</option>
                <option value="1024x768 (4:3)" {{ old('image_size') == '1024x768 (4:3)' ? 'selected' : '' }}>1024x768 (4:3)</option>
                <option value="1152x768 (3:2)" {{ old('image_size') == '1152x768 (3:2)' ? 'selected' : '' }}>1152x768 (3:2)</option>
                <option value="768x1344 (9:16)" {{ old('image_size') == '768x1344 (9:16)' ? 'selected' : '' }}>768x1344 (9:16)</option>
                <option value="1024x1024 (1:1)" {{ old('image_size') == '1024x1024 (1:1)' ? 'selected' : '' }}>1024x1024 (1:1)</option>
            </select>
            @error('image_size')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 pt-4">
            <a href="{{ route('media.index') }}"
               class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition shadow-lg">
                <x-heroicon-o-sparkles class="w-5 h-5 inline" />
                Generate Image
            </button>
        </div>
    </form>
</div>

<script>
// Prompt character counter
const promptTextarea = document.getElementById('prompt');
const promptCounter = document.getElementById('promptCounter');

promptTextarea.addEventListener('input', function() {
    promptCounter.textContent = this.value.length;
});

// Initialize counter
promptCounter.textContent = promptTextarea.value.length;
</script>
@endsection
