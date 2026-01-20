@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('insights.social.create') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            {{ __('insights.social.results.back') }}
        </a>

        <div class="bg-white rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-3">
                @if($search->platform === 'google_trends_news')
                    {{ __('insights.social.results.news_articles_title', ['keyword' => $search->keyword]) }}
                @else
                    {{ __('insights.social.results.trending_title', ['platform' => ucfirst(str_replace('_', ' ', $search->platform))]) }}
                @endif
            </h1>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-fire class="w-4 h-4" />
                    @if($search->platform === 'google_trends_news')
                        {{ __('insights.social.results.badge_news') }}
                    @else
                        {{ ucfirst(str_replace('_', ' ', $search->platform)) }}
                    @endif
                </span>
                @if($search->keyword)
                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-tag class="w-4 h-4" />
                    {{ $search->keyword }}
                </span>
                @endif
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-1 rounded-lg">
                    <x-heroicon-o-globe-alt class="w-4 h-4" />
                    {{ $search->country_code }}
                </span>
                <span class="text-gray-600">
                    {{ $search->total_results }}
                    @if($search->platform === 'google_trends_news')
                        {{ __('insights.social.results.news_label') }}
                    @else
                        {{ __('insights.social.results.topics_label') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Results Grid -->
    @if($results->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($results as $result)
        <div class="bg-white rounded-lg p-5 hover:shadow-md transition border border-gray-200 relative">
            @if($search->platform !== 'google_trends' && $search->platform !== 'google_trends_news')
            <!-- Checkbox Selection (Top Right) - Only for TikTok/Twitter -->
            <div class="absolute top-4 right-4 z-10">
                <input type="checkbox"
                       name="selected_topics[]"
                       value="{{ $result->id }}"
                       class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer"
                       onchange="updateSelectedCount()">
            </div>
            @elseif($search->platform === 'google_trends_news')
            <!-- Checkbox for News Articles -->
            <div class="absolute top-4 right-4 z-10">
                <input type="checkbox"
                       name="selected_news[]"
                       value="{{ $result->id }}"
                       class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500 cursor-pointer"
                       onchange="updateNewsSelectedCount()">
            </div>
            @endif

            <!-- Platform Badge -->
            <div class="flex items-center justify-between mb-3 @if($search->platform !== 'google_trends') pr-8 @endif">
                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-medium capitalize">
                    @if($result->platform === 'google_trends_news')
                        {{ __('insights.social.results.badge_news') }}
                    @else
                        {{ str_replace('_', ' ', $result->platform) }}
                    @endif
                </span>
                @if($result->is_trending)
                <span class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-full font-medium">
                    🔥 {{ __('insights.social.results.badge_trending') }}
                </span>
                @endif
            </div>

            <!-- Content -->
            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                @if($result->link)
                <a href="{{ $result->link }}" target="_blank" class="hover:text-blue-600">
                    {{ $result->content }}
                </a>
                @else
                {{ $result->content }}
                @endif
            </h3>

            @if($result->author)
            <p class="text-sm text-gray-600 mb-3">
                <x-heroicon-o-user class="w-4 h-4 inline" />
                {{ $result->author }}
            </p>
            @endif

            <!-- Engagement Metrics -->
            <div class="flex items-center gap-4 text-xs text-gray-600 mb-3">
                @if($result->engagement_likes > 0)
                <span class="flex items-center gap-1">
                    <x-heroicon-o-heart class="w-4 h-4" />
                    {{ number_format($result->engagement_likes) }}
                </span>
                @endif
                @if($result->engagement_comments > 0)
                <span class="flex items-center gap-1">
                    <x-heroicon-o-chat-bubble-left class="w-4 h-4" />
                    {{ number_format($result->engagement_comments) }}
                </span>
                @endif
                @if($result->engagement_shares > 0)
                <span class="flex items-center gap-1">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                    {{ number_format($result->engagement_shares) }}
                </span>
                @endif
            </div>

            <!-- Hashtags -->
            @if($result->hashtags && count($result->hashtags) > 0)
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach(array_slice($result->hashtags, 0, 3) as $tag)
                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                    #{{ $tag }}
                </span>
                @endforeach
                @if(count($result->hashtags) > 3)
                <span class="text-xs text-gray-500">+{{ count($result->hashtags) - 3 }}</span>
                @endif
            </div>
            @endif

            @if($search->platform === 'google_trends')
            <!-- Search News Button - Only for Google Trends -->
            <form action="{{ route('insights.social.search-news-from-trend') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="keyword" value="{{ $result->content }}">
                <input type="hidden" name="country_code" value="{{ $search->country_code }}">
                <input type="hidden" name="language" value="{{ $search->language }}">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    {{ __('insights.social.results.search_news') }}
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>

    @if($search->platform === 'google_trends_news')
    <!-- Choose Template Button - For Google Trends News -->
    <div id="generateNewsButton" class="fixed hidden z-50" style="bottom: 2rem; right: 2rem;">
        <button onclick="showTemplateModal()" type="button"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-full shadow-lg flex items-center gap-3 transition transform hover:scale-105">
            <x-heroicon-o-sparkles class="w-6 h-6" />
            <span class="font-semibold">{!! __('insights.social.results.generate_news', ['count' => '<span id="newsSelectedCount">0</span>']) !!}</span>
        </button>
    </div>
    @elseif($search->platform !== 'google_trends')
    <!-- Floating Action Button (FAB) - Only for TikTok/Twitter -->
    <div id="createContentButton" class="fixed hidden z-50" style="bottom: 2rem; right: 2rem;">
        <button onclick="showStrategyModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 rounded-full shadow-lg flex items-center gap-3 transition transform hover:scale-105">
            <x-heroicon-o-sparkles class="w-6 h-6" />
            <span class="font-semibold">{!! __('insights.social.results.create_content', ['count' => '<span id="selectedCount">0</span>']) !!}</span>
        </button>
    </div>
    @endif

    @if($search->platform === 'google_trends_news')
    <!-- Create Template Modal (iframe) -->
    <div id="newsTemplateCreateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[70]">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('insights.social.results.template_modal.title') }}</h3>
                    <button type="button" onclick="closeNewsTemplateModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <iframe src="{{ route('ai-templates.create-modal') }}"
                        class="w-full min-h-[80vh] h-[80vh] border-0"></iframe>
            </div>
        </div>
    </div>
    @endif

    <!-- Template Selection Modal (for Google Trends News) -->
    @if($search->platform === 'google_trends_news')
    <div id="templateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ __('insights.social.results.template_modal.title') }}</h3>
                <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-6">
                {{ __('insights.social.results.template_modal.selected') }}
                <span class="font-semibold text-green-600"><span id="modalNewsCount">0</span> {{ __('insights.social.results.news_label') }}</span>
            </p>

            <form action="{{ route('insights.social.generate-batch-news') }}" method="POST" id="batchGenerateForm">
                @csrf
                <input type="hidden" name="search_id" value="{{ $search->id }}">
                <input type="hidden" name="selected_news" id="modalSelectedNewsInput">

                <div class="mb-6">
                    @php
                        $templates = \App\Models\AiGenerateTemplate::where('created_by', auth()->id())->get();
                    @endphp

                    @if($templates->count() > 0)
                        <div class="custom-select-wrapper" id="newsTemplateSelectWrapper">
                            <div class="custom-select-trigger">
                                <span class="custom-select-value">{{ __('insights.social.results.template_modal.create_template') }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="custom-select-dropdown">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="{{ __('insights.social.results.template_modal.search_placeholder') }}" class="custom-select-search-input">
                                </div>
                                <div class="custom-select-options" id="newsTemplateSelectOptions">
                                    <div class="custom-select-option" data-value="__create__">{{ __('insights.social.results.template_modal.create_new') }}</div>
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

                        <div class="hidden" id="newsTemplateRadios">
                            @foreach($templates as $tmpl)
                                <input type="radio" name="template_id" value="{{ $tmpl->id }}" {{ $loop->first ? 'checked' : '' }} required>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-center py-4">
                            {{ __('insights.social.results.template_modal.no_template') }}
                            <button type="button" onclick="openNewsTemplateModal()" class="text-blue-600 hover:underline font-medium">
                                {{ __('insights.social.results.template_modal.create_template') }}
                            </button>
                        </p>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeTemplateModal()"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        {{ __('insights.social.results.template_modal.cancel') }}
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
                        <x-heroicon-o-sparkles class="w-5 h-5" />
                        {{ __('insights.social.results.template_modal.confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Strategy Selection Modal -->
    <div id="strategyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('insights.social.results.strategy_modal.title') }}</h3>

            <p class="text-sm text-gray-600 mb-6">
                {{ __('insights.social.results.strategy_modal.selected') }}
                <span class="font-semibold text-blue-600"><span id="modalSelectedCount">0</span> {{ __('insights.social.results.topics_label') }}</span>
            </p>

            <form action="{{ route('insights.social.choose-template') }}" method="GET" id="strategyForm">
                <input type="hidden" name="search_id" value="{{ $search->id }}">
                <input type="hidden" name="selected_topics" id="selectedTopicsInput">

                <div class="space-y-4 mb-6">
                    <!-- Option 1: Roundup Only -->
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="strategy" value="roundup" class="mt-1" required>
                        <div>
                            <div class="font-semibold text-gray-900">{{ __('insights.social.results.strategy_modal.roundup.title') }}</div>
                            <div class="text-sm text-gray-600">{{ __('insights.social.results.strategy_modal.roundup.desc') }}</div>
                        </div>
                    </label>

                    <!-- Option 2: Hybrid Strategy (RECOMMENDED) -->
                    <label class="flex items-start gap-3 p-4 border-2 border-blue-500 bg-blue-50 rounded-lg cursor-pointer">
                        <input type="radio" name="strategy" value="hybrid" class="mt-1" checked required>
                        <div>
                            <div class="font-semibold text-blue-900 flex items-center gap-2">
                                {{ __('insights.social.results.strategy_modal.hybrid.title') }}
                                <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded">{{ __('insights.social.results.strategy_modal.hybrid.badge') }}</span>
                            </div>
                            <div class="text-sm text-blue-700">{{ __('insights.social.results.strategy_modal.hybrid.desc') }}</div>
                            <div class="text-xs text-blue-600 mt-1">{{ __('insights.social.results.strategy_modal.hybrid.note') }}</div>
                        </div>
                    </label>

                    <!-- Option 3: Deep Dive All -->
                    <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="strategy" value="deep_dive_all" class="mt-1" required>
                        <div>
                            <div class="font-semibold text-gray-900">{{ __('insights.social.results.strategy_modal.deep_dive.title') }}</div>
                            <div class="text-sm text-gray-600">{!! __('insights.social.results.strategy_modal.deep_dive.desc', ['count' => '<span id=\"deepDiveCount\">0</span>']) !!}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ __('insights.social.results.strategy_modal.deep_dive.note') }}</div>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="closeStrategyModal()"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        {{ __('insights.social.results.strategy_modal.actions.cancel') }}
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <x-heroicon-o-arrow-right class="w-5 h-5" />
                        {{ __('insights.social.results.strategy_modal.actions.next') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @else
    <div class="bg-white rounded-lg p-12 text-center">
        <x-heroicon-o-magnifying-glass class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('insights.social.results.empty_title') }}</h3>
        <p class="text-gray-600 mb-6">{{ __('insights.social.results.empty_subtitle') }}</p>
        <a href="{{ route('insights.social.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
            {{ __('insights.social.results.cta_try_again') }}
        </a>
    </div>
    @endif

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-gray-700">
                @if($search->platform === 'google_trends')
                <p class="font-medium text-gray-900 mb-1">{{ __('insights.social.results.info.google_title') }}</p>
                <ul class="space-y-1 text-gray-700">
                    <li>• {{ __('insights.social.results.info.google_items.search_news') }}</li>
                    <li>• {{ __('insights.social.results.info.google_items.scrape') }}</li>
                    <li>• {{ __('insights.social.results.info.google_items.select') }}</li>
                </ul>
                @else
                <p class="font-medium text-gray-900 mb-1">{{ __('insights.social.results.info.topics_title') }}</p>
                <ul class="space-y-1 text-gray-700">
                    <li>• {{ __('insights.social.results.info.topics_items.select') }}</li>
                    <li>• {{ __('insights.social.results.info.topics_items.strategy') }}</li>
                    <li>• {{ __('insights.social.results.info.topics_items.auto') }}</li>
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

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
const msgSelectNews = @json(__('insights.social.results.errors.select_news'));
const msgSelectTopics = @json(__('insights.social.results.errors.select_topics'));

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="selected_topics[]"]:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;

    // Show/hide FAB button
    const fab = document.getElementById('createContentButton');
    if (count > 0) {
        fab.classList.remove('hidden');
    } else {
        fab.classList.add('hidden');
    }
}

function updateNewsSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="selected_news[]"]:checked');
    const count = checkboxes.length;
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);

    document.getElementById('newsSelectedCount').textContent = count;

    // Store selected IDs globally
    window.selectedNewsIds = selectedIds;

    // Show/hide Generate button
    const btn = document.getElementById('generateNewsButton');
    if (count > 0) {
        btn.classList.remove('hidden');
    } else {
        btn.classList.add('hidden');
    }
}

function showTemplateModal() {
    if (!window.selectedNewsIds || window.selectedNewsIds.length === 0) {
        alert(msgSelectNews);
        return;
    }

    const modalNewsCountEl = document.getElementById('modalNewsCount');
    const modalSelectedNewsInput = document.getElementById('modalSelectedNewsInput');
    if (modalNewsCountEl) modalNewsCountEl.textContent = window.selectedNewsIds.length;
    if (modalSelectedNewsInput) modalSelectedNewsInput.value = JSON.stringify(window.selectedNewsIds);

    // Show modal
    document.getElementById('templateModal')?.classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function openNewsTemplateModal() {
    // Ensure iframe modal is not covered by the template selection modal
    closeTemplateModal();
    document.getElementById('newsTemplateCreateModal')?.classList.remove('hidden');
}

function closeNewsTemplateModal() {
    document.getElementById('newsTemplateCreateModal')?.classList.add('hidden');
}

function initNewsTemplateSelect() {
    const wrapper = document.getElementById('newsTemplateSelectWrapper');
    if (!wrapper) return;

    const trigger = wrapper.querySelector('.custom-select-trigger');
    const valueDisplay = wrapper.querySelector('.custom-select-value');
    const searchInput = wrapper.querySelector('.custom-select-search-input');
    const options = wrapper.querySelectorAll('.custom-select-option');
    const radiosContainer = document.getElementById('newsTemplateRadios');
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
                openNewsTemplateModal();
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

function showStrategyModal() {
    const checkboxes = document.querySelectorAll('input[name="selected_topics[]"]:checked');
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert(msgSelectTopics);
        return;
    }

    const modalSelectedCountEl = document.getElementById('modalSelectedCount');
    const deepDiveCountEl = document.getElementById('deepDiveCount');
    const selectedTopicsInput = document.getElementById('selectedTopicsInput');
    if (modalSelectedCountEl) modalSelectedCountEl.textContent = selectedIds.length;
    if (deepDiveCountEl) deepDiveCountEl.textContent = selectedIds.length;
    if (selectedTopicsInput) selectedTopicsInput.value = JSON.stringify(selectedIds);

    // Show modal
    document.getElementById('strategyModal')?.classList.remove('hidden');
}

function closeStrategyModal() {
    document.getElementById('strategyModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStrategyModal();
    }
});

// Close modal on backdrop click
document.getElementById('strategyModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeStrategyModal();
    }
});

document.addEventListener('DOMContentLoaded', initNewsTemplateSelect);

window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) {
        return;
    }

    const payload = event.data || {};
    if (payload.type !== 'template-created' || !payload.template) {
        return;
    }

    const optionsContainer = document.getElementById('newsTemplateSelectOptions');
    const radiosContainer = document.getElementById('newsTemplateRadios');
    const wrapper = document.getElementById('newsTemplateSelectWrapper');
    if (!optionsContainer || !radiosContainer || !wrapper) {
        closeNewsTemplateModal();
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

    initNewsTemplateSelect();

    radiosContainer.querySelectorAll('input[type="radio"][name="template_id"]').forEach(r => {
        r.checked = (r.value === String(payload.template.id));
    });
    wrapper.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
    option.classList.add('selected');
    wrapper.querySelector('.custom-select-value').textContent = payload.template.name;

    closeNewsTemplateModal();
});
</script>
@endpush
