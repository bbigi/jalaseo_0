@extends('layouts.dashboard')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-500 text-white px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Setup Card -->
    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">Complete GA4 Setup</h2>
            <p class="text-gray-400 text-sm mt-1">Enter your Google Analytics 4 Property ID</p>
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
                    value="{{ old('property_id', $connection->property_id) }}"
                    placeholder="e.g., 123456789"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
                @error('property_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-2">
                    Find this in Google Analytics → Admin → Property Settings
                </p>
            </div>

            <!-- Instructions -->
            <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                <h3 class="text-white font-medium mb-2">How to find your GA4 Property ID:</h3>
                <ol class="list-decimal list-inside text-gray-300 text-sm space-y-1">
                    <li>Go to <a href="https://analytics.google.com" target="_blank" class="text-blue-400 hover:text-blue-300">Google Analytics</a></li>
                    <li>Click ⚙️ <strong>Admin</strong> (bottom left)</li>
                    <li>In the <strong>Property</strong> column, click <strong>Property Settings</strong></li>
                    <li>Copy the <strong>Property ID</strong> (a number like 123456789)</li>
                </ol>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <a
                    href="{{ route('website-analytics.index', $website) }}"
                    class="text-gray-400 hover:text-white"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors"
                >
                    Save & Start Sync
                </button>
            </div>
        </form>
    </div>

    <!-- Website Info -->
    <div class="mt-4 text-center">
        <p class="text-gray-500 text-sm">
            Setting up GA4 for: <span class="text-white font-medium">{{ $website->name }}</span>
        </p>
    </div>
</div>
@endsection
