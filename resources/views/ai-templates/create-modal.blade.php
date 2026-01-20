<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Template</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">

<!-- Content -->
<div class="p-6 bg-white">
    <div class="max-w-4xl mx-auto">
        <div>
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
                    <h6 class="font-semibold mb-2">Please fix the following errors:</h6>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ isset($aiTemplate) ? route('ai-templates.update', $aiTemplate) : route('ai-templates.store') }}" id="templateForm">
                @csrf
                @if (isset($aiTemplate))
                    @method('PUT')
                @endif

                <!-- Template Name -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-2 text-gray-700">
                        Template Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $aiTemplate->name ?? '') }}"
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                           placeholder="e.g., SEO Blog Template"
                           required>
                    <p class="text-gray-600 text-sm mt-2">A friendly name to identify this template</p>
                </div>

                <!-- AI Settings Section -->
                <div class="border-t border-gray-100 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">AI Settings</h3>

                    <!-- AI Provider -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            AI Provider
                        </label>
                        <div class="custom-select-wrapper" data-name="ai_provider" data-value="{{ old('ai_provider', $aiTemplate->ai_provider ?? '') }}" id="providerSelect">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select AI Provider</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search provider..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    @forelse($aiProviders ?? [] as $provider)
                                        <div class="custom-select-option" data-value="{{ $provider['value'] }}">{{ $provider['label'] }}</div>
                                    @empty
                                        <div class="px-4 py-3 text-sm text-gray-500 text-center">No AI providers configured</div>
                                    @endforelse
                                </div>
                            </div>
                            <input type="hidden" name="ai_provider" value="{{ old('ai_provider', $aiTemplate->ai_provider ?? '') }}">
                        </div>
                        <p class="text-gray-600 text-sm mt-2">Select from active AI providers</p>
                    </div>

                    <!-- AI Model (Cascading) -->
                    <div class="mb-6" id="modelSelectContainer">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            AI Model
                        </label>
                        <div class="custom-select-wrapper" data-name="ai_model" data-value="{{ old('ai_model', $aiTemplate->ai_model ?? '') }}" id="modelSelect">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select AI Provider first</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search model..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options" id="modelOptions">
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">Please select a provider first</div>
                                </div>
                            </div>
                            <input type="hidden" name="ai_model" value="{{ old('ai_model', $aiTemplate->ai_model ?? '') }}">
                        </div>
                        <p class="text-gray-600 text-sm mt-2">Specific model version from the selected provider</p>
                    </div>

                    <!-- Text Readability -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Text Readability
                        </label>
                        <div class="custom-select-wrapper" data-name="text_readability" data-value="{{ old('text_readability', $aiTemplate->text_readability ?? '') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Readability Level</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="elementary">Elementary School (5th-6th grade)</div>
                                    <div class="custom-select-option" data-value="middle">Middle School (7th-8th grade)</div>
                                    <div class="custom-select-option" data-value="high">High School (9th-12th grade)</div>
                                    <div class="custom-select-option" data-value="college">College Student</div>
                                    <div class="custom-select-option" data-value="graduate">Graduate Student</div>
                                    <div class="custom-select-option" data-value="general">General Adult Audience</div>
                                    <div class="custom-select-option" data-value="technical">Technical/Professional</div>
                                    <div class="custom-select-option" data-value="academic">Academic/Research</div>
                                </div>
                            </div>
                            <input type="hidden" name="text_readability" value="{{ old('text_readability', $aiTemplate->text_readability ?? '') }}">
                        </div>
                    </div>

                    <!-- AI Content Cleaning -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="ai_content_cleaning"
                                   value="1"
                                   {{ old('ai_content_cleaning', $aiTemplate->ai_content_cleaning ?? false) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm font-semibold text-gray-700">AI Content Cleaning</span>
                        </label>
                        <p class="text-gray-600 text-sm mt-2 ml-6">Remove AI-specific phrases and improve content quality</p>
                    </div>
                </div>

                <!-- Core Settings Section -->
                <div class="border-t border-gray-100 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Core Settings</h3>

                    <!-- Language -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Language <span class="text-red-500">*</span>
                        </label>
                        <div class="custom-select-wrapper" data-name="language" data-value="{{ old('language', $aiTemplate->language ?? 'en_US') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Language</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    @foreach($languages ?? [] as $lang)
                                        <div class="custom-select-option" data-value="{{ $lang['code'] }}">
                                            <span class="mr-2">{{ $lang['flag'] }}</span>{{ $lang['name'] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="language" value="{{ old('language', $aiTemplate->language ?? 'en_US') }}" required>
                        </div>
                    </div>

                    <!-- Article Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Article Type
                        </label>
                        <div class="custom-select-wrapper" data-name="article_type" data-value="{{ old('article_type', $aiTemplate->article_type ?? '') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Article Type</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="Blog Post">Blog Post</div>
                                    <div class="custom-select-option" data-value="How-to Guide">How-to Guide</div>
                                    <div class="custom-select-option" data-value="Listicle">Listicle</div>
                                    <div class="custom-select-option" data-value="News Article">News Article</div>
                                    <div class="custom-select-option" data-value="Product Review">Product Review</div>
                                    <div class="custom-select-option" data-value="Case Study">Case Study</div>
                                    <div class="custom-select-option" data-value="Tutorial">Tutorial</div>
                                    <div class="custom-select-option" data-value="Opinion Piece">Opinion Piece</div>
                                </div>
                            </div>
                            <input type="hidden" name="article_type" value="{{ old('article_type', $aiTemplate->article_type ?? '') }}">
                        </div>
                    </div>

                    <!-- Article Size -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Article Size <span class="text-red-500">*</span>
                        </label>
                        <div class="custom-select-wrapper" data-name="article_size" data-value="{{ old('article_size', $aiTemplate->article_size ?? 'medium') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Article Size</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="x-small">X-Small (300-500 words)</div>
                                    <div class="custom-select-option" data-value="small">Small (500-800 words)</div>
                                    <div class="custom-select-option" data-value="medium">Medium (800-1200 words)</div>
                                    <div class="custom-select-option" data-value="large">Large (1200-2000 words)</div>
                                </div>
                            </div>
                            <input type="hidden" name="article_size" value="{{ old('article_size', $aiTemplate->article_size ?? 'medium') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Content Settings Section -->
                <div class="border-t border-gray-100 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Settings</h3>

                    <!-- Tone -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Tone
                        </label>
                        <div class="custom-select-wrapper" data-name="tone" data-value="{{ old('tone', $aiTemplate->tone ?? '') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Tone</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="Professional">Professional</div>
                                    <div class="custom-select-option" data-value="Casual">Casual</div>
                                    <div class="custom-select-option" data-value="Friendly">Friendly</div>
                                    <div class="custom-select-option" data-value="Formal">Formal</div>
                                    <div class="custom-select-option" data-value="Informative">Informative</div>
                                    <div class="custom-select-option" data-value="Persuasive">Persuasive</div>
                                    <div class="custom-select-option" data-value="Educational">Educational</div>
                                    <div class="custom-select-option" data-value="Conversational">Conversational</div>
                                    <div class="custom-select-option" data-value="Technical">Technical</div>
                                    <div class="custom-select-option" data-value="Humorous">Humorous</div>
                                    <div class="custom-select-option" data-value="Inspirational">Inspirational</div>
                                    <div class="custom-select-option" data-value="Authoritative">Authoritative</div>
                                    <div class="custom-select-option" data-value="Empathetic">Empathetic</div>
                                </div>
                            </div>
                            <input type="hidden" name="tone" value="{{ old('tone', $aiTemplate->tone ?? '') }}">
                        </div>
                    </div>

                    <!-- Point of View -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Point of View
                        </label>
                        <div class="custom-select-wrapper" data-name="point_of_view" data-value="{{ old('point_of_view', $aiTemplate->point_of_view ?? '') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Point of View</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="First Person">First Person (I, We)</div>
                                    <div class="custom-select-option" data-value="Second Person">Second Person (You)</div>
                                    <div class="custom-select-option" data-value="Third Person">Third Person (He, She, They)</div>
                                    <div class="custom-select-option" data-value="Mixed">Mixed</div>
                                </div>
                            </div>
                            <input type="hidden" name="point_of_view" value="{{ old('point_of_view', $aiTemplate->point_of_view ?? '') }}">
                        </div>
                    </div>

                    <!-- Target Country -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">
                            Target Country
                        </label>
                        <div class="custom-select-wrapper" data-name="target_country" data-value="{{ old('target_country', $aiTemplate->target_country ?? '') }}">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">Select Target Country</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Search or type custom..." class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options">
                                    @foreach($countries ?? [] as $country)
                                        <div class="custom-select-option" data-value="{{ $country['code'] }}">
                                            <span class="mr-2">{{ $country['flag'] }}</span>{{ $country['name'] }} ({{ $country['code'] }})
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="target_country" value="{{ old('target_country', $aiTemplate->target_country ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-6 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition">
                        {{ isset($aiTemplate) ? 'Update Template' : 'Create Template' }}
                    </button>
                    <a href="{{ route('ai-templates.index') }}"
                       class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.custom-select-wrapper {
    position: relative;
}

.custom-select-trigger {
    width: 100%;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.custom-select-trigger:hover {
    border-color: #9ca3af;
}

.custom-select-wrapper.active .custom-select-trigger {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.custom-select-value {
    color: #111827;
    font-size: 0.875rem;
}

.custom-select-value.placeholder {
    color: #9ca3af;
}

.custom-select-dropdown {
    position: absolute;
    top: calc(100% + 0.25rem);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    display: none;
    max-height: 300px;
    overflow: hidden;
}

.custom-select-wrapper.active .custom-select-dropdown {
    display: block;
}

.custom-select-search {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.custom-select-search-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    outline: none;
}

.custom-select-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.custom-select-options {
    max-height: 200px;
    overflow-y: auto;
}

.custom-select-option {
    padding: 0.75rem 1rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
    transition: background-color 0.15s;
}

.custom-select-option:hover {
    background-color: #f3f4f6;
}

.custom-select-option.selected {
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 500;
}
</style>

<script>
// Models by provider data from backend
const modelsByProvider = @json($modelsByProvider ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all custom select dropdowns
    const selects = document.querySelectorAll('.custom-select-wrapper');

    selects.forEach(select => {
        const trigger = select.querySelector('.custom-select-trigger');
        const valueDisplay = select.querySelector('.custom-select-value');
        const searchInput = select.querySelector('.custom-select-search-input');
        const options = select.querySelectorAll('.custom-select-option');
        const hiddenInput = select.querySelector('input[type="hidden"]');
        const initialValue = select.dataset.value;

        // Set initial value if exists
        if (initialValue) {
            const matchedOption = Array.from(options).find(opt => opt.dataset.value === initialValue);
            if (matchedOption) {
                valueDisplay.textContent = matchedOption.textContent;
                valueDisplay.classList.remove('placeholder');
                matchedOption.classList.add('selected');
            } else {
                // Custom value not in list
                valueDisplay.textContent = initialValue;
                valueDisplay.classList.remove('placeholder');
            }
        }

        // Toggle dropdown
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();

            // Close other dropdowns
            selects.forEach(s => {
                if (s !== select) s.classList.remove('active');
            });

            select.classList.toggle('active');

            if (select.classList.contains('active')) {
                searchInput.focus();
            }
        });

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // Allow custom input on Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const customValue = this.value.trim();

                if (customValue) {
                    hiddenInput.value = customValue;
                    valueDisplay.textContent = customValue;
                    valueDisplay.classList.remove('placeholder');

                    // Deselect all options
                    options.forEach(opt => opt.classList.remove('selected'));

                    select.classList.remove('active');
                }
            }
        });

        // Select option
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                const text = this.textContent;

                hiddenInput.value = value;
                valueDisplay.textContent = text;
                valueDisplay.classList.remove('placeholder');

                // Update selected state
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                select.classList.remove('active');
                searchInput.value = '';

                // Reset options visibility
                options.forEach(opt => opt.style.display = 'block');
            });
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        selects.forEach(select => select.classList.remove('active'));
    });

    // ========================================
    // Cascading Dropdown: Provider → Model
    // ========================================
    const providerSelect = document.getElementById('providerSelect');
    const modelSelect = document.getElementById('modelSelect');
    const modelOptions = document.getElementById('modelOptions');
    const providerHiddenInput = providerSelect.querySelector('input[type="hidden"]');
    const modelHiddenInput = modelSelect.querySelector('input[type="hidden"]');
    const modelValueDisplay = modelSelect.querySelector('.custom-select-value');
    const modelSearchInput = modelSelect.querySelector('.custom-select-search-input');

    // Function to populate model dropdown based on selected provider
    function populateModelDropdown(provider) {
        if (!provider || !modelsByProvider[provider]) {
            modelOptions.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 text-center">Please select a provider first</div>';
            modelValueDisplay.textContent = 'Select AI Provider first';
            modelValueDisplay.classList.add('placeholder');
            modelHiddenInput.value = '';
            return;
        }

        const models = modelsByProvider[provider];
        let optionsHtml = '';

        Object.keys(models).forEach(modelKey => {
            optionsHtml += `<div class="custom-select-option" data-value="${modelKey}">${models[modelKey]}</div>`;
        });

        modelOptions.innerHTML = optionsHtml;
        modelValueDisplay.textContent = 'Select AI Model';
        modelValueDisplay.classList.add('placeholder');

        // Re-attach click events to new options
        const newModelOptions = modelOptions.querySelectorAll('.custom-select-option');
        newModelOptions.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.dataset.value;
                const text = this.textContent;

                modelHiddenInput.value = value;
                modelValueDisplay.textContent = text;
                modelValueDisplay.classList.remove('placeholder');

                // Update selected state
                newModelOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                modelSelect.classList.remove('active');
                modelSearchInput.value = '';

                // Reset options visibility
                newModelOptions.forEach(opt => opt.style.display = 'block');
            });
        });

        // Re-attach search functionality
        modelSearchInput.removeEventListener('input', modelSearchHandler);
        modelSearchInput.addEventListener('input', modelSearchHandler);
    }

    // Model search handler
    function modelSearchHandler() {
        const searchTerm = this.value.toLowerCase();
        const options = modelOptions.querySelectorAll('.custom-select-option');

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    }

    // Listen for provider change
    providerSelect.addEventListener('change', function() {
        const selectedProvider = providerHiddenInput.value;
        populateModelDropdown(selectedProvider);
    });

    // Listen for provider selection via custom dropdown
    const providerOptions = providerSelect.querySelectorAll('.custom-select-option');
    providerOptions.forEach(option => {
        option.addEventListener('click', function() {
            const provider = this.dataset.value;
            setTimeout(() => {
                populateModelDropdown(provider);
            }, 100);
        });
    });

    // Initialize on page load (for edit mode)
    const initialProvider = providerHiddenInput.value || '{{ old("ai_provider", $aiTemplate->ai_provider ?? "") }}';
    const initialModel = modelHiddenInput.value || '{{ old("ai_model", $aiTemplate->ai_model ?? "") }}';

    if (initialProvider) {
        populateModelDropdown(initialProvider);

        // Set initial model if exists
        if (initialModel) {
            setTimeout(() => {
                const matchedModelOption = Array.from(modelOptions.querySelectorAll('.custom-select-option')).find(opt => opt.dataset.value === initialModel);
                if (matchedModelOption) {
                    modelValueDisplay.textContent = matchedModelOption.textContent;
                    modelValueDisplay.classList.remove('placeholder');
                    matchedModelOption.classList.add('selected');
                }
            }, 200);
        }
    }
});

// Intercept form submission for modal
const form = document.getElementById('templateForm');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('modal', '1');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Notify parent window
                if (window.parent !== window) {
                    window.parent.postMessage({ type: 'template-created', template: data.template }, '*');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create template');
        });
    });
}
</script>

</body>
</html>
