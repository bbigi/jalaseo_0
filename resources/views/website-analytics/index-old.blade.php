@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Web Analytics</h1>
                <p class="text-gray-400 mt-1">{{ $website->name }}</p>
            </div>
            <a href="{{ route('websites.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                ← Back to Websites
            </a>
        </div>
    </div>

    <!-- Connection Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Google Search Console Card -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Google Search Console</h3>
                        <p class="text-sm text-gray-400">Keywords & Pages Performance</p>
                    </div>
                </div>
                @if($gscConnection)
                    <span class="px-2 py-1 bg-green-900 text-green-200 text-xs font-semibold rounded">Connected</span>
                @else
                    <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs font-semibold rounded">Not Connected</span>
                @endif
            </div>

            @if($gscConnection)
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Last Sync:</span>
                        <span class="text-white">{{ $gscConnection->last_sync_at ? $gscConnection->last_sync_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Status:</span>
                        <span class="text-white capitalize">{{ $gscConnection->sync_status }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Property:</span>
                        <span class="text-white text-xs">{{ $gscConnection->property_url }}</span>
                    </div>

                    <div class="flex gap-2 pt-3">
                        <form action="{{ route('website-analytics.gsc.sync', $website) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-700 hover:bg-blue-600 text-white text-sm rounded-lg transition">
                                Sync Now
                            </button>
                        </form>
                        <form action="{{ route('website-analytics.gsc.disconnect', $website) }}" method="POST" onsubmit="return confirm('Disconnect Google Search Console?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg transition">
                                Disconnect
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-sm mb-4">Connect to track keywords, impressions, clicks, and rankings from Google Search.</p>
                <a href="{{ route('website-analytics.gsc.connect', $website) }}" class="block w-full px-4 py-2 bg-blue-700 hover:bg-blue-600 text-white text-center rounded-lg transition">
                    Connect Google Search Console
                </a>
            @endif
        </div>

        <!-- Google Analytics 4 Card -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10zm-2 0h-4c0-2.21-1.79-4-4-4V4c4.41 0 8 3.59 8 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Google Analytics 4</h3>
                        <p class="text-sm text-gray-400">Traffic & Referral Sources</p>
                    </div>
                </div>
                @if($ga4Connection)
                    <span class="px-2 py-1 bg-green-900 text-green-200 text-xs font-semibold rounded">Connected</span>
                @else
                    <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs font-semibold rounded">Not Connected</span>
                @endif
            </div>

            @if($ga4Connection)
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Last Sync:</span>
                        <span class="text-white">{{ $ga4Connection->last_sync_at ? $ga4Connection->last_sync_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Status:</span>
                        <span class="text-white capitalize">{{ $ga4Connection->sync_status }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">Property ID:</span>
                        <span class="text-white text-xs">{{ $ga4Connection->property_id ?: 'Not set' }}</span>
                    </div>

                    @if(!$ga4Connection->property_id)
                        <!-- Setup Property ID Button -->
                        <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-3 text-sm text-yellow-200">
                            <p class="mb-2">⚠️ Please setup your GA4 Property ID to enable sync</p>
                            <button onclick="openGA4PropertyModal()" class="w-full px-4 py-2 bg-yellow-700 hover:bg-yellow-600 text-white rounded-lg transition">
                                Setup Property ID
                            </button>
                        </div>
                    @else
                        <!-- Normal Sync Buttons -->
                        <div class="flex gap-2 pt-3">
                            <form action="{{ route('website-analytics.ga4.sync', $website) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-orange-700 hover:bg-orange-600 text-white text-sm rounded-lg transition">
                                    Sync Now
                                </button>
                            </form>
                            <form action="{{ route('website-analytics.ga4.disconnect', $website) }}" method="POST" onsubmit="return confirm('Disconnect Google Analytics 4?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg transition">
                                    Disconnect
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-gray-400 text-sm mb-4">Connect to track traffic, referral sources (FREE backlink alternative!), and page performance.</p>
                <a href="{{ route('website-analytics.ga4.connect', $website) }}" class="block w-full px-4 py-2 bg-orange-700 hover:bg-orange-600 text-white text-center rounded-lg transition">
                    Connect Google Analytics 4
                </a>
            @endif
        </div>
    </div>

    @if($gscConnection || $ga4Connection)
        <!-- Tabs -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="border-b border-gray-700 flex">
                <button onclick="switchTab('keywords')" id="tab-keywords" class="px-6 py-3 text-sm font-medium text-white bg-gray-700 border-b-2 border-blue-500">
                    Keywords (GSC)
                </button>
                <button onclick="switchTab('pages')" id="tab-pages" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition">
                    Pages (GSC)
                </button>
                <button onclick="switchTab('traffic')" id="tab-traffic" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition">
                    Traffic (GA4)
                </button>
                <button onclick="switchTab('referrals')" id="tab-referrals" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition">
                    Referral Sources (GA4)
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Keywords Tab -->
                <div id="content-keywords">
                    @if($gscConnection && $gscKeywords && $gscKeywords->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-700">
                                    <tr class="text-left text-gray-400">
                                        <th class="pb-3 px-4">Keyword</th>
                                        <th class="pb-3 px-4 text-right">Clicks</th>
                                        <th class="pb-3 px-4 text-right">Impressions</th>
                                        <th class="pb-3 px-4 text-right">CTR</th>
                                        <th class="pb-3 px-4 text-right">Position</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white">
                                    @foreach($gscKeywords as $keyword)
                                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                                        <td class="py-3 px-4">{{ $keyword->keyword }}</td>
                                        <td class="py-3 px-4 text-right">{{ $keyword->clicks }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($keyword->impressions) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($keyword->ctr * 100, 2) }}%</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($keyword->position, 1) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($gscConnection)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-400">Keyword data will appear here after first sync completes.</p>
                            <p class="text-gray-500 text-sm mt-2">Check back in a few minutes...</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-400">Connect Google Search Console to view keyword data.</p>
                        </div>
                    @endif
                </div>

                <!-- Pages Tab -->
                <div id="content-pages" class="hidden">
                    @if($gscConnection && $gscPages && $gscPages->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-700">
                                    <tr class="text-left text-gray-400">
                                        <th class="pb-3 px-4">Page URL</th>
                                        <th class="pb-3 px-4 text-right">Clicks</th>
                                        <th class="pb-3 px-4 text-right">Impressions</th>
                                        <th class="pb-3 px-4 text-right">CTR</th>
                                        <th class="pb-3 px-4 text-right">Position</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white">
                                    @foreach($gscPages as $page)
                                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                                        <td class="py-3 px-4">
                                            <a href="{{ $page->page_url }}" target="_blank" class="text-blue-400 hover:text-blue-300 hover:underline">
                                                {{ Str::limit($page->page_url, 80) }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-right">{{ $page->clicks }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($page->impressions) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($page->ctr * 100, 2) }}%</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($page->position, 1) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($gscConnection)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-400">Page performance data will appear here after first sync completes.</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-400">Connect Google Search Console to view page data.</p>
                        </div>
                    @endif
                </div>

                <!-- Traffic Tab -->
                <div id="content-traffic" class="hidden">
                    @if($ga4Connection && $ga4Traffic && $ga4Traffic->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-700">
                                    <tr class="text-left text-gray-400">
                                        <th class="pb-3 px-4">Date</th>
                                        <th class="pb-3 px-4 text-right">Users</th>
                                        <th class="pb-3 px-4 text-right">Sessions</th>
                                        <th class="pb-3 px-4 text-right">Page Views</th>
                                        <th class="pb-3 px-4 text-right">Bounce Rate</th>
                                        <th class="pb-3 px-4 text-right">Avg. Session</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white">
                                    @foreach($ga4Traffic as $traffic)
                                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                                        <td class="py-3 px-4">{{ \Carbon\Carbon::parse($traffic->date)->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($traffic->users) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($traffic->sessions) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($traffic->page_views) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($traffic->bounce_rate * 100, 2) }}%</td>
                                        <td class="py-3 px-4 text-right">{{ gmdate('i:s', $traffic->avg_session_duration) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($ga4Connection)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-gray-400">Traffic data will appear here after first sync completes.</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-400">Connect Google Analytics 4 to view traffic data.</p>
                        </div>
                    @endif
                </div>

                <!-- Referrals Tab -->
                <div id="content-referrals" class="hidden">
                    @if($ga4Connection && $ga4Referrals && $ga4Referrals->count() > 0)
                        <div class="mb-4 bg-gradient-to-r from-green-900 to-blue-900 rounded-lg p-4 border border-green-700">
                            <p class="text-green-200 font-semibold flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                FREE Backlink Alternative!
                            </p>
                            <p class="text-green-300 text-sm mt-1">Track where your traffic comes from without expensive backlink tools</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-700">
                                    <tr class="text-left text-gray-400">
                                        <th class="pb-3 px-4">Source</th>
                                        <th class="pb-3 px-4">Medium</th>
                                        <th class="pb-3 px-4 text-right">Users</th>
                                        <th class="pb-3 px-4 text-right">Sessions</th>
                                        <th class="pb-3 px-4 text-right">Page Views</th>
                                        <th class="pb-3 px-4 text-right">Bounce Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white">
                                    @foreach($ga4Referrals as $referral)
                                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                                        <td class="py-3 px-4">
                                            <span class="font-medium text-blue-400">{{ $referral->source }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-gray-300">{{ $referral->medium }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right">{{ number_format($referral->users) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($referral->sessions) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($referral->page_views) }}</td>
                                        <td class="py-3 px-4 text-right">{{ number_format($referral->bounce_rate * 100, 2) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($ga4Connection)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <p class="text-gray-400 font-semibold">FREE Backlink Alternative!</p>
                            <p class="text-gray-500 text-sm mt-2">Referral source data will appear here after first sync completes.</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-400">Connect Google Analytics 4 to view referral sources (FREE backlink alternative!).</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- No Connections State -->
        <div class="bg-gray-800 rounded-xl p-12 border border-gray-700 text-center">
            <svg class="w-20 h-20 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No Analytics Connected</h3>
            <p class="text-gray-400 mb-6">Connect Google Search Console and/or Google Analytics 4 to start tracking your website performance.</p>
        </div>
    @endif
</div>

<!-- GA4 Property ID Modal -->
<div id="ga4PropertyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-700">
                <h3 class="text-xl font-semibold text-white">Setup GA4 Property ID</h3>
                <button onclick="closeGA4PropertyModal()" class="text-gray-400 hover:text-white transition">
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
                    <label for="property_id" class="block text-sm font-medium text-gray-300 mb-2">
                        GA4 Property ID <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="property_id"
                        id="property_id"
                        value="{{ old('property_id', $ga4Connection->property_id ?? '') }}"
                        placeholder="e.g., 123456789"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-lg"
                        required
                    >
                    @error('property_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instructions -->
                <div class="bg-gray-700 rounded-lg p-5 border border-gray-600">
                    <h4 class="text-white font-medium mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        How to find your GA4 Property ID:
                    </h4>
                    <ol class="list-decimal list-inside text-gray-300 text-sm space-y-2 ml-2">
                        <li>Go to <a href="https://analytics.google.com" target="_blank" class="text-orange-400 hover:text-orange-300 underline">Google Analytics</a></li>
                        <li>Click <strong>⚙️ Admin</strong> (bottom left)</li>
                        <li>In the <strong>Property</strong> column, click <strong>Property Settings</strong></li>
                        <li>Copy the <strong>Property ID</strong> (a number like <code class="px-1 py-0.5 bg-gray-800 rounded text-orange-300">123456789</code>)</li>
                    </ol>
                </div>

                <!-- Screenshot Hint -->
                <div class="bg-blue-900 bg-opacity-30 border border-blue-700 rounded-lg p-4 text-sm text-blue-200">
                    <p class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span><strong>Tip:</strong> If you can't find it, look for "Property ID" in the top section of Property Settings page.</span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                    <button type="button" onclick="closeGA4PropertyModal()" class="px-4 py-2 text-gray-400 hover:text-white transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition shadow-lg">
                        Save & Start Sync
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('[id^="content-"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(el => {
        el.classList.remove('bg-gray-700', 'border-b-2', 'border-blue-500', 'text-white');
        el.classList.add('text-gray-400');
    });

    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('bg-gray-700', 'border-b-2', 'border-blue-500', 'text-white');
    activeTab.classList.remove('text-gray-400');
}

function openGA4PropertyModal() {
    document.getElementById('ga4PropertyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeGA4PropertyModal() {
    document.getElementById('ga4PropertyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Auto-show modal if OAuth just completed (session message contains "OAuth successful")
@if(session('success') && str_contains(session('success'), 'OAuth successful'))
    document.addEventListener('DOMContentLoaded', function() {
        openGA4PropertyModal();
    });
@endif
</script>
@endsection
