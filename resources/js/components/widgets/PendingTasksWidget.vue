<template>
    <div class="pending-tasks-widget">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="tasks && tasks.length > 0" class="space-y-2 max-h-96 overflow-y-auto">
            <div v-for="task in tasks" :key="task.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ task.title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ formatDueDate(task.due_date) }}</p>
                </div>
                <span :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getPriorityClass(task.priority)
                ]">
                    {{ task.priority }}
                </span>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            No pending tasks
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'PendingTasksWidget',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const tasks = ref([]);
        const loading = ref(true);
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;

        const loadTasks = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${organizationId}/dashboard/pending-tasks`, {
                    params: { limit: 10 },
                });
                tasks.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load tasks:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatDueDate = (date) => {
            if (!date) return 'No due date';
            const dueDate = new Date(date);
            const now = new Date();
            const diff = dueDate - now;
            const days = Math.ceil(diff / 86400000);

            if (days < 0) return `Overdue by ${Math.abs(days)} days`;
            if (days === 0) return 'Due today';
            if (days === 1) return 'Due tomorrow';
            if (days < 7) return `Due in ${days} days`;
            return dueDate.toLocaleDateString();
        };

        const getPriorityClass = (priority) => {
            const classes = {
                high: 'bg-red-100 text-red-800',
                medium: 'bg-yellow-100 text-yellow-800',
                low: 'bg-blue-100 text-blue-800',
            };
            return classes[priority?.toLowerCase()] || 'bg-gray-100 text-gray-800';
        };

        onMounted(() => {
            loadTasks();
            // Refresh every 3 minutes
            setInterval(loadTasks, 180000);
        });

        return {
            tasks,
            loading,
            formatDueDate,
            getPriorityClass,
        };
    },
};
</script>


