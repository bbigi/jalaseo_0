@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('insights.news.results.progress.title') }}</h1>
            <p class="text-sm text-gray-600">{{ __('insights.news.results.progress.subtitle') }}</p>
        </div>

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
                        <p class="text-xs font-medium text-gray-900">{{ __('insights.news.results.progress.steps.prepare') }}</p>
                        <p class="text-xs text-gray-500">{{ $template->name }}</p>
                    </div>
                </div>

                <div class="flex items-center opacity-50" id="step2">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center">
                        <span class="text-xs text-gray-500">2</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-700">{{ __('insights.news.results.progress.steps.generate') }}</p>
                        <p class="text-xs text-gray-500">{{ __('insights.news.results.progress.steps.generate_using', ['provider' => ucfirst($template->ai_provider ?? 'AI')]) }}</p>
                    </div>
                </div>

                <div class="flex items-center opacity-50" id="step3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center">
                        <span class="text-xs text-gray-500">3</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-700">{{ __('insights.news.results.progress.steps.save') }}</p>
                        <p class="text-xs text-gray-500">{{ __('insights.news.results.progress.steps.save_detail') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Article Preview (Streaming) -->
        <div class="bg-white py-4">
            <div class="mb-6 pb-4 border-b border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-2" id="articleTitle">
                    <span class="inline-block w-64 h-8 bg-gray-200 rounded animate-pulse"></span>
                </h2>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>{{ __('insights.news.results.progress.keyword') }}: <strong>{{ $keyword }}</strong></span>
                        <span>•</span>
                        <span id="wordCount">0 {{ __('insights.news.results.progress.words') }}</span>
                    </div>
                    <!-- Skip Button (Header) -->
                    <button onclick="skipToEditor()" id="skipButtonHeader" class="hidden px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md transition shadow-sm">
                        {{ __('insights.news.results.progress.skip') }}
                    </button>
                </div>
            </div>

            <div class="prose prose-lg max-w-none" id="articleContent">
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-full animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-4/6 animate-pulse"></div>
                </div>
            </div>

            <!-- Cursor blink -->
            <span class="inline-block w-2 h-5 bg-blue-600 ml-1 animate-pulse" id="cursor"></span>
        </div>

        <!-- Cancel Button -->
        <div class="mt-6 text-center">
            <a href="{{ route('generate.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                {{ __('insights.news.results.progress.cancel') }}
            </a>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let articleContent = '';
let articleTitle = '';

// Simulate streaming (replace with real AI streaming later)
function startGeneration() {
    // Step 1: Complete (already shown)
    setTimeout(() => {
        activateStep(2);

        // Start streaming content
        setTimeout(() => {
            streamArticle();
        }, 1000);
    }, 500);
}

function activateStep(step) {
    currentStep = step;
    const stepEl = document.getElementById('step' + step);

    // Remove opacity
    stepEl.classList.remove('opacity-50');

    // Update icon
    const icon = stepEl.querySelector('.flex-shrink-0');
    icon.classList.remove('border-gray-300');
    icon.classList.add('bg-blue-600');
    icon.innerHTML = `<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>`;
}

function streamArticle() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const resultId = urlParams.get('result_id');
    const templateId = urlParams.get('template_id');
    const title = urlParams.get('title');
    const url = urlParams.get('url');

    // Call backend to generate
    fetch('{{ route('insights.news.generate-article') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            result_id: resultId,
            template_id: templateId,
            title: title,
            url: url
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Use real data from AI
            typeWriter(data.title || title, data.content || '', data.post_id);
        } else {
            // Show error
            document.getElementById('articleContent').innerHTML =
                '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">' +
                '<strong>' + @json(__('insights.news.results.progress.error.title')) + '</strong> ' + (data.error || @json(__('insights.news.results.progress.error.failed'))) +
                '</div>';
            document.getElementById('cursor').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Generation error:', error);
        document.getElementById('articleContent').innerHTML =
            '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">' +
            '<strong>' + @json(__('insights.news.results.progress.error.title')) + '</strong> ' + @json(__('insights.news.results.progress.error.network')) +
            '</div>';
        document.getElementById('cursor').style.display = 'none';
    });
}

let currentPostId = null;
let titleInterval = null;
let contentInterval = null;

function skipToEditor() {
    if (currentPostId) {
        // Clear intervals
        if (titleInterval) clearInterval(titleInterval);
        if (contentInterval) clearInterval(contentInterval);

        // Redirect immediately
        window.location.href = '/posts/' + currentPostId + '/edit';
    }
}

function typeWriter(title, content, postId) {
    const titleEl = document.getElementById('articleTitle');
    const contentEl = document.getElementById('articleContent');
    const cursor = document.getElementById('cursor');
    const wordCountEl = document.getElementById('wordCount');

    currentPostId = postId;

    // Show skip button in header
    document.getElementById('skipButtonHeader').classList.remove('hidden');

    let titleIndex = 0;
    let contentIndex = 0;

    // Type title
    titleInterval = setInterval(() => {
        if (titleIndex < title.length) {
            titleEl.textContent = title.substring(0, titleIndex + 1);
            titleIndex++;
        } else {
            clearInterval(titleInterval);

            // Start typing content
            contentInterval = setInterval(() => {
                if (contentIndex < content.length) {
                    contentEl.innerHTML = content.substring(0, contentIndex + 1);
                    contentIndex++;

                    // Update word count
                    const words = contentEl.innerText.trim().split(/\s+/).length;
                    wordCountEl.textContent = words + ' words';
                } else {
                    clearInterval(contentInterval);
                    cursor.style.display = 'none';

                    // Complete step 2
                    activateStep(3);

                    // Redirect after 1 second
                    setTimeout(() => {
                        window.location.href = '/posts/' + postId + '/edit';
                    }, 1500);
                }
            }, 10); // Faster typing for content
        }
    }, 50);
}

// Start generation on page load
document.addEventListener('DOMContentLoaded', () => {
    startGeneration();
});
</script>
@endsection
