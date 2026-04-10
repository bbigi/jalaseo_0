@extends('layouts.dashboard')

@section('title', 'Dashboard - AneWP')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<!-- Top Bar -->
<div class="bg-white border-b border-gray-200 px-8 py-4">
    <h2 class="text-2xl font-bold text-gray-900">Welcome , {{ auth()->user()->name }}! 👋</h2>
    <p class="text-gray-600 text-sm mt-1">Here's what's happening with your content</p>
</div>

<!-- Content -->
<div class="p-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Posts Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 hover:border-blue-300 transition shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-700 text-sm font-medium">Total Posts</p>
                                <p class="text-4xl font-bold mt-2 text-blue-900">{{ $postsCount ?? 0 }}</p>
                                <p class="text-blue-600 text-xs mt-2">↑ 12% from last month</p>
                            </div>
                            <div class="text-5xl opacity-30">📝</div>
                        </div>
                    </div>

                    <!-- Insights Card -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200 hover:border-purple-300 transition shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-700 text-sm font-medium">Total Insights</p>
                                <p class="text-4xl font-bold mt-2 text-purple-900">{{ $insightsCount ?? 0 }}</p>
                                <p class="text-purple-600 text-xs mt-2">↑ 8% from last month</p>
                            </div>
                            <div class="text-5xl opacity-30">💡</div>
                        </div>
                    </div>

                    <!-- Websites Card -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 hover:border-green-300 transition shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-700 text-sm font-medium">Active Websites</p>
                                <p class="text-4xl font-bold mt-2 text-green-900">{{ $websitesCount ?? 0 }}</p>
                                <p class="text-green-600 text-xs mt-2">All connected ✓</p>
                            </div>
                            <div class="text-5xl opacity-30">🌐</div>
                        </div>
                    </div>

                    <!-- API Usage Card -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200 hover:border-orange-300 transition shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-700 text-sm font-medium">API Usage</p>
                                <p class="text-4xl font-bold mt-2 text-orange-900">{{ auth()->user()->api_quota_used ?? 0 }}/{{ auth()->user()->api_quota_limit }}</p>
                                <div class="w-full bg-orange-200 rounded-full h-2 mt-3">
                                    <div class="bg-orange-600 h-2 rounded-full" style="width: {{ (auth()->user()->api_quota_used / auth()->user()->api_quota_limit) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Posts Chart -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Posts Over Time</h3>
                        <canvas id="postsChart"></canvas>
                    </div>

                    <!-- API Usage Chart -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">API Usage Breakdown</h3>
                        <canvas id="apiChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Recent Activity</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                            <div>
                                <p class="font-medium text-gray-900">New post published</p>
                                <p class="text-gray-600 text-sm">Lorem ipsum dolor sit amet</p>
                            </div>
                            <span class="text-gray-500 text-sm">2 hours ago</span>
                        </div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                            <div>
                                <p class="font-medium text-gray-900">Deep research completed</p>
                                <p class="text-gray-600 text-sm">AI trends analysis 2025</p>
                            </div>
                            <span class="text-gray-500 text-sm">5 hours ago</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Website connected</p>
                                <p class="text-gray-600 text-sm">example.com</p>
                            </div>
                            <span class="text-gray-500 text-sm">1 day ago</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="#" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition text-center">
                        ✍️ Create New Post
                    </a>
                    <a href="#" class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold py-3 px-6 rounded-xl transition text-center">
                        🔍 Start Deep Research
                    </a>
                    <a href="#" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-3 px-6 rounded-xl transition text-center">
                        🌐 Manage Websites
                    </a>
                </div>
            </div>
</div>

@push('scripts')
<script>
        // Posts Chart
        const postsCtx = document.getElementById('postsChart').getContext('2d');
        new Chart(postsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Posts Published',
                    data: [2, 4, 3, 5, 6, 8],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: { color: '#374151' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e5e7eb' },
                        ticks: { color: '#6b7280' }
                    },
                    x: {
                        grid: { color: '#e5e7eb' },
                        ticks: { color: '#6b7280' }
                    }
                }
            }
        });

        // API Usage Chart
        const apiCtx = document.getElementById('apiChart').getContext('2d');
        new Chart(apiCtx, {
            type: 'doughnut',
            data: {
                labels: ['Used', 'Available'],
                datasets: [{
                    data: [{{ auth()->user()->api_quota_used ?? 0 }}, {{ (auth()->user()->api_quota_limit ?? 100) - (auth()->user()->api_quota_used ?? 0) }}],
                    backgroundColor: [
                        '#f97316',
                        '#e5e7eb'
                    ],
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#374151' }
                    }
                }
            }
        });
    </script>
@endpush
@endsection
