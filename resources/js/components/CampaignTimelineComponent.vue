<template>
    <div class="campaign-timeline">
        <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="campaign" class="timeline-container">
            <!-- Campaign Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ campaign.name }}</h2>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span :class="getStatusBadgeClass(campaign.status)">{{ campaign.status }}</span>
                    <span>{{ formatDateRange(campaign.start_date, campaign.end_date) }}</span>
                </div>
            </div>

            <!-- Timeline -->
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                
                <div v-for="(milestone, index) in timeline" :key="index" class="relative flex items-start mb-8">
                    <!-- Timeline Dot -->
                    <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full bg-white border-2 border-gray-300"
                         :class="getMilestoneDotClass(milestone.status)">
                        <div v-if="milestone.status === 'completed'" class="w-3 h-3 rounded-full bg-green-500"></div>
                        <div v-else-if="milestone.status === 'in_progress'" class="w-3 h-3 rounded-full bg-primary-500 animate-pulse"></div>
                    </div>

                    <!-- Timeline Content -->
                    <div class="ml-6 flex-1">
                        <div class="card">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ milestone.title }}</h3>
                                    <p class="text-sm text-gray-600 mb-3">{{ milestone.description }}</p>
                                    
                                    <div v-if="milestone.type === 'goal'" class="mt-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-600">Progress</span>
                                            <span class="text-sm font-medium">{{ milestone.current_value }} / {{ milestone.target_value }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                                                :style="{ width: `${(milestone.current_value / milestone.target_value) * 100}%` }"
                                            ></div>
                                        </div>
                                    </div>

                                    <div v-if="milestone.posts && milestone.posts.length > 0" class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Scheduled Posts ({{ milestone.posts.length }})</p>
                                        <div class="space-y-2">
                                            <div v-for="post in milestone.posts" :key="post.id" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                <span class="text-sm text-gray-900">{{ truncate(post.content, 50) }}</span>
                                                <span class="text-xs text-gray-500">{{ formatDateTime(post.scheduled_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-4 text-right">
                                    <span class="text-xs text-gray-500">{{ formatDateTime(milestone.date) }}</span>
                                    <span :class="[
                                        'ml-2 px-2 py-1 text-xs font-medium rounded-full',
                                        getStatusBadgeClass(milestone.status)
                                    ]">
                                        {{ milestone.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-12 text-gray-500">
            Campaign not found
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

export default {
    name: 'CampaignTimelineComponent',
    props: {
        campaignId: {
            type: [String, Number],
            required: true,
        },
        organizationId: {
            type: [String, Number],
            required: true,
        },
    },
    setup(props) {
        const campaign = ref(null);
        const loading = ref(true);

        const timeline = computed(() => {
            if (!campaign.value) return [];

            const milestones = [];

            // Campaign start milestone
            milestones.push({
                title: 'Campaign Started',
                description: `Campaign "${campaign.value.name}" was launched`,
                date: campaign.value.start_date,
                status: campaign.value.status === 'active' || campaign.value.status === 'completed' ? 'completed' : 'pending',
                type: 'event',
            });

            // Goals milestones
            if (campaign.value.goals && campaign.value.goals.length > 0) {
                campaign.value.goals.forEach(goal => {
                    milestones.push({
                        title: goal.name,
                        description: goal.description || `Target: ${goal.target_value} ${goal.metric}`,
                        date: goal.target_date || campaign.value.end_date,
                        status: goal.current_value >= goal.target_value ? 'completed' : 
                                campaign.value.status === 'active' ? 'in_progress' : 'pending',
                        type: 'goal',
                        current_value: goal.current_value,
                        target_value: goal.target_value,
                    });
                });
            }

            // Scheduled posts milestones
            if (campaign.value.scheduled_posts && campaign.value.scheduled_posts.length > 0) {
                const postsByDate = {};
                campaign.value.scheduled_posts.forEach(post => {
                    const date = new Date(post.scheduled_at).toDateString();
                    if (!postsByDate[date]) {
                        postsByDate[date] = [];
                    }
                    postsByDate[date].push(post);
                });

                Object.keys(postsByDate).forEach(date => {
                    milestones.push({
                        title: `Scheduled Posts - ${new Date(date).toLocaleDateString()}`,
                        description: `${postsByDate[date].length} posts scheduled`,
                        date: date,
                        status: new Date(date) < new Date() ? 'completed' : 'pending',
                        type: 'posts',
                        posts: postsByDate[date],
                    });
                });
            }

            // Campaign end milestone
            milestones.push({
                title: 'Campaign End',
                description: `Campaign "${campaign.value.name}" ended`,
                date: campaign.value.end_date,
                status: campaign.value.status === 'completed' ? 'completed' : 'pending',
                type: 'event',
            });

            // Sort by date
            return milestones.sort((a, b) => new Date(a.date) - new Date(b.date));
        });

        const loadCampaign = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${props.organizationId}/campaigns/${props.campaignId}`);
                campaign.value = response.data.data;
            } catch (error) {
                console.error('Failed to load campaign:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatDateRange = (start, end) => {
            const startDate = new Date(start).toLocaleDateString();
            const endDate = new Date(end).toLocaleDateString();
            return `${startDate} - ${endDate}`;
        };

        const formatDateTime = (date) => {
            return new Date(date).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            });
        };

        const getStatusBadgeClass = (status) => {
            const classes = {
                active: 'bg-green-100 text-green-800',
                completed: 'bg-blue-100 text-blue-800',
                paused: 'bg-yellow-100 text-yellow-800',
                draft: 'bg-gray-100 text-gray-800',
                pending: 'bg-gray-100 text-gray-800',
                in_progress: 'bg-primary-100 text-primary-800',
            };
            return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
        };

        const getMilestoneDotClass = (status) => {
            const classes = {
                completed: 'border-green-500',
                in_progress: 'border-primary-500',
                pending: 'border-gray-300',
            };
            return classes[status] || 'border-gray-300';
        };

        const truncate = (text, length) => {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        };

        onMounted(() => {
            loadCampaign();
        });

        return {
            campaign,
            loading,
            timeline,
            formatDateRange,
            formatDateTime,
            getStatusBadgeClass,
            getMilestoneDotClass,
            truncate,
        };
    },
};
</script>

<style scoped>
.timeline-container {
    max-width: 1200px;
    margin: 0 auto;
}
</style>


