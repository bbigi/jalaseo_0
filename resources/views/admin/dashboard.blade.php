@extends('admin.layout')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-xl p-6 border border-blue-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-sm font-medium">Total Users</p>
                    <p class="text-4xl font-bold mt-2">{{ $totalUsers }}</p>
                    <p class="text-blue-300 text-xs mt-2">Active SaaS users</p>
                </div>
                <div class="text-5xl opacity-20">👥</div>
            </div>
        </div>

        <!-- Active Websites Card -->
        <div class="bg-gradient-to-br from-green-900 to-green-800 rounded-xl p-6 border border-green-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm font-medium">Active Websites</p>
                    <p class="text-4xl font-bold mt-2">{{ $activeWebsites }}</p>
                    <p class="text-green-300 text-xs mt-2">Connected & active</p>
                </div>
                <div class="text-5xl opacity-20">🌐</div>
            </div>
        </div>

        <!-- Total Posts Card -->
        <div class="bg-gradient-to-br from-purple-900 to-purple-800 rounded-xl p-6 border border-purple-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm font-medium">Total Posts</p>
                    <p class="text-4xl font-bold mt-2">{{ $totalPosts }}</p>
                    <p class="text-purple-300 text-xs mt-2">Generated articles</p>
                </div>
                <div class="text-5xl opacity-20">📝</div>
            </div>
        </div>

        <!-- API Credentials Card -->
        <div class="bg-gradient-to-br from-orange-900 to-orange-800 rounded-xl p-6 border border-orange-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-200 text-sm font-medium">API Keys Configured</p>
                    <p class="text-4xl font-bold mt-2">{{ $configuredProviders }}</p>
                    <p class="text-orange-300 text-xs mt-2">
                        <a href="{{ route('admin.api-keys.index') }}" class="underline hover:text-orange-200">
                            Manage →
                        </a>
                    </p>
                </div>
                <div class="text-5xl opacity-20">🔑</div>
            </div>
        </div>
    </div>

    <!-- API Credentials Status -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">API Credentials Overview</h3>

        @if ($apiCredentials->isEmpty())
            <p class="text-gray-400">No API credentials configured yet.</p>
            <a href="{{ route('admin.api-keys.create') }}" class="mt-4 inline-block px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                + Add API Key
            </a>
        @else
            <div class="space-y-3">
                @foreach ($apiCredentials as $credential)
                    <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                        <div>
                            <p class="font-semibold">{{ $credential->display_name }}</p>
                            <p class="text-gray-400 text-sm">Provider: <code>{{ $credential->provider }}</code></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $credential->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-600 text-gray-300' }}">
                                {{ $credential->is_active ? '✓ Active' : '○ Inactive' }}
                            </span>
                            <a href="{{ route('admin.api-keys.edit', $credential) }}" class="px-3 py-1 text-sm text-blue-400 hover:text-blue-300 transition">
                                Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
