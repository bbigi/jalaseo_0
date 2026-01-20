@extends('layouts.dashboard')

@section('title', __('generate.title') . ' - AneWP')

@section('content')
<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('generate.title') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('generate.subtitle') }}</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form id="generateForm">
                @csrf

                <!-- Website Selector -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('generate.form.website') }} <span class="text-gray-400">{{ __('generate.form.website_optional') }}</span>
                    </label>
                    <div class="custom-select-wrapper" data-name="website_id" data-value="{{ $context['website_id'] ?? '' }}">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('generate.form.website_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('generate.form.website_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @foreach($websites as $site)
                                    <div class="custom-select-option" data-value="{{ $site->id }}">{{ $site->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="website_id" id="websiteId" value="{{ $context['website_id'] ?? '' }}">
                    </div>
                </div>

                <!-- Template Selector -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('generate.form.template') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select-wrapper" data-name="template_id" data-value="{{ $context['template_id'] ?? '' }}" id="templateSelect">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('generate.form.template_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('generate.form.template_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                <div class="custom-select-option" data-value="__create__">
                                    {{ __('generate.form.template_create') }}
                                </div>
                                @foreach($templates as $tpl)
                                    <div class="custom-select-option" data-value="{{ $tpl->id }}">
                                        {{ $tpl->name }} - {{ $tpl->description }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="template_id" value="">
                    </div>
                </div>

                <!-- Generation Source -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('generate.form.generation_source') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select-wrapper" data-name="generation_source" data-value="{{ $context['generation_source'] ?? 'title' }}" id="generationSourceSelect">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('generate.form.generation_source_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('generate.form.generation_source_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                <div class="custom-select-option" data-value="title">{{ __('generate.form.generation_options.title') }}</div>
                                <div class="custom-select-option" data-value="reference">{{ __('generate.form.generation_options.reference') }}</div>
                                <div class="custom-select-option" data-value="text">{{ __('generate.form.generation_options.text') }}</div>
                                <div class="custom-select-option" data-value="insight">{{ __('generate.form.generation_options.insight') }}</div>
                            </div>
                        </div>
                        <input type="hidden" name="generation_source" id="generationSource" value="title">
                    </div>
                </div>

                <!-- Title Input -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('generate.form.topic') }} <span class="text-red-500">*</span>
                        </label>
                        <button type="button" id="openReferenceModal"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                            {{ __('generate.form.reference_button') }}
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="title" id="titleInput" required
                               value="{{ $context['title'] ?? '' }}"
                               placeholder="{{ __('generate.form.topic_placeholder') }}"
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div id="keywordSuggestions" class="autocomplete-dropdown hidden"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ __('generate.form.autocomplete_help') }}</p>
                </div>

                <!-- Reference Fields (Conditional) -->
                <div id="referenceFields" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('generate.form.reference_topic') }}</label>
                        <input type="text" name="ref_topic" id="refTopic"
                               value="{{ $context['description'] ?? '' }}"
                               placeholder="{{ __('generate.form.reference_topic_placeholder') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('generate.form.reference_description') }}</label>
                        <textarea name="ref_description" id="refDescription" rows="3"
                                  placeholder="{{ __('generate.form.reference_description_placeholder') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('generate.form.reference_url') }}</label>
                        <input type="url" name="ref_url" id="refUrl"
                               value="{{ $context['url'] ?? '' }}"
                               placeholder="{{ __('generate.form.reference_url_placeholder') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Insight Field (Conditional) -->
                <div id="insightField" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('generate.form.insight_select') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select-wrapper" data-name="insight_id" data-value="{{ $context['insight_id'] ?? '' }}" id="insightSelect">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('generate.form.insight_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('generate.form.insight_search') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options">
                                @forelse($insights as $insight)
                                    <div class="custom-select-option"
                                         data-value="{{ $insight->id }}"
                                         data-topic="{{ $insight->topic }}"
                                         data-description="{{ $insight->description }}"
                                         data-url="{{ $insight->source_url }}">
                                        {{ $insight->topic }}
                                    </div>
                                @empty
                                    <div class="custom-select-option" data-value="" data-empty="1">{{ __('generate.form.insight_empty') }}</div>
                                @endforelse
                            </div>
                        </div>
                        <input type="hidden" name="insight_id" id="insightId" value="">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ __('generate.form.insight_help') }}</p>
                </div>

                <!-- Text Field (Conditional) -->
                <div id="textField" class="hidden mb-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('generate.form.text_reference') }} <span class="text-red-500">*</span></label>
                        <textarea name="ref_text" rows="6"
                                  placeholder="{{ __('generate.form.text_reference_placeholder') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('generate.form.text_source_name') }}</label>
                        <input type="text" name="ref_source_name"
                               placeholder="{{ __('generate.form.text_source_name_placeholder') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold transition flex items-center justify-center gap-2">
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                    {{ __('generate.form.submit') }}
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
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

.autocomplete-dropdown {
    position: absolute;
    top: calc(100% + 0.25rem);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 40;
    max-height: 220px;
    overflow-y: auto;
}

.autocomplete-item {
    padding: 0.6rem 0.85rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.autocomplete-item:hover,
.autocomplete-item.active {
    background-color: #f3f4f6;
}

.autocomplete-empty {
    padding: 0.6rem 0.85rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.autocomplete-label {
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 0.125rem 0.4rem;
    border-radius: 999px;
    background: #e5e7eb;
    color: #4b5563;
}

.autocomplete-label.saved {
    background: #ecfdf3;
    color: #047857;
}

.autocomplete-label.live {
    background: #eef2ff;
    color: #4338ca;
}

.reference-result {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.reference-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}

.reference-meta {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
    word-break: break-all;
}

.reference-snippet {
    margin-top: 0.35rem;
    font-size: 0.8125rem;
    color: #4b5563;
}

.reference-empty,
.reference-loading {
    padding: 0.75rem;
    font-size: 0.8125rem;
    color: #6b7280;
    text-align: center;
}

.reference-modal-body {
    max-height: 80vh;
    overflow: hidden;
}

.reference-results {
    max-height: 55vh;
    overflow-y: auto;
    padding-right: 0.25rem;
}
</style>
@endpush

<!-- Create Template Modal -->
<div id="templateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900">Create New Template</h3>
                <button id="closeTemplateModal" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <iframe id="templateModalFrame"
                    src="{{ route('ai-templates.create-modal') }}"
                    class="w-full min-h-[80vh] h-[80vh] border-0"></iframe>
        </div>
    </div>
</div>

<!-- Reference Modal -->
<div id="referenceModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Cari Referensi</h3>
                    <p class="text-xs text-gray-500">Pilih satu hasil SERP sebagai sumber referensi.</p>
                </div>
                <button id="closeReferenceModal" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="p-4 reference-modal-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input type="text"
                           id="referenceSearchInput"
                           placeholder="Cari referensi berdasarkan keyword..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" id="referenceSearchButton"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                        Search
                    </button>
                </div>
                <div id="referenceResults" class="reference-results mt-4 space-y-3"></div>
                <div id="referencePagination" class="mt-3 flex items-center justify-between text-sm text-gray-500 hidden">
                    <button type="button" id="referencePrev"
                            class="reference-pagination-btn text-blue-600 hover:text-blue-700 disabled:text-gray-400 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <span id="referencePageInfo">Page 1 of 1</span>
                    <button type="button" id="referenceNext"
                            class="reference-pagination-btn text-blue-600 hover:text-blue-700 disabled:text-gray-400 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let currentJobId = null;

function bindSelectOption(select, option) {
    const valueDisplay = select.querySelector('.custom-select-value');
    const searchInput = select.querySelector('.custom-select-search-input');
    const options = select.querySelectorAll('.custom-select-option');
    const hiddenInput = select.querySelector('input[type="hidden"]');

    option.addEventListener('click', function() {
        const value = this.dataset.value;
        if (select.id === 'templateSelect' && value === '__create__') {
            select.classList.remove('active');
            openTemplateModal();
            return;
        }

        hiddenInput.value = value;
        valueDisplay.textContent = this.textContent;
        valueDisplay.classList.remove('placeholder');
        options.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        select.classList.remove('active');
        searchInput.value = '';
        options.forEach(opt => opt.style.display = 'block');

        hiddenInput.dispatchEvent(new Event('change'));

        if (select.id === 'insightSelect' && value) {
            const topic = this.dataset.topic || '';
            const titleInput = document.getElementById('titleInput');
            if (titleInput && topic) {
                titleInput.value = topic;
            }
        }

    });
}

function initCustomSelects() {
    const selects = document.querySelectorAll('.custom-select-wrapper');

    selects.forEach(select => {
        const trigger = select.querySelector('.custom-select-trigger');
        const valueDisplay = select.querySelector('.custom-select-value');
        const searchInput = select.querySelector('.custom-select-search-input');
        const options = select.querySelectorAll('.custom-select-option');
        const hiddenInput = select.querySelector('input[type="hidden"]');
        const initialValue = select.dataset.value;

        if (initialValue) {
            const matchedOption = Array.from(options).find(opt => opt.dataset.value === initialValue);
            if (matchedOption) {
                valueDisplay.textContent = matchedOption.textContent;
                valueDisplay.classList.remove('placeholder');
                matchedOption.classList.add('selected');
            } else {
                valueDisplay.textContent = initialValue;
                valueDisplay.classList.remove('placeholder');
            }
            hiddenInput.value = initialValue;
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            selects.forEach(s => { if (s !== select) s.classList.remove('active'); });
            select.classList.toggle('active');
            if (select.classList.contains('active')) {
                searchInput.focus();
            }
        });

        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            options.forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
            });
        });

        options.forEach(option => bindSelectOption(select, option));
    });

    document.addEventListener('click', () => {
        selects.forEach(select => select.classList.remove('active'));
    });
}

function openTemplateModal() {
    document.getElementById('templateModal').classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function openReferenceModal() {
    document.getElementById('referenceModal').classList.remove('hidden');
}

function closeReferenceModal() {
    document.getElementById('referenceModal').classList.add('hidden');
}

function setCustomSelectValue(select, value) {
    const option = select.querySelector(`.custom-select-option[data-value="${value}"]`);
    if (option) {
        option.click();
        return;
    }

    const hiddenInput = select.querySelector('input[type="hidden"]');
    const valueDisplay = select.querySelector('.custom-select-value');
    hiddenInput.value = value;
    valueDisplay.textContent = value;
    valueDisplay.classList.remove('placeholder');
    hiddenInput.dispatchEvent(new Event('change'));
}

// Toggle conditional fields based on generation source
document.addEventListener('DOMContentLoaded', function() {
    initCustomSelects();

    const generationSourceInput = document.getElementById('generationSource');
    const refFields = document.getElementById('referenceFields');
    const insightField = document.getElementById('insightField');
    const textField = document.getElementById('textField');
    const websiteInput = document.getElementById('websiteId');
    const generationSourceSelect = document.getElementById('generationSourceSelect');
    const keywordSuggestions = document.getElementById('keywordSuggestions');
    const titleInput = document.getElementById('titleInput');
    const openReferenceModalButton = document.getElementById('openReferenceModal');
    const referenceModal = document.getElementById('referenceModal');
    const referenceSearchInput = document.getElementById('referenceSearchInput');
    const referenceSearchButton = document.getElementById('referenceSearchButton');
    const referenceResults = document.getElementById('referenceResults');
    const referencePagination = document.getElementById('referencePagination');
    const referencePrev = document.getElementById('referencePrev');
    const referenceNext = document.getElementById('referenceNext');
    const referencePageInfo = document.getElementById('referencePageInfo');
    const refTopicInput = document.getElementById('refTopic');
    const refDescriptionInput = document.getElementById('refDescription');
    const refUrlInput = document.getElementById('refUrl');
    const closeReferenceModalButton = document.getElementById('closeReferenceModal');
    let autocompleteTimer = null;
    let autocompleteCounter = 0;
    let referenceResultsData = [];
    let referencePage = 1;
    let referenceKeyword = '';
    const referencePageSize = 10;

    const toggleFields = () => {
        const source = generationSourceInput.value;
        refFields.classList.toggle('hidden', source !== 'reference');
        textField.classList.toggle('hidden', source !== 'text');
        insightField.classList.toggle('hidden', source !== 'insight');
    };

    generationSourceInput.addEventListener('change', toggleFields);
    toggleFields();

    const closeTemplateModalButton = document.getElementById('closeTemplateModal');
    closeTemplateModalButton.addEventListener('click', closeTemplateModal);
    
    // Prefill from query params (e.g., FAQ redirect)
    const params = new URLSearchParams(window.location.search);
    const prefillSource = params.get('generation_source');
    const prefillRefText = params.get('ref_text');
    const prefillRefTopic = params.get('ref_topic');
    const prefillRefUrl = params.get('ref_url');
    if (prefillSource && generationSourceSelect) {
        setCustomSelectValue(generationSourceSelect, prefillSource);
    }
    if (prefillRefText && document.querySelector('textarea[name="ref_text"]')) {
        document.querySelector('textarea[name="ref_text"]').value = prefillRefText;
    }
    if (prefillRefTopic && refTopicInput) {
        refTopicInput.value = prefillRefTopic;
    }
    if (prefillRefUrl && refUrlInput) {
        refUrlInput.value = prefillRefUrl;
    }
    // If prefill source is text/reference/insight, ensure fields visible
    toggleFields();

    const renderKeywordSuggestions = (query, suggestions = []) => {
        keywordSuggestions.innerHTML = '';
        if (!query) {
            keywordSuggestions.classList.add('hidden');
            return;
        }

        if (!suggestions.length) {
            keywordSuggestions.innerHTML = '<div class="autocomplete-empty">No keywords found</div>';
            keywordSuggestions.classList.remove('hidden');
            return;
        }

        suggestions.forEach(item => {
            const div = document.createElement('div');
            div.className = 'autocomplete-item';
            const keyword = item.value || '';
            const label = document.createElement('span');
            label.className = `autocomplete-label ${item.type || 'live'}`;
            label.textContent = item.type === 'saved' ? 'Saved' : 'Live';

            const text = document.createElement('span');
            text.textContent = keyword;
            div.appendChild(text);
            div.appendChild(label);

            div.addEventListener('click', () => {
                titleInput.value = keyword;
                keywordSuggestions.classList.add('hidden');
            });
            keywordSuggestions.appendChild(div);
        });

        keywordSuggestions.classList.remove('hidden');
    };

    const fetchAutocomplete = async (query) => {
        const params = new URLSearchParams({ q: query });
        if (websiteInput && websiteInput.value) {
            params.append('website_id', websiteInput.value);
        }
        try {
            const response = await fetch(`/generate/autocomplete?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!data.success) {
                return [];
            }
            return data.suggestions || [];
        } catch (error) {
            return [];
        }
    };

    titleInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        keywordSuggestions.classList.add('hidden');

        if (autocompleteTimer) {
            clearTimeout(autocompleteTimer);
        }

        if (query.length < 2) {
            return;
        }

        const requestId = ++autocompleteCounter;
        autocompleteTimer = setTimeout(async () => {
            const suggestions = await fetchAutocomplete(query);
            if (requestId !== autocompleteCounter) {
                return;
            }
            renderKeywordSuggestions(query, suggestions);
        }, 250);
    });

    titleInput.addEventListener('focus', (e) => {
        const query = e.target.value.trim();
        if (query.length < 2) {
            return;
        }

        fetchAutocomplete(query).then(suggestions => {
            renderKeywordSuggestions(query, suggestions);
        });
    });

    document.addEventListener('click', (e) => {
        if (!keywordSuggestions.contains(e.target) && e.target !== titleInput) {
            keywordSuggestions.classList.add('hidden');
        }
    });

    const updateReferencePagination = () => {
        const totalPages = Math.max(1, Math.ceil(referenceResultsData.length / referencePageSize));
        referencePage = Math.min(referencePage, totalPages);
        referencePageInfo.textContent = `Page ${referencePage} of ${totalPages}`;
        referencePrev.disabled = referencePage <= 1;
        referenceNext.disabled = referencePage >= totalPages;
        referencePagination.classList.toggle('hidden', referenceResultsData.length <= referencePageSize);
    };

    const renderReferenceResultsPage = () => {
        const keyword = referenceKeyword;
        referenceResults.innerHTML = '';
        if (!referenceResultsData.length) {
            referenceResults.innerHTML = '<div class="reference-empty">No references found.</div>';
            referencePagination.classList.add('hidden');
            return;
        }

        const start = (referencePage - 1) * referencePageSize;
        const pageItems = referenceResultsData.slice(start, start + referencePageSize);

        pageItems.forEach((result) => {
            const item = document.createElement('div');
            item.className = 'reference-result';

            const content = document.createElement('div');
            const title = document.createElement('div');
            title.className = 'reference-title';
            title.textContent = result.title || 'Untitled';

            const meta = document.createElement('div');
            meta.className = 'reference-meta';
            meta.textContent = result.domain || result.url || '';

            const snippet = document.createElement('div');
            snippet.className = 'reference-snippet';
            snippet.textContent = result.snippet || '';

            content.appendChild(title);
            content.appendChild(meta);
            if (result.snippet) {
                content.appendChild(snippet);
            }

            const action = document.createElement('button');
            action.type = 'button';
            action.className = 'text-xs font-semibold text-blue-600 hover:text-blue-700';
            action.textContent = 'Use';
            action.addEventListener('click', async () => {
                const topicValue = titleInput.value.trim() || keyword || result.title || '';
                if (refTopicInput) {
                    refTopicInput.value = topicValue;
                }
                if (refDescriptionInput) {
                    refDescriptionInput.value = result.snippet || result.title || '';
                }
                if (refUrlInput) {
                    refUrlInput.value = result.url || '';
                }

                if (generationSourceSelect) {
                    setCustomSelectValue(generationSourceSelect, 'reference');
                }

                try {
                    await fetch('/generate/references/select', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            keyword,
                            title: result.title || topicValue,
                            url: result.url || '',
                            snippet: result.snippet || '',
                        }),
                    });
                } catch (error) {
                    // Ignore cache errors
                }

                closeReferenceModal();
            });

            item.appendChild(content);
            item.appendChild(action);
            referenceResults.appendChild(item);
        });
        updateReferencePagination();
    };

    const setReferenceResults = (keyword, results = []) => {
        referenceKeyword = keyword;
        referenceResultsData = results || [];
        referencePage = 1;
        renderReferenceResultsPage();
    };

    const searchReferences = async (keyword) => {
        referenceResults.innerHTML = '<div class="reference-loading">Searching references...</div>';
        referencePagination.classList.add('hidden');
        try {
            const params = new URLSearchParams({ keyword });
            const response = await fetch(`/generate/references/search?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!data.success) {
                referenceResultsData = [];
                referenceKeyword = keyword;
                referenceResults.innerHTML = '<div class="reference-empty">Failed to load references.</div>';
                referencePagination.classList.add('hidden');
                return;
            }
            setReferenceResults(keyword, data.results || []);
        } catch (error) {
            referenceResultsData = [];
            referenceKeyword = keyword;
            referenceResults.innerHTML = '<div class="reference-empty">Failed to load references.</div>';
            referencePagination.classList.add('hidden');
        }
    };

    if (referencePrev) {
        referencePrev.addEventListener('click', () => {
            if (referencePage <= 1) {
                return;
            }
            referencePage -= 1;
            renderReferenceResultsPage();
        });
    }

    if (referenceNext) {
        referenceNext.addEventListener('click', () => {
            const totalPages = Math.max(1, Math.ceil(referenceResultsData.length / referencePageSize));
            if (referencePage >= totalPages) {
                return;
            }
            referencePage += 1;
            renderReferenceResultsPage();
        });
    }

    if (openReferenceModalButton) {
        openReferenceModalButton.addEventListener('click', () => {
            openReferenceModal();
            const keyword = titleInput.value.trim();
            if (keyword && referenceSearchInput) {
                referenceSearchInput.value = keyword;
                searchReferences(keyword);
            } else {
                referenceResultsData = [];
                referenceKeyword = '';
                referenceResults.innerHTML = '';
                referencePagination.classList.add('hidden');
            }
            if (referenceSearchInput) {
                referenceSearchInput.focus();
            }
        });
    }

    if (closeReferenceModalButton) {
        closeReferenceModalButton.addEventListener('click', closeReferenceModal);
    }

    if (referenceSearchButton) {
        referenceSearchButton.addEventListener('click', () => {
            const keyword = referenceSearchInput.value.trim();
            if (!keyword) {
                referenceResults.innerHTML = '<div class="reference-empty">Enter a keyword first.</div>';
                return;
            }
            searchReferences(keyword);
        });
    }

    if (referenceSearchInput) {
        referenceSearchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                referenceSearchButton.click();
            }
        });
    }
});

window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) {
        return;
    }

    const payload = event.data || {};
    if (payload.type !== 'template-created' || !payload.template) {
        return;
    }

    const templateSelect = document.getElementById('templateSelect');
    const optionsContainer = templateSelect.querySelector('.custom-select-options');
    const hiddenInput = templateSelect.querySelector('input[type="hidden"]');
    const valueDisplay = templateSelect.querySelector('.custom-select-value');

    const option = document.createElement('div');
    option.className = 'custom-select-option';
    option.dataset.value = payload.template.id;
    option.textContent = payload.template.name;
    optionsContainer.appendChild(option);
    bindSelectOption(templateSelect, option);

    hiddenInput.value = payload.template.id;
    valueDisplay.textContent = payload.template.name;
    valueDisplay.classList.remove('placeholder');
    optionsContainer.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
    option.classList.add('selected');

    closeTemplateModal();
});

// Handle form submission
document.getElementById('generateForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    try {
        // Submit to backend
        const response = await fetch('/generate/article', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
            currentJobId = result.job_id;
            const params = new URLSearchParams({
                job_id: result.job_id,
                template_id: data.template_id,
                title: data.title,
            });
            window.location.href = `/generate/progress-page?${params.toString()}`;
        } else {
            alert('Failed to start generation: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Submit error:', error);
        alert('Failed to submit: ' + error.message);
    }
});
</script>
@endpush
@endsection
