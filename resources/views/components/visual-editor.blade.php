<!-- Visual Editor Component (Lightweight Fabric.js Editor) -->
@props(['brandings' => collect()])

@push('scripts')
<!-- WebFont Loader for Google Fonts (ensures fonts are loaded before Fabric.js uses them) -->
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>

<!-- Fabric.js -->
<script src="https://cdn.jsdelivr.net/npm/fabric@5.4.0/dist/fabric.min.js"></script>

@verbatim
<script>
// Load Google Fonts before using them in canvas
WebFont.load({
    google: {
        families: ['Poppins:400,700,900', 'Roboto:100,300,400,500,700,900', 'Inter:100,200,300,400,500,600,700,800,900', 'Montserrat:100,200,300,400,500,600,700,800,900']
    },
    active: function() {
        console.log('✓ All Google Fonts loaded successfully');
    },
    inactive: function() {
        console.error('✗ Google Fonts failed to load');
    }
});

// Fallback globals
window.editableHeadline = window.editableHeadline || '';
window.editableSubheadline = window.editableSubheadline || '';
window.isUploading = window.isUploading || false;
</script>
@endverbatim
@endpush

<div x-data="visualEditorData" x-init="brandings = {{ json_encode($brandings) }}" x-show="isOpen" x-cloak @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-hidden" style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="h-screen bg-gray-900 rounded-lg w-full max-w-7xl max-h-[95vh] flex flex-col shadow-2xl text-white">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
                <div>
                    <h2 class="text-xl font-semibold">Visual Editor</h2>
                    <p class="text-sm text-gray-400 mt-1">Create your social media visual</p>
                </div>
                <button @click="close()" class="text-gray-400 hover:text-white transition">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-hidden flex">

                <!-- Left Sidebar: Controls -->
                <div class="w-80 bg-gray-800 p-6 overflow-y-auto border-r border-gray-700">
                    <div class="space-y-6">

                        <!-- Ratio Selector -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Aspect Ratio</label>
                            <div class="grid grid-cols-5 gap-2">
                                <button @click="changeRatio('1:1')" type="button"
                                        :class="currentRatio === '1:1' ? 'bg-blue-600 ring-2 ring-blue-400' : 'bg-gray-700 hover:bg-gray-600'"
                                        class="px-2 py-3 rounded text-xs transition">
                                    1:1
                                </button>
                                <button @click="changeRatio('4:5')" type="button"
                                        :class="currentRatio === '4:5' ? 'bg-blue-600 ring-2 ring-blue-400' : 'bg-gray-700 hover:bg-gray-600'"
                                        class="px-2 py-3 rounded text-xs transition">
                                    4:5
                                </button>
                                <button @click="changeRatio('16:9')" type="button"
                                        :class="currentRatio === '16:9' ? 'bg-blue-600 ring-2 ring-blue-400' : 'bg-gray-700 hover:bg-gray-600'"
                                        class="px-2 py-3 rounded text-xs transition">
                                    16:9
                                </button>
                                <button @click="changeRatio('9:16')" type="button"
                                        :class="currentRatio === '9:16' ? 'bg-blue-600 ring-2 ring-blue-400' : 'bg-gray-700 hover:bg-gray-600'"
                                        class="px-2 py-3 rounded text-xs transition">
                                    9:16
                                </button>
                            </div>
                        </div>

                        <!-- Template Selector -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">Template</label>
                            <select @change="templateChanged = true; loadTemplate($event.target.value)"
                                    x-model="currentTemplate"
                                    class="w-full bg-gray-700 border-gray-600 rounded-lg px-3 py-2 text-sm">
                                <option value="">Select template...</option>
                                <template x-for="template in templates" :key="template.id">
                                    <option :value="template.id" x-text="template.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Branding Preset Selector -->
                        <div x-show="currentTemplate">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold">Branding Preset</label>
                                <a href="{{ route('brandings.index') }}" target="_blank" class="text-xs text-blue-400 hover:text-blue-300">
                                    Manage
                                </a>
                            </div>
                            <select @change="applyBranding($event.target.value)"
                                    x-model="selectedBranding"
                                    class="w-full bg-gray-700 border-gray-600 rounded-lg px-3 py-2 text-sm">
                                <option value="">-- No branding --</option>
                                <template x-for="branding in brandings" :key="branding.id">
                                    <option :value="branding.id" x-text="branding.preset_name"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 mt-1" x-show="selectedBranding">
                                Auto-apply logo and colors
                            </p>
                        </div>

                        <!-- Text Editing -->
                        <div x-show="currentTemplate">
                            <label class="block text-sm font-semibold mb-2">Headline</label>
                            <textarea @input="updateHeadline($event.target.value)"
                                      x-model="editableHeadline"
                                      rows="3"
                                      class="w-full bg-gray-700 border-gray-600 rounded-lg px-3 py-2 text-sm resize-none"
                                      placeholder="Enter headline..."></textarea>
                        </div>

                        <div x-show="currentTemplate">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold">Subheadline</label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="showSubheadline" @change="toggleSubheadline()" class="mr-2">
                                    <span class="text-xs text-gray-400">Show</span>
                                </label>
                            </div>
                            <textarea x-show="showSubheadline"
                                      @input="updateSubheadline($event.target.value)"
                                      x-model="editableSubheadline"
                                      rows="2"
                                      class="w-full bg-gray-700 border-gray-600 rounded-lg px-3 py-2 text-sm resize-none"
                                      placeholder="Enter subheadline..."></textarea>
                        </div>

                        <!-- Image Replacement -->
                        <div x-show="currentTemplate">
                            <label class="block text-sm font-semibold mb-2">Featured Image</label>
                            <div class="border-2 border-dashed border-gray-600 rounded-lg p-3 text-center cursor-pointer hover:border-blue-500 transition"
                                 @click="openMediaLibraryForImage()">
                                <template x-if="featuredImageUrl">
                                    <img :src="featuredImageUrl" alt="Featured" class="w-full h-32 object-cover rounded mb-2">
                                </template>
                                <template x-if="!featuredImageUrl">
                                    <div>
                                        <x-heroicon-o-photo class="w-8 h-8 mx-auto mb-2 text-gray-500" />
                                        <p class="text-xs text-gray-400">Pilih dari Media Library</p>
                                    </div>
                                </template>
                            </div>
                            <button type="button"
                                    x-show="featuredImageUrl"
                                    @click.stop="removeFeaturedImage()"
                                    class="mt-2 w-full px-3 py-2 text-xs text-gray-200 bg-gray-800 hover:bg-gray-700 rounded-lg transition">
                                Remove Image
                            </button>
                        </div>

                        <!-- Customization Panel -->
                        <div x-show="currentTemplate">
                            <label class="block text-sm font-semibold mb-3">Customization</label>

                            <!-- Overlay Color -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-400 mb-1">Overlay Color</label>
                                <input type="color"
                                       x-model="customSettings.overlayColor"
                                       @input="updateCustomSetting('overlayColor', $event.target.value)"
                                       class="w-full h-10 bg-gray-700 border-gray-600 rounded cursor-pointer">
                            </div>

                            <!-- Overlay Opacity -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-400 mb-1">Overlay Opacity</label>
                                <input type="range"
                                       x-model="customSettings.overlayOpacity"
                                       @input="updateCustomSetting('overlayOpacity', parseFloat($event.target.value))"
                                       min="0" max="1" step="0.05"
                                       class="w-full">
                                <span class="text-xs text-gray-500" x-text="`${Math.round(customSettings.overlayOpacity * 100)}%`"></span>
                            </div>

                            <!-- Headline Settings -->
                            <div class="mb-4 p-3 bg-gray-800 rounded">
                                <label class="block text-xs font-semibold mb-2">Headline Style</label>

                                <div class="mb-3">
                                    <label class="block text-xs text-gray-400 mb-1">Font Size</label>
                                    <input type="number"
                                           x-model="customSettings.headlineFontSize"
                                           @input="updateCustomSetting('headlineFontSize', parseInt($event.target.value))"
                                           min="20" max="120" step="2"
                                           class="w-full bg-gray-700 border-gray-600 rounded px-2 py-1 text-sm">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-xs text-gray-400 mb-1">Font Color</label>
                                    <input type="color"
                                           x-model="customSettings.headlineFontColor"
                                           @input="updateCustomSetting('headlineFontColor', $event.target.value)"
                                           class="w-full h-10 bg-gray-700 border-gray-600 rounded cursor-pointer">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Font Weight</label>
                                    <select x-model="customSettings.headlineFontWeight"
                                            @change="updateCustomSetting('headlineFontWeight', $event.target.value)"
                                            class="w-full bg-gray-700 border-gray-600 rounded px-2 py-1 text-sm">
                                        <option value="400">Normal (400)</option>
                                        <option value="700">Bold (700)</option>
                                        <option value="900">Black (900)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Subheadline Settings -->
                            <div x-show="showSubheadline" class="mb-4 p-3 bg-gray-800 rounded">
                                <label class="block text-xs font-semibold mb-2">Subheadline Style</label>

                                <div class="mb-3">
                                    <label class="block text-xs text-gray-400 mb-1">Font Size</label>
                                    <input type="number"
                                           x-model="customSettings.subheadlineFontSize"
                                           @input="updateCustomSetting('subheadlineFontSize', parseInt($event.target.value))"
                                           min="12" max="60" step="2"
                                           class="w-full bg-gray-700 border-gray-600 rounded px-2 py-1 text-sm">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-xs text-gray-400 mb-1">Font Color</label>
                                    <input type="color"
                                           x-model="customSettings.subheadlineFontColor"
                                           @input="updateCustomSetting('subheadlineFontColor', $event.target.value)"
                                           class="w-full h-10 bg-gray-700 border-gray-600 rounded cursor-pointer">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Font Weight</label>
                                    <select x-model="customSettings.subheadlineFontWeight"
                                            @change="updateCustomSetting('subheadlineFontWeight', $event.target.value)"
                                            class="w-full bg-gray-700 border-gray-600 rounded px-2 py-1 text-sm">
                                        <option value="400">Normal (400)</option>
                                        <option value="700">Bold (700)</option>
                                        <option value="900">Black (900)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Font Family -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-400 mb-1">Font Family</label>
                                <select x-model="customSettings.headlineFont"
                                        @change="updateCustomSetting('headlineFont', $event.target.value)"
                                        class="w-full bg-gray-700 border-gray-600 rounded px-2 py-1 text-sm">
                                    <option value="Inter">Inter</option>
                                    <option value="Poppins">Poppins</option>
                                    <option value="Roboto">Roboto</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Open Sans">Open Sans</option>
                                </select>
                            </div>

                            <!-- Text Alignment -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-400 mb-1">Text Alignment</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button @click="updateCustomSetting('textAlign', 'left')" type="button"
                                            :class="customSettings.textAlign === 'left' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'"
                                            class="px-2 py-1 rounded text-xs transition">
                                        Left
                                    </button>
                                    <button @click="updateCustomSetting('textAlign', 'center')" type="button"
                                            :class="customSettings.textAlign === 'center' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'"
                                            class="px-2 py-1 rounded text-xs transition">
                                        Center
                                    </button>
                                    <button @click="updateCustomSetting('textAlign', 'right')" type="button"
                                            :class="customSettings.textAlign === 'right' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'"
                                            class="px-2 py-1 rounded text-xs transition">
                                        Right
                                    </button>
                                </div>
                            </div>

                            <!-- Logo Upload -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-400 mb-1">Brand Logo</label>
                                <div class="border border-gray-600 rounded p-2 cursor-pointer hover:border-blue-500 transition"
                                     @click="openMediaLibraryForLogo()">
                                    <template x-if="brandLogo">
                                        <img :src="brandLogo" alt="Logo" class="h-12 object-contain mx-auto">
                                    </template>
                                    <template x-if="!brandLogo">
                                        <p class="text-xs text-gray-500 text-center">Click to upload logo</p>
                                    </template>
                                </div>
                                <button type="button"
                                        x-show="brandLogo"
                                        @click.stop="removeBrandLogo()"
                                        class="mt-1 w-full px-2 py-1 text-xs text-gray-400 hover:text-white transition">
                                    Remove Logo
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Center: Canvas Preview -->
                <div class="flex-1 bg-gray-950 flex items-center justify-center overflow-auto">
                    <div id="visualCanvasContainer" class="relative flex items-center justify-center p-4">
                        <!-- Canvas will be rendered here -->
                        <canvas id="visualCanvas" class="shadow-2xl"></canvas>

                        <!-- Loading overlay -->
                        <div x-show="isRendering" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-2"></div>
                                <p class="text-sm text-gray-300">Rendering...</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer: Actions -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-700">
                <button @click="close()" type="button"
                        class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg transition">
                    Cancel
                </button>
                <div class="flex gap-3">
                    <button @click="downloadPNG()" type="button"
                            :disabled="!currentTemplate"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Download PNG
                    </button>
                    <button @click="saveAndUpload()" type="button"
                            :disabled="!currentTemplate || isUploading"
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-lg transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isUploading">Save Visual</span>
                        <span x-show="isUploading">Uploading...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@verbatim
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('visualEditorData', () => ({
        isOpen: false,
        assetId: null,
        templates: [],
        brandings: [],
        selectedBranding: null,
        currentTemplate: null,
        currentRatio: '4:5',
        currentTheme: 'modern',
        editableHeadline: '',
        editableSubheadline: '',
        showSubheadline: true,
        featuredImageUrl: null,
        brandLogo: null,
        canvas: null,
        fabricCanvas: null,
        featuredImageObj: null,
        overlayObj: null,
        headlineObj: null,
        subheadlineObj: null,
        canvasEventsBound: false,
        isRendering: false,
        isUploading: false,
        templateData: null,
        templateChanged: false, // Track if user manually changed template
        customSettings: {
            overlayColor: '#dc2626',
            overlayOpacity: 0.95,
            headlineFont: 'Inter',
            headlineFontSize: 56,
            headlineFontColor: '#ffffff',
            headlineFontWeight: '700',
            textAlign: 'left',
            subheadlineFontSize: 24,
            subheadlineFontColor: '#ffffff',
            subheadlineFontWeight: '400',
        },

        async open(assetId) {
            this.assetId = assetId;
            this.isOpen = true;
            await this.loadTemplates();
        },

        close() {
            this.isOpen = false;
            this.reset();
        },

        reset() {
            this.assetId = null;
            this.templates = [];
            this.currentTemplate = null;
            this.editableHeadline = '';
            this.editableSubheadline = '';
            this.featuredImageUrl = null;
            this.featuredImageObj = null;
            this.overlayObj = null;
            this.headlineObj = null;
            this.subheadlineObj = null;
            if (this.fabricCanvas) {
                this.fabricCanvas.dispose();
                this.fabricCanvas = null;
            }
        },

        async loadTemplates() {
            try {
                const response = await fetch(`/social-assets/${this.assetId}/templates`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    this.templates = data.templates;
                    if (data.size) {
                        this.currentRatio = data.size;
                    }
                    // Auto-load previously used template if available
                    if (data.selected_template) {
                        this.currentTemplate = data.selected_template;
                        await this.loadTemplate(this.currentTemplate);
                    } else if (this.templates.length) {
                        this.currentTemplate = this.templates[0].id;
                        await this.loadTemplate(this.currentTemplate);
                    }
                }
            } catch (error) {
                console.error('Failed to load templates:', error);
            }
        },

        async changeRatio(ratio) {
            this.currentRatio = ratio;
            if (this.currentTemplate) {
                await this.loadTemplate(this.currentTemplate);
            }
        },

        async loadTemplate(templateId) {
            if (!templateId) return;

            this.isRendering = true;
            this.currentTemplate = templateId;

            try {
                // Get template data from server
                const response = await fetch(`/social-assets/${this.assetId}/template-data`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        template_id: templateId,
                        size: this.currentRatio,
                        brandLogo: this.brandLogo,
                        branding_id: this.selectedBranding // CRITICAL: Send branding_id to backend
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.templateData = data.templateData;

                    // Load branding if available (don't override if already selected)
                    if (data.branding) {
                        // Only update if not already selected (e.g., page load, not template change)
                        if (!this.selectedBranding) {
                            this.selectedBranding = data.branding.id;
                        }

                        if (data.branding.logo && !this.brandLogo) {
                            this.brandLogo = data.branding.logo.full_url;
                        }
                    }

                    // RESET custom settings to template defaults (not merge)
                    this.customSettings = {
                        overlayColor: this.templateData.settings.overlayColor || '#dc2626',
                        overlayOpacity: this.templateData.settings.overlayOpacity !== undefined ? this.templateData.settings.overlayOpacity : 0.95,
                        headlineFont: this.templateData.settings.headlineFont || 'Inter',
                        headlineFontSize: this.templateData.settings.headlineFontSizeBase || 56,
                        headlineFontColor: this.templateData.settings.headlineColor || '#ffffff',
                        headlineFontWeight: this.templateData.settings.headlineFontWeight || '700',
                        subheadlineFontSize: this.templateData.settings.subheadlineFontSize || 24,
                        subheadlineFontColor: this.templateData.settings.subheadlineColor || '#ffffff',
                        subheadlineFontWeight: this.templateData.settings.subheadlineFontWeight || '400',
                        textAlign: this.templateData.settings.textAlign || 'left',
                        websiteUrl: this.templateData.settings.websiteUrl || 'webane.com', // Footer domain
                    };

                    // Populate text/image state
                    this.editableHeadline = this.templateData.settings.headline || '';
                    this.editableSubheadline = this.templateData.settings.subheadline || '';
                    this.featuredImageUrl = this.templateData.settings.featuredImage || null;

                    // Decide whether to restore saved state or render fresh template
                    // - On initial load (templateChanged=false): restore saved canvas_state if available
                    // - On template change (templateChanged=true): always render fresh, ignore canvas_state
                    if (!this.templateChanged && data.canvas_state) {
                        await this.restoreCanvasState(data.canvas_state);
                    } else {
                        await this.renderCanvas();
                    }
                }
            } catch (error) {
                console.error('Failed to load template data:', error);
            } finally {
                this.isRendering = false;
            }
        },

        async updateCustomSetting(key, value) {
            console.log('updateCustomSetting:', key, value);
            this.customSettings[key] = value;

            // Update existing objects instead of full re-render for performance
            if (this.fabricCanvas) {
                const objects = this.fabricCanvas.getObjects();
                console.log('Objects on canvas:', objects.map(o => ({type: o.type, objectType: o.objectType})));

                objects.forEach(obj => {
                    // Only update overlay rect (not all rects)
                    if (key === 'overlayColor' && (obj.objectType === 'overlay' || obj.type === 'rect')) {
                        // If overlay has gradient, recreate gradient with new color
                        if (obj.gradientDef && obj.gradientDef.type === 'linear') {
                            const rectHeight = obj.height;
                            const newGradient = new fabric.Gradient({
                                type: 'linear',
                                coords: {
                                    x1: 0,
                                    y1: rectHeight * obj.gradientDef.coords.y1,
                                    x2: 0,
                                    y2: rectHeight * obj.gradientDef.coords.y2
                                },
                                colorStops: obj.gradientDef.colorStops.map(stop => ({
                                    offset: stop.offset,
                                    color: this.hexToRgba(value, stop.opacity)
                                }))
                            });
                            obj.set('fill', newGradient);
                        } else {
                            obj.set('fill', value);
                        }
                    } else if (key === 'overlayOpacity' && obj.objectType === 'overlay') {
                        // For gradient overlay, update colorStops opacity (multiply base opacity)
                        if (obj.gradientDef && obj.gradientDef.type === 'linear') {
                            const rectHeight = obj.height;
                            const newGradient = new fabric.Gradient({
                                type: 'linear',
                                coords: {
                                    x1: 0,
                                    y1: rectHeight * obj.gradientDef.coords.y1,
                                    x2: 0,
                                    y2: rectHeight * obj.gradientDef.coords.y2
                                },
                                colorStops: obj.gradientDef.colorStops.map(stop => ({
                                    offset: stop.offset,
                                    color: this.hexToRgba(this.customSettings.overlayColor, stop.opacity * value)
                                }))
                            });
                            obj.set('fill', newGradient);
                        } else {
                            obj.set('opacity', value);
                        }
                    }
                    // Update headline
                    else if (key === 'headlineFontSize' && obj.objectType === 'headline') {
                        obj.set('fontSize', value);
                        obj.initDimensions();
                        obj.setCoords();
                    } else if (key === 'headlineFontColor' && obj.objectType === 'headline') {
                        obj.set('fill', value);
                    } else if (key === 'headlineFontWeight' && obj.objectType === 'headline') {
                        const weight = value === 'normal' ? 400 : (value === 'bold' ? 700 : parseInt(value) || 400);
                        obj.set('fontWeight', weight);
                        obj.initDimensions();
                        obj.setCoords();
                    }
                    // Update subheadline
                    else if (key === 'subheadlineFontSize' && obj.objectType === 'subheadline') {
                        obj.set('fontSize', value);
                        obj.initDimensions();
                        obj.setCoords();
                    } else if (key === 'subheadlineFontColor' && obj.objectType === 'subheadline') {
                        obj.set('fill', value);
                    } else if (key === 'subheadlineFontWeight' && obj.objectType === 'subheadline') {
                        const weight = value === 'normal' ? 400 : (value === 'bold' ? 700 : parseInt(value) || 400);
                        obj.set('fontWeight', weight);
                        obj.initDimensions();
                        obj.setCoords();
                    }
                    // Update all text objects - font family change affects dimensions
                    else if (key === 'headlineFont' && obj.type === 'textbox') {
                        obj.set('fontFamily', value);
                        obj.initDimensions();
                        obj.setCoords();
                    } else if (key === 'textAlign' && obj.type === 'textbox') {
                        obj.set('textAlign', value);
                    }
                });

                this.fabricCanvas.renderAll();
            }
        },

        async applyBranding(brandingId) {
            // Update selected branding FIRST before reloading template
            this.selectedBranding = brandingId;

            // CRITICAL: Reload template with new branding data from backend
            // This ensures backgroundColor stays #f5f5f5, accentLineColor/footerBackgroundColor get new brand colors
            if (this.currentTemplate) {
                this.templateChanged = true; // Force fresh render, ignore canvas_state
                await this.loadTemplate(this.currentTemplate);
            }
        },

        async restoreCanvasState(canvasStateJSON) {
            const canvasEl = document.getElementById('visualCanvas');
            const container = document.getElementById('visualCanvasContainer');

            if (!this.fabricCanvas) {
                this.fabricCanvas = new fabric.Canvas('visualCanvas');
            }
            this.bindCanvasEvents();

            // Canvas native dimensions (template size)
            const { width, height } = this.templateData.dimensions;

            // Fit inside viewport (70% of window) while preserving ratio; scale down only
            const maxWidth = window.innerWidth * 0.7;
            const maxHeight = window.innerHeight * 0.7;
            const scale = Math.min(maxWidth / width, maxHeight / height, 1);
            const displayWidth = width * scale;
            const displayHeight = height * scale;

            // Scale canvas dimensions down and set zoom to match (identity viewport)
            this.fabricCanvas.setDimensions({ width: displayWidth, height: displayHeight });
            this.fabricCanvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            this.fabricCanvas.setZoom(scale);

            // Match element size; no CSS transform
            canvasEl.style.width = `${displayWidth}px`;
            canvasEl.style.height = `${displayHeight}px`;
            canvasEl.style.transform = 'none';
            canvasEl.style.transformOrigin = 'top left';
            this.fabricCanvas.calcOffset();
            this.syncOverlayWidthToCanvas();

            // Load canvas from saved state
            const canvasState = JSON.parse(canvasStateJSON);

            await new Promise((resolve) => {
                this.fabricCanvas.loadFromJSON(canvasState, async () => {
                    // Re-bind object references and fix image objects
                    const objects = this.fabricCanvas.getObjects();

                    // CRITICAL: Collect text objects first to bind headline/subheadline
                    const textObjects = objects.filter(obj => obj.type === 'textbox');

                    for (let i = 0; i < objects.length; i++) {
                        const obj = objects[i];

                        // CRITICAL: Re-assign objectType for all objects (fabric.js might not restore custom properties correctly)
                        // This ensures updateCustomSetting() can match objects after restore

                        // Featured image: can be Group (Instagram-style crop) or Image (simple)
                        if (obj.objectType === 'featuredImage' || (obj.type === 'image' && obj.clipPath)) {
                            obj.objectType = 'featuredImage'; // Re-assign to ensure it's set

                            // CRITICAL: Recreate clipPath with absolutePositioned
                            // fabric.js doesn't restore absolutePositioned correctly
                            if (obj.clipPath && obj.clipPath.left !== undefined) {
                                const clipRect = new fabric.Rect({
                                    left: obj.clipPath.left,
                                    top: obj.clipPath.top,
                                    width: obj.clipPath.width,
                                    height: obj.clipPath.height,
                                    absolutePositioned: true, // FORCE absolute positioning
                                    fill: 'transparent',
                                    stroke: 'transparent'
                                });
                                obj.clipPath = clipRect;
                            }

                            this.featuredImageObj = obj;
                        } else if (obj.type === 'image' && obj.objectType === 'logo') {
                            obj.objectType = 'logo'; // Re-assign logo objectType
                        } else if (obj.type === 'textbox') {
                            // Bind text objects (headline is first textbox, subheadline is second)
                            if (!this.headlineObj) {
                                this.headlineObj = obj;
                                obj.objectType = 'headline'; // Re-assign to ensure it's set
                                this.editableHeadline = obj.text;
                            } else if (!this.subheadlineObj) {
                                this.subheadlineObj = obj;
                                obj.objectType = 'subheadline'; // Re-assign to ensure it's set
                                this.editableSubheadline = obj.text;
                                this.showSubheadline = obj.visible !== false;
                            }
                        } else if (obj.type === 'rect' && (obj.objectType === 'overlay' || obj.gradientDef)) {
                            obj.objectType = 'overlay'; // Re-assign overlay objectType
                            this.overlayObj = obj;
                        }
                    }

                    // CRITICAL: Re-sort objects by zIndex after loadFromJSON
                    // fabric.js doesn't preserve z-index order when restoring from JSON
                    const sortedObjects = objects.slice().sort((a, b) => {
                        const aZ = a.zIndex !== undefined ? a.zIndex : 999;
                        const bZ = b.zIndex !== undefined ? b.zIndex : 999;
                        return aZ - bZ;
                    });

                    // Clear canvas and re-add in correct order
                    this.fabricCanvas.clear();
                    sortedObjects.forEach(obj => {
                        this.fabricCanvas.add(obj);
                    });

                    this.fabricCanvas.renderAll();
                    resolve();
                });
            });
        },

        async renderCanvas() {
            // Initialize Fabric.js canvas
            const canvasEl = document.getElementById('visualCanvas');
            const container = document.getElementById('visualCanvasContainer');
            if (!this.fabricCanvas) {
                this.fabricCanvas = new fabric.Canvas('visualCanvas');
            }
            this.bindCanvasEvents();

            // Native template dimensions
            const { width, height } = this.templateData.dimensions;

            // Fit inside viewport (70% of window) while preserving ratio; scale down only
            const maxWidth = window.innerWidth * 0.7;
            const maxHeight = window.innerHeight * 0.7;
            const scale = Math.min(maxWidth / width, maxHeight / height, 1);
            const displayWidth = width * scale;
            const displayHeight = height * scale;

            // Scale canvas dimensions down and set zoom to match (identity viewport)
            this.fabricCanvas.setDimensions({ width: displayWidth, height: displayHeight });
            this.fabricCanvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            this.fabricCanvas.setZoom(scale);

            canvasEl.style.width = `${displayWidth}px`;
            canvasEl.style.height = `${displayHeight}px`;
            canvasEl.style.transform = 'none';
            canvasEl.style.transformOrigin = 'top left';
            this.fabricCanvas.calcOffset();

            // Clear existing objects
            this.fabricCanvas.clear();
            this.featuredImageObj = null;
            this.overlayObj = null;
            this.headlineObj = null;
            this.subheadlineObj = null;

            // Render objects
            if (this.templateData.objects && this.templateData.objects.length > 0) {
                for (const objDef of this.templateData.objects) {
                    await this.renderObject(objDef);
                }
            }

            this.fabricCanvas.renderAll();
        },

        async renderObject(objDef) {
            if (objDef.type === 'image') {
                const isLogo = objDef.objectType === 'logo';
                const imageUrl = isLogo ? objDef.src : (this.featuredImageUrl || objDef.src);
                if (imageUrl) {
                    await new Promise((resolve, reject) => {
                        // Add timeout to detect if callback never fires
                        const timeout = setTimeout(() => {
                            console.error('ERROR: fabric.Image.fromURL callback timeout (10s) - callback never executed');
                            resolve(); // Don't block rendering
                        }, 10000);

                        fabric.Image.fromURL(imageUrl, (img, isError) => {
                            clearTimeout(timeout);

                            // Check for error flag (fabric.js passes true as 2nd param on error)
                            if (isError) {
                                console.error('ERROR: fabric.Image.fromURL failed with error flag:', isError);
                                resolve();
                                return;
                            }

                            // Check if image object is valid
                            if (!img || !img.width || img.width === 0) {
                                console.error('ERROR: fabric.Image.fromURL returned invalid image object:', {
                                    img: img,
                                    width: img ? img.width : 'null',
                                    height: img ? img.height : 'null'
                                });
                                resolve();
                                return;
                            }

                            // Different scaling for logo vs featured image
                            let targetWidth, targetHeight, scale;
                            const isLogo = objDef.objectType === 'logo';

                            if (isLogo) {
                                // Logo: use exact width from backend, auto-scale height
                                targetWidth = objDef.width;
                                scale = targetWidth / img.width;
                            } else {
                                // Featured image: use EXACT dimensions from backend (already calculated)
                                targetWidth = objDef.width;
                                targetHeight = objDef.height;
                                // COVER mode: scale proportionally to fill frame (keep aspect ratio)
                                scale = Math.max(targetWidth / img.width, targetHeight / img.height);
                            }

                            if (isLogo) {
                                // Logo: scale proportionally, simple positioning
                                img.set({
                                    left: objDef.left,
                                    top: objDef.top,
                                    originX: 'left',
                                    originY: 'top',
                                    scaleX: scale,
                                    scaleY: scale,
                                    selectable: true,
                                    evented: true,
                                    hasControls: true,
                                    hasBorders: true,
                                    objectType: 'logo',
                                    zIndex: objDef.zIndex || 3 // Save zIndex for restore
                                });

                                this.fabricCanvas.add(img);
                            } else {
                                // INSTAGRAM-STYLE CROP: Image dengan clipPath ABSOLUTE
                                // Image bisa di-drag/scale, clipPath tetap fixed

                                // Scale image to cover frame (keep aspect ratio)
                                img.set({
                                    left: objDef.left + targetWidth / 2,
                                    top: objDef.top + targetHeight / 2,
                                    originX: 'center',
                                    originY: 'center',
                                    scaleX: scale,
                                    scaleY: scale,
                                    selectable: true,
                                    evented: true,
                                    hasControls: true,
                                    hasBorders: true,
                                    lockScalingFlip: true,
                                    lockRotation: true, // Lock rotation untuk crop editor
                                    objectType: 'featuredImage',
                                    zIndex: objDef.zIndex || 2, // Save zIndex for restore
                                    cornerStyle: 'circle',
                                    cornerSize: 16,
                                    cornerColor: '#3b82f6',
                                    borderColor: '#3b82f6',
                                    transparentCorners: false
                                });

                                // ClipPath dengan absolutePositioned: TRUE
                                // Ini bikin clipPath tidak ikut transform image
                                const clipRect = new fabric.Rect({
                                    left: objDef.left,
                                    top: objDef.top,
                                    width: targetWidth,
                                    height: targetHeight,
                                    absolutePositioned: true, // CRITICAL: clipPath fixed, not follow image transform
                                    fill: 'transparent',
                                    stroke: 'transparent'
                                });

                                img.clipPath = clipRect;

                                this.featuredImageObj = img;
                                this.fabricCanvas.add(img);
                            }

                            resolve();
                        }, { crossOrigin: 'anonymous' });
                    });
                }
            } else if (objDef.type === 'image' && (!objDef.objectType || objDef.objectType === 'logo')) {
                // Handle logo from template (backend returns type: 'image' for logo rule)
                if (objDef.src) {
                    await new Promise((resolve) => {
                        fabric.Image.fromURL(objDef.src, (img, isError) => {
                            // Error handling
                            if (isError) {
                                console.error('ERROR: Logo image failed to load:', objDef.src);
                                resolve();
                                return;
                            }

                            if (!img || !img.width || img.width === 0) {
                                console.error('ERROR: Logo image invalid:', {
                                    img: img,
                                    width: img ? img.width : 'null'
                                });
                                resolve();
                                return;
                            }

                            // Scale to fit maxWidth from template (preserve aspect ratio)
                            const scaleToWidth = objDef.width ? (objDef.width / img.width) : 1;

                            img.set({
                                left: objDef.left,
                                top: objDef.top,
                                originX: 'left',
                                originY: 'top',
                                scaleX: scaleToWidth,
                                scaleY: scaleToWidth, // Same scale for both axes (preserve aspect ratio)
                                selectable: true,
                                evented: true,
                                hasControls: true,
                                hasBorders: true,
                                lockScalingFlip: true,
                                lockRotation: false,
                                objectType: 'logo',
                                cornerStyle: 'circle',
                                cornerSize: 12,
                                cornerColor: '#3b82f6',
                                borderColor: '#3b82f6',
                                transparentCorners: false
                            });

                            this.fabricCanvas.add(img);

                            // Ensure logo is on top (zIndex 4 in template)
                            this.fabricCanvas.bringToFront(img);

                            resolve();
                        }, { crossOrigin: 'anonymous' });
                    });
                }
            } else if (objDef.type === 'rect') {
                // CRITICAL: Only use overlayColor for actual overlay rects (news-overlay template)
                // For other rects (background, accent line, footer), use color from backend (objDef.fill)
                const isOverlay = objDef.objectType === 'overlay';

                let fillValue = isOverlay ? (this.customSettings.overlayColor || objDef.fill) : objDef.fill;

                // Handle gradient if defined (news-gradient template)
                if (objDef.gradient && objDef.gradient.type === 'linear') {
                    const gradientColor = isOverlay ? (this.customSettings.overlayColor || objDef.fill) : objDef.fill;
                    const rectHeight = objDef.height;

                    // Convert normalized coords (0-1) to absolute pixels
                    // For vertical gradient bottom-to-top: y1=1 (bottom) → y2=0 (top)
                    fillValue = new fabric.Gradient({
                        type: 'linear',
                        coords: {
                            x1: 0,
                            y1: rectHeight * objDef.gradient.coords.y1,  // 1 * height = bottom
                            x2: 0,
                            y2: rectHeight * objDef.gradient.coords.y2   // 0 * height = top
                        },
                        colorStops: objDef.gradient.colorStops.map(stop => ({
                            offset: stop.offset,
                            color: this.hexToRgba(gradientColor, stop.opacity)
                        }))
                    });
                }

                const rect = new fabric.Rect({
                    left: objDef.left !== undefined ? objDef.left : 0,
                    top: objDef.top,
                    width: objDef.width !== undefined ? objDef.width : (this.fabricCanvas ? (this.fabricCanvas.getWidth() / (this.fabricCanvas.getZoom() || 1)) : 1080),
                    height: objDef.height,
                    fill: fillValue,
                    opacity: isOverlay && !objDef.gradient ? (this.customSettings.overlayOpacity !== undefined ? this.customSettings.overlayOpacity : (objDef.opacity !== undefined ? objDef.opacity : 1)) : 1,
                    rx: objDef.rx || 0,  // Border radius X
                    ry: objDef.ry || 0,  // Border radius Y
                    selectable: false,
                    evented: false,
                    objectType: objDef.objectType || 'overlay',
                    zIndex: objDef.zIndex !== undefined ? objDef.zIndex : 0, // CRITICAL: Check !== undefined
                    gradientDef: objDef.gradient || null, // Store gradient definition for updates
                    initialGradientHeight: objDef.gradient ? objDef.height : null // Store initial height for gradient fade reference
                });

                if (isOverlay) {
                    this.overlayObj = rect;
                }
                this.fabricCanvas.add(rect);
                if (isOverlay) {
                    this.syncOverlayWidthToCanvas();
                }
            } else if (objDef.type === 'text') {
                // CRITICAL: Use objectType from backend to identify headline/subheadline
                // Don't rely on text content detection (text already replaced by backend)
                const isHeadline = objDef.objectType === 'headline';
                const isSubheadline = objDef.objectType === 'subheadline';

                // Text already replaced by backend (TemplateLayoutEngine)
                let text = objDef.text || '';

                // Convert CSS keyword to number for fontWeight
                const convertFontWeight = (weight) => {
                    if (weight === 'normal') return 400;
                    if (weight === 'bold') return 700;
                    return parseInt(weight) || 400;
                };

                const fontWeightValue = isHeadline ? convertFontWeight(this.customSettings.headlineFontWeight) : (isSubheadline ? convertFontWeight(this.customSettings.subheadlineFontWeight) : convertFontWeight(objDef.fontWeight));

                console.log('Creating textbox:', {
                    type: isHeadline ? 'headline' : (isSubheadline ? 'subheadline' : 'text'),
                    fontWeight: fontWeightValue,
                    fontWeightRaw: isHeadline ? this.customSettings.headlineFontWeight : (isSubheadline ? this.customSettings.subheadlineFontWeight : objDef.fontWeight)
                });

                const textObj = new fabric.Textbox(text, {
                    left: objDef.left,
                    top: objDef.top,
                    width: objDef.width,
                    fontSize: isHeadline ? this.customSettings.headlineFontSize : (isSubheadline ? this.customSettings.subheadlineFontSize : objDef.fontSize),
                    fontWeight: fontWeightValue,
                    fill: isHeadline ? this.customSettings.headlineFontColor : (isSubheadline ? this.customSettings.subheadlineFontColor : objDef.fill),
                    fontFamily: this.customSettings.headlineFont || objDef.fontFamily,
                    textAlign: this.customSettings.textAlign || objDef.textAlign,
                    lineHeight: objDef.lineHeight,
                    selectable: true,
                    evented: true,
                    objectType: isHeadline ? 'headline' : (isSubheadline ? 'subheadline' : 'text'),
                    zIndex: objDef.zIndex !== undefined ? objDef.zIndex : 4 // CRITICAL: Check !== undefined
                });

                // Store references for update functions
                if (isHeadline) {
                    this.headlineObj = textObj;
                } else if (isSubheadline) {
                    this.subheadlineObj = textObj;
                }

                this.fabricCanvas.add(textObj);

                // REMOVED: bringToFront - trust backend z-index sorting instead
            }
        },

        openMediaLibraryForLogo() {
            window.dispatchEvent(new CustomEvent('open-media-library', {
                detail: {
                    callback: (media) => {
                        this.brandLogo = media.full_url;
                        this.renderCanvas(); // Re-render with logo
                    }
                }
            }));
        },

        removeBrandLogo() {
            this.brandLogo = null;
            this.renderCanvas();
        },

        updateHeadline(value) {
            this.editableHeadline = value;
            if (this.fabricCanvas) {
                if (this.headlineObj) {
                    this.headlineObj.set('text', value);
                    this.fabricCanvas.renderAll();
                } else {
                    console.warn('[UPDATE] headlineObj not bound yet');
                }
            }
        },

        updateSubheadline(value) {
            this.editableSubheadline = value;
            if (this.fabricCanvas) {
                if (this.subheadlineObj) {
                    this.subheadlineObj.set('text', value);
                    this.fabricCanvas.renderAll();
                } else {
                    console.warn('[UPDATE] subheadlineObj not bound yet');
                }
            }
        },

        toggleSubheadline() {
            if (this.fabricCanvas && this.subheadlineObj) {
                this.subheadlineObj.set('visible', this.showSubheadline);
                this.fabricCanvas.renderAll();
            }
        },

        bindCanvasEvents() {
            if (!this.fabricCanvas || this.canvasEventsBound) return;
            this.canvasEventsBound = true;

            this.fabricCanvas.on('mouse:down', (opt) => {
                if (!opt.target) {
                    this.fabricCanvas.discardActiveObject();
                    this.fabricCanvas.renderAll();
                }
                // CRITICAL: Maintain proper z-order after any interaction
                // Order: image (bottom) → overlay → text (top)
                if (this.featuredImageObj) {
                    this.fabricCanvas.sendToBack(this.featuredImageObj);
                }
                if (this.overlayObj) {
                    this.fabricCanvas.bringToFront(this.overlayObj);
                }
                // Text MUST be above overlay
                if (this.headlineObj) {
                    this.fabricCanvas.bringToFront(this.headlineObj);
                }
                if (this.subheadlineObj) {
                    this.fabricCanvas.bringToFront(this.subheadlineObj);
                }
            });
        },

        syncOverlayWidthToCanvas() {
            if (this.overlayObj && this.fabricCanvas) {
                const zoom = this.fabricCanvas.getZoom() || 1;
                const logicalWidth = this.fabricCanvas.getWidth() / zoom;
                this.overlayObj.set({ left: 0, width: logicalWidth });
                this.overlayObj.setCoords();
                this.fabricCanvas.renderAll();
            }
        },

        hexToRgba(hex, alpha = 1) {
            // Convert hex to RGB
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        },

        openMediaLibraryForImage() {
            window.dispatchEvent(new CustomEvent('open-media-library', {
                detail: {
                    callback: (media) => {
                        this.featuredImageUrl = media.full_url;
                        this.replaceImageWithUrl(media.full_url);
                    }
                }
            }));
        },

        replaceImageWithUrl(url) {
            if (!url || !this.fabricCanvas) return;
            this.featuredImageUrl = url;

            // CRITICAL: Re-render entire canvas to apply z-index correctly
            // Don't manually insert image, let renderCanvas() handle layering
            this.renderCanvas();
        },

        removeFeaturedImage() {
            this.featuredImageUrl = null;
            if (this.featuredImageObj) {
                this.fabricCanvas.remove(this.featuredImageObj);
                this.featuredImageObj = null;
                this.fabricCanvas.renderAll();
            }
        },

        changeTheme(theme) {
            this.currentTheme = theme;
            // Apply theme colors (simplified)
            const colors = {
                modern: '#2563eb',
                bold: '#dc2626',
                minimal: '#ffffff',
            };
            this.fabricCanvas.setBackgroundColor(colors[theme], this.fabricCanvas.renderAll.bind(this.fabricCanvas));
        },

        downloadPNG() {
            if (!this.fabricCanvas) return;

            const dataURL = this.fabricCanvas.toDataURL({
                format: 'png',
                quality: 1,
                multiplier: 1
            });

            const link = document.createElement('a');
            link.download = `social-visual-${this.assetId}.png`;
            link.href = dataURL;
            link.click();
        },

        async saveAndUpload() {
            if (!this.fabricCanvas) return;

            this.isUploading = true;

            try {
                // CRITICAL: Fix featured image src before saving
                if (this.featuredImageObj && this.featuredImageUrl) {
                    // Store original URL in custom property
                    this.featuredImageObj._originalSrc = this.featuredImageUrl;
                }

                // Serialize canvas state (save object positions, scales, etc)
                const canvasState = JSON.stringify(
                    this.fabricCanvas.toJSON([
                        'selectable',
                        'evented',
                        'objectType',
                        'originX',
                        'originY',
                        'scaleX',
                        'scaleY',
                        'lockMovementX',
                        'lockMovementY',
                        'lockScalingX',
                        'lockScalingY',
                        'lockRotation',
                        'lockScalingFlip',
                        'lockUniScaling',
                        'hasControls',
                        'hasBorders',
                        'hasRotatingPoint',
                        'cornerStyle',
                        'cornerSize',
                        'cornerColor',
                        'borderColor',
                        'transparentCorners',
                        // Text-specific properties
                        'text',
                        'fontFamily',
                        'fontSize',
                        'fontWeight',
                        'fill',
                        'textAlign',
                        'lineHeight',
                        'width',
                        'visible',
                        // CRITICAL: ClipPath properties for Instagram-style crop
                        'clipPath',
                        'absolutePositioned',
                        // Z-index for correct layering on restore
                        'zIndex'
                    ])
                );

                // Convert canvas to blob
                const dataURL = this.fabricCanvas.toDataURL({ format: 'png', quality: 1 });
                const blob = await fetch(dataURL).then(res => res.blob());

                // Upload to server
                const formData = new FormData();
                formData.append('image', blob, `visual-${this.assetId}.png`);
                formData.append('template_id', this.currentTemplate);
                // CRITICAL: Save canvas_state to preserve user edits (text position, image crop, etc)
                formData.append('canvas_state', canvasState);
                formData.append('headline', this.editableHeadline);
                formData.append('subheadline', this.editableSubheadline);
                formData.append('featured_image_url', this.featuredImageUrl || '');
                formData.append('custom_settings', JSON.stringify(this.customSettings));
                formData.append('ratio', this.currentRatio);
                if (this.selectedBranding) {
                    formData.append('branding_id', this.selectedBranding);
                }

                const response = await fetch(`/social-assets/${this.assetId}/upload-visual`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    // Reset template changed flag after successful save
                    // This allows future loads to use the new saved canvas_state
                    this.templateChanged = false;

                    // Notify parent listeners to refresh thumbnail
                    window.dispatchEvent(new CustomEvent('visual-updated', {
                        detail: {
                            assetId: this.assetId,
                            visualUrl: data.visual_url
                        }
                    }));
                    this.close();
                }
            } catch (error) {
                console.error('Upload failed:', error);
            } finally {
                this.isUploading = false;
            }
        }
    }));
});

// IMPORTANT: Define global function IMMEDIATELY, not inside DOMContentLoaded
window.openVisualEditor = function(assetId) {
    const component = document.querySelector('[x-data*="visualEditorData"]');
    if (component && typeof Alpine !== 'undefined') {
        Alpine.evaluate(component, '$data.open(' + assetId + ')');
    }
};
</script>
@endverbatim

<style>
[x-cloak] { display: none !important; }
</style>
