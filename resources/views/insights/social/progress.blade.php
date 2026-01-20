@extends('layouts.dashboard')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <x-heroicon-o-sparkles class="w-8 h-8 text-blue-600 animate-pulse" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('insights.social.progress.title') }}</h1>
            <p class="text-gray-600">{{ __('insights.social.progress.subtitle') }}</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="progressBar" class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: 10%"></div>
            </div>
            <p id="progressText" class="text-sm text-gray-600 text-center mt-2">{{ __('insights.social.progress.initializing') }}</p>
        </div>

        <!-- Status Messages -->
        <div id="statusMessages" class="space-y-3 mb-6">
            <div class="flex items-center gap-3 text-gray-700">
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                <span>{{ __('insights.social.progress.ranking') }}</span>
            </div>
        </div>

        <div id="completionMessage" class="hidden">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 flex-shrink-0" />
                    <div>
                        <p class="font-semibold text-green-900">{{ __('insights.social.progress.complete_title') }}</p>
                        <p class="text-sm text-green-700 mt-1">{!! __('insights.social.progress.complete_subtitle', ['count' => '<span id="articleCount">0</span>']) !!}</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('posts.index') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-center">
                    {{ __('insights.social.progress.view_posts') }}
                </a>
                <a id="bulkReviewLink" href="{{ route('posts.bulk-review') }}" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                    {{ __('insights.social.progress.bulk_review') }}
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let strategyId = {{ $strategy->id }};
let pollInterval;
let progress = 10;
const textGeneratedOf = @json(__('insights.social.progress.status.generated_of', ['done' => '__DONE__', 'total' => '__TOTAL__']));
const textArticleDone = @json(__('insights.social.progress.status.article_done', ['done' => '__DONE__', 'total' => '__TOTAL__']));
const textError = @json(__('insights.social.progress.status.error', ['message' => '__MSG__']));

function updateProgress(percent, message) {
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressText').textContent = message;
}

function addStatusMessage(message, type = 'info') {
    const container = document.getElementById('statusMessages');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-3 text-gray-700';
    
    const icon = type === 'success' 
        ? '<div class="w-2 h-2 bg-green-600 rounded-full"></div>'
        : '<div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>';
    
    div.innerHTML = icon + '<span>' + message + '</span>';
    container.appendChild(div);
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

function checkProgress() {
    fetch('/api/content-strategy-status/' + strategyId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'completed') {
                clearInterval(pollInterval);
                updateProgress(100, '{{ __('insights.social.progress.status.completed') }}');

                document.getElementById('articleCount').textContent = data.articles_count;
                document.getElementById('completionMessage').classList.remove('hidden');

                // Fetch post IDs and redirect based on count
                fetch('/api/content-strategy-posts/' + strategyId)
                    .then(res => res.json())
                    .then(postsData => {
                        const postIds = postsData.post_ids;

                        // If only 1 article, redirect directly to single editor
                        if (postIds.length === 1) {
                            addStatusMessage('{{ __('insights.social.progress.status.redirect_single') }}', 'success');
                            setTimeout(() => {
                                window.location.href = '/posts/' + postIds[0] + '/edit';
                            }, 1000);
                        } else {
                            // Multiple articles, show bulk review option
                            document.getElementById('bulkReviewLink').href = '{{ route("posts.bulk-review") }}?ids=' + postIds.join(',');
                            addStatusMessage('{{ __('insights.social.progress.status.all_generated') }}', 'success');
                        }
                    });


            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                updateProgress(100, '{{ __('insights.social.progress.status.failed') }}');
                const errMsg = textError.replace('__MSG__', data.error || 'Unknown error');
                alert(errMsg);

            } else {
                // Calculate real progress based on articles count
                if (data.expected_articles > 0) {
                    const realProgress = Math.min(90, Math.round((data.articles_count / data.expected_articles) * 100));

                    // Only update if progress increased
                    if (realProgress > progress) {
                        progress = realProgress;
                        updateProgress(progress, textGeneratedOf.replace('__DONE__', data.articles_count).replace('__TOTAL__', data.expected_articles));

                        // Add status message for each new article
                        if (data.articles_count > 0) {
                            addStatusMessage(textArticleDone.replace('__DONE__', data.articles_count).replace('__TOTAL__', data.expected_articles), 'success');
                        }
                    }
                } else {
                    // Fallback to incremental progress
                    if (progress < 90) {
                        progress += 10;
                        updateProgress(progress, '{{ __('insights.social.progress.status.fallback') }}');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error checking progress:', error);
        });
}

// Start polling
setTimeout(() => {
    addStatusMessage('{{ __('insights.social.progress.status.generating_roundup') }}');
    updateProgress(30, '{{ __('insights.social.progress.status.creating_content') }}');
}, 2000);

setTimeout(() => {
    addStatusMessage('{{ __('insights.social.progress.status.generating_deep_dive') }}');
    updateProgress(60, '{{ __('insights.social.progress.status.almost_done') }}');
}, 5000);

// Poll every 3 seconds
pollInterval = setInterval(checkProgress, 3000);
</script>
@endpush
@endsection
