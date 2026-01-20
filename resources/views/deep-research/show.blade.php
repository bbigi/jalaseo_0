@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('deep-research.index') }}" class="text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->seed_keyword }}</h1>
            </div>
            <p class="text-gray-600 mt-1">{{ $session->website->name ?? __('deep-research.show.unknown') }} • {{ $session->created_at->format('d M Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                {{ __('deep-research.show.status.' . (in_array($session->status, ['completed', 'processing', 'failed']) ? $session->status : 'other')) }}
            </span>
            <form action="{{ route('deep-research.destroy', $session->session_id) }}" method="POST" onsubmit="return confirm('{{ __('deep-research.show.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800">
                    <x-heroicon-o-trash class="w-5 h-5" />
                </button>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-lg p-4">
            <p class="text-sm text-gray-600">{{ __('deep-research.show.stats.keywords_found') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($session->keywords_count) }}</p>
        </div>
        <div class="bg-white rounded-lg p-4">
            <p class="text-sm text-gray-600">{{ __('deep-research.show.stats.auto_saved') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($session->auto_saved_keywords_count ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg p-4">
            <p class="text-sm text-gray-600">{{ __('deep-research.show.stats.competitors') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($session->total_competitors_found) }}</p>
        </div>
        <div class="bg-white rounded-lg p-4">
            <p class="text-sm text-gray-600">{{ __('deep-research.show.stats.paa') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ count($session->paa_data ?? []) }}</p>
        </div>
        <div class="bg-white rounded-lg p-4">
            <p class="text-sm text-gray-600">{{ __('deep-research.show.stats.processing_time') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ $session->getProcessingDuration() ?? 0 }}s</p>
        </div>
    </div>

    <!-- AI Recommendations Section -->
    @php
        $aiRec = $session->ai_recommendations ?? [];
        $globalAnalysis = $aiRec['global_analysis'] ?? null;
        $mainTargets = $aiRec['main_targets'] ?? [];
        $informational = $aiRec['informational'] ?? [];
        $strategySummary = $aiRec['strategy_summary'] ?? null;
        $contentGap = $aiRec['content_gap'] ?? null;
        $competitorStrategy = $aiRec['competitor_strategy'] ?? null;
        $recommendedFormats = $aiRec['recommended_formats'] ?? [];
    @endphp
    
    @if(!empty($aiRec))
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl mb-6">
        <div class="px-6 py-4 border-b border-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-s-sparkles class="w-5 h-5 text-emerald-600" />
                <h2 class="text-lg font-semibold text-emerald-800">{{ __('deep-research.show.ai.title') }}</h2>
                <span class="text-sm text-emerald-600">{{ __('deep-research.show.ai.subtitle') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="regenerateAI()" id="regenerateAIBtn" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 flex items-center gap-1">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                    {{ __('deep-research.show.ai.reanalyze') }}
                </button>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Global Market Analysis -->
            @if($globalAnalysis)
            <div class="bg-white rounded-lg border border-blue-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-globe-alt class="w-5 h-5 text-blue-600" />
                        <h3 class="font-semibold text-blue-800">{{ __('deep-research.show.ai.global_title') }}</h3>
                        <span class="px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">{{ __('deep-research.show.ai.ai_badge') }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $session->updated_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    @if(!empty($globalAnalysis['primary_intent']))
                    <div>
                        <p class="text-xs text-gray-500 mb-1">{{ __('deep-research.show.ai.primary_intent') }}</p>
                        <p class="font-semibold text-gray-900">{{ $globalAnalysis['primary_intent'] }}</p>
                    </div>
                    @endif
                    @if(!empty($globalAnalysis['market_difficulty']))
                    <div>
                        <p class="text-xs text-gray-500 mb-1">{{ __('deep-research.show.ai.market_difficulty') }}</p>
                        <p class="font-semibold text-gray-900">{{ $globalAnalysis['market_difficulty'] }}</p>
                    </div>
                    @endif
                </div>
                @if(!empty($globalAnalysis['recommendation_summary']))
                <div class="pt-3 border-t border-blue-100">
                    <p class="text-xs text-gray-500 mb-1">{{ __('deep-research.show.ai.recommendation') }}</p>
                    <p class="text-gray-700">{{ $globalAnalysis['recommendation_summary'] }}</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Recommended Content Format -->
            @if(!empty($recommendedFormats))
            <div class="bg-white rounded-lg border border-emerald-200 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-document-text class="w-5 h-5 text-emerald-600" />
                    <h3 class="font-semibold text-emerald-800">{{ __('deep-research.show.ai.format_title') }}</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($recommendedFormats as $format)
                    <span class="px-3 py-1.5 bg-emerald-100 text-emerald-700 text-sm rounded-full font-medium">
                        {{ $format }}
                    </span>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <x-heroicon-o-information-circle class="w-3 h-3 inline" />
                    {{ __('deep-research.show.ai.format_help') }}
                </p>
            </div>
            @endif

            <!-- Strategy Summary -->
            @if($strategySummary)
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500" />
                    <h3 class="font-semibold text-gray-800">{{ __('deep-research.show.ai.strategy_title') }}</h3>
                </div>
                <p class="text-gray-700">{{ $strategySummary }}</p>
            </div>
            @endif

            <!-- Content Gap & Competitor Strategy -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($contentGap)
                <div class="bg-white rounded-lg border border-amber-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-s-light-bulb class="w-5 h-5 text-amber-500" />
                        <h3 class="font-semibold text-amber-800">{{ __('deep-research.show.ai.content_gap') }}</h3>
                    </div>
                    <p class="text-gray-700 text-sm">{{ $contentGap }}</p>
                </div>
                @endif
                
                @if($competitorStrategy)
                <div class="bg-white rounded-lg border border-cyan-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-cyan-600" />
                        <h3 class="font-semibold text-cyan-800">{{ __('deep-research.show.ai.competitor_analysis') }}</h3>
                    </div>
                    <p class="text-gray-700 text-sm">{{ $competitorStrategy }}</p>
                </div>
                @endif
            </div>

            <!-- Main Targets -->
            @if(!empty($mainTargets))
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-trophy class="w-5 h-5 text-emerald-600" />
                        <h3 class="font-semibold text-emerald-800">{{ __('deep-research.show.ai.main_targets_title') }}</h3>
                        <span class="px-2 py-0.5 bg-emerald-600 text-white text-xs rounded-full">{{ count($mainTargets) }}</span>
                        <span class="text-xs text-gray-500">{{ __('deep-research.show.ai.main_targets_help') }}</span>
                    </div>
                    <button onclick="regenerateAI()" id="regenerateMainTargetsBtn" class="px-3 py-1 bg-purple-600 text-white text-xs rounded-lg hover:bg-purple-700 flex items-center gap-1">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        {{ __('deep-research.show.ai.reanalyze_main') }}
                    </button>
                </div>
                <div class="space-y-3">
                    @foreach($mainTargets as $target)
                    <div class="bg-white rounded-lg border border-emerald-200 p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <x-heroicon-o-arrow-right-circle class="w-4 h-4 text-emerald-500" />
                                    {{ $target['keyword'] }}
                                </h4>
                                @if(!empty($target['reason']))
                                <p class="text-sm text-gray-600 mt-1">{{ $target['reason'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-full font-medium">
                                    {{ __('deep-research.show.ai.priority') }}: {{ $target['priority_score'] ?? '-' }}
                                </span>
                                @if(!empty($target['intent']))
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                    {{ ucfirst($target['intent']) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Metrics -->
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-chart-bar class="w-3 h-3" />
                                {{ __('deep-research.show.ai.volume') }}: {{ number_format($target['volume'] ?? 0) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-signal class="w-3 h-3" />
                                {{ __('deep-research.show.ai.competition') }}: {{ ucfirst($target['competition'] ?? 'medium') }}
                            </span>
                        </div>
                        
                        <!-- Suggested Titles -->
                        @if(!empty($target['suggested_titles']))
                        <div class="bg-emerald-50 rounded-lg p-3">
                            <p class="text-xs font-semibold text-emerald-700 mb-2 flex items-center gap-1">
                                <x-heroicon-s-light-bulb class="w-3 h-3" />
                                {{ __('deep-research.show.ai.suggested_titles') }}
                            </p>
                            @foreach($target['suggested_titles'] as $index => $title)
                            <div class="flex items-center justify-between text-sm text-gray-700 mb-2 suggested-title-row">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="inline-flex items-center justify-center w-5 h-5 bg-emerald-600 text-white text-xs rounded">{{ $index + 1 }}</span>
                                    <span class="flex-1">{{ $title }}</span>
                                </div>
                                <button type="button"
                                    onclick="openGenerateModal('{{ addslashes($target['keyword']) }}', '{{ addslashes($title) }}')"
                                    class="suggested-generate-btn p-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                                    title="{{ __('deep-research.show.ai.generate_article') }}"
                                    alt="{{ __('deep-research.show.ai.generate_article') }}">
                                    <x-heroicon-o-sparkles class="w-4 h-4" />
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Informational Keywords -->
            @if(!empty($informational))
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-book-open class="w-5 h-5 text-blue-600" />
                        <h3 class="font-semibold text-blue-800">{{ __('deep-research.show.ai.informational_title') }}</h3>
                        <span class="px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">{{ count($informational) }}</span>
                        <span class="text-xs text-gray-500">{{ __('deep-research.show.ai.informational_help') }}</span>
                    </div>
                    <button onclick="saveSelectedInformational()" class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 font-medium">
                        {{ __('common.save') }}
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($informational as $index => $info)
                    <div class="bg-white rounded-lg border border-gray-200 p-3">
                        <div class="flex items-start gap-2">
                            <input type="checkbox" class="informational-checkbox mt-1" value="info-{{ $index }}"
                                   data-keyword="{{ $info['keyword'] }}"
                                   data-volume="{{ $info['volume'] ?? 0 }}"
                                   data-intent="informational">
                            <div class="flex-1 flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $info['keyword'] }}</p>
                                    @if(!empty($info['reason']))
                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($info['reason'], 80) }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    @if(!empty($info['category']))
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">{{ ucfirst($info['category']) }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400">{{ __('deep-research.show.ai.volume') }}: {{ number_format($info['volume'] ?? 0) }}</span>
                                    <a href="{{ route('generate.index', ['source' => 'deep_research', 'generation_source' => 'text', 'title' => $info['keyword'], 'ref_text' => $info['reason'] ?? '', 'website_id' => $session->website_id]) }}"
                                       class="inline-flex text-purple-600 hover:text-purple-700"
                                       title="{{ __('deep-research.show.ai.generate_article') }}">
                                        <x-heroicon-o-sparkles class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <!-- No AI Recommendations - Show Generate Button -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-xl mb-6 p-6 text-center">
        <x-heroicon-o-sparkles class="w-12 h-12 text-gray-400 mx-auto mb-3" />
        <h3 class="text-lg font-semibold text-gray-700 mb-2">{{ __('deep-research.show.ai.title') }}</h3>
        <p class="text-gray-500 mb-4">{{ __('deep-research.show.ai.subtitle') }}</p>
        <button onclick="regenerateAI()" id="regenerateAIBtn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 flex items-center gap-2 mx-auto">
            <x-heroicon-o-sparkles class="w-5 h-5" />
            {{ __('deep-research.show.ai.reanalyze') }}
        </button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Keywords -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold">{{ __('deep-research.show.keywords.title') }} <span id="keywordsCount" class="text-gray-500 font-normal">({{ $session->keywords_count }})</span></h2>
                        <div class="flex items-center gap-2">
                            <button id="reEnrichBtn" onclick="reEnrichKeywords()" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 flex items-center gap-1">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                {{ __('deep-research.show.keywords.recalc') }}
                            </button>
                            <button id="saveSelectedBtn" class="hidden px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                {{ __('deep-research.show.keywords.save_selected') }}
                            </button>
                        </div>
                    </div>
                    <!-- Search & Filter -->
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <input type="text" id="keywordSearch" placeholder="{{ __('deep-research.show.keywords.search_placeholder') }}" 
                                   class="w-full pl-9 pr-8 py-2 px-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <select id="intentFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('deep-research.show.keywords.filter_intent.all') }}</option>
                            <option value="transactional">{{ __('deep-research.show.keywords.filter_intent.transactional') }}</option>
                            <option value="commercial">{{ __('deep-research.show.keywords.filter_intent.commercial') }}</option>
                            <option value="informational">{{ __('deep-research.show.keywords.filter_intent.informational') }}</option>
                            <option value="navigational">{{ __('deep-research.show.keywords.filter_intent.navigational') }}</option>
                        </select>
                        <select id="statusFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('deep-research.show.keywords.filter_status.all') }}</option>
                            <option value="auto_saved">{{ __('deep-research.show.keywords.filter_status.auto_saved') }}</option>
                            <option value="user_saved">{{ __('deep-research.show.keywords.filter_status.user_saved') }}</option>
                            <option value="none">{{ __('deep-research.show.keywords.filter_status.none') }}</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10">
                                    <input type="checkbox" id="selectAll" class="rounded">
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('deep-research.show.keywords.table.keyword') }}</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">{{ __('deep-research.show.keywords.table.volume') }}</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">{{ __('deep-research.show.keywords.table.difficulty') }}</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">{{ __('deep-research.show.keywords.table.opportunity') }}</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">{{ __('deep-research.show.keywords.table.priority') }}</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">{{ __('deep-research.show.keywords.table.status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="keywordsBody" class="divide-y divide-gray-200">
                            @forelse($session->keywords->sortByDesc('ai_priority_score') as $keyword)
                            <tr class="keyword-row hover:bg-gray-50 {{ $keyword->save_status === 'auto_saved' ? 'bg-green-50' : '' }}"
                                data-keyword-id="{{ $keyword->id }}"
                                data-keyword="{{ $keyword->keyword }}"
                                data-intent="{{ $keyword->intent ?? '' }}"
                                data-status="{{ $keyword->save_status ?? 'none' }}"
                                data-priority="{{ $keyword->ai_priority_score ?? 0 }}"
                                data-volume="{{ $keyword->volume ?? 0 }}"
                                data-difficulty="{{ $keyword->difficulty_score ?? 0 }}"
                                data-competition-index="{{ $keyword->competition_index ?? 0 }}">
                                <td class="px-3 py-3">
                                    <input type="checkbox" class="keyword-checkbox rounded" value="{{ $keyword->id }}">
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-900">{{ $keyword->keyword }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($keyword->intent)
                                        <span class="text-xs px-1.5 py-0.5 rounded
                                            @if($keyword->intent === 'transactional') bg-green-100 text-green-700
                                            @elseif($keyword->intent === 'commercial') bg-yellow-100 text-yellow-700
                                            @elseif($keyword->intent === 'informational') bg-blue-100 text-blue-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ ucfirst($keyword->intent) }}
                                        </span>
                                        @endif
                                       <span class="text-xs text-gray-400">{{ $keyword->source }}</span>
                                       <a href="{{ route('generate.index', ['source' => 'deep_research', 'keyword' => $keyword->keyword, 'title' => $keyword->keyword, 'website_id' => $session->website_id]) }}"
                                          class="inline-flex text-blue-600 hover:text-blue-700"
                                           title="{{ __('deep-research.show.keywords.generate_article') }}">
                                           <x-heroicon-o-sparkles class="w-4 h-4" />
                                       </a>
                                   </div>
                               </td>
                                <td class="px-3 py-3 text-gray-900 font-medium">
                                    {{ $keyword->volume ? number_format($keyword->volume) : '-' }}
                                </td>
                                <td class="px-3 py-3">
                                    @if($keyword->difficulty_score)
                                    <div class="flex items-center gap-1">
                                        <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full 
                                                @if($keyword->difficulty_score >= 70) bg-red-500 
                                                @elseif($keyword->difficulty_score >= 40) bg-yellow-500 
                                                @else bg-green-500 @endif" 
                                                 style="width: {{ $keyword->difficulty_score }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $keyword->difficulty_score }}</span>
                                    </div>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($keyword->opportunity_score)
                                    <div class="flex items-center gap-1">
                                        <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full 
                                                @if($keyword->opportunity_score >= 70) bg-green-500 
                                                @elseif($keyword->opportunity_score >= 40) bg-yellow-500 
                                                @else bg-red-500 @endif" 
                                                 style="width: {{ $keyword->opportunity_score }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ number_format($keyword->opportunity_score, 1) }}</span>
                                    </div>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($keyword->ai_priority_score)
                                    <div class="flex items-center gap-1">
                                        <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full 
                                                @if($keyword->ai_priority_score >= 70) bg-green-500 
                                                @elseif($keyword->ai_priority_score >= 50) bg-yellow-500 
                                                @else bg-gray-400 @endif" 
                                                 style="width: {{ $keyword->ai_priority_score }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium 
                                            @if($keyword->ai_priority_score >= 70) text-green-600 
                                            @elseif($keyword->ai_priority_score >= 50) text-yellow-600 
                                            @else text-gray-600 @endif">{{ $keyword->ai_priority_score }}</span>
                                    </div>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                   @if($keyword->save_status === 'auto_saved')
                                   <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                                       <x-heroicon-s-check-circle class="w-3 h-3" />
                                        {{ $keyword->save_reason === 'ai_high_priority' ? __('deep-research.show.keywords.badges.high_priority') : __('deep-research.show.keywords.badges.quick_win') }}
                                   </span>
                                   @elseif($keyword->save_status === 'user_saved')
                                   <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">
                                       <x-heroicon-s-bookmark class="w-3 h-3" />
                                        {{ __('deep-research.show.keywords.badges.saved') }}
                                   </span>
                                   @else
                                   <span class="text-gray-400">-</span>
                                   @endif
                               </td>
                           </tr>
                           @empty
                           <tr id="emptyRow">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('deep-research.show.keywords.table.empty') }}</td>
                           </tr>
                           @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500" id="paginationInfo">
                        {{ __('deep-research.show.keywords.table.pagination', ['from' => 1, 'to' => 20, 'total' => $session->keywords_count]) }}
                    </p>
                    <div class="flex items-center gap-3">
                        <select id="perPageSelect" onchange="changePerPage()" class="text-sm border border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500">
                            <option value="15">15</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                        <div class="flex items-center gap-1">
                            <button id="prevPageBtn" onclick="prevPage()" disabled class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <x-heroicon-o-chevron-left class="w-4 h-4" />
                            </button>
                            <span id="pageIndicator" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                            <button id="nextPageBtn" onclick="nextPage()" class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <x-heroicon-o-chevron-right class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- PAA Questions -->
            <div class="bg-white rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ __('deep-research.show.sidebar.paa_title') }}</h2>
                        <button type="button"
                                onclick="generateFAQFromPAA()"
                                class="inline-flex text-purple-600 hover:text-purple-700"
                                title="{{ __('deep-research.show.sidebar.paa_generate') }}">
                            <x-heroicon-o-sparkles class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                    @forelse($session->paa_data ?? [] as $paa)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-gray-900 text-sm">{{ $paa['question'] ?? '' }}</p>
                        @if(!empty($paa['answer']))
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ $paa['answer'] }}</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">{{ __('deep-research.show.sidebar.paa_empty') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Competitors -->
            <div class="bg-white rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">{{ __('deep-research.show.sidebar.competitors_title') }}</h2>
                </div>
                <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                    @forelse($session->competitorAnalysis as $competitor)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $competitor->competitor_domain }}</p>
                            <p class="text-xs text-gray-500">{{ __('deep-research.show.sidebar.competitor_rank') }}: {{ $competitor->avg_rank }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full">
                            {{ $competitor->competitor_type }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">{{ __('deep-research.show.sidebar.competitors_empty') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- AI Analysis -->
            @if($session->ai_analysis)
            <div class="bg-white rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">{{ __('deep-research.show.sidebar.ai_insights') }}</h2>
                </div>
                <div class="p-4">
                    @if(isset($session->ai_analysis['top_opportunities']))
                    <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('deep-research.show.sidebar.top_opportunities') }}</h3>
                    <ul class="space-y-1 mb-4">
                        @foreach(array_slice($session->ai_analysis['top_opportunities'], 0, 5) as $opp)
                        <li class="text-sm text-gray-600">• {{ $opp['keyword'] ?? $opp }}</li>
                        @endforeach
                    </ul>
                    @endif
                    
                    @if(isset($session->ai_analysis['content_gaps']))
                    <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('deep-research.show.sidebar.content_gaps') }}</h3>
                    <ul class="space-y-1">
                        @foreach(array_slice($session->ai_analysis['content_gaps'], 0, 3) as $gap)
                        <li class="text-sm text-gray-600">• {{ $gap }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Generate Article Modal -->
<div id="generateArticleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl">
            <form id="generateArticleForm" action="{{ route('deep-research.prepare-generate', $session->session_id) }}" method="POST">
                @csrf
                <input type="hidden" name="keyword" id="modalKeyword">
                <input type="hidden" name="title" id="modalTitle">
                
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <x-heroicon-s-sparkles class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('deep-research.show.modal.generate_title') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('deep-research.show.modal.generate_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Selected Keyword & Title Preview -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                        <p class="text-xs text-emerald-600 mb-1">{{ __('deep-research.show.modal.selected_title') }}</p>
                        <p id="previewTitle" class="font-semibold text-emerald-800"></p>
                        <p class="text-xs text-gray-500 mt-2">{{ __('deep-research.show.modal.keyword_label') }} <span id="previewKeyword" class="font-medium"></span></p>
                    </div>
                    
                    <!-- Template Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('deep-research.show.modal.template_label') }} <span class="text-red-500">*</span>
                        </label>
                        @if($templates->count() > 0)
                        <div class="relative space-y-2">
                            <select name="template_id" id="templateSelect" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 appearance-none bg-white">
                                <option value="">{{ __('deep-research.show.modal.template_required') }}</option>
                                @foreach($templates as $template)
                                <option value="{{ $template->id }}" 
                                    data-tone="{{ $template->tone ?? 'professional' }}"
                                    data-size="{{ $template->article_size ?? 'medium' }}"
                                    data-lang="{{ strtoupper($template->language ?? 'id') }}"
                                    data-provider="{{ $template->ai_provider ?? '' }}">
                                    {{ $template->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                            </div>
                            <button type="button"
                                onclick="openTemplateModal()"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                <x-heroicon-o-plus-small class="w-4 h-4" />
                                {{ __('deep-research.show.modal.create_template') }}
                            </button>
                        </div>
                        <div id="templateInfo" class="hidden mt-2 p-2 bg-gray-50 rounded-lg text-xs text-gray-600">
                            <span id="templateTone"></span> • 
                            <span id="templateSize"></span> • 
                            <span id="templateLang"></span>
                            <span id="templateProvider"></span>
                        </div>
                        @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                {{ __('deep-research.show.modal.no_template') }}
                                <a href="{{ route('ai-templates.create') }}" class="underline font-medium" target="_blank">{{ __('deep-research.show.modal.create_template_link') }}</a>
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Brand Context (Optional) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('deep-research.show.modal.brand_context') }} <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <textarea name="brand_context" rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="{{ __('deep-research.show.modal.brand_placeholder') }}"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ __('deep-research.show.modal.brand_help') }}
                        </p>
                    </div>
                    
                    <!-- Additional Notes (Optional) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('deep-research.show.modal.notes') }} <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <textarea name="additional_notes" rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="{{ __('deep-research.show.modal.notes_placeholder') }}"></textarea>
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-200 flex gap-3">
                    <button type="button" onclick="closeGenerateModal()" 
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        {{ __('deep-research.show.modal.cancel') }}
                    </button>
                    <button type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 from-emerald-600 to-teal-600 text-white rounded-lg hover:from-emerald-700 hover:to-teal-700 font-medium flex items-center justify-center gap-2"
                        {{ $templates->count() == 0 ? 'disabled' : '' }}>
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        {{ __('deep-research.show.modal.generate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div id="drTemplateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('deep-research.show.modal.create_template_modal') }}</h3>
                <button type="button" onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <iframe src="{{ route('ai-templates.create-modal') }}"
                    class="w-full min-h-[80vh] h-[80vh] border-0"></iframe>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Show generate button only on hover for suggested titles */
.suggested-generate-btn {
    opacity: 1;
    transition: opacity 150ms ease;
}
.suggested-title-row:hover .suggested-generate-btn {
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
// ========================================
// PAGINATION & FILTERING STATE
// ========================================
let currentPage = 1;
let perPage = 20;
let allRows = [];
let filteredRows = [];

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rows for pagination
    initKeywordsTable();
    
    const selectAll = document.getElementById('selectAll');
    const saveBtn = document.getElementById('saveSelectedBtn');

    selectAll.addEventListener('change', function() {
        // Only check visible checkboxes
        const visibleCheckboxes = document.querySelectorAll('#keywordsBody .keyword-row:not([style*="display: none"]) .keyword-checkbox');
        visibleCheckboxes.forEach(cb => cb.checked = this.checked);
        toggleSaveBtn();
    });

    document.getElementById('keywordsBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('keyword-checkbox')) {
            toggleSaveBtn();
        }
    });

    function toggleSaveBtn() {
        const checked = document.querySelectorAll('.keyword-checkbox:checked');
        saveBtn.classList.toggle('hidden', checked.length === 0);
    }

    saveBtn.addEventListener('click', function() {
        // Show modal to select website and priority
        openSaveKeywordsModal();
    });

    function openSaveKeywordsModal() {
        const checkedBoxes = document.querySelectorAll('.keyword-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select keywords to save');
            return;
        }

        // Create modal HTML (Light Theme, Compact)
        const modal = document.createElement('div');
        modal.id = 'saveKeywordsModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-lg w-80 shadow-xl" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Save ${checkedBoxes.length} Keyword${checkedBoxes.length > 1 ? 's' : ''}</h3>
                    <button onclick="closeSaveModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Website</label>
                        <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                            {{ $session->website->name }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Target Position</label>
                        <input type="number" id="saveTargetPosition" min="1" max="100" value="10"
                               class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                        <p class="text-xs text-gray-500 mt-1">Default ranking goal for these keywords</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                        <select id="savePriority" class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                            <option value="high">High Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button onclick="closeSaveModal()" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs font-medium">
                            Cancel
                        </button>
                        <button onclick="saveKeywordsToDatabase()" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-medium">
                            Save Keywords
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeSaveModal();
        });
    }

    window.closeSaveModal = function() {
        const modal = document.getElementById('saveKeywordsModal');
        if (modal) modal.remove();
    };

    window.saveKeywordsToDatabase = function() {
        const priority = document.getElementById('savePriority').value;
        const targetPosition = parseInt(document.getElementById('saveTargetPosition').value) || 10;
        const websiteId = {{ $session->website_id }}; // Use website from session

        const checkedBoxes = document.querySelectorAll('.keyword-checkbox:checked');
        const keywordIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

        // Get keyword data from deep research results
        const keywords = keywordIds.map(id => {
            const row = document.querySelector(`tr[data-keyword-id="${id}"]`);
            if (!row) return null;

            return {
                keyword: row.dataset.keyword,
                website_id: websiteId,
                volume: parseInt(row.dataset.volume) || 0,
                difficulty: parseInt(row.dataset.difficulty) || 0,
                intent: row.dataset.intent || null,
                competition_index: parseInt(row.dataset.competitionIndex) || null,
                priority: priority,
                target_position: targetPosition
            };
        }).filter(k => k !== null);

        fetch('{{ route("keywords.save-from-deep-research") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ keywords })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(`${data.saved} new keywords saved, ${data.updated} updated`, 'success');
                closeSaveModal();
                saveToDeepResearchTable(keywordIds);
            } else {
                showToast('Failed to save keywords', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error saving keywords', 'error');
        });
    };

    function saveToDeepResearchTable(keywordIds) {
        fetch('{{ route("deep-research.save-keywords", $session->session_id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ keyword_ids: keywordIds, reason: 'user_selected' })
        })
        .then(r => r.json())
        .then(() => location.reload());
    }
    
    // Search input
    const searchInput = document.getElementById('keywordSearch');
    const clearBtn = document.getElementById('clearSearch');
    
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                applyFilters();
            }, 150);
        });
        
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                applyFilters();
                searchInput.focus();
            });
        }
    }
    
    // Filter dropdowns
    document.getElementById('intentFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
});

// ========================================
// TABLE INITIALIZATION
// ========================================
function initKeywordsTable() {
    const tbody = document.getElementById('keywordsBody');
    if (!tbody) return;
    
    // Collect all rows (skip empty row)
    allRows = Array.from(tbody.querySelectorAll('.keyword-row'));
    
    // Sort by priority score descending (already sorted by blade, but ensure JS has same order)
    allRows.sort((a, b) => {
        const priorityA = parseFloat(a.dataset.priority) || 0;
        const priorityB = parseFloat(b.dataset.priority) || 0;
        return priorityB - priorityA;
    });
    
    filteredRows = [...allRows];
    renderPage();
}

// ========================================
// FILTERING
// ========================================
function applyFilters() {
    const searchTerm = document.getElementById('keywordSearch').value.toLowerCase().trim();
    const intentFilter = document.getElementById('intentFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearSearch');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', searchTerm === '');
    }
    
    filteredRows = allRows.filter(row => {
        const keyword = row.dataset.keyword || '';
        const intent = row.dataset.intent || '';
        const status = row.dataset.status || 'none';
        
        // Search filter
        if (searchTerm && !keyword.includes(searchTerm)) {
            return false;
        }
        
        // Intent filter
        if (intentFilter && intent !== intentFilter) {
            return false;
        }
        
        // Status filter
        if (statusFilter) {
            if (statusFilter === 'none' && (status === 'auto_saved' || status === 'user_saved')) {
                return false;
            } else if (statusFilter !== 'none' && status !== statusFilter) {
                return false;
            }
        }
        
        return true;
    });
    
    currentPage = 1;
    renderPage();
}

// ========================================
// PAGINATION
// ========================================
function renderPage() {
    const tbody = document.getElementById('keywordsBody');
    if (!tbody) return;
    
    // Hide all rows first
    allRows.forEach(row => row.style.display = 'none');
    
    // Calculate which rows to show
    let rowsToShow;
    if (perPage === 'all') {
        rowsToShow = filteredRows;
    } else {
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        rowsToShow = filteredRows.slice(start, end);
    }
    
    // Show the rows for current page
    rowsToShow.forEach(row => {
        row.style.display = '';
    });
    
    // Handle empty state
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) {
        emptyRow.style.display = filteredRows.length === 0 ? '' : 'none';
    }
    
    updatePaginationUI();
}

function updatePaginationUI() {
    const total = filteredRows.length;
    const totalPages = perPage === 'all' ? 1 : Math.ceil(total / perPage);
    
    // Update info text
    const infoEl = document.getElementById('paginationInfo');
    const countEl = document.getElementById('keywordsCount');
    
    if (perPage === 'all') {
        if (infoEl) infoEl.textContent = `Showing all ${total} keywords`;
    } else {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        if (infoEl) infoEl.textContent = `Showing ${total > 0 ? start : 0} to ${end} of ${total} keywords`;
    }
    
    if (countEl) countEl.textContent = `(${total})`;
    
    // Update page indicator
    const pageIndicator = document.getElementById('pageIndicator');
    if (pageIndicator) {
        pageIndicator.textContent = perPage === 'all' ? 'All' : `Page ${currentPage} of ${totalPages || 1}`;
    }
    
    // Update buttons
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1 || perPage === 'all';
    }
    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages || perPage === 'all';
    }
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderPage();
        scrollToTable();
    }
}

function nextPage() {
    const totalPages = Math.ceil(filteredRows.length / perPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderPage();
        scrollToTable();
    }
}

function changePerPage() {
    const select = document.getElementById('perPageSelect');
    const value = select.value;
    perPage = value === 'all' ? 'all' : parseInt(value);
    currentPage = 1;
    renderPage();
}

function scrollToTable() {
    const table = document.getElementById('keywordsBody');
    if (table) {
        table.closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ========================================
// RE-ENRICH KEYWORDS
// ========================================
function reEnrichKeywords() {
    const btn = document.getElementById('reEnrichBtn');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...';
    
    fetch('{{ route("deep-research.re-enrich", $session->session_id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Failed to re-enrich keywords');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// ========================================
// REGENERATE AI RECOMMENDATIONS
// ========================================
function regenerateAI() {
    const btn = document.getElementById('regenerateAIBtn');
    if (!btn) return;
    
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing with AI...';
    
    fetch('{{ route("deep-research.regenerate-ai", $session->session_id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(async r => {
        const text = await r.text();
        console.log('Response status:', r.status);
        console.log('Response text:', text.substring(0, 500));
        
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error('JSON parse error:', e);
            throw new Error('Server returned invalid response. Check console for details.');
        }
    })
    .then(data => {
        if (data.success) {
            alert(data.message + '\n\nMain Targets: ' + (data.data?.main_targets || 0) + '\nInformational: ' + (data.data?.informational || 0));
            location.reload();
        } else {
            alert('Failed to regenerate AI recommendations: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// ========================================
// GENERATE ARTICLE MODAL
// ========================================
function openGenerateModal(keyword, title) {
    document.getElementById('modalKeyword').value = keyword;
    document.getElementById('modalTitle').value = title;
    document.getElementById('previewKeyword').textContent = keyword;
    document.getElementById('previewTitle').textContent = title;
    
    // Reset template selection
    const templateSelect = document.getElementById('templateSelect');
    if (templateSelect) {
        templateSelect.selectedIndex = 0;
        document.getElementById('templateInfo').classList.add('hidden');
    }
    
    document.getElementById('generateArticleModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeGenerateModal() {
    document.getElementById('generateArticleModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openTemplateModal() {
    document.getElementById('drTemplateModal').classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('drTemplateModal').classList.add('hidden');
}

// Template select change handler
document.getElementById('templateSelect')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('templateInfo');
    
    if (this.value) {
        document.getElementById('templateTone').textContent = option.dataset.tone ? option.dataset.tone.charAt(0).toUpperCase() + option.dataset.tone.slice(1) : '';
        document.getElementById('templateSize').textContent = option.dataset.size || '';
        document.getElementById('templateLang').textContent = option.dataset.lang || '';
        
        const providerSpan = document.getElementById('templateProvider');
        if (option.dataset.provider) {
            providerSpan.textContent = ' • ' + option.dataset.provider.charAt(0).toUpperCase() + option.dataset.provider.slice(1);
        } else {
            providerSpan.textContent = '';
        }
        
        infoDiv.classList.remove('hidden');
    } else {
        infoDiv.classList.add('hidden');
    }
});

// Close modal on backdrop click
document.getElementById('generateArticleModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeGenerateModal();
    }
});

// Listen for template-created from iframe (reuse pattern from /generate)
window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin) {
        return;
    }

    const payload = event.data || {};
    if (payload.type !== 'template-created' || !payload.template) {
        return;
    }

    const select = document.getElementById('templateSelect');
    if (!select) return;

    const option = document.createElement('option');
    option.value = payload.template.id;
    option.textContent = payload.template.name;
    select.appendChild(option);

    select.value = payload.template.id;
    select.dispatchEvent(new Event('change'));

    closeTemplateModal();
});

// Generate FAQ article from PAA list -> redirect to /generate with prefilled data
function generateFAQFromPAA() {
    const paa = @json($session->paa_data ?? []);
    if (!paa || !paa.length) {
        alert('PAA data not available.');
        return;
    }

    const title = `${@json($session->seed_keyword ?? 'FAQ')} - FAQ`;
    const blocks = paa.slice(0, 10).map(item => {
        const q = item.question || '';
        const a = item.answer || '';
        return `Q: ${q}\nA: ${a}`;
    });
    const refText = blocks.join('\n\n');

    const params = new URLSearchParams({
        source: 'deep_research',
        generation_source: 'text',
        title: title,
        ref_text: refText,
    });

    @if($session->website_id)
    params.append('website_id', '{{ $session->website_id }}');
    @endif

    window.location.href = `/generate?${params.toString()}`;
}
// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeGenerateModal();
    }
});

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Save Informational Keywords
function saveSelectedInformational() {
    const checked = document.querySelectorAll('.informational-checkbox:checked');
    if (checked.length === 0) {
        showToast('Please select keywords to save', 'error');
        return;
    }

    // Create modal (same as openSaveKeywordsModal)
    const modal = document.createElement('div');
    modal.id = 'saveInformationalModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-80 shadow-xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Save ${checked.length} Keyword${checked.length > 1 ? 's' : ''}</h3>
                <button onclick="closeInformationalModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Website</label>
                    <div class="bg-gray-50 text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                        {{ $session->website->name }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Target Position</label>
                    <input type="number" id="saveInformationalTarget" min="1" max="100" value="10"
                           class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                    <p class="text-xs text-gray-500 mt-1">Default ranking goal for these keywords</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                    <select id="saveInformationalPriority" class="w-full bg-white text-gray-900 rounded px-3 py-2 border border-gray-200 text-xs">
                        <option value="high">High Priority</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-2">
                    <button onclick="closeInformationalModal()" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs font-medium">
                        Cancel
                    </button>
                    <button onclick="saveInformationalToDatabase()" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-medium">
                        Save Keywords
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeInformationalModal();
    });
}

window.closeInformationalModal = function() {
    const modal = document.getElementById('saveInformationalModal');
    if (modal) modal.remove();
};

window.saveInformationalToDatabase = function() {
    const priority = document.getElementById('saveInformationalPriority').value;
    const targetPosition = parseInt(document.getElementById('saveInformationalTarget').value) || 10;
    const checked = document.querySelectorAll('.informational-checkbox:checked');

    const keywords = Array.from(checked).map(cb => ({
        keyword: cb.dataset.keyword,
        website_id: {{ $session->website_id }},
        volume: parseInt(cb.dataset.volume) || 0,
        difficulty: 0,
        intent: cb.dataset.intent,
        priority: priority,
        target_position: targetPosition
    }));

    fetch('{{ route("keywords.save-from-deep-research") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ keywords })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(`${data.saved} new keywords saved, ${data.updated} updated`, 'success');
            closeInformationalModal();
            checked.forEach(cb => cb.checked = false);
        } else {
            showToast('Failed to save keywords', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error saving keywords', 'error');
    });
};
</script>
@endpush
@endsection
