@extends('layouts.dashboard')

@section('title', 'Websites - AneWP')

@section('content')
<!-- Top Bar -->
<div class="px-8 py-4 pl-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('websites.title') }}</h2>
            <p class="text-gray-600 text-sm mt-1">{{ __('websites.subtitle') }}</p>
        </div>
        <a href="{{ route('websites.create') }}"
           class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
            + {{ __('websites.add') }}
        </a>
    </div>
</div>

<!-- Content -->
<div class="p-8">
    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($websites->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-gray-200 text-center shadow-sm">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-700 text-lg mb-4">{{ __('websites.empty_title') }}</p>
            <p class="text-gray-500 mb-6">{{ __('websites.empty_subtitle') }}</p>
            <a href="{{ route('websites.create') }}"
               class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
                + {{ __('websites.add_first') }}
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.name') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.url') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.username') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.category') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.status') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.created') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">{{ __('websites.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($websites as $website)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-900">{{ $website->name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ $website->url }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-700 transition">
                                        {{ \Illuminate\Support\Str::limit($website->url, 40) }}
                                        <span class="ml-1">↗</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $website->username }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600 text-sm">
                                        {{ __('websites.categories.' . ($website->category ?? 'other')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $website->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($website->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-sm">
                                    {{ $website->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('websites.show', $website) }}"
                                           class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition"
                                           title="{{ __('websites.table.view') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('websites.edit', $website) }}"
                                           class="p-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded transition"
                                           title="{{ __('websites.table.edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('websites.destroy', $website) }}"
                                              class="inline"
                                              onsubmit="return confirm('{{ __('websites.table.delete_confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 bg-red-100 hover:bg-red-200 text-red-700 rounded transition"
                                                    title="{{ __('websites.table.delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
