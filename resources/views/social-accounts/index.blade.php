@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Connected Accounts</h1>
            <p class="text-gray-400 mt-1">Manage your social media accounts for auto-posting</p>
        </div>
    </div>

    <!-- Facebook Accounts -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-share class="w-8 h-8 text-blue-500" />
                <h2 class="text-xl font-semibold text-white">Facebook Pages</h2>
            </div>
            <a href="/oauth/meta/connect?channel=facebook"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <x-heroicon-o-plus class="w-5 h-5" />
                Connect New Page
            </a>
        </div>

        @if($facebook->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <x-heroicon-o-share class="w-16 h-16 mx-auto mb-3 opacity-20" />
                <p>No Facebook pages connected</p>
                <p class="text-sm mt-1">Click "Connect New Page" to get started</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($facebook as $account)
                    <div class="bg-gray-700 rounded-lg p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                <x-heroicon-s-user class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-white">
                                        {{ $account->page_name ?: $account->username }}
                                    </h3>
                                    @if($account->isPrimary())
                                        <span class="px-2 py-1 text-xs bg-green-600 text-white rounded">Primary</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-400">@{{ $account->username }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Connected {{ $account->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$account->isPrimary())
                                <button onclick="setPrimary({{ $account->id }})"
                                        class="px-3 py-1.5 text-sm bg-gray-600 hover:bg-gray-500 text-white rounded transition-colors">
                                    Set as Primary
                                </button>
                            @endif

                            <button onclick="refreshAccount({{ $account->id }})"
                                    class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors flex items-center gap-1">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                Refresh
                            </button>

                            <button onclick="disconnectAccount({{ $account->id }})"
                                    class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition-colors flex items-center gap-1">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Disconnect
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Instagram Accounts -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-camera class="w-8 h-8 text-pink-500" />
                <h2 class="text-xl font-semibold text-white">Instagram Business Accounts</h2>
            </div>
        </div>

        @if($instagram->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <x-heroicon-o-camera class="w-16 h-16 mx-auto mb-3 opacity-20" />
                <p>No Instagram accounts connected</p>
                <p class="text-sm mt-1 max-w-md mx-auto">
                    Instagram accounts are automatically synced when you connect a Facebook Page with Instagram Business Account.
                    Click "Refresh" on your Facebook page above to detect Instagram.
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($instagram as $account)
                    <div class="bg-gray-700 rounded-lg p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center">
                                <x-heroicon-s-camera class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-white">
                                        @{{ $account->username }}
                                    </h3>
                                    @if($account->isPrimary())
                                        <span class="px-2 py-1 text-xs bg-green-600 text-white rounded">Primary</span>
                                    @endif
                                    @if(($account->metadata['source'] ?? '') === 'auto_sync')
                                        <span class="px-2 py-1 text-xs bg-blue-600 text-white rounded">Auto-synced</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-400">IG Business Account</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Connected {{ $account->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$account->isPrimary())
                                <button onclick="setPrimary({{ $account->id }})"
                                        class="px-3 py-1.5 text-sm bg-gray-600 hover:bg-gray-500 text-white rounded transition-colors">
                                    Set as Primary
                                </button>
                            @endif

                            <button onclick="disconnectAccount({{ $account->id }})"
                                    class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition-colors flex items-center gap-1">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Disconnect
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Threads Accounts (Coming Soon) -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-500" />
                <h2 class="text-xl font-semibold text-white">Threads</h2>
            </div>
            <span class="px-3 py-1 text-sm bg-gray-700 text-gray-400 rounded">Coming Soon</span>
        </div>

        <div class="text-center py-8 text-gray-400">
            <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto mb-3 opacity-20" />
            <p>Threads auto-posting will be available soon</p>
        </div>
    </div>
</div>

<script>
function setPrimary(accountId) {
    if (!confirm('Set this account as primary? This will be the default account for publishing.')) {
        return;
    }

    fetch(`/social-accounts/${accountId}/set-primary`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to set primary account');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function refreshAccount(accountId) {
    const button = event.target.closest('button');
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Refreshing...';

    fetch(`/social-accounts/${accountId}/refresh`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Account refreshed successfully! Reloading...');
            location.reload();
        } else {
            alert('Failed to refresh: ' + data.message);
            button.disabled = false;
            button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Refresh';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
        button.disabled = false;
    });
}

function disconnectAccount(accountId) {
    if (!confirm('Are you sure you want to disconnect this account? This action cannot be undone.')) {
        return;
    }

    fetch(`/social-accounts/${accountId}/disconnect`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to disconnect account');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>
@endsection
