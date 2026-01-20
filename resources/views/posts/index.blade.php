@extends('layouts.dashboard')

@section('title', __('posts.title') . ' - AneWP')

@section('content')
<div class="p-8" x-data="bulkAmplifyData()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('posts.title') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ trans_choice('posts.subtitle_count', $posts->total(), ['count' => $posts->total()]) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Bulk Amplify Button (shows when posts selected) -->
            <button x-show="selectedPosts.length > 0"
                    @click="bulkAmplify()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                <x-heroicon-o-share class="w-5 h-5" />
                <span x-text="`{{ str_replace(':count', '${selectedPosts.length}', __('posts.bulk_amplify')) }}`"></span>
            </button>
            <button x-show="selectedPosts.length > 0"
                    @click="bulkDelete()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                <x-heroicon-o-trash class="w-5 h-5" />
                <span x-text="`{{ str_replace(':count', '${selectedPosts.length}', __('posts.bulk_delete')) }}`"></span>
            </button>
            <select class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>{{ __('posts.filters.all_statuses') }}</option>
                <option>{{ __('posts.filters.published') }}</option>
                <option>{{ __('posts.filters.draft') }}</option>
                <option>{{ __('posts.filters.scheduled') }}</option>
            </select>
            <a href="{{ route('posts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('posts.cta_add') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($posts->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 text-lg mb-2">{{ __('posts.empty_title') }}</p>
            <p class="text-gray-400 mb-6">{{ __('posts.empty_subtitle') }}</p>
        </div>
    @else
        <div class="bg-white">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input type="checkbox"
                                   @change="toggleAll($event.target.checked)"
                                   :checked="selectedPosts.length === {{ $posts->count() }} && {{ $posts->count() }} > 0"
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.title') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.type') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.insight') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.source') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.created') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.status') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.amplify') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('posts.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition"
                            :class="selectedPosts.includes({{ $post->id }}) ? 'bg-blue-50' : ''">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       :checked="selectedPosts.includes({{ $post->id }})"
                                       @change="togglePost({{ $post->id }})"
                                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</div>
                                @if($post->seo_focus_keyword)
                                    <div class="text-xs text-gray-400 mt-1">{{ $post->seo_focus_keyword }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-600">{{ ucfirst($post->article_type) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($post->insight)
                                    <a href="{{ route('insights.show', $post->insight) }}" class="text-indigo-600 hover:text-indigo-700">
                                        {{ \Illuminate\Support\Str::limit($post->insight->topic, 30) }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $post->source_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $target = $post->targets->first();
                                    if (!$target) {
                                        $status = 'local';
                                    } elseif ($target->scheduled_at && !$target->wp_post_id) {
                                        $status = 'scheduled';
                                    } else {
                                        $status = $target->status;
                                    }
                                    $colors = [
                                        'draft' => 'text-yellow-600',
                                        'published' => 'text-green-600',
                                        'scheduled' => 'text-blue-600',
                                        'local' => 'text-gray-400'
                                    ];
                                @endphp
                                <div>
                                    <span class="text-sm {{ $colors[$status] ?? 'text-gray-400' }}">
                                        {{ $status === 'published' ? '✓ ' : '' }}{{ __('posts.statuses.' . $status, [], app()->getLocale()) }}
                                    </span>
                                    @if($target && $target->scheduled_at && !$target->wp_post_id)
                                        <div class="text-xs text-gray-400 mt-1">{{ $target->scheduled_at->format('M d, H:i') }}</div>
                                    @endif
                                    @if($target && $target->wp_post_id)
                                        <div class="text-xs text-gray-400 mt-1">WP: {{ $target->wp_post_id }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            onclick="window.dispatchEvent(new CustomEvent('open-amplify-modal', { detail: { postId: {{ $post->id }} } }))"
                                            class="text-gray-400 hover:text-blue-600 transition cursor-pointer"
                                            title="{{ __('posts.actions.amplify_social') }}">
                                        <x-heroicon-o-share class="w-4 h-4" />
                                    </button>
                                    <a href="{{ route('assets.index', ['post_id' => $post->id]) }}"
                                       class="text-gray-400 hover:text-green-600 transition cursor-pointer"
                                       title="{{ __('posts.actions.download') }}">
                                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('posts.show', $post) }}"
                                       class="text-gray-400 hover:text-indigo-600 transition"
                                       title="{{ __('posts.actions.view') }}">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('posts.edit', $post) }}"
                                       class="text-gray-400 hover:text-blue-600 transition"
                                       title="{{ __('posts.actions.edit') }}">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('{{ __('posts.actions.confirm_delete') }}')"
                                                class="text-gray-400 hover:text-red-600 transition"
                                                title="{{ __('posts.actions.delete') }}">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @endif
</div>

{{-- Include Amplify Modal Component --}}
<x-amplify-modal />

@push('scripts')
<script>
function bulkAmplifyData() {
    return {
        selectedPosts: [],
        bulkDeleteUrl: '{{ route('posts.bulk-delete') }}',
        bulkDeleteConfirm: '{{ __('posts.actions.confirm_bulk_delete') }}',

        togglePost(postId) {
            const index = this.selectedPosts.indexOf(postId);
            if (index > -1) {
                this.selectedPosts.splice(index, 1);
            } else {
                this.selectedPosts.push(postId);
            }
        },

        toggleAll(checked) {
            if (checked) {
                // Select all posts on current page
                this.selectedPosts = [
                    @foreach($posts as $post)
                        {{ $post->id }},
                    @endforeach
                ];
            } else {
                this.selectedPosts = [];
            }
        },

        bulkAmplify() {
            if (this.selectedPosts.length === 0) {
                alert('Please select at least one post');
                return;
            }

            // Trigger amplify modal with multiple post IDs
            window.dispatchEvent(new CustomEvent('open-amplify-modal', {
                detail: {
                    postIds: this.selectedPosts,  // Array of IDs
                    callback: (data) => {
                        // After successful amplify, redirect to assets
                        window.location.href = '/assets';
                    }
                }
            }));
        },

        async bulkDelete() {
            if (this.selectedPosts.length === 0) {
                alert('Please select at least one post');
                return;
            }
            if (!confirm(this.bulkDeleteConfirm)) {
                return;
            }

            try {
                const response = await fetch(this.bulkDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: this.selectedPosts }),
                });

                if (!response.ok) {
                    throw new Error('Failed to delete posts');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'Failed to delete posts');
            }
        }
    };
}
</script>
@endpush

@endsection
