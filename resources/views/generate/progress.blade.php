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
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Generating Article</h1>
            <p class="text-sm text-gray-600">Please wait while AI creates your content...</p>
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
                        <p class="text-xs font-medium text-gray-900">Preparing template</p>
                        <p class="text-xs text-gray-500">{{ $template->name ?? 'AI Template' }}</p>
                    </div>
                </div>

                <div class="flex items-center opacity-50" id="step2">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center">
                        <span class="text-xs text-gray-500">2</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-700">Generating content with AI</p>
                        <p class="text-xs text-gray-500">Using {{ ucfirst($template->ai_provider ?? 'AI') }}</p>
                    </div>
                </div>

                <div class="flex items-center opacity-50" id="step3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center">
                        <span class="text-xs text-gray-500">3</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-xs font-medium text-gray-700">Saving to database</p>
                        <p class="text-xs text-gray-500">Creating post entry</p>
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
                        <span>Keyword: <strong>{{ $keyword }}</strong></span>
                        <span>•</span>
                        <span id="wordCount">0 words</span>
                    </div>
                    <button onclick="skipToEditor()" id="skipButtonHeader" class="hidden px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md transition shadow-sm">
                        Skip animation →
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

            <span class="inline-block w-2 h-5 bg-blue-600 ml-1 animate-pulse" id="cursor"></span>
        </div>

        <!-- Cancel Button -->
        <div class="mt-6 text-center">
            <a href="{{ route('generate.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">
                Cancel and go back
            </a>
        </div>
    </div>
</div>

<script>
let currentPostId = null;
let titleInterval = null;
let contentInterval = null;
let pollTimer = null;

function activateStep(step) {
    const stepEl = document.getElementById('step' + step);
    if (!stepEl || !stepEl.classList.contains('opacity-50')) {
        return;
    }

    stepEl.classList.remove('opacity-50');
    const icon = stepEl.querySelector('.flex-shrink-0');
    icon.classList.remove('border-gray-300');
    icon.classList.add('bg-blue-600');
    icon.innerHTML = `<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>`;
}

function updateSteps(progress) {
    if (progress >= 40) {
        activateStep(2);
    }
    if (progress >= 90) {
        activateStep(3);
    }
}

function showError(message) {
    document.getElementById('articleContent').innerHTML =
        '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">' +
        '<strong>Error:</strong> ' + message +
        '</div>';
    document.getElementById('cursor').style.display = 'none';
}

function pollProgress(jobId) {
    pollTimer = setInterval(async () => {
        try {
            const response = await fetch(`/generate/progress/${jobId}`);
            if (!response.ok) {
                throw new Error('Progress not found');
            }

            const data = await response.json();
            updateSteps(data.progress || 0);

            if (data.status === 'failed') {
                clearInterval(pollTimer);
                showError(data.error || 'Failed to generate article');
                return;
            }

            if (data.status === 'completed') {
                clearInterval(pollTimer);
                const result = data.result || {};
                if (!result.content) {
                    showError('Article content is empty.');
                    return;
                }

                activateStep(3);
                typeWriter(result.title || '{{ $title }}', result.content, result.post_id || null);
            }
        } catch (error) {
            clearInterval(pollTimer);
            showError('Network error occurred. Please try again.');
        }
    }, 2000);
}

function skipToEditor() {
    if (currentPostId) {
        if (titleInterval) clearInterval(titleInterval);
        if (contentInterval) clearInterval(contentInterval);
        window.location.href = '/posts/' + currentPostId + '/edit';
    }
}

function typeWriter(title, content, postId) {
    const titleEl = document.getElementById('articleTitle');
    const contentEl = document.getElementById('articleContent');
    const cursor = document.getElementById('cursor');
    const wordCountEl = document.getElementById('wordCount');

    currentPostId = postId;
    document.getElementById('skipButtonHeader').classList.remove('hidden');

    let titleIndex = 0;
    let contentIndex = 0;

    titleInterval = setInterval(() => {
        if (titleIndex < title.length) {
            titleEl.textContent = title.substring(0, titleIndex + 1);
            titleIndex++;
        } else {
            clearInterval(titleInterval);

            contentInterval = setInterval(() => {
                if (contentIndex < content.length) {
                    contentEl.innerHTML = content.substring(0, contentIndex + 1);
                    contentIndex++;

                    const words = contentEl.innerText.trim().split(/\s+/).filter(Boolean).length;
                    wordCountEl.textContent = words + ' words';
                } else {
                    clearInterval(contentInterval);
                    cursor.style.display = 'none';
                    activateStep(3);

                    if (currentPostId) {
                        setTimeout(() => {
                            window.location.href = '/posts/' + currentPostId + '/edit';
                        }, 1500);
                    }
                }
            }, 10);
        }
    }, 50);
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const jobId = urlParams.get('job_id') || @json($jobId);

    if (!jobId) {
        showError('Job ID not found.');
        return;
    }

    pollProgress(jobId);
});
</script>
@endsection
