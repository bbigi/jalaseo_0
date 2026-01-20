@extends('admin.layout')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <h3 class="text-xl font-semibold">Edit OAuth Service</h3>
            <p class="text-gray-400 text-sm mt-1">Update OAuth credentials for {{ $oauthService->display_name }}</p>
        </div>

        <form action="{{ route('admin.oauth-services.update', $oauthService) }}" method="POST" class="bg-gray-800 rounded-xl p-8 border border-gray-700 space-y-6">
            @csrf
            @method('PUT')

            <!-- Service Name -->
            <div>
                <label for="service_name" class="block text-sm font-semibold text-gray-300 mb-2">Service Name *</label>
                <input type="text" name="service_name" id="service_name" placeholder="e.g., google, facebook" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono" value="{{ old('service_name', $oauthService->service_name) }}" required>
                <p class="text-gray-400 text-xs mt-1">Lowercase, no spaces. Used in code (e.g., "google" for Google OAuth)</p>
                @error('service_name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Display Name -->
            <div>
                <label for="display_name" class="block text-sm font-semibold text-gray-300 mb-2">Display Name *</label>
                <input type="text" name="display_name" id="display_name" placeholder="e.g., Google OAuth (GSC & GA4)" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500" value="{{ old('display_name', $oauthService->display_name) }}" required>
                @error('display_name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Client ID -->
            <div>
                <label for="client_id" class="block text-sm font-semibold text-gray-300 mb-2">Client ID *</label>
                <input type="text" name="client_id" id="client_id" placeholder="Current: {{ $oauthService->getMaskedClientId() }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono" value="{{ old('client_id', $oauthService->client_id) }}" required>
                <p class="text-gray-400 text-xs mt-2">Will be encrypted in database</p>
                @error('client_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Client Secret -->
            <div>
                <label for="client_secret" class="block text-sm font-semibold text-gray-300 mb-2">Client Secret *</label>
                <input type="password" name="client_secret" id="client_secret" placeholder="Current: {{ $oauthService->getMaskedClientSecret() }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono" value="{{ old('client_secret', $oauthService->client_secret) }}" required>
                <p class="text-gray-400 text-xs mt-2">Encrypted and never displayed in plain text</p>
                @error('client_secret')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Redirect URI -->
            <div>
                <label for="redirect_uri" class="block text-sm font-semibold text-gray-300 mb-2">Redirect URI</label>
                <input type="url" name="redirect_uri" id="redirect_uri" placeholder="e.g., http://127.0.0.1:8000/oauth/google/callback" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono" value="{{ old('redirect_uri', $oauthService->redirect_uri) }}">
                <p class="text-gray-400 text-xs mt-1">Leave empty to use default from .env</p>
                @error('redirect_uri')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Scopes -->
            <div>
                <label for="scopes" class="block text-sm font-semibold text-gray-300 mb-2">OAuth Scopes</label>
                <textarea name="scopes" id="scopes" placeholder="e.g., email, profile, https://www.googleapis.com/auth/webmasters.readonly" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 h-20 resize-none font-mono">{{ old('scopes', is_array($oauthService->scopes) ? implode(', ', $oauthService->scopes) : '') }}</textarea>
                <p class="text-gray-400 text-xs mt-1">Comma-separated OAuth scopes (optional)</p>
                @error('scopes')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-300 mb-2">Admin Notes</label>
                <textarea name="notes" id="notes" placeholder="e.g., Created on Google Cloud Console project 'anewp-prod'" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 h-20 resize-none">{{ old('notes', $oauthService->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 justify-end">
                <a href="{{ route('admin.oauth-services.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 font-semibold rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                    Update OAuth Service
                </button>
            </div>
        </form>
    </div>
@endsection
