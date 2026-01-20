<!-- Amplify Builder Modal Component -->
<div x-data="amplifyBuilderData()" x-show="isOpen" x-cloak @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-hidden" style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-gray-900 rounded-lg w-full max-w-6xl max-h-[90vh] flex flex-col shadow-2xl text-white"
             @click.away="close()">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
                <div>
                    <h2 class="text-xl font-semibold">Amplify to Social Media</h2>
                    <p class="text-sm text-gray-400 mt-1">Generate social media content from your article</p>
                </div>
                <button @click="close()" class="text-gray-400 hover:text-white transition">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">

                <!-- Step 1: Channel Selection -->
                <div x-show="step === 1" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Select Channels</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Instagram -->
                            <button @click="toggleChannel('instagram')"
                                    :class="channels.instagram ? 'bg-gradient-to-br from-purple-600 to-pink-600 border-transparent' : 'bg-gray-800 border-gray-700 hover:bg-gray-750'"
                                    class="p-4 rounded-lg border-2 transition text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">Instagram</div>
                                        <div class="text-xs text-gray-300">Feed + Story</div>
                                    </div>
                                    <div x-show="channels.instagram" class="text-white">
                                        <x-heroicon-s-check-circle class="w-6 h-6" />
                                    </div>
                                </div>
                            </button>

                            <!-- Facebook -->
                            <button @click="toggleChannel('facebook')"
                                    :class="channels.facebook ? 'bg-blue-600 border-transparent' : 'bg-gray-800 border-gray-700 hover:bg-gray-750'"
                                    class="p-4 rounded-lg border-2 transition text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">Facebook</div>
                                        <div class="text-xs text-gray-300">Page Post</div>
                                    </div>
                                    <div x-show="channels.facebook" class="text-white">
                                        <x-heroicon-s-check-circle class="w-6 h-6" />
                                    </div>
                                </div>
                            </button>

                            <!-- Twitter/X -->
                            <button @click="toggleChannel('twitter')"
                                    :class="channels.twitter ? 'bg-black border-white' : 'bg-gray-800 border-gray-700 hover:bg-gray-750'"
                                    class="p-4 rounded-lg border-2 transition text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">Twitter / X</div>
                                        <div class="text-xs text-gray-300">Post + Thread</div>
                                    </div>
                                    <div x-show="channels.twitter" class="text-white">
                                        <x-heroicon-s-check-circle class="w-6 h-6" />
                                    </div>
                                </div>
                            </button>

                            <!-- LinkedIn -->
                            <button @click="toggleChannel('linkedin')"
                                    :class="channels.linkedin ? 'bg-blue-700 border-transparent' : 'bg-gray-800 border-gray-700 hover:bg-gray-750'"
                                    class="p-4 rounded-lg border-2 transition text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">LinkedIn</div>
                                        <div class="text-xs text-gray-300">Professional Post</div>
                                    </div>
                                    <div x-show="channels.linkedin" class="text-white">
                                        <x-heroicon-s-check-circle class="w-6 h-6" />
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="flex justify-end gap-3">
                        <button @click="close()" type="button"
                                class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg transition">
                            Cancel
                        </button>
                        <button @click="generateAssets()" type="button"
                                :disabled="!hasSelectedChannels()"
                                :class="hasSelectedChannels() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-700 cursor-not-allowed opacity-50'"
                                class="px-6 py-3 rounded-lg transition font-semibold">
                            Generate Social Content
                        </button>
                    </div>
                </div>

                <!-- Step 2: Loading State -->
                <div x-show="step === 2" class="space-y-6">
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4 animate-pulse">
                            <x-heroicon-o-sparkles class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Generating Content DNA...</h3>
                        <p class="text-gray-400">Extracting key elements from your article</p>
                        <div class="mt-6 max-w-md mx-auto">
                            <div class="w-full bg-gray-800 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                     :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="text-sm text-gray-400 mt-2" x-text="loadingMessage"></p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Edit Mode -->
                <div x-show="step === 3" class="space-y-6">
                    <!-- DNA Preview Card (Collapsible) -->
                    <div class="bg-gray-800 rounded-lg border border-gray-700">
                        <button @click="dnaExpanded = !dnaExpanded" type="button"
                                class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-750 transition">
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <x-heroicon-o-document-text class="w-5 h-5 text-blue-500" />
                                Content DNA
                            </h3>
                            <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform"
                                                       :class="dnaExpanded ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="dnaExpanded" x-collapse class="px-6 pb-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-400 mb-1">Headline</div>
                                    <div class="font-medium" x-text="dna?.headline || '-'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-400 mb-1">Tone</div>
                                    <div class="font-medium capitalize" x-text="dna?.tone || '-'"></div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-gray-400 mb-1">Key Points</div>
                                    <ul class="list-disc list-inside space-y-1">
                                        <template x-for="point in dna?.key_points || []" :key="point">
                                            <li x-text="point"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Editable Social Copy Cards -->
                    <div class="space-y-4">
                        <template x-for="(asset, index) in assets" :key="asset.id">
                            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold"
                                              :class="{
                                                  'bg-gradient-to-br from-purple-600 to-pink-600': asset.channel === 'instagram',
                                                  'bg-blue-600': asset.channel === 'facebook',
                                                  'bg-black border border-white': asset.channel === 'twitter',
                                                  'bg-blue-700': asset.channel === 'linkedin'
                                              }">
                                            <span x-text="asset.channel.charAt(0).toUpperCase()"></span>
                                        </span>
                                        <div>
                                            <div class="font-semibold capitalize" x-text="asset.channel"></div>
                                            <div class="text-xs text-gray-400">
                                                Version <span x-text="asset.copy_version"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <!-- Version Selector -->
                                        <select @change="loadVersion(asset.id, $event.target.value)"
                                                class="text-xs bg-gray-700 border-gray-600 rounded px-2 py-1">
                                            <template x-for="v in getVersions(asset.id)" :key="v">
                                                <option :value="v" :selected="v === asset.copy_version"
                                                        x-text="`v${v}`"></option>
                                            </template>
                                        </select>
                                        <!-- Regenerate Button -->
                                        <button @click="regenerateAsset(index)" type="button"
                                                class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1">
                                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                                            Regenerate
                                        </button>
                                    </div>
                                </div>

                                <!-- Character Counter -->
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs text-gray-400">
                                        <span :class="getCharCount(asset) > getCharLimit(asset.channel) ? 'text-red-400 font-semibold' : 'text-gray-400'"
                                              x-text="getCharCount(asset)"></span>
                                        <span class="text-gray-500"> / </span>
                                        <span x-text="getCharLimit(asset.channel)"></span>
                                        <span> chars</span>
                                    </div>
                                    <div class="text-xs" :class="isSaved(asset.id) ? 'text-green-400' : 'text-gray-500'">
                                        <span x-show="isSaved(asset.id)">Saved</span>
                                        <span x-show="!isSaved(asset.id)">Unsaved</span>
                                    </div>
                                </div>

                                <!-- Editable Textarea -->
                                <textarea @input="onCopyChange(asset.id); autoSave(asset.id)"
                                          x-model="asset.copy_text"
                                          :rows="asset.channel === 'twitter' ? 4 : 6"
                                          class="w-full bg-gray-900 text-white border border-gray-700 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                          :class="getCharCount(asset) > getCharLimit(asset.channel) ? 'border-red-500' : ''"
                                          :placeholder="`Edit ${asset.channel} copy...`"></textarea>
                            </div>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-700">
                        <button @click="close()" type="button"
                                class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg transition">
                            Close
                        </button>
                        <div class="flex gap-3">
                            <button @click="saveDrafts()" type="button"
                                    class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                Save Drafts
                            </button>
                            <button @click="markReady()" type="button"
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-lg transition font-semibold">
                                Mark as Ready
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="step === 'error'" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-600 rounded-full mb-4">
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-red-500">Generation Failed</h3>
                    <p class="text-gray-400 mb-6" x-text="errorMessage"></p>
                    <div class="flex justify-center gap-3">
                        <button @click="close()" type="button"
                                class="px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg transition">
                            Cancel
                        </button>
                        <button @click="retryGeneration()" type="button"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                            Try Again
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function amplifyBuilderData() {
    return {
        isOpen: false,
        step: 1, // 1: channel selection, 2: loading, 3: edit mode, 'error': error
        postId: null,
        channels: {
            instagram: false,
            facebook: false,
            twitter: false,
            linkedin: false
        },
        dna: null,
        assets: [],
        progress: 0,
        loadingMessage: 'Initializing...',
        errorMessage: '',
        dnaExpanded: false,
        savedAssets: new Set(),
        autoSaveTimers: {},
        charLimits: {
            instagram: 2200,
            facebook: 63206,
            twitter: 280,
            linkedin: 3000
        },

        open(postId) {
            this.postId = postId;
            this.isOpen = true;
            this.reset();
        },

        close() {
            this.isOpen = false;
            this.reset();
        },

        reset() {
            this.step = 1;
            this.channels = { instagram: false, facebook: false, twitter: false, linkedin: false };
            this.dna = null;
            this.assets = [];
            this.progress = 0;
            this.errorMessage = '';
            this.dnaExpanded = false;
            this.savedAssets.clear();
            Object.values(this.autoSaveTimers).forEach(timer => clearTimeout(timer));
            this.autoSaveTimers = {};
        },

        toggleChannel(channel) {
            this.channels[channel] = !this.channels[channel];
        },

        hasSelectedChannels() {
            return Object.values(this.channels).some(selected => selected);
        },

        getCharLimit(channel) {
            return this.charLimits[channel] || 1000;
        },

        getCharCount(asset) {
            return asset.copy_text?.length || 0;
        },

        isSaved(assetId) {
            return this.savedAssets.has(assetId);
        },

        onCopyChange(assetId) {
            this.savedAssets.delete(assetId);
        },

        getVersions(assetId) {
            // Return available versions (default v1, can be extended)
            const asset = this.assets.find(a => a.id === assetId);
            return Array.from({ length: asset?.copy_version || 1 }, (_, i) => i + 1);
        },

        loadVersion(assetId, version) {
            // Placeholder: Load specific version from server
            console.log(`Loading version ${version} for asset ${assetId}`);
        },

        autoSave(assetId) {
            // Clear existing timer
            if (this.autoSaveTimers[assetId]) {
                clearTimeout(this.autoSaveTimers[assetId]);
            }

            // Set new timer (2 seconds debounce)
            this.autoSaveTimers[assetId] = setTimeout(() => {
                this.saveAsset(assetId);
            }, 2000);
        },

        async saveAsset(assetId) {
            const asset = this.assets.find(a => a.id === assetId);
            if (!asset) return;

            try {
                const response = await fetch(`/social-assets/${assetId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        copy_text: asset.copy_text,
                        status: 'draft'
                    })
                });

                if (response.ok) {
                    this.savedAssets.add(assetId);
                }
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        },

        async regenerateAsset(index) {
            const asset = this.assets[index];
            if (!asset) return;

            // Show loading for this specific card
            asset.isRegenerating = true;

            try {
                const response = await fetch(`/social-assets/${asset.id}/regenerate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update with new version
                    this.assets[index] = data.asset;
                    this.savedAssets.delete(asset.id);
                }
            } catch (error) {
                console.error('Regeneration failed:', error);
            } finally {
                delete asset.isRegenerating;
            }
        },

        async saveDrafts() {
            // Save all assets
            const promises = this.assets.map(asset => this.saveAsset(asset.id));
            await Promise.all(promises);
        },

        async markReady() {
            // Mark all assets as ready
            try {
                const promises = this.assets.map(asset =>
                    fetch(`/social-assets/${asset.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            copy_text: asset.copy_text,
                            status: 'ready'
                        })
                    })
                );

                await Promise.all(promises);
                this.close();
            } catch (error) {
                console.error('Failed to mark ready:', error);
            }
        },

        async generateAssets() {
            const selectedChannels = Object.keys(this.channels).filter(ch => this.channels[ch]);
            if (selectedChannels.length === 0) return;

            this.step = 2;
            this.progress = 0;
            this.loadingMessage = 'Extracting content DNA...';

            // Simulate progress
            const progressInterval = setInterval(() => {
                if (this.progress < 90) {
                    this.progress += 10;
                    if (this.progress >= 30) this.loadingMessage = 'Generating social copies...';
                }
            }, 500);

            try {
                const response = await fetch(`/posts/${this.postId}/amplify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        channels: selectedChannels
                    })
                });

                const data = await response.json();

                clearInterval(progressInterval);
                this.progress = 100;

                if (data.success) {
                    this.dna = data.dna;
                    this.assets = data.assets;
                    setTimeout(() => {
                        this.step = 3;
                    }, 300);
                } else {
                    throw new Error(data.error || 'Generation failed');
                }
            } catch (error) {
                clearInterval(progressInterval);
                this.step = 'error';
                this.errorMessage = error.message || 'An error occurred while generating content';
                console.error('Amplify error:', error);
            }
        },

        retryGeneration() {
            this.step = 1;
        }
    };
}

// Global function to open amplify modal
window.openAmplifyModal = function(postId) {
    Alpine.store('amplifyBuilder', Alpine.evaluate(document.querySelector('[x-data*="amplifyBuilderData"]'), '$data'));
    const component = document.querySelector('[x-data*="amplifyBuilderData"]');
    if (component) {
        Alpine.evaluate(component, '$data.open(' + postId + ')');
    }
};
</script>

<style>
[x-cloak] { display: none !important; }
</style>
