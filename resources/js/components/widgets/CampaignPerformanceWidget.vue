<template>
    <div class="campaign-performance-widget">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="performance && performance.length > 0" class="space-y-3">
            <div v-for="campaign in performance" :key="campaign.id" class="p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ campaign.name }}</h4>
                    <span :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        getStatusClass(campaign.status)
                    ]">
                        {{ campaign.status }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <div class="text-gray-500">Engagement</div>
                        <div class="font-semibold text-gray-900">{{ formatNumber(campaign.engagement) }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Reach</div>
                        <div class="font-semibold text-gray-900">{{ formatNumber(campaign.reach) }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">ROI</div>
                        <div class="font-semibold" :class="campaign.roi >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ campaign.roi >= 0 ? '+' : '' }}{{ campaign.roi }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            No campaign data available
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'CampaignPerformanceWidget',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const performance = ref([]);
        const loading = ref(true);
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;

        const loadPerformance = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${organizationId}/dashboard`);
                performance.value = response.data.data?.campaign_performance || [];
            } catch (error) {
                console.error('Failed to load campaign performance:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatNumber = (value) => {
            if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`;
            if (value >= 1000) return `${(value / 1000).toFixed(1)}K`;
            return value.toString();
        };

        const getStatusClass = (status) => {
            const classes = {
                active: 'bg-green-100 text-green-800',
                paused: 'bg-yellow-100 text-yellow-800',
                completed: 'bg-blue-100 text-blue-800',
                draft: 'bg-gray-100 text-gray-800',
            };
            return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
        };

        onMounted(() => {
            loadPerformance();
            // Refresh every 5 minutes
            setInterval(loadPerformance, 300000);
        });

        return {
            performance,
            loading,
            formatNumber,
            getStatusClass,
        };
    },
};
</script>


