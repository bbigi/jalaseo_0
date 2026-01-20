@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Templates</h1>
            <p class="text-gray-600 mt-2">Manage design templates for social media</p>
        </div>
    </div>

    @if($templates->isEmpty())
        <div class="text-center py-16">
            <x-heroicon-o-rectangle-stack class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <p class="text-gray-600 text-lg">No templates found</p>
        </div>
    @else
        <!-- Platform Templates (Grouped by Category) -->
        @php
            $platformTemplates = $templates->where('is_public', 1)->groupBy('category_id');
            $myTemplates = $templates->where('is_public', 0);
        @endphp

        @if($platformTemplates->count() > 0)
            @foreach($platformTemplates as $categoryId => $categoryTemplates)
                @php
                    $category = $categoryTemplates->first()->category;
                @endphp
                @if($category)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $category->name }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($categoryTemplates as $template)
                            <div class="group bg-white rounded-lg border border-gray-200 hover:shadow-lg transition">
                                <div class="cursor-pointer" onclick="openTemplateDetail({{ $template->id }})">
                                    <div class="relative bg-gray-50 overflow-hidden rounded-t-lg" style="padding-bottom: 100%;">
                                        @if($template->thumbnail_path)
                                            <img src="{{ asset('storage/' . $template->thumbnail_path) }}"
                                                 class="absolute inset-0 w-full h-full object-contain"
                                                 alt="{{ $template->name }}">
                                        @else
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                                <x-heroicon-o-rectangle-stack class="w-16 h-16 text-blue-400" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-3">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $template->name }}</p>
                                    </div>
                                </div>
                                @if(auth()->user()->role === 'super_admin')
                                <div class="px-3 pb-3 flex gap-2">
                                    <button onclick="event.stopPropagation(); editTemplate({{ $template->id }})"
                                            class="flex-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded">
                                        Edit
                                    </button>
                                    <button onclick="event.stopPropagation(); deleteTemplate({{ $template->id }})"
                                            class="flex-1 px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                        Delete
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        @endif

        <!-- My Templates -->
        @if($myTemplates->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">My Templates</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($myTemplates as $template)
                    <div class="group bg-white rounded-lg border border-gray-200 hover:shadow-lg transition">
                        <div class="cursor-pointer" onclick="openTemplateDetail({{ $template->id }})">
                            <div class="relative bg-gray-50 overflow-hidden rounded-t-lg" style="padding-bottom: 100%;">
                                @if($template->thumbnail_path)
                                    <img src="{{ asset('storage/' . $template->thumbnail_path) }}"
                                         class="absolute inset-0 w-full h-full object-contain"
                                         alt="{{ $template->name }}">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center">
                                        <x-heroicon-o-rectangle-stack class="w-16 h-16 text-green-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $template->name }}</p>
                            </div>
                        </div>
                        <div class="px-3 pb-3 flex gap-2">
                            <button onclick="event.stopPropagation(); editTemplate({{ $template->id }})"
                                    class="flex-1 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded">
                                Edit
                            </button>
                            <button onclick="event.stopPropagation(); deleteTemplate({{ $template->id }})"
                                    class="flex-1 px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($templates->hasPages())
            <div class="mt-8">{{ $templates->links() }}</div>
        @endif
    @endif
</div>

<!-- Template Detail Modal -->
<div id="templateDetailModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="absolute inset-0" onclick="closeTemplateDetail()"></div>
    <div class="relative bg-white rounded-xl w-full max-w-3xl mx-4 p-6">
        <button onclick="closeTemplateDetail()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <div id="templateDetailContent"></div>
    </div>
</div>

@push('scripts')
<script>
function openTemplateDetail(id) {
    document.getElementById('templateDetailModal').classList.remove('hidden');
    fetch(`/api/templates/${id}`)
        .then(res => res.json())
        .then(data => {
            const template = data.template;
            const thumbnailHtml = template.thumbnail_url
                ? `<img src="${template.thumbnail_url}" class="w-full h-auto rounded border mb-4">`
                : `<div class="w-full aspect-square bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center rounded border mb-4">
                    <svg class="w-24 h-24 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/>
                    </svg>
                </div>`;

            document.getElementById('templateDetailContent').innerHTML = `
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">${template.name}</h3>
                    ${thumbnailHtml}
                    <p class="text-sm text-gray-600">${template.canvas_width} × ${template.canvas_height}px</p>
                    <p class="text-xs text-gray-500 capitalize mt-2">Category: ${template.category || 'No Category'}</p>
                </div>
            `;
        });
}

function closeTemplateDetail() {
    document.getElementById('templateDetailModal').classList.add('hidden');
}

function editTemplate(id) {
    window.location.href = `/image-editor?template_id=${id}`;
}

function deleteTemplate(id) {
    if (!confirm('Delete this template?')) return;

    fetch(`/templates/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    });
}
</script>
@endpush
@endsection
