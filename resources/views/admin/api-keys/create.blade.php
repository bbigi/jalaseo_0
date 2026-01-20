@extends('admin.layout')

@section('content')
    <div class="max-w-2xl">
        <h3 class="text-xl font-semibold mb-6">Add New API Key</h3>

        <form action="{{ route('admin.api-keys.store') }}" method="POST" class="bg-gray-800 rounded-xl p-8 border border-gray-700 space-y-6">
            @csrf

            <!-- Category Selection -->
            <div>
                <label for="category" class="block text-sm font-semibold text-gray-300 mb-2">Category *</label>
                <select name="category" id="category" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500" required onchange="updateProviders()">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Provider Selection -->
            <div>
                <label for="provider" class="block text-sm font-semibold text-gray-300 mb-2">Provider *</label>
                <select name="provider" id="provider" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500" required>
                    <option value="">-- Select Provider --</option>
                </select>
                @error('provider')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Display Name -->
            <div>
                <label for="display_name" class="block text-sm font-semibold text-gray-300 mb-2">Display Name</label>
                <input type="text" name="display_name" id="display_name" placeholder="e.g., My API Key" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500" value="{{ old('display_name') }}">
            </div>

            <!-- API Key -->
            <div>
                <label for="api_key" class="block text-sm font-semibold text-gray-300 mb-2">API Key / Username *</label>
                <input type="password" name="api_key" id="api_key" placeholder="Paste your API key here" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono" required>
                <p class="text-gray-400 text-xs mt-2">Encrypted and never displayed in plain text.</p>
                @error('api_key')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- API Secret (for providers that need password/secret) -->
            <div id="apiSecretField" class="hidden">
                <label for="api_secret" class="block text-sm font-semibold text-gray-300 mb-2">API Password / Secret</label>
                <input type="password" name="api_secret" id="api_secret" placeholder="Paste your API password or secret" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 font-mono">
                <p class="text-gray-400 text-xs mt-2">Required for DataForSEO and other providers with dual credentials.</p>
                @error('api_secret')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" placeholder="e.g., Production key" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-orange-500 h-20 resize-none">{{ old('description') }}</textarea>
            </div>

            <!-- Is Active -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" class="w-4 h-4 rounded bg-gray-700 border border-gray-600" checked>
                <label for="is_active" class="text-sm text-gray-300">Activate immediately</label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 justify-end">
                <a href="{{ route('admin.api-keys.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-gray-100 font-semibold rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-lg transition">
                    ✓ Add API Key
                </button>
            </div>
        </form>
    </div>

    <script>
        const providersByCategory = @json($providers);
        const providersNeedSecret = ['dataforseo']; // Providers that need api_secret

        function updateProviders() {
            const category = document.getElementById('category').value;
            const providerSelect = document.getElementById('provider');
            
            providerSelect.innerHTML = '<option value="">-- Select Provider --</option>';
            
            if (category && providersByCategory[category]) {
                Object.entries(providersByCategory[category]).forEach(([key, value]) => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = value;
                    providerSelect.appendChild(option);
                });
            }
            
            toggleSecretField();
        }

        function toggleSecretField() {
            const provider = document.getElementById('provider').value;
            const secretField = document.getElementById('apiSecretField');
            
            if (providersNeedSecret.includes(provider)) {
                secretField.classList.remove('hidden');
            } else {
                secretField.classList.add('hidden');
            }
        }

        document.getElementById('provider').addEventListener('change', toggleSecretField);
    </script>
@endsection
