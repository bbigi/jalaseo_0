@extends('admin.layout')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-semibold">OAuth Services</h3>
            <p class="text-gray-400 text-sm mt-1">Manage OAuth credentials for Google, Facebook, etc.</p>
        </div>
        <a href="{{ route('admin.oauth-services.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
            + Add OAuth Service
        </a>
    </div>

    @if ($services->isEmpty())
        <div class="bg-gray-800 rounded-xl p-12 border border-gray-700 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
            </svg>
            <p class="text-gray-400 text-lg mb-4">No OAuth services configured yet.</p>
            <p class="text-gray-500 text-sm mb-6">Add Google OAuth to enable GSC and GA4 integration for all users.</p>
            <a href="{{ route('admin.oauth-services.create') }}" class="inline-block px-6 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                Create First OAuth Service
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($services as $service)
                <div class="bg-gray-800 rounded-lg p-5 border border-gray-700 hover:border-gray-600 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="font-semibold text-lg">{{ $service->display_name }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $service->is_active ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500">Service Name:</span>
                                    <span class="text-gray-300 font-mono">{{ $service->service_name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Client ID:</span>
                                    <span class="text-gray-400 font-mono">{{ $service->getMaskedClientId() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Client Secret:</span>
                                    <span class="text-gray-400 font-mono">{{ $service->getMaskedClientSecret() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Redirect URI:</span>
                                    <span class="text-gray-400 font-mono text-xs">{{ $service->redirect_uri ?: 'Default' }}</span>
                                </div>
                            </div>

                            @if($service->notes)
                                <div class="mt-3 text-sm text-gray-400 bg-gray-900 rounded p-2">
                                    <span class="text-gray-500">Notes:</span> {{ $service->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <form action="{{ route('admin.oauth-services.toggle-active', $service) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1 text-sm rounded {{ $service->is_active ? 'bg-gray-700 hover:bg-gray-600 text-gray-300' : 'bg-green-700 hover:bg-green-600 text-white' }} transition">
                                    {{ $service->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.oauth-services.edit', $service) }}" class="px-3 py-1 bg-blue-700 hover:bg-blue-600 text-white text-sm rounded transition">
                                Edit
                            </a>

                            <form action="{{ route('admin.oauth-services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will break OAuth for all users!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-700 hover:bg-red-600 text-white text-sm rounded transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
