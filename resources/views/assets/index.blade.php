@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('assets.title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('assets.subtitle') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('assets.search') }}</label>
                <input type="text" id="searchInput" placeholder="{{ __('assets.search_placeholder') }}"
                       class="w-full bg-white text-gray-900 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('assets.channel') }}</label>
                <select id="channelFilter" class="w-full bg-white text-gray-900 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('assets.all_channels') }}</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('assets.status') }}</label>
                <select id="statusFilter" class="w-full bg-white text-gray-900 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('assets.all_status') }}</option>
                    <option value="draft">{{ __('assets.status_draft') }}</option>
                    <option value="ready">{{ __('assets.status_ready') }}</option>
                    <option value="scheduled">{{ __('assets.status_scheduled') }}</option>
                    <option value="published">{{ __('assets.status_published') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Assets Grid (4:5 ratio Instagram, hover buttons) -->
    <div id="assetsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-1">
        @forelse($assets as $asset)
            <div class="group bg-gray-100 rounded-lg cursor-pointer relative" style="overflow: hidden;" onclick="openAssetDetail({{ $asset->id }})" data-asset-id="{{ $asset->id }}">
                <div style="padding-bottom: 125%;"></div>

                <!-- Thumbnail -->
                @if($asset->visual_path)
                    @php
                        $isExternalUrl = str_starts_with($asset->visual_path, 'http://') || str_starts_with($asset->visual_path, 'https://');
                        $thumbnailSrc = $isExternalUrl ? $asset->visual_path : asset('storage/' . $asset->visual_path);
                    @endphp
                    <img src="{{ $thumbnailSrc }}" alt="Asset" class="absolute inset-0 w-full h-full object-cover" data-asset-thumbnail="{{ $asset->id }}">
                @elseif($asset->canvas_json)
                    <div class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-purple-50 to-pink-50">
                        <x-heroicon-o-photo class="w-12 h-12 text-purple-400 mb-2" />
                        <span class="px-3 py-1 bg-purple-600 text-white text-xs font-semibold rounded-full">Render Required</span>
                    </div>
                @else
                    <div class="absolute inset-0 w-full h-full flex items-center justify-center">
                        <x-heroicon-o-photo class="w-12 h-12 text-gray-400" />
                    </div>
                @endif

                <!-- Channel Badge (Icon Only) -->
                <div class="absolute top-2 left-2 p-1 rounded-md text-white flex items-center justify-center
                    @if($asset->channel === 'instagram') bg-gradient-to-br from-purple-600 to-pink-600
                    @elseif($asset->channel === 'facebook') bg-blue-600
                    @elseif($asset->channel === 'twitter') bg-sky-500
                    @elseif($asset->channel === 'linkedin') bg-blue-700
                    @elseif($asset->channel === 'tiktok') bg-black
                    @else bg-gray-600
                    @endif">
                    @if($asset->channel === 'instagram')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    @elseif($asset->channel === 'facebook')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    @elseif($asset->channel === 'twitter')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    @elseif($asset->channel === 'linkedin')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    @elseif($asset->channel === 'tiktok')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                    @endif
                </div>

                <!-- Hover Overlay with Buttons -->
                <div class="absolute w-full inset-x-0 bottom-0 p-3 bg-black/70 translate-y-full group-hover:translate-y-0 transition-transform duration-200 flex gap-2">
                    @if($asset->canvas_json && !$asset->visual_path)
                    <button onclick="event.stopPropagation(); openImageEditor({{ $asset->id }})"
                            class="flex-1 px-3 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm rounded-lg font-semibold flex items-center justify-center gap-1">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        Complete Rendering
                    </button>
                    @else
                    <button onclick="event.stopPropagation(); openImageEditor({{ $asset->id }})"
                            class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg font-semibold flex items-center justify-center gap-1">
                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                        {{ __('assets.edit') }}
                    </button>
                    @endif
                    @if($asset->visual_path)
                    @php
                        $isExternal = str_starts_with($asset->visual_path, 'http://') || str_starts_with($asset->visual_path, 'https://');
                        $downloadHref = $isExternal ? $asset->visual_path : asset('storage/' . $asset->visual_path);
                    @endphp
                    <a href="{{ $downloadHref }}" download onclick="event.stopPropagation()"
                       class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-semibold flex items-center justify-center">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                    </a>
                    @endif
                    <button onclick="event.stopPropagation(); deleteAsset({{ $asset->id }})"
                            class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg font-semibold flex items-center justify-center">
                        <x-heroicon-o-trash class="w-5 h-5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <x-heroicon-o-photo class="w-16 h-16 mx-auto mb-4 text-gray-400" />
                        <p class="text-gray-600 text-lg">{{ __('assets.no_assets') }}</p>
            </div>
        @endforelse
    </div>

    @if($assets->hasPages())
        <div class="mt-8">{{ $assets->links() }}</div>
    @endif

</div>

<!-- Asset Detail Popup -->
<div id="assetDetailModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="absolute inset-0" onclick="closeAssetDetail()"></div>
    <div class="relative bg-white rounded-xl w-full max-w-5xl mx-4 my-8 border border-gray-200 shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">{{ __('assets.asset_details_title') }}</h3>
            <button onclick="closeAssetDetail()" class="text-gray-400 hover:text-gray-600">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>
        <div id="assetDetailsContent" class="p-6 overflow-y-auto">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentAssetId = null;
let autoSaveTimeout = null;
const assetI18n = {
    saving: @json(__('assets.saving')),
    saved: @json(__('assets.saved')),
    saveFailed: @json(__('assets.save_failed')),
    post: @json(__('assets.post')),
    channel: @json(__('assets.channel')),
    status: @json(__('assets.status')),
    created: @json(__('assets.created')),
    copyText: @json(__('assets.copy_text')),
    hashtags: @json(__('assets.hashtags')),
    copyToClipboard: @json(__('assets.copy_button')),
    editVisual: @json(__('assets.edit_visual')),
    download: @json(__('assets.download')),
};

function openAssetDetail(assetId) {
    currentAssetId = assetId;
    document.getElementById('assetDetailModal').classList.remove('hidden');

    fetch(`/assets/details/${assetId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const asset = data.asset;

                document.getElementById('assetDetailsContent').innerHTML = `
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="relative w-full">
                                    <div style="padding-bottom: 125%;"></div>
                                    <img src="${asset.visual_url || ''}" class="absolute inset-0 w-full h-full object-cover rounded" alt="${asset.title}">
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button onclick="openImageEditor(${asset.id})" class="flex-1 px-4 py-2 ${asset.visual_url ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700'} text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${asset.visual_url ? 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10' : 'M9.813 15.904L9 18.75l2.846-.813a4.5 4.5 0 001.897-1.13l8.932-8.932a.75.75 0 000-1.06l-1.5-1.5a.75.75 0 00-1.06 0l-8.932 8.932a4.5 4.5 0 00-1.13 1.897z'}"/></svg>
                                    ${asset.visual_url ? assetI18n.editVisual : 'Complete Rendering'}
                                </button>
                                ${asset.visual_url ? `<a href="${asset.visual_url}" download class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    ${assetI18n.download}
                                </a>` : ''}
                            </div>
                        </div>
                        <div>
                            <div id="autoSaveStatus" class="hidden mb-4 p-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                                ${assetI18n.saving}
                            </div>

                            <p class="text-sm text-gray-600 mb-4">
                                <strong class="text-gray-900">${assetI18n.post}:</strong> ${asset.post_title}<br>
                                <strong class="text-gray-900">${assetI18n.channel}:</strong> ${asset.channel}<br>
                                <strong class="text-gray-900">${assetI18n.status}:</strong> <span class="capitalize">${asset.status}</span><br>
                                <strong class="text-gray-900">${assetI18n.created}:</strong> ${asset.created_at}
                            </p>

                            <form id="updateAssetForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-gray-900">${assetI18n.copyText}</label>
                                    <textarea name="copy_text" rows="6" class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">${asset.copy_text || ''}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-gray-900">${assetI18n.hashtags}</label>
                                    <textarea name="hashtags" rows="3" class="auto-save w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" placeholder="#example #hashtags">${asset.hashtags || ''}</textarea>
                                </div>

                                <button type="button" onclick="copyToClipboard('${asset.copy_text ? asset.copy_text.replace(/'/g, "\\'").replace(/\n/g, '\\n') : ''}')" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                                    <x-heroicon-o-clipboard-document class="w-5 h-5" />
                                    ${assetI18n.copyToClipboard}
                                </button>
                            </form>

                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Schedule Publish</h4>
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600 mb-1">Publish at (for schedule)</label>
                                            <input type="datetime-local" id="scheduleAt" value="${asset.schedule_suggest || ''}"
                                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                            <p class="text-xs text-gray-500 mt-1">Pilih waktu untuk jadwal. Kosongkan jika mau publish sekarang.</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="scheduleAsset(${asset.id}, false)" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold">Schedule</button>
                                            <button onclick="scheduleAsset(${asset.id}, true)" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-semibold">Publish now</button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">Publish now akan langsung mengantre posting (scheduled_at = sekarang). Schedule butuh tanggal & jam.</p>
                                </div>
                                <div id="scheduleStatus" class="mt-2 text-xs text-gray-600">
                                    ${asset.last_schedule ? `Last: ${asset.last_schedule.status} at ${asset.last_schedule.scheduled_at || '-'}${asset.last_schedule.last_error ? ' | Error: ' + asset.last_schedule.last_error : ''}` : 'No schedule yet.'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.querySelectorAll('.auto-save').forEach(input => {
                    input.addEventListener('input', triggerAutoSave);
                });
            }
        });
}

function closeAssetDetail() {
    document.getElementById('assetDetailModal').classList.add('hidden');
}

function copyAsset(id) {
    fetch(`/assets/details/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.asset.copy_text) {
                copyToClipboard(data.asset.copy_text);
            }
        });
}

function deleteAsset(id) {
    if (!confirm('Delete this asset? This action cannot be undone.')) {
        return;
    }

    fetch(`/assets/delete/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to delete asset');
        }
    })
    .catch(() => {
        alert('Error deleting asset');
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalContent;
        }, 2000);
    });
}

async function scheduleAsset(assetId, publishNow = false) {
    const scheduleInput = document.getElementById('scheduleAt');
    const scheduledAt = scheduleInput ? scheduleInput.value : null;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!publishNow && !scheduledAt) {
        alert('Pilih tanggal & waktu untuk schedule.');
        return;
    }

    try {
        const res = await fetch(`/assets/${assetId}/schedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                scheduled_at: publishNow ? null : scheduledAt,
                publish_now: publishNow,
            }),
        });

        const data = await res.json();

        if (data.success) {
            const statusEl = document.getElementById('scheduleStatus');
            if (statusEl) {
                statusEl.textContent = `Last: ${data.schedule.status} at ${data.schedule.scheduled_at}`;
            }
            alert(publishNow ? 'Publish now: job dikirim.' : 'Schedule created.');
        } else {
            alert('Failed to schedule. Please try again.');
        }
    } catch (e) {
        alert('Failed to schedule. Please try again.');
    }
}

function triggerAutoSave() {
    clearTimeout(autoSaveTimeout);

    const status = document.getElementById('autoSaveStatus');
    status.classList.remove('hidden');
    status.className = 'mb-4 p-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200';
    status.textContent = assetI18n.saving;

    autoSaveTimeout = setTimeout(() => {
        autoSaveAsset();
    }, 1500);
}

function autoSaveAsset() {
    const form = document.getElementById('updateAssetForm');
    const formData = new FormData(form);

    fetch(`/assets/update/${currentAssetId}`, {
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
        status.textContent = assetI18n.saved;

        setTimeout(() => {
            status.classList.add('hidden');
        }, 2000);
    })
    .catch(err => {
        const status = document.getElementById('autoSaveStatus');
        status.className = 'mb-4 p-3 bg-red-50 text-red-700 rounded-lg border border-red-200';
        status.textContent = assetI18n.saveFailed;
    });
}

// Filters
const searchInput = document.getElementById('searchInput');
const channelFilter = document.getElementById('channelFilter');
const statusFilter = document.getElementById('statusFilter');

let filterTimeout;
function applyFilters() {
    const params = new URLSearchParams();
    if (searchInput.value) params.append('search', searchInput.value);
    if (channelFilter.value) params.append('channel', channelFilter.value);
    if (statusFilter.value) params.append('status', statusFilter.value);
    window.location.href = '{{ route('assets.index') }}?' + params.toString();
}

searchInput.addEventListener('input', () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(applyFilters, 500);
});
channelFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);

// Listen for visual editor save event to refresh asset data
window.addEventListener('visual-updated', (event) => {
    const updatedAssetId = event.detail.assetId;

    // Refresh asset data from server
    fetch(`/assets/details/${updatedAssetId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update asset in grid
                const assetCard = document.querySelector(`[data-asset-id="${updatedAssetId}"]`);
                if (assetCard) {
                    // Update thumbnail
                    const thumbnail = assetCard.querySelector('img');
                    if (thumbnail && data.asset.visual_url) {
                        thumbnail.src = data.asset.visual_url + '?t=' + Date.now(); // Cache bust
                    }

                    // Update download button href
                    const downloadBtn = assetCard.querySelector('a[download]');
                    if (downloadBtn && data.asset.visual_url) {
                        downloadBtn.href = data.asset.visual_url;
                    }
                }

                // If detail modal is open for this asset, update it too
                const modal = document.getElementById('assetDetailModal');
                if (modal && !modal.classList.contains('hidden')) {
                    const modalAssetId = modal.dataset.currentAssetId;
                    if (modalAssetId == updatedAssetId) {
                        openAssetDetail(updatedAssetId); // Reload modal content
                    }
                }
            }
        })
        .catch(err => console.error('Failed to refresh asset:', err));
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('search')) searchInput.value = urlParams.get('search');
if (urlParams.get('channel')) channelFilter.value = urlParams.get('channel');
if (urlParams.get('status')) statusFilter.value = urlParams.get('status');

// Open image editor
function openImageEditor(assetId) {
    window.location.href = `/image-editor?asset_id=${assetId}`;
}

// Listen for asset updates from image editor
window.addEventListener('message', function(event) {
    if (event.data.type === 'asset-updated') {
        const assetId = event.data.assetId;
        const visualUrl = event.data.visualUrl;

        // Update thumbnail in grid
        const thumbnail = document.querySelector(`[data-asset-id="${assetId}"] img`);
        if (thumbnail && visualUrl) {
            thumbnail.src = visualUrl + '?t=' + Date.now(); // Cache bust
        }

        // Reload grid to show updated asset
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
});
</script>
@endpush
@endsection

<x-asset-library />
