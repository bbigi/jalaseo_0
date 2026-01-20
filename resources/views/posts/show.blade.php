@extends('layouts.dashboard')

@section('title', $post->title . ' - AneWP')

@section('content')
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                {{ __('posts.show.back') }}
            </a>
            <div class="flex items-center justify-between mt-4">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-900">{{ $post->title }}</h1>
                    <p class="text-gray-500 text-sm mt-1">
                        {{ ucfirst($post->article_type) }} {{ __('posts.show.article_label') }}
                        @if($post->insight)
                            • {{ __('posts.show.linked_to') }} <a href="{{ route('insights.show', $post->insight) }}" class="text-blue-600 hover:text-blue-700">{{ $post->insight->topic }}</a>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('posts.edit', $post) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition">
                        <x-heroicon-o-pencil class="w-4 h-4" />
                        {{ __('posts.actions.edit') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.article_type') }}</p>
                    <p class="font-semibold text-gray-900">{{ ucfirst($post->article_type) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.source') }}</p>
                    <p class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $post->source_type)) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.created') }}</p>
                    <p class="font-semibold text-gray-900">{{ $post->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.updated') }}</p>
                    <p class="font-semibold text-gray-900">{{ $post->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Featured Image -->
        @if($post->featuredMedia)
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('posts.show.featured_image') }}</h3>
            <img src="{{ asset('storage/' . $post->featuredMedia->file_path) }}"
                 alt="{{ $post->featuredMedia->alt_text ?? $post->title }}"
                 class="w-full h-auto rounded-lg border border-gray-200">
            @if($post->featuredMedia->title)
                <p class="text-sm text-gray-500 mt-2">{{ $post->featuredMedia->title }}</p>
            @endif
        </div>
        @endif

        <!-- SEO Info -->
        @if($post->seo_focus_keyword || $post->seo_meta_description)
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('posts.show.seo') }}</h3>
            @if($post->seo_focus_keyword)
                <div class="mb-3">
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.focus_keyword') }}</p>
                    <p class="font-semibold text-gray-900">{{ $post->seo_focus_keyword }}</p>
                </div>
            @endif
            @if($post->seo_meta_description)
                <div>
                    <p class="text-gray-500 text-sm mb-1">{{ __('posts.show.meta_description') }}</p>
                    <p class="text-gray-700">{{ $post->seo_meta_description }}</p>
                </div>
            @endif
        </div>
        @endif

        <!-- Content -->
        <div class="bg-white rounded-xl p-8 border border-gray-200 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('posts.show.content') }}</h3>
            <div class="prose max-w-none">
                {!! $post->content !!}
            </div>
        </div>

        <!-- Publishing Targets -->
        @if($post->targets->isNotEmpty())
        <div class="bg-white rounded-xl p-6 border border-gray-200">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('posts.show.publishing_targets') }}</h3>
            <div class="space-y-3">
                @foreach($post->targets as $target)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $target->website->name }}</p>
                        <p class="text-sm text-gray-500">{{ $target->website->url }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-xs rounded-full font-medium
                            @if($target->status === 'published') bg-green-100 text-green-700
                            @elseif($target->status === 'scheduled') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ __('posts.statuses.' . $target->status, [], app()->getLocale()) }}
                        </span>
                        @if($target->published_at)
                            <p class="text-xs text-gray-500 mt-1">{{ $target->published_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
