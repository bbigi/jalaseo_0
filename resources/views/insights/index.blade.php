@extends('layouts.dashboard')

@section('title', __('insights.title') . ' - AneWP')

@section('content')
<div class="p-8" x-data="bulkInsightsData()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('insights.title') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ trans_choice('insights.subtitle_count', $insights->total(), ['count' => $insights->total()]) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button x-show="selectedInsights.length > 0"
                    @click="bulkDelete()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                <x-heroicon-o-trash class="w-5 h-5" />
                <span x-text="`{{ str_replace(':count', '${selectedInsights.length}', __('insights.bulk_delete')) }}`"></span>
            </button>
            <select class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>{{ __('insights.filters.all_statuses') }}</option>
                <option>{{ __('insights.filters.pending') }}</option>
                <option>{{ __('insights.filters.in_progress') }}</option>
                <option>{{ __('insights.filters.completed') }}</option>
            </select>
            <a href="{{ route('insights.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('insights.cta_add') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($insights->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <p class="text-gray-600 text-lg mb-2">{{ __('insights.empty_title') }}</p>
            <p class="text-gray-400 mb-6">{{ __('insights.empty_subtitle') }}</p>
        </div>
    @else
        <div class="bg-white">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input type="checkbox"
                                   @change="toggleAll($event.target.checked)"
                                   :checked="selectedInsights.length === {{ $insights->count() }} && {{ $insights->count() }} > 0"
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.topic') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.source') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.articles') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.status') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.created') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($insights as $insight)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition"
                            :class="selectedInsights.includes({{ $insight->id }}) ? 'bg-blue-50' : ''">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       :checked="selectedInsights.includes({{ $insight->id }})"
                                       @change="toggleInsight({{ $insight->id }})"
                                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $insight->topic }}</div>
                                @if($insight->description)
                                    <div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($insight->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $insight->source_type ?? 'manual')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-blue-600 font-semibold">{{ $insight->article_count }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($insight->status === 'in_progress')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">{{ __('insights.statuses.in_progress') }}</span>
                                @elseif($insight->status === 'completed')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full font-medium">{{ __('insights.statuses.completed') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full font-medium">{{ __('insights.statuses.pending') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                {{ $insight->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('generate.index', ['source' => 'insight', 'source_id' => $insight->id, 'title' => $insight->topic, 'description' => $insight->description, 'website_id' => $insight->website_id]) }}"
                                       class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition cursor-pointer"
                                       title="{{ __('insights.actions.generate') }}">
                                        <x-heroicon-o-sparkles class="w-4 h-4" />
                                        <span class="sr-only">{{ __('insights.actions.generate') }}</span>
                                    </a>
                                    <a href="{{ route('insights.show', $insight) }}"
                                       class="text-gray-400 hover:text-indigo-600 transition cursor-pointer"
                                       title="{{ __('insights.actions.view') }}">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        <span class="sr-only">{{ __('insights.actions.view') }}</span>
                                    </a>
                                    <a href="{{ route('insights.edit', $insight) }}"
                                       class="text-gray-400 hover:text-blue-600 transition cursor-pointer"
                                       title="{{ __('insights.actions.edit') }}">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                        <span class="sr-only">{{ __('insights.actions.edit') }}</span>
                                    </a>
                                    <button type="button"
                                            onclick="openDeleteModal('{{ route('insights.destroy', $insight) }}')"
                                            class="text-gray-400 hover:text-red-600 transition cursor-pointer"
                                            title="{{ __('insights.actions.delete') }}">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                        <span class="sr-only">{{ __('insights.actions.delete') }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $insights->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal (Compact Light Theme) -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4 shadow-xl">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ __('insights.modal.title') }}</h3>
                <p class="text-sm text-gray-600">{{ __('insights.modal.body') }}</p>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition text-sm">
                {{ __('insights.modal.cancel') }}
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition text-sm">
                    {{ __('insights.modal.confirm') }}
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function bulkInsightsData() {
    return {
        selectedInsights: [],
        bulkDeleteUrl: '{{ route('insights.bulk-delete') }}',
        bulkDeleteConfirm: '{{ __('insights.actions.confirm_bulk_delete') }}',

        toggleInsight(id) {
            const index = this.selectedInsights.indexOf(id);
            if (index > -1) {
                this.selectedInsights.splice(index, 1);
            } else {
                this.selectedInsights.push(id);
            }
        },

        toggleAll(checked) {
            if (checked) {
                this.selectedInsights = [
                    @foreach($insights as $insight)
                        {{ $insight->id }},
                    @endforeach
                ];
            } else {
                this.selectedInsights = [];
            }
        },

        async bulkDelete() {
            if (this.selectedInsights.length === 0) {
                alert('Please select at least one insight');
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
                    body: JSON.stringify({ ids: this.selectedInsights }),
                });

                if (!response.ok) {
                    throw new Error('Failed to delete insights');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'Failed to delete insights');
            }
        }
    };
}
</script>
@endpush

<script>
function openDeleteModal(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection
