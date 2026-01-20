@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Progress Steps -->
        <div class="bg-white mb-8">
            <div class="space-y-3">
                <div class="flex items-center" id="step1">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-900">Preparing AI model</p>
                        <p class="text-xs text-gray-500">{{ $provider === 'auto' ? 'Auto-selecting provider' : $provider }}</p>
                    </div>
                </div>

                <div class="flex items-center" id="step2">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-500">Processing prompt</p>
                        <p class="text-xs text-gray-400">Analyzing and optimizing...</p>
                    </div>
                </div>

                <div class="flex items-center" id="step3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-500">Generating image</p>
                        <p class="text-xs text-gray-400">This may take 30-60 seconds</p>
                    </div>
                </div>

                <div class="flex items-center" id="step4">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-500">Saving to media library</p>
                        <p class="text-xs text-gray-400">Almost done...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Preview -->
        <div class="bg-white py-4">
            <div class="mb-6 pb-4 border-b border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Generating Image...
                </h2>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span>Style: <strong>{{ $image_style }}</strong></span>
                    <span>•</span>
                    <span>Size: <strong>{{ $image_size }}</strong></span>
                </div>
            </div>

            <!-- Image container -->
            <div id="imageContainer" class="hidden bg-gray-50 rounded-lg p-4 border-2 border-gray-200">
                <img id="generatedImage" src="" alt="Generated Image" class="w-full h-auto rounded-lg shadow-lg">
                <div class="mt-4 text-center">
                    <a href="{{ route('media.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-sm">
                        <x-heroicon-o-check class="w-5 h-5" />
                        View in Media Library
                    </a>
                </div>
            </div>

            <!-- Loading placeholder -->
            <div id="loadingPlaceholder" class="bg-gray-100 rounded-lg flex items-center justify-center" style="aspect-ratio: 1/1; max-height: 512px;">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 font-medium">Generating your image...</p>
                    <p class="text-gray-500 text-sm mt-2">This usually takes 30-60 seconds</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Start generation on page load
window.addEventListener('load', function() {
    generateImage();
});

function generateImage() {
    updateStep(2, 'in-progress');

    fetch('{{ route("image-generation.run") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateStep(2, 'completed');
            updateStep(3, 'completed');
            updateStep(4, 'completed');

            // Show generated image
            document.getElementById('loadingPlaceholder').classList.add('hidden');
            document.getElementById('imageContainer').classList.remove('hidden');
            document.getElementById('generatedImage').src = data.image_url;

            console.log('Image generated successfully with provider:', data.provider);
        } else {
            throw new Error(data.error || 'Image generation failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
        window.location.href = '{{ route("image-generation.create") }}';
    });
}

function updateStep(stepNumber, status) {
    const step = document.getElementById('step' + stepNumber);
    const circle = step.querySelector('.flex-shrink-0');
    const text = step.querySelector('.font-medium');
    const subtext = step.querySelector('.text-gray-400, .text-gray-500');

    if (status === 'in-progress') {
        circle.innerHTML = '<div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>';
        circle.className = 'flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center';
        text.classList.remove('text-gray-500');
        text.classList.add('text-gray-900');
    } else if (status === 'completed') {
        circle.innerHTML = '<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        circle.className = 'flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center';
        text.classList.remove('text-gray-500');
        text.classList.add('text-gray-900');
        if (subtext) {
            subtext.classList.remove('text-gray-400');
            subtext.classList.add('text-gray-500');
        }
    }
}
</script>
@endsection
