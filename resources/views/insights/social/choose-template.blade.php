@extends('layouts.dashboard')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('insights.social.results.choose_template.title') }}</h1>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm">
                    <p class="font-medium text-blue-900 mb-1">{{ __('insights.social.results.choose_template.strategy_label') }} <span class="uppercase">{{ $strategy }}</span></p>
                    <p class="text-blue-700">{{ __('insights.social.results.choose_template.selected_topics', ['count' => count($selectedTopicIds)]) }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('insights.social.generate-content') }}" method="POST">
            @csrf
            <input type="hidden" name="search_id" value="{{ $search->id }}">
            <input type="hidden" name="selected_topics" value="{{ json_encode($selectedTopicIds) }}">
            <input type="hidden" name="strategy" value="{{ $strategy }}">

            <div class="space-y-4 mb-6">
                @forelse($templates as $template)
                @if($loop->first)
                    <div class="custom-select-wrapper" id="templateSelectWrapper">
                        <div class="custom-select-trigger">
                            <span class="custom-select-value">{{ __('insights.social.results.choose_template.select_placeholder') }}</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown">
                            <div class="custom-select-search">
                                <input type="text" placeholder="{{ __('insights.social.results.choose_template.search_placeholder') }}" class="custom-select-search-input">
                            </div>
                            <div class="custom-select-options" id="templateSelectOptions">
                                <div class="custom-select-option" data-value="__create__">{{ __('insights.social.results.choose_template.create_new') }}</div>
                                @foreach($templates as $tmpl)
                                    <div class="custom-select-option"
                                         data-value="{{ $tmpl->id }}"
                                         data-name="{{ $tmpl->name }}">
                                        <div class="font-semibold text-gray-900 text-sm">{{ $tmpl->name }}</div>
                                        <div class="text-xs text-gray-600 mt-0.5">
                                            <span class="inline-block mr-2">{{ ucfirst($tmpl->tone ?? 'professional') }}</span>
                                            <span class="inline-block mr-2">• {{ $tmpl->article_size ?? 'medium' }}</span>
                                            <span class="inline-block">• {{ strtoupper($tmpl->language ?? 'id') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="hidden" id="templateRadios">
                        @foreach($templates as $tmpl)
                            <input type="radio" name="template_id" value="{{ $tmpl->id }}" {{ $loop->first ? 'checked' : '' }} required>
                        @endforeach
                    </div>
                @endif
                @empty
                <div class="text-center py-8 text-gray-500">
                    <p class="mb-4">{{ __('insights.social.results.choose_template.empty_title') }}. {{ __('insights.social.results.choose_template.empty_body') }}</p>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">{{ __('insights.social.results.choose_template.go_admin') }}</a>
                </div>
                @endforelse
            </div>

            <div class="flex gap-3">
                <a href="{{ route('insights.social.results', $search->id) }}"
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-center">
                    {{ __('insights.social.results.choose_template.cancel') }}
                </a>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2"
                        {{ $templates->isEmpty() ? 'disabled' : '' }}>
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                    {{ __('insights.social.results.choose_template.generate') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<!-- Create Template Modal (iframe) -->
<div id="templateCreateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('insights.social.results.choose_template.create_modal_title') }}</h3>
                <button type="button" onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <iframe src="{{ route('ai-templates.create-modal') }}"
                    class="w-full min-h-[80vh] h-[80vh] border-0"></iframe>
        </div>
    </div>
</div>

@push('styles')
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
@endpush

@push('scripts')
<script>
function openTemplateModal() {
    document.getElementById('templateCreateModal')?.classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateCreateModal')?.classList.add('hidden');
}

function initTemplateSelect() {
    const wrapper = document.getElementById('templateSelectWrapper');
    if (!wrapper) return;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const valueDisplay = wrapper.querySelector('.custom-select-value');
    const searchInput = wrapper.querySelector('.custom-select-search-input');
    const options = wrapper.querySelectorAll('.custom-select-option');
    const radiosContainer = document.getElementById('templateRadios');
    const radios = radiosContainer ? radiosContainer.querySelectorAll('input[type="radio"][name="template_id"]') : [];

    const setRadioChecked = (value) => {
        if (!radios || !radios.length) return;
        radios.forEach(r => { r.checked = (r.value === String(value)); });
    };

    const checked = Array.from(radios).find(r => r.checked);
    if (checked) {
        const selectedOption = wrapper.querySelector(`.custom-select-option[data-value="${checked.value}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.dataset.name || selectedOption.textContent.trim();
            options.forEach(o => o.classList.remove('selected'));
            selectedOption.classList.add('selected');
        }
    }

    trigger?.addEventListener('click', (e) => {
        e.stopPropagation();
        wrapper.classList.toggle('active');
        if (wrapper.classList.contains('active')) {
            searchInput?.focus();
        }
    });

    searchInput?.addEventListener('input', function() {
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

document.addEventListener('DOMContentLoaded', initTemplateSelect);

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
    option.innerHTML = `<div class="font-semibold text-gray-900 text-sm">${payload.template.name}</div>`;
    optionsContainer.appendChild(option);

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'template_id';
    radio.value = payload.template.id;
    radio.required = true;
    radiosContainer.appendChild(radio);

    initTemplateSelect();

    radiosContainer.querySelectorAll('input[type="radio"][name="template_id"]').forEach(r => {
        r.checked = (r.value === String(payload.template.id));
    });
    wrapper.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
    option.classList.add('selected');
    wrapper.querySelector('.custom-select-value').textContent = payload.template.name;

    closeTemplateModal();
});
</script>
@endpush
