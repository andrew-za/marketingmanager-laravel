<template>
    <div class="platform-content-preview">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Preview</h3>
            
            <!-- Platform Selector -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    v-for="platform in platforms"
                    :key="platform"
                    @click="selectedPlatform = platform"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        selectedPlatform === platform
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]"
                >
                    {{ getPlatformName(platform) }}
                </button>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="platform in selectedPlatforms"
                :key="platform"
                class="preview-card"
            >
                <div class="preview-header">
                    <div class="flex items-center space-x-2">
                        <img :src="getPlatformIcon(platform)" :alt="platform" class="w-5 h-5">
                        <span class="font-semibold">{{ getPlatformName(platform) }}</span>
                    </div>
                </div>
                
                <div class="preview-content" :class="getPlatformPreviewClass(platform)">
                    <!-- Facebook Preview -->
                    <div v-if="platform === 'facebook'" class="facebook-preview">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                            <div>
                                <div class="font-semibold text-sm">Page Name</div>
                                <div class="text-xs text-gray-500">Just now</div>
                            </div>
                        </div>
                        <div class="content-text mb-3">{{ content }}</div>
                        <div v-if="imageUrl" class="image-preview mb-3">
                            <img :src="imageUrl" alt="Preview" class="w-full rounded-lg">
                        </div>
                        <div class="flex items-center justify-between text-gray-500 text-sm pt-3 border-t">
                            <span>👍 Like</span>
                            <span>💬 Comment</span>
                            <span>📤 Share</span>
                        </div>
                    </div>

                    <!-- Instagram Preview -->
                    <div v-else-if="platform === 'instagram'" class="instagram-preview">
                        <div class="flex items-center justify-between p-3 border-b">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                                <span class="font-semibold text-sm">username</span>
                            </div>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <div v-if="imageUrl" class="image-preview">
                            <img :src="imageUrl" alt="Preview" class="w-full">
                        </div>
                        <div class="p-3">
                            <div class="flex items-center space-x-4 mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                <svg class="w-6 h-6 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h14M5 12h14M5 19h14"></path>
                                </svg>
                            </div>
                            <div class="font-semibold text-sm mb-1">username</div>
                            <div class="content-text text-sm">{{ content }}</div>
                            <div class="text-xs text-gray-500 mt-2">View all comments</div>
                        </div>
                    </div>

                    <!-- Twitter/X Preview -->
                    <div v-else-if="platform === 'twitter'" class="twitter-preview">
                        <div class="flex items-start space-x-3 p-3">
                            <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-semibold text-sm">Display Name</span>
                                    <span class="text-gray-500 text-sm">@username</span>
                                    <span class="text-gray-500 text-sm">·</span>
                                    <span class="text-gray-500 text-sm">Just now</span>
                                </div>
                                <div class="content-text text-sm mb-3">{{ content }}</div>
                                <div v-if="imageUrl" class="image-preview mb-3 rounded-lg overflow-hidden">
                                    <img :src="imageUrl" alt="Preview" class="w-full">
                                </div>
                                <div class="flex items-center justify-between text-gray-500 text-sm">
                                    <span>💬</span>
                                    <span>🔄</span>
                                    <span>❤️</span>
                                    <span>📤</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LinkedIn Preview -->
                    <div v-else-if="platform === 'linkedin'" class="linkedin-preview">
                        <div class="p-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                                <div>
                                    <div class="font-semibold text-sm">Company Name</div>
                                    <div class="text-xs text-gray-500">Just now</div>
                                </div>
                            </div>
                            <div class="content-text mb-3">{{ content }}</div>
                            <div v-if="imageUrl" class="image-preview mb-3 rounded-lg overflow-hidden">
                                <img :src="imageUrl" alt="Preview" class="w-full">
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm pt-3 border-t">
                                <span>👍 Like</span>
                                <span>💬 Comment</span>
                                <span>📤 Share</span>
                                <span>📧 Send</span>
                            </div>
                        </div>
                    </div>

                    <!-- Generic Preview -->
                    <div v-else class="generic-preview">
                        <div class="p-4">
                            <div class="content-text mb-3">{{ content }}</div>
                            <div v-if="imageUrl" class="image-preview mb-3">
                                <img :src="imageUrl" alt="Preview" class="w-full rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
    name: 'PlatformContentPreviewComponent',
    props: {
        content: {
            type: String,
            default: '',
        },
        imageUrl: {
            type: String,
            default: '',
        },
        platforms: {
            type: Array,
            default: () => ['facebook', 'instagram', 'twitter', 'linkedin'],
        },
    },
    setup(props) {
        const selectedPlatform = ref(null);

        const selectedPlatforms = computed(() => {
            if (selectedPlatform.value) {
                return [selectedPlatform.value];
            }
            return props.platforms;
        });

        const getPlatformName = (platform) => {
            const names = {
                facebook: 'Facebook',
                instagram: 'Instagram',
                twitter: 'Twitter/X',
                linkedin: 'LinkedIn',
                tiktok: 'TikTok',
                pinterest: 'Pinterest',
            };
            return names[platform] || platform;
        };

        const getPlatformIcon = (platform) => {
            // In a real app, you'd use actual platform icons
            return `https://via.placeholder.com/20?text=${platform.charAt(0).toUpperCase()}`;
        };

        const getPlatformPreviewClass = (platform) => {
            const classes = {
                facebook: 'bg-white border border-gray-200',
                instagram: 'bg-white border border-gray-200',
                twitter: 'bg-white border border-gray-200',
                linkedin: 'bg-white border border-gray-200',
            };
            return classes[platform] || 'bg-gray-50 border border-gray-200';
        };

        return {
            selectedPlatform,
            selectedPlatforms,
            getPlatformName,
            getPlatformIcon,
            getPlatformPreviewClass,
        };
    },
};
</script>

<style scoped>
.preview-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.preview-header {
    background: #f9fafb;
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.preview-content {
    padding: 0;
}

.facebook-preview,
.instagram-preview,
.twitter-preview,
.linkedin-preview {
    padding: 12px;
}

.content-text {
    white-space: pre-wrap;
    word-wrap: break-word;
}

.image-preview {
    width: 100%;
}

.image-preview img {
    object-fit: cover;
}
</style>


