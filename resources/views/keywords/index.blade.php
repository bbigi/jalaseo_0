@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">My Keywords</h1>
            <p class="text-gray-600 mt-1">Manage and track your SEO keywords</p>
        </div>
        <div>
            <form method="GET" action="{{ route('keywords.index') }}">
                <select name="website_id" onchange="this.form.submit()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Websites</option>
                    @foreach($websites as $site)
                        <option value="{{ $site->id }}" {{ request('website_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="flex gap-4 mb-8">
        <div class="bg-white rounded-lg p-4 border border-gray-100 flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Keywords</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <x-heroicon-o-hashtag class="w-10 h-10 text-blue-500" />
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-100 flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">High Priority</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['high_priority'] }}</p>
                </div>
                <x-heroicon-o-fire class="w-10 h-10 text-red-500" />
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-100 flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Active Tracking</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active_tracking'] }}</p>
                </div>
                <x-heroicon-o-chart-bar class="w-10 h-10 text-green-500" />
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-100 flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">In Google</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['in_google'] }}</p>
                </div>
                <x-heroicon-o-check-circle class="w-10 h-10 text-purple-500" />
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-100 flex-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Targets</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['targets'] }}</p>
                </div>
                <x-heroicon-o-arrow-trending-up class="w-10 h-10 text-amber-500" />
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex -mb-px">
                <a href="{{ route('keywords.index', ['tab' => 'all']) }}"
                   class="px-6 py-3 text-sm font-medium {{ $tab === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    All Keywords ({{ $stats['total'] }})
                </a>
                <a href="{{ route('keywords.index', ['tab' => 'high_priority']) }}"
                   class="px-6 py-3 text-sm font-medium {{ $tab === 'high_priority' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    High Priority ({{ $stats['high_priority'] }})
                </a>
                <a href="{{ route('keywords.index', ['tab' => 'active_tracking']) }}"
                   class="px-6 py-3 text-sm font-medium {{ $tab === 'active_tracking' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Active Tracking ({{ $stats['active_tracking'] }})
                </a>
                <a href="{{ route('keywords.index', ['tab' => 'in_google']) }}"
                   class="px-6 py-3 text-sm font-medium {{ $tab === 'in_google' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    In Google ({{ $stats['in_google'] }})
                </a>
                <a href="{{ route('keywords.index', ['tab' => 'targets']) }}"
                   class="px-6 py-3 text-sm font-medium {{ $tab === 'targets' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Targets ({{ $stats['targets'] }})
                </a>
            </nav>
        </div>

        <!-- Keywords Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Keyword</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Website</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Volume</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Position</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Priority</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Source</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($keywords as $keyword)
                    <tr class="hover:bg-gray-50" id="keyword-row-{{ $keyword->id }}">
                        <td class="px-3 py-4">
                            <div class="flex items-center">
                                @if($keyword->has_deep_research_data)
                                <x-heroicon-o-beaker class="w-4 h-4 text-purple-500 mr-2" title="Has Deep Research Data" />
                                @endif
                                <span class="text-gray-900 font-medium text-sm">{{ $keyword->keyword }}</span>
                            </div>
                            @if($keyword->intent)
                            <span class="text-xs text-gray-500">{{ ucfirst($keyword->intent) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-700">
                            <a href="{{ route('websites.show', $keyword->website_id) }}" class="hover:text-blue-600">
                                {{ $keyword->website->name }}
                            </a>
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-700">
                            {{ number_format($keyword->volume) }}
                        </td>
                        <td class="px-3 py-4">
                            @if($keyword->current_position)
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-900 font-medium text-sm">#{{ $keyword->current_position }}</span>
                                    @if($keyword->target_position)
                                        <span class="text-xs text-gray-500">/ {{ $keyword->target_position }}</span>
                                        @if($keyword->is_on_target)
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-green-500" />
                                        @else
                                            <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-amber-500" />
                                        @endif
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">Not ranked</span>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            <select onchange="updatePriority({{ $keyword->id }}, this.value)"
                                    class="bg-white text-gray-900 text-sm rounded border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500">
                                <option value="high" {{ $keyword->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ $keyword->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ $keyword->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="none" {{ $keyword->priority === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </td>
                        <td class="px-3 py-4">
                            <select onchange="updateTracking({{ $keyword->id }}, this.value)"
                                    class="bg-white text-gray-900 text-sm rounded border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-blue-500">
                                <option value="active" {{ $keyword->tracking_status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ $keyword->tracking_status === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="archived" {{ $keyword->tracking_status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </td>
                        <td class="px-3 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $keyword->source === 'gsc_sync' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $keyword->source === 'deep_research' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $keyword->source === 'manual' ? 'bg-gray-100 text-gray-700' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $keyword->source)) }}
                            </span>
                        </td>
                        <td class="px-3 py-4 text-right space-x-2">
                            <button onclick="openTargetModal({{ $keyword->id }}, {{ $keyword->current_position ?? 'null' }}, {{ $keyword->target_position ?? 'null' }})"
                                    class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-blue-600 rounded hover:text-blue-700 cursor-pointer"
                                    title="Save and Track">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                </svg>
                            </button>
                            <a href="{{ route('generate.index', ['keyword' => $keyword->keyword, 'title' => $keyword->keyword, 'website_id' => $keyword->website_id]) }}"
                               class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded hover:bg-blue-700"
                               title="Generate Article">
                                <x-heroicon-o-sparkles class="w-4 h-4" />
                            </a>
                            <form action="{{ route('keywords.destroy', $keyword->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this keyword?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded hover:text-red-700" title="Delete">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <x-heroicon-o-hashtag class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                            <p class="text-lg">No keywords found</p>
                            <p class="text-sm mt-2">Start by running Deep Research or syncing with Google Search Console</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($keywords->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $keywords->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Target Position Modal -->
<div id="targetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md border border-gray-200 shadow-xl">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Set Target Position</h3>
                <button onclick="closeTargetModal()" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6">
                <input type="hidden" id="targetKeywordId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Position</label>
                    <input type="text" id="currentPosition" readonly
                           class="w-full bg-gray-100 text-gray-600 rounded-lg px-4 py-2 border border-gray-300">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Position</label>
                    <input type="number" id="targetPosition" min="1" max="100"
                           class="w-full bg-white text-gray-900 rounded-lg px-4 py-2 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., 10">
                </div>
                <div class="flex gap-3">
                    <button onclick="closeTargetModal()"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button onclick="saveTarget()"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save Target
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updatePriority(keywordId, priority) {
    fetch(`/keywords/${keywordId}/priority`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ priority })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Priority updated successfully', 'success');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to update priority', 'error');
    });
}

function updateTracking(keywordId, tracking_status) {
    fetch(`/keywords/${keywordId}/tracking`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ tracking_status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Tracking status updated', 'success');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to update tracking', 'error');
    });
}

function openTargetModal(keywordId, currentPos, targetPos) {
    document.getElementById('targetKeywordId').value = keywordId;
    document.getElementById('currentPosition').value = currentPos ? `#${currentPos}` : 'Not ranked';
    document.getElementById('targetPosition').value = targetPos || '';
    document.getElementById('targetModal').classList.remove('hidden');
}

function closeTargetModal() {
    document.getElementById('targetModal').classList.add('hidden');
}

function saveTarget() {
    const keywordId = document.getElementById('targetKeywordId').value;
    const targetPosition = document.getElementById('targetPosition').value;

    if (!targetPosition || targetPosition < 1 || targetPosition > 100) {
        showToast('Please enter a valid position (1-100)', 'error');
        return;
    }

    fetch(`/keywords/${keywordId}/target`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ target_position: parseInt(targetPosition) })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Target position saved', 'success');
            closeTargetModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to save target', 'error');
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 shadow-lg ${
        type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush

@endsection
