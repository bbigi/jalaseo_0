@extends('layouts.dashboard')

@section('title', 'Media Library - AneWP')

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Media Library</h2>
            <p class="text-gray-500 text-sm mt-1">{{ $media->total() }} file{{ $media->total() !== 1 ? 's' : '' }}</p>
        </div>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Media
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-200 mb-6">
        <form method="GET" action="{{ route('media.index') }}" class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Website</label>
                <select name="website" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">All Websites</option>
                    @foreach($websites as $ws)
                        <option value="{{ $ws->id }}" {{ request('website') == $ws->id ? 'selected' : '' }}>
                            {{ $ws->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Source</label>
                <select name="source" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">All Sources</option>
                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual Upload</option>
                    <option value="ai_generated" {{ request('source') == 'ai_generated' ? 'selected' : '' }}>AI Generated</option>
                </select>
            </div>
            <div class="flex-[2]">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or filename"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition text-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Media Grid -->
    @if($media->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-600 text-lg mb-4">No media found</p>
            <p class="text-gray-500 mb-6">Start by uploading your first media file</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1">
            @foreach($media as $item)
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-blue-500 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                 onclick="viewMediaDetails({{ $item->id }})">
                <div class="relative bg-gray-50" style="aspect-ratio: 1/1;">
                    <img src="{{ $item->full_url }}"
                         alt="{{ $item->alt_text ?? $item->file_name }}"
                         class="absolute inset-0 w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                    @if($item->source_type === 'ai_generated')
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 text-[10px] font-semibold bg-green-600 text-white rounded">AI</span>
                    @elseif($item->source_type === 'unsplash')
                        <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 text-[10px] font-semibold bg-blue-600 text-white rounded">UNSPLASH</span>
                    @endif
                </div>
                <div class="p-2 bg-white">
                    <p class="text-xs font-medium truncate text-gray-900 leading-tight">{{ $item->title ?? $item->file_name }}</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $item->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $media->links() }}
        </div>
    @endif
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gradient-to-br from-blue-50/40 via-purple-50/40 to-pink-50/40 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4 border border-gray-200 shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Upload Media</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('media.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Select Image *</label>
                    <input type="file" name="media_file" accept="image/*" required
                           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Website Target (Optional)</label>
                    <select name="website_id" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <option value="">All Websites</option>
                        @foreach($websites as $ws)
                            <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Title</label>
                    <input type="text" name="title" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
                </div>
            </div>
            <div class="flex gap-3 p-6 border-t border-gray-200">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    Upload
                </button>
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                        class="px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Media Details Modal -->
<div id="mediaDetailsModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="absolute inset-0" onclick="closeMediaDetails()"></div>
    <div class="relative bg-white rounded-xl w-full max-w-5xl mx-4 my-8 border border-gray-200 shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Media Details</h3>
            <button onclick="closeMediaDetails()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="mediaDetailsContent" class="p-6 overflow-y-auto">
            <div class="text-center py-12">
                <svg class="animate-spin h-12 w-12 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMediaId = null;
let autoSaveTimeout = null;

function viewMediaDetails(mediaId) {
    currentMediaId = mediaId;
    document.getElementById('mediaDetailsModal').classList.remove('hidden');

    fetch(`/media/details/${mediaId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const m = data.media;
                const uploadDate = new Date(m.created_at).toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                document.getElementById('mediaDetailsContent').innerHTML = `
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4 border border-gray-200">
                                <img src="${m.full_url}" class="w-full h-auto rounded" alt="${m.alt_text || m.file_name}">
                            </div>
                        </div>
                        <div>
                            <div id="autoSaveStatus" class="hidden mb-4 p-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                                Saving...
                            </div>

                            <p class="text-sm text-gray-600 mb-4">
                                <strong class="text-gray-900">Uploaded:</strong> ${uploadDate}<br>
                                <strong class="text-gray-900">By:</strong> ${m.created_by_name}<br>
                                <strong class="text-gray-900">Filename:</strong> ${m.file_name}<br>
                                <strong class="text-gray-900">Type:</strong> ${m.file_type}<br>
                                <strong class="text-gray-900">Size:</strong> ${Math.round(m.file_size / 1024)} KB
                            </p>

                            ${m.source_type === 'unsplash' ? `
                                <!-- Unsplash Attribution (Read-only) -->
                                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" viewBox="0 0 32 32" fill="currentColor">
                                            <path d="M10 9V0L0 9h10zm12 5h10L22 0v14zM0 18h10v14L0 18zm22 0v14l10-14H22z"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900 font-semibold mb-1">Unsplash Photo</p>
                                            <p class="text-sm text-gray-700">
                                                Photo by <a href="${JSON.parse(m.ai_prompt || '{}').photographer_url}" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">${JSON.parse(m.ai_prompt || '{}').photographer_name}</a> on <a href="https://unsplash.com?utm_source=anewp&utm_medium=referral" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">Unsplash</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Title</label>
                                        <input type="text" value="${m.title || ''}" readonly
                                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Alt Text</label>
                                        <input type="text" value="${m.alt_text || ''}" readonly
                                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Caption</label>
                                        <textarea rows="2" readonly
                                                  class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">${m.caption || ''}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">File URL</label>
                                        <div class="flex gap-2">
                                            <input type="text" value="${m.full_url}" readonly
                                                   class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                            <button type="button" onclick="copyUrl('${m.full_url}')"
                                                    class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg transition">
                                                Copy
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" onclick="confirmDelete(${m.id}, '${m.file_name.replace(/'/g, "\\'")}')"
                                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                                        Remove from Library
                                    </button>
                                </div>
                            ` : `
                                <!-- Regular/AI Generated Media (Editable) -->
                                <form id="updateMediaForm" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Alt Text</label>
                                        <input type="text" name="alt_text" value="${m.alt_text || ''}"
                                               class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Title</label>
                                        <input type="text" name="title" value="${m.title || ''}"
                                               class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Caption</label>
                                        <textarea name="caption" rows="2"
                                                  class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">${m.caption || ''}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">Description</label>
                                        <textarea name="description" rows="3"
                                                  class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">${m.description || ''}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold mb-2 text-gray-900">File URL</label>
                                        <div class="flex gap-2">
                                            <input type="text" value="${m.full_url}" readonly
                                                   class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                            <button type="button" onclick="copyUrl('${m.full_url}')"
                                                    class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg transition">
                                                Copy
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" onclick="confirmDelete(${m.id}, '${m.file_name.replace(/'/g, "\\'")}')"
                                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                                        Delete Permanently
                                    </button>
                                </form>

                                ${m.source_type === 'ai_generated' && m.ai_prompt ? `
                                    <div class="mt-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
                                        <strong>AI Prompt:</strong> ${m.ai_prompt}
                                    </div>
                                ` : ''}
                            `}
                        </div>
                    </div>
                `;

                document.querySelectorAll('.auto-save').forEach(input => {
                    input.addEventListener('input', triggerAutoSave);
                });
            }
        });
}

function closeMediaDetails() {
    document.getElementById('mediaDetailsModal').classList.add('hidden');
}

function triggerAutoSave() {
    clearTimeout(autoSaveTimeout);

    const status = document.getElementById('autoSaveStatus');
    status.classList.remove('hidden');
    status.className = 'mb-4 p-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200';
    status.textContent = 'Saving...';

    autoSaveTimeout = setTimeout(() => {
        autoSaveMedia();
    }, 1500);
}

function autoSaveMedia() {
    const form = document.getElementById('updateMediaForm');
    const formData = new FormData(form);

    fetch(`/media/update/${currentMediaId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const status = document.getElementById('autoSaveStatus');
        status.className = 'mb-4 p-3 bg-green-50 text-green-700 rounded-lg border border-green-200';
        status.textContent = 'Saved!';

        setTimeout(() => {
            status.classList.add('hidden');
        }, 2000);
    })
    .catch(err => {
        const status = document.getElementById('autoSaveStatus');
        status.className = 'mb-4 p-3 bg-red-50 text-red-700 rounded-lg border border-red-200';
        status.textContent = 'Failed to save';
    });
}

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.target;
        btn.textContent = 'Copied!';
        setTimeout(() => {
            btn.textContent = 'Copy';
        }, 2000);
    });
}

function confirmDelete(mediaId, fileName) {
    if (confirm(`Delete "${fileName}"?\n\nThis action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/media/delete/${mediaId}`;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
