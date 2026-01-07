<template>
    <div class="calendar-preview-widget">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="events && events.length > 0" class="space-y-2 max-h-96 overflow-y-auto">
            <div v-for="event in events" :key="event.id" class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div :class="[
                        'w-2 h-2 rounded-full',
                        getEventColor(event.type)
                    ]"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ event.title }}</p>
                    <p class="text-xs text-gray-500">{{ formatEventTime(event.start) }}</p>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            No upcoming events
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'CalendarPreviewWidget',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const events = ref([]);
        const loading = ref(true);
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;

        const loadEvents = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${organizationId}/dashboard/content-calendar`, {
                    params: { days: 7 },
                });
                events.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load events:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatEventTime = (timestamp) => {
            const date = new Date(timestamp);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        };

        const getEventColor = (type) => {
            const colors = {
                post: 'bg-blue-500',
                campaign: 'bg-purple-500',
                meeting: 'bg-green-500',
                deadline: 'bg-red-500',
                reminder: 'bg-yellow-500',
                custom: 'bg-gray-500',
            };
            return colors[type] || 'bg-gray-500';
        };

        onMounted(() => {
            loadEvents();
            // Refresh every 5 minutes
            setInterval(loadEvents, 300000);
        });

        return {
            events,
            loading,
            formatEventTime,
            getEventColor,
        };
    },
};
</script>


