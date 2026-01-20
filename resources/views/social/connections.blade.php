@extends('layouts.dashboard')

@section('title', __('social-autoshare.title'))

@section('content')
<div class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('social-autoshare.title') }}</h2>
        <p class="text-gray-600 text-sm mt-1">{{ __('social-autoshare.not_connected') }}? {{ __('social-autoshare.connect_meta') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('oauth.meta.connect', ['channel' => 'facebook']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-semibold">
            <x-heroicon-o-share class="w-5 h-5" />
            <span>Connect Facebook</span>
        </a>
        <a href="{{ route('oauth.meta.connect', ['channel' => 'instagram']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-pink-600 text-white hover:bg-pink-700 transition text-sm font-semibold">
            <x-heroicon-o-share class="w-5 h-5" />
            <span>Connect Instagram</span>
        </a>
        <a href="{{ route('oauth.meta.connect', ['channel' => 'threads']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition text-sm font-semibold">
            <x-heroicon-o-share class="w-5 h-5" />
            <span>Connect Threads</span>
        </a>
    </div>
</div>

<div class="p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach (['meta_facebook' => 'Facebook', 'meta_instagram' => 'Instagram', 'meta_threads' => 'Threads'] as $provider => $label)
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        @if(isset($accounts[$provider]) && $accounts[$provider]->count())
                            <p class="text-lg font-semibold text-gray-900">{{ __('social-autoshare.connected') }}</p>
                        @else
                            <p class="text-lg font-semibold text-gray-900">{{ __('social-autoshare.not_connected') }}</p>
                        @endif
                    </div>
                    <a href="{{ route('oauth.meta.connect', ['channel' => str_replace('meta_', '', $provider)]) }}"
                       class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                        {{ __('social-autoshare.connect_meta') }}
                    </a>
                </div>

                @if(isset($accounts[$provider]) && $accounts[$provider]->count())
                    <div class="mt-4 space-y-3">
                        @foreach($accounts[$provider] as $account)
                            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                <p class="text-sm font-semibold text-gray-900">{{ $account->username ?? $account->account_id }}</p>
                                <p class="text-xs text-gray-600">{{ $account->account_id }}</p>
                                <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                                    <span>Status: {{ $account->status }}</span>
                                    @if($account->expires_at)
                                        <span>Expires: {{ $account->expires_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Publish Status</h3>
            <span class="text-xs text-gray-500">Last 5</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentSchedules as $schedule)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ ucfirst($schedule->publish_status) }}
                            @if($schedule->asset)
                                <span class="text-xs text-gray-500 ml-2">#{{ $schedule->asset->id }} ({{ $schedule->asset->channel }})</span>
                            @endif
                        </p>
                        @if($schedule->last_error)
                            <p class="text-xs text-red-600 mt-1">{{ $schedule->last_error }}</p>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        @if($schedule->published_at)
                            {{ $schedule->published_at->format('Y-m-d H:i') }}
                        @else
                            {{ $schedule->scheduled_at?->format('Y-m-d H:i') ?? '-' }}
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No schedules yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
