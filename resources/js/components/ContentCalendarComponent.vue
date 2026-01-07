<template>
    <div class="content-calendar">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Content Calendar</h2>
                <p class="text-gray-600 mt-1">Manage and schedule your content</p>
            </div>
            <div class="flex items-center space-x-4">
                <button @click="showCreateModal = true" class="btn btn-primary">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Schedule Post
                </button>
            </div>
        </div>

        <!-- Calendar View Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <nav class="flex space-x-8">
                <button
                    @click="currentView = 'dayGridMonth'"
                    :class="[
                        'py-4 px-1 border-b-2 font-medium text-sm',
                        currentView === 'dayGridMonth' 
                            ? 'border-primary-500 text-primary-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    Month
                </button>
                <button
                    @click="currentView = 'timeGridWeek'"
                    :class="[
                        'py-4 px-1 border-b-2 font-medium text-sm',
                        currentView === 'timeGridWeek' 
                            ? 'border-primary-500 text-primary-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    Week
                </button>
                <button
                    @click="currentView = 'timeGridDay'"
                    :class="[
                        'py-4 px-1 border-b-2 font-medium text-sm',
                        currentView === 'timeGridDay' 
                            ? 'border-primary-500 text-primary-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    Day
                </button>
                <button
                    @click="currentView = 'listWeek'"
                    :class="[
                        'py-4 px-1 border-b-2 font-medium text-sm',
                        currentView === 'listWeek' 
                            ? 'border-primary-500 text-primary-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    List
                </button>
            </nav>
        </div>

        <!-- FullCalendar -->
        <div class="card p-4">
            <div ref="calendarEl"></div>
        </div>

        <!-- Create Event Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showCreateModal = false">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Schedule New Post</h3>
                <form @submit.prevent="createEvent">
                    <div class="space-y-4">
                        <div>
                            <label class="label">Title</label>
                            <input v-model="newEvent.title" type="text" class="input" required>
                        </div>
                        <div>
                            <label class="label">Content</label>
                            <textarea v-model="newEvent.content" class="input" rows="4" required></textarea>
                        </div>
                        <div>
                            <label class="label">Channel</label>
                            <select v-model="newEvent.channel_id" class="input" required>
                                <option value="">Select Channel</option>
                                <option v-for="channel in channels" :key="channel.id" :value="channel.id">
                                    {{ channel.display_name }} ({{ channel.platform }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Scheduled Date & Time</label>
                            <input v-model="newEvent.scheduled_at" type="datetime-local" class="input" required>
                        </div>
                        <div>
                            <label class="label">Campaign (Optional)</label>
                            <select v-model="newEvent.campaign_id" class="input">
                                <option value="">None</option>
                                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                                    {{ campaign.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showCreateModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import axios from 'axios';

export default {
    name: 'ContentCalendarComponent',
    props: {
        organizationId: {
            type: [String, Number],
            required: true,
        },
    },
    setup(props) {
        const calendarEl = ref(null);
        const calendar = ref(null);
        const currentView = ref('dayGridMonth');
        const showCreateModal = ref(false);
        const channels = ref([]);
        const campaigns = ref([]);
        const events = ref([]);

        const newEvent = ref({
            title: '',
            content: '',
            channel_id: '',
            campaign_id: '',
            scheduled_at: '',
        });

        const loadChannels = async () => {
            try {
                const response = await axios.get(`/main/${props.organizationId}/channels`);
                channels.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load channels:', error);
            }
        };

        const loadCampaigns = async () => {
            try {
                const response = await axios.get(`/main/${props.organizationId}/campaigns`);
                campaigns.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load campaigns:', error);
            }
        };

        const loadEvents = async (start, end) => {
            try {
                const response = await axios.get(`/main/${props.organizationId}/content-calendar`, {
                    params: {
                        start: start.toISOString(),
                        end: end.toISOString(),
                    },
                });
                events.value = response.data.data || [];
                
                if (calendar.value) {
                    calendar.value.removeAllEvents();
                    calendar.value.addEventSource(events.value.map(event => ({
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        allDay: event.allDay,
                        backgroundColor: getEventColor(event.type),
                        borderColor: getEventColor(event.type),
                        extendedProps: event,
                    })));
                }
            } catch (error) {
                console.error('Failed to load events:', error);
            }
        };

        const getEventColor = (type) => {
            const colors = {
                post: '#3b82f6',
                campaign: '#8b5cf6',
                meeting: '#10b981',
                deadline: '#ef4444',
                reminder: '#f59e0b',
                custom: '#6b7280',
            };
            return colors[type] || '#6b7280';
        };

        const initCalendar = () => {
            if (!calendarEl.value) return;

            calendar.value = new Calendar(calendarEl.value, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
                initialView: currentView.value,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                },
                editable: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                select: (arg) => {
                    newEvent.value.scheduled_at = arg.startStr;
                    showCreateModal.value = true;
                    calendar.value.unselect();
                },
                eventClick: (arg) => {
                    // Handle event click - could show details modal
                    console.log('Event clicked:', arg.event);
                },
                eventDrop: async (arg) => {
                    // Handle drag and drop
                    try {
                        const eventId = arg.event.id;
                        if (eventId.toString().startsWith('post_')) {
                            const postId = eventId.toString().replace('post_', '');
                            await axios.put(`/main/${props.organizationId}/content-calendar/${postId}`, {
                                scheduled_at: arg.event.start.toISOString(),
                            });
                        } else {
                            await axios.put(`/main/${props.organizationId}/content-calendar/${eventId}`, {
                                start_time: arg.event.start.toISOString(),
                                end_time: arg.event.end?.toISOString(),
                            });
                        }
                    } catch (error) {
                        console.error('Failed to update event:', error);
                        arg.revert();
                    }
                },
                events: (info, successCallback, failureCallback) => {
                    loadEvents(info.start, info.end).then(() => {
                        successCallback(events.value.map(event => ({
                            id: event.id,
                            title: event.title,
                            start: event.start,
                            end: event.end,
                            allDay: event.allDay,
                            backgroundColor: getEventColor(event.type),
                            borderColor: getEventColor(event.type),
                            extendedProps: event,
                        })));
                    }).catch(failureCallback);
                },
            });

            calendar.value.render();
        };

        const createEvent = async () => {
            try {
                await axios.post(`/main/${props.organizationId}/content-calendar/bulk-schedule`, {
                    posts: [{
                        channel_id: newEvent.value.channel_id,
                        content: newEvent.value.content,
                        scheduled_at: newEvent.value.scheduled_at,
                        campaign_id: newEvent.value.campaign_id || null,
                    }],
                });

                // Reset form
                newEvent.value = {
                    title: '',
                    content: '',
                    channel_id: '',
                    campaign_id: '',
                    scheduled_at: '',
                };
                showCreateModal.value = false;

                // Reload calendar
                if (calendar.value) {
                    const view = calendar.value.view;
                    loadEvents(view.activeStart, view.activeEnd);
                }
            } catch (error) {
                console.error('Failed to create event:', error);
                alert('Failed to schedule post. Please try again.');
            }
        };

        watch(currentView, (newView) => {
            if (calendar.value) {
                calendar.value.changeView(newView);
            }
        });

        onMounted(async () => {
            await loadChannels();
            await loadCampaigns();
            await nextTick();
            initCalendar();
        });

        return {
            calendarEl,
            currentView,
            showCreateModal,
            channels,
            campaigns,
            newEvent,
            createEvent,
        };
    },
};
</script>

<style>
@import '@fullcalendar/core/main.css';
@import '@fullcalendar/daygrid/main.css';
@import '@fullcalendar/timegrid/main.css';
@import '@fullcalendar/list/main.css';
</style>


