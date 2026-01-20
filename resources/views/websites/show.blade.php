@extends('layouts.dashboard')

@section('title', $website->name . ' - AneWP')

@section('content')
<!-- Top Bar -->
<div class="px-8 py-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $website->name }}</h2>
            <p class="text-gray-600 text-sm mt-1">
                <a href="{{ $website->url }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                    {{ $website->url }} ↗
                </a>
                <span class="mx-2">•</span>
                <span class="px-2 py-1 rounded text-xs {{ $website->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $website->status === 'active' ? __('websites.show.status_active') : __('websites.show.status_inactive') }}
                </span>
            </p>
        </div>
        <a href="{{ route('websites.index') }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            ← {{ __('websites.show.all') }}
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Website Details -->
        <div class="bg-white rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('websites.show.health.title') }}</h3>
            <!-- Website Health Card -->
            <div class="bg-white mb-4"
                x-data="{
                    sessionId: null,
                    polling: false,
                    healthData: null,
                    async runHealthCheck() {
                        const response = await fetch('{{ route('websites.health.run', $website) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        this.sessionId = data.session_id;
                        this.polling = true;
                        this.pollStatus();
                    },
                    async pollStatus() {
                        if (!this.polling || !this.sessionId) return;

                        const response = await fetch(`/health-sessions/${this.sessionId}/status`);
                        const session = await response.json();

                        if (session.status === 'completed') {
                            this.polling = false;
                            await this.loadLatest();
                        } else if (session.status === 'failed') {
                            this.polling = false;
                            alert('Health check failed');
                        } else {
                            setTimeout(() => this.pollStatus(), 2500);
                        }
                    },
                    async loadLatest() {
                        const response = await fetch('{{ route('websites.health.latest', $website) }}');
                        const data = await response.json();
                        this.healthData = data.session;
                    },
                    init() {
                        this.loadLatest();
                    }
                }">

                <div x-show="healthData" class="space-y-5 mb-2">
                    <div class="grid grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">{{ __('websites.show.health.health_score') }}</p>
                            <p class="text-2xl font-semibold" :class="{
                                'text-green-600': healthData && healthData.health_score >= 90,
                                'text-blue-600': healthData && healthData.health_score >= 75 && healthData.health_score < 90,
                                'text-yellow-600': healthData && healthData.health_score >= 60 && healthData.health_score < 75,
                                'text-red-600': healthData && healthData.health_score < 60
                            }" x-text="healthData ? Math.round(healthData.health_score) : '--'"></p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">{{ __('websites.show.health.indexing') }}</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.indexing_score) : '--'"></p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">{{ __('websites.show.health.performance') }}</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.performance_score) : '--'"></p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">{{ __('websites.show.health.page_experience') }}</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.page_experience_score) : '--'"></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end mb-4">
                    <button @click="runHealthCheck()"
                            :disabled="polling"
                            class="cursor-pointer px-4 py-2 bg-green-50 hover:bg-green-700 disabled:bg-gray-400 text-gray-900 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span x-show="!polling">{{ __('websites.show.health.check') }}</span>
                        <span x-show="polling">{{ __('websites.show.health.checking') }}</span>
                    </button>
                </div>

                <div x-show="!healthData" class="text-center py-8 text-gray-500">
                    {{ __('websites.show.health.empty') }}
                </div>
            </div>
            <!-- end of website health card -->
             
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600">{{ __('websites.show.details.name') }}</span>
                    <span class="font-semibold text-gray-900">{{ $website->name }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600">{{ __('websites.show.details.url') }}</span>
                    <a href="{{ $website->url }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                        {{ $website->url }} ↗
                    </a>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600">{{ __('websites.show.details.username') }}</span>
                    <span class="font-mono text-gray-900">{{ $website->username }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600">{{ __('websites.show.details.category') }}</span>
                    <span class="text-gray-900">
                        {{ __('websites.categories.' . ($website->category ?? 'other')) }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600">{{ __('websites.show.details.status') }}</span>
                    <span class="px-2 py-1 rounded text-xs {{ $website->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $website->status === 'active' ? __('websites.show.status_active') : __('websites.show.status_inactive') }}
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">{{ __('websites.show.details.created') }}</span>
                    <span class="text-gray-900">{{ $website->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <!-- Web Analytics Button (NEW!) -->
                <a href="{{ route('website-analytics.index', $website) }}"
                   class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ __('websites.show.actions.web_analytics') }}
                </a>

                <div class="flex gap-3">
                    <a href="{{ route('websites.edit', $website) }}"
                       class="flex-1 px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('websites.show.actions.edit') }}
                    </a>
                    <form method="POST" action="{{ route('websites.destroy', $website) }}"
                          class="flex-1"
                          onsubmit="return confirm('{{ __('websites.show.actions.delete_confirm') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('websites.show.actions.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Website Intelligence -->
        <div class="bg-white rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('websites.show.intelligence.title') }}</h3>
            <p class="text-gray-600 text-sm mb-6">
                {{ __('websites.show.intelligence.subtitle') }}
            </p>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.gsc_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.gsc_desc') }}</p>
                    </div>
                    <a href="{{ route('website-analytics.index', $website) }}">
                    <button 
                            class="cursor-pointer px-3 ml-2 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm transition">
                        {{ __('websites.show.actions.open') }}
                    </button>
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.deep_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.deep_desc') }}</p>
                    </div>
                    <a href="{{ route('deep-research.index') }}">
                        <button
                                class="cursor-pointer px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm transition">
                            {{ __('websites.show.actions.open') }}
                        </button>
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.saved_keywords_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.saved_keywords_desc') }}</p>
                    </div>
                    <a href="{{ route('keywords.index', ['website_id' => $website->id]) }}">
                        <button
                                class="cursor-pointer ml-2 px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm transition">
                            {{ __('websites.show.actions.open') }}
                        </button>
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.competitor_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.competitor_desc') }}</p>
                    </div>
                    <a href="{{ route('competitors.index', ['website_id' => $website->id]) }}">
                        <button 
                                class="cursor-pointer ml-2 px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded text-sm transition">
                            {{ __('websites.show.actions.open') }}
                        </button>
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.gap_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.gap_desc') }}</p>
                    </div>
                    <a href="{{ url('/websites/'.$website->id.'/analytics-gaps') }}">
                        <button
                                class="cursor-pointer ml-2 px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded text-sm transition">
                            {{ __('websites.show.actions.open') }}
                        </button>
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.speed_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.speed_desc') }}</p>
                    </div>
                    <a href="{{ route('website-analytics.speed-insights', $website) }}"
                       class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded text-sm transition">
                        {{ __('websites.show.actions.open') }}
                    </a>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ __('websites.show.intelligence.health_title') }}</p>
                        <p class="text-xs text-gray-600">{{ __('websites.show.intelligence.health_desc') }}</p>
                    </div>
                    <a href="#"
                       class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded text-sm transition">
                        {{ __('websites.show.actions.open') }}
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- speed test section -->
    {{-- Speed Intelligence --}}
    <div class="bg-white rounded-xl p-6 mb-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ __('websites.show.speed.title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('websites.show.speed.subtitle', ['name' => $website->name]) }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('website-analytics.speed-insights', $website) }}"
                    class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-semibold transition">
                    {{ __('websites.show.actions.view_speed_insights') }}
                </a>
            </div>
        </div>

        @if($mobilePageSpeed || $desktopPageSpeed)
            <div class="overflow-hidden border border-gray-100 rounded-xl">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('websites.show.speed.metric') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('websites.show.speed.mobile') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('websites.show.speed.desktop') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ __('websites.show.speed.performance') }}</td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $mobilePageSpeed ? ($mobilePageSpeed->scores['performance'] ?? '–') : '–' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $desktopPageSpeed ? ($desktopPageSpeed->scores['performance'] ?? '–') : '–' }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ __('websites.show.speed.lcp_lab') }}</td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $mobilePageSpeed && isset($mobilePageSpeed->lab_metrics['lcp_ms']) ? number_format($mobilePageSpeed->lab_metrics['lcp_ms']/1000, 2) . 's' : '–' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $desktopPageSpeed && isset($desktopPageSpeed->lab_metrics['lcp_ms']) ? number_format($desktopPageSpeed->lab_metrics['lcp_ms']/1000, 2) . 's' : '–' }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ __('websites.show.speed.cls_lab') }}</td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $mobilePageSpeed && isset($mobilePageSpeed->lab_metrics['cls']) ? number_format($mobilePageSpeed->lab_metrics['cls'], 3) : '–' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $desktopPageSpeed && isset($desktopPageSpeed->lab_metrics['cls']) ? number_format($desktopPageSpeed->lab_metrics['cls'], 3) : '–' }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ __('websites.show.speed.lcp_field') }}</td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $mobilePageSpeed && isset($mobilePageSpeed->field_metrics['lcp_p75_ms']) ? number_format($mobilePageSpeed->field_metrics['lcp_p75_ms']/1000, 2) . 's' : '–' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-900">
                                {{ $desktopPageSpeed && isset($desktopPageSpeed->field_metrics['lcp_p75_ms']) ? number_format($desktopPageSpeed->field_metrics['lcp_p75_ms']/1000, 2) . 's' : '–' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">{{ __('websites.show.speed.no_data') }}</p>
        @endif
    </div>
    <!-- end of sepeed test section -->

    <!-- competitor analysis section -->
    @if(isset($competitorsGap) && $competitorsGap->count() > 0)
    <div class="bg-white rounded-xl p-6 mb-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ __('websites.show.competitors.title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('websites.show.competitors.subtitle', ['name' => $website->name]) }}</p>
            </div>
            <div>
                <a href="{{ route('competitors.index', ['website_id' => $website->id]) }}">
                <button 
                        class="cursor-pointer px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg text-sm transition">
                    {{ __('websites.show.actions.manage_competitors') }}
                </button>
                </a>
                <a href="{{ url('/websites/'.$website->id.'/analytics-gaps') }}"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-semibold transition">
                    {{ __('websites.show.actions.view_gap') }}
                </a>
            </div>
        </div>

        <div class="overflow-hidden border border-gray-100 rounded-xl">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.domain') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.website') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.primary') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.keywords') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.traffic') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.avg_rank') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('websites.show.competitors.table.last_analyzed') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($competitorsGap as $comp)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $comp->domain }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $comp->website->name ?? '–' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClasses = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'processing' => 'bg-amber-100 text-amber-700',
                                        'error' => 'bg-red-100 text-red-700',
                                        'not_analyzed' => 'bg-gray-100 text-gray-600',
                                    ];
                                    $labels = [
                                        'active' => __('websites.show.competitors.status.active'),
                                        'processing' => __('websites.show.competitors.status.processing'),
                                        'error' => __('websites.show.competitors.status.error'),
                                        'not_analyzed' => __('websites.show.competitors.status.not_analyzed'),
                                    ];
                                    $cls = $statusClasses[$comp->status] ?? 'bg-gray-100 text-gray-600';
                                    $label = $labels[$comp->status] ?? ucfirst($comp->status);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($comp->is_primary)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ __('websites.show.competitors.primary_badge') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $comp->total_keywords !== null ? number_format($comp->total_keywords) : '–' }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $comp->traffic_estimate !== null ? number_format($comp->traffic_estimate) : '–' }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $comp->avg_rank !== null ? number_format($comp->avg_rank, 1) : '–' }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                @if($comp->last_analyzed_at)
                                    {{ $comp->last_analyzed_at->diffForHumans() }}
                                @else
                                    –
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    <!-- end of competitor analysis section -->


</div>
<!-- end of content -->


@endsection
