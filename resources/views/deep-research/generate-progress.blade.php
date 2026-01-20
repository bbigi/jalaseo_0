@extends('layouts.dashboard')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-heroicon-s-sparkles class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Generating Article</h1>
            <p class="text-gray-600 mt-2">Deep Research powered content generation</p>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">Keyword</p>
                <p class="font-semibold text-gray-900 truncate">{{ $keyword }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">Template</p>
                <p class="font-semibold text-gray-900 truncate">{{ $template->name }}</p>
            </div>
        </div>

        <!-- Title Preview -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-8">
            <p class="text-xs text-emerald-600 mb-1">Article Title</p>
            <p class="font-semibold text-emerald-800">{{ $title }}</p>
        </div>

        @if(!empty($brandContext))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
            <p class="text-xs text-blue-600 mb-1">Brand Context</p>
            <p class="text-sm text-blue-800">{{ $brandContext }}</p>
        </div>
        @endif

        <!-- Progress Steps -->
        <div class="space-y-4 mb-8">
            <div class="flex items-center gap-3" id="step1">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-4 h-4 text-emerald-600" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Building optimized prompt</p>
                    <p class="text-xs text-gray-500">Including Deep Research insights</p>
                </div>
                <div id="step1-status" class="text-emerald-500">
                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-3" id="step2">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                    <x-heroicon-o-cpu-chip class="w-4 h-4 text-gray-400" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Generating with AI</p>
                    <p class="text-xs text-gray-400">{{ ucfirst($template->ai_provider ?? 'AI') }} • {{ $template->article_size ?? 'medium' }}</p>
                </div>
                <div id="step2-status" class="text-gray-300">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
            </div>

            <div class="flex items-center gap-3" id="step3">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-gray-400" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Saving article</p>
                    <p class="text-xs text-gray-400">Creating draft post</p>
                </div>
                <div id="step3-status" class="text-gray-300">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
            <div id="progressBar" class="bg-gradient-to-r from-emerald-500 to-teal-600 h-2 rounded-full transition-all duration-500" style="width: 10%"></div>
        </div>
        <p id="progressText" class="text-center text-sm text-gray-600">Initializing...</p>

        <!-- Error Box (hidden by default) -->
        <div id="errorBox" class="hidden mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-red-800">
                <x-heroicon-o-exclamation-circle class="w-5 h-5" />
                <p class="font-medium">Generation Failed</p>
            </div>
            <p id="errorMessage" class="text-sm text-red-700 mt-2"></p>
            <div class="mt-4">
                <a href="{{ route('deep-research.show', $session->session_id) }}" class="text-red-600 hover:underline text-sm">← Back to Deep Research</a>
            </div>
        </div>

        <!-- Success Box (hidden by default) -->
        <div id="successBox" class="hidden mt-6 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-emerald-800 mb-2">
                <x-heroicon-s-check-circle class="w-5 h-5" />
                <p class="font-medium">Article Generated Successfully!</p>
            </div>
            <p id="successMessage" class="text-sm text-emerald-700"></p>
            <div class="mt-4 flex gap-3">
                <a id="editLink" href="#" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
                    Edit Article
                </a>
                <a href="{{ route('deep-research.show', $session->session_id) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                    Back to Research
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Start generation
    generateArticle();
});

function updateStep(stepNum, status) {
    const stepEl = document.getElementById('step' + stepNum);
    const statusEl = document.getElementById('step' + stepNum + '-status');
    
    if (!stepEl || !statusEl) return;
    
    const iconWrapper = stepEl.querySelector('.w-8');
    const stepTitle = stepEl.querySelector('p.text-sm');
    
    if (status === 'active') {
        if (iconWrapper) {
            iconWrapper.classList.remove('bg-gray-100');
            iconWrapper.classList.add('bg-emerald-100');
        }
        if (stepTitle) {
            stepTitle.classList.remove('text-gray-500');
            stepTitle.classList.add('text-gray-900');
        }
        statusEl.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        statusEl.classList.remove('text-gray-300');
        statusEl.classList.add('text-emerald-500');
    } else if (status === 'done') {
        if (iconWrapper) {
            iconWrapper.classList.remove('bg-gray-100', 'bg-emerald-100');
            iconWrapper.classList.add('bg-emerald-500');
        }
        if (stepTitle) {
            stepTitle.classList.remove('text-gray-500');
            stepTitle.classList.add('text-gray-900');
        }
        statusEl.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
        statusEl.classList.remove('text-gray-300');
        statusEl.classList.add('text-emerald-500');
    }
}

function generateArticle() {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    // Step 1: Building prompt
    progressBar.style.width = '20%';
    progressText.textContent = 'Building optimized prompt with Deep Research insights...';
    
    setTimeout(() => {
        updateStep(1, 'done');
        updateStep(2, 'active');
        progressBar.style.width = '40%';
        progressText.textContent = 'Generating article with AI...';
        
        // Make API call
        fetch('{{ route("deep-research.generate-article") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_id: '{{ $session->session_id }}',
                template_id: {{ $template->id }},
                keyword: @json($keyword),
                title: @json($title),
                brand_context: @json($brandContext ?? ''),
                additional_notes: @json($additionalNotes ?? '')
            })
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error('Parse error:', text.substring(0, 500));
                throw new Error('Invalid response from server');
            }
        })
        .then(data => {
            if (data.success) {
                // Step 2 done
                updateStep(2, 'done');
                updateStep(3, 'active');
                progressBar.style.width = '80%';
                progressText.textContent = 'Saving article...';
                
                setTimeout(() => {
                    updateStep(3, 'done');
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Complete!';
                    
                    // Show success
                    document.getElementById('successBox').classList.remove('hidden');
                    document.getElementById('successMessage').textContent = 
                        `"${data.title}" - ${data.word_count} words`;
                    document.getElementById('editLink').href = data.redirect_url;
                }, 500);
            } else {
                throw new Error(data.error || 'Generation failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            progressBar.classList.remove('bg-gradient-to-r', 'from-emerald-500', 'to-teal-600');
            progressBar.classList.add('bg-red-500');
            progressText.textContent = 'Generation failed';
            
            document.getElementById('errorBox').classList.remove('hidden');
            document.getElementById('errorMessage').textContent = error.message;
        });
        
    }, 1000);
}
</script>
@endpush
