<!-- Media Library Modal Component -->
@php
    $providers = \App\Models\ApiCredential::where('category', 'image_generation')
        ->where('is_active', true)
        ->get(['provider', 'display_name'])
        ->map(function ($credential) {
            return [
                'value' => $credential->provider,
                'label' => $credential->display_name ?? ucfirst($credential->provider),
            ];
        });
    $languages = \App\Helpers\CountryHelper::getLanguages();
@endphp

<div x-data="mediaLibraryData(@js($languages))" x-show="isOpen" x-cloak @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-hidden" style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg w-full max-w-7xl max-h-[90vh] flex flex-col shadow-2xl"
             @click.away="close()">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('media-library.title') }}</h2>
                <button @click="close()" class="text-gray-400 hover:text-gray-600 transition">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'library'"
                            :class="activeTab === 'library' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-3 border-b-2 font-medium text-sm transition">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-photo class="w-5 h-5" />
                            {{ __('media-library.tabs.library') }}
                        </div>
                    </button>
                    <button @click="activeTab = 'upload'"
                            :class="activeTab === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-3 border-b-2 font-medium text-sm transition">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                            {{ __('media-library.tabs.upload') }}
                        </div>
                    </button>
                    <button @click="activeTab = 'generate'"
                            :class="activeTab === 'generate' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-3 border-b-2 font-medium text-sm transition">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-sparkles class="w-5 h-5" />
                            {{ __('media-library.tabs.generate') }}
                        </div>
                    </button>
                    <button @click="activeTab = 'unsplash'"
                            :class="activeTab === 'unsplash' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-3 border-b-2 font-medium text-sm transition">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M10 9V0L0 9h10zm12 5h10L22 0v14zM0 18h10v14L0 18zm22 0v14l10-14H22z"/>
                            </svg>
                            Unsplash
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-hidden flex">

                <!-- Left Panel: Grid View -->
                <div class="flex-1 overflow-y-auto p-6 border-r border-gray-200">

                    <!-- Media Library Tab -->
                    <div x-show="activeTab === 'library'">
                        <!-- Search & Filter -->
                        <div class="mb-4 flex gap-3">
                            <input type="text"
                                   x-model="searchQuery"
                                   @input="searchMedia()"
                                   placeholder="{{ __('media-library.search_media') }}"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button @click="loadMedia()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <x-heroicon-o-arrow-path class="w-5 h-5 text-gray-600" />
                            </button>
                        </div>

                        <!-- Grid -->
                        <div class="grid grid-cols-4 gap-4" id="mediaGrid">
                            <template x-for="item in mediaItems" :key="item.id">
                                <div @click="selectMedia(item)"
                                     :class="selectedMedia?.id === item.id ? 'ring-4 ring-blue-500' : 'ring-2 ring-gray-200 hover:ring-blue-300'"
                                     class="relative rounded-lg overflow-hidden cursor-pointer transition group">
                                    <div class="relative bg-gray-100" style="aspect-ratio: 1/1;">
                                        <img :src="item.full_url"
                                             :alt="item.alt_text || item.file_name"
                                             class="absolute inset-0 w-full h-full object-cover">
                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-30 transition-opacity flex items-center justify-center"></div>
                                        <!-- Check Icon -->
                                        <div x-show="selectedMedia?.id === item.id" class="absolute inset-0 flex items-center justify-center">
                                            <x-heroicon-o-check-circle class="w-8 h-8 text-white drop-shadow-lg" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Loading State -->
                        <div x-show="isLoading" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!isLoading && mediaItems.length === 0" class="text-center py-12">
                            <x-heroicon-o-photo class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <p class="text-gray-500">{{ __('media-library.empty') }}</p>
                        </div>
                    </div>

                    <!-- Upload Tab -->
                    <div x-show="activeTab === 'upload'">
                        <div @drop.prevent="handleDrop($event)"
                             @dragover.prevent
                             @dragenter.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                             class="border-2 border-dashed rounded-lg p-12 text-center transition cursor-pointer"
                             @click="$refs.fileInput.click()">

                            <input type="file"
                                   x-ref="fileInput"
                                   @change="handleFileSelect($event)"
                                   accept="image/*"
                                   class="hidden">

                            <x-heroicon-o-arrow-up-tray class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <p class="text-lg font-medium text-gray-700 mb-2">{{ __('media-library.upload.drop') }}</p>
                            <p class="text-sm text-gray-500">{{ __('media-library.upload.browse') }}</p>
                        </div>

                        <!-- Upload Progress -->
                        <div x-show="uploadProgress > 0 && uploadProgress < 100" class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ __('media-library.upload.uploading') }}</span>
                                <span class="text-sm text-gray-600" x-text="uploadProgress + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all" :style="`width: ${uploadProgress}%`"></div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Generate Tab -->
                    <div x-show="activeTab === 'generate'" class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('media-library.generate.prompt_label') }}</label>
                                <button type="button" @click="generatePromptFromContent()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-full transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    {{ __('media-library.generate.prompt_button') }}
                                </button>
                            </div>
                            <textarea x-model="generatePrompt"
                                      rows="4"
                                      placeholder="{{ __('media-library.generate.prompt_placeholder') }}"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('media-library.generate.language') }}</label>
                            <button type="button" @click="languageDropdownOpen = !languageDropdownOpen" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-left flex items-center justify-between">
                                <span x-text="selectedLanguage.flag + ' ' + selectedLanguage.name"></span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="languageDropdownOpen" @click.away="languageDropdownOpen = false" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-hidden">
                                <div class="p-2 border-b border-gray-200">
                                    <input type="text" x-model="languageSearch" placeholder="{{ __('media-library.generate.language_search') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="overflow-y-auto max-h-48">
                                    <template x-for="lang in filteredLanguageOptions" :key="lang.code">
                                        <button type="button" @click="generateLanguage = lang.code; languageDropdownOpen = false" class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-2" :class="{'bg-blue-50': generateLanguage === lang.code}">
                                            <span x-text="lang.flag"></span>
                                            <span x-text="lang.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('media-library.generate.provider') }}</label>
                            <select x-model="generateProvider" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('media-library.generate.provider_auto') }}</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider['value'] }}">{{ $provider['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('media-library.generate.style') }}</label>
                            <select x-model="generateStyle" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="Photo">Photo</option>
                                <option value="Cartoon">Cartoon</option>
                                <option value="Cubism">Cubism</option>
                                <option value="Expressionism">Expressionism</option>
                                <option value="Cyberpunk">Cyberpunk</option>
                                <option value="Fantasy">Fantasy</option>
                                <option value="Cinematic">Cinematic</option>
                                <option value="Abstract">Abstract</option>
                                <option value="Impressionism">Impressionism</option>
                                <option value="Surrealism">Surrealism</option>
                                <option value="Anime">Anime</option>
                                <option value="Comic">Comic</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('media-library.generate.size') }}</label>
                            <select x-model="generateSize" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="1080x1350 (4:5 Instagram Portrait)">1080x1350 (4:5 Instagram Portrait)</option>
                                <option value="960x768 (5:4)">960x768 (5:4)</option>
                                <option value="1024x640 (8:5)">1024x640 (8:5)</option>
                                <option value="1024x768 (4:3)">1024x768 (4:3)</option>
                                <option value="1152x768 (3:2)">1152x768 (3:2)</option>
                                <option value="768x1344 (9:16)">768x1344 (9:16)</option>
                                <option value="1024x1024 (1:1)">1024x1024 (1:1)</option>
                            </select>
                        </div>

                        <button @click="generateImage()"
                                :disabled="isGenerating || !generatePrompt"
                                class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                            <span x-show="!isGenerating">{{ __('media-library.generate.generate') }}</span>
                            <span x-show="isGenerating">{{ __('media-library.generate.generating') }}</span>
                        </button>
                    </div>

                    <!-- Unsplash Tab -->
                    <div x-show="activeTab === 'unsplash'">
                        <!-- Search Bar -->
                        <div class="mb-4">
                            <div class="flex gap-3">
                                <input type="text"
                                       x-model="unsplashQuery"
                                       @keydown.enter="searchUnsplash()"
                                       placeholder="Search free high-resolution photos..."
                                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button @click="searchUnsplash()"
                                        :disabled="isSearchingUnsplash || !unsplashQuery"
                                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                                    <span x-show="!isSearchingUnsplash">Search</span>
                                    <span x-show="isSearchingUnsplash">Searching...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Grid -->
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <template x-for="photo in unsplashPhotos" :key="photo.id">
                                <div @click="selectUnsplashPhoto(photo)"
                                     :class="selectedMedia?.id === photo.id ? 'ring-4 ring-blue-500' : 'ring-2 ring-gray-200 hover:ring-blue-300'"
                                     class="relative rounded-lg overflow-hidden cursor-pointer transition group">
                                    <div class="relative bg-gray-100" style="aspect-ratio: 1/1;">
                                        <img :src="photo.urls.small"
                                             :alt="photo.alt_description"
                                             class="absolute inset-0 w-full h-full object-cover">
                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-30 transition-opacity"></div>
                                        <!-- Check Icon -->
                                        <div x-show="selectedMedia?.id === photo.id" class="absolute inset-0 flex items-center justify-center">
                                            <x-heroicon-o-check-circle class="w-8 h-8 text-white drop-shadow-lg" />
                                        </div>
                                    </div>
                                    <!-- Attribution -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                                        <p class="text-xs text-white truncate" x-text="'Photo by ' + photo.user.name"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Load More Button -->
                        <div x-show="unsplashPhotos.length > 0 && unsplashHasMore" class="text-center py-4">
                            <button @click="loadMoreUnsplash()"
                                    :disabled="isSearchingUnsplash"
                                    class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition disabled:opacity-50">
                                <span x-show="!isSearchingUnsplash">Load More (20 more photos)</span>
                                <span x-show="isSearchingUnsplash">Loading...</span>
                            </button>
                            <p class="text-xs text-gray-500 mt-2" x-text="'Showing ' + unsplashPhotos.length + ' photos'"></p>
                        </div>

                        <!-- Loading State -->
                        <div x-show="isSearchingUnsplash" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!isSearchingUnsplash && unsplashPhotos.length === 0 && !unsplashQuery" class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M10 9V0L0 9h10zm12 5h10L22 0v14zM0 18h10v14L0 18zm22 0v14l10-14H22z"/>
                            </svg>
                            <p class="text-gray-500">Search for beautiful free photos from Unsplash</p>
                        </div>

                        <!-- No Results -->
                        <div x-show="!isSearchingUnsplash && unsplashPhotos.length === 0 && unsplashQuery" class="text-center py-12">
                            <x-heroicon-o-magnifying-glass class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <p class="text-gray-500">No photos found. Try a different search term.</p>
                        </div>
                    </div>

                </div>

                <!-- Right Panel: Detail View -->
                <div class="w-96 bg-gray-50 p-6 overflow-y-auto">
                    <template x-if="selectedMedia">
                        <div class="space-y-4">
                            <!-- Preview -->
                            <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                <img :src="selectedMedia.full_url || selectedMedia.urls?.regular"
                                     :alt="selectedMedia.alt_text || selectedMedia.alt_description"
                                     class="w-full h-auto">
                            </div>

                            <!-- Unsplash Attribution (required by API guidelines) -->
                            <div x-show="selectedMedia.isUnsplash" class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" viewBox="0 0 32 32" fill="currentColor">
                                        <path d="M10 9V0L0 9h10zm12 5h10L22 0v14zM0 18h10v14L0 18zm22 0v14l10-14H22z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-700">
                                            Photo by
                                            <a :href="selectedMedia.user?.profile_url" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">
                                                <span x-text="selectedMedia.user?.name"></span>
                                            </a>
                                            on
                                            <a href="https://unsplash.com?utm_source=anewp&utm_medium=referral" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">
                                                Unsplash
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Metadata Form (hidden for Unsplash photos) -->
                            <div x-show="!selectedMedia.isUnsplash" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text"
                                           x-model="selectedMedia.title"
                                           @blur="updateMetadata()"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                                    <input type="text"
                                           x-model="selectedMedia.alt_text"
                                           @blur="updateMetadata()"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea x-model="selectedMedia.description"
                                              @blur="updateMetadata()"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                                    <textarea x-model="selectedMedia.caption"
                                              @blur="updateMetadata()"
                                              rows="2"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                                </div>

                                <!-- File Info -->
                                <div class="pt-3 border-t border-gray-200 space-y-1">
                                    <p class="text-xs text-gray-600">
                                        <span class="font-medium">File:</span>
                                        <span x-text="selectedMedia.file_name"></span>
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        <span class="font-medium">Size:</span>
                                        <span x-text="formatFileSize(selectedMedia.file_size)"></span>
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        <span class="font-medium">Uploaded:</span>
                                        <span x-text="formatDate(selectedMedia.created_at)"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="pt-4 border-t border-gray-200 space-y-2">
                                <button @click="confirmSelection()"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                    <span x-text="selectedMedia.isUnsplash ? 'Use This Photo' : 'Set Featured Image'"></span>
                                </button>
                                <button x-show="!selectedMedia.isUnsplash" @click="deleteMedia()"
                                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- No Selection State -->
                    <template x-if="!selectedMedia">
                        <div class="text-center py-12">
                            <x-heroicon-o-photo class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <p class="text-gray-500 text-sm">Select an image to view details</p>
                        </div>
                    </template>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
function mediaLibraryData(languages = []) {
    return {
        isOpen: false,
        activeTab: 'library',
        mediaItems: [],
        selectedMedia: null,
        searchQuery: '',
        isLoading: false,
        isDragging: false,
        uploadProgress: 0,
        isGenerating: false,
        generatePrompt: '',
        generateLanguage: 'id_ID',
        generateProvider: '',
        generateStyle: 'Photo',
        generateSize: '960x768 (5:4)',
        languageOptions: languages,
        languageDropdownOpen: false,
        languageSearch: '',
        unsplashQuery: '',
        unsplashPhotos: [],
        isSearchingUnsplash: false,
        unsplashCurrentPage: 1,
        unsplashHasMore: true,
        get filteredLanguageOptions() {
            if (!this.languageSearch) return this.languageOptions;
            return this.languageOptions.filter(lang =>
                lang.name.toLowerCase().includes(this.languageSearch.toLowerCase())
            );
        },
        get selectedLanguage() {
            return this.languageOptions.find(l => l.code === this.generateLanguage) || this.languageOptions[0] || { flag: '', name: '' };
        },
        callback: null,

        init() {
            // Listen for global open event
            window.addEventListener('open-media-library', (e) => {
                this.callback = e.detail?.callback || null;
                this.open();
            });
        },

        open() {
            this.isOpen = true;
            this.activeTab = 'library';
            this.loadMedia();
        },

        close() {
            this.isOpen = false;
            this.selectedMedia = null;
            this.searchQuery = '';
            this.uploadProgress = 0;
        },

        async loadMedia() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/media/list');
                const data = await response.json();
                this.mediaItems = data.media || [];
            } catch (error) {
                console.error('Error loading media:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async searchMedia() {
            // Debounce search
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(async () => {
                this.isLoading = true;
                try {
                    const response = await fetch(`/api/media/search?q=${encodeURIComponent(this.searchQuery)}`);
                    const data = await response.json();
                    this.mediaItems = data.media || [];
                } catch (error) {
                    console.error('Error searching media:', error);
                } finally {
                    this.isLoading = false;
                }
            }, 300);
        },

        selectMedia(item) {
            this.selectedMedia = { ...item };
        },

        async updateMetadata() {
            if (!this.selectedMedia) return;

            try {
                const response = await fetch(`/api/media/${this.selectedMedia.id}/metadata`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: this.selectedMedia.title,
                        alt_text: this.selectedMedia.alt_text,
                        description: this.selectedMedia.description,
                        caption: this.selectedMedia.caption
                    })
                });

                if (!response.ok) throw new Error('Failed to update metadata');

                // Update in list
                const index = this.mediaItems.findIndex(m => m.id === this.selectedMedia.id);
                if (index !== -1) {
                    this.mediaItems[index] = { ...this.selectedMedia };
                }
            } catch (error) {
                console.error('Error updating metadata:', error);
                alert('Failed to update metadata');
            }
        },

        async handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                await this.uploadFile(file);
            }
        },

        async handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                await this.uploadFile(file);
            }
        },

        async uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            this.uploadProgress = 0;

            try {
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            this.mediaItems.unshift(data.media);
                            this.selectMedia(data.media);
                            this.activeTab = 'library';
                            this.uploadProgress = 0;
                        } else {
                            console.error('Upload failed:', data);
                            alert('Upload failed: ' + (data.message || 'Unknown error'));
                            this.uploadProgress = 0;
                        }
                    } else {
                        console.error('HTTP Error:', xhr.status, xhr.responseText);
                        alert('Upload failed with status: ' + xhr.status);
                        this.uploadProgress = 0;
                    }
                });

                xhr.addEventListener('error', () => {
                    console.error('Network error during upload');
                    alert('Network error during upload');
                    this.uploadProgress = 0;
                });

                xhr.open('POST', '/media/upload');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.send(formData);

            } catch (error) {
                console.error('Upload error:', error);
                alert('Upload failed: ' + error.message);
                this.uploadProgress = 0;
            }
        },

        async generateImage() {
            if (!this.generatePrompt) return;

            this.isGenerating = true;

            try {
                // Store in session
                const storeResponse = await fetch('/image-generation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: this.generatePrompt,
                        language: this.generateLanguage,
                        provider: this.generateProvider,
                        image_style: this.generateStyle,
                        image_size: this.generateSize,
                        context_title: document.querySelector('input[name="title"]')?.value || ''
                    })
                });

                if (!storeResponse.ok) throw new Error('Failed to start generation');

                // Generate image
                const generateResponse = await fetch('/image-generation/run', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await generateResponse.json();

                if (result.success) {
                    // Add to library and select
                    await this.loadMedia();
                    const newMedia = this.mediaItems.find(m => m.id === result.media_id);
                    if (newMedia) {
                        this.selectMedia(newMedia);
                    }
                    this.activeTab = 'library';
                    this.generatePrompt = '';
                } else {
                    throw new Error(result.error || 'Generation failed');
                }

            } catch (error) {
                console.error('Error generating image:', error);
                alert('Failed to generate image: ' + error.message);
            } finally {
                this.isGenerating = false;
            }
        },

        async deleteMedia() {
            if (!this.selectedMedia) return;
            if (!confirm('Are you sure you want to delete this image?')) return;

            try {
                const response = await fetch(`/media/${this.selectedMedia.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Failed to delete');

                // Remove from list
                this.mediaItems = this.mediaItems.filter(m => m.id !== this.selectedMedia.id);
                this.selectedMedia = null;

            } catch (error) {
                console.error('Error deleting media:', error);
                alert('Failed to delete image');
            }
        },

        async confirmSelection() {
            if (!this.selectedMedia) return;

            // Save Unsplash photo to library first (creates local media record)
            if (this.selectedMedia.isUnsplash) {
                try {
                    const response = await fetch('/unsplash/save-to-library', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            unsplash_id: this.selectedMedia.id,
                            url: this.selectedMedia.urls.regular,
                            title: this.selectedMedia.description || this.selectedMedia.alt_description || 'Unsplash Photo',
                            alt_text: this.selectedMedia.alt_description || '',
                            description: this.selectedMedia.description || '',
                            photographer_name: this.selectedMedia.user.name,
                            photographer_url: this.selectedMedia.user.profile_url,
                            download_location: this.selectedMedia.links.download_location,
                            width: this.selectedMedia.width,
                            height: this.selectedMedia.height,
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Replace selected media with the saved media record
                        this.selectedMedia = data.media;
                    } else {
                        alert('Failed to save Unsplash photo to library');
                        return;
                    }

                } catch (error) {
                    console.error('Failed to save Unsplash photo:', error);
                    alert('Error saving Unsplash photo: ' + error.message);
                    return;
                }
            }

            // Call callback if provided
            if (this.callback) {
                this.callback(this.selectedMedia);
            }

            // Dispatch event for parent listeners
            window.dispatchEvent(new CustomEvent('media-selected', {
                detail: this.selectedMedia
            }));

            this.close();
        },

        async generatePromptFromContent() {
            const contentElement = document.querySelector('#quillEditor .ql-editor, [name="content"]');
            let articleContent = '';

            // 1) Ambil dari editor/content jika ada
            if (contentElement) {
                articleContent = contentElement.innerHTML || contentElement.value || '';
            }

            // 2) Jika kosong, coba pakai Image Prompt (jika sudah diisi user)
            if (!articleContent.trim() && this.generatePrompt && this.generatePrompt.trim()) {
                articleContent = this.generatePrompt.trim();
            }

            // 3) Jika masih kosong, coba subheadline (jika ada di DOM visual editor)
            if (!articleContent.trim()) {
                const subheadlineEl = document.querySelector('[x-model=\"editableSubheadline\"]');
                if (subheadlineEl && subheadlineEl.value) {
                    articleContent = subheadlineEl.value;
                }
            }

            // 4) Kalau masih kosong, tanya user
            if (!articleContent.trim()) {
                const userInput = prompt('Artikel belum ada isinya. Kamu mau bikin gambar apa?');
                if (!userInput) return;
                articleContent = userInput;
            }

            this.isGenerating = true;

            try {
                const response = await fetch('/image-generation/generate-prompt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: articleContent
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.generatePrompt = data.prompt;
                } else {
                    alert('Failed to generate prompt: ' + (data.error || 'Unknown error'));
                }

            } catch (error) {
                console.error('Generate prompt error:', error);
                alert('Error: ' + error.message);
            } finally {
                this.isGenerating = false;
            }
        },

        async searchUnsplash() {
            if (!this.unsplashQuery) return;

            // Reset for new search
            this.isSearchingUnsplash = true;
            this.unsplashPhotos = [];
            this.unsplashCurrentPage = 1;
            this.unsplashHasMore = true;

            try {
                const response = await fetch('/unsplash/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: this.unsplashQuery,
                        page: 1,
                        per_page: 20
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.unsplashPhotos = data.results;
                    // Check if there are more pages (Unsplash returns total_pages in response)
                    this.unsplashHasMore = data.total_pages > 1;
                } else {
                    alert('Failed to search Unsplash: ' + (data.error || 'Unknown error'));
                }

            } catch (error) {
                console.error('Unsplash search error:', error);
                alert('Error searching Unsplash: ' + error.message);
            } finally {
                this.isSearchingUnsplash = false;
            }
        },

        async loadMoreUnsplash() {
            if (!this.unsplashQuery || this.isSearchingUnsplash) return;

            this.isSearchingUnsplash = true;
            this.unsplashCurrentPage++;

            try {
                const response = await fetch('/unsplash/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: this.unsplashQuery,
                        page: this.unsplashCurrentPage,
                        per_page: 20
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Append new results to existing photos
                    this.unsplashPhotos = [...this.unsplashPhotos, ...data.results];
                    // Check if there are more pages
                    this.unsplashHasMore = this.unsplashCurrentPage < data.total_pages;
                } else {
                    alert('Failed to load more photos: ' + (data.error || 'Unknown error'));
                }

            } catch (error) {
                console.error('Load more error:', error);
                alert('Error loading more photos: ' + error.message);
            } finally {
                this.isSearchingUnsplash = false;
            }
        },

        selectUnsplashPhoto(photo) {
            // Mark as Unsplash photo and add full_url for compatibility
            this.selectedMedia = {
                ...photo,
                isUnsplash: true,
                full_url: photo.urls.regular,
                alt_text: photo.alt_description,
                description: photo.description
            };
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
