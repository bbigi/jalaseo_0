@extends('layouts.dashboard')

@section('title', __('website-analytics.title') . ' - ' . $website->name)

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('website-analytics.title') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ $website->name }}</p>
        </div>
        <a href="{{ url('/websites/'.$website->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm font-medium">
            ← {{ __('website-analytics.back') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Sync Progress Popup (Hidden by default) -->
    <div id="syncProgressPopup" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900" id="syncTitle">{{ __('website-analytics.sync.title') }}</h3>
                    <p class="text-sm text-gray-500" id="syncSubtitle">{{ __('website-analytics.sync.subtitle') }}</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600" id="syncStatusText">{{ __('website-analytics.sync.status') }}</span>
                    <span class="text-gray-500" id="syncPercentage">{{ __('website-analytics.sync.percentage') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                    <div id="syncProgressBar" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Status Details -->
                <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                    <div class="flex items-center justify-between" id="gscStatusRow">
                        <span class="flex items-center gap-2">
                            <span id="gscIcon" class="w-2 h-2 rounded-full bg-gray-300"></span>
                            <span class="text-gray-600">{{ __('website-analytics.sync.gsc') }}</span>
                        </span>
                        <span id="gscStatusBadge" class="text-gray-500">--</span>
                    </div>
                    <div class="flex items-center justify-between" id="ga4StatusRow">
                        <span class="flex items-center gap-2">
                            <span id="ga4Icon" class="w-2 h-2 rounded-full bg-gray-300"></span>
                            <span class="text-gray-600">{{ __('website-analytics.sync.ga4') }}</span>
                        </span>
                        <span id="ga4StatusBadge" class="text-gray-500">--</span>
                    </div>
                </div>
            
            <!-- Keywords synced count -->
            <div id="syncStats" class="hidden mt-3 text-center text-sm text-gray-500">
                <span id="keywordsSynced">0</span> {{ __('website-analytics.sync.keywords_synced', ['count' => '']) }}
            </div>
        </div>
    </div>

    <!-- Connection Cards (Setup Priority) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- GSC Card -->
            <div class="bg-white rounded-xl p-6 ">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0L0 12h7v12h10V12h7L12 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ __('website-analytics.gsc.title') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('website-analytics.gsc.description') }}</p>
                        </div>
                    </div>
                    @if($gscConnection)
                        <span class="px-3 py-1 bg-green-50 text-green-600 text-xs font-semibold rounded-full">{{ __('website-analytics.connection.connected') }}</span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">{{ __('website-analytics.connection.not_connected') }}</span>
                    @endif
                </div>

                @if($gscConnection)
                    <div class="flex items-center justify-between text-sm mb-3">
                        <span class="text-gray-500">{{ __('website-analytics.connection.last_sync') }}</span>
                        <span class="text-gray-900">{{ $gscConnection->last_sync_at ? $gscConnection->last_sync_at->diffForHumans() : __('website-analytics.connection.never') }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="startGSCSync()" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            {{ __('website-analytics.connection.sync_now') }}
                        </button>
                        <form action="{{ route('website-analytics.gsc.disconnect', $website) }}" method="POST" onsubmit="return confirm('{{ __('website-analytics.gsc.disconnect_confirm') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2  text-gray-600 hover:bg-gray-50 text-sm font-medium rounded-lg transition">
                                {{ __('website-analytics.connection.disconnect') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('website-analytics.gsc.connect', $website) }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium rounded-lg transition">
                        {{ __('website-analytics.gsc.connect') }}
                    </a>
                @endif
            </div>

            <!-- GA4 Card -->
            <div class="bg-white rounded-xl p-6 ">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10zm-2 0h-4c0-2.21-1.79-4-4-4V4c4.41 0 8 3.59 8 8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ __('website-analytics.ga4.title') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('website-analytics.ga4.description') }}</p>
                        </div>
                    </div>
                    @if($ga4Connection)
                        <span class="px-3 py-1 bg-green-50 text-green-600 text-xs font-semibold rounded-full">{{ __('website-analytics.connection.connected') }}</span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">{{ __('website-analytics.connection.not_connected') }}</span>
                    @endif
                </div>

                @if($ga4Connection)
                    @if(!$ga4Connection->property_id)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                            <p class="text-sm text-yellow-800 mb-2">{{ __('website-analytics.ga4.property_warning') }}</p>
                            <button onclick="openGA4PropertyModal()" class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition">
                                {{ __('website-analytics.ga4.setup_property') }}
                            </button>
                        </div>
                    @else
                        <div class="flex items-center justify-between text-sm mb-3">
                            <span class="text-gray-500">{{ __('website-analytics.connection.last_sync') }}</span>
                            <span class="text-gray-900">{{ $ga4Connection->last_sync_at ? $ga4Connection->last_sync_at->diffForHumans() : __('website-analytics.connection.never') }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="startGA4Sync()" class="flex-1 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition">
                                {{ __('website-analytics.connection.sync_now') }}
                            </button>
                            <form action="{{ route('website-analytics.ga4.disconnect', $website) }}" method="POST" onsubmit="return confirm('{{ __('website-analytics.ga4.disconnect_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2  text-gray-600 hover:bg-gray-50 text-sm font-medium rounded-lg transition">
                                    {{ __('website-analytics.connection.disconnect') }}
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <a href="{{ route('website-analytics.ga4.connect', $website) }}" class="block w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-center font-medium rounded-lg transition">
                        {{ __('website-analytics.ga4.connect') }}
                    </a>
                @endif
            </div>
        </div>

    <!-- Website Health Card -->
    <!-- <div class="bg-white rounded-xl p-6 mb-6"
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
                    alert('{{ __('website-analytics.health.failed') }}');
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
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('website-analytics.health.title') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('website-analytics.health.subtitle') }}</p>
                </div>
            </div>
            <button @click="runHealthCheck()"
                    :disabled="polling"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg transition">
                <span x-show="!polling">{{ __('website-analytics.health.run_check') }}</span>
                <span x-show="polling">{{ __('website-analytics.health.checking') }}</span>
            </button>
        </div>

        <div x-show="healthData" class="space-y-4">
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.health.score') }}</p>
                    <p class="text-2xl font-semibold" :class="{
                        'text-green-600': healthData && healthData.health_score >= 90,
                        'text-blue-600': healthData && healthData.health_score >= 75 && healthData.health_score < 90,
                        'text-yellow-600': healthData && healthData.health_score >= 60 && healthData.health_score < 75,
                        'text-red-600': healthData && healthData.health_score < 60
                    }" x-text="healthData ? Math.round(healthData.health_score) : '--'"></p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.health.indexing') }}</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.indexing_score) : '--'"></p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.health.performance') }}</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.performance_score) : '--'"></p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.health.page_experience') }}</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="healthData ? Math.round(healthData.page_experience_score) : '--'"></p>
                </div>
            </div>
        </div>

        <div x-show="!healthData" class="text-center py-8 text-gray-500">
            {{ __('website-analytics.health.empty') }}
        </div>
    </div> -->

    @if(($gscConnection || $ga4Connection) && ($ga4Traffic && $ga4Traffic->count() > 0))
        <!-- Summary Stats Cards -->
            @php
                $totalUsers = $ga4Traffic ? $ga4Traffic->sum('users') : 0;
                $totalSessions = $ga4Traffic ? $ga4Traffic->sum('sessions') : 0;
                $avgBounceRate = $ga4Overview->avg_bounce_rate ?? 0;
                $avgSessionDuration = $ga4Overview->avg_session_duration ?? 0;
                $durationMinutes = floor($avgSessionDuration / 60);
                $durationSeconds = $avgSessionDuration % 60;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Users -->
                <div class="bg-white rounded-xl p-6 ">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.ga4_cards.total_users') }}</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalUsers) }}</p>
                            @if($ga4Connection)
                                <p class="text-xs text-gray-400 mt-1">{{ __('website-analytics.ga4_cards.timespan') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Sessions -->
                <div class="bg-white rounded-xl p-6 ">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.ga4_cards.total_sessions') }}</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalSessions) }}</p>
                            @if($ga4Connection)
                                <p class="text-xs text-gray-400 mt-1">{{ __('website-analytics.ga4_cards.timespan') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Bounce Rate -->
                <div class="bg-white rounded-xl p-6 ">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.ga4_cards.bounce_rate') }}</p>
                            <p class="text-2xl font-semibold {{ $avgBounceRate > 70 ? 'text-red-600' : ($avgBounceRate > 50 ? 'text-yellow-600' : 'text-green-600') }}">{{ number_format($avgBounceRate, 1) }}%</p>
                            @if($ga4Connection)
                                <p class="text-xs text-gray-400 mt-1">{{ __('website-analytics.ga4_cards.timespan') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 {{ $avgBounceRate > 70 ? 'bg-red-50' : ($avgBounceRate > 50 ? 'bg-yellow-50' : 'bg-green-50') }} rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 {{ $avgBounceRate > 70 ? 'text-red-600' : ($avgBounceRate > 50 ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Avg Session Duration -->
                <div class="bg-white rounded-xl p-6 ">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ __('website-analytics.ga4_cards.avg_duration') }}</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $durationMinutes }}:{{ str_pad($durationSeconds, 2, '0', STR_PAD_LEFT) }}</p>
                            @if($ga4Connection)
                                <p class="text-xs text-gray-400 mt-1">{{ __('website-analytics.ga4_cards.timespan') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

        <!-- GSC Performance Chart -->
        @if($gscConnection && $gscChartData && $gscChartData->count() > 0)
            @php
                $totalClicks = $keywordStats ? $keywordStats->total_clicks : 0;
                $totalImpressions = $keywordStats ? $keywordStats->total_impressions : 0;
            @endphp
            <div class="bg-white rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('website-analytics.gsc.search_performance') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('website-analytics.gsc.subtitle') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Metric Toggles -->
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="toggleClicks" checked class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-blue-600">{{ __('website-analytics.gsc.toggle.clicks') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="toggleImpressions" checked class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                            <span class="text-sm font-medium text-purple-600">{{ __('website-analytics.gsc.toggle.impressions') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="toggleCTR" class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                            <span class="text-sm font-medium text-green-600">{{ __('website-analytics.gsc.toggle.ctr') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="togglePosition" class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
                            <span class="text-sm font-medium text-orange-600">{{ __('website-analytics.gsc.toggle.position') }}</span>
                        </label>
                    </div>
                </div>
                
                <!-- Summary Stats in Chart Header -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalClicks) }}</p>
                        <p class="text-xs text-gray-500">{{ __('website-analytics.gsc.summary.clicks') }}</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($totalImpressions) }}</p>
                        <p class="text-xs text-gray-500">{{ __('website-analytics.gsc.summary.impressions') }}</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        @php
                            $avgCTR = $gscChartData->avg('avg_ctr') * 100;
                        @endphp
                        <p class="text-2xl font-bold text-green-600">{{ number_format($avgCTR, 1) }}%</p>
                        <p class="text-xs text-gray-500">{{ __('website-analytics.gsc.summary.ctr') }}</p>
                    </div>
                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                        @php
                            $avgPosition = $gscChartData->avg('avg_position');
                        @endphp
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($avgPosition, 1) }}</p>
                        <p class="text-xs text-gray-500">{{ __('website-analytics.gsc.summary.position') }}</p>
                    </div>
                </div>

                <!-- Chart Canvas -->
                <div class="relative" style="height: 300px;">
                    <canvas id="gscPerformanceChart"></canvas>
                </div>
            </div>
        @endif



        <!-- Your Keywords Section -->
        @if($gscConnection && ($topPerformingKeywords->count() > 0 || $keywordOpportunities->count() > 0 || $allKeywords))
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('website-analytics.gsc.keywords_title') }}</h2>
            </div>

            <div class="bg-white rounded-xl overflow-hidden">
                <!-- Keyword Tabs -->
                <div class="border-b border-gray-200 flex">
                    <button id="kw-tab-top" onclick="switchKeywordTab('top')" class="px-6 py-3 text-sm font-medium text-gray-900 bg-gray-50 border-b-2 border-blue-600">
                        {{ __('website-analytics.gsc.tabs.top') }}
                    </button>
                    <button id="kw-tab-opportunities" onclick="switchKeywordTab('opportunities')" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                        {{ __('website-analytics.gsc.tabs.opportunities') }}
                    </button>
                    <button id="kw-tab-all" onclick="switchKeywordTab('all')" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                        {{ __('website-analytics.gsc.tabs.all') }}
                    </button>
                    <button id="kw-tab-tracking" onclick="switchKeywordTab('tracking')" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                        {{ __('website-analytics.gsc.tabs.tracking') }}
                    </button>
                </div>

                <!-- Keyword Tab Contents -->
                <div class="p-6">
                    <!-- Top 15 Keywords -->
                    <div id="kw-content-top" class="">
                        @if($topPerformingKeywords && $topPerformingKeywords->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full" id="topKeywordsTable">
                                    <thead>
                                        <tr class="text-left border-b border-gray-200">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.#') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.keyword') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.clicks') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.impressions') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.ctr') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.position') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topPerformingKeywords as $index => $keyword)
                                        <tr class="hover:bg-gray-50/50 transition keyword-row" data-keyword="{{ strtolower($keyword->keyword) }}">
                                            <td class="px-4 py-3 text-sm text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium keyword-text">{{ Str::limit($keyword->keyword, 50) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-blue-600 text-right">{{ number_format($keyword->total_clicks) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($keyword->total_impressions) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($keyword->avg_ctr * 100, 2) }}%</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-block px-2 py-1 {{ $keyword->avg_position <= 10 ? 'bg-green-50 text-green-600' : ($keyword->avg_position <= 20 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }} text-xs font-semibold rounded">
                                                    #{{ number_format($keyword->avg_position, 1) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="inline-flex items-center gap-2">
                                                    <button onclick="saveKeyword('{{ addslashes($keyword->keyword) }}', {{ $keyword->avg_position }})" class="cursor-pointer text-blue-600 hover:text-blue-700" title="{{ __('website-analytics.gsc.save_track') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('generate.index', ['source' => 'gsc_keyword', 'keyword' => $keyword->keyword, 'title' => $keyword->keyword]) }}" class="cursor-pointer text-purple-600 hover:text-purple-700" title="{{ __('website-analytics.gsc.generate') }}" alt="{{ __('website-analytics.gsc.generate') }}">
                                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <p>{{ __('website-analytics.gsc.empty_keywords') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Keyword Opportunities -->
                    <div id="kw-content-opportunities" class="hidden">
                        @if($keywordOpportunities && $keywordOpportunities->count() > 0)
                            <div class="mb-4 p-4 bg-amber-50 rounded-lg">
                                <p class="text-sm text-amber-800">
                                    <strong>💡</strong> {{ __('website-analytics.gsc.tip_opportunities') }}
                                </p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full" id="opportunitiesTable">
                                    <thead>
                                        <tr class="text-left border-b border-gray-200">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.#') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.keyword') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.impressions') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.clicks') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">{{ __('website-analytics.gsc.table_headers.ctr') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.position') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($keywordOpportunities as $index => $keyword)
                                        <tr class="hover:bg-gray-50/50 transition keyword-row" data-keyword="{{ strtolower($keyword->keyword) }}">
                                            <td class="px-4 py-3 text-sm text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium keyword-text">{{ Str::limit($keyword->keyword, 50) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-purple-600 text-right">{{ number_format($keyword->total_impressions) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($keyword->total_clicks) }}</td>
                                            <td class="px-4 py-3 text-sm text-red-600 text-right">{{ number_format($keyword->avg_ctr * 100, 2) }}%</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-block px-2 py-1 bg-yellow-50 text-yellow-600 text-xs font-semibold rounded">
                                                    #{{ number_format($keyword->avg_position, 1) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="inline-flex items-center gap-2">
                                                    <button onclick="saveKeyword('{{ addslashes($keyword->keyword) }}', {{ $keyword->avg_position }})" class="cursor-pointer text-blue-600 hover:text-blue-700" title="{{ __('website-analytics.gsc.save_track') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('generate.index', ['source' => 'gsc_keyword', 'keyword' => $keyword->keyword, 'title' => $keyword->keyword]) }}" class="cursor-pointer text-purple-600 hover:text-purple-700" title="{{ __('website-analytics.gsc.generate') }}" alt="{{ __('website-analytics.gsc.generate') }}">
                                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <p>{{ __('website-analytics.gsc.empty_opportunities') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- All Queried Keywords -->
                    <div id="kw-content-all" class="hidden">
                        @if(isset($allKeywords) && $allKeywords->count() > 0)
                            <!-- Search Bar -->
                            <div class="mb-4 flex items-center justify-between">
                                <input type="text" 
                                       id="allKeywordsSearch"
                                       placeholder="{{ __('website-analytics.gsc.search_all') }}" 
                                       class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent w-80">
                                <p class="text-sm text-gray-500" id="allKeywordsCount">{{ __('website-analytics.gsc.all_count', ['count' => $allKeywords->count()]) }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full" id="allKeywordsTable">
                                    <thead>
                                       <tr class="text-left border-b border-gray-200">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.#') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 select-none" onclick="sortAllKeywords('keyword')">
                                                <span class="inline-flex items-center gap-1">
                                                    {{ __('website-analytics.gsc.table_headers.keyword') }}
                                                    <svg id="sort-icon-keyword" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortAllKeywords('clicks')">
                                                    <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.gsc.table_headers.clicks') }}
                                                    <svg id="sort-icon-clicks" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortAllKeywords('impressions')">
                                                    <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.gsc.table_headers.impressions') }}
                                                    <svg id="sort-icon-impressions" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortAllKeywords('ctr')">
                                                    <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.gsc.table_headers.ctr') }}
                                                    <svg id="sort-icon-ctr" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center cursor-pointer hover:text-gray-700 select-none" onclick="sortAllKeywords('position')">
                                                    <span class="inline-flex items-center gap-1 justify-center">
                                                    {{ __('website-analytics.gsc.table_headers.position') }}
                                                    <svg id="sort-icon-position" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allKeywordsBody">
                                        @foreach($allKeywords as $index => $keyword)
                                        <tr class="hover:bg-gray-50/50 transition keyword-row" 
                                            data-keyword="{{ strtolower($keyword->keyword) }}"
                                            data-clicks="{{ $keyword->total_clicks }}"
                                            data-impressions="{{ $keyword->total_impressions }}"
                                            data-ctr="{{ $keyword->avg_ctr }}"
                                            data-position="{{ $keyword->avg_position }}">
                                            <td class="px-4 py-3 text-sm text-gray-400 row-number">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium keyword-text">{{ Str::limit($keyword->keyword, 50) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-blue-600 text-right">{{ number_format($keyword->total_clicks) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($keyword->total_impressions) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($keyword->avg_ctr * 100, 2) }}%</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-block px-2 py-1 {{ $keyword->avg_position <= 10 ? 'bg-green-50 text-green-600' : ($keyword->avg_position <= 20 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }} text-xs font-semibold rounded">
                                                    #{{ number_format($keyword->avg_position, 1) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="inline-flex items-center gap-2">
                                                   <button onclick="saveKeyword('{{ addslashes($keyword->keyword) }}', {{ $keyword->avg_position }})" class="cursor-pointer text-blue-600 hover:text-blue-700" title="{{ __('website-analytics.gsc.save_track') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('generate.index', ['source' => 'gsc_keyword', 'keyword' => $keyword->keyword, 'title' => $keyword->keyword]) }}" class="cursor-pointer text-purple-600 hover:text-purple-700" title="{{ __('website-analytics.gsc.generate') }}" alt="{{ __('website-analytics.gsc.generate') }}">
                                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Client-side Pagination -->
                            <div class="mt-4 flex items-center justify-between" id="allKeywordsPagination">
                                <p class="text-sm text-gray-500" id="paginationInfo">
                                    {{ __('website-analytics.gsc.pagination.showing', ['from' => 1, 'to' => min(25, $allKeywords->count()), 'total' => $allKeywords->count()]) }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <select id="perPageSelect" onchange="changePerPage()" class="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500">
                                        <option value="25">{{ __('website-analytics.gsc.pagination.per_page', ['count' => 25]) }}</option>
                                        <option value="50">{{ __('website-analytics.gsc.pagination.per_page', ['count' => 50]) }}</option>
                                        <option value="100">{{ __('website-analytics.gsc.pagination.per_page', ['count' => 100]) }}</option>
                                        <option value="all">{{ __('website-analytics.gsc.pagination.show_all') }}</option>
                                    </select>
                                    <button onclick="prevPage()" id="prevPageBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                        {{ __('website-analytics.pages.pagination.prev') }}
                                    </button>
                                    <span class="text-sm text-gray-600" id="pageIndicator">{{ __('website-analytics.gsc.pagination.page', ['page' => 1, 'pages' => ceil($allKeywords->count() / 25)]) }}</span>
                                    <button onclick="nextPage()" id="nextPageBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ __('website-analytics.pages.pagination.next') }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <p>{{ __('website-analytics.gsc.empty_keywords') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tracking Keywords -->
                    <div id="kw-content-tracking" class="hidden">
                        @if(isset($savedKeywords) && $savedKeywords->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full" id="trackingTable">
                                    <thead>
                                       <tr class="text-left border-b border-gray-200">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.#') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.keyword') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.position') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.position') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.gsc.table_headers.action') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($savedKeywords as $index => $saved)
                                        <tr class="hover:bg-gray-50/50 transition keyword-row" data-keyword="{{ strtolower($saved->keyword) }}">
                                            <td class="px-4 py-3 text-sm text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium keyword-text">{{ $saved->keyword }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-block px-2 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded">
                                                    #{{ number_format($saved->current_position, 1) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="text-sm text-gray-500">#{{ number_format($saved->target_position, 1) }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @php
                                                    $change = ($saved->current_position) - ($saved->target_position);
                                                @endphp
                                                @if($change > 0)
                                                    <span class="text-red-600 text-sm font-medium">↓ {{ number_format($change, 1) }}</span>
                                                @elseif($change < 0)
                                                    <span class="text-green-600 text-sm font-medium">↑ {{ number_format(abs($change), 1) }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $saved->created_at->diffForHumans() }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <p class="text-gray-500 mb-2">{{ __('website-analytics.gsc.tracking_empty') }}</p>
                                <p class="text-sm text-gray-400">{{ __('website-analytics.gsc.tracking_hint') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Your Pages Section -->
        @if($gscConnection && $gscPages && $gscPages->count() > 0)
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('website-analytics.pages.title') }}</h2>
                <p class="text-sm text-gray-500">{{ __('website-analytics.pages.subtitle') }}</p>
            </div>

            <div class="bg-white rounded-xl overflow-hidden">
                <!-- Search Bar -->
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <input type="text" 
                               id="pagesSearch"
                               placeholder="{{ __('website-analytics.gsc.search_all') }}" 
                               class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent w-80">
                        <p class="text-sm text-gray-500" id="pagesCount">{{ __('website-analytics.pages.pagination.count', ['total' => $gscPages->count()]) }}</p>
                    </div>
                </div>

                <!-- Pages Table -->
                <div class="overflow-x-auto">
                    <table class="w-full" id="pagesTable">
                                    <thead>
                                        <tr class="text-left border-b border-gray-200">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('website-analytics.gsc.table_headers.#') }}</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 select-none" onclick="sortPages('url')">
                                                <span class="inline-flex items-center gap-1">
                                                    {{ __('website-analytics.pages.table.url') }}
                                                    <svg id="sort-icon-url" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortPages('clicks')">
                                                <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.pages.table.clicks') }}
                                                    <svg id="sort-icon-page-clicks" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortPages('impressions')">
                                                <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.pages.table.impressions') }}
                                                    <svg id="sort-icon-page-impressions" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right cursor-pointer hover:text-gray-700 select-none" onclick="sortPages('ctr')">
                                                <span class="inline-flex items-center gap-1 justify-end">
                                                    {{ __('website-analytics.gsc.table_headers.ctr') }}
                                                    <svg id="sort-icon-page-ctr" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center cursor-pointer hover:text-gray-700 select-none" onclick="sortPages('position')">
                                                <span class="inline-flex items-center gap-1 justify-center">
                                                    {{ __('website-analytics.pages.table.position') }}
                                                    <svg id="sort-icon-page-position" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </span>
                                            </th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('website-analytics.pages.table.action') }}</th>
                                        </tr>
                                    </thead>
                        <tbody id="pagesBody">
                            @foreach($gscPages as $index => $page)
                            @php
                                // Extract path from URL for cleaner display
                                $parsedUrl = parse_url($page->page_url);
                                $urlPath = ($parsedUrl['path'] ?? '/');
                                // If path is just /, show full URL without protocol
                                if ($urlPath === '/' || $urlPath === '') {
                                    $displayUrl = ($parsedUrl['host'] ?? '') . '/';
                                } else {
                                    $displayUrl = rtrim($urlPath, '/');
                                }

                                // Extract keyword from URL path for generate params
                                $pathSegments = array_filter(explode('/', $urlPath));
                                $lastSegment = end($pathSegments);
                                $keyword = $lastSegment ? str_replace(['-', '_'], ' ', $lastSegment) : '';
                                $title = $keyword; // Use same as keyword, or empty if no keyword
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition page-row"
                                data-url="{{ strtolower($displayUrl) }}"
                                data-clicks="{{ $page->total_clicks }}"
                                data-impressions="{{ $page->total_impressions }}"
                                data-ctr="{{ $page->avg_ctr }}"
                                data-position="{{ $page->avg_position }}">
                                <td class="px-4 py-3 text-sm text-gray-400 page-row-number">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm max-w-xs">
                                    <a href="{{ $page->page_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline flex items-start gap-1">
                                        <span class="break-all" title="{{ $page->page_url }}">{{ $displayUrl }}</span>
                                        <svg class="w-3 h-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-blue-600 text-right">{{ number_format($page->total_clicks) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($page->total_impressions) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($page->avg_ctr * 100, 2) }}%</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-1 {{ $page->avg_position <= 10 ? 'bg-green-50 text-green-600' : ($page->avg_position <= 20 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }} text-xs font-semibold rounded">
                                        #{{ number_format($page->avg_position, 1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('generate.index', ['source' => 'analytics_gap', 'generation_source' => 'reference', 'keyword' => $keyword, 'title' => $title, 'ref_topic' => $keyword, 'ref_url' => $page->page_url, 'website_id' => $website->id]) }}" class="cursor-pointer text-purple-600 hover:text-purple-700" title="{{ __('website-analytics.pages.duplicate_article') }}" alt="{{ __('website-analytics.pages.duplicate_article') }}">
                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-gray-100 flex items-center justify-between" id="pagesPagination">
                    <p class="text-sm text-gray-500" id="pagesPaginationInfo">
                        {{ __('website-analytics.pages.pagination.showing', ['from' => 1, 'to' => min(25, $gscPages->count()), 'total' => $gscPages->count()]) }}
                    </p>
                    <div class="flex items-center gap-2">
                        <select id="pagesPerPageSelect" onchange="changePagesPerPage()" class="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500">
                            <option value="10" selected>{{ __('website-analytics.pages.pagination.per_page', ['count' => 10]) }}</option>
                            <option value="25">{{ __('website-analytics.pages.pagination.per_page', ['count' => 25]) }}</option>
                            <option value="50">{{ __('website-analytics.pages.pagination.per_page', ['count' => 50]) }}</option>
                            <option value="100">{{ __('website-analytics.pages.pagination.per_page', ['count' => 100]) }}</option>
                            <option value="all">{{ __('website-analytics.pages.pagination.show_all') }}</option>
                        </select>
                        <button onclick="prevPagesPage()" id="prevPagesBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            {{ __('website-analytics.pages.pagination.prev') }}
                        </button>
                        <span class="text-sm text-gray-600" id="pagesPageIndicator">{{ __('website-analytics.pages.pagination.page', ['page' => 1, 'pages' => ceil($gscPages->count() / 25)]) }}</span>
                        <button onclick="nextPagesPage()" id="nextPagesBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('website-analytics.pages.pagination.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

                <!-- Your Traffic Section (Device & Country) -->
        @if($gscConnection && $trafficByDevice && $topCountries)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('website-analytics.traffic.section') }}</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Traffic by Device -->
                @if($trafficByDevice && $trafficByDevice->count() > 0)
                <div class="bg-white rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('website-analytics.traffic.device') }}</h3>
                    
                    @php
                        $totalDeviceClicks = $trafficByDevice->sum('total_clicks');
                        $deviceData = $trafficByDevice->keyBy('device');
                        $mobileData = $deviceData->get('MOBILE');
                        $desktopData = $deviceData->get('DESKTOP');
                        $tabletData = $deviceData->get('TABLET');
                    @endphp
                    
                    <!-- 3 Column Summary Cards (Horizontal) -->
                    <div class="flex gap-3 mb-3">
                        <!-- Mobile -->
                        <div class="flex-1 text-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ $mobileData ? number_format(($mobileData->total_clicks / max($totalDeviceClicks, 1)) * 100, 0) : 0 }}%</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.traffic.devices.mobile') }}</p>
                        </div>
                        <!-- Desktop -->
                        <div class="flex-1 text-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ $desktopData ? number_format(($desktopData->total_clicks / max($totalDeviceClicks, 1)) * 100, 0) : 0 }}%</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.traffic.devices.desktop') }}</p>
                        </div>
                        <!-- Tablet -->
                        <div class="flex-1 text-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ $tabletData ? number_format(($tabletData->total_clicks / max($totalDeviceClicks, 1)) * 100, 0) : 0 }}%</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.traffic.devices.tablet') }}</p>
                        </div>
                    </div>
                    
                    <!-- Device Detail List -->
                    <div class="space-y-4">
                        @foreach($trafficByDevice as $device)
                        @php
                            $percentage = $totalDeviceClicks > 0 ? ($device->total_clicks / $totalDeviceClicks) * 100 : 0;
                            $barColors = ['MOBILE' => 'bg-blue-500', 'DESKTOP' => 'bg-green-500', 'TABLET' => 'bg-purple-500'];
                            $barColor = $barColors[$device->device] ?? 'bg-gray-500';
                            $deviceLabels = [
                                'MOBILE' => __('website-analytics.traffic.devices.mobile'),
                                'DESKTOP' => __('website-analytics.traffic.devices.desktop'),
                                'TABLET' => __('website-analytics.traffic.devices.tablet'),
                            ];
                            $deviceLabel = $deviceLabels[$device->device] ?? ucfirst(strtolower($device->device));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $deviceLabel }}</span>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($device->total_clicks) }}</span>
                                    <span class="text-xs text-gray-500 ml-1">({{ number_format($percentage, 1) }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                <span>{{ __('website-analytics.traffic.device_impressions', ['count' => number_format($device->total_impressions)]) }}</span>
                                <span>{{ __('website-analytics.traffic.device_avg_position', ['position' => number_format($device->avg_position, 1)]) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Top Countries -->
                @if($topCountries && $topCountries->count() > 0)
                <div class="bg-white rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('website-analytics.traffic.countries') }}</h3>
                    
                    @php
                        $countryNames = [
                            'idn' => 'Indonesia', 'id' => 'Indonesia',
                            'usa' => 'United States', 'us' => 'United States',
                            'mys' => 'Malaysia', 'my' => 'Malaysia',
                            'sgp' => 'Singapore', 'sg' => 'Singapore',
                            'jpn' => 'Japan', 'jp' => 'Japan',
                            'gbr' => 'United Kingdom', 'gb' => 'United Kingdom',
                            'aus' => 'Australia', 'au' => 'Australia',
                            'ind' => 'India', 'in' => 'India',
                            'deu' => 'Germany', 'de' => 'Germany',
                            'fra' => 'France', 'fr' => 'France',
                            'nld' => 'Netherlands', 'nl' => 'Netherlands',
                            'bra' => 'Brazil', 'br' => 'Brazil',
                            'can' => 'Canada', 'ca' => 'Canada',
                            'kor' => 'South Korea', 'kr' => 'South Korea',
                            'phl' => 'Philippines', 'ph' => 'Philippines',
                            'tha' => 'Thailand', 'th' => 'Thailand',
                            'vnm' => 'Vietnam', 'vn' => 'Vietnam',
                            'sau' => 'Saudi Arabia', 'sa' => 'Saudi Arabia',
                            'are' => 'UAE', 'ae' => 'UAE',
                            'all' => __('website-analytics.traffic.all_countries'),
                        ];
                        $top3Countries = $topCountries->take(3);
                        $totalCountryClicks = $topCountries->sum('total_clicks');
                        $maxClicks = $topCountries->max('total_clicks');
                    @endphp
                    
                    <!-- 3 Column Summary Cards (Top 3) - Horizontal -->
                    <div class="flex gap-3 mb-3">
                        @foreach($top3Countries as $index => $country)
                        @php
                            $name = $countryNames[strtolower($country->country)] ?? strtoupper($country->country);
                            $bgColors = ['bg-blue-100', 'bg-emerald-100', 'bg-amber-100'];
                            $iconColors = ['text-blue-600', 'text-emerald-600', 'text-amber-600'];
                        @endphp
                        <div class="flex-1 text-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 {{ $bgColors[$index] ?? 'bg-gray-100' }} rounded-lg flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4 {{ $iconColors[$index] ?? 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($country->total_clicks) }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $name }}</p>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Country Detail List -->
                    <div class="space-y-3">
                        @foreach($topCountries as $index => $country)
                        @php
                            $name = $countryNames[strtolower($country->country)] ?? strtoupper($country->country);
                            $barWidth = $maxClicks > 0 ? ($country->total_clicks / $maxClicks) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 w-4">{{ $index + 1 }}</span>
                            <div class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 truncate">{{ $name }}</span>
                                    <span class="text-sm font-semibold text-blue-600 ml-2">{{ number_format($country->total_clicks) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Keyword Insights Section (Position Distribution & Low Hanging Fruits) -->
        @if($gscConnection && $positionDistribution && $lowHangingFruits && $lowHangingFruits->count() > 0)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('website-analytics.keyword_insights.title') }}</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Position Distribution -->
                @if(isset($positionDistribution))
                <div class="bg-white rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('website-analytics.keyword_insights.position_dist.title') }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('website-analytics.keyword_insights.position_dist.subtitle') }}</p>
                    
                    @php
                        $totalKeywords = $positionDistribution['top3'] + $positionDistribution['top10'] + $positionDistribution['top20'] + $positionDistribution['beyond20'];
                    @endphp
                    
                    <!-- Visual Bar Distribution -->
                    <div class="flex h-8 rounded-lg overflow-hidden mb-4">
                        @if($positionDistribution['top3'] > 0)
                        <div class="bg-green-500 flex items-center justify-center text-white text-xs font-medium" 
                             style="width: {{ $totalKeywords > 0 ? ($positionDistribution['top3'] / $totalKeywords) * 100 : 0 }}%"
                             title="{{ __('website-analytics.keyword_insights.position_dist.tooltip.top3', ['count' => $positionDistribution['top3']]) }}">
                            @if(($positionDistribution['top3'] / max($totalKeywords, 1)) * 100 > 10){{ $positionDistribution['top3'] }}@endif
                        </div>
                        @endif
                        @if($positionDistribution['top10'] > 0)
                        <div class="bg-blue-500 flex items-center justify-center text-white text-xs font-medium" 
                             style="width: {{ $totalKeywords > 0 ? ($positionDistribution['top10'] / $totalKeywords) * 100 : 0 }}%"
                             title="{{ __('website-analytics.keyword_insights.position_dist.tooltip.top10', ['count' => $positionDistribution['top10']]) }}">
                            @if(($positionDistribution['top10'] / max($totalKeywords, 1)) * 100 > 10){{ $positionDistribution['top10'] }}@endif
                        </div>
                        @endif
                        @if($positionDistribution['top20'] > 0)
                        <div class="bg-yellow-500 flex items-center justify-center text-white text-xs font-medium" 
                             style="width: {{ $totalKeywords > 0 ? ($positionDistribution['top20'] / $totalKeywords) * 100 : 0 }}%"
                             title="{{ __('website-analytics.keyword_insights.position_dist.tooltip.top20', ['count' => $positionDistribution['top20']]) }}">
                            @if(($positionDistribution['top20'] / max($totalKeywords, 1)) * 100 > 10){{ $positionDistribution['top20'] }}@endif
                        </div>
                        @endif
                        @if($positionDistribution['beyond20'] > 0)
                        <div class="bg-red-400 flex items-center justify-center text-white text-xs font-medium" 
                             style="width: {{ $totalKeywords > 0 ? ($positionDistribution['beyond20'] / $totalKeywords) * 100 : 0 }}%"
                             title="{{ __('website-analytics.keyword_insights.position_dist.tooltip.beyond20', ['count' => $positionDistribution['beyond20']]) }}">
                            @if(($positionDistribution['beyond20'] / max($totalKeywords, 1)) * 100 > 10){{ $positionDistribution['beyond20'] }}@endif
                        </div>
                        @endif
                    </div>
                    
                    <!-- Position Cards -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Top 3 -->
                        <div class="p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 bg-green-500 rounded"></div>
                                <span class="text-sm font-medium text-gray-700">{{ __('website-analytics.keyword_insights.position_dist.labels.top3') }}</span>
                            </div>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($positionDistribution['top3']) }}</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.keyword_insights.position_dist.percentage', ['percent' => $totalKeywords > 0 ? number_format(($positionDistribution['top3'] / $totalKeywords) * 100, 1) : 0]) }}</p>
                        </div>
                        
                        <!-- Position 4-10 -->
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 bg-blue-500 rounded"></div>
                                <span class="text-sm font-medium text-gray-700">{{ __('website-analytics.keyword_insights.position_dist.labels.top10') }}</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($positionDistribution['top10']) }}</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.keyword_insights.position_dist.percentage', ['percent' => $totalKeywords > 0 ? number_format(($positionDistribution['top10'] / $totalKeywords) * 100, 1) : 0]) }}</p>
                        </div>
                        
                        <!-- Position 11-20 -->
                        <div class="p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                                <span class="text-sm font-medium text-gray-700">{{ __('website-analytics.keyword_insights.position_dist.labels.top20') }}</span>
                            </div>
                            <p class="text-2xl font-bold text-yellow-600">{{ number_format($positionDistribution['top20']) }}</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.keyword_insights.position_dist.percentage', ['percent' => $totalKeywords > 0 ? number_format(($positionDistribution['top20'] / $totalKeywords) * 100, 1) : 0]) }}</p>
                        </div>
                        
                        <!-- Position 20+ -->
                        <div class="p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 bg-red-400 rounded"></div>
                                <span class="text-sm font-medium text-gray-700">{{ __('website-analytics.keyword_insights.position_dist.labels.beyond20') }}</span>
                            </div>
                            <p class="text-2xl font-bold text-red-500">{{ number_format($positionDistribution['beyond20']) }}</p>
                            <p class="text-xs text-gray-500">{{ __('website-analytics.keyword_insights.position_dist.percentage', ['percent' => $totalKeywords > 0 ? number_format(($positionDistribution['beyond20'] / $totalKeywords) * 100, 1) : 0]) }}</p>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-400 mt-4 text-center">{{ __('website-analytics.keyword_insights.position_dist.total', ['count' => number_format($totalKeywords)]) }}</p>
                </div>
                @endif
                
                <!-- Low Hanging Fruits -->
                @if(isset($lowHangingFruits) && $lowHangingFruits->count() > 0)
                <div class="bg-white rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('website-analytics.keyword_insights.low_fruit.title') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('website-analytics.keyword_insights.low_fruit.subtitle') }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-2 mb-4">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('website-analytics.keyword_insights.low_fruit.note') }}
                    </p>
                    
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($lowHangingFruits as $index => $kw)
                        @php
                            $spotsToPageOne = max(0, ceil($kw->avg_position) - 10);
                        @endphp
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <span class="text-xs text-gray-400 w-4">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $kw->keyword }}">{{ $kw->keyword }}</p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span>{{ __('website-analytics.keyword_insights.low_fruit.metrics.impressions', ['count' => number_format($kw->total_impressions)]) }}</span>
                                    <span>{{ __('website-analytics.keyword_insights.low_fruit.metrics.clicks', ['count' => number_format($kw->total_clicks)]) }}</span>
                                    <span>{{ __('website-analytics.keyword_insights.low_fruit.metrics.ctr', ['percent' => number_format($kw->avg_ctr * 100, 1)]) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded">
                                        #{{ number_format($kw->avg_position, 1) }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $spotsToPageOne > 0 ? __('website-analytics.keyword_insights.low_fruit.page_one_in', ['spots' => $spotsToPageOne]) : __('website-analytics.keyword_insights.low_fruit.almost_there') }}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button onclick="saveKeyword('{{ addslashes($kw->keyword) }}', {{ $kw->avg_position }})" class="cursor-pointer text-blue-600 hover:text-blue-700" title="{{ __('website-analytics.gsc.save_track') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    </button>
                                    <a href="{{ route('generate.index', ['source' => 'gsc_keyword', 'keyword' => $kw->keyword, 'title' => $kw->keyword]) }}" class="cursor-pointer text-purple-600 hover:text-purple-700" title="{{ __('website-analytics.gsc.generate') }}" alt="{{ __('website-analytics.gsc.generate') }}">
                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- GA4 Analytics Section -->
        @if($ga4Connection && (isset($ga4ChartData) || (isset($topReferrals) && $topReferrals->count() > 0)))
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('website-analytics.ga_overview.title') }}</h2>
            
            <!-- GA4 Traffic Trend Chart (Full Width) -->
            @if(isset($ga4ChartData) && $ga4ChartData->count() > 0)
            <div class="bg-white rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('website-analytics.ga_overview.traffic_trend') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('website-analytics.ga_overview.subtitle') }}</p>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="ga4TrafficChart"></canvas>
                </div>
            </div>
            @endif
            
            <!-- Top Referral Sources (Full Width) -->
            @if(isset($topReferrals) && $topReferrals->count() > 0)
            <div class="bg-white rounded-xl p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('website-analytics.traffic.referral.title') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('website-analytics.traffic.referral.subtitle') }}</p>
                    </div>
                </div>
                
                @php
                    $maxSessions = $topReferrals->max('total_sessions');
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topReferrals as $index => $ref)
                    @php
                        $barWidth = $maxSessions > 0 ? ($ref->total_sessions / $maxSessions) * 100 : 0;
                        $sourceColors = [
                            'google' => 'bg-blue-500',
                            'facebook' => 'bg-blue-600',
                            'twitter' => 'bg-sky-500',
                            'instagram' => 'bg-pink-500',
                            'youtube' => 'bg-red-500',
                            'linkedin' => 'bg-blue-700',
                            'tiktok' => 'bg-gray-900',
                            'direct' => 'bg-green-500',
                            '(direct)' => 'bg-green-500',
                            'organic' => 'bg-emerald-500',
                        ];
                        $barColor = $sourceColors[strtolower($ref->source)] ?? 'bg-indigo-500';
                    @endphp
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 w-4">{{ $index + 1 }}</span>
                                <span class="text-sm font-medium text-gray-700 truncate">{{ $ref->source }}</span>
                                @if($ref->medium && $ref->medium !== '(none)')
                                <span class="text-xs text-gray-400">/ {{ $ref->medium }}</span>
                                @endif
                            </div>
                            <span class="text-sm font-semibold text-indigo-600">{{ number_format($ref->total_sessions) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-2">
                            <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $barWidth }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ __('website-analytics.ga_overview.metrics.users', ['count' => number_format($ref->total_users)]) }}</span>
                            <span>{{ __('website-analytics.ga_overview.metrics.pageviews', ['count' => number_format($ref->total_page_views)]) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(isset($competitorsGap) && $competitorsGap->count() > 0)
            <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ __('website-analytics.competitors.title') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('website-analytics.competitors.subtitle') }}</p>
                    </div>
                    <a href="{{ url('/websites/'.$website->id.'/analytics-gaps') }}"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                        {{ __('website-analytics.competitors.view_gaps') }}
                    </a>
                </div>

                <div class="overflow-hidden border border-gray-100 rounded-xl">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.domain') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.website') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.status') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.primary') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.keywords') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.traffic') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.avg_rank') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('website-analytics.competitors.table.last_analyzed') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($competitorsGap as $comp)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $comp['domain'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $comp['website_name'] }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusClasses = [
                                                'active' => 'bg-green-100 text-green-700',
                                            'processing' => 'bg-amber-100 text-amber-700',
                                            'error' => 'bg-red-100 text-red-700',
                                            'not_analyzed' => 'bg-gray-100 text-gray-600',
                                        ];
                                            $cls = $statusClasses[$comp['status']] ?? 'bg-gray-100 text-gray-600';
                                            $labelKey = 'website-analytics.competitors.status.' . ($comp['status'] ?? '');
                                            $label = __($labelKey);
                                            if ($label === $labelKey) {
                                                $label = ucfirst($comp['status'] ?? '');
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($comp['is_primary'])
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ __('website-analytics.competitors.table.primary') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">{{ $comp['keywords'] !== null ? number_format($comp['keywords']) : '–' }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $comp['traffic'] !== null ? number_format($comp['traffic']) : '–' }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $comp['avg_rank'] !== null ? number_format($comp['avg_rank'], 1) : '–' }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if($comp['last_analyzed'])
                                            {{ $comp['last_analyzed']->diffForHumans() }}
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

        {{-- Speed Intelligence --}}
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">{{ __('website-analytics.speed.title') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('website-analytics.speed.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('website-analytics.speed-insights', $website) }}"
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition">
                        {{ __('website-analytics.speed.view_insights') }}
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('website-analytics.pagespeed.run', $website) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end mb-4">
                @csrf
                <div class="md:col-span-7">
                    <label class="text-xs text-gray-500 mb-1 block">{{ __('website-analytics.speed.url_label') }}</label>
                    <input type="url" name="url" required
                           value="{{ old('url', optional($latestPageSpeedRun)->url ?? $website->url) }}"
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500" placeholder="https://example.com/page">
                </div>
                <div class="md:col-span-3">
                    <label class="text-xs text-gray-500 mb-1 block">{{ __('website-analytics.speed.strategy') }}</label>
                    <select name="strategy" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        <option value="mobile" {{ old('strategy', optional($latestPageSpeedRun)->strategy ?? 'mobile') === 'mobile' ? 'selected' : '' }}>{{ __('website-analytics.speed.strategies.mobile') }}</option>
                        <option value="desktop" {{ old('strategy', optional($latestPageSpeedRun)->strategy) === 'desktop' ? 'selected' : '' }}>{{ __('website-analytics.speed.strategies.desktop') }}</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                        {{ __('website-analytics.speed.run_test') }}
                    </button>
                </div>
            </form>

            @if($latestPageSpeedRun)
                @php
                    $statusBadge = [
                        'queued' => [__('website-analytics.speed.status.queued'), 'bg-amber-100 text-amber-700'],
                        'running' => [__('website-analytics.speed.status.running'), 'bg-blue-100 text-blue-700'],
                        'success' => [__('website-analytics.speed.status.success'), 'bg-green-100 text-green-700'],
                        'error' => [__('website-analytics.speed.status.error'), 'bg-red-100 text-red-700'],
                    ][$latestPageSpeedRun->status] ?? [__('website-analytics.speed.status.unknown'), 'bg-gray-100 text-gray-600'];
                    $scores = $latestPageSpeedRun->scores ?? [];
                    $lab = $latestPageSpeedRun->lab_metrics ?? [];
                    $strategyLabel = __(
                        'website-analytics.speed.strategy_label',
                        ['strategy' => __('website-analytics.speed.strategies.' . ($latestPageSpeedRun->strategy ?? ''))]
                    );
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-gray-500">{{ __('website-analytics.speed.last_run') }}</p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                        </div>
                        <p class="text-lg font-semibold text-gray-900">{{ $latestPageSpeedRun->updated_at?->diffForHumans() ?? '–' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $strategyLabel }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">{{ __('website-analytics.speed.performance') }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $scores['performance'] ?? '–' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('website-analytics.speed.lighthouse') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">{{ __('website-analytics.speed.web_vitals') }}</p>
                        <p class="text-sm text-gray-900">LCP: {{ isset($lab['lcp_ms']) ? number_format($lab['lcp_ms']/1000, 2) . 's' : '–' }}</p>
                        <p class="text-sm text-gray-900">INP: {{ isset($lab['inp_ms']) ? number_format($lab['inp_ms']/1000, 2) . 's' : '–' }}</p>
                        <p class="text-sm text-gray-900">CLS: {{ $lab['cls'] ?? '–' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">{{ __('website-analytics.speed.field_data') }}</p>
                        <p class="text-sm text-gray-900">
                            {{ $latestPageSpeedRun->field_metrics ? __('website-analytics.speed.field_available') : __('website-analytics.speed.field_unavailable') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('website-analytics.speed.lab_only') }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">{{ __('website-analytics.speed.empty') }}</p>
            @endif
        </div>

    @else
        <!-- No Connections State -->
        <div class="bg-white rounded-xl p-16 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('website-analytics.no_connection.title') }}</h3>
            <p class="text-gray-600 mb-8">{{ __('website-analytics.no_connection.description') }}</p>
            <div class="flex items-center justify-center gap-4">
                <a href="{{ route('website-analytics.gsc.connect', $website) }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    {{ __('website-analytics.no_connection.connect_gsc') }}
                </a>
                <a href="{{ route('website-analytics.ga4.connect', $website) }}" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition">
                    {{ __('website-analytics.no_connection.connect_ga4') }}
                </a>
            </div>
        </div>
    @endif
</div>

<!-- GA4 Property ID Modal (keep modal dark for consistency) -->
<div id="ga4PropertyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">{{ __('website-analytics.ga4_modal.title') }}</h3>
                <button onclick="closeGA4PropertyModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('website-analytics.ga4.save-property', $website) }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Property ID Input -->
                <div>
                    <label for="property_id" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('website-analytics.ga4_modal.property_id_label') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="property_id"
                        id="property_id"
                        value="{{ old('property_id', $ga4Connection->property_id ?? '') }}"
                        placeholder="{{ __('website-analytics.ga4_modal.property_id_placeholder') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-lg"
                        required
                    >
                    @error('property_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instructions -->
                <div class="bg-gray-50 rounded-lg p-5 ">
                    <h4 class="text-gray-900 font-medium mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('website-analytics.ga4_modal.instructions.title') }}
                    </h4>
                    <ol class="list-decimal list-inside text-gray-700 text-sm space-y-2 ml-2">
                        <li>{!! __('website-analytics.ga4_modal.instructions.step1', ['link' => '<a href="https://analytics.google.com" target="_blank" class="text-orange-600 hover:text-orange-700 underline">Google Analytics</a>']) !!}</li>
                        <li>{!! __('website-analytics.ga4_modal.instructions.step2') !!}</li>
                        <li>{!! __('website-analytics.ga4_modal.instructions.step3') !!}</li>
                        <li>{!! __('website-analytics.ga4_modal.instructions.step4') !!}</li>
                    </ol>
                </div>

                <!-- Screenshot Hint -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                    <p class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span>{{ __('website-analytics.ga4_modal.tip') }}</span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeGA4PropertyModal()" class="px-4 py-2 text-gray-600 hover:text-gray-900 transition">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition shadow-lg">
                        {{ __('website-analytics.ga4_modal.actions.save_sync') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// ========================================
// GSC PERFORMANCE CHART
// ========================================
@if($gscConnection && isset($gscChartData) && $gscChartData && $gscChartData->count() > 0)
const gscData = @json($gscChartData);
let gscChart = null;
const gscLabels = {
    clicks: @json(__('website-analytics.gsc.toggle.clicks')),
    impressions: @json(__('website-analytics.gsc.toggle.impressions')),
    ctr: @json(__('website-analytics.gsc.toggle.ctr')),
    position: @json(__('website-analytics.gsc.toggle.position')),
};

function initGSCChart() {
    const ctx = document.getElementById('gscPerformanceChart');
    if (!ctx) return;
    
    const labels = gscData.map(d => {
        const date = new Date(d.date);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    
    const clicksData = gscData.map(d => Number(d.total_clicks) || 0);
    const impressionsData = gscData.map(d => Number(d.total_impressions) || 0);
    const ctrData = gscData.map(d => ((Number(d.avg_ctr) || 0) * 100).toFixed(2));
    const positionData = gscData.map(d => (Number(d.avg_position) || 0).toFixed(1));
    
    gscChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: gscLabels.clicks,
                    data: clicksData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y',
                    hidden: false
                },
                {
                    label: gscLabels.impressions,
                    data: impressionsData,
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1',
                    hidden: false
                },
                {
                    label: `${gscLabels.ctr} (%)`,
                    data: ctrData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y2',
                    hidden: true
                },
                {
                    label: gscLabels.position,
                    data: positionData,
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y3',
                    hidden: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label === 'CTR (%)') {
                                return label + ': ' + context.parsed.y + '%';
                            } else if (label === 'Position') {
                                return label + ': #' + context.parsed.y;
                            } else {
                                return label + ': ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: gscLabels.clicks, color: 'rgb(59, 130, 246)' },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { color: 'rgb(59, 130, 246)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: gscLabels.impressions, color: 'rgb(139, 92, 246)' },
                    grid: { drawOnChartArea: false },
                    ticks: { color: 'rgb(139, 92, 246)' }
                },
                y2: {
                    type: 'linear',
                    display: false,
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: { display: true, text: `${gscLabels.ctr} %`, color: 'rgb(34, 197, 94)' },
                    grid: { drawOnChartArea: false },
                    ticks: { color: 'rgb(34, 197, 94)' }
                },
                y3: {
                    type: 'linear',
                    display: false,
                    position: 'right',
                    reverse: true, // Lower position = better
                    title: { display: true, text: gscLabels.position, color: 'rgb(249, 115, 22)' },
                    grid: { drawOnChartArea: false },
                    ticks: { color: 'rgb(249, 115, 22)' }
                }
            }
        }
    });
    
    // Toggle handlers
    document.getElementById('toggleClicks')?.addEventListener('change', function() {
        gscChart.data.datasets[0].hidden = !this.checked;
        gscChart.options.scales.y.display = this.checked;
        gscChart.update();
    });
    
    document.getElementById('toggleImpressions')?.addEventListener('change', function() {
        gscChart.data.datasets[1].hidden = !this.checked;
        gscChart.options.scales.y1.display = this.checked;
        gscChart.update();
    });
    
    document.getElementById('toggleCTR')?.addEventListener('change', function() {
        gscChart.data.datasets[2].hidden = !this.checked;
        gscChart.options.scales.y2.display = this.checked;
        gscChart.update();
    });
    
    document.getElementById('togglePosition')?.addEventListener('change', function() {
        gscChart.data.datasets[3].hidden = !this.checked;
        gscChart.options.scales.y3.display = this.checked;
        gscChart.update();
    });
}
@endif

// ========================================
// GA4 TRAFFIC CHART
// ========================================
@if(isset($ga4ChartData) && $ga4ChartData->count() > 0)
let ga4Chart = null;

function initGA4Chart() {
    const ctx = document.getElementById('ga4TrafficChart');
    if (!ctx) return;
    
    const ga4Data = @json($ga4ChartData);
    const ga4Labels = {
        users: @json(__('website-analytics.ga_overview.labels.users')),
        sessions: @json(__('website-analytics.ga_overview.labels.sessions')),
        pageViews: @json(__('website-analytics.ga_overview.labels.page_views')),
        newUsers: @json(__('website-analytics.ga_overview.labels.new_users')),
    };
    
    if (!ga4Data || ga4Data.length === 0) return;
    
    const labels = ga4Data.map(d => {
        const date = new Date(d.date);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    
    const usersData = ga4Data.map(d => Number(d.users) || 0);
    const sessionsData = ga4Data.map(d => Number(d.sessions) || 0);
    const pageViewsData = ga4Data.map(d => Number(d.page_views) || 0);
    const newUsersData = ga4Data.map(d => Number(d.new_users) || 0);
    
    ga4Chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: ga4Labels.users,
                    data: usersData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: ga4Labels.sessions,
                    data: sessionsData,
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3
                },
                {
                    label: ga4Labels.pageViews,
                    data: pageViewsData,
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    hidden: true
                },
                {
                    label: ga4Labels.newUsers,
                    data: newUsersData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    hidden: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        padding: 15,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
}
@endif

// ========================================
// KEYWORD TAB SWITCHING
// ========================================
function switchKeywordTab(tabName) {
    // Hide all keyword tab contents
    document.querySelectorAll('[id^="kw-content-"]').forEach(el => el.classList.add('hidden'));
    
    // Reset all keyword tab buttons
    document.querySelectorAll('[id^="kw-tab-"]').forEach(el => {
        el.classList.remove('bg-gray-50', 'text-gray-900', 'border-b-2', 'border-blue-600');
        el.classList.add('text-gray-500');
    });

    // Show selected keyword tab content
    document.getElementById('kw-content-' + tabName).classList.remove('hidden');
    
    // Activate selected keyword tab button
    const activeTab = document.getElementById('kw-tab-' + tabName);
    activeTab.classList.add('bg-gray-50', 'text-gray-900', 'border-b-2', 'border-blue-600');
    activeTab.classList.remove('text-gray-500');
    
    // Re-apply search filter when switching tabs
    const searchInput = document.getElementById('allKeywordsSearch');
    if (searchInput && searchInput.value) {
        filterAllKeywords(searchInput.value);
    }
}

// ========================================
// ALL KEYWORDS TABLE SORTING
// ========================================
let currentSort = { column: null, direction: 'asc' };
let currentPage = 1;
let perPage = 25;
let allRows = [];
let filteredRows = [];
const keywordPaginationText = {!! json_encode([
    'showingAll' => __('website-analytics.gsc.pagination.showing_all', ['total' => ':total']),
    'showing' => __('website-analytics.gsc.pagination.showing', ['from' => ':from', 'to' => ':to', 'total' => ':total']),
    'count' => __('website-analytics.gsc.pagination.count', ['total' => ':total']),
    'page' => __('website-analytics.gsc.pagination.page', ['page' => ':page', 'pages' => ':pages']),
    'all' => __('website-analytics.gsc.pagination.all'),
]) !!};

function initAllKeywordsTable() {
    const tbody = document.getElementById('allKeywordsBody');
    if (!tbody) return;
    
    // Store all rows
    allRows = Array.from(tbody.querySelectorAll('tr'));
    filteredRows = [...allRows];
    
    // Initial render
    renderPage();
}

function sortAllKeywords(column) {
    // Toggle direction if same column
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'desc'; // Default to descending (highest first)
    }
    
    // Sort filtered rows
    filteredRows.sort((a, b) => {
        let aVal, bVal;
        
        if (column === 'keyword') {
            aVal = a.getAttribute('data-keyword') || '';
            bVal = b.getAttribute('data-keyword') || '';
            return currentSort.direction === 'asc' 
                ? aVal.localeCompare(bVal) 
                : bVal.localeCompare(aVal);
        } else {
            aVal = parseFloat(a.getAttribute(`data-${column}`)) || 0;
            bVal = parseFloat(b.getAttribute(`data-${column}`)) || 0;
            return currentSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
        }
    });
    
    // Reset to first page after sort
    currentPage = 1;
    renderPage();
    
    // Update sort icons
    updateSortIcons(column, currentSort.direction);
}

function updateSortIcons(activeColumn, direction) {
    const columns = ['keyword', 'clicks', 'impressions', 'ctr', 'position'];
    const defaultIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>';
    const ascIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>';
    const descIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>';
    
    columns.forEach(col => {
        const icon = document.getElementById(`sort-icon-${col}`);
        if (icon) {
            if (col === activeColumn) {
                icon.innerHTML = direction === 'asc' ? ascIcon : descIcon;
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-blue-600');
            } else {
                icon.innerHTML = defaultIcon;
                icon.classList.remove('text-blue-600');
                icon.classList.add('text-gray-400');
            }
        }
    });
}

// ========================================
// ALL KEYWORDS SEARCH FILTER
// ========================================
function filterAllKeywords(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    
    if (term === '') {
        filteredRows = [...allRows];
    } else {
        filteredRows = allRows.filter(row => {
            const keyword = row.getAttribute('data-keyword') || '';
            return keyword.includes(term);
        });
    }
    
    // Re-apply current sort if any
    if (currentSort.column) {
        const column = currentSort.column;
        filteredRows.sort((a, b) => {
            let aVal, bVal;
            if (column === 'keyword') {
                aVal = a.getAttribute('data-keyword') || '';
                bVal = b.getAttribute('data-keyword') || '';
                return currentSort.direction === 'asc' 
                    ? aVal.localeCompare(bVal) 
                    : bVal.localeCompare(aVal);
            } else {
                aVal = parseFloat(a.getAttribute(`data-${column}`)) || 0;
                bVal = parseFloat(b.getAttribute(`data-${column}`)) || 0;
                return currentSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });
    }
    
    // Reset to first page after filter
    currentPage = 1;
    renderPage();
}

// ========================================
// PAGINATION
// ========================================
function renderPage() {
    const tbody = document.getElementById('allKeywordsBody');
    if (!tbody) return;
    
    // Clear tbody
    tbody.innerHTML = '';
    
    // Calculate slice
    let rowsToShow;
    if (perPage === 'all') {
        rowsToShow = filteredRows;
    } else {
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        rowsToShow = filteredRows.slice(start, end);
    }
    
    // Append rows and update numbers
    rowsToShow.forEach((row, index) => {
        const numCell = row.querySelector('.row-number');
        const globalIndex = perPage === 'all' ? index + 1 : (currentPage - 1) * perPage + index + 1;
        if (numCell) numCell.textContent = globalIndex;
        tbody.appendChild(row);
    });
    
    // Update pagination UI
    updatePaginationUI();
}

function updatePaginationUI() {
    const total = filteredRows.length;
    const totalPages = perPage === 'all' ? 1 : Math.ceil(total / perPage);
    
    // Update info text
    const infoEl = document.getElementById('paginationInfo');
    const countEl = document.getElementById('allKeywordsCount');
    
    if (perPage === 'all') {
        if (infoEl) infoEl.textContent = keywordPaginationText.showingAll.replace(':total', total);
    } else {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        if (infoEl) {
            const fromVal = total > 0 ? start : 0;
            infoEl.textContent = keywordPaginationText.showing
                .replace(':from', fromVal)
                .replace(':to', end)
                .replace(':total', total);
        }
    }
    
    if (countEl) {
        countEl.textContent = keywordPaginationText.count.replace(':total', total);
    }
    
    // Update page indicator
    const pageIndicator = document.getElementById('pageIndicator');
    if (pageIndicator) {
        pageIndicator.textContent = perPage === 'all'
            ? keywordPaginationText.all
            : keywordPaginationText.page.replace(':page', currentPage).replace(':pages', totalPages);
    }
    
    // Update buttons
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1 || perPage === 'all';
    }
    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages || perPage === 'all';
    }
}

function prevPage() {
    if (perPage === 'all') return;
    if (currentPage > 1) {
        currentPage--;
        renderPage();
    }
}

function nextPage() {
    if (perPage === 'all') return;
    const totalPages = Math.ceil(filteredRows.length / perPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderPage();
    }
}

function changePerPage() {
    const select = document.getElementById('perPageSelect');
    const value = select.value;
    perPage = value === 'all' ? 'all' : parseInt(value);
    currentPage = 1;
    renderPage();
}

document.addEventListener('DOMContentLoaded', function() {
    try { initAllKeywordsTable(); } catch(e) { console.error('Keywords init failed:', e); }
    try { initPagesTable(); } catch(e) { console.error('Pages init failed:', e); }
    try { initGSCChart(); } catch(e) { console.error('GSC chart init failed:', e); }
    try { initGA4Chart(); } catch(e) { console.error('GA4 chart init failed:', e); }

    const searchInput = document.getElementById('allKeywordsSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterAllKeywords(this.value);
            }, 150);
        });
    }

    const pagesSearchInput = document.getElementById('pagesSearch');
    if (pagesSearchInput) {
        let debounceTimer;
        pagesSearchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterPages(this.value);
            }, 150);
        });
    }
});

// ========================================
// PAGES TABLE SORTING, SEARCH, PAGINATION
// ========================================
let pagesSort = { column: null, direction: 'asc' };
let pagesCurrentPage = 1;
let pagesPerPage = 10;
let allPageRows = [];
let filteredPageRows = [];
const pagesI18n = {
    showing: "{{ __('website-analytics.pages.pagination.showing', ['from' => ':from', 'to' => ':to', 'total' => ':total']) }}",
    page: "{{ __('website-analytics.pages.pagination.page', ['page' => ':page', 'pages' => ':pages']) }}",
    all: "{{ __('website-analytics.pages.pagination.show_all') }}",
    count: "{{ __('website-analytics.pages.pagination.count', ['total' => ':total']) }}",
};

function initPagesTable() {
    const tbody = document.getElementById('pagesBody');
    if (!tbody) return;

    allPageRows = Array.from(tbody.querySelectorAll('tr'));
    filteredPageRows = [...allPageRows];
    pagesCurrentPage = 1;

    // Set dropdown to match pagesPerPage variable
    const selectEl = document.getElementById('pagesPerPageSelect');
    if (selectEl) {
        selectEl.value = pagesPerPage;
    }

    renderPagesPage();
}

function sortPages(column) {
    if (pagesSort.column === column) {
        pagesSort.direction = pagesSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        pagesSort.column = column;
        pagesSort.direction = 'desc';
    }

    filteredPageRows.sort((a, b) => {
        let aVal, bVal;

        if (column === 'url') {
            aVal = a.getAttribute('data-url') || '';
            bVal = b.getAttribute('data-url') || '';
            return pagesSort.direction === 'asc'
                ? aVal.localeCompare(bVal)
                : bVal.localeCompare(aVal);
        } else {
            aVal = parseFloat(a.getAttribute(`data-${column}`)) || 0;
            bVal = parseFloat(b.getAttribute(`data-${column}`)) || 0;
            return pagesSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
        }
    });

    pagesCurrentPage = 1;
    renderPagesPage();
    updatePagesSortIcons(column, pagesSort.direction);
}

function updatePagesSortIcons(activeColumn, direction) {
    const columns = ['url', 'clicks', 'impressions', 'ctr', 'position'];
    const columnIds = ['url', 'page-clicks', 'page-impressions', 'page-ctr', 'page-position'];

    const defaultIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>';
    const ascIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>';
    const descIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>';

    columns.forEach((col, i) => {
        const icon = document.getElementById(`sort-icon-${columnIds[i]}`);
        if (!icon) return;

        if (col === activeColumn) {
            icon.innerHTML = direction === 'asc' ? ascIcon : descIcon;
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-blue-600');
        } else {
            icon.innerHTML = defaultIcon;
            icon.classList.remove('text-blue-600');
            icon.classList.add('text-gray-400');
        }
    });
}

function filterPages(searchTerm) {
    const term = (searchTerm || '').toLowerCase().trim();

    if (term === '') {
        filteredPageRows = [...allPageRows];
    } else {
        filteredPageRows = allPageRows.filter(row => {
            const url = (row.getAttribute('data-url') || '');
            return url.includes(term);
        });
    }

    // keep sort after filter
    if (pagesSort.column) {
        const col = pagesSort.column;
        filteredPageRows.sort((a, b) => {
            let aVal, bVal;
            if (col === 'url') {
                aVal = a.getAttribute('data-url') || '';
                bVal = b.getAttribute('data-url') || '';
                return pagesSort.direction === 'asc'
                    ? aVal.localeCompare(bVal)
                    : bVal.localeCompare(aVal);
            } else {
                aVal = parseFloat(a.getAttribute(`data-${col}`)) || 0;
                bVal = parseFloat(b.getAttribute(`data-${col}`)) || 0;
                return pagesSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });
    }

    pagesCurrentPage = 1;
    renderPagesPage();
}

function renderPagesPage() {
    const tbody = document.getElementById('pagesBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    let rowsToShow;
    const total = filteredPageRows.length;

    if (pagesPerPage === 'all') {
        rowsToShow = filteredPageRows;
    } else {
        const start = (pagesCurrentPage - 1) * pagesPerPage;
        const end = start + pagesPerPage;
        rowsToShow = filteredPageRows.slice(start, end);
    }

    rowsToShow.forEach((row, index) => {
        const numCell = row.querySelector('.page-row-number');
        const globalIndex = pagesPerPage === 'all'
            ? index + 1
            : (pagesCurrentPage - 1) * pagesPerPage + index + 1;

        if (numCell) numCell.textContent = globalIndex;
        tbody.appendChild(row);
    });

    updatePagesPaginationUI();
}

function updatePagesPaginationUI() {
    const total = filteredPageRows.length;
    const totalPages = pagesPerPage === 'all' ? 1 : Math.ceil(total / pagesPerPage);

    const infoEl = document.getElementById('pagesPaginationInfo');
    const countEl = document.getElementById('pagesCount');
    const indicator = document.getElementById('pagesPageIndicator');
    const prevBtn = document.getElementById('prevPagesBtn');
    const nextBtn = document.getElementById('nextPagesBtn');

    if (pagesPerPage === 'all') {
        if (infoEl) infoEl.textContent = pagesI18n.showing
            .replace(':from', 1)
            .replace(':to', total)
            .replace(':total', total);
        if (indicator) indicator.textContent = pagesI18n.page
            .replace(':page', 1)
            .replace(':pages', 1);
    } else {
        const start = (pagesCurrentPage - 1) * pagesPerPage + 1;
        const end = Math.min(pagesCurrentPage * pagesPerPage, total);
        if (infoEl) infoEl.textContent = pagesI18n.showing
            .replace(':from', total > 0 ? start : 0)
            .replace(':to', end)
            .replace(':total', total);
        if (indicator) indicator.textContent = pagesI18n.page
            .replace(':page', pagesCurrentPage)
            .replace(':pages', totalPages);
    }

    if (countEl) countEl.textContent = pagesI18n.count.replace(':total', total);

    if (prevBtn) prevBtn.disabled = pagesCurrentPage <= 1 || pagesPerPage === 'all';
    if (nextBtn) nextBtn.disabled = pagesCurrentPage >= totalPages || pagesPerPage === 'all';
}

function nextPagesPage() {
    if (pagesPerPage === 'all') return;
    const totalPages = Math.ceil(filteredPageRows.length / pagesPerPage);
    if (pagesCurrentPage < totalPages) {
        pagesCurrentPage++;
        renderPagesPage();
    }
}

function prevPagesPage() {
    if (pagesPerPage === 'all') return;
    if (pagesCurrentPage > 1) {
        pagesCurrentPage--;
        renderPagesPage();
    }
}

function changePagesPerPage() {
    const select = document.getElementById('pagesPerPageSelect');
    if (!select) return;

    const value = select.value;
    pagesPerPage = value === 'all' ? 'all' : parseInt(value, 10);
    pagesCurrentPage = 1;
    renderPagesPage();
}

function openGA4PropertyModal() {
    document.getElementById('ga4PropertyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeGA4PropertyModal() {
    document.getElementById('ga4PropertyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function saveKeyword(keyword, position) {
    openTrackKeywordModal(keyword, position);
}

const trackTranslations = {
    title: @json(__('website-analytics.gsc.track.title')),
    keyword: @json(__('website-analytics.gsc.track.keyword_label')),
    currentPosition: @json(__('website-analytics.gsc.track.current_position')),
    targetPosition: @json(__('website-analytics.gsc.track.target_position')),
    priority: @json(__('website-analytics.gsc.track.priority')),
    priorityOptions: {
        high: @json(__('website-analytics.gsc.track.priority_options.high')),
        medium: @json(__('website-analytics.gsc.track.priority_options.medium')),
        low: @json(__('website-analytics.gsc.track.priority_options.low')),
    },
    cancel: @json(__('common.cancel')),
    submit: @json(__('website-analytics.gsc.track.submit')),
};

function openTrackKeywordModal(keyword, currentPos) {
    const modal = document.createElement('div');
    modal.id = 'trackKeywordModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-80 shadow-xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">${trackTranslations.title}</h3>
                <button onclick="closeTrackModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${trackTranslations.keyword}</label>
                    <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs font-medium">
                        ${keyword}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${trackTranslations.currentPosition}</label>
                    <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                        #${Math.round(currentPos)}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${trackTranslations.targetPosition}</label>
                    <input type="number" id="targetPosition" min="1" max="100" value="${Math.max(1, Math.round(currentPos) - 5)}"
                           class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">${trackTranslations.priority}</label>
                    <select id="keywordPriority" class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                        <option value="high">${trackTranslations.priorityOptions.high}</option>
                        <option value="medium" selected>${trackTranslations.priorityOptions.medium}</option>
                        <option value="low">${trackTranslations.priorityOptions.low}</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-2">
                    <button onclick="closeTrackModal()" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs font-medium">
                        ${trackTranslations.cancel}
                    </button>
                    <button onclick="submitTrackKeyword('${keyword.replace(/'/g, "\\'")}', ${currentPos})"
                            class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-medium">
                        ${trackTranslations.submit}
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeTrackModal();
    });
}

function closeTrackModal() {
    const modal = document.getElementById('trackKeywordModal');
    if (modal) modal.remove();
}

function submitTrackKeyword(keyword, currentPos) {
    const targetPos = document.getElementById('targetPosition').value;
    const priority = document.getElementById('keywordPriority').value;

    fetch('{{ route("website-analytics.keywords.save", $website) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            keyword: keyword,
            position: currentPos,
            target_position: targetPos,
            priority: priority
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Keyword tracked successfully!', 'success');
            closeTrackModal();
        } else {
            showToast('Failed: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to track keyword', 'error');
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Auto-show modal if OAuth just completed
@if(session('success') && str_contains(session('success'), 'OAuth successful'))
    document.addEventListener('DOMContentLoaded', function() {
        openGA4PropertyModal();
    });
@endif

// ========================================
// SYNC PROGRESS POPUP & AUTO-REFRESH
// ========================================
let pollInterval = null;
let wasProcessing = false;
let pollCount = 0;
let syncStartTime = null;

const popup = {
    el: () => document.getElementById('syncProgressPopup'),
    bar: () => document.getElementById('syncProgressBar'),
    percentage: () => document.getElementById('syncPercentage'),
    statusText: () => document.getElementById('syncStatusText'),
    title: () => document.getElementById('syncTitle'),
    subtitle: () => document.getElementById('syncSubtitle'),
    gscIcon: () => document.getElementById('gscIcon'),
    ga4Icon: () => document.getElementById('ga4Icon'),
    gscBadge: () => document.getElementById('gscStatusBadge'),
    ga4Badge: () => document.getElementById('ga4StatusBadge'),
    syncStats: () => document.getElementById('syncStats'),
    keywordsSynced: () => document.getElementById('keywordsSynced'),
    
    show() {
        this.el().classList.remove('hidden');
        syncStartTime = Date.now();
    },
    hide() {
        this.el().classList.add('hidden');
    },
    setProgress(percent, status) {
        this.bar().style.width = percent + '%';
        this.percentage().textContent = percent + '%';
        if (status) this.statusText().textContent = status;
    },
    setGSC(status, isSyncing) {
        this.gscIcon().className = `w-2 h-2 rounded-full ${isSyncing ? 'bg-blue-500 animate-pulse' : (status === 'completed' ? 'bg-green-500' : 'bg-gray-300')}`;
        this.gscBadge().textContent = status || '--';
        this.gscBadge().className = isSyncing ? 'text-blue-600 font-medium' : (status === 'completed' ? 'text-green-600' : 'text-gray-500');
    },
    setGA4(status, isSyncing) {
        this.ga4Icon().className = `w-2 h-2 rounded-full ${isSyncing ? 'bg-orange-500 animate-pulse' : (status === 'completed' ? 'bg-green-500' : 'bg-gray-300')}`;
        this.ga4Badge().textContent = status || '--';
        this.ga4Badge().className = isSyncing ? 'text-orange-600 font-medium' : (status === 'completed' ? 'text-green-600' : 'text-gray-500');
    },
    setKeywords(count) {
        if (count > 0) {
            this.syncStats().classList.remove('hidden');
            this.keywordsSynced().textContent = count.toLocaleString();
        }
    },
    showComplete() {
        this.title().textContent = '✓ Sync Complete!';
        this.subtitle().textContent = 'Refreshing page...';
        this.setProgress(100, 'Done!');
        this.bar().classList.remove('from-blue-500', 'to-indigo-600');
        this.bar().classList.add('from-green-500', 'to-emerald-600');
    }
};

function checkSyncStatus() {
    pollCount++;

    fetch('{{ route("website-analytics.sync-status", $website) }}')
        .then(res => res.json())
        .then(data => {
            const gscSyncing = data.gsc?.is_syncing || false;
            const ga4Syncing = data.ga4?.is_syncing || false;
            const isProcessing = gscSyncing || ga4Syncing;

            // Update popup status
            popup.setGSC(data.gsc?.status, gscSyncing);
            popup.setGA4(data.ga4?.status, ga4Syncing);
            
            // Update keywords count if available
            if (data.gsc?.job?.keywords_synced) {
                popup.setKeywords(data.gsc.job.keywords_synced);
            }

            // Calculate progress based on time (simulated, max 90% until complete)
            if (isProcessing && syncStartTime) {
                const elapsed = (Date.now() - syncStartTime) / 1000;
                const progress = Math.min(90, Math.floor(elapsed * 2)); // ~45 sec to 90%
                popup.setProgress(progress, 'Fetching data from Google...');
            }

            // If currently syncing, set flag
            if (isProcessing) {
                wasProcessing = true;
            }

            // If was syncing before but now finished → SUCCESS!
            if (wasProcessing && !isProcessing) {
                clearInterval(pollInterval);
                pollInterval = null;
                
                popup.showComplete();

                // Refresh after 1.5 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(error => {
            console.error('❌ Sync status check failed:', error);
        });
}

function startPollingIfNeeded() {
    fetch('{{ route("website-analytics.sync-status", $website) }}')
        .then(res => res.json())
        .then(data => {
            const gscSyncing = data.gsc?.is_syncing || false;
            const ga4Syncing = data.ga4?.is_syncing || false;
            const isProcessing = gscSyncing || ga4Syncing;

            if (isProcessing) {
                wasProcessing = true;
                popup.show();
                popup.setProgress(10, 'Connecting to Google...');
                popup.setGSC(data.gsc?.status, gscSyncing);
                popup.setGA4(data.ga4?.status, ga4Syncing);
                
                // Start polling every 3 seconds
                pollInterval = setInterval(checkSyncStatus, 3000);

                // Stop polling after 5 minutes (safety fallback)
                setTimeout(() => {
                    if (pollInterval) {
                        clearInterval(pollInterval);
                        pollInterval = null;
                        popup.hide();
                    }
                }, 300000);
            }
        })
        .catch(error => {
            console.error('❌ Initial sync check failed:', error);
        });
}

// AJAX Sync Functions
function startGSCSync() {
    popup.show();
    popup.setProgress(5, 'Starting GSC sync...');
    popup.setGSC('starting', true);
    wasProcessing = true;
    syncStartTime = Date.now();
    
    fetch('{{ route("website-analytics.gsc.sync", $website) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            popup.setProgress(15, 'Sync job dispatched...');
            // Start polling
            pollInterval = setInterval(checkSyncStatus, 3000);
        } else {
            popup.hide();
            alert('Failed to start sync: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        popup.hide();
        alert('Failed to start sync');
    });
}

function startGA4Sync() {
    popup.show();
    popup.setProgress(5, 'Starting GA4 sync...');
    popup.setGA4('starting', true);
    wasProcessing = true;
    syncStartTime = Date.now();
    
    fetch('{{ route("website-analytics.ga4.sync", $website) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            popup.setProgress(15, 'Sync job dispatched...');
            // Start polling
            pollInterval = setInterval(checkSyncStatus, 3000);
        } else {
            popup.hide();
            alert('Failed to start sync: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        popup.hide();
        alert('Failed to start sync');
    });
}

// Check on page load if sync is in progress
document.addEventListener('DOMContentLoaded', function() {
    startPollingIfNeeded();
});
</script>
@endsection
