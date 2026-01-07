<template>
    <div class="activity-feed-widget">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="activities && activities.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
            <div v-for="activity in activities" :key="activity.id" class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-primary-600 text-xs font-semibold">
                            {{ activity.user ? activity.user.name.charAt(0).toUpperCase() : 'A' }}
                        </span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">
                        <span class="font-medium">{{ activity.user ? activity.user.name : 'System' }}</span>
                        {{ activity.description }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ formatTime(activity.created_at) }}</p>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            No recent activity
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'ActivityFeedWidget',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const activities = ref([]);
        const loading = ref(true);
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;

        const loadActivities = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${organizationId}/dashboard/activity-feed`, {
                    params: { limit: 10 },
                });
                activities.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load activities:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatTime = (timestamp) => {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            return date.toLocaleDateString();
        };

        onMounted(() => {
            loadActivities();
            // Refresh every 2 minutes
            setInterval(loadActivities, 120000);
        });

        return {
            activities,
            loading,
            formatTime,
        };
    },
};
</script>


