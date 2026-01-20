@extends('layouts.dashboard')

@section('title', __('insights.news.title') . ' - AneWP')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('insights.news.title') }}</h1>
            <p class="text-gray-600 mt-1">{{ __('insights.news.subtitle') }}</p>
        </div>
        <a href="{{ route('insights.news.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            {{ __('insights.news.cta_search') }}
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
                    <p class="text-sm text-gray-600">{{ __('insights.news.stats.total_searches') }}</p>
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
                    <p class="text-sm text-gray-600">{{ __('insights.news.stats.insights_created') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['insights_created'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Top Sources -->
        <div class="bg-white rounded-lg p-6">
            <div class="flex items-center gap-3 mb-3">
                <x-heroicon-o-trophy class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-gray-900">{{ __('insights.news.stats.top_sources') }}</h3>
            </div>
            @if(!empty($stats['top_sources']))
            <div class="space-y-2">
                @foreach(array_slice($stats['top_sources'], 0, 3) as $source)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 truncate">{{ $source['source'] }}</span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $source['count'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">{{ __('insights.news.stats.no_data') }}</p>
            @endif
        </div>
    </div>

    <!-- Table (Full Width) -->
    <div class="w-full">
            @if(count($history) > 0)
            <div class="bg-white rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.source') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.theme') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.language') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.country') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.time_range') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.results') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('insights.news.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($history as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-globe-alt class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                    <span class="font-medium text-gray-900">{{ $item['source'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item['theme'])
                                <span class="text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($item['theme'], 30) }}</span>
                                @else
                                <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700">{{ strtoupper($item['language'] ?? 'id') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700">{{ strtoupper($item['country'] ?? 'id') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    {{ ucfirst($item['time_range']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $item['results_count'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($item['created_at'])->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('insights.news.results', $item['id']) }}"
                                       class="text-blue-600 hover:text-blue-700"
                                       title="{{ __('insights.news.view_results') }}">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </a>
                                    <form method="POST" action="{{ route('insights.news.destroy', $item['id']) }}"
                                          onsubmit="return confirm('{{ __('insights.news.confirm_delete') }}')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-700"
                                                title="{{ __('insights.news.delete') }}">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="bg-white rounded-lg p-12 text-center">
                <x-heroicon-o-newspaper class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('insights.news.empty_title') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('insights.news.empty_subtitle') }}</p>
                <a href="{{ route('insights.news.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    {{ __('insights.news.cta_search') }}
                </a>
            </div>
            @endif
    </div>
</div>
@endsection
