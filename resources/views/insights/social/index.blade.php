@extends('layouts.dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('insights.social.title') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('insights.social.subtitle') }}</p>
        </div>
        <a href="{{ route('insights.social.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            {{ __('insights.social.cta_search') }}
        </a>
    </div>

    <!-- Stats Cards (3 columns) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Searches -->
        <div class="bg-white rounded-lg p-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm text-gray-600">{{ __('insights.social.stats.total_searches') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_searches'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Insights Created -->
        <div class="bg-white rounded-lg p-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-green-600" />
                </div>
                <div>
                    <p class="text-sm text-gray-600">{{ __('insights.social.stats.insights_created') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['insights_created'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Top Platforms -->
        <div class="bg-white rounded-lg p-6">
            <div class="flex items-center gap-3 mb-3">
                <x-heroicon-o-trophy class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-gray-900">{{ __('insights.social.stats.top_platforms') }}</h3>
            </div>
            @if(!empty($stats['top_platforms']))
            <div class="space-y-2">
                @foreach(array_slice($stats['top_platforms'], 0, 3) as $platform)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 capitalize">{{ $platform['platform'] }}</span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $platform['count'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">{{ __('insights.social.stats.no_data') }}</p>
            @endif
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600" />
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('insights.social.info.title') }}</h3>
                <ul class="space-y-1 text-sm text-gray-700">
                    <li>• <strong>{{ __('insights.social.info.items.platform') }}</strong></li>
                    <li>• <strong>{{ __('insights.social.info.items.keyword') }}</strong></li>
                    <li>• <strong>{{ __('insights.social.info.items.country') }}</strong></li>
                    <li>• <strong>{{ __('insights.social.info.items.cache') }}</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Search History Table -->
    @if($searches->count() > 0)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('insights.social.history_title') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.platform') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.keyword') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.country') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.results') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.date') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.social.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($searches as $search)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if($search->platform === 'tiktok')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-pink-50 text-pink-700 text-xs font-medium rounded-full">
                                        <x-heroicon-o-musical-note class="w-3 h-3" />
                                        TikTok
                                    </span>
                                @elseif($search->platform === 'twitter')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                                        <x-heroicon-o-chat-bubble-left-right class="w-3 h-3" />
                                        Twitter
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">
                                        <x-heroicon-o-chart-bar class="w-3 h-3" />
                                        Google Trends
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-900">{{ $search->keyword ?: __('insights.social.table.keyword') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ strtoupper($search->country_code) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $search->total_results }}</span>
                                <span class="text-xs text-gray-500">{{ __('insights.social.table.results') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($search->status === 'completed')
                                <span class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">
                                    <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                    {{ __('insights.social.statuses.completed') }}
                                </span>
                            @elseif($search->status === 'failed')
                                <span class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-full">
                                    <x-heroicon-o-x-circle class="w-3 h-3 mr-1" />
                                    {{ __('insights.social.statuses.failed') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-full">
                                    <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                    {{ ucfirst(__('insights.social.statuses.' . ($search->status ?? 'processing'))) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $search->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('insights.social.results', $search->id) }}"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium transition"
                                   title="{{ __('insights.social.view_title') }}">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    {{ __('insights.social.view') }}
                                </a>
                                <form action="{{ route('insights.social.destroy', $search->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('insights.social.delete_confirm', ['count' => $search->total_results]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 font-medium transition"
                                            title="{{ __('insights.social.delete') }}">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                        {{ __('insights.social.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($searches->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $searches->links() }}
        </div>
        @endif
    </div>
    @else
    <!-- No data state -->
    <div class="bg-white rounded-lg p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <x-heroicon-o-fire class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('insights.social.no_data_title') }}</h3>
        <p class="text-gray-600 mb-4">{{ __('insights.social.no_data_subtitle') }}</p>
        <a href="{{ route('insights.social.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            {{ __('insights.social.cta_start') }}
        </a>
    </div>
    @endif
</div>
@endsection
