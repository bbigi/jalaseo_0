@extends('admin.layout')

@section('content')
    <div class="max-w-2xl">
        <h3 class="text-xl font-semibold mb-6">Edit API Key</h3>

        <form action="{{ route('admin.api-keys.update', $credential) }}" method="POST" class="bg-gray-800 rounded-xl p-8 border border-gray-700 space-y-6">
            @csrf
            @method('PUT')

            <!-- Provider (Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Provider</label>
                <input type="text" disabled value="{{ ucfirst($credential->provider) }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 opacity-50">
                <p class="text-gray-500 text-xs mt-1">Provider cannot be changed. Delete and recreate if needed.</p>
            </div>

            <!-- Display Name -->
            <div>
                <label for="display_name" class="block text-sm font-semibold text-gray-300 mb-2">Display Name</label>
                <input type="text" name="display_name" id="display_name" placeholder="e.g., My Gemini API Key" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500" value="{{ old('display_name', $credential->display_name) }}">
                @error('display_name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- API Key -->
            <div>
                <label for="api_key" class="block text-sm font-semibold text-gray-300 mb-2">API Key *</label>
                <div class="text-gray-400 text-xs mb-2">Current: <code class="bg-gray-900 px-2 py-1 rounded font-mono">{{ $credential->getMaskedApiKey() }}</code></div>
                <input type="password" name="api_key" id="api_key" placeholder="Leave blank to keep current key, or paste a new one" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono">
                <p class="text-gray-400 text-xs mt-2">Your API key will be encrypted and never displayed in plain text.</p>
                @error('api_key')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" placeholder="e.g., Production API key for content generation" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 h-24 resize-none">{{ old('description', $credential->description) }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Active -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" class="w-4 h-4 rounded bg-gray-700 border border-gray-600" {{ $credential->is_active ? 'checked' : '' }}>
                <label for="is_active" class="text-sm text-gray-300">Active</label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                    ✓ Update API Key
                </button>
                <a href="{{ route('admin.api-keys.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 font-semibold rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
