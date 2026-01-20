@extends('layouts.dashboard')

@section('title', 'Create Branding Preset - AneWP')

@section('content')
<!-- Top Bar -->
<div class="px-8 py-4 pl-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Branding Preset</h2>
            <p class="text-gray-600 text-sm mt-1">Define your brand identity</p>
        </div>
        <a href="{{ route('brandings.index') }}"
           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">
            Back to Presets
        </a>
    </div>
</div>

<!-- Content -->
<div class="p-8">
    <div class="max-w-3xl">
        <form action="{{ route('brandings.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-8">
            @csrf

            <!-- Preset Name -->
            <div class="mb-6">
                <label for="preset_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Preset Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="preset_name"
                       id="preset_name"
                       value="{{ old('preset_name') }}"
                       placeholder="e.g., TechBlog Brand, E-commerce Default"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('preset_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Website -->
            <div class="mb-6">
                <label for="website_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Associated Website <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <select name="website_id"
                        id="website_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- No website (General) --</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}" {{ old('website_id') == $website->id ? 'selected' : '' }}>
                            {{ $website->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500">Link this preset to a specific website or keep it general.</p>
                @error('website_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Logo -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Brand Logo <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <input type="hidden" name="logo_id" id="logoId" value="{{ old('logo_id') }}">
                <div id="logoPreview" class="mb-3 {{ old('logo_id') ? '' : 'hidden' }}">
                    <div class="relative">
                        <img id="logoThumb" src="" alt="" class="w-full h-40 object-contain rounded border border-gray-300 bg-gray-50">
                        <button type="button" onclick="removeLogo()" class="absolute top-2 right-2 w-6 h-6 bg-red-600 hover:bg-red-700 rounded-full text-white text-xs">×</button>
                    </div>
                </div>
                <button type="button" onclick="openLogoLibrary()" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition flex text-sm items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Select Logo
                </button>
                <p class="text-xs text-gray-500 mt-2">Select from library, upload, or generate with AI</p>
                @error('logo_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Colors -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Primary Color -->
                <div>
                    <label for="primary_color" class="block text-sm font-semibold text-gray-700 mb-2">
                        Primary Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               name="primary_color"
                               id="primary_color"
                               value="{{ old('primary_color', '#000000') }}"
                               required
                               class="w-16 h-12 border border-gray-300 rounded cursor-pointer">
                        <input type="text"
                               id="primary_color_text"
                               value="{{ old('primary_color', '#000000') }}"
                               readonly
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                    </div>
                    @error('primary_color')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Secondary Color -->
                <div>
                    <label for="secondary_color" class="block text-sm font-semibold text-gray-700 mb-2">
                        Secondary Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               name="secondary_color"
                               id="secondary_color"
                               value="{{ old('secondary_color', '#ffffff') }}"
                               required
                               class="w-16 h-12 border border-gray-300 rounded cursor-pointer">
                        <input type="text"
                               id="secondary_color_text"
                               value="{{ old('secondary_color', '#ffffff') }}"
                               readonly
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                    </div>
                    @error('secondary_color')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Default Font -->
            <div class="mb-6">
                <label for="default_font" class="block text-sm font-semibold text-gray-700 mb-2">
                    Default Font <span class="text-red-500">*</span>
                </label>
                <select name="default_font"
                        id="default_font"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="Inter" {{ old('default_font', 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                    <option value="Roboto" {{ old('default_font') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                    <option value="Poppins" {{ old('default_font') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    <option value="Montserrat" {{ old('default_font') == 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                    <option value="Open Sans" {{ old('default_font') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                    <option value="Lato" {{ old('default_font') == 'Lato' ? 'selected' : '' }}>Lato</option>
                    <option value="Playfair Display" {{ old('default_font') == 'Playfair Display' ? 'selected' : '' }}>Playfair Display</option>
                    <option value="Merriweather" {{ old('default_font') == 'Merriweather' ? 'selected' : '' }}>Merriweather</option>
                </select>
                @error('default_font')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Default Template -->
            <div class="mb-8">
                <label for="default_template_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Default Template for Social Media <span class="text-gray-500 text-xs">(Optional)</span>
                </label>
                <select name="default_template_id"
                        id="default_template_id"
                        onchange="previewTemplate(this.value)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" {{ old('default_template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }} ({{ $template->category }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500">Select default article template for social media amplification.</p>

                <!-- Template Preview -->
                <div id="templatePreview" class="hidden mt-4">
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <img id="templateThumbnail" src="" alt="" class="w-full h-48 object-contain rounded">
                        <div class="mt-2">
                            <p class="text-sm font-semibold text-gray-700" id="templateName"></p>
                            <p class="text-xs text-gray-500" id="templateDimensions"></p>
                        </div>
                    </div>
                </div>

                @error('default_template_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4">
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
                    Create Preset
                </button>
                <a href="{{ route('brandings.index') }}"
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Media Library Component -->
<x-media-library />

<script>
// Media Library Integration
function openLogoLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                document.getElementById('logoId').value = media.id;
                document.getElementById('logoThumb').src = media.full_url;
                document.getElementById('logoPreview').classList.remove('hidden');
            }
        }
    }));
}

function removeLogo() {
    document.getElementById('logoId').value = '';
    document.getElementById('logoPreview').classList.add('hidden');
}

// Color sync
document.getElementById('primary_color').addEventListener('input', function() {
    document.getElementById('primary_color_text').value = this.value;
});

document.getElementById('secondary_color').addEventListener('input', function() {
    document.getElementById('secondary_color_text').value = this.value;
});

// Template preview
function previewTemplate(templateId) {
    if (!templateId) {
        document.getElementById('templatePreview').classList.add('hidden');
        return;
    }

    fetch(`/api/templates/${templateId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(template => {
        document.getElementById('templateThumbnail').src = template.thumbnail_url;
        document.getElementById('templateName').textContent = template.name;
        document.getElementById('templateDimensions').textContent = `${template.canvas_width} × ${template.canvas_height}px`;
        document.getElementById('templatePreview').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Template preview error:', error);
    });
}
</script>
@endsection
