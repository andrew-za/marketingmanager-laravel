<template>
    <div class="campaign-creation-wizard">
        <!-- Campaign Stepper -->
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                <div v-for="(step, index) in steps" :key="index" class="flex items-center flex-1">
                    <div class="flex flex-col items-center flex-1">
                        <div 
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-colors',
                                step.active ? 'bg-primary-600 text-white' : 
                                step.completed ? 'bg-green-500 text-white' : 
                                'bg-gray-200 text-gray-600'
                            ]"
                        >
                            <span v-if="step.completed">✓</span>
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <span 
                            :class="[
                                'mt-2 text-sm font-medium',
                                step.active ? 'text-primary-600' : 
                                step.completed ? 'text-green-600' : 
                                'text-gray-500'
                            ]"
                        >
                            {{ step.label }}
                        </span>
                    </div>
                    <div v-if="index < steps.length - 1" 
                         :class="[
                             'flex-1 h-0.5 mx-4',
                             step.completed ? 'bg-green-500' : 'bg-gray-200'
                         ]"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Step 1: Plan Campaign -->
        <div v-if="currentStep === 0" class="max-w-4xl mx-auto">
            <div class="card">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Plan Your Campaign</h2>
                
                <form @submit.prevent="handlePlanSubmit" class="space-y-6">
                    <!-- Campaign Goal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Campaign Goal <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="formData.goal"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Describe what you want to achieve with this campaign..."
                            required
                        ></textarea>
                    </div>

                    <!-- Goal Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Goal Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="formData.goalType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            required
                        >
                            <option value="">Select goal type</option>
                            <option value="product_launch">Product Launch</option>
                            <option value="brand_awareness">Brand Awareness</option>
                            <option value="lead_generation">Lead Generation</option>
                            <option value="event_promotion">Event Promotion</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <!-- Brand Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Brand
                        </label>
                        <select
                            v-model="formData.brandId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Select a brand (optional)</option>
                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                {{ brand.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Product Selection -->
                    <div v-if="formData.goalType === 'product_launch'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product
                        </label>
                        <select
                            v-model="formData.productId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Select a product (optional)</option>
                            <option v-for="product in filteredProducts" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Goal Prompt/URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Goal Prompt/URL (Optional)
                        </label>
                        <input
                            v-model="formData.goalPrompt"
                            type="url"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            placeholder="https://example.com/page"
                        />
                    </div>

                    <!-- AI Suggestions -->
                    <div>
                        <button
                            type="button"
                            @click="getAISuggestions"
                            :disabled="loadingSuggestions"
                            class="btn-secondary flex items-center gap-2"
                        >
                            <span v-if="loadingSuggestions" class="animate-spin">⟳</span>
                            <span>{{ loadingSuggestions ? 'Getting Suggestions...' : 'Get AI Suggestions' }}</span>
                        </button>

                        <div v-if="aiSuggestions.length > 0" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(suggestion, index) in aiSuggestions"
                                :key="index"
                                @click="useSuggestion(suggestion)"
                                class="p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition-colors"
                            >
                                <h4 class="font-semibold text-gray-900 mb-2">{{ suggestion.title }}</h4>
                                <p class="text-sm text-gray-600">{{ suggestion.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Channel Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Select Channels <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label
                                v-for="channel in channels"
                                :key="channel.id"
                                class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition-colors"
                                :class="{ 'border-primary-500 bg-primary-50': formData.channelIds.includes(channel.id) }"
                            >
                                <input
                                    type="checkbox"
                                    :value="channel.id"
                                    v-model="formData.channelIds"
                                    class="mr-3 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                />
                                <span class="text-sm font-medium text-gray-700">{{ channel.display_name || channel.platform }}</span>
                            </label>
                        </div>
                        <p v-if="errors.channels" class="mt-2 text-sm text-red-600">{{ errors.channels }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-4 border-t">
                        <button
                            type="button"
                            @click="saveDraft"
                            class="btn-secondary"
                        >
                            Save Draft
                        </button>
                        <button
                            type="submit"
                            :disabled="generatingPlan"
                            class="btn-primary"
                        >
                            {{ generatingPlan ? 'Generating Plan...' : 'Generate Campaign Plan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Campaign Plan Summary (shown after generation) -->
            <div v-if="campaignPlan" class="mt-8 card">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Campaign Plan Summary</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Strategy</h4>
                        <p class="text-gray-700">{{ campaignPlan.strategy }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Content Themes</h4>
                        <ul class="list-disc list-inside text-gray-700 space-y-1">
                            <li v-for="(theme, index) in campaignPlan.contentThemes" :key="index">{{ theme }}</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Posting Schedule</h4>
                        <p class="text-gray-700">{{ campaignPlan.postingSchedule }}</p>
                    </div>
                    <div v-if="campaignPlan.channelRecommendations">
                        <h4 class="font-semibold text-gray-900 mb-2">Channel Recommendations</h4>
                        <div class="space-y-2">
                            <div v-for="(rec, channel) in campaignPlan.channelRecommendations" :key="channel" class="p-3 bg-gray-50 rounded">
                                <strong class="text-gray-900">{{ channel }}:</strong>
                                <p class="text-gray-700 text-sm mt-1">{{ rec }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t">
                        <button
                            @click="goToContentStep"
                            class="btn-primary"
                        >
                            Continue to Content Generation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Generate Content -->
        <div v-if="currentStep === 1" class="max-w-6xl mx-auto">
            <div class="card mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Campaign Plan Summary</h2>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Goal:</strong> {{ formData.goal }}</p>
                    <p><strong>Type:</strong> {{ formatGoalType(formData.goalType) }}</p>
                    <p><strong>Channels:</strong> {{ selectedChannelsNames.join(', ') }}</p>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Generate Content</h2>
                    <button
                        v-if="generatedPosts.length === 0"
                        @click="generateContent"
                        :disabled="generatingContent"
                        class="btn-primary"
                    >
                        {{ generatingContent ? 'Generating Content...' : 'Generate Initial Launch Content' }}
                    </button>
                    <button
                        v-else
                        @click="generateContent"
                        :disabled="generatingContent"
                        class="btn-secondary"
                    >
                        {{ generatingContent ? 'Regenerating...' : 'Regenerate Content' }}
                    </button>
                </div>

                <!-- Generated Posts -->
                <div v-if="generatedPosts.length > 0" class="space-y-6">
                    <div
                        v-for="(post, index) in generatedPosts"
                        :key="index"
                        class="p-4 border border-gray-200 rounded-lg"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded">
                                    {{ post.channel }}
                                </span>
                                <span v-if="post.scheduledAt" class="ml-2 text-sm text-gray-600">
                                    Scheduled: {{ formatDateTime(post.scheduledAt) }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="editPost(index)"
                                    class="text-sm text-primary-600 hover:text-primary-800"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deletePost(index)"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div class="prose max-w-none">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ post.content }}</p>
                            <div v-if="post.hashtags && post.hashtags.length > 0" class="mt-2">
                                <p class="text-sm text-gray-600">
                                    <strong>Hashtags:</strong> {{ post.hashtags.join(' ') }}
                                </p>
                            </div>
                            <div v-if="post.imageSuggestions && post.imageSuggestions.length > 0" class="mt-2">
                                <p class="text-sm text-gray-600">
                                    <strong>Image Suggestions:</strong> {{ post.imageSuggestions.join(', ') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Editor -->
                    <div class="mt-6 p-4 border border-gray-200 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-3">Schedule Posts</h3>
                        <button
                            @click="openScheduleEditor"
                            class="btn-secondary"
                        >
                            Adjust Schedule
                        </button>
                    </div>

                    <!-- Submit for Review -->
                    <div class="flex justify-end pt-4 border-t">
                        <button
                            @click="submitForReview"
                            :disabled="submitting"
                            class="btn-primary"
                        >
                            {{ submitting ? 'Submitting...' : 'Submit for Review' }}
                        </button>
                    </div>
                </div>

                <div v-else-if="!generatingContent" class="text-center py-12 text-gray-500">
                    Click "Generate Initial Launch Content" to create posts for your campaign.
                </div>
            </div>
        </div>

        <!-- Edit Post Modal -->
        <div v-if="editingPostIndex !== null" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Post</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea
                            v-model="editingPost.content"
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hashtags (space-separated)</label>
                        <input
                            v-model="editingPost.hashtagsString"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            placeholder="#hashtag1 #hashtag2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date & Time</label>
                        <input
                            v-model="editingPost.scheduledAt"
                            type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        />
                    </div>
                    <div class="flex justify-end gap-4 pt-4 border-t">
                        <button
                            @click="cancelEdit"
                            class="btn-secondary"
                        >
                            Cancel
                        </button>
                        <button
                            @click="savePost"
                            class="btn-primary"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'CampaignCreationWizard',
    props: {
        organizationId: {
            type: [String, Number],
            required: true,
        },
        brandId: {
            type: [String, Number],
            default: null,
        },
        brands: {
            type: Array,
            default: () => [],
        },
        products: {
            type: Array,
            default: () => [],
        },
        channels: {
            type: Array,
            default: () => [],
        },
    },
    setup(props) {
        const currentStep = ref(0);
        const formData = ref({
            goal: '',
            goalType: '',
            brandId: props.brandId || '',
            productId: '',
            goalPrompt: '',
            channelIds: [],
        });
        const aiSuggestions = ref([]);
        const loadingSuggestions = ref(false);
        const campaignPlan = ref(null);
        const generatingPlan = ref(false);
        const generatedPosts = ref([]);
        const generatingContent = ref(false);
        const editingPostIndex = ref(null);
        const editingPost = ref({});
        const submitting = ref(false);
        const errors = ref({});
        const campaignId = ref(null);

        const steps = computed(() => [
            { label: 'Plan', active: currentStep.value === 0, completed: currentStep.value > 0 },
            { label: 'Content', active: currentStep.value === 1, completed: currentStep.value > 1 },
            { label: 'Review', active: currentStep.value === 2, completed: false },
        ]);

        const filteredProducts = computed(() => {
            if (!formData.value.brandId) return props.products;
            return props.products.filter(p => p.brand_id == formData.value.brandId);
        });

        const selectedChannelsNames = computed(() => {
            return props.channels
                .filter(c => formData.value.channelIds.includes(c.id))
                .map(c => c.display_name || c.platform);
        });

        const loadDraft = () => {
            const draft = localStorage.getItem(`campaign_draft_${props.organizationId}`);
            if (draft) {
                try {
                    const parsed = JSON.parse(draft);
                    formData.value = { ...formData.value, ...parsed };
                } catch (e) {
                    console.error('Failed to load draft:', e);
                }
            }
        };

        const saveDraft = () => {
            localStorage.setItem(`campaign_draft_${props.organizationId}`, JSON.stringify(formData.value));
            alert('Draft saved successfully!');
        };

        const getAISuggestions = async () => {
            loadingSuggestions.value = true;
            try {
                const response = await axios.post(`/main/${props.organizationId}/campaigns/ai/suggestions`, {
                    brand_id: formData.value.brandId,
                    product_id: formData.value.productId,
                });
                aiSuggestions.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to get AI suggestions:', error);
                alert('Failed to get AI suggestions. Please try again.');
            } finally {
                loadingSuggestions.value = false;
            }
        };

        const useSuggestion = (suggestion) => {
            formData.value.goal = suggestion.description || suggestion.title;
            if (suggestion.goalType) {
                formData.value.goalType = suggestion.goalType;
            }
        };

        const handlePlanSubmit = async () => {
            errors.value = {};
            
            if (!formData.value.channelIds || formData.value.channelIds.length === 0) {
                errors.value.channels = 'Please select at least one channel.';
                return;
            }

            generatingPlan.value = true;
            try {
                const response = await axios.post(`/main/${props.organizationId}/campaigns/generate-plan`, {
                    ...formData.value,
                });
                campaignPlan.value = response.data.data.plan;
                campaignId.value = response.data.data.campaign_id;
            } catch (error) {
                console.error('Failed to generate campaign plan:', error);
                alert(error.response?.data?.message || 'Failed to generate campaign plan. Please try again.');
            } finally {
                generatingPlan.value = false;
            }
        };

        const goToContentStep = () => {
            currentStep.value = 1;
        };

        const generateContent = async () => {
            if (!campaignId.value) {
                alert('Please generate a campaign plan first.');
                return;
            }

            generatingContent.value = true;
            try {
                const response = await axios.post(`/main/${props.organizationId}/campaigns/${campaignId.value}/generate-content`, {
                    channels: formData.value.channelIds,
                });
                generatedPosts.value = response.data.data.posts || [];
            } catch (error) {
                console.error('Failed to generate content:', error);
                alert(error.response?.data?.message || 'Failed to generate content. Please try again.');
            } finally {
                generatingContent.value = false;
            }
        };

        const editPost = (index) => {
            editingPostIndex.value = index;
            const post = generatedPosts.value[index];
            editingPost.value = {
                ...post,
                hashtagsString: post.hashtags ? post.hashtags.join(' ') : '',
            };
        };

        const savePost = () => {
            const post = generatedPosts.value[editingPostIndex.value];
            post.content = editingPost.value.content;
            post.hashtags = editingPost.value.hashtagsString
                .split(' ')
                .filter(t => t.trim().startsWith('#'));
            post.scheduledAt = editingPost.value.scheduledAt;
            cancelEdit();
        };

        const cancelEdit = () => {
            editingPostIndex.value = null;
            editingPost.value = {};
        };

        const deletePost = (index) => {
            if (confirm('Are you sure you want to delete this post?')) {
                generatedPosts.value.splice(index, 1);
            }
        };

        const openScheduleEditor = () => {
            // TODO: Implement schedule editor modal
            alert('Schedule editor coming soon!');
        };

        const submitForReview = async () => {
            if (!campaignId.value) {
                alert('Please generate a campaign plan first.');
                return;
            }

            if (generatedPosts.value.length === 0) {
                alert('Please generate at least one post before submitting.');
                return;
            }

            submitting.value = true;
            try {
                // Map posts to include channel IDs
                const postsToSave = generatedPosts.value.map(post => {
                    const channel = props.channels.find(c => 
                        (c.platform && c.platform.toLowerCase() === post.platform.toLowerCase()) ||
                        (c.display_name && c.display_name.toLowerCase() === post.channel.toLowerCase())
                    );
                    return {
                        ...post,
                        channel_id: channel?.id,
                    };
                }).filter(post => post.channel_id);

                if (postsToSave.length === 0) {
                    alert('Could not match posts to channels. Please check your channel configuration.');
                    submitting.value = false;
                    return;
                }

                // Save posts first
                await axios.post(`/main/${props.organizationId}/campaigns/${campaignId.value}/content`, {
                    posts: postsToSave,
                });

                // Submit for review
                await axios.post(`/main/${props.organizationId}/campaigns/${campaignId.value}/submit-for-review`);
                
                // Clear draft
                localStorage.removeItem(`campaign_draft_${props.organizationId}`);
                
                // Redirect to campaigns page
                window.location.href = `/main/${props.organizationId}/campaigns`;
            } catch (error) {
                console.error('Failed to submit for review:', error);
                alert(error.response?.data?.message || 'Failed to submit for review. Please try again.');
            } finally {
                submitting.value = false;
            }
        };

        const formatGoalType = (type) => {
            const types = {
                product_launch: 'Product Launch',
                brand_awareness: 'Brand Awareness',
                lead_generation: 'Lead Generation',
                event_promotion: 'Event Promotion',
                custom: 'Custom',
            };
            return types[type] || type;
        };

        const formatDateTime = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            });
        };

        onMounted(() => {
            loadDraft();
        });

        return {
            currentStep,
            steps,
            formData,
            aiSuggestions,
            loadingSuggestions,
            campaignPlan,
            generatingPlan,
            generatedPosts,
            generatingContent,
            editingPostIndex,
            editingPost,
            submitting,
            errors,
            filteredProducts,
            selectedChannelsNames,
            getAISuggestions,
            useSuggestion,
            handlePlanSubmit,
            saveDraft,
            goToContentStep,
            generateContent,
            editPost,
            savePost,
            cancelEdit,
            deletePost,
            openScheduleEditor,
            submitForReview,
            formatGoalType,
            formatDateTime,
        };
    },
};
</script>

<style scoped>
.card {
    @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.btn-primary {
    @apply px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
    @apply px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
}
</style>

