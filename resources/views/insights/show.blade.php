@extends('layouts.dashboard')

@section('title', $insight->topic . ' - AneWP')

@section('content')
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('insights.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Back to insights
            </a>
            <div class="flex items-center justify-between mt-4">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-900">{{ $insight->topic }}</h1>
                    <p class="text-gray-500 text-sm mt-1">
                        {{ ucfirst(str_replace('_', ' ', $insight->source_type)) }}
                        • {{ $insight->article_count }} {{ \Illuminate\Support\Str::plural('article', $insight->article_count) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('insights.edit', $insight) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition">
                        <x-heroicon-o-pencil class="w-4 h-4" />
                        Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Status</p>
                    <p class="font-semibold">
                        @if($insight->status === 'in_progress')
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">In Progress</span>
                        @elseif($insight->status === 'completed')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full font-medium">Completed</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full font-medium">Pending</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Source</p>
                    <p class="font-semibold text-gray-900">{{ $insight->source ?? 'Manual' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Created</p>
                    <p class="font-semibold text-gray-900">{{ $insight->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Updated</p>
                    <p class="font-semibold text-gray-900">{{ $insight->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($insight->description)
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3">Description</h3>
            <p class="text-gray-700 leading-relaxed">{{ $insight->description }}</p>
        </div>
        @endif

        <!-- Source URL -->
        @if($insight->source_url)
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3">Source URL</h3>
            <a href="{{ $insight->source_url }}" target="_blank" class="text-blue-600 hover:text-blue-700 break-all inline-flex items-center gap-1">
                {{ $insight->source_url }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
        @endif

        <!-- Linked Articles -->
        @if($insight->posts->isNotEmpty())
        <div class="bg-white rounded-xl p-6 border border-gray-200">
            <h3 class="font-semibold text-gray-900 mb-4">Linked Articles ({{ $insight->posts->count() }})</h3>
            <div class="space-y-3">
                @foreach($insight->posts as $post)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $post->title }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ ucfirst($post->article_type) }} • {{ $post->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <a href="{{ route('posts.show', $post) }}"
                       class="text-blue-600 hover:text-blue-700 transition font-medium inline-flex items-center gap-1">
                        View
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl p-8 border border-gray-200 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 mb-4">No articles linked to this insight yet</p>
            <a href="{{ route('posts.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Article
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
