<template>
    <div class="collaboration-container flex h-full">
        <!-- Topic Sidebar -->
        <div class="w-64 border-r border-gray-200 bg-white flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-2">Conversations</h3>
                <button 
                    @click="showCreateTopicModal = true"
                    class="w-full btn btn-primary btn-sm"
                >
                    + New Topic
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                <div 
                    v-for="topic in topics" 
                    :key="topic.id"
                    @click="selectTopic(topic)"
                    class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-primary-50 border-l-4 border-l-primary-500': selectedTopic?.id === topic.id }"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-sm">{{ topic.name }}</span>
                        <span v-if="topic.unread_count > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                            {{ topic.unread_count }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ topic.description || 'No description' }}</p>
                    <p v-if="topic.latest_message" class="text-xs text-gray-400 mt-1 truncate">
                        {{ topic.latest_message.message }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Wall Feed View (default) -->
            <div v-if="!selectedTopic" class="flex-1 overflow-y-auto p-6">
                <!-- Welcome Card -->
                <div class="card mb-6">
                    <h2 class="text-2xl font-bold mb-2">Welcome to Collaboration</h2>
                    <p class="text-gray-600">Select a conversation from the sidebar or create a new topic to start collaborating.</p>
                </div>

                <!-- For Your Review Section -->
                <div class="card mb-6">
                    <h3 class="text-lg font-semibold mb-4">For Your Review</h3>
                    <div v-if="pendingReviews.length === 0" class="text-gray-500 text-center py-8">
                        No pending reviews
                    </div>
                    <div v-else class="space-y-3">
                        <div 
                            v-for="review in pendingReviews" 
                            :key="review.id"
                            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium">{{ review.scheduled_post?.campaign?.name || 'Untitled Campaign' }}</h4>
                                    <p class="text-sm text-gray-600">{{ review.scheduled_post?.channel?.name || 'No channel' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        @click="approveReview(review)"
                                        class="btn btn-success btn-sm"
                                    >
                                        Approve
                                    </button>
                                    <button 
                                        @click="rejectReview(review)"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="pendingReviews.length > 0" class="mt-4">
                        <a :href="reviewPageUrl" class="text-primary-600 hover:underline text-sm">
                            View all reviews →
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                    <div v-if="recentActivity.length === 0" class="text-gray-500 text-center py-8">
                        No recent activity
                    </div>
                    <div v-else class="space-y-3">
                        <div 
                            v-for="activity in recentActivity" 
                            :key="activity.id"
                            class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0"
                        >
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-primary-600 text-xs font-medium">
                                    {{ activity.user?.name?.charAt(0) || 'U' }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm">
                                    <span class="font-medium">{{ activity.user?.name || 'Unknown' }}</span>
                                    <span class="text-gray-600">{{ activity.description }}</span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ formatDate(activity.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Panel (when topic selected) -->
            <div v-else class="flex-1 flex flex-col">
                <div class="p-4 border-b border-gray-200 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">{{ selectedTopic.name }}</h3>
                            <p class="text-sm text-gray-500">{{ selectedTopic.description }}</p>
                        </div>
                        <button 
                            @click="selectedTopic = null"
                            class="btn btn-sm btn-secondary"
                        >
                            Close
                        </button>
                    </div>
                </div>

                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div 
                        v-for="message in messages" 
                        :key="message.id"
                        class="flex gap-3"
                    >
                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-primary-600 text-xs font-medium">
                                {{ message.user?.name?.charAt(0) || 'U' }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-sm">{{ message.user?.name || 'Unknown' }}</span>
                                <span class="text-xs text-gray-400">{{ formatDate(message.created_at) }}</span>
                            </div>
                            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ message.message }}</div>
                            <div v-if="message.attachments && message.attachments.length > 0" class="mt-2 space-y-2">
                                <div 
                                    v-for="(attachment, idx) in message.attachments" 
                                    :key="idx"
                                    class="border border-gray-200 rounded p-2"
                                >
                                    <a :href="attachment.url" target="_blank" class="text-primary-600 hover:underline text-sm">
                                        {{ attachment.name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-white">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Type a message..."
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :disabled="sending"
                        />
                        <button 
                            type="submit"
                            class="btn btn-primary"
                            :disabled="!newMessage.trim() || sending"
                        >
                            {{ sending ? 'Sending...' : 'Send' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Topic Modal -->
        <div 
            v-if="showCreateTopicModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showCreateTopicModal = false"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Create New Topic</h3>
                <form @submit.prevent="createTopic">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input
                            v-model="newTopic.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea
                            v-model="newTopic.description"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            rows="3"
                        ></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Type</label>
                        <select v-model="newTopic.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="channel">Channel</option>
                            <option value="direct">Direct Message</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button 
                            type="button"
                            @click="showCreateTopicModal = false"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="btn btn-primary"
                            :disabled="creating"
                        >
                            {{ creating ? 'Creating...' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'CollaborationComponent',
    props: {
        organizationId: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            topics: [],
            selectedTopic: null,
            messages: [],
            pendingReviews: [],
            recentActivity: [],
            newMessage: '',
            sending: false,
            showCreateTopicModal: false,
            creating: false,
            newTopic: {
                name: '',
                description: '',
                type: 'channel'
            },
            echo: null
        };
    },
    computed: {
        reviewPageUrl() {
            return `/main/${this.organizationId}/review`;
        }
    },
    mounted() {
        this.loadData();
        this.setupBroadcasting();
    },
    beforeUnmount() {
        if (this.echo && this.selectedTopic) {
            this.echo.leave(`chat.topic.${this.selectedTopic.id}`);
        }
        if (this.echo) {
            this.echo.leave(`organization.${this.organizationId}`);
        }
    },
    methods: {
        async loadData() {
            try {
                const response = await axios.get(`/main/${this.organizationId}/collaboration`);
                if (response.data.success) {
                    this.topics = response.data.data.topics || [];
                    this.pendingReviews = response.data.data.pending_reviews || [];
                    this.recentActivity = response.data.data.recent_activity || [];
                }
            } catch (error) {
                console.error('Failed to load collaboration data:', error);
            }
        },
        async selectTopic(topic) {
            this.selectedTopic = topic;
            this.messages = [];
            
            try {
                const response = await axios.get(`/main/${this.organizationId}/collaboration/topics/${topic.id}`);
                if (response.data.success) {
                    this.messages = response.data.data.messages?.data || response.data.data.messages || [];
                    
                    // Mark topic as read
                    const topicIndex = this.topics.findIndex(t => t.id === topic.id);
                    if (topicIndex !== -1) {
                        this.topics[topicIndex].unread_count = 0;
                    }
                    
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            } catch (error) {
                console.error('Failed to load topic messages:', error);
                alert('Failed to load messages. Please try again.');
            }
        },
        async sendMessage() {
            if (!this.newMessage.trim() || !this.selectedTopic) return;

            this.sending = true;
            const messageText = this.newMessage.trim();
            this.newMessage = ''; // Clear input immediately for better UX
            
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/collaboration/topics/${this.selectedTopic.id}/messages`,
                    {
                        message: messageText,
                        attachments: []
                    }
                );
                if (response.data.success) {
                    // Message will be added via broadcasting, but add it immediately for instant feedback
                    const exists = this.messages.some(m => m.id === response.data.data.id);
                    if (!exists) {
                        this.messages.push(response.data.data);
                    }
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            } catch (error) {
                console.error('Failed to send message:', error);
                this.newMessage = messageText; // Restore message on error
                alert(error.response?.data?.message || 'Failed to send message. Please try again.');
            } finally {
                this.sending = false;
            }
        },
        async createTopic() {
            this.creating = true;
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/collaboration/topics`,
                    this.newTopic
                );
                if (response.data.success) {
                    this.topics.unshift(response.data.data);
                    this.showCreateTopicModal = false;
                    this.newTopic = { name: '', description: '', type: 'channel' };
                }
            } catch (error) {
                console.error('Failed to create topic:', error);
                alert('Failed to create topic. Please try again.');
            } finally {
                this.creating = false;
            }
        },
        async approveReview(review) {
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${review.id}/approve`,
                    { comments: '' }
                );
                if (response.data.success) {
                    this.pendingReviews = this.pendingReviews.filter(r => r.id !== review.id);
                }
            } catch (error) {
                console.error('Failed to approve review:', error);
                alert('Failed to approve. Please try again.');
            }
        },
        async rejectReview(review) {
            const reason = prompt('Please provide a rejection reason:');
            if (!reason) return;

            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${review.id}/reject`,
                    { rejection_reason: reason, comments: '' }
                );
                if (response.data.success) {
                    this.pendingReviews = this.pendingReviews.filter(r => r.id !== review.id);
                }
            } catch (error) {
                console.error('Failed to reject review:', error);
                alert('Failed to reject. Please try again.');
            }
        },
        setupBroadcasting() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo is not initialized. Real-time features will not work.');
                return;
            }

            this.echo = window.Echo;
            
            // Join organization channel for presence
            const organizationChannel = this.echo.join(`organization.${this.organizationId}`);
            
            // Listen for new messages in the organization
            organizationChannel.listen('.message.sent', (data) => {
                if (this.selectedTopic && data.chat_topic_id === this.selectedTopic.id) {
                    // Check if message already exists to avoid duplicates
                    const exists = this.messages.some(m => m.id === data.id);
                    if (!exists) {
                        this.messages.push(data);
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                } else {
                    // Update unread count for the topic
                    const topic = this.topics.find(t => t.id === data.chat_topic_id);
                    if (topic) {
                        topic.unread_count = (topic.unread_count || 0) + 1;
                    }
                }
            });

            // Listen for new topics
            organizationChannel.listen('.topic.created', (data) => {
                this.loadData();
            });

            // Listen for approval status changes
            organizationChannel.listen('.approval.status.changed', () => {
                this.loadData();
            });

            // Also listen to specific topic channel for better performance
            this.$watch('selectedTopic', (newTopic, oldTopic) => {
                if (oldTopic) {
                    // Leave old topic channel
                    this.echo.leave(`chat.topic.${oldTopic.id}`);
                }
                
                if (newTopic) {
                    // Join new topic channel
                    const topicChannel = this.echo.join(`chat.topic.${newTopic.id}`);
                    
                    topicChannel.listen('.message.sent', (data) => {
                        const exists = this.messages.some(m => m.id === data.id);
                        if (!exists) {
                            this.messages.push(data);
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        }
                    });
                }
            }, { immediate: true });
        },
        scrollToBottom() {
            if (this.$refs.messagesContainer) {
                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
            }
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            
            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (minutes < 1440) return `${Math.floor(minutes / 60)}h ago`;
            return date.toLocaleDateString();
        }
    }
};
</script>

<style scoped>
.collaboration-container {
    height: calc(100vh - 200px);
}

.card {
    @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.btn {
    @apply px-4 py-2 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
    @apply bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500;
}

.btn-secondary {
    @apply bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-500;
}

.btn-success {
    @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
}

.btn-danger {
    @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
}

.btn-sm {
    @apply px-3 py-1.5 text-sm;
}
</style>

