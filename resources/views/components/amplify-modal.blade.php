<!-- Amplify to Social Media Modal Component -->
<div x-data="amplifyModalData()" x-show="isOpen" x-cloak @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-hidden" style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
         @click="close()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg w-full max-w-md shadow-2xl" @click.away="close()">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Amplify to Social Media</h2>
                <button @click="close()" class="text-gray-400 hover:text-gray-600 transition">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Select branding preset and channels:</p>

                <!-- Branding Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branding Preset</label>
                    <select x-model="selectedBranding" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Branding --</option>
                        <template x-for="branding in brandings" :key="branding.id">
                            <option :value="branding.id" x-text="branding.preset_name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Choose which branding style to use for this post</p>
                </div>

                <!-- Channel Selection -->
                <div class="space-y-3 mb-6">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" x-model="selectedChannels" value="instagram"
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </div>
                            <span class="font-medium text-gray-900">Instagram</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" x-model="selectedChannels" value="facebook"
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </div>
                            <span class="font-medium text-gray-900">Facebook</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" x-model="selectedChannels" value="twitter"
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-sky-500 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </div>
                            <span class="font-medium text-gray-900">Twitter</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" x-model="selectedChannels" value="linkedin"
                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        <div class="flex items-center gap-2 flex-1">
                            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </div>
                            <span class="font-medium text-gray-900">LinkedIn</span>
                        </div>
                    </label>
                </div>

                <!-- Auto Schedule Option -->
                <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-6">
                    <input type="checkbox" x-model="autoSchedule"
                           class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Auto-schedule posts</div>
                        <div class="text-xs text-gray-600">Automatically schedule posts with AI recommendations</div>
                    </div>
                </label>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button @click="close()" type="button"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                        Cancel
                    </button>
                    <button @click="amplify()" type="button"
                            :disabled="!selectedBranding || selectedChannels.length === 0 || isProcessing"
                            :class="!selectedBranding || selectedChannels.length === 0 || isProcessing ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                            class="flex-1 px-4 py-2 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                        <svg x-show="isProcessing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isProcessing ? 'Generating...' : 'Amplify'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function amplifyModalData() {
    return {
        isOpen: false,
        selectedChannels: ['instagram', 'facebook', 'twitter', 'linkedin'], // Default all checked
        selectedBranding: '',
        brandings: [],
        autoSchedule: false,
        isProcessing: false,
        postId: null,
        postIds: [],  // Support bulk amplify
        callback: null,

        init() {
            // Load brandings
            this.loadBrandings();

            // Listen for open-amplify-modal event
            window.addEventListener('open-amplify-modal', (e) => {
                // Support both single and multiple posts
                this.postId = e.detail.postId || null;
                this.postIds = e.detail.postIds || [];
                this.callback = e.detail.callback || null;
                this.selectedChannels = e.detail.defaultChannels || ['instagram', 'facebook', 'twitter', 'linkedin'];
                this.autoSchedule = e.detail.autoSchedule || false;
                this.open();
            });
        },

        loadBrandings() {
            fetch('/api/brandings', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                this.brandings = data.brandings || [];
                // Auto-select first branding if only one exists
                if (this.brandings.length === 1) {
                    this.selectedBranding = this.brandings[0].id;
                }
            })
            .catch(error => {
                console.error('Failed to load brandings:', error);
            });
        },

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
            this.isProcessing = false;
        },

        async amplify() {
            if (!this.selectedBranding) {
                alert('Please select a branding preset');
                return;
            }

            if (this.selectedChannels.length === 0) {
                alert('Please select at least one channel');
                return;
            }

            // Check if bulk or single amplify
            const isBulk = this.postIds.length > 0;
            const targetPostIds = isBulk ? this.postIds : [this.postId];

            if (!isBulk && !this.postId) {
                alert('Error: Post ID is missing');
                return;
            }

            this.isProcessing = true;

            try {
                // Process each post sequentially
                let totalAssets = 0;
                for (const postId of targetPostIds) {
                    const response = await fetch(`/posts/${postId}/amplify`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            branding_id: this.selectedBranding,
                            channels: this.selectedChannels,
                            auto_schedule: this.autoSchedule
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        totalAssets += data.assets.length;
                    } else {
                        console.error(`Failed to amplify post ${postId}:`, data.error);
                    }
                }

                // Call callback if provided
                if (this.callback && typeof this.callback === 'function') {
                    this.callback({ totalAssets });
                }

                this.close();

                // Redirect to assets page
                if (isBulk) {
                    window.location.href = '/assets';
                } else {
                    window.location.href = '/assets?post_id=' + this.postId;
                }
            } catch (error) {
                console.error('Amplify error:', error);
                alert('Failed to amplify post. Please try again.');
                this.isProcessing = false;
            }
        }
    };
}
</script>
@endpush
