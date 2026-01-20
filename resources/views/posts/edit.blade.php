@extends('layouts.dashboard')

@section('title', __('posts.edit.title') . ' - AneWP')

@section('content')
<form action="{{ route('posts.update', $post) }}" method="POST" id="postForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="featured_media_id" id="featuredMediaId" value="{{ old('featured_media_id', $post->featured_media_id) }}">
    <input type="hidden" name="publish_action" id="publishAction" value="save_only">
    <input type="hidden" name="article_type" value="{{ old('article_type', $post->article_type) }}">

    <div class="flex h-screen overflow-hidden">
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto bg-white content-no-scroll">
            <div class="max-w-4xl mx-auto p-8">
                <div class="mb-8">
                    <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        {{ __('posts.edit.back') }}
                    </a>
                    <h1 class="text-3xl font-semibold text-gray-900 mt-4">{{ __('posts.edit.heading') }}</h1>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.form.title') }} *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" placeholder="{{ __('posts.create.form.title_placeholder') }}"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                           required>
                    @error('title')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold">{{ __('posts.create.form.content') }} *</label>
                        <button type="button" onclick="openExpandModal()" class="inline-flex items-center gap-1 text-sm text-purple-600 hover:text-purple-700">
                            <x-heroicon-o-sparkles class="w-4 h-4" />
                            {{ __('posts.edit.expand') }}
                        </button>
                    </div>
                    <div id="quillEditor" class="border border-gray-200 rounded-lg" style="height: 500px; background: white;"></div>
                    <textarea id="contentEditor" name="content" required style="display:none;">{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
                <h3 class="text-lg font-bold mb-4">{{ __('posts.create.seo.heading') }}</h3>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.seo.focus') }}</label>
                    <input type="text" name="seo_focus_keyword" value="{{ old('seo_focus_keyword', $post->seo_focus_keyword) }}"
                           placeholder="{{ __('posts.create.seo.focus_placeholder') }}"
                           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.seo.meta') }}</label>
                    <textarea name="seo_meta_description" rows="3"
                              placeholder="{{ __('posts.create.seo.meta_placeholder') }}"
                              class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('seo_meta_description', $post->seo_meta_description) }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">{{ __('posts.create.seo.meta_counter') }}</p>
                </div>
            </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="w-80 space-y-6 h-screen overflow-y-auto p-6 sidebar-no-scroll">
            
            <!-- Featured Image -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('posts.create.featured.heading') }}
                </h3>

                <div id="featuredImagePreview" class="{{ $post->featured_media_id ? '' : 'hidden' }} mb-3">
                    <div class="relative rounded-lg overflow-hidden">
                        <img id="featuredImageThumb"
                             src="{{ $post->featuredMedia ? $post->featuredMedia->full_url : '' }}"
                             alt="Featured image"
                             class="w-full h-40 object-cover">
                        <button type="button" onclick="removeFeaturedImage()"
                                class="absolute top-2 right-2 p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="button" onclick="openMediaLibrary()"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $post->featured_media_id ? __('posts.edit.featured_change') : __('posts.edit.featured_select') }}
                </button>
                <p class="text-xs text-gray-500 text-center mt-2">{{ __('posts.edit.featured_help') }}</p>
            </div>

            <!-- Publish Settings -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('posts.edit.publish_heading') }}
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.publish.website') }}</label>
                    <select name="website_id" id="website"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <option value="">{{ __('posts.create.publish.website_placeholder') }}</option>
                        @foreach($websites as $website)
                            <option value="{{ $website->id }}"
                                    {{ old('website_id', optional($post->targets->first())->website_id) == $website->id ? 'selected' : '' }}>
                                {{ $website->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.create.publish.category') }}</label>
                    <select name="wp_category_id" id="wpCategory"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <option value="">Select Category</option>
                    </select>
                </div>

                <div class="mb-4" id="tagsField">
                    <label class="block text-sm font-semibold mb-2">Tags</label>
                    <input type="text"
                           name="wp_tags"
                           id="tagsInput"
                           placeholder="tag1, tag2, tag3"
                           value="{{ old('wp_tags', !empty($post->ai_generated_tags) ? implode(', ', $post->ai_generated_tags) : '') }}"
                           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent mb-2">
                    <button type="button"
                            id="addExistingTagsBtn"
                            onclick="openTagsModal()"
                            style="display:none;"
                            class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add existing WordPress tags
                    </button>
                    <p class="text-xs text-gray-500 mt-1">{{ !empty($post->ai_generated_tags) ? 'AI-generated tags auto-filled. You can edit or add more.' : 'Comma-separated tags (e.g., tag1, tag2, tag3)' }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.edit.publish_action') }}</label>
                    @php
                        $currentAction = 'save_only';
                        if ($post->targets->first()) {
                            $target = $post->targets->first();

                            // Check status first (most reliable)
                            if ($target->status === 'scheduled') {
                                $currentAction = 'schedule';
                            }
                            elseif ($target->status === 'published') {
                                $currentAction = 'publish_now';
                            }
                            elseif ($target->status === 'draft' && $target->wp_post_id) {
                                $currentAction = 'send_draft';
                            }
                        }
                    @endphp
                    <select name="publish_action" id="publishActionSelect"
                            class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <option value="save_only" {{ $currentAction === 'save_only' ? 'selected' : '' }}>{{ __('posts.create.publish.publish_options.save_only') }}</option>
                        <option value="send_draft" {{ $currentAction === 'send_draft' ? 'selected' : '' }}>{{ __('posts.create.publish.publish_options.send_draft') }}</option>
                        <option value="publish_now" {{ $currentAction === 'publish_now' ? 'selected' : '' }}>{{ __('posts.create.publish.publish_options.publish_now') }}</option>
                        <option value="schedule" {{ $currentAction === 'schedule' ? 'selected' : '' }}>{{ __('posts.create.publish.publish_options.schedule') }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('posts.create.publish.publish_help') }}</p>
                </div>

                <div id="scheduleField" class="mb-4 {{ $currentAction === 'schedule' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.edit.schedule') }} ({{ auth()->user()->timezone ?? 'UTC' }})</label>
                    @php
                        $scheduledValue = old('scheduled_at');
                        if (!$scheduledValue && optional($post->targets->first())->scheduled_at) {
                            $userTz = auth()->user()->timezone ?? 'UTC';
                            $scheduledValue = $post->targets->first()->scheduled_at->copy()->setTimezone($userTz)->format('Y-m-d\TH:i');
                        }
                    @endphp
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ $scheduledValue }}"
                           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">WordPress will auto-publish at this time</p>
                    {{-- Debug info (remove after fix) --}}
                    @if(optional($post->targets->first())->scheduled_at)
                        <p class="text-xs text-red-500 mt-1">
                            Debug: UTC={{ $post->targets->first()->scheduled_at->format('H:i') }} |
                            Local={{ $post->targets->first()->scheduled_at->copy()->setTimezone(auth()->user()->timezone ?? 'UTC')->format('H:i') }} |
                            TZ={{ auth()->user()->timezone ?? 'UTC' }}
                        </p>
                    @endif
                </div>

                <!-- Force Publish Checkbox (bypass duplicate check) -->
                <div id="forcePublishField" class="mb-4 {{ in_array($currentAction, ['publish_now', 'send_draft']) ? '' : 'hidden' }}">
                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="checkbox" name="force_publish" value="1" {{ old('force_publish') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-600 focus:ring-2">
                        <span class="text-gray-700">
                            <span class="font-semibold">Force Publish</span>
                            <span class="text-xs text-gray-500 block">Ignore duplicate check and publish anyway</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('posts.edit.insight_label') }}</label>
                    <div class="relative">
                        <input type="text" id="insightSearch" placeholder="Search insight..." autocomplete="off"
                               value="{{ old('insight_id') ? $insights->firstWhere('id', old('insight_id'))->topic ?? '' : ($post->insight ? $post->insight->topic : '') }}"
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <input type="hidden" name="insight_id" id="insightId" value="{{ old('insight_id', $post->insight_id) }}">
                        <div id="insightDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $post->insight ? $post->insight->topic : __('posts.edit.insight_none') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <!-- Amplify Button -->
                <button type="button" onclick="openAmplifyModal()"
                        class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                    {{ __('posts.edit.amplify') }}
                </button>

                <!-- Export Pack Button -->
                @if($post->contentDNA && $post->socialAssets()->count() > 0)
                    <a href="{{ route('posts.export-pack', $post) }}"
                       class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        {{ __('posts.edit.export_pack', ['count' => $post->socialAssets()->count()]) }}
                    </a>
                @endif

                <button type="submit"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('posts.edit.update') }}
                </button>
                <a href="{{ route('posts.index') }}"
                   class="block w-full px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-lg transition text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('posts.create.actions.cancel') }}
                </a>
            </div>
        </div>
    </div>
</form>

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

// Load existing content
const oldContent = {!! json_encode(old('content', $post->content)) !!};
console.log('Loading content, length:', oldContent ? oldContent.length : 0);
if (oldContent) {
    // Use clipboard API to paste HTML (preserves formatting better)
    quill.clipboard.dangerouslyPasteHTML(oldContent);
    console.log('Content loaded into Quill editor');
}

// Handle image upload in editor
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

// Load WordPress categories and tags when website selected
document.getElementById('website').addEventListener('change', function() {
    const websiteId = this.value;
    const categoriesSelect = document.getElementById('wpCategory');
    const addExistingTagsBtn = document.getElementById('addExistingTagsBtn');

    if (!websiteId) {
        categoriesSelect.innerHTML = '<option value="">Select Category</option>';
        addExistingTagsBtn.style.display = 'none';
        return;
    }

    // Show "Add existing WordPress tags" button
    addExistingTagsBtn.style.display = 'flex';

    // Load categories
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

                const savedCategoryId = {{ optional($post->targets->first())->categories[0] ?? 'null' }};

                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;

                    if (savedCategoryId && cat.id == savedCategoryId) {
                        option.selected = true;
                    }

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

    // Load tags
    fetch(`/websites/${websiteId}/tags`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.tags) {
                availableTags = data.data.tags;
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

// Trigger on page load if website already selected
@if(optional($post->targets->first())->website_id)
    window.addEventListener('load', function() {
        document.getElementById('website').dispatchEvent(new Event('change'));
    });
@endif

// Publish Action Change
document.getElementById('publishActionSelect').addEventListener('change', function() {
    const scheduleField = document.getElementById('scheduleField');
    const forcePublishField = document.getElementById('forcePublishField');
    const helpText = this.parentElement.querySelector('.text-xs');

    scheduleField.classList.add('hidden');
    forcePublishField.classList.add('hidden');

    switch(this.value) {
        case 'save_only':
            helpText.textContent = 'Post will be saved locally only';
            break;
        case 'send_draft':
            helpText.textContent = 'Post will be sent to WordPress as draft';
            forcePublishField.classList.remove('hidden'); // Show force publish for draft
            break;
        case 'publish_now':
            helpText.textContent = 'Post will be published immediately to WordPress';
            forcePublishField.classList.remove('hidden'); // Show force publish for immediate publish
            break;
        case 'schedule':
            helpText.textContent = 'Post will be scheduled for future publishing';
            scheduleField.classList.remove('hidden');
            break;
    }

    document.getElementById('publishAction').value = this.value;
});

// Trigger publish action change on page load to set correct help text
window.addEventListener('load', function() {
    document.getElementById('publishActionSelect').dispatchEvent(new Event('change'));
});

// Media Library Component Integration
function openMediaLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                // Update featured image
                document.getElementById('featuredMediaId').value = media.id;
                document.getElementById('featuredImageThumb').src = media.full_url;
                document.getElementById('featuredImagePreview').classList.remove('hidden');

                console.log('Featured image set:', media.title);
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
const insights = @json($insights);

insightSearch.addEventListener('input', function() {
    const query = this.value.toLowerCase();

    if (query.length < 2) {
        insightDropdown.classList.add('hidden');
        return;
    }

    const filtered = insights.filter(i => i.topic.toLowerCase().includes(query));

    insightDropdown.innerHTML = filtered.map(i => `
        <div class="px-4 py-2 hover:bg-gray-50 cursor-pointer"
             onclick="selectInsight(${i.id}, \`${i.topic.replace(/`/g, '')}\`)">
            ${i.topic}
        </div>
    `).join('');

    insightDropdown.classList.remove('hidden');
});

function selectInsight(id, topic) {
    document.getElementById('insightId').value = id;
    document.getElementById('insightSearch').value = topic;
    insightDropdown.classList.add('hidden');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#insightSearch') && !e.target.closest('#insightDropdown')) {
        insightDropdown.classList.add('hidden');
    }
});

// Expand Article Modal
function openExpandModal() {
    document.getElementById('expandModal').classList.remove('hidden');
}

function closeExpandModal() {
    document.getElementById('expandModal').classList.add('hidden');
}

function expandArticle() {
    const templateId = document.getElementById('expandTemplateSelect').value;

    if (!templateId) {
        alert('Please select a template');
        return;
    }

    // Get current content from Quill editor
    const content = quill.root.innerHTML;
    const title = document.querySelector('input[name="title"]').value;
    const websiteId = {{ optional($post->targets->first())->website_id ?? 'null' }};

    // Close template selection modal
    closeExpandModal();

    // Show progress modal
    document.getElementById('expandProgressModal').classList.remove('hidden');
    document.getElementById('expandProgressText').textContent = 'Initializing...';
    document.getElementById('expandProgressBar').style.width = '0%';

    // Prepare form data
    const formData = new FormData();
    formData.append('generation_source', 'text');
    formData.append('title', title);
    formData.append('ref_text', content);
    formData.append('template_id', templateId);
    if (websiteId) {
        formData.append('website_id', websiteId);
    }

    // Submit to generate endpoint
    fetch('{{ route("generate.article") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.job_id) {
            // Start polling for progress
            pollExpandProgress(data.job_id);
        } else {
            throw new Error(data.message || 'Failed to start generation');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to start generation: ' + error.message);
        document.getElementById('expandProgressModal').classList.add('hidden');
    });
}

function pollExpandProgress(jobId) {
    const interval = setInterval(() => {
        fetch(`/generate/progress/${jobId}`)
            .then(res => res.json())
            .then(data => {
                // Update progress
                const progress = data.progress || 0;
                const step = data.step || 'Processing...';

                document.getElementById('expandProgressBar').style.width = progress + '%';
                document.getElementById('expandProgressText').textContent = step;

                // Check if completed
                if (data.status === 'completed' && data.result) {
                    clearInterval(interval);

                    // Update editor with new content
                    quill.root.innerHTML = data.result.content;

                    // Update title if different
                    if (data.result.title) {
                        document.querySelector('input[name="title"]').value = data.result.title;
                    }

                    // Update SEO fields if available
                    if (data.result.focus_keyword) {
                        document.querySelector('input[name="seo_focus_keyword"]').value = data.result.focus_keyword;
                    }
                    if (data.result.meta_description) {
                        document.querySelector('textarea[name="seo_meta_description"]').value = data.result.meta_description;
                    }

                    // Close progress modal
                    setTimeout(() => {
                        document.getElementById('expandProgressModal').classList.add('hidden');
                        alert('Article expanded successfully!');
                    }, 500);
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    document.getElementById('expandProgressModal').classList.add('hidden');
                    alert('Generation failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
            });
    }, 1000); // Poll every 1 second
}

// Amplify Modal Functions
function openAmplifyModal() {
    document.getElementById('amplifyModal').classList.remove('hidden');
}

function closeAmplifyModal() {
    document.getElementById('amplifyModal').classList.add('hidden');
}

function amplifyPost() {
    const channels = [];
    if (document.getElementById('channel_instagram').checked) channels.push('instagram');
    if (document.getElementById('channel_facebook').checked) channels.push('facebook');
    if (document.getElementById('channel_twitter').checked) channels.push('twitter');
    if (document.getElementById('channel_linkedin').checked) channels.push('linkedin');

    if (channels.length === 0) {
        alert('Please select at least one channel');
        return;
    }

    const autoSchedule = document.getElementById('auto_schedule').checked;

    // Close modal
    closeAmplifyModal();

    // Show loading state
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

    // Call amplify endpoint
    fetch('{{ route("posts.amplify", $post) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            channels: channels,
            auto_schedule: autoSchedule
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`Success! Generated ${data.assets.length} social assets.\n\n` +
                  `Channels: ${channels.join(', ')}\n` +
                  (data.recommendations.length > 0 ? `\nRecommendation: ${data.recommendations[0].message}` : ''));

            // Reload page to show new assets
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to generate assets'));
        }
    })
    .catch(error => {
        console.error('Amplify error:', error);
        alert('Failed to amplify post. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

// Tags Modal Functions
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

<!-- Amplify Modal -->
<div id="amplifyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Amplify to Social Media</h3>
                <button onclick="closeAmplifyModal()" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Select social media channels to generate content:</p>

                <div class="space-y-3 mb-4">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" id="channel_instagram" value="instagram" class="w-4 h-4 text-blue-600 rounded" checked>
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600"></div>
                            <span class="font-medium text-gray-900">Instagram</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" id="channel_facebook" value="facebook" class="w-4 h-4 text-blue-600 rounded" checked>
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-blue-600"></div>
                            <span class="font-medium text-gray-900">Facebook</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" id="channel_twitter" value="twitter" class="w-4 h-4 text-blue-600 rounded" checked>
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-black"></div>
                            <span class="font-medium text-gray-900">Twitter</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" id="channel_linkedin" value="linkedin" class="w-4 h-4 text-blue-600 rounded" checked>
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-blue-700"></div>
                            <span class="font-medium text-gray-900">LinkedIn</span>
                        </div>
                    </label>
                </div>

                <label class="flex items-center gap-2 mb-4">
                    <input type="checkbox" id="auto_schedule" class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Auto-schedule posts</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200">
                <button onclick="closeAmplifyModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancel
                </button>
                <button onclick="amplifyPost()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-lg flex items-center gap-2">
                    <x-heroicon-o-sparkles class="w-4 h-4" />
                    Generate Assets
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Expand Article Modal -->
<div id="expandModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Expand Article</h3>
                <button onclick="closeExpandModal()" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Template *</label>
                <select id="expandTemplateSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4">
                    <option value="">Choose template...</option>
                    @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>

                <a href="{{ route('ai-templates.create') }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-700">
                    + Create New Template
                </a>
            </div>

            <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200">
                <button onclick="closeExpandModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancel
                </button>
                <button onclick="expandArticle()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center gap-2">
                    <x-heroicon-o-sparkles class="w-4 h-4" />
                    Expand Article
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div id="expandProgressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <div class="text-center mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                    <x-heroicon-o-sparkles class="w-8 h-8 text-purple-600 animate-pulse" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Expanding Article...</h3>
                <p id="expandProgressText" class="text-sm text-gray-600">Initializing...</p>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                <div id="expandProgressBar" class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>

            <p class="text-xs text-gray-500 text-center">Please wait, this may take a minute...</p>
        </div>
    </div>
</div>

<!-- Media Library Modal -->
<x-media-library />

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
