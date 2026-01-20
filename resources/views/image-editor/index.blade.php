@extends('layouts.dashboard')

@section('title', 'Image Editor (POC) - AneWP')

@section('content')
<div class="h-screen flex bg-white" x-data="{ showMediaLibrary: false }">
    <!-- Left Sidebar - Tools -->
    <div class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-6 gap-4">
        <!-- Templates -->
        <button onclick="openTemplatesModal()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/>
            </svg>
            <span class="text-xs text-gray-600">Templates</span>
        </button>

        <!-- Text -->
        <button onclick="addText()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-xs text-gray-600">Text</span>
        </button>

        <!-- Photos -->
        <button @click="$dispatch('open-media-library')" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs text-gray-600">Photos</span>
        </button>

        <!-- Elements -->
        <button onclick="openElementsPanel()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-xs text-gray-600">Elements</span>
        </button>

        <!-- Background -->
        <button class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
            <span class="text-xs text-gray-600">Background</span>
        </button>

        <!-- Resize -->
        <button onclick="openResizeModal()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
            <span class="text-xs text-gray-600">Resize</span>
        </button>

        <!-- Apply Branding Button -->
        <button onclick="applyBranding()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition" title="Apply Branding">
            <x-heroicon-o-paint-brush class="w-6 h-6 text-gray-700" />
            <span class="text-xs text-gray-600">Brand</span>
        </button>

        <div class="flex-1"></div>

        <!-- Save as Template (shown when no asset) -->
        <button id="saveTemplateBtn" onclick="openSaveTemplateModal()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <span class="text-xs text-gray-600">Save</span>
        </button>

        <!-- Save Asset (shown when asset loaded) -->
        <button id="saveAssetBtn" onclick="saveAsset()" class="hidden w-14 h-14 flex flex-col items-center justify-center gap-1 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <span class="text-xs">Save</span>
        </button>

        <!-- Export Button (bottom) -->
        <button onclick="openExportModal()" class="w-14 h-14 flex flex-col items-center justify-center gap-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span class="text-xs">Export</span>
        </button>
    </div>

    <!-- Canvas Area -->
    <div class="flex-1 flex items-center justify-center bg-gray-50 relative">
        <div id="canvasContainer" class="relative shadow-lg overflow-hidden" style="width: 540px; height: 675px; background: white;">
            <!-- Konva canvas will be inserted here -->
        </div>

        <!-- Zoom Controls -->
        <div class="absolute bottom-4 right-4 flex gap-2 bg-white rounded-lg shadow-lg border border-gray-200 p-2">
            <button onclick="zoomIn()" class="px-3 py-2 hover:bg-gray-100 rounded transition" title="Zoom In">
                <x-heroicon-o-magnifying-glass-plus class="w-5 h-5 text-gray-700" />
            </button>
            <button onclick="zoomOut()" class="px-3 py-2 hover:bg-gray-100 rounded transition" title="Zoom Out">
                <x-heroicon-o-magnifying-glass-minus class="w-5 h-5 text-gray-700" />
            </button>
            <button onclick="fitToScreen()" class="px-3 py-2 hover:bg-gray-100 rounded transition" title="Fit to Screen">
                <x-heroicon-o-arrows-pointing-in class="w-5 h-5 text-gray-700" />
            </button>
            <div class="border-l border-gray-300 mx-1"></div>
            <span id="zoomLevel" class="px-2 py-2 text-sm text-gray-600 font-medium">50%</span>
        </div>
    </div>

    <!-- Right Sidebar - Tabs -->
    <div class="w-80 bg-white border-l border-gray-200 flex flex-col">
        <!-- Branding Selector -->
        <div class="p-4 border-b border-gray-200">
            <label class="block text-xs font-semibold text-gray-700 mb-2">Branding Preset</label>
            <select id="brandingSelector" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">None</option>
            </select>
            <button onclick="applySelectedBranding()" class="mt-2 w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition">
                Apply Branding
            </button>
        </div>

        <!-- Tabs Header -->
        <div class="flex border-b border-gray-200">
            <button onclick="switchTab('properties')" id="propertiesTab" class="flex-1 px-4 py-3 text-sm font-medium text-purple-600 border-b-2 border-purple-600 hover:text-purple-700">
                Properties
            </button>
            <button onclick="switchTab('layers')" id="layersTab" class="flex-1 px-4 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300">
                Layers
            </button>
            <button onclick="switchTab('assets')" id="assetsTab" class="flex-1 px-4 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300">
                Assets
            </button>
        </div>

        <!-- Tabs Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <!-- Assets Tab Content -->
            <div id="assetsContent" class="hidden">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Shapes</h3>
                <div class="grid grid-cols-3 gap-3">
                    <!-- Square -->
                    <button onclick="addShape('square')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <div class="w-12 h-12 bg-gray-300 rounded"></div>
                    </button>
                    <!-- Rectangle -->
                    <button onclick="addShape('rectangle')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <div class="w-16 h-8 bg-gray-300 rounded"></div>
                    </button>
                    <!-- Circle -->
                    <button onclick="addShape('circle')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <div class="w-12 h-12 bg-gray-300 rounded-full"></div>
                    </button>
                    <!-- Triangle -->
                    <button onclick="addShape('triangle')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="50,15 90,85 10,85" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Star -->
                    <button onclick="addShape('star')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="50,10 61,40 95,40 68,60 79,90 50,70 21,90 32,60 5,40 39,40" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Pentagon -->
                    <button onclick="addShape('pentagon')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="50,10 90,40 75,85 25,85 10,40" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Hexagon -->
                    <button onclick="addShape('hexagon')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="50,5 90,30 90,70 50,95 10,70 10,30" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Torn Paper -->
                    <button onclick="addShape('torn-paper')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Torn Paper">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <path d="M 5,8 L 9,11 L 13,8 L 17,12 L 22,9 L 27,13 L 32,10 L 37,14 L 42,11 L 48,15 L 53,12 L 58,16 L 64,13 L 69,17 L 75,14 L 80,18 L 86,15 L 91,19 L 95,16 L 95,92 L 5,92 Z" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Octagram Rounded -->
                    <button onclick="addShape('octagram-rounded')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Octagram Rounded">
                        <svg class="w-12 h-12" viewBox="0 0 48 48">
                            <path fill="#D1D5DB" d="M26.86 2.507c-1.693-1.397-4.027-1.396-5.72 0c-1.013.835-2.449 2.084-4.416 3.925c-2.692.089-4.591.221-5.898.347c-2.186.21-3.836 1.86-4.046 4.046c-.125 1.306-.257 3.205-.347 5.898c-1.84 1.967-3.09 3.404-3.925 4.416c-1.397 1.694-1.397 4.028 0 5.722c.835 1.012 2.085 2.449 3.926 4.416c.089 2.691.221 4.59.346 5.896c.21 2.186 1.86 3.836 4.046 4.046c1.306.125 3.205.258 5.896.347c1.968 1.841 3.404 3.09 4.417 3.926c1.694 1.397 4.028 1.397 5.722 0c1.013-.835 2.45-2.085 4.417-3.926c2.692-.09 4.591-.222 5.897-.347c2.186-.21 3.836-1.86 4.046-4.045c.125-1.306.258-3.206.347-5.898c1.84-1.967 3.09-3.403 3.924-4.415c1.397-1.694 1.397-4.028 0-5.721c-.834-1.013-2.083-2.45-3.924-4.416c-.09-2.694-.222-4.593-.347-5.9c-.21-2.185-1.86-3.835-4.046-4.045c-1.306-.126-3.206-.258-5.9-.347c-1.966-1.84-3.402-3.09-4.415-3.925"/>
                        </svg>
                    </button>
                    <!-- Octagram Sharp -->
                    <button onclick="addShape('octagram-sharp')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Octagram Sharp">
                        <svg class="w-12 h-12" viewBox="0 0 24 24">
                            <path fill="#D1D5DB" d="M9.5 4L12 1l3 3h5v5l3 3l-3 3v5h-5l-3 3l-3-3H4v-5l-3-3l3-3V4z"/>
                        </svg>
                    </button>
                    <!-- Arrow Right -->
                    <button onclick="addShape('arrow')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="10,40 60,40 60,20 90,50 60,80 60,60 10,60" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Heart -->
                    <button onclick="addShape('heart')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <path d="M50,90 C50,90 10,60 10,35 C10,20 20,10 30,10 C40,10 50,20 50,20 C50,20 60,10 70,10 C80,10 90,20 90,35 C90,60 50,90 50,90 Z" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Diamond -->
                    <button onclick="addShape('diamond')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <polygon points="50,10 90,50 50,90 10,50" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Speech Bubble -->
                    <button onclick="addShape('speech-bubble')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <path d="M 10 20 Q 10 10 20 10 L 80 10 Q 90 10 90 20 L 90 60 Q 90 70 80 70 L 30 70 L 20 85 L 25 70 L 20 70 Q 10 70 10 60 Z" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- House -->
                    <button onclick="addShape('house')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <path d="M 50 10 L 90 45 L 80 45 L 80 85 L 60 85 L 60 60 L 40 60 L 40 85 L 20 85 L 20 45 L 10 45 Z" fill="#D1D5DB"/>
                        </svg>
                    </button>
                    <!-- Water Drop -->
                    <button onclick="addShape('water-drop')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <path d="M 50 10 C 50 10 25 40 25 60 C 25 75 35 90 50 90 C 65 90 75 75 75 60 C 75 40 50 10 50 10 Z" fill="#D1D5DB"/>
                        </svg>
                    </button>
                </div>

                <h3 class="text-sm font-semibold text-gray-700 mb-3 mt-6">Icons</h3>
                <div class="grid grid-cols-3 gap-3">
                    <!-- Facebook -->
                    <button onclick="addShape('icon-facebook')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Facebook">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M13.5 1A4.5 4.5 0 0 0 9 5.5V9H6.5a.5.5 0 0 0-.5.5v4a.5.5 0 0 0 .5.5H9v8.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5V14h2.5a.5.5 0 0 0 .485-.379l1-4A.5.5 0 0 0 17.5 9H14V7.5A1.5 1.5 0 0 1 15.5 6h2a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-.5-.5z"/></svg>
                    </button>
                    <!-- Facebook Alt -->
                    <button onclick="addShape('icon-facebook-alt')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Facebook Alt">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" fillRule="evenodd" d="M5 1a4 4 0 0 0-4 4v14a4 4 0 0 0 4 4h14a4 4 0 0 0 4-4V5a4 4 0 0 0-4-4zm4.682 6.273A3.273 3.273 0 0 1 12.954 4h2.91c.2 0 .363.163.363.364v2.909c0 .2-.163.363-.363.363h-1.455a1.09 1.09 0 0 0-1.09 1.091v1.091h2.545a.364.364 0 0 1 .352.452l-.727 2.91a.36.36 0 0 1-.353.274h-1.818v6.182a.364.364 0 0 1-.364.364h-2.909a.364.364 0 0 1-.363-.364v-6.181H7.864a.364.364 0 0 1-.364-.364v-2.91c0-.2.163-.363.364-.363h1.818z" clipRule="evenodd"/></svg>
                    </button>
                    <!-- Instagram Outline -->
                    <button onclick="addShape('icon-instagram-outline')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Instagram Outline">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" fillRule="evenodd" d="M12 1.734c-2.788 0-3.138.012-4.233.062c-1.092.05-1.839.223-2.492.477a5 5 0 0 0-1.818 1.184a5 5 0 0 0-1.184 1.818c-.254.653-.427 1.4-.477 2.492c-.05 1.095-.062 1.445-.062 4.233s.012 3.138.062 4.233c.05 1.092.223 1.839.477 2.492a5 5 0 0 0 1.184 1.818a5 5 0 0 0 1.818 1.184c.653.254 1.4.427 2.492.477c1.095.05 1.445.062 4.233.062s3.138-.012 4.233-.062c1.092-.05 1.839-.223 2.492-.477c.675-.262 1.247-.613 1.818-1.184s.922-1.143 1.184-1.818c.254-.653.427-1.4.477-2.492c.05-1.095.062-1.445.062-4.233s-.012-3.138-.062-4.233c-.05-1.092-.223-1.839-.477-2.492a5 5 0 0 0-1.184-1.818a5 5 0 0 0-1.818-1.184c-.653-.254-1.4-.427-2.492-.477c-1.095-.05-1.445-.062-4.233-.062m0 1.85c2.741 0 3.066.01 4.148.06c1.001.045 1.545.213 1.907.353c.479.186.82.409 1.18.768s.582.701.768 1.18c.14.362.308.906.353 1.907c.05 1.082.06 1.407.06 4.148s-.01 3.066-.06 4.148c-.045 1.001-.213 1.545-.353 1.907c-.186.479-.409.82-.768 1.18s-.701.582-1.18.768c-.362.14-.906.308-1.907.353c-1.082.05-1.407.06-4.148.06s-3.066-.01-4.148-.06c-1.001-.045-1.545-.213-1.907-.353c-.479-.186-.82-.409-1.18-.768s-.582-.701-.768-1.18c-.14-.362-.308-.906-.353-1.907c-.05-1.082-.06-1.407-.06-4.148s.01-3.066.06-4.148c.045-1.001.213-1.545.353-1.907a3.2 3.2 0 0 1 .768-1.18c.36-.36.701-.582 1.18-.768c.362-.14.906-.308 1.907-.353c1.082-.05 1.407-.06 4.148-.06M6.728 12a5.272 5.272 0 1 1 10.544 0a5.272 5.272 0 0 1-10.544 0M12 15.422a3.422 3.422 0 1 1 0-6.844a3.422 3.422 0 0 1 0 6.844m5.48-7.67a1.232 1.232 0 1 0 0-2.464a1.232 1.232 0 0 0 0 2.464" clipRule="evenodd"/></svg>
                    </button>
                    <!-- WhatsApp -->
                    <button onclick="addShape('icon-whatsapp')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="WhatsApp">
                        <svg class="w-12 h-12" viewBox="0 0 16 16"><path fill="#D1D5DB" d="M8 0a8 8 0 1 1-4.075 14.886L.658 15.974a.5.5 0 0 1-.632-.632l1.089-3.266A8 8 0 0 1 8 0M5.214 4.004a.7.7 0 0 0-.526.266C4.508 4.481 4 4.995 4 6.037c0 1.044.705 2.054.804 2.196c.098.138 1.388 2.28 3.363 3.2q.55.255 1.12.446c.472.16.902.139 1.242.085c.379-.06 1.164-.513 1.329-1.01c.163-.493.163-.918.113-1.007c-.049-.088-.18-.142-.378-.25c-.196-.105-1.165-.618-1.345-.687c-.18-.073-.312-.106-.443.105c-.132.213-.507.691-.623.832c-.113.139-.23.159-.425.053c-.198-.105-.831-.33-1.584-1.054c-.585-.561-.98-1.258-1.094-1.469c-.116-.213-.013-.326.085-.433c.09-.094.198-.246.296-.371c.097-.122.132-.21.198-.353c.064-.141.031-.266-.018-.371s-.443-1.152-.607-1.577c-.16-.413-.323-.355-.443-.363c-.114-.005-.245-.005-.376-.005"/></svg>
                    </button>
                    <!-- X Twitter -->
                    <button onclick="addShape('icon-twitter')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="X (Twitter)">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M10.488 14.651L15.25 21h7l-7.858-10.478L20.93 3h-2.65l-5.117 5.886L8.75 3h-7l7.51 10.015L2.32 21h2.65zM16.25 19L5.75 5h2l10.5 14z"/></svg>
                    </button>
                    <!-- Threads -->
                    <button onclick="addShape('icon-threads')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Threads">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" fillRule="evenodd" d="M4.584 3.235C6.464 1.04 9.067 0 11.888 0c2.693 0 5.074.645 6.911 1.963c1.857 1.332 3.07 3.287 3.502 5.712a1.25 1.25 0 1 1-2.461.438c-.325-1.822-1.202-3.19-2.497-4.118c-1.314-.942-3.146-1.495-5.455-1.495c-2.18 0-4.056.786-5.406 2.361c-1.365 1.594-2.302 4.128-2.302 7.81c0 3.552 1.201 5.696 2.74 6.977c1.58 1.316 3.676 1.852 5.64 1.852c3.878 0 6.255-2.282 6.366-4.778c.055-1.239-.462-2.24-1.422-2.973l-.07-.053c-.193.878-.512 1.66-.952 2.328c-.92 1.397-2.32 2.213-3.924 2.331c-1.48.11-2.722-.246-3.63-.976a3.84 3.84 0 0 1-1.434-2.771c-.048-.823.09-2.038 1.028-3.043c.943-1.01 2.463-1.561 4.635-1.561c.641 0 1.268.047 1.872.143c-.213-1.05-.693-1.56-1.109-1.836c-.563-.375-1.21-.447-1.525-.447c-.884 0-1.667.332-2.403 1.176a1.25 1.25 0 0 1-1.885-1.642c1.172-1.345 2.624-2.034 4.288-2.034c.583 0 1.781.115 2.909.864c1.193.793 2.142 2.188 2.31 4.409l.018.289c.5.234.966.513 1.389.836c1.559 1.19 2.496 2.951 2.402 5.071C21.235 21.057 17.345 24 12.56 24c-2.367 0-5.088-.64-7.24-2.43c-2.193-1.826-3.64-4.721-3.64-8.898c0-4.086 1.04-7.263 2.904-9.437m10.49 9.461a9.2 9.2 0 0 0-1.917-.192c-1.79 0-2.514.453-2.807.767c-.297.318-.386.743-.36 1.19c.02.354.182.709.507.97c.32.259.899.503 1.877.431c.82-.06 1.52-.455 2.02-1.213c.312-.474.562-1.12.68-1.953" clipRule="evenodd"/></svg>
                    </button>
                    <!-- TikTok -->
                    <button onclick="addShape('icon-tiktok')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="TikTok">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M16.6 5.82s.51.5 0 0A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6c0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64c0 3.33 2.76 5.7 5.69 5.7c3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3s-1.88.09-3.24-1.48"/></svg>
                    </button>
                    <!-- Email -->
                    <button onclick="addShape('icon-email')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Email">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M19 4H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3m0 2l-6.5 4.47a1 1 0 0 1-1 0L5 6Z"/></svg>
                    </button>
                    <!-- Phone -->
                    <button onclick="addShape('icon-phone')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Phone">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="m16.556 12.906l-.455.453s-1.083 1.076-4.038-1.862s-1.872-4.014-1.872-4.014l.286-.286c.707-.702.774-1.83.157-2.654L9.374 2.86C8.61 1.84 7.135 1.705 6.26 2.575l-1.57 1.56c-.433.432-.723.99-.688 1.61c.09 1.587.808 5 4.812 8.982c4.247 4.222 8.232 4.39 9.861 4.238c.516-.048.964-.31 1.325-.67l1.42-1.412c.96-.953.69-2.588-.538-3.255l-1.91-1.039c-.806-.437-1.787-.309-2.417.317"/></svg>
                    </button>
                    <!-- Mobile -->
                    <button onclick="addShape('icon-mobile')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Mobile">
                        <svg class="w-12 h-12" viewBox="0 0 15 24"><path fill="#D1D5DB" d="M12.75 0H2.25A2.25 2.25 0 0 0 0 2.25v19.5A2.25 2.25 0 0 0 2.25 24h10.5A2.25 2.25 0 0 0 15 21.75V2.25A2.25 2.25 0 0 0 12.75 0M7.5 22.5a1.498 1.498 0 1 1 .002-2.996A1.498 1.498 0 0 1 7.5 22.5h-.001z"/></svg>
                    </button>
                    <!-- Phone Call -->
                    <button onclick="addShape('icon-phone-call')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Phone Call">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M20 10.999h2C22 5.869 18.127 2 12.99 2v2C17.052 4 20 6.943 20 10.999"/><path fill="#D1D5DB" d="M13 8c2.103 0 3 .897 3 3h2c0-3.225-1.775-5-5-5zm3.422 5.443a1 1 0 0 0-1.391.043l-2.393 2.461c-.576-.11-1.734-.471-2.926-1.66c-1.192-1.193-1.553-2.354-1.66-2.926l2.459-2.394a1 1 0 0 0 .043-1.391L6.859 3.513a1 1 0 0 0-1.391-.087l-2.17 1.861a1 1 0 0 0-.29.649c-.015.25-.301 6.172 4.291 10.766C11.305 20.707 16.323 21 17.705 21c.202 0 .326-.006.359-.008a1 1 0 0 0 .648-.291l1.86-2.171a1 1 0 0 0-.086-1.391z"/></svg>
                    </button>
                    <!-- Zoom -->
                    <button onclick="addShape('icon-zoom')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Zoom">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" fillRule="evenodd" d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12S5.373 0 12 0s12 5.373 12 12M6 16.2h9V9.6a1.8 1.8 0 0 0-1.8-1.8h-9v6.6A1.8 1.8 0 0 0 6 16.2m10.2-2.4l3.6 2.4V7.8l-3.6 2.4z" clipRule="evenodd"/></svg>
                    </button>
                    <!-- Zoom Alt -->
                    <button onclick="addShape('icon-zoom-alt')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition" title="Zoom Alt">
                        <svg class="w-12 h-12" viewBox="0 0 24 24"><path fill="#D1D5DB" d="M1.984 7.506v6.74c.006 1.524 1.361 2.75 3.014 2.745h10.693c.303 0 .549-.225.549-.498v-6.74c-.008-1.523-1.363-2.75-3.014-2.744H2.531c-.302 0-.547.224-.547.497m14.936 2.63l4.416-2.963c.383-.292.68-.219.68.309v9.036c0 .601-.363.528-.68.309L16.92 13.87z"/></svg>
                    </button>
                </div>

                <h3 class="text-sm font-semibold text-gray-700 mb-3 mt-6">Gradients</h3>
                <div class="grid grid-cols-4 gap-3">
                    <!-- Gradient Rectangle -->
                    <button onclick="addGradientShape('rect')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition overflow-hidden">
                        <div class="w-12 h-12 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    </button>
                    <!-- Gradient Circle -->
                    <button onclick="addGradientShape('circle')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition overflow-hidden">
                        <div class="w-12 h-12 rounded-full" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                    </button>
                    <!-- Gradient Star -->
                    <button onclick="addGradientShape('star')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="starGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ffecd2;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#fcb69f;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <polygon points="50,15 61,40 88,40 67,56 75,85 50,68 25,85 33,56 12,40 39,40" fill="url(#starGrad)"/>
                        </svg>
                    </button>
                    <!-- Gradient Heart -->
                    <button onclick="addGradientShape('heart')" class="aspect-square bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-12 h-12" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="heartGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#fa709a;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#fee140;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <path d="M50,90 C50,90 10,60 10,35 C10,20 20,10 30,10 C40,10 50,20 50,20 C50,20 60,10 70,10 C80,10 90,20 90,35 C90,60 50,90 50,90 Z" fill="url(#heartGrad)"/>
                        </svg>
                    </button>
                </div>

                <h3 class="text-sm font-semibold text-gray-700 mb-3 mt-6">Lines</h3>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Straight Line -->
                    <button onclick="addShape('line')" class="h-16 bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-16 h-8" viewBox="0 0 100 50">
                            <line x1="10" y1="25" x2="90" y2="25" stroke="#D1D5DB" stroke-width="3"/>
                        </svg>
                    </button>
                    <!-- Arrow Line -->
                    <button onclick="addShape('arrow-line')" class="h-16 bg-gray-50 hover:bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 transition">
                        <svg class="w-16 h-8" viewBox="0 0 100 50">
                            <defs>
                                <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                                    <polygon points="0 0, 10 3.5, 0 7" fill="#D1D5DB" />
                                </marker>
                            </defs>
                            <line x1="10" y1="25" x2="90" y2="25" stroke="#D1D5DB" stroke-width="3" marker-end="url(#arrowhead)"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Layers Tab Content -->
            <div id="layersContent" class="hidden">
                <div class="mb-3">
                    <h3 class="text-xs font-semibold text-gray-700 mb-2">Canvas Objects</h3>
                    <p class="text-xs text-gray-500">Click to select, drag to reorder</p>
                </div>
                <div id="layersList" class="space-y-1">
                    <p id="noLayersMessage" class="text-gray-400 text-sm text-center py-8">No objects yet</p>
                </div>
            </div>

            <!-- Properties Tab Content -->
            <div id="propertiesContent">
                <div id="textProperties" class="space-y-3">
                    <p class="text-gray-400 text-sm text-center py-8">Select an object to edit properties</p>
                </div>

                <!-- Image Properties Form -->
                <div id="imagePropertiesForm" class="hidden">
                    <!-- Accordion Layout -->
                    <div class="space-y-1">
                        <!-- General Accordion -->
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <button onclick="toggleImageAccordion('general')" class="w-full flex items-center justify-between px-3 py-2.5 bg-white hover:bg-gray-50 transition">
                                <span class="text-xs font-medium text-gray-700">General</span>
                                <svg id="iconGeneral" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="accordionGeneral" class="accordion-content px-3">
                                <!-- Flip -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Flip</label>
                                    <div class="flex gap-2">
                                        <button onclick="flipImage('horizontal')" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            <span class="text-gray-700">Horizontal</span>
                                        </button>
                                        <button onclick="flipImage('vertical')" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                            <span class="text-gray-700">Vertical</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Fit to Page -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Adjust</label>
                                    <button onclick="fitToPage()" class="w-full h-9 flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                        </svg>
                                        <span class="text-gray-700">Fit to Page</span>
                                    </button>
                                </div>

                                <!-- Crop -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Crop</label>
                                    <button onclick="cropImage()" id="cropBtn" class="w-full h-9 flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                                        </svg>
                                        <span class="text-gray-700">Crop Image</span>
                                    </button>
                                </div>

                                <!-- Remove Background -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Background</label>
                                    <button onclick="removeImageBackground()" id="removeBgBtn" class="w-full h-9 flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="text-gray-700">Remove BG</span>
                                    </button>
                                </div>

                                <!-- Transparency -->
                                <div class="py-3">
                                    <label class="text-xs text-gray-600 block mb-2">Transparency</label>
                                    <input type="range" id="imageOpacity" min="0" max="10" value="10" onchange="updateImageOpacity()" class="w-full">
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>0</span>
                                        <span>10</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Effect Accordion -->
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <button onclick="toggleImageAccordion('effect')" class="w-full flex items-center justify-between px-3 py-2.5 bg-white hover:bg-gray-50 transition">
                                <span class="text-xs font-medium text-gray-700">Effect</span>
                                <svg id="iconEffect" class="w-4 h-4 text-gray-500 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="accordionEffect" class="accordion-content hidden px-3">
                                <!-- Filter Presets -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Presets</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div onclick="applyImageFilter('original')" class="cursor-pointer text-center">
                                            <div class="w-full h-12 bg-gradient-to-br from-white to-gray-200 rounded mb-1 border border-gray-300"></div>
                                            <span class="text-xs text-gray-700">Original</span>
                                        </div>
                                        <div onclick="applyImageFilter('grayscale')" class="cursor-pointer text-center">
                                            <div class="w-full h-12 bg-gradient-to-br from-gray-400 to-gray-600 rounded mb-1"></div>
                                            <span class="text-xs text-gray-700">Grayscale</span>
                                        </div>
                                        <div onclick="applyImageFilter('sepia')" class="cursor-pointer text-center">
                                            <div class="w-full h-12 bg-gradient-to-br from-yellow-600 to-amber-800 rounded mb-1"></div>
                                            <span class="text-xs text-gray-700">Sepia</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Blur -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Blur</label>
                                    <input type="range" id="imageBlur" min="0" max="10" value="0" onchange="updateImageBlur()" class="w-full">
                                </div>

                                <!-- Brightness -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Brightness</label>
                                    <input type="range" id="imageBrightness" min="0" max="2" step="0.1" value="1" onchange="updateImageEffect()" class="w-full">
                                </div>

                                <!-- Contrast -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Contrast</label>
                                    <input type="range" id="imageContrast" min="-100" max="100" value="0" onchange="updateImageEffect()" class="w-full">
                                </div>

                                <!-- Hue -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Hue</label>
                                    <input type="range" id="imageHue" min="0" max="359" value="0" onchange="updateImageEffect()" class="w-full">
                                </div>

                                <!-- Saturation -->
                                <div class="py-3 border-b border-gray-100">
                                    <label class="text-xs text-gray-600 block mb-2">Saturation</label>
                                    <input type="range" id="imageSaturation" min="-2" max="10" step="0.1" value="0" onchange="updateImageEffect()" class="w-full">
                                </div>

                                <!-- Luminance -->
                                <div class="py-3">
                                    <label class="text-xs text-gray-600 block mb-2">Luminance</label>
                                    <input type="range" id="imageLuminance" min="-2" max="2" step="0.1" value="0" onchange="updateImageEffect()" class="w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Clipping Mask Accordion -->
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <button onclick="toggleImageAccordion('masking')" class="w-full flex items-center justify-between px-3 py-2.5 bg-white hover:bg-gray-50 transition">
                                <span class="text-xs font-medium text-gray-700">Clipping Mask</span>
                                <svg id="iconMasking" class="w-4 h-4 text-gray-500 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="accordionMasking" class="accordion-content hidden px-3">
                                <div class="py-3">
                                    <!-- Shapes Grid -->
                                    <div class="grid grid-cols-3 gap-3 mb-3">
                                        <div onclick="applyClippingMask('rect')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <div class="w-12 h-12 bg-gray-300 rounded"></div>
                                        </div>
                                        <div onclick="applyClippingMask('circle')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <div class="w-12 h-12 bg-gray-300 rounded-full"></div>
                                        </div>
                                        <div onclick="applyClippingMask('triangle')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <polygon points="50,15 90,85 10,85" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('star')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <polygon points="50,10 61,40 95,40 68,60 79,90 50,70 21,90 32,60 5,40 39,40" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('pentagon')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <polygon points="50,10 90,40 75,85 25,85 10,40" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('hexagon')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <polygon points="50,5 90,30 90,70 50,95 10,70 10,30" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('diamond')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <polygon points="50,10 90,50 50,90 10,50" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('heart')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <path d="M50,90 C50,90 10,60 10,35 C10,20 20,10 30,10 C40,10 50,20 50,20 C50,20 60,10 70,10 C80,10 90,20 90,35 C90,60 50,90 50,90 Z" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                        <div onclick="applyClippingMask('waterdrop')" class="aspect-square bg-white border border-gray-200 rounded-lg flex items-center justify-center hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                            <svg class="w-12 h-12" viewBox="0 0 100 100">
                                                <path d="M 50 10 C 50 10 25 40 25 60 C 25 75 35 90 50 90 C 65 90 75 75 75 60 C 75 40 50 10 50 10 Z" fill="#D1D5DB"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Remove Mask Button -->
                                    <button id="removeMaskBtn" onclick="removeMask()" class="hidden w-full h-9 flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 rounded transition text-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span>Remove Mask</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Template Variables (Admin Only) -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="border-t border-gray-200 mt-3 pt-3">
                        <label class="text-xs text-gray-600 block mb-2">Template Variable</label>
                        <select id="imageVariableType" onchange="setImageAsVariable()" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                            <option value="">Not a variable</option>
                            <option value="brand_logo">Brand Logo</option>
                            <option value="featured_image">Featured Image</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Mark this image as template variable</p>
                    </div>
                    @endif

                    <!-- Others Section (Standalone) -->
                    <div class="border-t border-gray-200 mt-3 pt-3">
                        <div class="flex gap-2">
                            <button onclick="duplicateImage()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700">Duplicate</span>
                            </button>
                            <button onclick="deleteImage()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="text-gray-700">Delete</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Shape Properties Form -->
                <div id="shapePropertiesForm" class="hidden">
                    <!-- Fill Type Toggle -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Fill Type</label>
                        <div class="flex gap-2">
                            <button onclick="setFillType('solid')" id="fillTypeSolid" class="flex-1 h-9 flex items-center justify-center gap-1 bg-indigo-100 border border-indigo-300 rounded transition text-xs">
                                <span class="text-indigo-700 font-medium">Solid</span>
                            </button>
                            <button onclick="setFillType('gradient')" id="fillTypeGradient" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 border border-gray-200 rounded transition text-xs">
                                <span class="text-gray-700">Gradient</span>
                            </button>
                        </div>
                    </div>

                    <!-- Solid Fill Color -->
                    <div id="solidFillControl" class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Fill Color</label>
                        <button id="shapeFillPicker" class="w-full h-10 rounded border border-gray-300"></button>
                    </div>

                    <!-- Gradient Controls -->
                    <div id="gradientFillControls" class="hidden">
                        <div class="py-3 border-b border-gray-100">
                            <label class="text-xs text-gray-600 block mb-2">Start Color</label>
                            <button id="gradientStartColorPicker" class="w-full h-10 rounded border border-gray-300"></button>
                        </div>
                        <div class="py-3 border-b border-gray-100">
                            <label class="text-xs text-gray-600 block mb-2">End Color</label>
                            <button id="gradientEndColorPicker" class="w-full h-10 rounded border border-gray-300"></button>
                        </div>
                        <div class="py-3 border-b border-gray-100">
                            <label class="text-xs text-gray-600 block mb-2">Gradient Angle</label>
                            <input type="range" id="gradientAngle" min="0" max="360" value="135" onchange="updateGradientAngle()" class="w-full">
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0°</span>
                                <span id="gradientAngleValue">135°</span>
                                <span>360°</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stroke Color -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Stroke Color</label>
                        <button id="shapeStrokePicker" class="w-full h-10 rounded border border-gray-300"></button>
                    </div>

                    <!-- Stroke Width -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Stroke Width</label>
                        <input type="number" id="shapeStrokeWidth" onchange="updateShapeStrokeWidth()" value="0" min="0" max="20" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                    </div>

                    <!-- Border Radius (only for Rect) -->
                    <div id="borderRadiusControl" class="py-3 border-b border-gray-100 hidden">
                        <label class="text-xs text-gray-600 block mb-2">Border Radius</label>
                        <input type="range" id="shapeBorderRadius" min="0" max="100" value="0" onchange="updateShapeBorderRadius()" class="w-full">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>0</span>
                            <span id="borderRadiusValue">0</span>
                            <span>100</span>
                        </div>
                    </div>

                    <!-- Transparency -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Transparency</label>
                        <input type="range" id="shapeOpacity" min="0" max="10" value="10" onchange="updateShapeOpacity()" class="w-full">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>0</span>
                            <span>10</span>
                        </div>
                    </div>

                    <!-- Template Variables (Admin Only) -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Color Variable</label>
                        <select id="shapeColorVariable" onchange="setShapeColorAsVariable()" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                            <option value="">Not a variable</option>
                            <option value="brand_color_primary">Primary Brand Color</option>
                            <option value="brand_color_secondary">Secondary Brand Color</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Mark shape color as brand variable</p>
                    </div>
                    @endif

                    <!-- Other Actions -->
                    <div class="py-3">
                        <label class="text-xs text-gray-600 block mb-2">Others</label>
                        <div class="flex gap-2">
                            <button onclick="duplicateShape()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700">Duplicate</span>
                            </button>
                            <button onclick="deleteShape()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="text-gray-700">Delete</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="textPropertiesForm" class="hidden">
                    <!-- Font Family -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Font Family</label>
                        <div class="relative">
                            <input type="text" id="fontFamilyInput" readonly onclick="toggleFontDropdown()" value="Inter" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm cursor-pointer">
                            <div id="fontFamilyDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-48 overflow-y-auto">
                                <div onclick="selectFont('Inter')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">Inter</div>
                                <div onclick="selectFont('Poppins')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">Poppins</div>
                                <div onclick="selectFont('Roboto')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">Roboto</div>
                                <div onclick="selectFont('Montserrat')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">Montserrat</div>
                            </div>
                        </div>
                    </div>

                    <!-- Font Size & Weight -->
                    <div class="py-3 border-b border-gray-100 grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-2">Size</label>
                            <input type="number" id="fontSize" onchange="updateTextSize()" value="40" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-2">Weight</label>
                            <div class="relative">
                                <input type="text" id="fontWeightInput" readonly onclick="toggleWeightDropdown()" value="400" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm cursor-pointer">
                                <div id="fontWeightDropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-48 overflow-y-auto">
                                    <div onclick="selectWeight('300')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">300</div>
                                    <div onclick="selectWeight('400')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">400</div>
                                    <div onclick="selectWeight('500')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">500</div>
                                    <div onclick="selectWeight('600')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">600</div>
                                    <div onclick="selectWeight('700')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">700</div>
                                    <div onclick="selectWeight('800')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">800</div>
                                    <div onclick="selectWeight('900')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">900</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text Color -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Color</label>
                        <button id="textColorPicker" class="w-full h-10 rounded border border-gray-300"></button>
                    </div>

                    <!-- Text Align -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Align</label>
                        <div class="flex gap-2">
                            <button onclick="setTextAlign('left')" id="alignLeft" class="flex-1 h-9 flex items-center justify-center bg-gray-50 hover:bg-gray-100 rounded transition">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                                </svg>
                            </button>
                            <button onclick="setTextAlign('center')" id="alignCenter" class="flex-1 h-9 flex items-center justify-center bg-gray-50 hover:bg-gray-100 rounded transition">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/>
                                </svg>
                            </button>
                            <button onclick="setTextAlign('right')" id="alignRight" class="flex-1 h-9 flex items-center justify-center bg-gray-50 hover:bg-gray-100 rounded transition">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Transparency -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Transparency</label>
                        <input type="range" id="textOpacity" min="0" max="10" value="10" onchange="updateTextOpacity()" class="w-full">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>0</span>
                            <span>10</span>
                        </div>
                    </div>

                    <!-- Text Styling Button -->
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Advanced Styling</label>
                        <button onclick="openTextStylingModal()" class="w-full h-9 flex items-center justify-center gap-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded transition text-xs">
                            <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <span class="text-indigo-700 font-medium">Edit Text Styling</span>
                        </button>
                    </div>

                    <!-- Template Variables (Admin Only) -->
                    @if(auth()->user()->role === 'super_admin')
                    <div class="py-3 border-b border-gray-100">
                        <label class="text-xs text-gray-600 block mb-2">Template Variable</label>
                        <select id="textVariableType" onchange="setTextAsVariable()" class="w-full bg-white border border-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                            <option value="">Not a variable</option>
                            <option value="article_title">Article Title</option>
                            <option value="article_excerpt">Article Excerpt</option>
                            <option value="article_author">Author Name</option>
                            <option value="website_url">Website URL</option>
                            <option value="brand_font">Brand Font Family</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Mark this text as template variable</p>
                    </div>
                    @endif

                    <!-- Other Actions -->
                    <div class="py-3">
                        <label class="text-xs text-gray-600 block mb-2">Others</label>
                        <div class="flex gap-2">
                            <button onclick="duplicateText()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700">Duplicate</span>
                            </button>
                            <button onclick="deleteText()" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="text-gray-700">Delete</span>
                            </button>
                            <button onclick="toggleUppercase()" id="uppercaseBtn" class="flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 hover:bg-gray-100 rounded transition text-xs">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-gray-700">ABC</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="toastMessage">Image uploaded successfully!</span>
</div>

<!-- Templates Modal -->
<div id="templatesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Choose Template</h3>
            <button onclick="closeTemplatesModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Templates Grid -->
        <div class="p-4 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div id="templatesGrid" class="grid grid-cols-3 gap-4">
                <!-- Templates will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div id="saveTemplateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 id="saveTemplateModalTitle" class="text-lg font-semibold text-gray-900">Save as Template</h3>
            <button onclick="closeSaveTemplateModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="saveTemplateForm" class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                <input type="text" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="My Template">
            </div>

            @if(auth()->user()->role === 'super_admin')
            <!-- Admin Only: Category Selection + Create New -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category (Admin Only)</label>
                <div class="flex gap-2">
                    <select name="category_id" id="categorySelect"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">No Category (User Template)</option>
                        <!-- Categories will be loaded via JavaScript -->
                    </select>
                    <button type="button" onclick="openQuickAddCategory()"
                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Admin Only: Make Public Checkbox -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_public" value="1"
                        class="w-4 h-4 text-purple-600 rounded focus:ring-2 focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700">Make this a Platform Template (visible to all users)</span>
                </label>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template Type</label>
                <select name="template_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="article">Article (with content variables)</option>
                    <option value="standalone">Standalone (no variables)</option>
                </select>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeSaveTemplateModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" id="saveTemplateButton"
                    class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Save Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Category Modal (Admin Only) -->
<div id="quickAddCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-sm">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Add New Category</h3>
            <button onclick="closeQuickAddCategory()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="quickAddCategoryForm" class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                <input type="text" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="e.g., Social Media">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Icon (Heroicons name)</label>
                <input type="text" name="icon"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="e.g., photo, document-text">
                <p class="mt-1 text-xs text-gray-500">Optional. Use Heroicons icon names</p>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeQuickAddCategory()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resize Modal -->
<div id="resizeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-sm">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Resize Canvas</h3>
            <button onclick="closeResizeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-4 space-y-4">
            <div class="flex items-center justify-between mb-4">
                <label class="text-sm font-medium text-gray-700">Use magic resize</label>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="magicResize" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Width (px)</label>
                    <input type="number" id="canvasWidth" min="100" max="4000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Height (px)</label>
                    <input type="number" id="canvasHeight" min="100" max="4000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <button onclick="applyResize()"
                    class="w-full px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                Resize
            </button>

            <div class="pt-2 border-t">
                <p class="text-xs font-semibold text-gray-700 mb-3">Presets</p>

                <div class="space-y-2 mb-3">
                    <p class="text-xs font-medium text-gray-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        Instagram
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setPreset(1080, 1350)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Post</div>
                            <div class="text-gray-500">1080×1350</div>
                        </button>
                        <button onclick="setPreset(1080, 1920)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Story</div>
                            <div class="text-gray-500">1080×1920</div>
                        </button>
                        <button onclick="setPreset(1080, 1080)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Square</div>
                            <div class="text-gray-500">1080×1080</div>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    <p class="text-xs font-medium text-gray-600 flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setPreset(1200, 630)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Post (Land)</div>
                            <div class="text-gray-500">1200×630</div>
                        </button>
                        <button onclick="setPreset(1080, 1080)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Post (Sq)</div>
                            <div class="text-gray-500">1080×1080</div>
                        </button>
                        <button onclick="setPreset(1200, 1500)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Cover</div>
                            <div class="text-gray-500">1200×1500</div>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    <p class="text-xs font-medium text-gray-600 flex items-center gap-1">
                        <svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        Twitter
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setPreset(1200, 675)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Post</div>
                            <div class="text-gray-500">1200×675</div>
                        </button>
                        <button onclick="setPreset(1500, 500)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Header</div>
                            <div class="text-gray-500">1500×500</div>
                        </button>
                        <button onclick="setPreset(1080, 1080)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Square</div>
                            <div class="text-gray-500">1080×1080</div>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 mb-3">
                    <p class="text-xs font-medium text-gray-600 flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        LinkedIn
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setPreset(1200, 627)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Post</div>
                            <div class="text-gray-500">1200×627</div>
                        </button>
                        <button onclick="setPreset(1584, 396)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Cover</div>
                            <div class="text-gray-500">1584×396</div>
                        </button>
                        <button onclick="setPreset(1080, 1080)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Square</div>
                            <div class="text-gray-500">1080×1080</div>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-600 flex items-center gap-1">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        YouTube
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button onclick="setPreset(1280, 720)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Thumbnail</div>
                            <div class="text-gray-500">1280×720</div>
                        </button>
                        <button onclick="setPreset(2560, 1440)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Channel</div>
                            <div class="text-gray-500">2560×1440</div>
                        </button>
                        <button onclick="setPreset(1080, 1920)" class="px-2 py-1.5 text-xs border hover:bg-gray-50 rounded">
                            <div class="font-medium">Short</div>
                            <div class="text-gray-500">1080×1920</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md shadow-xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Export Design</h3>
            <button onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Format Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Export Format</label>
                <div class="grid grid-cols-3 gap-3">
                    <button onclick="selectExportFormat('png')" id="format-png" class="export-format-btn px-4 py-3 border-2 border-purple-600 bg-purple-50 text-purple-700 rounded-lg text-sm font-semibold transition">
                        PNG
                    </button>
                    <button onclick="selectExportFormat('jpg')" id="format-jpg" class="export-format-btn px-4 py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition">
                        JPG
                    </button>
                    <button onclick="selectExportFormat('webp')" id="format-webp" class="export-format-btn px-4 py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition">
                        WebP
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">PNG supports transparency, JPG is smaller file size</p>
            </div>

            <!-- Quality Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Quality</label>
                <div class="grid grid-cols-3 gap-3">
                    <button onclick="selectExportQuality('low')" id="quality-low" class="export-quality-btn px-4 py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition">
                        Low<br><span class="text-xs font-normal">Fast</span>
                    </button>
                    <button onclick="selectExportQuality('medium')" id="quality-medium" class="export-quality-btn px-4 py-3 border-2 border-purple-600 bg-purple-50 text-purple-700 rounded-lg text-sm font-semibold transition">
                        Medium<br><span class="text-xs font-normal">Balanced</span>
                    </button>
                    <button onclick="selectExportQuality('high')" id="quality-high" class="export-quality-btn px-4 py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition">
                        High<br><span class="text-xs font-normal">Best</span>
                    </button>
                </div>
            </div>

            <!-- Export Action -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Export To</label>
                <div class="flex gap-3">
                    <button onclick="performExport('download')" class="flex-1 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </button>
                    <button onclick="performExport('library')" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                        </svg>
                        Save to Library
                    </button>
                </div>
            </div>

            <!-- Batch Export (Future Feature) -->
            <div class="border-t border-gray-200 pt-4">
                <button onclick="openBatchExport()" class="w-full px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/>
                    </svg>
                    Batch Export (Multiple Sizes)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Text Styling Modal -->
<div id="textStylingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-2xl shadow-xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Text Styling Editor</h3>
            <button onclick="closeTextStylingModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Info Banner -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-blue-800">
                        <p class="font-semibold mb-1">Best for short text (1-5 words)</p>
                        <p>Multi-color text will be converted to grouped objects and <strong>cannot be edited as text anymore</strong>. Use for final touches only.</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="text-sm text-gray-600 mb-2 block">Click on any word to change its color:</label>
                <div id="textWordsContainer" class="p-4 bg-gray-50 rounded border border-gray-200 min-h-[100px] flex flex-wrap gap-2 items-center">
                    <!-- Words will be inserted here dynamically -->
                </div>
            </div>

            <div class="mb-4">
                <label class="text-sm text-gray-600 mb-2 block">Selected Word Color:</label>
                <input type="color" id="wordColorPicker" value="#000000" class="w-full h-12 border border-gray-300 rounded cursor-pointer">
            </div>

            <div class="text-xs text-gray-500 mb-4">
                <p>💡 Tip: Click a word above, then choose a color to apply</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200 bg-gray-50">
            <button onclick="closeTextStylingModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition text-sm">
                Cancel
            </button>
            <button onclick="applyTextStyling()" class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition text-sm">
                Apply Styling
            </button>
        </div>
    </div>
</div>

<!-- Media Library Modal -->
<x-media-library />

@push('scripts')
<!-- Konva.js CDN -->
<script src="https://unpkg.com/konva@9/konva.min.js"></script>
<!-- Pickr Color Picker -->
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css"/>

<script>
// Global variables
let stage, layer, transformer;
let selectedObject = null;

// Color pickers
let gradientStartPicker, gradientEndPicker, shapeFillPicker, shapeStrokePicker, textColorPicker;

// Flag to prevent save handler from executing when updating Pickr programmatically
let isUpdatingPickrProgrammatically = false;

// Calculate optimal zoom for canvas to fit in viewport
function calculateOptimalZoom(canvasWidth, canvasHeight) {
    const maxDisplayHeight = 800; // Max viewport height
    const maxDisplayWidth = 650;  // Max viewport width

    // Calculate zoom needed to fit height
    const zoomForHeight = maxDisplayHeight / canvasHeight;
    // Calculate zoom needed to fit width
    const zoomForWidth = maxDisplayWidth / canvasWidth;

    // Use the smaller zoom to ensure both dimensions fit
    const optimalZoom = Math.min(zoomForHeight, zoomForWidth, 0.5); // Max 50%

    return optimalZoom;
}

// Helper function to create color picker with consistent config
function createColorPicker(elementId, defaultColor = '#000000') {
    return Pickr.create({
        el: elementId,
        theme: 'nano',
        default: defaultColor,
        components: {
            preview: true,
            opacity: true,
            hue: true,
            interaction: {
                hex: true,
                rgba: true,
                input: true,
                save: true
            }
        }
    });
}

// Helper function to convert RGBA array to CSS rgba string
function rgbaToString(rgba) {
    return `rgba(${Math.round(rgba[0])}, ${Math.round(rgba[1])}, ${Math.round(rgba[2])}, ${rgba[3]})`;
}

// Initialize color pickers
function initColorPickers() {
    // Gradient Start Color Picker
    gradientStartPicker = createColorPicker('#gradientStartColorPicker', '#667eea');

    gradientStartPicker.on('change', (color) => {
        // Ignore if we're updating Pickr programmatically
        if (isUpdatingPickrProgrammatically) {
            return;
        }

        if (color && selectedObject) {
            const rgba = color.toRGBA();
            const hex = color.toHEXA().toString().substring(0, 7);
            const opacity = Math.round(rgba[3] * 100);

            console.log('Start color changed:', hex, 'opacity:', opacity);

            // Update attributes
            selectedObject.setAttr('gradientStartColor', hex);
            selectedObject.setAttr('gradientStartOpacity', opacity);

            // Get current end color from ACTUAL gradient
            const colorStops = selectedObject.fillLinearGradientColorStops();
            let endColor = '#764ba2';
            let endOpacity = 100;

            if (colorStops && colorStops.length >= 4) {
                const endRgbaMatch = colorStops[3].match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
                if (endRgbaMatch) {
                    const r = parseInt(endRgbaMatch[1]);
                    const g = parseInt(endRgbaMatch[2]);
                    const b = parseInt(endRgbaMatch[3]);
                    const a = parseFloat(endRgbaMatch[4] || 1);
                    endColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
                    endOpacity = Math.round(a * 100);
                }
            }

            // Force update gradient with new values
            applyGradientToShape(hex, opacity, endColor, endOpacity);
        }
    });

    // Gradient End Color Picker
    gradientEndPicker = createColorPicker('#gradientEndColorPicker', '#764ba2');

    gradientEndPicker.on('change', (color) => {
        // Ignore if we're updating Pickr programmatically
        if (isUpdatingPickrProgrammatically) {
            return;
        }

        if (color && selectedObject) {
            const rgba = color.toRGBA();
            const hex = color.toHEXA().toString().substring(0, 7);
            const opacity = Math.round(rgba[3] * 100);

            console.log('End color changed:', hex, 'opacity:', opacity);

            // Update attributes
            selectedObject.setAttr('gradientEndColor', hex);
            selectedObject.setAttr('gradientEndOpacity', opacity);

            // Get current start color from ACTUAL gradient
            const colorStops = selectedObject.fillLinearGradientColorStops();
            let startColor = '#667eea';
            let startOpacity = 100;

            if (colorStops && colorStops.length >= 4) {
                const startRgbaMatch = colorStops[1].match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
                if (startRgbaMatch) {
                    const r = parseInt(startRgbaMatch[1]);
                    const g = parseInt(startRgbaMatch[2]);
                    const b = parseInt(startRgbaMatch[3]);
                    const a = parseFloat(startRgbaMatch[4] || 1);
                    startColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
                    startOpacity = Math.round(a * 100);
                }
            }

            // Force update gradient with new values
            applyGradientToShape(startColor, startOpacity, hex, opacity);
        }
    });

    // Shape Fill Color Picker
    shapeFillPicker = createColorPicker('#shapeFillPicker', '#D1D5DB');

    shapeFillPicker.on('change', (color) => {
        if (isUpdatingPickrProgrammatically) {
            return;
        }

        if (color && selectedObject) {
            const rgba = color.toRGBA();
            const rgbaString = rgbaToString(rgba);
            selectedObject.fill(rgbaString);
            layer.batchDraw();
        }
    });

    // Shape Stroke Color Picker
    shapeStrokePicker = createColorPicker('#shapeStrokePicker', '#000000');

    shapeStrokePicker.on('change', (color) => {
        if (isUpdatingPickrProgrammatically) {
            return;
        }

        if (color && selectedObject) {
            const rgba = color.toRGBA();
            const rgbaString = rgbaToString(rgba);
            selectedObject.stroke(rgbaString);
            layer.batchDraw();
        }
    });

    // Text Color Picker
    textColorPicker = createColorPicker('#textColorPicker', '#000000');

    textColorPicker.on('change', (color) => {
        if (isUpdatingPickrProgrammatically) {
            return;
        }

        if (color && selectedObject) {
            const rgba = color.toRGBA();
            const rgbaString = rgbaToString(rgba);
            selectedObject.fill(rgbaString);
            layer.batchDraw();
        }
    });
}

// Listen for media selection from Media Library
window.addEventListener('media-selected', function(event) {
    const media = event.detail;
    if (media && media.full_url) {
        addImageToCanvas(media.full_url);
    }
});

// Tab switching
function switchTab(tabName) {
    // Update tab buttons
    const layersTab = document.getElementById('layersTab');
    const propertiesTab = document.getElementById('propertiesTab');
    const assetsTab = document.getElementById('assetsTab');
    const layersContent = document.getElementById('layersContent');
    const propertiesContent = document.getElementById('propertiesContent');
    const assetsContent = document.getElementById('assetsContent');

    // Reset all tabs
    [layersTab, propertiesTab, assetsTab].forEach(tab => {
        tab.classList.remove('text-purple-600', 'border-purple-600');
        tab.classList.add('text-gray-500', 'border-transparent');
    });

    // Hide all content
    [layersContent, propertiesContent, assetsContent].forEach(content => {
        content.classList.add('hidden');
    });

    // Activate selected tab
    if (tabName === 'layers') {
        layersTab.classList.remove('text-gray-500', 'border-transparent');
        layersTab.classList.add('text-purple-600', 'border-purple-600');
        layersContent.classList.remove('hidden');
    } else if (tabName === 'assets') {
        assetsTab.classList.remove('text-gray-500', 'border-transparent');
        assetsTab.classList.add('text-purple-600', 'border-purple-600');
        assetsContent.classList.remove('hidden');
    } else {
        propertiesTab.classList.remove('text-gray-500', 'border-transparent');
        propertiesTab.classList.add('text-purple-600', 'border-purple-600');
        propertiesContent.classList.remove('hidden');
    }
}

// Open Elements Panel
function openElementsPanel() {
    switchTab('assets');
}

// Initialize Konva Stage
function initKonva() {
    // Default canvas size (Instagram Post: 1080x1350)
    const defaultWidth = 1080;
    const defaultHeight = 1350;
    const defaultZoom = calculateOptimalZoom(defaultWidth, defaultHeight);

    // Create stage with actual canvas dimensions
    stage = new Konva.Stage({
        container: 'canvasContainer',
        width: defaultWidth,
        height: defaultHeight,
        scaleX: defaultZoom,
        scaleY: defaultZoom
    });

    // Update container size
    const container = document.getElementById('canvasContainer');
    container.style.width = (defaultWidth * defaultZoom) + 'px';
    container.style.height = (defaultHeight * defaultZoom) + 'px';

    // Update zoom display
    updateZoomDisplay();

    // Create layer
    layer = new Konva.Layer();
    stage.add(layer);

    // Create transformer for resize/rotate
    transformer = new Konva.Transformer({
        enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
        rotateEnabled: true,
        borderStroke: '#4F46E5',
        borderStrokeWidth: 2,
        anchorStroke: '#4F46E5',
        anchorFill: '#FFFFFF',
        anchorSize: 10
    });
    layer.add(transformer);

    // Background (grid)
    addBackground();

    // Click on empty area deselects
    stage.on('click tap', function (e) {
        // Check if clicked on stage or background (empty area)
        if (e.target === stage || e.target.name() === 'background') {
            transformer.nodes([]);
            selectedObject = null;
            hideObjectProperties();
            updateLayersList();
            layer.batchDraw();
        }
    });

    // Delete key removes selected object
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Delete' && selectedObject) {
            selectedObject.destroy();
            transformer.nodes([]);
            selectedObject = null;
            hideObjectProperties();
            updateLayersList();
            layer.batchDraw();
            showToast('Object deleted');
        }
    });

    layer.batchDraw();
}

// Add background grid
function addBackground() {
    const bg = new Konva.Rect({
        x: 0,
        y: 0,
        width: stage.width(),
        height: stage.height(),
        fill: '#FFFFFF',
        listening: true,  // Enable click events on background
        name: 'background'
    });
    layer.add(bg);
    bg.moveToBottom();
}

// Upload image
function uploadImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    fetch('{{ route("image-editor.upload") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            addImageToCanvas(data.url);
            showToast('Image uploaded successfully!');
        }
    })
    .catch(err => {
        console.error('Upload error:', err);
        alert('Upload failed');
    });
}

// Add image to canvas
function addImageToCanvas(imageUrl) {
    const imageObj = new Image();

    // Fix CORS for external images (Unsplash)
    imageObj.crossOrigin = 'Anonymous';

    imageObj.onload = function() {
        // Calculate scale to fit
        const maxWidth = stage.width();
        const maxHeight = stage.height();
        const scale = Math.min(maxWidth / imageObj.width, maxHeight / imageObj.height);

        const konvaImage = new Konva.Image({
            x: (stage.width() / 2) - (imageObj.width * scale) / 2,
            y: (stage.height() / 2) - (imageObj.height * scale) / 2,
            image: imageObj,
            width: imageObj.width * scale,
            height: imageObj.height * scale,
            draggable: true,
            name: 'image-' + Date.now()
        });

        konvaImage.on('click tap', function() {
            selectObject(konvaImage);
        });

        layer.add(konvaImage);
        konvaImage.moveToTop();
        transformer.moveToTop();
        layer.batchDraw();

        updateLayersList();
        selectObject(konvaImage);
    };
    imageObj.src = imageUrl;
}

// Add text
// Attach double-click edit handler to text
function attachTextEditHandler(text) {
    text.on('dblclick dbltap', function() {
        // Hide text and transformer during editing
        text.hide();
        transformer.nodes([]);
        layer.batchDraw();

        // Get text position
        const textPosition = text.absolutePosition();
        const stageBox = stage.container().getBoundingClientRect();
        const scale = stage.scaleX(); // 0.5

        // Create textarea
        const textarea = document.createElement('textarea');
        document.body.appendChild(textarea);

        // Calculate position WITHOUT multiplying scale
        // absolutePosition() already returns position in stage coordinates
        const areaPosition = {
            x: stageBox.left + textPosition.x,
            y: stageBox.top + textPosition.y
        };

        // Style textarea - SCALED size manually (NO CSS transform)
        textarea.value = text.text();
        textarea.style.position = 'fixed';
        textarea.style.top = areaPosition.y + 'px';
        textarea.style.left = areaPosition.x + 'px';
        textarea.style.width = (text.width() * scale) + 'px';
        textarea.style.fontSize = (text.fontSize() * scale) + 'px';
        textarea.style.fontFamily = text.fontFamily();
        textarea.style.color = text.fill();
        textarea.style.textAlign = text.align();
        textarea.style.lineHeight = text.lineHeight();
        textarea.style.border = '2px solid #4F46E5';
        textarea.style.padding = '4px';
        textarea.style.margin = '0px';
        textarea.style.overflow = 'hidden';
        textarea.style.background = 'rgba(255, 255, 255, 0.9)';
        textarea.style.outline = 'none';
        textarea.style.resize = 'none';
        textarea.style.borderRadius = '4px';
        textarea.style.zIndex = '9999';

        // Auto-expand height (scaled)
        textarea.style.height = (text.height() * scale) + 'px';

        textarea.focus();
        textarea.select();

        // Save on Enter (without Shift)
        textarea.addEventListener('keydown', function(e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                e.preventDefault();
                text.text(textarea.value);
                text.show();
                document.body.removeChild(textarea);
                selectObject(text);
                layer.batchDraw();
            }
        });

        // Auto-resize as user types
        textarea.addEventListener('input', function() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        });

        // Save on blur (click outside)
        textarea.addEventListener('blur', function() {
            text.text(textarea.value);
            text.show();
            document.body.removeChild(textarea);
            selectObject(text);
            layer.batchDraw();
        });
    });
}

function addText() {
    const text = new Konva.Text({
        x: (stage.width() / 2) - 100,
        y: (stage.height() / 2) - 20,
        text: 'Double-click to edit',
        fontSize: 40,
        fontFamily: 'Inter',
        fill: '#000000',
        draggable: true,
        name: 'text-' + Date.now(),
        width: 200,
        wrap: 'word'
    });

    text.on('click tap', function() {
        selectObject(text);
    });

    attachTextEditHandler(text);

    layer.add(text);
    layer.batchDraw();

    updateLayersList();
    selectObject(text);
}

// Select object
function selectObject(obj) {
    selectedObject = obj;

    // DON'T move object - keep layer order intact
    // Only transformer will be moved to top (see below)

    // Load actual effect values from image (NOT reset to defaults)
    if (obj.className === 'Image') {
        // Read actual values from object
        const brightness = obj.brightness ? obj.brightness() + 1 : 1; // Convert -1 to 1 → 0 to 2
        const contrast = obj.contrast ? obj.contrast() : 0;
        const hue = obj.hue ? obj.hue() : 0;
        const saturation = obj.saturation ? obj.saturation() : 0;
        const luminance = obj.luminance ? obj.luminance() : 0;
        const blur = obj.blurRadius ? obj.blurRadius() / 2 : 0;

        // Update sliders with actual values
        document.getElementById('imageBrightness').value = brightness;
        document.getElementById('imageContrast').value = contrast;
        document.getElementById('imageHue').value = hue;
        document.getElementById('imageSaturation').value = saturation;
        document.getElementById('imageLuminance').value = luminance;
        document.getElementById('imageBlur').value = blur;
    }

    // Set nodes FIRST before any configuration
    transformer.nodes([obj]);

    // Configure transformer based on object type
    if (obj.className === 'Text') {
        // For text: enable all anchors including middle ones for width/height adjustment
        transformer.enabledAnchors([
                'top-left', 'top-center', 'top-right',
                'middle-left', 'middle-right',
                'bottom-left', 'bottom-center', 'bottom-right'
            ]);
        transformer.rotateEnabled(true);
        transformer.keepRatio(false);

        // Remove any previous transform listeners to avoid duplicates
        obj.off('transform');
        obj.off('transformend');

        // Handle transform end to finalize changes
        obj.on('transformend', function() {
            const scaleX = obj.scaleX();
            const scaleY = obj.scaleY();

            // Detect which anchor was used based on scale changes
            const isMiddleAnchor = Math.abs(scaleX - 1) > 0.01 && Math.abs(scaleY - 1) < 0.01;
            const isCornerAnchor = Math.abs(scaleX - 1) > 0.01 && Math.abs(scaleY - 1) > 0.01;

            if (isMiddleAnchor) {
                // Middle anchor: Only change width (text box size), NOT font size
                obj.width(Math.max(obj.width() * scaleX, 30));
                obj.scaleX(1);
                obj.scaleY(1);
            } else if (isCornerAnchor) {
                // Corner anchor: Scale both width AND font size proportionally
                obj.width(Math.max(obj.width() * scaleX, 30));
                obj.fontSize(Math.max(obj.fontSize() * scaleY, 10));
                obj.scaleX(1);
                obj.scaleY(1);
            }

            layer.batchDraw();
        });
    } else if (obj.className === 'Image') {
        // For images: only corner anchors for proportional resize
        transformer.enabledAnchors(['top-left', 'top-right', 'bottom-left', 'bottom-right']);
        transformer.rotateEnabled(true);
        transformer.keepRatio(true);

        // Remove transform listeners for images
        obj.off('transform');
        obj.off('transformend');
    } else {
        // For shapes: all anchors including middle ones for non-proportional resize
        transformer.enabledAnchors([
            'top-left', 'top-center', 'top-right',
            'middle-left', 'middle-right',
            'bottom-left', 'bottom-center', 'bottom-right'
        ]);
        transformer.rotateEnabled(true);
        transformer.keepRatio(false);

        // Remove transform listeners
        obj.off('transform');
        obj.off('transformend');
    }

    // CRITICAL: Move transformer to top so handles are always visible above all shapes
    transformer.moveToTop();

    layer.batchDraw();

    // Show properties in Properties tab
    if (obj.className === 'Text') {
        document.getElementById('textProperties').classList.add('hidden');
        document.getElementById('imagePropertiesForm').classList.add('hidden');
        document.getElementById('shapePropertiesForm').classList.add('hidden');
        document.getElementById('textPropertiesForm').classList.remove('hidden');
        document.getElementById('fontFamilyInput').value = obj.fontFamily();
        document.getElementById('fontSize').value = obj.fontSize();

        // Update text color Pickr with actual color from object
        isUpdatingPickrProgrammatically = true;
        if (textColorPicker) {
            textColorPicker.setColor(obj.fill() || '#000000');
        }
        setTimeout(() => {
            isUpdatingPickrProgrammatically = false;
        }, 100);

        // Update variable dropdown if object is marked as variable
        const variableDropdown = document.getElementById('textVariableType');
        if (variableDropdown) {
            variableDropdown.value = obj.getAttr('isVariable') || '';
        }
    } else if (obj.className === 'Image') {
        document.getElementById('textProperties').classList.add('hidden');
        document.getElementById('textPropertiesForm').classList.add('hidden');
        document.getElementById('shapePropertiesForm').classList.add('hidden');
        document.getElementById('imagePropertiesForm').classList.remove('hidden');
        document.getElementById('imageOpacity').value = Math.round(obj.opacity() * 10);

        // Get current blur value
        const currentBlur = obj.blurRadius() ? Math.round(obj.blurRadius() / 2) : 0;
        document.getElementById('imageBlur').value = currentBlur;

        // Update image variable dropdown if object is marked as variable
        const imageVariableDropdown = document.getElementById('imageVariableType');
        if (imageVariableDropdown) {
            imageVariableDropdown.value = obj.getAttr('isVariable') || '';
        }
    } else if (['Rect', 'Circle', 'Star', 'RegularPolygon', 'Line', 'Arrow', 'Path'].includes(obj.className)) {
        // Shape properties
        document.getElementById('textProperties').classList.add('hidden');
        document.getElementById('textPropertiesForm').classList.add('hidden');
        document.getElementById('imagePropertiesForm').classList.add('hidden');
        document.getElementById('shapePropertiesForm').classList.remove('hidden');

        // Check if shape has gradient
        const hasGradient = obj.getAttr('hasGradient');

        if (hasGradient) {
            // Show gradient controls
            setFillType('gradient');

            // Get gradient values from the ACTUAL gradient (not from cached attributes)
            const colorStops = obj.fillLinearGradientColorStops();

            // Set flag to prevent save/change handlers from executing
            isUpdatingPickrProgrammatically = true;

            if (colorStops && colorStops.length >= 4) {
                // Parse RGBA values from gradient color stops
                const startColorRGBA = colorStops[1]; // format: "rgba(r, g, b, a)"
                const endColorRGBA = colorStops[3];

                // Update Pickr with actual gradient colors
                if (gradientStartPicker && startColorRGBA) {
                    gradientStartPicker.setColor(startColorRGBA);
                }
                if (gradientEndPicker && endColorRGBA) {
                    gradientEndPicker.setColor(endColorRGBA);
                }
            }

            // Update angle slider (also protected by flag)
            const savedAngle = obj.getAttr('gradientAngle') || 135;
            document.getElementById('gradientAngle').value = savedAngle;
            document.getElementById('gradientAngleValue').textContent = savedAngle + '°';

            // Reset flag after a short delay
            setTimeout(() => {
                isUpdatingPickrProgrammatically = false;
            }, 100);
        } else {
            // Show solid color controls (without changing shape color)
            isUpdatingPickrProgrammatically = true;  // Set flag BEFORE setFillType
            setFillType('solid');

            // Update shape fill Pickr with actual color from object
            if (shapeFillPicker) {
                shapeFillPicker.setColor(obj.fill() || '#D1D5DB');
            }
            setTimeout(() => {
                isUpdatingPickrProgrammatically = false;
            }, 100);
        }

        // Update shape stroke Pickr with actual color from object
        isUpdatingPickrProgrammatically = true;
        if (shapeStrokePicker) {
            shapeStrokePicker.setColor(obj.stroke() || '#000000');
        }
        setTimeout(() => {
            isUpdatingPickrProgrammatically = false;
        }, 100);

        document.getElementById('shapeStrokeWidth').value = obj.strokeWidth() || 0;
        document.getElementById('shapeOpacity').value = Math.round(obj.opacity() * 10);

        // Show border radius control only for Rect
        const borderRadiusControl = document.getElementById('borderRadiusControl');
        if (obj.className === 'Rect') {
            borderRadiusControl.classList.remove('hidden');
            const currentRadius = obj.cornerRadius() || 0;
            document.getElementById('shapeBorderRadius').value = currentRadius;
            document.getElementById('borderRadiusValue').textContent = currentRadius;
        } else {
            borderRadiusControl.classList.add('hidden');
        }

        // Update color variable dropdown if object is marked as color variable
        const colorVariableDropdown = document.getElementById('shapeColorVariable');
        if (colorVariableDropdown) {
            colorVariableDropdown.value = obj.getAttr('colorVariable') || '';
        }
    } else {
        document.getElementById('textPropertiesForm').classList.add('hidden');
        document.getElementById('imagePropertiesForm').classList.add('hidden');
        document.getElementById('shapePropertiesForm').classList.add('hidden');
        document.getElementById('textProperties').classList.remove('hidden');
    }

    updateLayersList();
}

// Hide object properties
function hideObjectProperties() {
    document.getElementById('textPropertiesForm').classList.add('hidden');
    document.getElementById('imagePropertiesForm').classList.add('hidden');
    document.getElementById('shapePropertiesForm').classList.add('hidden');
    document.getElementById('textProperties').classList.remove('hidden');
}

// Update text properties
function updateTextFont() {
    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.fontFamily(document.getElementById('fontFamily').value);
        transformer.forceUpdate();
        layer.batchDraw();
    }
}

function updateTextSize() {
    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.fontSize(parseInt(document.getElementById('fontSize').value));
        transformer.forceUpdate();
        layer.batchDraw();
    }
}

// Font dropdown
function toggleFontDropdown() {
    document.getElementById('fontFamilyDropdown').classList.toggle('hidden');
}

function selectFont(font) {
    document.getElementById('fontFamilyInput').value = font;
    document.getElementById('fontFamilyDropdown').classList.add('hidden');
    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.fontFamily(font);

        // Update transformer to fit new text dimensions
        transformer.forceUpdate();
        layer.batchDraw();
    }
}

// Weight dropdown
function toggleWeightDropdown() {
    document.getElementById('fontWeightDropdown').classList.toggle('hidden');
}

function selectWeight(weight) {
    document.getElementById('fontWeightInput').value = weight;
    document.getElementById('fontWeightDropdown').classList.add('hidden');
    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.fontStyle(weight);

        // Update transformer to fit new text dimensions
        transformer.forceUpdate();
        layer.batchDraw();
    }
}

// Text align
function setTextAlign(align) {
    // Reset all buttons
    document.getElementById('alignLeft').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('alignCenter').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('alignRight').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('alignLeft').classList.add('bg-gray-50');
    document.getElementById('alignCenter').classList.add('bg-gray-50');
    document.getElementById('alignRight').classList.add('bg-gray-50');

    // Set active button
    const activeBtn = document.getElementById('align' + align.charAt(0).toUpperCase() + align.slice(1));
    activeBtn.classList.remove('bg-gray-50');
    activeBtn.classList.add('bg-indigo-600', 'text-white');

    // Update SVG color
    const svg = activeBtn.querySelector('svg');
    svg.classList.remove('text-gray-700');
    svg.classList.add('text-white');

    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.align(align);
        layer.batchDraw();
    }
}

// Text opacity
function updateTextOpacity() {
    const value = parseInt(document.getElementById('textOpacity').value);
    const opacity = value / 10; // Convert 0-10 to 0-1
    if (selectedObject && selectedObject.className === 'Text') {
        selectedObject.opacity(opacity);
        layer.batchDraw();
    }
}

// Duplicate text
function duplicateText() {
    if (selectedObject && selectedObject.className === 'Text') {
        const clone = selectedObject.clone();
        clone.x(selectedObject.x() + 20);
        clone.y(selectedObject.y() + 20);
        clone.name('text-' + Date.now());
        layer.add(clone);

        clone.on('click tap', function() {
            selectObject(clone);
        });

        clone.on('dblclick dbltap', function() {
            // Reuse same double-click logic as original text
            text.on('dblclick dbltap').call(clone);
        });

        layer.batchDraw();
        updateLayersList();
        selectObject(clone);
    }
}

// Delete text
function deleteText() {
    if (selectedObject) {
        selectedObject.destroy();
        transformer.nodes([]);
        selectedObject = null;
        hideObjectProperties();
        layer.batchDraw();
        updateLayersList();
    }
}

// Set text as template variable
function setTextAsVariable() {
    if (!selectedObject || selectedObject.className !== 'Text') return;

    const variableType = document.getElementById('textVariableType').value;

    if (variableType) {
        // Mark as variable (keep placeholder text as-is, don't change it)
        selectedObject.setAttr('isVariable', variableType);
        showToast(`Text marked as ${variableType} variable`);
    } else {
        // Remove variable marking
        selectedObject.setAttr('isVariable', null);
    }

    layer.batchDraw();
}

// Set image as template variable
function setImageAsVariable() {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    const variableType = document.getElementById('imageVariableType').value;

    if (variableType) {
        selectedObject.setAttr('isVariable', variableType);
        showToast(`Image marked as ${variableType} variable`);
    } else {
        selectedObject.setAttr('isVariable', null);
    }

    layer.batchDraw();
}

// Set shape color as template variable
function setShapeColorAsVariable() {
    if (!selectedObject || !['Rect', 'Circle', 'Star', 'RegularPolygon'].includes(selectedObject.className)) return;

    const variableType = document.getElementById('shapeColorVariable').value;

    if (variableType) {
        // Mark as color variable (keep current fill color as placeholder)
        selectedObject.setAttr('colorVariable', variableType);
        showToast(`Shape color marked as ${variableType}`);
    } else {
        // Remove variable marking
        selectedObject.setAttr('colorVariable', null);
    }

    layer.batchDraw();
}

// Toggle uppercase
let isUppercase = false;
function toggleUppercase() {
    if (selectedObject && selectedObject.className === 'Text') {
        const btn = document.getElementById('uppercaseBtn');
        isUppercase = !isUppercase;

        if (isUppercase) {
            selectedObject.text(selectedObject.text().toUpperCase());
            btn.classList.remove('bg-gray-50');
            btn.classList.add('bg-indigo-600', 'text-white');
            const svg = btn.querySelector('svg');
            const span = btn.querySelector('span');
            svg.classList.remove('text-gray-700');
            svg.classList.add('text-white');
            span.classList.remove('text-gray-700');
            span.classList.add('text-white');
        } else {
            selectedObject.text(selectedObject.text().toLowerCase());
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('bg-gray-50');
            const svg = btn.querySelector('svg');
            const span = btn.querySelector('span');
            svg.classList.remove('text-white');
            svg.classList.add('text-gray-700');
            span.classList.remove('text-white');
            span.classList.add('text-gray-700');
        }

        transformer.forceUpdate();
        layer.batchDraw();
    }
}

// Update layers list
function updateLayersList() {
    const layersList = document.getElementById('layersList');
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');

    if (objects.length === 0) {
        layersList.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">No objects yet</p>';
        return;
    }

    const totalLayers = objects.length;

    layersList.innerHTML = objects.map((obj, konvaIndex) => {
        const isSelected = obj === selectedObject;
        const isVisible = obj.visible() !== false;
        const isLocked = obj.draggable() === false;

        // Get icon SVG based on type
        let iconSvg = '';
        if (obj.className === 'Text') {
            iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
        } else if (obj.className === 'Image') {
            iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
        } else {
            iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
        }

        const name = obj.className === 'Text'
            ? obj.text().substring(0, 12) + (obj.text().length > 12 ? '...' : '')
            : obj.className === 'Image' ? 'Image' : 'Shape';

        // After reverse, highest z-index (last in konva) will be first in UI
        const canMoveUp = konvaIndex < totalLayers - 1;
        const canMoveDown = konvaIndex > 0;

        return `
            <div draggable="true"
                 ondragstart="handleLayerDragStart(event, ${konvaIndex})"
                 ondragover="handleLayerDragOver(event)"
                 ondragleave="handleLayerDragLeave(event)"
                 ondrop="handleLayerDrop(event, ${konvaIndex})"
                 ondragend="handleLayerDragEnd(event)"
                 class="layer-item px-2 py-2 rounded-lg cursor-move transition-all duration-200 ${isSelected ? 'bg-purple-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'} ${!isVisible ? 'opacity-50' : ''}">
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 opacity-50 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
                        <circle cx="4" cy="4" r="1.5"/><circle cx="12" cy="4" r="1.5"/>
                        <circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
                        <circle cx="4" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/>
                    </svg>
                    <div onclick="selectObjectByIndex(${konvaIndex})" class="flex items-center gap-1 flex-1 min-w-0 cursor-pointer">
                        <div class="flex-shrink-0">${iconSvg}</div>
                        <span class="text-xs truncate">${name}</span>
                    </div>
                    <div class="flex gap-0.5 flex-shrink-0">
                        <!-- Visibility Toggle -->
                        <button onclick="event.stopPropagation(); toggleLayerVisibility(${konvaIndex})"
                                title="${isVisible ? 'Hide layer' : 'Show layer'}"
                                class="p-1 rounded hover:bg-opacity-20 hover:bg-gray-700 transition">
                            ${isVisible
                                ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                                : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>'
                            }
                        </button>
                        <!-- Lock Toggle -->
                        <button onclick="event.stopPropagation(); toggleLayerLock(${konvaIndex})"
                                title="${isLocked ? 'Unlock layer' : 'Lock layer'}"
                                class="p-1 rounded hover:bg-opacity-20 hover:bg-gray-700 transition">
                            ${isLocked
                                ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>'
                                : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>'
                            }
                        </button>
                        <!-- Move Up -->
                        <button onclick="event.stopPropagation(); moveLayerUp(${konvaIndex})"
                                ${!canMoveUp ? 'disabled' : ''}
                                title="Move layer up"
                                class="p-1 rounded hover:bg-opacity-20 hover:bg-gray-700 transition ${!canMoveUp ? 'opacity-30 cursor-not-allowed' : ''}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <!-- Move Down -->
                        <button onclick="event.stopPropagation(); moveLayerDown(${konvaIndex})"
                                ${!canMoveDown ? 'disabled' : ''}
                                title="Move layer down"
                                class="p-1 rounded hover:bg-opacity-20 hover:bg-gray-700 transition ${!canMoveDown ? 'opacity-30 cursor-not-allowed' : ''}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).reverse().join('');
}

// Layer drag & drop handlers
let draggedLayerIndex = null;

function handleLayerDragStart(e, index) {
    draggedLayerIndex = index;
    e.target.style.opacity = '0.4';
    e.target.classList.add('dragging');
}

function handleLayerDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    // Visual feedback - border top blue
    const target = e.currentTarget;
    if (target && !target.classList.contains('dragging')) {
        target.classList.add('border-t-2', 'border-blue-400', 'mt-2');
    }
}

function handleLayerDragLeave(e) {
    // Remove visual feedback
    const target = e.currentTarget;
    if (target) {
        target.classList.remove('border-t-2', 'border-blue-400', 'mt-2');
    }
}

function handleLayerDrop(e, targetIndex) {
    e.preventDefault();
    e.stopPropagation();

    // Remove all visual feedback
    document.querySelectorAll('.layer-item').forEach(item => {
        item.classList.remove('border-t-2', 'border-blue-400', 'mt-2');
    });

    if (draggedLayerIndex === null || draggedLayerIndex === targetIndex) return;

    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const totalLayers = objects.length;

    // Reverse index (UI shows reversed)
    const fromIndex = totalLayers - 1 - draggedLayerIndex;
    const toIndex = totalLayers - 1 - targetIndex;

    // Get objects
    const draggedObj = objects[fromIndex];
    const targetObj = objects[toIndex];

    if (!draggedObj || !targetObj) return;

    // Reorder in Konva
    if (fromIndex < toIndex) {
        draggedObj.moveUp();
    } else {
        draggedObj.moveDown();
    }

    layer.batchDraw();
    updateLayersList();
}

function handleLayerDragEnd(e) {
    e.target.style.opacity = '1';
    e.target.classList.remove('dragging');

    // Clean up all visual feedback
    document.querySelectorAll('.layer-item').forEach(item => {
        item.classList.remove('border-t-2', 'border-blue-400', 'mt-2');
    });

    draggedLayerIndex = null;
}

// Move layer up in UI (increase z-index in Konva)
function moveLayerUp(konvaIndex) {
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const totalLayers = objects.length;

    if (konvaIndex >= totalLayers - 1) {
        showToast('Already at top', 'info');
        return;
    }

    const obj = objects[konvaIndex];
    if (obj) {
        obj.moveUp(); // Move forward in Konva array (higher z-index)
        layer.batchDraw();
        updateLayersList();
        showToast('Layer moved up');
    }
}

// Move layer down in UI (decrease z-index in Konva)
function moveLayerDown(konvaIndex) {
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const totalLayers = objects.length;

    if (konvaIndex <= 0) {
        showToast('Already at bottom', 'info');
        return;
    }

    const obj = objects[konvaIndex];
    if (obj) {
        obj.moveDown(); // Move backward in Konva array (lower z-index)
        layer.batchDraw();
        updateLayersList();
        showToast('Layer moved down');
    }
}

// Select object by index from layers panel
function selectObjectByIndex(konvaIndex) {
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const obj = objects[konvaIndex];
    if (obj) {
        selectObject(obj);
    }
}

// Toggle layer visibility
function toggleLayerVisibility(konvaIndex) {
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const obj = objects[konvaIndex];

    if (obj) {
        const currentVisibility = obj.visible() !== false;
        obj.visible(!currentVisibility);

        // Hide transformer if object is hidden
        if (!currentVisibility === false && selectedObject === obj) {
            transformer.nodes([]);
        }

        layer.batchDraw();
        updateLayersList();

        const action = currentVisibility ? 'hidden' : 'shown';
        showToast(`Layer ${action}`);
    }
}

// Toggle layer lock
function toggleLayerLock(konvaIndex) {
    const objects = layer.children.filter(obj => obj.name() !== 'background' && obj.className !== 'Transformer');
    const obj = objects[konvaIndex];

    if (obj) {
        const currentLock = obj.draggable() === false;
        obj.draggable(currentLock);

        // Also prevent selection when locked
        if (!currentLock) {
            // Locking the object - remove transformer if this object is selected
            if (selectedObject === obj) {
                transformer.nodes([]);
                selectedObject = null;
            }
        }

        layer.batchDraw();
        updateLayersList();

        const action = currentLock ? 'unlocked' : 'locked';
        showToast(`Layer ${action}`);
    }
}

// Export canvas
// Export Modal State
let exportFormat = 'png';
let exportQuality = 'medium';

// Open Export Modal
function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}

// Close Export Modal
function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

// Select Export Format
function selectExportFormat(format) {
    exportFormat = format;

    // Update UI
    document.querySelectorAll('.export-format-btn').forEach(btn => {
        btn.classList.remove('border-purple-600', 'bg-purple-50', 'text-purple-700');
        btn.classList.add('border-gray-200', 'text-gray-700');
    });

    const selectedBtn = document.getElementById('format-' + format);
    selectedBtn.classList.remove('border-gray-200', 'text-gray-700');
    selectedBtn.classList.add('border-purple-600', 'bg-purple-50', 'text-purple-700');
}

// Select Export Quality
function selectExportQuality(quality) {
    exportQuality = quality;

    // Update UI
    document.querySelectorAll('.export-quality-btn').forEach(btn => {
        btn.classList.remove('border-purple-600', 'bg-purple-50', 'text-purple-700');
        btn.classList.add('border-gray-200', 'text-gray-700');
    });

    const selectedBtn = document.getElementById('quality-' + quality);
    selectedBtn.classList.remove('border-gray-200', 'text-gray-700');
    selectedBtn.classList.add('border-purple-600', 'bg-purple-50', 'text-purple-700');
}

// Perform Export
function performExport(action) {
    // Hide transformer before export
    const wasTransformerVisible = transformer.nodes().length > 0;
    const selectedNodes = transformer.nodes();
    transformer.nodes([]);

    // Save current stage scale
    const currentScaleX = stage.scaleX();
    const currentScaleY = stage.scaleY();

    // Reset stage scale to 1 for export (export at actual canvas size)
    stage.scaleX(1);
    stage.scaleY(1);

    layer.batchDraw();

    // Get quality settings
    const qualityMap = {
        'low': { quality: 0.5, pixelRatio: 1 },
        'medium': { quality: 0.8, pixelRatio: 1 },
        'high': { quality: 1.0, pixelRatio: 1 }
    };

    const settings = qualityMap[exportQuality];

    // Get MIME type
    const mimeTypeMap = {
        'png': 'image/png',
        'jpg': 'image/jpeg',
        'webp': 'image/webp'
    };

    const mimeType = mimeTypeMap[exportFormat];

    // Export canvas at actual size
    const dataURL = stage.toDataURL({
        mimeType: mimeType,
        quality: exportFormat === 'png' ? 1 : settings.quality,
        pixelRatio: settings.pixelRatio
    });

    // Restore stage scale
    stage.scaleX(currentScaleX);
    stage.scaleY(currentScaleY);

    // Restore transformer if it was visible
    if (wasTransformerVisible) {
        transformer.nodes(selectedNodes);
    }

    layer.batchDraw();

    if (action === 'download') {
        // Download file
        const link = document.createElement('a');
        link.download = 'design-' + Date.now() + '.' + exportFormat;
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast('Image exported successfully!');
        closeExportModal();
    } else if (action === 'library') {
        // Save to media library
        saveToLibrary(dataURL);
    }
}

// Save to Media Library
function saveToLibrary(dataURL) {
    showToast('Saving to library...', 'info');

    // Convert data URL to blob
    fetch(dataURL)
        .then(res => res.blob())
        .then(blob => {
            const formData = new FormData();
            formData.append('file', blob, 'design-' + Date.now() + '.' + exportFormat);

            // Upload to server
            return fetch('/media/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Saved to media library successfully!');
                closeExportModal();
            } else {
                showToast('Failed to save to library', 'error');
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            showToast('Failed to save to library', 'error');
        });
}

// Open Batch Export (Future Feature)
function openBatchExport() {
    alert('Batch export feature coming soon! This will allow you to export multiple sizes at once (Instagram Post, Story, Facebook Post, etc.)');
}

// Legacy function for backward compatibility
function exportCanvas() {
    openExportModal();
}

// Show toast
function showToast(message, type = 'success') {
    const toast = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    toastMessage.textContent = message;

    // Change color based on type
    if (type === 'error') {
        toast.classList.remove('bg-green-600');
        toast.classList.add('bg-red-600');
    } else {
        toast.classList.remove('bg-red-600');
        toast.classList.add('bg-green-600');
    }

    toast.classList.remove('hidden');

    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

// Templates Modal
function openTemplatesModal() {
    const modal = document.getElementById('templatesModal');
    modal.classList.remove('hidden');
    loadTemplates();
}

function closeTemplatesModal() {
    const modal = document.getElementById('templatesModal');
    modal.classList.add('hidden');
}

function loadTemplates() {
    fetch('/api/templates')
        .then(res => res.json())
        .then(data => {
            const grid = document.getElementById('templatesGrid');
            grid.innerHTML = '';

            data.templates.forEach(template => {
                const card = document.createElement('div');
                card.className = 'group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition cursor-pointer';
                card.onclick = () => loadTemplate(template.id);

                card.innerHTML = `
                    <div class="relative" style="padding-bottom: 100%;">
                        ${template.thumbnail_url
                            ? `<img src="${template.thumbnail_url}" class="absolute inset-0 w-full h-full object-cover" alt="${template.name}">`
                            : `<div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                                <svg class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/>
                                </svg>
                            </div>`
                        }
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-900 truncate">${template.name}</p>
                        <p class="text-xs text-gray-500 capitalize">${template.category}</p>
                    </div>
                `;

                grid.appendChild(card);
            });
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            showToast('Failed to load templates', 'error');
        });
}

function loadTemplate(templateId) {
    fetch(`/api/templates/${templateId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            const template = data.template;

            // Store template data for pre-filling form
            window.currentTemplateData = {
                name: template.name,
                category_id: template.category_id,
                template_type: template.template_type,
                is_public: template.is_public
            };

            // Resize canvas if needed
            const container = document.getElementById('canvasContainer');
            const canvasWidth = template.canvas_width;
            const canvasHeight = template.canvas_height;

            // Calculate optimal zoom
            const zoom = calculateOptimalZoom(canvasWidth, canvasHeight);
            container.style.width = (canvasWidth * zoom) + 'px';
            container.style.height = (canvasHeight * zoom) + 'px';

            // Update stage size
            stage.width(canvasWidth);
            stage.height(canvasHeight);
            stage.scale({ x: zoom, y: zoom });

            // Update zoom display
            updateZoomDisplay();

            // Clear existing layers
            layer.destroyChildren();

            // Load canvas JSON with variable data
            const canvasJson = template.canvas_json;

            // Variable data for replacement (empty by default, will be filled when applying branding or amplifying article)
            const variableData = {
                'article_title': '',
                'article_excerpt': '',
                'article_author': '',
                'article_date': '',
                'featured_image': '',
                'brand_logo': '',
                'brand_color_primary': '',
                'brand_color_secondary': '',
                'website_url': ''
            };

            loadCanvasFromJSON(canvasJson, variableData);

            // Set current template ID for editing mode
            currentTemplateId = template.id;

            // Update URL to reflect loaded template
            window.history.replaceState({}, '', `/image-editor?template_id=${template.id}`);

            closeTemplatesModal();
            showToast('Template loaded successfully');
        })
        .catch(error => {
            console.error('Error loading template:', error);
            showToast('Failed to load template', 'error');
        });
}

// Template Variable Parser
function parseTemplateVariables(value, variableData = {}) {
    if (typeof value !== 'string') return value;

    // Default values for variables
    const defaults = {
        'article_title': 'Article Title',
        'article_excerpt': 'Article excerpt goes here...',
        'article_author': 'Author Name',
        'article_date': new Date().toLocaleDateString(),
        'brand_logo': '', // Will be loaded as image
        'brand_color_primary': '#3B82F6',
        'brand_color_secondary': '#1E40AF',
        'website_url': 'example.com'
    };

    // Merge provided data with defaults
    const data = { ...defaults, ...variableData };

    // Replace variables
    return value.replace(/\{([^}]+)\}/g, (match, variable) => {
        if (data.hasOwnProperty(variable)) {
            return data[variable] || defaults[variable] || `[${variable}]`;
        }
        console.warn(`Template variable not found: ${variable}`);
        return `[${variable}]`; // Show as placeholder if not found
    });
}

/**
 * Apply image constraint for auto-sizing
 * @param {Image} imageObj - HTML Image element
 * @param {Object} attrs - Original Konva Image attributes
 * @param {Object} constraint - Constraint configuration
 * @returns {Konva.Image} - Configured Konva Image
 */
function applyImageConstraint(imageObj, attrs, constraint) {
    const { type, width: constraintWidth, height: constraintHeight, allowReposition } = constraint;

    let finalAttrs = {
        ...attrs,
        image: imageObj,
        draggable: allowReposition !== false, // Default true
        name: attrs.name || 'image-' + Date.now()
    };

    if (type === 'fill') {
        // FILL MODE (Cover): Scale to fill container, allow cropping
        // Used for featured images that need to cover a specific area
        const scaleX = constraintWidth / imageObj.width;
        const scaleY = constraintHeight / imageObj.height;
        const scale = Math.max(scaleX, scaleY); // Cover (larger scale)

        finalAttrs.width = imageObj.width * scale;
        finalAttrs.height = imageObj.height * scale;

        // Center image in constraint box
        finalAttrs.x = attrs.x + (constraintWidth - finalAttrs.width) / 2;
        finalAttrs.y = attrs.y + (constraintHeight - finalAttrs.height) / 2;

        // Add visual clipping box (user can still drag to reposition)
        // Note: We don't use clip because it prevents dragging outside bounds
        // Instead, we rely on user to position correctly

    } else if (type === 'fit') {
        // FIT MODE (Contain): Scale to fit width, maintain aspect ratio
        // Used for logos that need consistent width
        const scale = constraintWidth / imageObj.width;

        finalAttrs.width = constraintWidth;
        finalAttrs.height = imageObj.height * scale;

        // Keep x position from template, but you can adjust if needed
        // finalAttrs.x = attrs.x; // Already set from attrs
        // finalAttrs.y = attrs.y; // Already set from attrs
    }

    // Remove constraint metadata (so it doesn't get saved back to template)
    delete finalAttrs.initialConstraint;

    return new Konva.Image(finalAttrs);
}

function loadCanvasFromJSON(canvasJson, variableData = {}) {
    // Clear current layer
    layer.destroyChildren();

    // Recreate transformer (old one is destroyed)
    transformer.destroy();
    transformer = new Konva.Transformer({
        enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
        rotateEnabled: true,
        borderStroke: '#4F46E5',
        borderStrokeWidth: 2,
        anchorStroke: '#4F46E5',
        anchorFill: '#FFFFFF',
        anchorSize: 10
    });
    layer.add(transformer);

    // Parse canvas JSON structure
    const layers = canvasJson.children || [];

    layers.forEach(layerData => {
        const children = layerData.children || [];

        children.forEach((childData, originalIndex) => {
            const className = childData.className;
            const attrs = childData.attrs || {};

            // Replace variables with actual data
            Object.keys(attrs).forEach(key => {
                attrs[key] = parseTemplateVariables(attrs[key], variableData);
            });

            let shape;

            if (className === 'Rect') {
                // Handle color variable replacement
                if (attrs.colorVariable && variableData[attrs.colorVariable]) {
                    attrs.fill = variableData[attrs.colorVariable];
                }
                shape = new Konva.Rect(attrs);
            } else if (className === 'Circle') {
                // Handle color variable replacement
                if (attrs.colorVariable && variableData[attrs.colorVariable]) {
                    attrs.fill = variableData[attrs.colorVariable];
                }
                shape = new Konva.Circle(attrs);
            } else if (className === 'Text') {
                // Ensure text wraps properly if width is set
                if (!attrs.wrap && attrs.width) {
                    attrs.wrap = 'word';
                }

                // Remove fixed height to allow auto-calculation based on wrapped content
                delete attrs.height;

                // Handle variable replacement (placeholder fallback)
                if (attrs.isVariable && variableData[attrs.isVariable]) {
                    // Replace placeholder with actual data
                    attrs.text = variableData[attrs.isVariable];
                }

                shape = new Konva.Text(attrs);
                attachTextEditHandler(shape);
            } else if (className === 'Image') {
                // Handle variable replacement for images
                let imageSrc = attrs.image;

                if (attrs.isVariable && variableData[attrs.isVariable]) {
                    // Replace with actual image URL
                    imageSrc = variableData[attrs.isVariable];
                }

                // Skip if no image source (e.g., variable not yet replaced)
                if (!imageSrc) {
                    console.warn('Image object has no source, skipping:', attrs.name);
                    return;
                }

                // Handle image loading with constraint support
                const imageObj = new Image();
                imageObj.crossOrigin = 'Anonymous'; // Fix CORS

                imageObj.onload = function() {
                    // Check if image has initialConstraint
                    const constraint = attrs.initialConstraint;

                    if (constraint) {
                        // Apply constraint for auto-sizing
                        shape = applyImageConstraint(imageObj, attrs, constraint);
                    } else {
                        // No constraint, use attrs as-is
                        shape = new Konva.Image({
                            ...attrs,
                            image: imageObj,
                            draggable: true
                        });
                    }

                    // Add click handler
                    shape.on('click tap', function() {
                        selectObject(shape);
                    });

                    layer.add(shape);

                    // Move to correct position (images are loaded async, so need repositioning)
                    // Move to bottom first (index 1, after transformer at index 0)
                    shape.moveToBottom();
                    shape.moveUp(); // Now at index 1

                    // Then move up by originalIndex times to reach correct position
                    for (let i = 0; i < originalIndex; i++) {
                        shape.moveUp();
                    }

                    layer.batchDraw();
                    updateLayersList();
                };
                imageObj.src = imageSrc; // Use replaced imageSrc (supports variable replacement)
                return; // Skip adding shape below
            }

            if (shape) {
                // Don't attach click handler to background - it should only deselect
                if (shape.name() !== 'background') {
                    shape.on('click tap', function() {
                        selectObject(shape);
                    });
                }
                layer.add(shape);
                // Sync shapes are added in order automatically, no need to reorder
            }
        });
    });

    layer.batchDraw();
    updateLayersList();

    // CRITICAL: Deselect all objects after loading template
    transformer.nodes([]);
    selectedObject = null;

    // CRITICAL: Force redraw after fonts are loaded to fix text dimensions
    // Wait for all fonts to load (especially bold/custom weights)
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            // Additional delay to ensure all font variants are loaded
            setTimeout(() => {
                layer.batchDraw();
            }, 100);
        });
    } else {
        // Fallback for browsers without Font Loading API
        setTimeout(() => {
            layer.batchDraw();
        }, 500);
    }
}

// Save Template Modal
function openSaveTemplateModal() {
    const modal = document.getElementById('saveTemplateModal');
    const modalTitle = document.getElementById('saveTemplateModalTitle');
    const submitButton = document.getElementById('saveTemplateButton');
    const form = document.getElementById('saveTemplateForm');

    // Change title and button text based on mode
    if (currentTemplateId !== null) {
        modalTitle.textContent = 'Update Template';
        submitButton.textContent = 'Update Template';
    } else {
        modalTitle.textContent = 'Save as Template';
        submitButton.textContent = 'Save Template';
        form.reset();
    }

    modal.classList.remove('hidden');

    // Load categories for admin, then pre-fill if editing
    @if(auth()->user()->role === 'super_admin')
    loadTemplateCategories().then(() => {
        if (currentTemplateId !== null && window.currentTemplateData) {
            form.querySelector('[name="name"]').value = window.currentTemplateData.name;
            form.querySelector('[name="template_type"]').value = window.currentTemplateData.template_type;

            if (form.querySelector('[name="category_id"]')) {
                form.querySelector('[name="category_id"]').value = window.currentTemplateData.category_id || '';
            }
            if (form.querySelector('[name="is_public"]')) {
                form.querySelector('[name="is_public"]').checked = window.currentTemplateData.is_public;
            }
        }
    });
    @else
    if (currentTemplateId !== null && window.currentTemplateData) {
        form.querySelector('[name="name"]').value = window.currentTemplateData.name;
        form.querySelector('[name="template_type"]').value = window.currentTemplateData.template_type;
    }
    @endif
}

function closeSaveTemplateModal() {
    const modal = document.getElementById('saveTemplateModal');
    modal.classList.add('hidden');
    document.getElementById('saveTemplateForm').reset();
}

// Category Management (Admin Only)
function loadTemplateCategories() {
    return fetch('/api/template-categories', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(categories => {
        const select = document.getElementById('categorySelect');
        // Keep "No Category" option
        select.innerHTML = '<option value="">No Category (User Template)</option>';

        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Failed to load categories:', error);
    });
}

function openQuickAddCategory() {
    document.getElementById('quickAddCategoryModal').classList.remove('hidden');
}

function closeQuickAddCategory() {
    document.getElementById('quickAddCategoryModal').classList.add('hidden');
    document.getElementById('quickAddCategoryForm').reset();
}

// Handle Quick Add Category Form Submit
document.addEventListener('DOMContentLoaded', function() {
    const quickAddForm = document.getElementById('quickAddCategoryForm');
    if (quickAddForm) {
        quickAddForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                icon: formData.get('icon') || null,
                sort_order: 999, // Add to end
                is_active: true
            };

            fetch('/api/template-categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showToast('Category added successfully!');
                    closeQuickAddCategory();
                    loadTemplateCategories(); // Reload categories

                    // Auto-select the new category
                    setTimeout(() => {
                        document.getElementById('categorySelect').value = result.category.id;
                    }, 100);
                } else {
                    alert('Failed to add category: ' + (result.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error adding category:', error);
                alert('Failed to add category');
            });
        });
    }
});

// Branding functions
let brandingsData = [];

function loadBrandings() {
    fetch('/api/brandings', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Failed to load brandings');
        }
        return res.json();
    })
    .then(data => {
        brandingsData = data.brandings;
        const selector = document.getElementById('brandingSelector');
        selector.innerHTML = '<option value="">None</option>';

        data.brandings.forEach(branding => {
            const option = document.createElement('option');
            option.value = branding.id;
            option.textContent = branding.preset_name;
            option.dataset.primaryColor = branding.primary_color;
            option.dataset.secondaryColor = branding.secondary_color;
            option.dataset.font = branding.default_font;
            option.dataset.logoUrl = branding.logo_url || '';
            option.dataset.websiteUrl = branding.website_url || '';
            selector.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading brandings:', error);
    });
}

function applySelectedBranding() {
    const selector = document.getElementById('brandingSelector');
    const selectedOption = selector.options[selector.selectedIndex];

    if (!selectedOption.value) {
        showToast('Please select a branding preset', 'error');
        return;
    }

    const variables = {
        brand_color_primary: selectedOption.dataset.primaryColor,
        brand_color_secondary: selectedOption.dataset.secondaryColor,
        brand_font: selectedOption.dataset.font,
        brand_logo: selectedOption.dataset.logoUrl,
        website_url: selectedOption.dataset.websiteUrl
    };

    applyBrandingVariables(variables);
    showToast('Branding applied successfully');
}

function applyBrandingVariables(variables) {
    // NEW SYSTEM: Use custom attributes instead of placeholder text
    layer.find('Text, Image, Rect, Circle').forEach(shape => {
        // Replace brand logo using isVariable attribute
        if (shape.className === 'Image' && shape.getAttr('isVariable') === 'brand_logo') {
            if (variables.brand_logo) {
                const imageObj = new Image();
                imageObj.crossOrigin = 'Anonymous';

                // Store original dimensions
                const originalWidth = shape.width();
                const originalHeight = shape.height();

                imageObj.onload = function() {
                    // Calculate aspect ratio
                    const imageAspect = imageObj.width / imageObj.height;
                    const containerAspect = originalWidth / originalHeight;

                    let newWidth, newHeight;

                    // Fit image to container while maintaining aspect ratio (contain mode)
                    if (imageAspect > containerAspect) {
                        // Image is wider, fit to width
                        newWidth = originalWidth;
                        newHeight = originalWidth / imageAspect;
                    } else {
                        // Image is taller, fit to height
                        newHeight = originalHeight;
                        newWidth = originalHeight * imageAspect;
                    }

                    shape.image(imageObj);
                    shape.width(newWidth);
                    shape.height(newHeight);

                    // Remove variable marker
                    shape.setAttr('isVariable', null);

                    layer.batchDraw();
                };
                imageObj.src = variables.brand_logo;
            }
        }

        // Replace brand colors using colorVariable attribute
        if (['Rect', 'Circle'].includes(shape.className) && shape.getAttr('colorVariable')) {
            const colorVar = shape.getAttr('colorVariable');
            if (colorVar === 'brand_color_primary' && variables.brand_color_primary) {
                shape.fill(variables.brand_color_primary);
                shape.setAttr('colorVariable', null);
            } else if (colorVar === 'brand_color_secondary' && variables.brand_color_secondary) {
                shape.fill(variables.brand_color_secondary);
                shape.setAttr('colorVariable', null);
            }
        }

        // Replace ALL text font family with brand font (AUTOMATIC)
        if (shape.className === 'Text' && variables.brand_font) {
            shape.fontFamily(variables.brand_font);
        }

        // Replace text variables using isVariable attribute
        if (shape.className === 'Text' && shape.getAttr('isVariable')) {
            const varType = shape.getAttr('isVariable');
            if (varType === 'website_url' && variables.website_url) {
                shape.text(variables.website_url);
                shape.setAttr('isVariable', null);
            }
            // Add more text variables as needed
        }
    });

    layer.batchDraw();
}

// Serialize canvas with image URLs preserved
function serializeCanvasWithImages() {
    const json = JSON.parse(stage.toJSON());

    // Loop through all layers
    json.children.forEach(layerData => {
        if (!layerData.children) return;

        // Filter out Transformer before saving
        layerData.children = layerData.children.filter(obj => obj.className !== 'Transformer');

        // Loop through all objects in layer
        layerData.children.forEach((objData, index) => {
            // Find the actual Konva object by name (more reliable than index)
            const actualLayer = stage.children[json.children.indexOf(layerData)];

            if (actualLayer) {
                // Find shape by name attribute (unique identifier)
                const shapeName = objData.attrs.name;
                const shape = actualLayer.findOne(node => node.name() === shapeName);

                if (shape) {
                    // Save custom variable attributes for ALL object types
                    const isVariable = shape.getAttr('isVariable');
                    const colorVariable = shape.getAttr('colorVariable');

                    if (isVariable) {
                        objData.attrs.isVariable = isVariable;
                    }
                    if (colorVariable) {
                        objData.attrs.colorVariable = colorVariable;
                    }

                    // Image-specific handling
                    if (objData.className === 'Image') {
                        const imageElement = shape.image();

                        if (imageElement) {
                            // Save image URL (works for both external URLs and data URLs)
                            objData.attrs.image = imageElement.src || imageElement.currentSrc;

                            // Ensure dimensions are saved
                            if (!objData.attrs.width) {
                                objData.attrs.width = imageElement.naturalWidth || imageElement.width;
                            }
                            if (!objData.attrs.height) {
                                objData.attrs.height = imageElement.naturalHeight || imageElement.height;
                            }
                        } else if (!objData.attrs.image) {
                            // CRITICAL: If image element is null and no image attr exists,
                            // this is a problem - log warning
                            console.warn('Image has no imageElement and no image attr:', shape.name(), 'isVariable:', shape.getAttr('isVariable'));

                            // For variable images without source, keep the variable marking
                            // but skip saving (will be handled when variables are applied)
                        }
                    }
                }
            }
        });
    });

    return json;
}

// Track current template ID for editing
let currentTemplateId = null;

// Save template form submission
document.addEventListener('DOMContentLoaded', function() {
    loadBrandings();

    // Load asset or template if parameter exists
    const urlParams = new URLSearchParams(window.location.search);
    const assetId = urlParams.get('asset_id');
    const templateId = urlParams.get('template_id');

    if (assetId) {
        loadAsset(assetId);
    } else if (templateId) {
        currentTemplateId = templateId;
        loadTemplate(templateId);
    }

    const form = document.getElementById('saveTemplateForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        // CRITICAL: Deselect all objects before saving to prevent transformer state from being saved
        transformer.nodes([]);
        selectedObject = null;
        layer.batchDraw();

        // Custom serialization to preserve image URLs
        const canvasData = serializeCanvasWithImages();

        // Generate thumbnail - temporarily reset zoom to capture actual canvas size
        const currentScale = stage.scaleX();
        const currentPosition = stage.position();

        // Reset to 1:1 scale for thumbnail
        stage.scale({ x: 1, y: 1 });
        stage.position({ x: 0, y: 0 });
        layer.batchDraw();

        // Calculate pixelRatio to get ~400px on longest side
        const maxThumbnailSize = 400;
        const longestSide = Math.max(stage.width(), stage.height());
        const thumbnailRatio = maxThumbnailSize / longestSide;

        const thumbnailDataURL = stage.toDataURL({
            pixelRatio: thumbnailRatio,
            mimeType: 'image/jpeg',
            quality: 0.8
        });

        // Restore zoom
        stage.scale({ x: currentScale, y: currentScale });
        stage.position(currentPosition);
        layer.batchDraw();

        const data = {
            name: formData.get('name'),
            category_id: formData.get('category_id') || null,
            template_type: formData.get('template_type'),
            canvas_json: canvasData,
            canvas_width: stage.width(),
            canvas_height: stage.height(),
            is_public: formData.get('is_public') ? true : false,
            thumbnail: thumbnailDataURL
        };

        // Determine if we're updating or creating
        const isEditing = currentTemplateId !== null;
        const url = isEditing ? `/templates/${currentTemplateId}` : '/templates';
        const method = isEditing ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                closeSaveTemplateModal();
                const message = isEditing ? 'Template updated successfully' : 'Template saved successfully';
                showToast(message);

                // If we just created a new template, update the URL and currentTemplateId
                if (!isEditing && response.template && response.template.id) {
                    currentTemplateId = response.template.id;
                    const newUrl = `/image-editor?template_id=${currentTemplateId}`;
                    window.history.replaceState({}, '', newUrl);
                }
            } else {
                showToast('Failed to save template', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving template:', error);
            showToast('Failed to save template', 'error');
        });
    });
});

// Global variable to store current asset ID
let currentAssetId = null;

// Load asset from API
function loadAsset(assetId) {
    currentAssetId = assetId;

    fetch(`/api/assets/${assetId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const asset = data.asset;

        // Show Save Asset button, hide Save Template button
        document.getElementById('saveAssetBtn').classList.remove('hidden');
        document.getElementById('saveTemplateBtn').classList.add('hidden');

        // Load canvas JSON if exists, or fallback to visual_url
        if (asset.canvas_json && asset.canvas_json.children && asset.canvas_json.children.length > 0) {
            try {
                loadCanvasFromJSON(asset.canvas_json);
                showToast('Asset loaded successfully');
            } catch (error) {
                console.error('Failed to load canvas JSON, falling back to visual_url:', error);
                // Fallback to visual_url if canvas JSON is corrupted
                if (asset.visual_url) {
                    loadVisualAsBackground(asset.visual_url);
                }
            }
        }
        // Otherwise load visual_path as background image if exists
        else if (asset.visual_url) {
            loadVisualAsBackground(asset.visual_url);
        }
    })
    .catch(error => {
        console.error('Error loading asset:', error);
        showToast('Failed to load asset', 'error');
    });
}

// Helper function to load visual as background
function loadVisualAsBackground(visualUrl) {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = function() {
        const canvasWidth = stage.width();
        const canvasHeight = stage.height();

        // Calculate scale to fit
        const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);

        const imageObj = new Konva.Image({
            x: 0,
            y: 0,
            image: img,
            width: img.width * scale,
            height: img.height * scale,
            draggable: true,
            name: 'asset-background'
        });

        imageObj.on('click tap', function() {
            selectObject(imageObj);
        });

        layer.add(imageObj);
        layer.batchDraw();
        updateLayersList();
        showToast('Asset image loaded');
    };
    img.onerror = function() {
        console.error('Failed to load image:', visualUrl);
        showToast('Failed to load asset image', 'error');
    };
    img.src = visualUrl;
}

// Save edited asset
function saveAsset() {
    if (!currentAssetId) {
        showToast('No asset loaded', 'error');
        return;
    }

    const canvasJson = serializeCanvasWithImages();

    // Save current state
    const currentScaleX = stage.scaleX();
    const currentScaleY = stage.scaleY();
    const currentSelection = transformer.nodes();

    // Clear selection (hide transformer)
    transformer.nodes([]);

    // Reset zoom to 1:1 for full resolution export
    stage.scaleX(1);
    stage.scaleY(1);

    // Export at full resolution
    const imageData = stage.toDataURL({ pixelRatio: 1 });

    // Restore zoom and selection
    stage.scaleX(currentScaleX);
    stage.scaleY(currentScaleY);
    transformer.nodes(currentSelection);

    fetch(`/api/assets/${currentAssetId}/update-visual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            canvas_json: canvasJson,
            image_data: imageData
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Asset saved successfully');

            // Trigger refresh event for asset library if in modal context
            if (window.opener && data.visual_url) {
                window.opener.postMessage({ type: 'asset-updated', assetId: currentAssetId, visualUrl: data.visual_url }, '*');
            }
        } else {
            showToast('Failed to save asset', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving asset:', error);
        showToast('Failed to save asset', 'error');
    });
}

// Image manipulation functions
function flipImage(direction) {
    if (selectedObject && selectedObject.className === 'Image') {
        if (direction === 'horizontal') {
            // Flip horizontally by inverting scaleX
            const currentScaleX = selectedObject.scaleX();
            selectedObject.scaleX(currentScaleX * -1);

            // Adjust position to keep image in place visually
            const width = selectedObject.width() * Math.abs(currentScaleX);
            selectedObject.x(selectedObject.x() + width * Math.sign(currentScaleX));
        } else if (direction === 'vertical') {
            // Flip vertically by inverting scaleY
            const currentScaleY = selectedObject.scaleY();
            selectedObject.scaleY(currentScaleY * -1);

            // Adjust position to keep image in place visually
            const height = selectedObject.height() * Math.abs(currentScaleY);
            selectedObject.y(selectedObject.y() + height * Math.sign(currentScaleY));
        }
        layer.batchDraw();
        showToast('Image flipped ' + direction);
    }
}

function fitToPage() {
    if (selectedObject && selectedObject.className === 'Image') {
        const canvasWidth = stage.width();
        const canvasHeight = stage.height();

        // Get original image dimensions
        const imageWidth = selectedObject.image().width;
        const imageHeight = selectedObject.image().height;

        // Calculate scale to COVER canvas (Math.max instead of Math.min)
        const scale = Math.max(canvasWidth / imageWidth, canvasHeight / imageHeight);

        // Set new dimensions
        const newWidth = imageWidth * scale;
        const newHeight = imageHeight * scale;

        // Reset scale and set width/height directly
        selectedObject.scaleX(1);
        selectedObject.scaleY(1);
        selectedObject.width(newWidth);
        selectedObject.height(newHeight);

        // Center position
        selectedObject.x((canvasWidth - newWidth) / 2);
        selectedObject.y((canvasHeight - newHeight) / 2);

        layer.batchDraw();
    }
}

let isCropping = false;
let cropRect = null;

function cropImage() {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    const btn = document.getElementById('cropBtn');

    if (!isCropping) {
        // Enter crop mode
        isCropping = true;
        btn.classList.remove('bg-gray-50');
        btn.classList.add('bg-indigo-600');
        btn.querySelector('span').classList.add('text-white');
        btn.querySelector('svg').classList.add('text-white');
        btn.querySelector('span').textContent = 'Apply Crop';

        // Hide transformer
        transformer.nodes([]);

        // Create crop rectangle
        const imgPos = selectedObject.position();
        const imgWidth = selectedObject.width() * Math.abs(selectedObject.scaleX());
        const imgHeight = selectedObject.height() * Math.abs(selectedObject.scaleY());

        cropRect = new Konva.Rect({
            x: imgPos.x,
            y: imgPos.y,
            width: imgWidth,
            height: imgHeight,
            stroke: '#4F46E5',
            strokeWidth: 3,
            dash: [10, 5],
            draggable: true,
            name: 'crop-rect'
        });

        // Add resize anchors manually
        const anchors = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        layer.add(cropRect);

        // Create transformer for crop rect
        const cropTransformer = new Konva.Transformer({
            nodes: [cropRect],
            enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
            rotateEnabled: false,
            borderStroke: '#4F46E5',
            borderStrokeWidth: 2,
            anchorStroke: '#4F46E5',
            anchorFill: '#FFFFFF',
            anchorSize: 10,
            keepRatio: false,
            name: 'crop-transformer'
        });
        layer.add(cropTransformer);
        layer.batchDraw();

    } else {
        // Apply crop
        if (cropRect) {
            const cropX = cropRect.x();
            const cropY = cropRect.y();
            const cropWidth = cropRect.width() * cropRect.scaleX();
            const cropHeight = cropRect.height() * cropRect.scaleY();

            const imgX = selectedObject.x();
            const imgY = selectedObject.y();
            const imgScale = selectedObject.scaleX(); // Assume uniform scale

            // Calculate crop area relative to original image coordinates
            const relativeX = (cropX - imgX) / imgScale;
            const relativeY = (cropY - imgY) / imgScale;
            const relativeWidth = cropWidth / imgScale;
            const relativeHeight = cropHeight / imgScale;

            // Apply crop property (defines which part of source image to show)
            selectedObject.crop({
                x: relativeX,
                y: relativeY,
                width: relativeWidth,
                height: relativeHeight
            });

            // Update image display size and position
            selectedObject.position({ x: cropX, y: cropY });
            selectedObject.width(relativeWidth);
            selectedObject.height(relativeHeight);
            selectedObject.scaleX(1);
            selectedObject.scaleY(1);

            // Remove crop rect and transformer
            const cropTransformer = layer.findOne('.crop-transformer');
            if (cropTransformer) cropTransformer.destroy();
            cropRect.destroy();
            cropRect = null;

            // Reset button
            btn.classList.remove('bg-indigo-600');
            btn.classList.add('bg-gray-50');
            btn.querySelector('span').classList.remove('text-white');
            btn.querySelector('svg').classList.remove('text-white');
            btn.querySelector('span').textContent = 'Crop Image';

            isCropping = false;

            // Re-select image
            selectObject(selectedObject);
            layer.batchDraw();

            showToast('Image cropped successfully!');
        }
    }
}

function updateImageBlur() {
    const value = parseInt(document.getElementById('imageBlur').value);
    if (selectedObject && selectedObject.className === 'Image') {
        if (value > 0) {
            // Apply blur filter
            selectedObject.filters([Konva.Filters.Blur]);
            selectedObject.blurRadius(value * 2); // Scale 0-10 to 0-20 for better effect
        } else {
            // Remove blur filter
            selectedObject.filters([]);
        }
        selectedObject.cache();
        layer.batchDraw();
    }
}

function updateImageOpacity() {
    const value = parseInt(document.getElementById('imageOpacity').value);
    const opacity = value / 10;
    if (selectedObject && selectedObject.className === 'Image') {
        selectedObject.opacity(opacity);
        layer.batchDraw();
    }
}

function duplicateImage() {
    if (selectedObject && selectedObject.className === 'Image') {
        const clone = selectedObject.clone();
        clone.x(selectedObject.x() + 20);
        clone.y(selectedObject.y() + 20);
        clone.name('image-' + Date.now());
        layer.add(clone);

        clone.on('click tap', function() {
            selectObject(clone);
        });

        layer.batchDraw();
        updateLayersList();
        selectObject(clone);
    }
}

function deleteImage() {
    if (selectedObject && selectedObject.className === 'Image') {
        selectedObject.destroy();
        transformer.nodes([]);
        selectedObject = null;
        hideObjectProperties();
        layer.batchDraw();
        updateLayersList();
    }
}

// Add Shape to Canvas
function addShape(shapeType) {
    let shape;
    const centerX = stage.width() / 2;
    const centerY = stage.height() / 2;
    const shapeName = 'shape-' + Date.now();

    switch(shapeType) {
        case 'square':
            shape = new Konva.Rect({
                x: centerX - 75,
                y: centerY - 75,
                width: 150,
                height: 150,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'rectangle':
            shape = new Konva.Rect({
                x: centerX - 100,
                y: centerY - 50,
                width: 200,
                height: 100,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'circle':
            shape = new Konva.Circle({
                x: centerX,
                y: centerY,
                radius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'triangle':
            shape = new Konva.RegularPolygon({
                x: centerX,
                y: centerY,
                sides: 3,
                radius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'star':
            shape = new Konva.Star({
                x: centerX,
                y: centerY,
                numPoints: 5,
                innerRadius: 40,
                outerRadius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'pentagon':
            shape = new Konva.RegularPolygon({
                x: centerX,
                y: centerY,
                sides: 5,
                radius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'hexagon':
            shape = new Konva.RegularPolygon({
                x: centerX,
                y: centerY,
                sides: 6,
                radius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'arrow':
            // Arrow shape using path
            shape = new Konva.Path({
                x: centerX - 75,
                y: centerY - 50,
                data: 'M 0 30 L 60 30 L 60 10 L 100 50 L 60 90 L 60 70 L 0 70 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'torn-paper':
            // Torn paper shape with irregular top edge
            shape = new Konva.Path({
                x: centerX - 100,
                y: centerY - 75,
                data: 'M 5,8 L 9,11 L 13,8 L 17,12 L 22,9 L 27,13 L 32,10 L 37,14 L 42,11 L 48,15 L 53,12 L 58,16 L 64,13 L 69,17 L 75,14 L 80,18 L 86,15 L 91,19 L 95,16 L 195,16 L 199,19 L 205,16 L 210,20 L 215,17 L 220,21 L 225,18 L 230,22 L 236,19 L 241,23 L 246,20 L 252,24 L 257,21 L 262,25 L 268,22 L 273,26 L 278,23 L 283,27 L 289,24 L 294,28 L 295,150 L 5,150 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 0.67,
                scaleY: 1
            });
            break;

        case 'octagram-rounded':
            // Octagram rounded shape (8-point star with rounded edges)
            shape = new Konva.Path({
                x: centerX - 75,
                y: centerY - 75,
                data: 'M26.86 2.507c-1.693-1.397-4.027-1.396-5.72 0c-1.013.835-2.449 2.084-4.416 3.925c-2.692.089-4.591.221-5.898.347c-2.186.21-3.836 1.86-4.046 4.046c-.125 1.306-.257 3.205-.347 5.898c-1.84 1.967-3.09 3.404-3.925 4.416c-1.397 1.694-1.397 4.028 0 5.722c.835 1.012 2.085 2.449 3.926 4.416c.089 2.691.221 4.59.346 5.896c.21 2.186 1.86 3.836 4.046 4.046c1.306.125 3.205.258 5.896.347c1.968 1.841 3.404 3.09 4.417 3.926c1.694 1.397 4.028 1.397 5.722 0c1.013-.835 2.45-2.085 4.417-3.926c2.692-.09 4.591-.222 5.897-.347c2.186-.21 3.836-1.86 4.046-4.045c.125-1.306.258-3.206.347-5.898c1.84-1.967 3.09-3.403 3.924-4.415c1.397-1.694 1.397-4.028 0-5.721c-.834-1.013-2.083-2.45-3.924-4.416c-.09-2.694-.222-4.593-.347-5.9c-.21-2.185-1.86-3.835-4.046-4.045c-1.306-.126-3.206-.258-5.9-.347c-1.966-1.84-3.402-3.09-4.415-3.925',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 3,
                scaleY: 3
            });
            break;

        case 'octagram-sharp':
            // Octagram sharp shape (8-point star with sharp edges)
            shape = new Konva.Path({
                x: centerX - 75,
                y: centerY - 75,
                data: 'M9.5 4L12 1l3 3h5v5l3 3l-3 3v5h-5l-3 3l-3-3H4v-5l-3-3l3-3V4z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-facebook':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978c.401 0 .955.042 1.468.103a9 9 0 0 1 1.141.195v3.325a9 9 0 0 0-.653-.036a26 26 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103l-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-facebook-alt':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-instagram-outline':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4zm9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8A1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5a5 5 0 0 1-5 5a5 5 0 0 1-5-5a5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-whatsapp':
            shape = new Konva.Path({
                x: centerX - 48,
                y: centerY - 48,
                data: 'M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144l-2.494.654l.666-2.433l-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931a6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646c-.182-.065-.315-.099-.445.099c-.133.197-.513.646-.627.775c-.114.133-.232.148-.43.05c-.197-.1-.836-.308-1.592-.985c-.59-.525-.985-1.175-1.103-1.372c-.114-.198-.011-.304.088-.403c.087-.088.197-.232.296-.346c.1-.114.133-.198.198-.33c.065-.134.034-.248-.015-.347c-.05-.099-.445-1.076-.612-1.47c-.16-.389-.323-.335-.445-.34c-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992c.47.205.84.326 1.129.418c.475.152.904.129 1.246.08c.38-.058 1.171-.48 1.338-.943c.164-.464.164-.86.114-.943c-.049-.084-.182-.133-.38-.232',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-twitter':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584l-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-threads':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M16.586 4.194c-1.04-.292-2.157-.41-3.335-.38c-2.38.062-4.165.793-5.46 2.235l1.418 1.073c.934-1.037 2.22-1.56 3.943-1.605c.87-.022 1.69.062 2.448.255c1.143.292 2.038.83 2.663 1.598c.524.644.866 1.49 1.02 2.52c-.77-.328-1.66-.552-2.663-.665c-2.736-.305-4.998.337-6.374 1.812c-1.145 1.23-1.628 2.796-1.462 4.574c.157 1.694.949 3.103 2.301 4.094c1.21.886 2.737 1.305 4.425 1.215c1.933-.103 3.505-.76 4.68-1.958c.926-.944 1.577-2.16 1.944-3.622c.167-.662.274-1.41.324-2.234c.065-1.073.03-2.05-.109-2.927c-.24-1.544-.805-2.87-1.689-3.955c-.977-1.194-2.307-2-3.972-2.404zM6.807 17.046c-.117-1.264.214-2.32.995-3.165c.975-1.048 2.65-1.533 4.893-1.28c.753.083 1.421.238 2 .456c-.046.45-.114.867-.203 1.248c-.267 1.144-.73 2.078-1.388 2.795c-.843.914-1.968 1.403-3.352 1.475c-1.215.063-2.248-.22-3.082-.838c-.85-.624-1.335-1.548-1.445-2.684z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-tiktok':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M12.525.02c1.31-.02 2.61-.01 3.91-.02c.08 1.53.63 3.09 1.75 4.17c1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97c-.57-.26-1.1-.59-1.62-.93c-.01 2.92.01 5.84-.02 8.75c-.08 1.4-.54 2.79-1.35 3.94c-1.31 1.92-3.58 3.17-5.91 3.21c-1.43.08-2.86-.31-4.08-1.03c-2.02-1.19-3.44-3.37-3.65-5.71c-.02-.5-.03-1-.01-1.49c.18-1.9 1.12-3.72 2.58-4.96c1.66-1.44 3.98-2.13 6.15-1.72c.02 1.48-.04 2.96-.04 4.44c-.99-.32-2.15-.23-3.02.37c-.63.41-1.11 1.04-1.36 1.75c-.21.51-.15 1.07-.14 1.61c.24 1.64 1.82 3.02 3.5 2.87c1.12-.01 2.19-.66 2.77-1.61c.19-.33.4-.67.41-1.06c.1-1.79.06-3.57.07-5.36c.01-4.03-.01-8.05.02-12.07z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-email':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 4l-8 5l-8-5V6l8 5l8-5z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-phone':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24c1.12.37 2.33.57 3.57.57c.55 0 1 .45 1 1V20c0 .55-.45 1-1 1c-9.39 0-17-7.61-17-17c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1c0 1.25.2 2.45.57 3.57c.11.35.03.74-.25 1.02z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-mobile':
            shape = new Konva.Path({
                x: centerX - 45,
                y: centerY - 72,
                data: 'M2.25 0A2.25 2.25 0 0 0 0 2.25v19.5A2.25 2.25 0 0 0 2.25 24h10.5A2.25 2.25 0 0 0 15 21.75V2.25A2.25 2.25 0 0 0 12.75 0zM7.5 22.5a1.5 1.5 0 1 1 0-3a1.5 1.5 0 0 1 0 3M12 18H3V3h9z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-phone-call':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 7.98v3a2 2 0 0 1-2.18 2a19.8 19.8 0 0 1-8.63-3.07a19.5 19.5 0 0 1-6-6a19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72a12.8 12.8 0 0 0 .7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45a12.8 12.8 0 0 0 2.81.7A2 2 0 0 1 22 16.92',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-zoom':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M1.984 7.506v6.74c.006 1.524 1.361 2.75 3.014 2.745H10.9c1.653.005 3.008-1.22 3.014-2.745v-6.74A2.75 2.75 0 0 0 10.9 4.761H4.998a2.75 2.75 0 0 0-3.014 2.745M15.726 5c-.686.004-1.24.507-1.24 1.125v9.75c0 .618.554 1.121 1.24 1.125c.686-.004 1.24-.507 1.24-1.125v-1.726l4.396 2.883c.578.379.93.132.93-.655V7.623c0-.787-.352-1.034-.93-.655l-4.396 2.883V7.125c0-.618-.554-1.121-1.24-1.125',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'icon-zoom-alt':
            shape = new Konva.Path({
                x: centerX - 72,
                y: centerY - 72,
                data: 'M1.923 6.038C.697 6.07.014 6.866 0 7.95v8.1c.014 1.084.697 1.88 1.923 1.912h9.098c1.226-.033 1.909-.828 1.923-1.912v-8.1c-.014-1.084-.697-1.88-1.923-1.912zm13.268.506c-.356 0-.648.224-.648.5v1.636l-1.746-1.048a.642.642 0 0 0-.642 1.11l2.388 1.432v2.752l-2.388 1.433a.642.642 0 1 0 .642 1.11l1.746-1.049v1.636c0 .277.292.5.648.5s.648-.223.648-.5V7.044c0-.276-.292-.5-.648-.5m6.154.064c-.356 0-.648.224-.648.5v1.636l-1.746-1.048a.642.642 0 0 0-.642 1.11l2.388 1.432v2.752l-2.388 1.433a.642.642 0 0 0 .642 1.11l1.746-1.049v1.636c0 .277.292.5.648.5s.648-.223.648-.5V7.108c0-.276-.292-.5-.648-.5',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName,
                scaleX: 6,
                scaleY: 6
            });
            break;

        case 'heart':
            // Heart shape using path
            shape = new Konva.Path({
                x: centerX - 50,
                y: centerY - 50,
                data: 'M 50 90 C 50 90 10 60 10 35 C 10 20 20 10 30 10 C 40 10 50 20 50 20 C 50 20 60 10 70 10 C 80 10 90 20 90 35 C 90 60 50 90 50 90 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'diamond':
            shape = new Konva.RegularPolygon({
                x: centerX,
                y: centerY,
                sides: 4,
                radius: 75,
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'line':
            shape = new Konva.Line({
                points: [centerX - 100, centerY, centerX + 100, centerY],
                stroke: '#D1D5DB',
                strokeWidth: 3,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'arrow-line':
            shape = new Konva.Arrow({
                points: [centerX - 100, centerY, centerX + 100, centerY],
                stroke: '#D1D5DB',
                strokeWidth: 3,
                fill: '#D1D5DB',
                pointerLength: 10,
                pointerWidth: 10,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'speech-bubble':
            shape = new Konva.Path({
                x: centerX - 50,
                y: centerY - 50,
                data: 'M 10 20 Q 10 10 20 10 L 80 10 Q 90 10 90 20 L 90 60 Q 90 70 80 70 L 30 70 L 20 85 L 25 70 L 20 70 Q 10 70 10 60 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'house':
            shape = new Konva.Path({
                x: centerX - 50,
                y: centerY - 50,
                data: 'M 50 10 L 90 45 L 80 45 L 80 85 L 60 85 L 60 60 L 40 60 L 40 85 L 20 85 L 20 45 L 10 45 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'water-drop':
            shape = new Konva.Path({
                x: centerX - 50,
                y: centerY - 50,
                data: 'M 50 10 C 50 10 25 40 25 60 C 25 75 35 90 50 90 C 65 90 75 75 75 60 C 75 40 50 10 50 10 Z',
                fill: '#D1D5DB',
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;
    }

    if (shape) {
        shape.on('click tap', function() {
            selectObject(shape);
        });

        layer.add(shape);
        layer.batchDraw();
        updateLayersList();
        selectObject(shape);
        showToast('Shape added to canvas');
    }
}

// Update Shape Properties
function updateShapeStrokeWidth() {
    const value = parseInt(document.getElementById('shapeStrokeWidth').value);
    if (selectedObject) {
        selectedObject.strokeWidth(value);
        layer.batchDraw();
    }
}

function updateShapeOpacity() {
    const value = parseInt(document.getElementById('shapeOpacity').value);
    const opacity = value / 10;
    if (selectedObject) {
        selectedObject.opacity(opacity);
        layer.batchDraw();
    }
}

function updateShapeBorderRadius() {
    const value = parseInt(document.getElementById('shapeBorderRadius').value);
    document.getElementById('borderRadiusValue').textContent = value;

    if (selectedObject && selectedObject.className === 'Rect') {
        selectedObject.cornerRadius(value);
        layer.batchDraw();
    }
}

function duplicateShape() {
    if (selectedObject) {
        const clone = selectedObject.clone();
        clone.x(selectedObject.x() + 20);
        clone.y(selectedObject.y() + 20);
        clone.name('shape-' + Date.now());
        layer.add(clone);

        clone.on('click tap', function() {
            selectObject(clone);
        });

        layer.batchDraw();
        updateLayersList();
        selectObject(clone);
        showToast('Shape duplicated');
    }
}

// Add gradient shape
function addGradientShape(shapeType) {
    let shape;
    const centerX = stage.width() / 2;
    const centerY = stage.height() / 2;
    const shapeName = 'shape-' + Date.now();

    // Gradient color presets
    const gradientPresets = {
        rect: { start: '#667eea', end: '#764ba2' },
        circle: { start: '#f093fb', end: '#f5576c' },
        star: { start: '#ffecd2', end: '#fcb69f' },
        heart: { start: '#fa709a', end: '#fee140' }
    };

    const colors = gradientPresets[shapeType] || { start: '#667eea', end: '#764ba2' };

    switch(shapeType) {
        case 'rect':
            shape = new Konva.Rect({
                x: centerX - 75,
                y: centerY - 75,
                width: 150,
                height: 150,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'circle':
            shape = new Konva.Circle({
                x: centerX,
                y: centerY,
                radius: 75,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'star':
            shape = new Konva.Star({
                x: centerX,
                y: centerY,
                numPoints: 5,
                innerRadius: 40,
                outerRadius: 75,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;

        case 'heart':
            // Heart as SVG path
            shape = new Konva.Path({
                x: centerX - 75,
                y: centerY - 75,
                data: 'M75,135 C75,135 15,90 15,52.5 C15,30 30,15 45,15 C60,15 75,30 75,30 C75,30 90,15 105,15 C120,15 135,30 135,52.5 C135,90 75,135 75,135 Z',
                scaleX: 1,
                scaleY: 1,
                draggable: true,
                name: shapeName,
                id: shapeName
            });
            break;
    }

    if (!shape) return;

    // Calculate gradient points for 135deg angle
    const angle = 135 * (Math.PI / 180);
    const width = shape.width ? shape.width() : 150;
    const height = shape.height ? shape.height() : 150;

    const centerShapeX = width / 2;
    const centerShapeY = height / 2;
    const length = Math.max(width, height);

    const startX = centerShapeX - (length / 2) * Math.cos(angle);
    const startY = centerShapeY - (length / 2) * Math.sin(angle);
    const endX = centerShapeX + (length / 2) * Math.cos(angle);
    const endY = centerShapeY + (length / 2) * Math.sin(angle);

    // Convert to RGBA (default 100% opacity)
    const startColorRGBA = hexToRgba(colors.start, 100);
    const endColorRGBA = hexToRgba(colors.end, 100);

    shape.fillLinearGradientStartPoint({ x: startX, y: startY });
    shape.fillLinearGradientEndPoint({ x: endX, y: endY });
    shape.fillLinearGradientColorStops([0, startColorRGBA, 1, endColorRGBA]);

    // Store gradient metadata
    shape.setAttr('hasGradient', true);
    shape.setAttr('gradientStartColor', colors.start);
    shape.setAttr('gradientEndColor', colors.end);
    shape.setAttr('gradientStartOpacity', 100);
    shape.setAttr('gradientEndOpacity', 100);
    shape.setAttr('gradientAngle', 135);

    layer.add(shape);

    shape.on('click tap', function() {
        selectObject(shape);
    });

    layer.batchDraw();
    updateLayersList();
    selectObject(shape);
    showToast('Gradient shape added');
}

// Toggle fill type (solid / gradient)
function setFillType(type) {
    const solidBtn = document.getElementById('fillTypeSolid');
    const gradientBtn = document.getElementById('fillTypeGradient');
    const solidControl = document.getElementById('solidFillControl');
    const gradientControls = document.getElementById('gradientFillControls');

    if (type === 'solid') {
        solidBtn.className = 'flex-1 h-9 flex items-center justify-center gap-1 bg-indigo-100 border border-indigo-300 rounded transition text-xs';
        solidBtn.querySelector('span').className = 'text-indigo-700 font-medium';
        gradientBtn.className = 'flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 border border-gray-200 rounded transition text-xs';
        gradientBtn.querySelector('span').className = 'text-gray-700';
        solidControl.classList.remove('hidden');
        gradientControls.classList.add('hidden');

        // Remove gradient from selected shape (only if user action, not when updating UI)
        if (!isUpdatingPickrProgrammatically && selectedObject && selectedObject.getClassName() !== 'Image' && selectedObject.getClassName() !== 'Text') {
            // Get current fill color from Pickr (with RGBA support) or use default
            let currentFill = '#D1D5DB';
            if (shapeFillPicker) {
                const rgba = shapeFillPicker.getColor().toRGBA();
                currentFill = rgbaToString(rgba);
            }
            selectedObject.fill(currentFill);
            selectedObject.fillLinearGradientStartPoint(null);
            selectedObject.fillLinearGradientEndPoint(null);
            selectedObject.fillLinearGradientColorStops(null);
            selectedObject.setAttr('hasGradient', false);
            layer.batchDraw();
        }
    } else {
        solidBtn.className = 'flex-1 h-9 flex items-center justify-center gap-1 bg-gray-50 border border-gray-200 rounded transition text-xs';
        solidBtn.querySelector('span').className = 'text-gray-700';
        gradientBtn.className = 'flex-1 h-9 flex items-center justify-center gap-1 bg-indigo-100 border border-indigo-300 rounded transition text-xs';
        gradientBtn.querySelector('span').className = 'text-indigo-700 font-medium';
        solidControl.classList.add('hidden');
        gradientControls.classList.remove('hidden');

        // Only apply default gradient if shape doesn't have gradient yet
        // Check by looking at actual gradient, not cached attribute
        const hasGradient = selectedObject && selectedObject.fillLinearGradientColorStops();

        if (selectedObject && !hasGradient) {
            // Apply default gradient for new shapes
            const startColor = '#667eea';
            const endColor = '#764ba2';
            const startOpacity = 100;
            const endOpacity = 100;

            applyGradientToShape(startColor, startOpacity, endColor, endOpacity);

            // Update Pickr to show default colors
            if (gradientStartPicker) {
                gradientStartPicker.setColor(startColor);
            }
            if (gradientEndPicker) {
                gradientEndPicker.setColor(endColor);
            }
        }
        // If shape already has gradient, don't reapply - just show the UI controls
    }
}

// Helper: Convert hex to RGBA
function hexToRgba(hex, opacity) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity / 100})`;
}

// Update gradient angle
function updateGradientAngle() {
    // Ignore if we're updating angle programmatically
    if (isUpdatingPickrProgrammatically) {
        return;
    }

    if (!selectedObject) return;

    const angle = parseInt(document.getElementById('gradientAngle').value);
    document.getElementById('gradientAngleValue').textContent = angle + '°';

    // Get current colors and opacities from ACTUAL gradient (not from cached attributes)
    const colorStops = selectedObject.fillLinearGradientColorStops();

    let startColor = '#667eea';
    let endColor = '#764ba2';
    let startOpacity = 100;
    let endOpacity = 100;

    if (colorStops && colorStops.length >= 4) {
        // Parse start color RGBA
        const startRgbaMatch = colorStops[1].match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
        if (startRgbaMatch) {
            const r = parseInt(startRgbaMatch[1]);
            const g = parseInt(startRgbaMatch[2]);
            const b = parseInt(startRgbaMatch[3]);
            const a = parseFloat(startRgbaMatch[4] || 1);
            startColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
            startOpacity = Math.round(a * 100);
        }

        // Parse end color RGBA
        const endRgbaMatch = colorStops[3].match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
        if (endRgbaMatch) {
            const r = parseInt(endRgbaMatch[1]);
            const g = parseInt(endRgbaMatch[2]);
            const b = parseInt(endRgbaMatch[3]);
            const a = parseFloat(endRgbaMatch[4] || 1);
            endColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
            endOpacity = Math.round(a * 100);
        }
    }

    // Apply gradient with new angle
    applyGradientToShape(startColor, startOpacity, endColor, endOpacity);
}

// Apply gradient to shape with explicit parameters
function applyGradientToShape(startColor, startOpacity, endColor, endOpacity) {
    if (!selectedObject) return;

    const angle = parseInt(document.getElementById('gradientAngle').value) || 135;

    console.log('applyGradientToShape:', {startColor, startOpacity, endColor, endOpacity, angle});

    // Convert to RGBA
    const startColorRGBA = hexToRgba(startColor, startOpacity);
    const endColorRGBA = hexToRgba(endColor, endOpacity);

    // Calculate gradient direction
    const radians = angle * (Math.PI / 180);
    const width = selectedObject.width ? selectedObject.width() : 200;
    const height = selectedObject.height ? selectedObject.height() : 200;

    const centerX = width / 2;
    const centerY = height / 2;
    const length = Math.max(width, height);

    const startX = centerX - (length / 2) * Math.cos(radians);
    const startY = centerY - (length / 2) * Math.sin(radians);
    const endX = centerX + (length / 2) * Math.cos(radians);
    const endY = centerY + (length / 2) * Math.sin(radians);

    // Apply gradient
    selectedObject.fillLinearGradientStartPoint({ x: startX, y: startY });
    selectedObject.fillLinearGradientEndPoint({ x: endX, y: endY });
    selectedObject.fillLinearGradientColorStops([0, startColorRGBA, 1, endColorRGBA]);
    selectedObject.fill(null);

    // Store metadata
    selectedObject.setAttr('hasGradient', true);
    selectedObject.setAttr('gradientStartColor', startColor);
    selectedObject.setAttr('gradientEndColor', endColor);
    selectedObject.setAttr('gradientStartOpacity', startOpacity);
    selectedObject.setAttr('gradientEndOpacity', endOpacity);
    selectedObject.setAttr('gradientAngle', angle);

    layer.batchDraw();
}

// Update shape gradient
function updateShapeGradient() {
    if (!selectedObject || selectedObject.getClassName() === 'Image' || selectedObject.getClassName() === 'Text') {
        console.log('updateShapeGradient: No valid object selected');
        return;
    }

    // Get values from stored attributes (updated by Pickr)
    const startColor = selectedObject.getAttr('gradientStartColor') || '#667eea';
    const endColor = selectedObject.getAttr('gradientEndColor') || '#764ba2';
    const startOpacity = selectedObject.getAttr('gradientStartOpacity') || 100;
    const endOpacity = selectedObject.getAttr('gradientEndOpacity') || 100;
    const angle = parseInt(document.getElementById('gradientAngle').value);

    console.log('updateShapeGradient:', {startColor, endColor, startOpacity, endOpacity, angle});

    document.getElementById('gradientAngleValue').textContent = angle + '°';

    // Convert to RGBA
    const startColorRGBA = hexToRgba(startColor, startOpacity);
    const endColorRGBA = hexToRgba(endColor, endOpacity);

    console.log('RGBA colors:', {startColorRGBA, endColorRGBA});

    // Calculate gradient direction based on angle
    const radians = angle * (Math.PI / 180);
    const width = selectedObject.width ? selectedObject.width() : 200;
    const height = selectedObject.height ? selectedObject.height() : 200;

    const centerX = width / 2;
    const centerY = height / 2;

    const length = Math.max(width, height);
    const startX = centerX - (length / 2) * Math.cos(radians);
    const startY = centerY - (length / 2) * Math.sin(radians);
    const endX = centerX + (length / 2) * Math.cos(radians);
    const endY = centerY + (length / 2) * Math.sin(radians);

    selectedObject.fillLinearGradientStartPoint({ x: startX, y: startY });
    selectedObject.fillLinearGradientEndPoint({ x: endX, y: endY });
    selectedObject.fillLinearGradientColorStops([0, startColorRGBA, 1, endColorRGBA]);
    selectedObject.fill(null); // Remove solid fill

    // Store metadata
    selectedObject.setAttr('hasGradient', true);
    selectedObject.setAttr('gradientStartColor', startColor);
    selectedObject.setAttr('gradientEndColor', endColor);
    selectedObject.setAttr('gradientStartOpacity', startOpacity);
    selectedObject.setAttr('gradientEndOpacity', endOpacity);
    selectedObject.setAttr('gradientAngle', angle);

    layer.batchDraw();
}

function deleteShape() {
    if (selectedObject) {
        selectedObject.destroy();
        transformer.nodes([]);
        selectedObject = null;
        hideObjectProperties();
        layer.batchDraw();
        updateLayersList();
        showToast('Shape deleted');
    }
}

// Image Accordion Toggle
function toggleImageAccordion(section) {
    const accordion = document.getElementById('accordion' + section.charAt(0).toUpperCase() + section.slice(1));
    const icon = document.getElementById('icon' + section.charAt(0).toUpperCase() + section.slice(1));

    // Close all other accordions
    document.querySelectorAll('.accordion-content').forEach(el => {
        if (el !== accordion) {
            el.classList.add('hidden');
        }
    });

    // Reset all icons
    document.querySelectorAll('[id^="icon"]').forEach(el => {
        if (el !== icon) {
            el.classList.remove('rotate-0');
            el.classList.add('-rotate-90');
        }
    });

    // Toggle current accordion
    accordion.classList.toggle('hidden');

    // Toggle icon rotation
    if (accordion.classList.contains('hidden')) {
        icon.classList.remove('rotate-0');
        icon.classList.add('-rotate-90');
    } else {
        icon.classList.remove('-rotate-90');
        icon.classList.add('rotate-0');
    }
}

// Apply Image Filter Presets
function applyImageFilter(filterType) {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    // KRUSIAL: Hapus cache lama (menghindari filter stacking)
    selectedObject.clearCache();

    // Reset semua filter dan RGB values
    selectedObject.filters([]);
    selectedObject.red(0);
    selectedObject.green(0);
    selectedObject.blue(0);
    if (selectedObject.brightness) selectedObject.brightness(0);

    switch(filterType) {
        case 'original':
            // Reset to original - no filters
            selectedObject.filters([]);
            break;
        case 'grayscale':
            selectedObject.filters([Konva.Filters.Grayscale]);
            break;
        case 'sepia':
            selectedObject.filters([Konva.Filters.Sepia]);
            break;
    }

    // Cache ulang SETELAH filter diterapkan
    selectedObject.cache();
    layer.batchDraw();

    // Reset sliders ke default
    document.getElementById('imageBrightness').value = 1;
    document.getElementById('imageContrast').value = 0;
    document.getElementById('imageHue').value = 0;
    document.getElementById('imageSaturation').value = 0;
    document.getElementById('imageLuminance').value = 0;

    showToast('Filter applied: ' + filterType);
}

// Update Image Effects (Manual Adjustments) - Konva Standard
function updateImageEffect() {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    // KRUSIAL: Clear cache dulu
    selectedObject.clearCache();

    const brightness = parseFloat(document.getElementById('imageBrightness').value);
    const contrast = parseInt(document.getElementById('imageContrast').value);
    const hue = parseInt(document.getElementById('imageHue').value);
    const saturation = parseFloat(document.getElementById('imageSaturation').value);
    const luminance = parseFloat(document.getElementById('imageLuminance').value);

    // Build filter array
    const filters = [];

    // Brightness (Konva standard: 0 to 2, default 1)
    if (brightness !== 1) {
        filters.push(Konva.Filters.Brighten);
        selectedObject.brightness(brightness - 1); // Convert 0-2 to -1 to 1
    }

    // Contrast (Konva standard: -100 to 100)
    if (contrast !== 0) {
        filters.push(Konva.Filters.Contrast);
        selectedObject.contrast(contrast);
    }

    // HSL (Hue, Saturation, Luminance)
    if (hue !== 0 || saturation !== 0 || luminance !== 0) {
        filters.push(Konva.Filters.HSL);
        selectedObject.hue(hue);           // 0-359
        selectedObject.saturation(saturation); // -2 to 10
        selectedObject.luminance(luminance);   // -2 to 2
    }

    selectedObject.filters(filters.length > 0 ? filters : []);
    selectedObject.cache();
    layer.batchDraw();
}

// Apply Clipping Mask
function applyClippingMask(shapeType) {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    const imgWidth = selectedObject.width();
    const imgHeight = selectedObject.height();
    const size = Math.min(imgWidth, imgHeight);

    selectedObject.sceneFunc(function(ctx, shape) {
        const centerX = imgWidth / 2;
        const centerY = imgHeight / 2;

        ctx.save();
        ctx.beginPath();

        switch(shapeType) {
            case 'rect':
                ctx.rect(0, 0, imgWidth, imgHeight);
                break;

            case 'circle':
                ctx.arc(centerX, centerY, size / 2, 0, Math.PI * 2);
                break;

            case 'triangle':
                const triR = size / 2;
                for (let i = 0; i < 3; i++) {
                    const angle = (i * 2 * Math.PI) / 3 - Math.PI / 2;
                    const x = centerX + triR * Math.cos(angle);
                    const y = centerY + triR * Math.sin(angle);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                break;

            case 'star':
                const outerR = size / 2;
                const innerR = outerR * 0.5;
                for (let i = 0; i < 10; i++) {
                    const angle = (i * Math.PI) / 5 - Math.PI / 2;
                    const r = i % 2 === 0 ? outerR : innerR;
                    const x = centerX + r * Math.cos(angle);
                    const y = centerY + r * Math.sin(angle);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                break;

            case 'pentagon':
                const pentR = size / 2;
                for (let i = 0; i < 5; i++) {
                    const angle = (i * 2 * Math.PI) / 5 - Math.PI / 2;
                    const x = centerX + pentR * Math.cos(angle);
                    const y = centerY + pentR * Math.sin(angle);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                break;

            case 'hexagon':
                const hexR = size / 2;
                for (let i = 0; i < 6; i++) {
                    const angle = (i * 2 * Math.PI) / 6;
                    const x = centerX + hexR * Math.cos(angle);
                    const y = centerY + hexR * Math.sin(angle);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                break;

            case 'diamond':
                const dR = size / 2;
                for (let i = 0; i < 4; i++) {
                    const angle = (i * 2 * Math.PI) / 4;
                    const x = centerX + dR * Math.cos(angle);
                    const y = centerY + dR * Math.sin(angle);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.closePath();
                break;

            case 'heart':
                const h = size * 0.8;
                const w = h;
                const x = centerX - w / 2;
                const y = centerY - h / 2.5;
                ctx.moveTo(x + w / 2, y + h / 4);
                ctx.bezierCurveTo(x + w / 2, y, x, y, x, y + h / 4);
                ctx.bezierCurveTo(x, y + h / 2, x + w / 4, y + h * 0.75, x + w / 2, y + h);
                ctx.bezierCurveTo(x + w * 0.75, y + h * 0.75, x + w, y + h / 2, x + w, y + h / 4);
                ctx.bezierCurveTo(x + w, y, x + w / 2, y, x + w / 2, y + h / 4);
                ctx.closePath();
                break;

            case 'waterdrop':
                const dropR = size / 2;
                ctx.moveTo(centerX, centerY - dropR);
                ctx.bezierCurveTo(
                    centerX - dropR * 0.5, centerY - dropR * 0.5,
                    centerX - dropR * 0.7, centerY + dropR * 0.3,
                    centerX, centerY + dropR
                );
                ctx.bezierCurveTo(
                    centerX + dropR * 0.7, centerY + dropR * 0.3,
                    centerX + dropR * 0.5, centerY - dropR * 0.5,
                    centerX, centerY - dropR
                );
                ctx.closePath();
                break;
        }

        // Apply clipping region
        ctx.clip();

        // Draw the image within the clipped region
        const img = shape.image();
        if (img) {
            ctx.drawImage(img, 0, 0, imgWidth, imgHeight);
        }

        // Restore context state
        ctx.restore();
    });

    // Cache the object to apply the clipping mask
    selectedObject.cache();

    layer.batchDraw();

    // Show remove mask button
    document.getElementById('removeMaskBtn').classList.remove('hidden');

    showToast(`Clipping mask: ${shapeType}`);
}

// Remove Clipping Mask
function removeMask() {
    if (!selectedObject || selectedObject.className !== 'Image') return;

    // Remove sceneFunc to restore original image
    selectedObject.sceneFunc(undefined);
    selectedObject.clearCache();
    layer.batchDraw();

    // Hide remove button
    document.getElementById('removeMaskBtn').classList.add('hidden');

    showToast('Clipping mask removed');
}

// Remove Image Background (AI)
function removeImageBackground() {
    if (!selectedObject || selectedObject.className !== 'Image') {
        showToast('Please select an image first', 'error');
        return;
    }

    const button = document.getElementById('removeBgBtn');
    const originalHtml = button.innerHTML;

    // Show loading state
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="text-gray-700 ml-2">Processing...</span>';

    // Convert current image to base64
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = selectedObject.image();

    canvas.width = img.width;
    canvas.height = img.height;
    ctx.drawImage(img, 0, 0);

    const imageData = canvas.toDataURL('image/png');

    // Call API
    fetch('{{ route("image-editor.remove-background") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ image_data: imageData })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Load new image
            const newImg = new Image();
            newImg.crossOrigin = 'anonymous';
            newImg.onload = function() {
                selectedObject.image(newImg);
                layer.batchDraw();
                showToast('Background removed!');
            };
            newImg.src = data.url;
        } else {
            showToast(data.error || 'Failed to remove background', 'error');
        }
    })
    .catch(error => {
        console.error('Remove BG error:', error);
        showToast('Error occurred. Try again.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}

// Text Styling Modal
let textStylingData = [];
let currentSelectedWordIndex = null;

function openTextStylingModal() {
    if (!selectedObject || selectedObject.className !== 'Text') {
        showToast('Please select a text object first', 'error');
        return;
    }

    // Validate text length
    const fullText = selectedObject.text();
    if (fullText.length > 50) {
        showToast('Text too long! Use this feature for short text only (max 50 characters)', 'error');
        return;
    }

    const modal = document.getElementById('textStylingModal');
    const container = document.getElementById('textWordsContainer');
    const colorPicker = document.getElementById('wordColorPicker');

    // Split into words
    const words = fullText.split(/(\s+)/); // Split but keep spaces

    // Get current text color
    const currentColor = selectedObject.fill() || '#000000';

    // Initialize styling data
    textStylingData = words.map((word, index) => ({
        text: word,
        color: currentColor,
        index: index
    }));

    // Render words
    renderWordsInModal();

    // Show modal
    modal.classList.remove('hidden');
}

function renderWordsInModal() {
    const container = document.getElementById('textWordsContainer');
    const currentFont = selectedObject.fontFamily() || 'Inter';
    const currentSize = Math.max(24, Math.min(selectedObject.fontSize() || 40, 48)); // Clamp between 24-48px for preview

    container.innerHTML = textStylingData.map((item, index) => {
        const isSpace = /^\s+$/.test(item.text);
        const isSelected = currentSelectedWordIndex === index;

        if (isSpace) {
            // Render space as transparent span
            return `<span style="width: ${item.text.length * 8}px; display: inline-block;"></span>`;
        }

        return `
            <span onclick="selectWordForStyling(${index})"
                  class="cursor-pointer px-2 py-1 rounded transition ${isSelected ? 'bg-indigo-100 ring-2 ring-indigo-500' : 'hover:bg-gray-200'}"
                  style="color: ${item.color}; font-family: ${currentFont}; font-size: ${currentSize}px; font-weight: ${selectedObject.fontStyle() === 'bold' ? 'bold' : 'normal'};">
                ${item.text}
            </span>
        `;
    }).join('');
}

function selectWordForStyling(index) {
    currentSelectedWordIndex = index;
    const item = textStylingData[index];

    // Update color picker
    document.getElementById('wordColorPicker').value = item.color;

    // Re-render to show selection
    renderWordsInModal();
}

function closeTextStylingModal() {
    document.getElementById('textStylingModal').classList.add('hidden');
    textStylingData = [];
    currentSelectedWordIndex = null;
}

function applyTextStyling() {
    if (!selectedObject || selectedObject.className !== 'Text') {
        showToast('No text selected', 'error');
        return;
    }

    // Check if there are multiple colors
    const uniqueColors = [...new Set(textStylingData.map(item => item.color))];

    if (uniqueColors.length === 1) {
        // Single color - just update existing text
        selectedObject.fill(uniqueColors[0]);
        layer.batchDraw();
        showToast('Text color updated');
        closeTextStylingModal();
        return;
    }

    // Multiple colors - show warning and confirm
    if (!confirm('⚠️ WARNING: Multi-color text will be converted to a group and CANNOT be edited as text anymore. The text will become non-editable shapes.\n\nThis is best for final touches only.\n\nContinue?')) {
        return;
    }

    // Multiple colors - convert to Group with multiple Text objects
    const originalPos = {
        x: selectedObject.x(),
        y: selectedObject.y(),
        rotation: selectedObject.rotation(),
        scaleX: selectedObject.scaleX(),
        scaleY: selectedObject.scaleY()
    };

    const fontSize = selectedObject.fontSize();
    const fontFamily = selectedObject.fontFamily();
    const fontStyle = selectedObject.fontStyle();
    const align = selectedObject.align();

    // Create group
    const group = new Konva.Group({
        x: originalPos.x,
        y: originalPos.y,
        rotation: originalPos.rotation,
        scaleX: originalPos.scaleX,
        scaleY: originalPos.scaleY,
        draggable: true
    });

    let xOffset = 0;
    let yOffset = 0;

    // Create text for each word/segment
    textStylingData.forEach(item => {
        const text = new Konva.Text({
            x: xOffset,
            y: yOffset,
            text: item.text,
            fontSize: fontSize,
            fontFamily: fontFamily,
            fontStyle: fontStyle,
            fill: item.color
        });

        // Update x offset for next word
        xOffset += text.width();

        group.add(text);
    });

    // Remove old text, add group
    selectedObject.destroy();
    layer.add(group);

    // Select the new group
    selectObject(group);

    layer.batchDraw();
    showToast('Rich text styling applied!');
    closeTextStylingModal();
}

// Listen to color picker change
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('wordColorPicker');
    if (colorPicker) {
        colorPicker.addEventListener('input', function() {
            if (currentSelectedWordIndex !== null) {
                textStylingData[currentSelectedWordIndex].color = this.value;
                renderWordsInModal();
            }
        });
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initKonva();
    initColorPickers();
    document.getElementById('canvasWidth').value = stage.width();
    document.getElementById('canvasHeight').value = stage.height();
});

// Apply Branding - Replace brand variables with user branding data
function applyBranding() {
    console.log('Apply Branding clicked');

    fetch('/api/user-branding')
        .then(res => res.json())
        .then(data => {
            console.log('Branding data received:', data);

            if (data.success && data.branding) {
                const branding = data.branding;
                console.log('Branding:', branding);

                // Replace all brand variables in canvas
                let replacedCount = 0;
                layer.find('Text, Image, Rect, Circle').forEach(shape => {
                    console.log('Checking shape:', shape.className, 'isVariable:', shape.getAttr('isVariable'), 'colorVariable:', shape.getAttr('colorVariable'));

                    // Replace brand logo
                    if (shape.className === 'Image' && shape.getAttr('isVariable') === 'brand_logo') {
                        console.log('Replacing brand logo with:', branding.logo_url);
                        replacedCount++;
                        if (branding.logo_url) {
                            const imageObj = new Image();
                            imageObj.crossOrigin = 'Anonymous';
                            imageObj.onload = function() {
                                console.log('Logo image loaded successfully');
                                shape.image(imageObj);
                                // Remove variable marker
                                shape.setAttr('isVariable', null);
                                shape.stroke(null);
                                shape.strokeWidth(0);
                                shape.dash([]);
                                layer.batchDraw();
                                console.log('Logo applied and canvas redrawn');
                            };
                            imageObj.onerror = function() {
                                console.error('Failed to load logo image:', branding.logo_url);
                            };
                            imageObj.src = branding.logo_url;
                        }
                    }

                    // Replace brand colors
                    if (['Rect', 'Circle'].includes(shape.className) && shape.getAttr('colorVariable')) {
                        const colorVar = shape.getAttr('colorVariable');
                        console.log('Replacing color variable:', colorVar);
                        console.log('Current fill before replace:', shape.fill());

                        if (colorVar === 'brand_color_primary' && branding.primary_color) {
                            console.log('Setting primary color:', branding.primary_color);
                            shape.fill(branding.primary_color);
                            shape.setAttr('colorVariable', null);
                            replacedCount++;
                            console.log('Fill after replace:', shape.fill());
                        } else if (colorVar === 'brand_color_secondary' && branding.secondary_color) {
                            console.log('Setting secondary color:', branding.secondary_color);
                            shape.fill(branding.secondary_color);
                            shape.setAttr('colorVariable', null);
                            replacedCount++;
                            console.log('Fill after replace:', shape.fill());
                        }
                    }
                });

                console.log('Total replaced:', replacedCount);
                layer.batchDraw();
                showToast(`Branding applied! ${replacedCount} items replaced.`);
            } else {
                showToast('No branding data found. Please set up your branding first.', 'error');
            }
        })
        .catch(error => {
            console.error('Error applying branding:', error);
            showToast('Failed to apply branding', 'error');
        });
}

function openResizeModal() {
    document.getElementById('canvasWidth').value = stage.width();
    document.getElementById('canvasHeight').value = stage.height();
    document.getElementById('resizeModal').classList.remove('hidden');
}

function closeResizeModal() {
    document.getElementById('resizeModal').classList.add('hidden');
}

function setPreset(width, height) {
    document.getElementById('canvasWidth').value = width;
    document.getElementById('canvasHeight').value = height;
}

function applyResize() {
    const newWidth = parseInt(document.getElementById('canvasWidth').value);
    const newHeight = parseInt(document.getElementById('canvasHeight').value);
    const magicResize = document.getElementById('magicResize').checked;

    if (magicResize) {
        const scaleX = newWidth / stage.width();
        const scaleY = newHeight / stage.height();

        stage.find('Text, Rect, Circle, Line, Image').forEach(node => {
            node.x(node.x() * scaleX);
            node.y(node.y() * scaleY);
            node.width(node.width() * scaleX);
            node.height(node.height() * scaleY);
            if (node.fontSize) node.fontSize(node.fontSize() * Math.min(scaleX, scaleY));
        });
    }

    stage.width(newWidth);
    stage.height(newHeight);

    // Calculate optimal zoom
    const zoom = calculateOptimalZoom(newWidth, newHeight);
    stage.scaleX(zoom);
    stage.scaleY(zoom);

    const container = document.getElementById('canvasContainer');
    container.style.width = (newWidth * zoom) + 'px';
    container.style.height = (newHeight * zoom) + 'px';

    updateZoomDisplay();
    closeResizeModal();
}

// Zoom Controls
function updateZoomDisplay() {
    const currentZoom = stage.scaleX();
    const zoomPercent = Math.round(currentZoom * 100);
    document.getElementById('zoomLevel').textContent = zoomPercent + '%';
}

function zoomIn() {
    const currentZoom = stage.scaleX();
    const newZoom = Math.min(currentZoom + 0.1, 2.0); // Max 200%

    stage.scaleX(newZoom);
    stage.scaleY(newZoom);

    const container = document.getElementById('canvasContainer');
    container.style.width = (stage.width() * newZoom) + 'px';
    container.style.height = (stage.height() * newZoom) + 'px';

    updateZoomDisplay();
}

function zoomOut() {
    const currentZoom = stage.scaleX();
    const newZoom = Math.max(currentZoom - 0.1, 0.1); // Min 10%

    stage.scaleX(newZoom);
    stage.scaleY(newZoom);

    const container = document.getElementById('canvasContainer');
    container.style.width = (stage.width() * newZoom) + 'px';
    container.style.height = (stage.height() * newZoom) + 'px';

    updateZoomDisplay();
}

function fitToScreen() {
    const optimalZoom = calculateOptimalZoom(stage.width(), stage.height());

    stage.scaleX(optimalZoom);
    stage.scaleY(optimalZoom);

    const container = document.getElementById('canvasContainer');
    container.style.width = (stage.width() * optimalZoom) + 'px';
    container.style.height = (stage.height() * optimalZoom) + 'px';

    updateZoomDisplay();
}
</script>

<style>
.accordion-content {
    transition: max-height 0.3s ease-out;
}
</style>

@endpush

@endsection
