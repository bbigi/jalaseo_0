@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-32">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('insights.news.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            {{ __('insights.news.results.back') }}
        </a>

        <div class="bg-white rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ __('insights.news.results.title') }}</h1>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-globe-alt class="w-4 h-4" />
                    {{ $search['source'] }}
                </span>
                @if($search['theme'])
                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-tag class="w-4 h-4" />
                    {{ $search['theme'] }}
                </span>
                @endif
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-clock class="w-4 h-4" />
                    {{ ucfirst($search['time_range']) }}
                </span>
                <span class="text-gray-600">
                    {{ trans_choice('insights.news.results.found_count', $search['results_count'], ['count' => $search['results_count']]) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Results List (Full Width Cards) -->
    @if(count($results) > 0)
    <div class="bg-white rounded-lg overflow-hidden">
        <div class="divide-y divide-gray-200">
            @foreach($results as $result)
            <div class="p-5 hover:bg-gray-50 transition">
                <div class="flex items-start gap-4">
                    <!-- Checkbox -->
                    <div class="flex-shrink-0 pt-1">
                        <input type="checkbox"
                               class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 news-checkbox"
                               value="{{ $result['id'] }}"
                               data-title="{{ addslashes($result['title']) }}"
                               data-snippet="{{ addslashes($result['snippet'] ?? '') }}"
                               data-url="{{ $result['url'] }}">
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <a href="{{ $result['url'] }}" target="_blank" class="hover:text-blue-600 hover:underline">
                                    {{ $result['title'] }}
                                </a>
                            </h3>
                            <span class="flex-shrink-0 bg-green-50 text-green-700 text-xs font-medium px-2 py-1 rounded">
                                #{{ $result['position'] }}
                            </span>
                        </div>

                        @if($result['snippet'])
                        <p class="text-sm text-gray-600 mb-3">{{ $result['snippet'] }}</p>
                        @endif

                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-4 h-4" />
                                {{ $result['publish_date'] ? date('d M Y', strtotime($result['publish_date'])) : __('insights.news.results.publish_date_na') }}
                            </span>
                            <a href="{{ $result['url'] }}" target="_blank" class="text-blue-600 hover:text-blue-700 hover:underline">
                                {{ __('insights.news.results.view_source') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg p-12 text-center">
        <x-heroicon-o-magnifying-glass class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('insights.news.results.empty_title') }}</h3>
        <p class="text-gray-600 mb-6">{{ __('insights.news.results.empty_subtitle') }}</p>
        <a href="{{ route('insights.news.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            {{ __('insights.news.results.cta_try_again') }}
        </a>
    </div>
    @endif
</div>

<!-- Sticky Bottom Action Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">
                        {{ __('insights.news.results.selection.selected') }}: <span id="selectedCount" class="font-semibold text-gray-900">0</span>
                    </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="cancelSelection()"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium rounded-lg transition">
                    {{ __('insights.news.results.selection.cancel') }}
                </button>
                <button type="button"
                        onclick="saveAsInsights()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        id="saveInsightBtn"
                        disabled>
                    <x-heroicon-o-light-bulb class="w-5 h-5" />
                    {{ __('insights.news.results.selection.save_insight') }}
                </button>
                <button type="button"
                        onclick="showArticleTypeModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                        id="generateBtn"
                        disabled>
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                    {{ __('insights.news.results.selection.generate') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template Selection Modal -->
<div id="articleTypeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('insights.news.results.template_modal.title') }}</h3>

                @if($templates->count() > 0)
                <div class="mb-6">
                    <div class="custom-select-wrapper" id="templateSelectWrapper">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">Select template...</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="Search template..." class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options" id="templateSelectOptions">
                                <div class="custom-select-option" data-value="__create__">{{ __('insights.news.results.template_modal.create_new') }}</div>
                                @foreach($templates as $template)
                                    <div class="custom-select-option"
                                         data-value="{{ $template->id }}"
                                         data-name="{{ $template->name }}">
                                        <div class="font-semibold text-gray-900 text-sm">{{ $template->name }}</div>
                                        <div class="text-xs text-gray-600 mt-0.5">
                                            <span class="inline-block mr-2">{{ ucfirst($template->tone ?? 'professional') }}</span>
                                            <span class="inline-block mr-2">• {{ $template->article_size ?? 'medium' }}</span>
                                            <span class="inline-block">• {{ strtoupper($template->language ?? 'id') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="hidden" id="templateRadios">
                        @foreach($templates as $template)
                            <input type="radio" name="templateId" value="{{ $template->id }}" {{ $loop->first ? 'checked' : '' }}>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">{{ __('insights.news.results.template_modal.no_template') }} <a href="{{ route('ai-templates.create') }}" class="underline font-medium">{{ __('insights.news.results.template_modal.create_template') }}</a></p>
                    <button type="button" onclick="openTemplateModal()"
                            class="mt-3 inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                        <x-heroicon-o-plus-small class="w-5 h-5" />
                        {{ __('insights.news.results.template_modal.create_template') }}
                    </button>
                </div>
                @endif

                <div class="flex gap-3">
                    <button type="button" onclick="closeArticleTypeModal()"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        {{ __('insights.news.results.template_modal.cancel') }}
                    </button>
                    <button type="button" onclick="confirmGenerateArticles()"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            {{ $templates->count() == 0 ? 'disabled' : '' }}>
                        {{ __('insights.news.results.template_modal.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal (iframe) -->
<div id="templateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900">Create New Template</h3>
                <button type="button" onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <iframe src="{{ route('ai-templates.create-modal') }}"
                    class="w-full min-h-[80vh] h-[80vh] border-0"></iframe>
        </div>
    </div>
</div>

<style>
.custom-select-wrapper { position: relative; }
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
.custom-select-wrapper.active .custom-select-trigger {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
    z-index: 60;
    display: none;
    max-height: 320px;
    overflow: hidden;
}
.custom-select-wrapper.active .custom-select-dropdown { display: block; }
.custom-select-search { padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.custom-select-search-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    outline: none;
}
.custom-select-options { max-height: 240px; overflow-y: auto; }
.custom-select-option { padding: 0.75rem 1rem; cursor: pointer; transition: background-color 0.15s; }
.custom-select-option:hover { background-color: #f3f4f6; }
.custom-select-option.selected { background-color: #eff6ff; }
</style>

<script>
// Track selected items
let selectedItems = [];

function openTemplateModal() {
    document.getElementById('templateModal')?.classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateModal')?.classList.add('hidden');
}

function initTemplateSelect() {
    const wrapper = document.getElementById('templateSelectWrapper');
    if (!wrapper) return;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const valueDisplay = wrapper.querySelector('.custom-select-value');
    const searchInput = wrapper.querySelector('.custom-select-search-input');
    const options = wrapper.querySelectorAll('.custom-select-option');
    const radiosContainer = document.getElementById('templateRadios');
    const radios = radiosContainer ? radiosContainer.querySelectorAll('input[type=\"radio\"][name=\"templateId\"]') : [];

    const setRadioChecked = (value) => {
        if (!radios || !radios.length) return;
        radios.forEach(r => { r.checked = (r.value === String(value)); });
    };

    const checked = Array.from(radios).find(r => r.checked);
    if (checked) {
        const selectedOption = wrapper.querySelector(`.custom-select-option[data-value=\"${checked.value}\"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.dataset.name || selectedOption.textContent.trim();
            options.forEach(o => o.classList.remove('selected'));
            selectedOption.classList.add('selected');
        }
    }

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        wrapper.classList.toggle('active');
        if (wrapper.classList.contains('active')) {
            searchInput.focus();
        }
    });

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        options.forEach(option => {
            option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
        });
    });

    options.forEach(option => {
        option.addEventListener('click', () => {
            const value = option.dataset.value;
            if (value === '__create__') {
                wrapper.classList.remove('active');
                searchInput.value = '';
                options.forEach(o => o.style.display = 'block');
                openTemplateModal();
                return;
            }

            valueDisplay.textContent = option.dataset.name || option.textContent.trim();
            options.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
            setRadioChecked(value);
            wrapper.classList.remove('active');
            searchInput.value = '';
            options.forEach(o => o.style.display = 'block');
        });
    });

    document.addEventListener('click', () => wrapper.classList.remove('active'));
}

// Update selection count and button states
function updateSelection() {
    const checkboxes = document.querySelectorAll('.news-checkbox:checked');
    selectedItems = Array.from(checkboxes).map(cb => ({
        id: cb.value,
        title: cb.dataset.title,
        snippet: cb.dataset.snippet,
        url: cb.dataset.url
    }));

    document.getElementById('selectedCount').textContent = selectedItems.length;

    const saveBtn = document.getElementById('saveInsightBtn');
    const generateBtn = document.getElementById('generateBtn');

    if (selectedItems.length > 0) {
        saveBtn.disabled = false;
        generateBtn.disabled = false;
    } else {
        saveBtn.disabled = true;
        generateBtn.disabled = true;
    }
}

// Add event listeners to all checkboxes
document.addEventListener('DOMContentLoaded', function() {
    initTemplateSelect();
    document.querySelectorAll('.news-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
});

// Listen for template-created event from iframe and auto-select it
window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) {
        return;
    }

    const payload = event.data || {};
    if (payload.type !== 'template-created' || !payload.template) {
        return;
    }

    const optionsContainer = document.getElementById('templateSelectOptions');
    const radiosContainer = document.getElementById('templateRadios');
    const wrapper = document.getElementById('templateSelectWrapper');
    if (!optionsContainer || !radiosContainer || !wrapper) {
        closeTemplateModal();
        return;
    }

    const option = document.createElement('div');
    option.className = 'custom-select-option';
    option.dataset.value = payload.template.id;
    option.dataset.name = payload.template.name;
    option.innerHTML = `<div class=\"font-semibold text-gray-900 text-sm\">${payload.template.name}</div>`;
    optionsContainer.appendChild(option);

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'templateId';
    radio.value = payload.template.id;
    radiosContainer.appendChild(radio);

    initTemplateSelect();

    // select newly created
    radiosContainer.querySelectorAll('input[type=\"radio\"][name=\"templateId\"]').forEach(r => {
        r.checked = (r.value === String(payload.template.id));
    });
    wrapper.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
    option.classList.add('selected');
    wrapper.querySelector('.custom-select-value').textContent = payload.template.name;

    closeTemplateModal();
});

// Cancel selection
function cancelSelection() {
    document.querySelectorAll('.news-checkbox').forEach(cb => cb.checked = false);
    updateSelection();
}

// Save as insights
function saveAsInsights() {
    if (selectedItems.length === 0) {
        alert('Pilih minimal 1 berita');
        return;
    }

    const saveBtn = document.getElementById('saveInsightBtn');
    const originalText = saveBtn.innerHTML;

    // Disable button and show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';

    // Send AJAX request
    fetch('{{ route('insights.news.save-as-insights') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            items: selectedItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Redirect to insights index
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Terjadi kesalahan saat menyimpan insight');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan insight');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Show article type modal
function showArticleTypeModal() {
    if (selectedItems.length === 0) {
        alert('Pilih minimal 1 berita');
        return;
    }
    document.getElementById('articleTypeModal').classList.remove('hidden');
}

// Close article type modal
function closeArticleTypeModal() {
    document.getElementById('articleTypeModal').classList.add('hidden');
}

// Confirm and generate articles
async function confirmGenerateArticles() {
    const templateId = document.querySelector('input[name="templateId"]:checked')?.value;
    if (!templateId) {
        alert('Pilih template terlebih dahulu');
        return;
    }
    closeArticleTypeModal();

    // For single item, redirect to progress page
    if (selectedItems.length === 1) {
        const item = selectedItems[0];
        const params = new URLSearchParams({
            result_id: item.id,
            template_id: templateId,
            title: item.title,
            url: item.url
        });
        window.location.href = '{{ route("insights.news.generate-progress") }}?' + params.toString();
        return;
    }

    // For multiple items, continue with bulk generation
    const generateBtn = document.getElementById('generateBtn');
    const originalText = generateBtn.innerHTML;

    // Disable button
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

    const successfulPosts = [];
    const failedItems = [];

    // Process each item
    for (let i = 0; i < selectedItems.length; i++) {
        const item = selectedItems[i];

        try {
            const response = await fetch('{{ route('insights.news.generate-article') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    result_id: item.id,
                    title: item.title,
                    url: item.url,
                    template_id: templateId
                })
            });

            const data = await response.json();

            if (data.success) {
                successfulPosts.push(data.post_id);
            } else {
                failedItems.push({ title: item.title, error: data.message });
            }
        } catch (error) {
            console.error('Error generating article:', error);
            failedItems.push({ title: item.title, error: error.message });
        }

        // Small delay between requests
        if (i < selectedItems.length - 1) {
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }

    // Show results
    let message = `${successfulPosts.length} artikel berhasil digenerate`;
    if (failedItems.length > 0) {
        message += `, ${failedItems.length} gagal`;
    }

    alert(message);

    // Redirect based on count
    if (successfulPosts.length === 1) {
        // Single article → edit page
        window.location.href = '{{ url('/posts') }}/' + successfulPosts[0] + '/edit';
    } else if (successfulPosts.length > 1) {
        // Multiple articles → bulk review
        const postIds = successfulPosts.join(',');
        window.location.href = '{{ route('posts.bulk-review') }}?ids=' + postIds;
    } else {
        // No success
        generateBtn.disabled = false;
        generateBtn.innerHTML = originalText;
    }
}
</script>
@endsection
