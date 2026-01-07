<template>
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Dashboard</h2>
                <p class="text-gray-600 mt-1">Welcome back, {{ userName }}</p>
            </div>
            <button 
                @click="toggleEditMode" 
                class="btn btn-secondary"
                :class="{ 'bg-primary-600 text-white': editMode }"
            >
                {{ editMode ? 'Done Editing' : 'Customize Dashboard' }}
            </button>
        </div>

        <!-- Widget Library (shown when editing) -->
        <div v-if="editMode" class="mb-6 card">
            <h3 class="text-lg font-semibold mb-4">Add Widget</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                    v-for="widgetType in availableWidgetTypes"
                    :key="widgetType.type"
                    @click="addWidget(widgetType)"
                    class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors"
                >
                    <div class="text-center">
                        <div class="text-2xl mb-2">{{ widgetType.icon }}</div>
                        <div class="text-sm font-medium">{{ widgetType.name }}</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div 
            ref="gridContainer"
            class="grid grid-cols-12 gap-4"
            :class="{ 'cursor-move': editMode }"
        >
            <div
                v-for="widget in widgets"
                :key="widget.id"
                :class="[
                    `col-span-${widget.width}`,
                    'widget-wrapper',
                    { 'editing': editMode }
                ]"
                :style="{ gridRow: `span ${widget.height}` }"
            >
                <div
                    class="card h-full relative"
                    :data-widget-id="widget.id"
                >
                    <!-- Widget Header -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ widget.title }}</h3>
                        <div class="flex items-center space-x-2">
                            <button
                                v-if="editMode"
                                @click="removeWidget(widget.id)"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <div
                                v-if="editMode"
                                class="cursor-move text-gray-400 hover:text-gray-600"
                                @mousedown="startDrag(widget, $event)"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Widget Content -->
                    <div class="widget-content">
                        <kpi-widget v-if="widget.widget_type === 'kpi'" :widget="widget" />
                        <activity-feed-widget v-else-if="widget.widget_type === 'activity_feed'" :widget="widget" />
                        <pending-tasks-widget v-else-if="widget.widget_type === 'pending_tasks'" :widget="widget" />
                        <calendar-preview-widget v-else-if="widget.widget_type === 'calendar_preview'" :widget="widget" />
                        <campaign-performance-widget v-else-if="widget.widget_type === 'campaign_performance'" :widget="widget" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="widgets.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No widgets</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding a widget to your dashboard.</p>
            <div class="mt-6">
                <button @click="toggleEditMode" class="btn btn-primary">Add Widget</button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Sortable from 'sortablejs';
import KpiWidget from './widgets/KpiWidget.vue';
import ActivityFeedWidget from './widgets/ActivityFeedWidget.vue';
import PendingTasksWidget from './widgets/PendingTasksWidget.vue';
import CalendarPreviewWidget from './widgets/CalendarPreviewWidget.vue';
import CampaignPerformanceWidget from './widgets/CampaignPerformanceWidget.vue';

export default {
    name: 'DashboardComponent',
    components: {
        KpiWidget,
        ActivityFeedWidget,
        PendingTasksWidget,
        CalendarPreviewWidget,
        CampaignPerformanceWidget,
    },
    setup() {
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;
        const widgets = ref([]);
        const editMode = ref(false);
        const gridContainer = ref(null);
        const userName = ref('User');
        let sortableInstance = null;

        const availableWidgetTypes = [
            { type: 'kpi', name: 'KPIs', icon: '📊' },
            { type: 'activity_feed', name: 'Activity Feed', icon: '📝' },
            { type: 'pending_tasks', name: 'Pending Tasks', icon: '✅' },
            { type: 'calendar_preview', name: 'Calendar Preview', icon: '📅' },
            { type: 'campaign_performance', name: 'Campaign Performance', icon: '📈' },
        ];

        const loadWidgets = async () => {
            try {
                const response = await axios.get(`/main/${organizationId}/dashboard/widgets`);
                widgets.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load widgets:', error);
            }
        };

        const loadUserData = async () => {
            try {
                const response = await axios.get('/api/user');
                userName.value = response.data.name || 'User';
            } catch (error) {
                console.error('Failed to load user data:', error);
            }
        };

        const addWidget = async (widgetType) => {
            try {
                const response = await axios.post(`/main/${organizationId}/dashboard/widgets`, {
                    widget_type: widgetType.type,
                    title: widgetType.name,
                    position_x: widgets.value.length % 12,
                    position_y: Math.floor(widgets.value.length / 12),
                    width: widgetType.type === 'kpi' ? 3 : 6,
                    height: 4,
                });
                widgets.value.push(response.data.data);
            } catch (error) {
                console.error('Failed to add widget:', error);
                alert('Failed to add widget. Please try again.');
            }
        };

        const removeWidget = async (widgetId) => {
            if (!confirm('Are you sure you want to remove this widget?')) {
                return;
            }

            try {
                await axios.delete(`/main/${organizationId}/dashboard/widgets/${widgetId}`);
                widgets.value = widgets.value.filter(w => w.id !== widgetId);
            } catch (error) {
                console.error('Failed to remove widget:', error);
                alert('Failed to remove widget. Please try again.');
            }
        };

        const toggleEditMode = () => {
            editMode.value = !editMode.value;
            
            if (editMode.value) {
                initSortable();
            } else {
                destroySortable();
                saveWidgetPositions();
            }
        };

        const initSortable = () => {
            if (!gridContainer.value || sortableInstance) return;

            sortableInstance = Sortable.create(gridContainer.value, {
                animation: 150,
                handle: '.cursor-move',
                onEnd: (evt) => {
                    const widgetId = parseInt(evt.item.dataset.widgetId);
                    const widget = widgets.value.find(w => w.id === widgetId);
                    if (widget) {
                        const newIndex = evt.newIndex;
                        const cols = 12;
                        widget.position_x = newIndex % cols;
                        widget.position_y = Math.floor(newIndex / cols);
                    }
                }
            });
        };

        const destroySortable = () => {
            if (sortableInstance) {
                sortableInstance.destroy();
                sortableInstance = null;
            }
        };

        const saveWidgetPositions = async () => {
            try {
                const positions = widgets.value.map(w => ({
                    id: w.id,
                    position_x: w.position_x,
                    position_y: w.position_y,
                    width: w.width,
                    height: w.height,
                }));

                await axios.put(`/main/${organizationId}/dashboard/widgets/positions`, {
                    widgets: positions,
                });
            } catch (error) {
                console.error('Failed to save widget positions:', error);
            }
        };

        const startDrag = (widget, event) => {
            // Drag handle functionality
        };

        onMounted(() => {
            loadWidgets();
            loadUserData();
        });

        return {
            widgets,
            editMode,
            gridContainer,
            userName,
            availableWidgetTypes,
            toggleEditMode,
            addWidget,
            removeWidget,
            startDrag,
        };
    },
};
</script>

<style scoped>
.widget-wrapper {
    transition: transform 0.2s;
}

.widget-wrapper.editing {
    cursor: move;
}

.widget-wrapper.editing:hover {
    transform: scale(1.02);
    z-index: 10;
}
</style>


