@extends('layouts.dashboard')

@section('title', __('posts.create.title') . ' - AneWP')

@section('content')
<form action="{{ route('posts.store') }}" method="POST" id="postForm">
    @csrf
    <input type="hidden" name="featured_media_id" id="featuredMediaId" value="">
    <input type="hidden" name="publish_action" id="publishAction" value="save_only">
    <input type="hidden" name="article_type" value="blog">

    <div class="flex h-screen overflow-hidden">
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto bg-white content-no-scroll">
            <div class="max-w-4xl mx-auto p-8">
                <div class="mb-8">
                    <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        {{ __('posts.create.back') }}
                    </a>
                    <h1 class="text-3xl font-semibold text-gray-900 mt-4">{{ __('posts.create.heading') }}</h1>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.form.title') }} *</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="{{ __('posts.create.form.title_placeholder') }}"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                           required>
                    @error('title')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="text-sm font-semibold mb-2 block">{{ __('posts.create.form.content') }} *</label>
                    <div id="quillEditor" class="border border-gray-200 rounded-lg" style="height: 500px; background: white;"></div>
                    <textarea id="contentEditor" name="content" required style="display:none;">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
                <h3 class="text-lg font-bold mb-4">{{ __('posts.create.seo.heading') }}</h3>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.seo.focus') }}</label>
                    <input type="text" name="seo_focus_keyword" value="{{ old('seo_focus_keyword') }}"
                           placeholder="{{ __('posts.create.seo.focus_placeholder') }}"
                           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.seo.meta') }}</label>
                    <textarea name="seo_meta_description" rows="3"
                              placeholder="{{ __('posts.create.seo.meta_placeholder') }}"
                              class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('seo_meta_description') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">{{ __('posts.create.seo.meta_counter') }}</p>
                </div>
            </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="w-80 space-y-6 h-screen overflow-y-auto p-6 sidebar-no-scroll">
            <!-- Generate Article -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <a href="{{ route('generate.index') }}" class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                    {{ __('posts.create.generate.button') }}
                </a>
                <p class="text-xs text-gray-500 text-center mt-2">{{ __('posts.create.generate.help') }}</p>
            </div>

            <!-- Featured Image -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('posts.create.featured.heading') }}
                </h3>
                <div id="featuredImagePreview" class="hidden mb-3">
                    <div class="relative">
                        <img id="featuredImageThumb" src="" alt="" class="w-full h-40 object-cover rounded">
                        <button type="button" onclick="removeFeaturedImage()" class="absolute top-2 right-2 w-6 h-6 bg-red-600 hover:bg-red-700 rounded-full text-gray-900 text-xs">×</button>
                    </div>
                </div>
                <button type="button" onclick="openMediaLibrary()" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition flex text-sm items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('posts.create.featured.button') }}
                </button>
                <p class="text-xs text-gray-500 mt-2">{{ __('posts.create.featured.help') }}</p>
            </div>

            <!-- Publish Settings -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('posts.create.publish.heading') }}
                </h3>

                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1 text-gray-500">{{ __('posts.create.publish.website') }}</label>
                    <select name="website_id" id="websiteSelect" class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm" required>
                        <option value="">{{ __('posts.create.publish.website_placeholder') }}</option>
                        @foreach($websites as $website)
                            <option value="{{ $website->id }}"
                                    data-category="{{ $website->category }}"
                                    {{ old('website_id') == $website->id ? 'selected' : '' }}>
                                {{ $website->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4" id="categoriesField" style="display:none;">
                    <label class="block text-xs font-semibold mb-1 text-gray-500">{{ __('posts.create.publish.category') }}</label>
                    <select name="wp_category_id" id="categoriesSelect" class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div class="mb-4" id="tagsField" style="display:none;">
                    <label class="block text-xs font-semibold mb-1 text-gray-500">WordPress Tags</label>
                    <input type="text"
                           name="wp_tags"
                           id="tagsInput"
                           placeholder="tag1, tag2, tag3"
                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm mb-2">
                    <button type="button"
                            onclick="openTagsModal()"
                            class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add existing tags
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1 text-gray-500">{{ __('posts.create.publish.publish_action') }}</label>
                    <select name="publish_action" id="publishActionSelect" class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm">
                        <option value="save_only">{{ __('posts.create.publish.publish_options.save_only') }}</option>
                        <option value="send_draft">{{ __('posts.create.publish.publish_options.send_draft') }}</option>
                        <option value="publish_now">{{ __('posts.create.publish.publish_options.publish_now') }}</option>
                        <option value="schedule">{{ __('posts.create.publish.publish_options.schedule') }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('posts.create.publish.publish_help') }}</p>
                </div>

                <div id="scheduleField" class="mb-4 hidden">
                    <label class="block text-xs font-semibold mb-1 text-gray-500">{{ __('posts.create.publish.schedule') }} ({{ auth()->user()->timezone ?? 'UTC' }})</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm">
                    <p class="text-xs text-gray-400 mt-1">WordPress will auto-publish at this time</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-gray-500">{{ __('posts.create.insight.label') }}</label>
                    <div class="relative">
                        <input type="text"
                               id="insightSearch"
                               placeholder="{{ __('posts.create.insight.search_placeholder') }}"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-sm"
                               autocomplete="off">
                        <input type="hidden" name="insight_id" id="insightId" value="{{ old('insight_id') }}">
                        <div id="insightDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded max-h-60 overflow-y-auto">
                            <!-- Results here -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" id="selectedInsight">{{ __('posts.create.insight.none') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button type="submit" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('posts.create.actions.create') }}
                </button>
                <a href="{{ route('posts.index') }}" class="block w-full px-4 py-3 bg-gray-100 hover:bg-gray-600 text-gray-900 font-semibold rounded-lg transition text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('posts.create.actions.cancel') }}
                </a>
            </div>
        </div>
    </div>
</form>

<x-media-library />

@push('styles')
<link href="/js/quill/quill.snow.css" rel="stylesheet">
<style>
    /* Remove all borders from toolbar, use white background */
    .ql-toolbar.ql-snow {
        border: 0 !important;
        border-bottom: 0 !important;
        border-radius: 0 !important;
        background: #ffffff;
    }

    .ql-container.ql-snow {
        border: none !important;
    }

    /* Editor content has the border */
    .ql-editor {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-family: Inter, system-ui, -apple-system, sans-serif;
        font-size: 1rem;
        line-height: 1.7142857;
    }

    /* Override Quill container default font */
    .ql-container {
        font-family: Inter, system-ui, -apple-system, sans-serif;
        font-size: 1rem;
    }

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

@push('scripts')
<script src="/js/quill/quill.js"></script>
<script>
// Load WordPress categories and tags when website selected
document.getElementById('websiteSelect').addEventListener('change', function() {
    const websiteId = this.value;
    const categoriesField = document.getElementById('categoriesField');
    const categoriesSelect = document.getElementById('categoriesSelect');
    const tagsField = document.getElementById('tagsField');
    const tagsSelect = document.getElementById('tagsSelect');

    if (!websiteId) {
        categoriesField.style.display = 'none';
        tagsField.style.display = 'none';
        return;
    }

    // Load Categories
    categoriesField.style.display = 'block';
    categoriesSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`/websites/fetch-categories/${websiteId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.categories) {
                const categories = data.data.categories;

                if (categories.length === 0) {
                    categoriesSelect.innerHTML = '<option value="">No categories found</option>';
                    return;
                }

                categoriesSelect.innerHTML = '';

                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categoriesSelect.appendChild(option);
                });
            } else {
                categoriesSelect.innerHTML = '<option value="">Failed to load categories</option>';
                console.error('Error loading categories:', data.message);
            }
        })
        .catch(error => {
            categoriesSelect.innerHTML = '<option value="">Error loading categories</option>';
            console.error('Fetch error:', error);
        });

    // Load Tags
    tagsField.style.display = 'block';

    fetch(`/websites/${websiteId}/tags`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.tags) {
                availableTags = data.data.tags; // Store tags globally for modal
                console.log('Tags loaded:', availableTags.length);
            } else {
                availableTags = [];
                console.error('Error loading tags:', data.message);
            }
        })
        .catch(error => {
            availableTags = [];
            console.error('Fetch error:', error);
        });
});

// Publish Action Change
document.getElementById('publishActionSelect').addEventListener('change', function() {
    const scheduleField = document.getElementById('scheduleField');
    const helpText = this.parentElement.querySelector('.text-xs');

    scheduleField.classList.add('hidden');

    switch(this.value) {
        case 'save_only':
            helpText.textContent = 'Post will be saved locally only';
            break;
        case 'send_draft':
            helpText.textContent = 'Post will be sent to WordPress as draft';
            break;
        case 'publish_now':
            helpText.textContent = 'Post will be published immediately to WordPress';
            break;
        case 'schedule':
            helpText.textContent = 'Post will be scheduled for future publishing';
            scheduleField.classList.remove('hidden');
            break;
    }

    document.getElementById('publishAction').value = this.value;
});

// Media Library Integration
function openMediaLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                document.getElementById('featuredMediaId').value = media.id;
                document.getElementById('featuredImageThumb').src = media.full_url;
                document.getElementById('featuredImagePreview').classList.remove('hidden');
            }
        }
    }));
}

function removeFeaturedImage() {
    document.getElementById('featuredMediaId').value = '';
    document.getElementById('featuredImagePreview').classList.add('hidden');
}

// Insight Autocomplete
const insightSearch = document.getElementById('insightSearch');
const insightDropdown = document.getElementById('insightDropdown');
const insightId = document.getElementById('insightId');
const selectedInsight = document.getElementById('selectedInsight');
const insights = @json($insights);

insightSearch.addEventListener('input', function() {
    const query = this.value.toLowerCase();

    if (!query) {
        insightDropdown.classList.add('hidden');
        return;
    }

    const filtered = insights.filter(i => i.topic.toLowerCase().includes(query));

    if (filtered.length === 0) {
        insightDropdown.innerHTML = '<div class="p-2 text-xs text-gray-500">No results</div>';
        insightDropdown.classList.remove('hidden');
        return;
    }

    insightDropdown.innerHTML = filtered.map(i => `
        <div class="p-2 hover:bg-white cursor-pointer text-sm" onclick="selectInsight(${i.id}, '${i.topic.replace(/'/g, "\\'")}')">
            ${i.topic}
        </div>
    `).join('');
    insightDropdown.classList.remove('hidden');
});

function selectInsight(id, topic) {
    insightId.value = id;
    insightSearch.value = topic;
    selectedInsight.textContent = topic;
    insightDropdown.classList.add('hidden');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#insightSearch') && !e.target.closest('#insightDropdown')) {
        insightDropdown.classList.add('hidden');
    }
});

// Quill Editor Configuration
const quill = new Quill('#quillEditor', {
    theme: 'snow',
    placeholder: 'Start writing your article here...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['blockquote', 'code-block'],
            ['clean']
        ]
    }
});

// Sync Quill content to hidden textarea
quill.on('text-change', function() {
    document.getElementById('contentEditor').value = quill.root.innerHTML;
});

// Load old content if exists
const oldContent = document.getElementById('contentEditor').value;
if (oldContent) {
    quill.root.innerHTML = oldContent;
}

// Handle image upload
quill.getModule('toolbar').addHandler('image', function() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async function() {
        const file = input.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch('/media/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    const range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', data.media.full_url);
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Upload error: ' + error.message);
            }
        }
    };
});

// Tags Modal Functionality
let availableTags = [];

function openTagsModal() {
    const modal = document.getElementById('tagsModal');
    const tagsGrid = document.getElementById('tagsGrid');

    // Populate tags as pills
    if (availableTags.length === 0) {
        tagsGrid.innerHTML = '<p class="text-gray-500 text-sm">No tags available. Please select a website first.</p>';
    } else {
        tagsGrid.innerHTML = availableTags.map(tag => `
            <button type="button"
                    onclick="addTag('${tag.name.replace(/'/g, "\\'")}')"
                    class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-full text-sm font-medium transition-colors">
                ${tag.name}
            </button>
        `).join('');
    }

    modal.classList.remove('hidden');
}

function closeTagsModal() {
    document.getElementById('tagsModal').classList.add('hidden');
}

function addTag(tagName) {
    const tagsInput = document.getElementById('tagsInput');
    const currentTags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t);

    // Check if tag already exists
    if (!currentTags.includes(tagName)) {
        currentTags.push(tagName);
        tagsInput.value = currentTags.join(', ');
    }

    closeTagsModal();
}

</script>
@endpush

<!-- Tags Modal -->
<div id="tagsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Select Tags</h3>
                <button onclick="closeTagsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Tags Grid -->
            <div id="tagsModalContent" class="p-4 overflow-y-auto max-h-[60vh]">
                <div id="tagsGrid" class="flex flex-wrap gap-2">
                    <!-- Tags will be populated here -->
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end p-4 border-t">
                <button onclick="closeTagsModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
