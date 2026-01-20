@extends('layouts.dashboard')

@push('styles')
<link href="/js/quill/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bulk Edit & Publish</h1>
                <p class="mt-2 text-gray-600">Edit {{ $posts->count() }} articles before publishing to WordPress</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg font-semibold">
                    {{ $posts->count() }} Articles
                </span>
                <a href="{{ route('posts.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <x-heroicon-o-arrow-left class="w-5 h-5 inline" /> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex items-start">
            <x-heroicon-s-information-circle class="w-5 h-5 text-blue-500 mt-0.5 mr-3" />
            <div>
                <p class="text-sm text-blue-700">
                    <strong>Quick Edit Mode:</strong> Edit inline fields (title, meta, keywords) directly. Click <strong>"Edit Content"</strong> for full HTML editor. Changes auto-save.
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form id="bulkReviewForm" method="POST" action="{{ route('posts.bulk-publish') }}">
        @csrf

        <!-- Articles List -->
        @foreach($posts as $index => $post)
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition" data-post-id="{{ $post->id }}">
            <input type="hidden" name="articles[{{ $index }}][post_id]" value="{{ $post->id }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-0">
                <!-- Main: Editable Content (2/3) -->
                <div class="lg:col-span-2 p-6 border-r border-gray-200">
                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Title</label>
                        <input type="text"
                               name="articles[{{ $index }}][title]"
                               value="{{ $post->title }}"
                               data-field="title"
                               data-post-id="{{ $post->id }}"
                               onblur="saveField(this)"
                               class="w-full text-xl font-bold text-gray-900 border-0 border-b-2 border-transparent focus:border-blue-500 focus:ring-0 px-0 py-2 transition"
                               placeholder="Article title...">
                    </div>

                    <!-- Meta Description -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Meta Description</label>
                        <textarea name="articles[{{ $index }}][seo_meta_description]"
                                  rows="2"
                                  maxlength="160"
                                  data-field="seo_meta_description"
                                  data-post-id="{{ $post->id }}"
                                  onblur="saveField(this)"
                                  class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 px-3 py-2 transition resize-none"
                                  placeholder="Brief description...">{{ $post->seo_meta_description }}</textarea>
                        <small class="text-xs text-gray-500 char-count">{{ strlen($post->seo_meta_description ?? '') }}/160</small>
                    </div>

                    <!-- Focus Keyword -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Focus Keyword</label>
                        <input type="text"
                               name="articles[{{ $index }}][seo_focus_keyword]"
                               value="{{ $post->seo_focus_keyword }}"
                               data-field="seo_focus_keyword"
                               data-post-id="{{ $post->id }}"
                               onblur="saveField(this)"
                               class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 px-3 py-2 transition"
                               placeholder="Main keyword...">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 mt-4">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium">
                            <x-heroicon-o-document-text class="w-4 h-4 inline" /> {{ str_word_count(strip_tags($post->content)) }} words
                        </span>
                        <button type="button"
                                onclick="editContent({{ $post->id }})"
                                class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200">
                            <x-heroicon-o-pencil class="w-4 h-4 inline" /> Edit Content
                        </button>
                        <button type="button"
                                onclick="deleteArticle({{ $post->id }}, this)"
                                class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">
                            <x-heroicon-o-trash class="w-4 h-4 inline" /> Delete
                        </button>
                    </div>
                </div>

                <!-- Right: Sidebar (1/3) -->
                <div class="lg:col-span-1 bg-gray-50 p-6 space-y-6">
                    <!-- Featured Image -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">
                            <x-heroicon-o-photo class="w-4 h-4 inline" /> Featured Image
                        </h3>
                        <div class="featured-image-container" data-post-id="{{ $post->id }}">
                            @if($post->featuredMedia)
                                <div class="space-y-2">
                                    <div class="relative rounded-lg overflow-hidden bg-gray-100">
                                        <img src="{{ asset('storage/' . $post->featuredMedia->file_path) }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-auto object-cover">
                                    </div>
                                    <!-- Action Buttons -->
                                    <div class="flex gap-2">
                                        <button type="button"
                                                onclick="openImageLibrary({{ $post->id }})"
                                                class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100 transition">
                                            <x-heroicon-o-arrow-path class="w-4 h-4 inline" /> Change
                                        </button>
                                        <button type="button"
                                                onclick="removeImage({{ $post->id }})"
                                                class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition">
                                            <x-heroicon-o-trash class="w-4 h-4 inline" />
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition">
                                    <x-heroicon-o-photo class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                                    <p class="text-xs text-gray-500 mb-3">No image set</p>
                                    <button type="button"
                                            onclick="openImageLibrary({{ $post->id }})"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                                        <x-heroicon-o-plus class="w-4 h-4 inline" /> Add Image
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Publish Settings -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">
                            <x-heroicon-o-paper-airplane class="w-4 h-4 inline" /> Publish Settings
                        </h3>

                        <!-- Website Selection -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Target Website *</label>
                        <select name="articles[{{ $index }}][website_id]"
                                class="website-select w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                data-post-id="{{ $post->id }}"
                                onchange="saveWebsite(this)"
                                required>
                            <option value="">Select Website</option>
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}"
                                        {{ optional($post->targets->first())->website_id == $website->id ? 'selected' : '' }}>
                                    {{ $website->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Category *</label>
                        <select name="articles[{{ $index }}][wp_category_id]"
                                class="category-select w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                data-post-id="{{ $post->id }}"
                                data-selected-category="{{ optional($post->targets->first())->categories[0] ?? '' }}"
                                onchange="saveCategory(this)"
                                required>
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tags</label>
                        <input type="text"
                               name="articles[{{ $index }}][wp_tags]"
                               placeholder="tag1, tag2, tag3"
                               value="{{ !empty($post->ai_generated_tags) ? implode(', ', $post->ai_generated_tags) : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">{{ !empty($post->ai_generated_tags) ? 'AI-generated tags. You can edit.' : 'Comma-separated tags' }}</p>
                    </div>

                    <!-- Publish Action -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Action *</label>
                        <select name="articles[{{ $index }}][status]"
                                class="publish-action w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                data-index="{{ $index }}"
                                required>
                            <option value="draft">📝 Save as Draft</option>
                            <option value="publish" selected>🚀 Publish Now</option>
                            <option value="scheduled">📅 Schedule</option>
                        </select>
                    </div>

                        <!-- Schedule DateTime (hidden by default) -->
                        <div class="schedule-field hidden" id="schedule-{{ $index }}">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Schedule Date & Time</label>
                            <input type="datetime-local"
                                   name="articles[{{ $index }}][scheduled_at]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Sticky Bottom Actions -->
        <div class="sticky bottom-4 bg-blue-600 rounded-lg shadow-lg p-6 mt-8">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Ready to Publish</h3>
                    <p class="text-sm text-blue-100">{{ $posts->count() }} articles will be processed</p>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                            onclick="saveAndBack()"
                            class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50">
                        <x-heroicon-o-bookmark class="w-5 h-5 inline" /> Save & Back
                    </button>
                    <button type="submit"
                            class="px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 inline" /> Send to WordPress
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Content Editor Modal -->
<div id="contentEditorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl overflow-hidden border border-gray-200 shadow-2xl flex flex-col" style="width: 90vw; height: 85vh;">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-white">
                <h3 class="text-lg font-semibold text-gray-900" id="contentEditorTitle">Edit Article Content</h3>
                <button type="button" onclick="closeContentEditor()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Editor Container -->
            <div class="flex-1 overflow-hidden p-6 bg-gray-50">
                <div id="quillEditorBulk" class="bg-white border border-gray-200 rounded-lg" style="height: calc(85vh - 180px);"></div>
                <textarea id="contentEditorTextarea" style="display:none;"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center p-4 border-t border-gray-200 bg-white">
                <div class="text-sm text-gray-500">
                    <span id="wordCount">0 words</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeContentEditor()"
                            class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" onclick="saveContent()"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <x-heroicon-o-check class="w-5 h-5 inline" /> Save Content
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<x-media-library />

<script src="/js/quill/quill.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';

// Auto-save field on blur
function saveField(element) {
    const postId = element.dataset.postId;
    const field = element.dataset.field;
    const value = element.value.trim();

    if (!postId || !field) return;

    // Visual feedback - loading
    element.classList.add('bg-yellow-50');

    fetch('/posts/update-field/' + postId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ field: field, value: value })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            element.classList.remove('bg-yellow-50');
            element.classList.add('bg-green-50');
            setTimeout(() => {
                element.classList.remove('bg-green-50');
            }, 1000);
        } else {
            element.classList.remove('bg-yellow-50');
            element.classList.add('bg-red-50');
            alert('Failed to save: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        element.classList.remove('bg-yellow-50');
        element.classList.add('bg-red-50');
        alert('Error saving field: ' + error.message);
    });
}

// Save website selection
function saveWebsite(selectElement) {
    const postId = selectElement.dataset.postId;
    const websiteId = selectElement.value;

    if (!websiteId) return;

    // Visual feedback
    selectElement.classList.add('bg-yellow-50');

    fetch('/posts/' + postId + '/save-target', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            website_id: websiteId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectElement.classList.remove('bg-yellow-50');
            selectElement.classList.add('bg-green-50');
            setTimeout(() => selectElement.classList.remove('bg-green-50'), 1000);
        } else {
            selectElement.classList.remove('bg-yellow-50');
            selectElement.classList.add('bg-red-50');
        }
    });
}

// Save category selection
function saveCategory(selectElement) {
    const postId = selectElement.dataset.postId;
    const categoryId = selectElement.value;

    if (!categoryId) return;

    // Visual feedback
    selectElement.classList.add('bg-yellow-50');

    fetch('/posts/' + postId + '/save-target', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            category_id: categoryId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectElement.classList.remove('bg-yellow-50');
            selectElement.classList.add('bg-green-50');
            setTimeout(() => selectElement.classList.remove('bg-green-50'), 1000);
        } else {
            selectElement.classList.remove('bg-yellow-50');
            selectElement.classList.add('bg-red-50');
        }
    });
}

// Media Library Integration
let currentEditingPostId = null;

function openImageLibrary(postId) {
    currentEditingPostId = postId;

    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                // Update via AJAX
                fetch('/posts/update-field/' + currentEditingPostId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        field: 'featured_media_id',
                        value: media.id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        const postCard = document.querySelector('[data-post-id="' + currentEditingPostId + '"]');
                        if (postCard) {
                            const imageContainer = postCard.querySelector('.featured-image-container[data-post-id="' + currentEditingPostId + '"]');
                            if (imageContainer) {
                                const titleInput = postCard.querySelector('input[data-field="title"]');
                                const postTitle = titleInput ? titleInput.value : 'Featured Image';

                                imageContainer.innerHTML = '<div class="space-y-2">' +
                                    '<div class="relative rounded-lg overflow-hidden bg-gray-100">' +
                                    '<img src="' + media.full_url + '" alt="' + postTitle + '" class="w-full h-auto object-cover">' +
                                    '</div>' +
                                    '<div class="flex gap-2">' +
                                    '<button type="button" onclick="openImageLibrary(' + currentEditingPostId + ')" ' +
                                    'class="flex-1 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100 transition">' +
                                    '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg> Change</button>' +
                                    '<button type="button" onclick="removeImage(' + currentEditingPostId + ')" ' +
                                    'class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition">' +
                                    '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>' +
                                    '</button>' +
                                    '</div>' +
                                    '</div>';
                            }
                        }
                    } else {
                        alert('Failed to set featured image: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Set image error:', error);
                    alert('Error setting featured image: ' + error.message);
                });
            }
        }
    }));
}

// Remove featured image
function removeImage(postId) {
    if (!confirm('Are you sure you want to remove this image?')) {
        return;
    }

    fetch('/posts/remove-image/' + postId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Reload page to update UI
            window.location.reload();
        } else {
            alert('Failed to remove image: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Remove image error:', error);
        alert('Error removing image: ' + error.message);
    });
}

// Edit content in modal
function editContent(postId) {
    currentEditingPostId = postId;

    // Fetch post content from server
    fetch(`/posts/${postId}/content`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Set content to Quill editor
            quillEditorBulk.root.innerHTML = data.content;

            // Update title
            document.getElementById('contentEditorTitle').textContent = `Edit: ${data.title || 'Untitled'}`;

            // Update word count
            updateWordCount();

            // Show modal
            document.getElementById('contentEditorModal').classList.remove('hidden');
        } else {
            alert('Failed to load content: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error loading content:', error);
        alert('Failed to load content. Please try again.');
    });
}

// Delete article
function deleteArticle(postId, button) {
    if (!confirm('Are you sure you want to delete this article? This cannot be undone.')) {
        return;
    }

    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

    fetch('/posts/delete-ajax/' + postId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove card from DOM
            const card = button.closest('[data-post-id]');
            if (card) {
                card.style.opacity = '0';
                card.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    card.remove();

                    // Check if no articles left
                    const remaining = document.querySelectorAll('[data-post-id]').length;
                    if (remaining === 0) {
                        alert('All articles deleted. Redirecting...');
                        window.location.href = '{{ route('posts.index') }}';
                    }
                }, 300);
            }
        } else {
            alert('Failed to delete: ' + (data.error || 'Unknown error'));
            button.disabled = false;
            button.innerHTML = '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Delete';
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Error deleting article: ' + error.message);
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Delete';
    });
}

// Save and back (without publishing)
function saveAndBack() {
    if (!confirm('Save all changes and return to posts list?')) {
        return;
    }

    // Just redirect back - changes are already auto-saved
    window.location.href = '{{ route('posts.index') }}';
}

// Form submit handler for bulk publish
document.getElementById('bulkReviewForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!confirm('Send all articles to WordPress?')) {
        return;
    }

    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Publishing...';

    const formData = new FormData(form);
    const articles = [];

    // Collect articles data
    const articleIndices = new Set();
    for (let pair of formData.entries()) {
        const match = pair[0].match(/^articles\[(\d+)\]/);
        if (match) {
            articleIndices.add(match[1]);
        }
    }

    articleIndices.forEach(index => {
        const article = {
            post_id: formData.get('articles[' + index + '][post_id]'),
            website_id: formData.get('articles[' + index + '][website_id]'),
            wp_category_id: formData.get('articles[' + index + '][wp_category_id]'),
            status: formData.get('articles[' + index + '][status]'),
            scheduled_at: formData.get('articles[' + index + '][scheduled_at]')
        };
        articles.push(article);
    });

    fetch('{{ route('posts.bulk-publish') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ articles: articles })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect_url || '{{ route('posts.index') }}';
        } else {
            alert('Publish failed: ' + (data.message || 'Unknown error'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('Bulk publish error:', error);
        alert('Error publishing articles: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    });
});

// Toggle schedule field based on action selection
document.querySelectorAll('.publish-action').forEach(select => {
    select.addEventListener('change', function() {
        const index = this.dataset.index;
        const scheduleField = document.getElementById('schedule-' + index);
        if (scheduleField) {
            if (this.value === 'scheduled') {
                scheduleField.classList.remove('hidden');
            } else {
                scheduleField.classList.add('hidden');
            }
        }
    });
});

// Character counter for meta description
document.querySelectorAll('textarea[data-field="seo_meta_description"]').forEach(textarea => {
    textarea.addEventListener('input', function() {
        const counter = this.parentElement.querySelector('.char-count');
        if (counter) {
            counter.textContent = this.value.length + '/160';
        }
    });
});

// Load WordPress categories dynamically when website is selected
document.querySelectorAll('.website-select').forEach(websiteSelect => {
    const postId = websiteSelect.dataset.postId;
    const categorySelect = document.querySelector('.category-select[data-post-id="' + postId + '"]');

    websiteSelect.addEventListener('change', function() {
        const websiteId = this.value;

        if (!websiteId) {
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            return;
        }

        categorySelect.innerHTML = '<option value="">Loading...</option>';

        fetch('/websites/fetch-categories/' + websiteId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.categories) {
                    const categories = data.data.categories;

                    if (categories.length === 0) {
                        categorySelect.innerHTML = '<option value="">No categories found</option>';
                        return;
                    }

                    categorySelect.innerHTML = '';

                    const savedCategoryId = categorySelect.dataset.selectedCategory;

                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;

                        if (savedCategoryId && cat.id == savedCategoryId) {
                            option.selected = true;
                        }

                        categorySelect.appendChild(option);
                    });
                } else {
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                }
            })
            .catch(error => {
                console.error('Category load error:', error);
                categorySelect.innerHTML = '<option value="">Error loading</option>';
            });
    });

    // Trigger on page load if website already selected
    if (websiteSelect.value) {
        websiteSelect.dispatchEvent(new Event('change'));
    }
});

// ========== Content Editor Modal ==========
let quillEditorBulk = null;
let currentPostContent = '';

function editContent(postId) {
    currentEditingPostId = postId;

    // Fetch current content
    const postCard = document.querySelector('[data-post-id="' + postId + '"]');
    fetch('/posts/' + postId + '/edit', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
        .then(data => {
            currentPostContent = data.content || '';

            // Initialize Quill if not yet initialized
            if (!quillEditorBulk) {
                quillEditorBulk = new Quill('#quillEditorBulk', {
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

                // Word count on text change
                quillEditorBulk.on('text-change', function() {
                    const text = quillEditorBulk.getText();
                    const words = text.trim().split(/\s+/).filter(w => w.length > 0).length;
                    document.getElementById('wordCount').textContent = words + ' words';
                });
            }

            // Load content into editor
            quillEditorBulk.root.innerHTML = currentPostContent;

            // Update word count
            const text = quillEditorBulk.getText();
            const words = text.trim().split(/\s+/).filter(w => w.length > 0).length;
            document.getElementById('wordCount').textContent = words + ' words';

            // Show modal
            document.getElementById('contentEditorModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading content:', error);
            alert('Failed to load content');
        });
}

function closeContentEditor() {
    document.getElementById('contentEditorModal').classList.add('hidden');
    currentEditingPostId = null;
}

function saveContent() {
    if (!currentEditingPostId || !quillEditorBulk) return;

    const content = quillEditorBulk.root.innerHTML;

    // Save via AJAX
    fetch('/posts/update-field/' + currentEditingPostId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            field: 'content',
            value: content
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeContentEditor();
            // Show success feedback
            const postCard = document.querySelector('[data-post-id="' + currentEditingPostId + '"]');
            if (postCard) {
                const feedback = document.createElement('div');
                feedback.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                feedback.textContent = 'Content saved successfully!';
                document.body.appendChild(feedback);
                setTimeout(() => feedback.remove(), 2000);
            }
        } else {
            alert('Failed to save content: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('Failed to save content');
    });
}


</script>
@endsection
