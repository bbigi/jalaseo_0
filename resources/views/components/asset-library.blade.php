@props(['channels' => ['instagram', 'facebook', 'twitter', 'linkedin', 'tiktok']])

<div x-data="assetLibraryData()" x-show="isOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center"
     @keydown.escape.window="close()">

    <!-- Modal Container -->
    <div class="absolute inset-0" @click="close()"></div>

    <div class="relative bg-gray-800 rounded-xl w-full max-w-7xl mx-4 my-8 border border-gray-700 shadow-2xl max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-700">
            <h3 class="text-xl font-bold text-white">Asset Library</h3>
            <button @click="close()" class="text-gray-400 hover:text-white transition">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-700">
            <div class="flex px-6">
                <button @click="activeTab = 'library'"
                        :class="activeTab === 'library' ? 'text-blue-500 border-b-2 border-blue-500' : 'text-gray-400 hover:text-white'"
                        class="px-4 py-3 font-semibold transition">
                    Asset Library
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-hidden flex">
            <!-- Library Tab -->
            <div x-show="activeTab === 'library'" class="flex-1 flex flex-col">
                <!-- Filters -->
                <div class="p-6 border-b border-gray-700 bg-gray-800/50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <input type="text" x-model="filters.search" @input="loadAssets()" placeholder="Search assets..."
                               class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <!-- Channel Filter -->
                        <select x-model="filters.channel" @change="loadAssets()"
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Channels</option>
                            @foreach($channels as $channel)
                                <option value="{{ $channel }}">{{ ucfirst($channel) }}</option>
                            @endforeach
                        </select>

                        <!-- Status Filter -->
                        <select x-model="filters.status" @change="loadAssets()"
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="ready">Ready</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <!-- Content Area: Grid or Detail View -->
                <div class="flex-1 overflow-hidden flex">
                    <!-- Left: Asset Grid -->
                    <div class="flex-1 overflow-y-auto p-6" :class="selectedAsset ? 'lg:w-2/3' : 'w-full'">
                        <div x-show="loading" class="flex items-center justify-center h-64">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                        </div>

                        <div x-show="!loading && assets.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-400">
                            <x-heroicon-o-photo class="w-16 h-16 mb-4" />
                            <p>No assets found</p>
                        </div>

                        <div x-show="!loading && assets.length > 0"
                             class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <template x-for="asset in assets" :key="asset.id">
                                <div @click="selectAsset(asset)"
                                     :class="selectedAsset && selectedAsset.id === asset.id ? 'ring-2 ring-blue-500' : ''"
                                     class="group bg-gray-700 rounded-lg cursor-pointer relative hover:ring-2 hover:ring-blue-400 transition-all"
                                     style="overflow: hidden;">

                                    <!-- 4:5 Aspect Ratio Container -->
                                    <div style="padding-bottom: 125%;"></div>

                                    <!-- Thumbnail -->
                                    <template x-if="asset.visual_path">
                                        <img :src="`/storage/${asset.visual_path}`" :alt="asset.post_title"
                                             class="absolute inset-0 w-full h-full object-cover">
                                    </template>
                                    <template x-if="!asset.visual_path">
                                        <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-gray-600">
                                            <x-heroicon-o-photo class="w-12 h-12 text-gray-400" />
                                        </div>
                                    </template>

                                    <!-- Channel Badge -->
                                    <div class="absolute top-2 left-2 px-2 py-1 rounded-md text-white text-xs font-semibold flex items-center gap-1"
                                         :class="{
                                             'bg-gradient-to-br from-purple-600 to-pink-600': asset.channel === 'instagram',
                                             'bg-blue-600': asset.channel === 'facebook',
                                             'bg-sky-500': asset.channel === 'twitter',
                                             'bg-blue-700': asset.channel === 'linkedin',
                                             'bg-black': asset.channel === 'tiktok'
                                         }">
                                        <span class="uppercase" x-text="asset.channel"></span>
                                    </div>

                                    <!-- Selection Overlay -->
                                    <template x-if="selectedAsset && selectedAsset.id === asset.id">
                                        <div class="absolute inset-0 bg-blue-500/20 flex items-center justify-center">
                                            <div class="bg-blue-500 rounded-full p-2">
                                                <x-heroicon-o-check class="w-6 h-6 text-white" />
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Pagination -->
                        <div x-show="!loading && pagination.last_page > 1" class="mt-6 flex justify-center gap-2">
                            <button @click="loadAssets(pagination.current_page - 1)"
                                    :disabled="pagination.current_page <= 1"
                                    :class="pagination.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg">
                                Previous
                            </button>
                            <span class="px-4 py-2 text-white">
                                Page <span x-text="pagination.current_page"></span> of <span x-text="pagination.last_page"></span>
                            </span>
                            <button @click="loadAssets(pagination.current_page + 1)"
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    :class="pagination.current_page >= pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg">
                                Next
                            </button>
                        </div>
                    </div>

                    <!-- Right: Detail View -->
                    <div x-show="selectedAsset"
                         class="hidden lg:block w-1/3 border-l border-gray-700 bg-gray-800/50 overflow-y-auto p-6">
                        <template x-if="selectedAsset">
                            <div class="space-y-6">
                                <!-- Preview Image -->
                                <div class="bg-gray-700 p-4 rounded-lg border border-gray-600">
                                    <div class="relative w-full">
                                        <div style="padding-bottom: 125%;"></div>
                                        <template x-if="selectedAsset.visual_path">
                                            <img :src="`/storage/${selectedAsset.visual_path}`"
                                                 :alt="selectedAsset.post_title"
                                                 class="absolute inset-0 w-full h-full object-cover rounded">
                                        </template>
                                        <template x-if="!selectedAsset.visual_path">
                                            <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-gray-600 rounded">
                                                <x-heroicon-o-photo class="w-16 h-16 text-gray-400" />
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Auto-save Status -->
                                <div x-show="autoSaveStatus"
                                     :class="autoSaveStatus === 'saving' ? 'bg-blue-900/50 text-blue-300 border-blue-700' : 'bg-green-900/50 text-green-300 border-green-700'"
                                     class="p-3 rounded-lg border">
                                    <span x-text="autoSaveStatus === 'saving' ? 'Saving...' : 'Saved'"></span>
                                </div>

                                <!-- Asset Details -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-300 mb-2">Post</label>
                                        <div class="text-white" x-text="selectedAsset.post?.title || 'Untitled'"></div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-300 mb-2">Copy Text</label>
                                        <textarea x-model="selectedAsset.copy_text"
                                                  @input="triggerAutoSave()"
                                                  rows="6"
                                                  class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-300 mb-2">Hashtags</label>
                                        <textarea x-model="selectedAsset.hashtags"
                                                  @input="triggerAutoSave()"
                                                  rows="3"
                                                  placeholder="#example #hashtags"
                                                  class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-300 mb-2">Channel</label>
                                            <div class="text-white capitalize" x-text="selectedAsset.channel"></div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-300 mb-2">Status</label>
                                            <div class="text-white capitalize" x-text="selectedAsset.status"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-300 mb-2">Created</label>
                                        <div class="text-gray-400 text-sm" x-text="selectedAsset.created_at"></div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2 pt-4">
                                        <button @click="confirmSelection()"
                                                class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold flex items-center justify-center gap-2">
                                            <x-heroicon-o-check class="w-5 h-5" />
                                            Select Asset
                                        </button>
                                        <template x-if="selectedAsset.visual_path">
                                            <a :href="`/storage/${selectedAsset.visual_path}`" download
                                               class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold flex items-center justify-center">
                                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function assetLibraryData() {
    return {
        isOpen: false,
        activeTab: 'library',
        loading: false,
        assets: [],
        selectedAsset: null,
        callback: null,
        autoSaveTimeout: null,
        autoSaveStatus: null,
        filters: {
            search: '',
            channel: '',
            status: ''
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 24
        },

        init() {
            // Listen for global event to open modal
            window.addEventListener('open-asset-library', (e) => {
                this.callback = e.detail?.callback || null;
                this.open();
            });
        },

        open() {
            this.isOpen = true;
            this.loadAssets();
        },

        close() {
            this.isOpen = false;
            this.selectedAsset = null;
            this.filters = { search: '', channel: '', status: '' };
            this.pagination.current_page = 1;
        },

        loadAssets(page = 1) {
            this.loading = true;

            const params = new URLSearchParams({
                page: page,
                search: this.filters.search,
                channel: this.filters.channel,
                status: this.filters.status
            });

            fetch(`/api/assets/data?${params}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.assets = data.assets.data;
                        this.pagination = {
                            current_page: data.assets.current_page,
                            last_page: data.assets.last_page,
                            per_page: data.assets.per_page
                        };
                    }
                })
                .catch(error => {
                    console.error('Failed to load assets:', error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        selectAsset(asset) {
            this.selectedAsset = asset;
        },

        confirmSelection() {
            if (this.callback && typeof this.callback === 'function') {
                this.callback(this.selectedAsset);
            }
            this.close();
        },

        triggerAutoSave() {
            clearTimeout(this.autoSaveTimeout);
            this.autoSaveStatus = 'saving';

            this.autoSaveTimeout = setTimeout(() => {
                this.autoSaveAsset();
            }, 1500);
        },

        autoSaveAsset() {
            if (!this.selectedAsset) return;

            const formData = new FormData();
            formData.append('copy_text', this.selectedAsset.copy_text || '');
            formData.append('hashtags', this.selectedAsset.hashtags || '');

            fetch(`/assets/update/${this.selectedAsset.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.autoSaveStatus = 'saved';
                    setTimeout(() => {
                        this.autoSaveStatus = null;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Auto-save failed:', error);
                this.autoSaveStatus = null;
            });
        }
    }
}

// Listen for visual editor updates
window.addEventListener('visual-updated', (e) => {
    const assetId = e.detail?.assetId;
    const visualUrl = e.detail?.visualUrl;

    if (assetId && visualUrl) {
        // Update thumbnail in grid
        const gridItem = document.querySelector(`img[data-asset-thumbnail="${assetId}"]`);
        if (gridItem) {
            gridItem.src = visualUrl + '?t=' + Date.now();
        }

        // Update modal if open
        if (currentAssetId === assetId) {
            const modalImg = document.querySelector('#assetDetailsContent img');
            if (modalImg) {
                modalImg.src = visualUrl + '?t=' + Date.now();
            }
        }
    }
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
