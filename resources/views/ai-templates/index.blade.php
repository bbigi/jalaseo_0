@extends('layouts.dashboard')

@section('title', __('ai-templates.title') . ' - AneWP')

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('ai-templates.title') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ trans_choice('ai-templates.subtitle_count', $templates->count(), ['count' => $templates->count()]) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ai-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('ai-templates.cta_add') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($templates->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 text-lg mb-2">{{ __('ai-templates.empty_title') }}</p>
            <p class="text-gray-400 mb-6">{{ __('ai-templates.empty_subtitle') }}</p>
        </div>
    @else
        <div class="bg-white">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.name') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.ai_model') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.language') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.article_size') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.tone') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.created') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ai-templates.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                @if($template->article_type)
                                    <div class="text-xs text-gray-400 mt-1">{{ $template->article_type }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($template->ai_provider && $template->ai_model)
                                    <div class="text-xs text-gray-600">
                                        <div class="font-medium">{{ ucfirst($template->ai_provider) }}</div>
                                        <div class="text-gray-400 mt-0.5">{{ \App\Helpers\AiModelHelper::getModelDisplayName($template->ai_provider, $template->ai_model) }}</div>
                                    </div>
                                @elseif($template->ai_provider)
                                    <span class="text-xs text-gray-600">{{ ucfirst($template->ai_provider) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @php
                                    $languages = \App\Helpers\CountryHelper::getLanguages();
                                    $lang = collect($languages)->firstWhere('code', $template->language);
                                @endphp
                                @if($lang)
                                    <span class="mr-1">{{ $lang['flag'] }}</span>{{ explode(' ', $lang['name'])[0] }}
                                @else
                                    {{ $template->language }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-600">{{ ucfirst($template->article_size) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($template->tone)
                                    <span class="text-xs text-gray-600">{{ $template->tone }}</span>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('ai-templates.dash') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $template->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('ai-templates.edit', $template) }}"
                                       class="text-gray-400 hover:text-blue-600 transition"
                                       title="{{ __('common.edit') }}">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('ai-templates.destroy', $template) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('{{ __('ai-templates.confirm_delete') }}')"
                                                class="text-gray-400 hover:text-red-600 transition"
                                                title="{{ __('common.delete') }}">
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
    @endif
</div>
@endsection
