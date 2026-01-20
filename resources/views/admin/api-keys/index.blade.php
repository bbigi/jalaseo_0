@extends('admin.layout')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-xl font-semibold">API Keys Management</h3>
        <a href="{{ route('admin.api-keys.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
            + Add New API Key
        </a>
    </div>

    @php
        $byCategory = $credentials->groupBy('category');
    @endphp

    @if ($credentials->isEmpty())
        <div class="bg-gray-800 rounded-xl p-12 border border-gray-700 text-center">
            <p class="text-gray-400 text-lg mb-4">No API credentials configured yet.</p>
            <a href="{{ route('admin.api-keys.create') }}" class="inline-block px-6 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                Create First API Key
            </a>
        </div>
    @else
        @foreach ($byCategory as $category => $categoryCredentials)
            <div class="mb-8">
                <h4 class="text-lg font-semibold mb-4 text-orange-400 capitalize">
                    {{ str_replace('_', ' ', $category) }}
                </h4>
                <div class="space-y-3">
                    @foreach ($categoryCredentials as $credential)
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-semibold">{{ $credential->display_name }}</p>
                                <p class="text-gray-400 text-sm">{{ $credential->provider }} • {{ $credential->getMaskedApiKey() }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold {{ $credential->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-600 text-gray-300' }} rounded">
                                    {{ $credential->is_active ? '✓' : '○' }}
                                </span>
                                <a href="{{ route('admin.api-keys.edit', $credential) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                <form action="{{ route('admin.api-keys.destroy', $credential) }}" method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
@endsection
