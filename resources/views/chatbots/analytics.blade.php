@extends('layouts.app')

@section('page-title', 'Chatbot Analytics')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="chatbotAnalytics()" x-init="init()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button
                    onclick="window.history.back()"
                    class="text-gray-600 hover:text-gray-900 focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900" x-text="chatbot?.name || 'Chatbot Analytics'"></h1>
                    <p class="mt-1 text-sm text-gray-600">Monitor your chatbot performance and user interactions</p>
                </div>
            </div>

            <!-- Date Range Picker -->
            <div class="flex items-center space-x-3">
                <select x-model="dateRange" @change="updateDateRange" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="7d">Last 7 days</option>
                    <option value="30d">Last 30 days</option>
                    <option value="90d">Last 90 days</option>
                    <option value="custom">Custom range</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Conversations -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Conversations</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="metrics.total_conversations || 0"></p>
                    <p class="text-sm text-green-600" x-show="metrics.conversations_change">
                        <span x-text="metrics.conversations_change > 0 ? '+' : ''"></span>
                        <span x-text="metrics.conversations_change"></span>% from last period
                    </p>
                </div>
            </div>
        </div>

        <!-- Leads Captured -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Leads Captured</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="metrics.total_leads || 0"></p>
                    <p class="text-sm text-green-600" x-show="metrics.leads_change">
                        <span x-text="metrics.leads_change > 0 ? '+' : ''"></span>
                        <span x-text="metrics.leads_change"></span>% from last period
                    </p>
                </div>
            </div>
        </div>

        <!-- Average Response Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Avg Response Time</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="formatDuration(metrics.avg_response_time)"></p>
                    <p class="text-sm text-red-600" x-show="metrics.response_time_change">
                        <span x-text="metrics.response_time_change > 0 ? '+' : ''"></span>
                        <span x-text="metrics.response_time_change"></span>% slower
                    </p>
                </div>
            </div>
        </div>

        <!-- Satisfaction Rate -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Satisfaction Rate</p>
                    <p class="text-2xl font-semibold text-gray-900" x-text="(metrics.satisfaction_rate || 0) + '%'"></p>
                    <p class="text-sm text-green-600" x-show="metrics.satisfaction_change">
                        <span x-text="metrics.satisfaction_change > 0 ? '+' : ''"></span>
                        <span x-text="metrics.satisfaction_change"></span>% from last period
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Conversations Over Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Conversations Over Time</h3>
            <div class="h-64">
                <canvas id="conversationsChart"></canvas>
            </div>
        </div>

        <!-- Top Pages -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Most Active Pages</h3>
            <div class="space-y-3">
                <template x-for="(page, index) in topPages" :key="index">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="page.url"></p>
                            <p class="text-xs text-gray-500" x-text="page.conversations + ' conversations'"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    :style="'width: ' + (page.conversations / maxConversations * 100) + '%'"
                                ></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversation Flow -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Conversation Flow Analysis</h3>

                <div class="space-y-4">
                    <!-- Flow Steps -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(step, index) in flowSteps" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900" x-text="step.name"></h4>
                                    <span class="text-xs text-gray-500" x-text="step.percentage + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="bg-blue-600 h-2 rounded-full"
                                        :style="'width: ' + step.percentage + '%'"
                                    ></div>
                                </div>
                                <p class="text-xs text-gray-600 mt-2" x-text="step.conversations + ' conversations'"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Drop-off Points -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Drop-off Points</h4>
                        <div class="space-y-2">
                            <template x-for="(point, index) in dropOffPoints" :key="index">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600" x-text="point.step"></span>
                                    <span class="text-red-600 font-medium" x-text="point.drop_off + '% dropped off'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Conversations -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Conversations</h3>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <template x-for="conversation in recentConversations" :key="conversation.id">
                        <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900" x-text="conversation.visitor_name || 'Anonymous'"></span>
                                <span class="text-xs text-gray-500" x-text="formatDate(conversation.created_at)"></span>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2" x-text="conversation.last_message"></p>
                            <div class="flex items-center justify-between mt-2">
                                <span
                                    :class="conversation.status === 'completed' ? 'bg-green-100 text-green-800' : conversation.status === 'active' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                    x-text="conversation.status"
                                ></span>
                                <span class="text-xs text-gray-500" x-text="conversation.message_count + ' messages'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="recentConversations.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No conversations yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Captured Leads</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Captured</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="lead in leads" :key="lead.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="lead.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.phone || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(lead.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="lead.status === 'new' ? 'bg-blue-100 text-blue-800' : lead.status === 'contacted' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    x-text="lead.status"
                                ></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="leads.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No leads captured</h3>
            <p class="mt-1 text-sm text-gray-500">Leads will appear here when visitors provide their contact information</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function chatbotAnalytics() {
    return {
        chatbot: null,
        dateRange: '30d',
        metrics: {},
        topPages: [],
        flowSteps: [],
        dropOffPoints: [],
        recentConversations: [],
        leads: [],
        conversationsChart: null,

        init() {
            this.loadChatbot();
            this.loadAnalytics();
        },

        loadChatbot() {
            const urlParams = new URLSearchParams(window.location.search);
            const chatbotId = urlParams.get('id');

            if (chatbotId) {
                fetch(`{{ route('chatbots.show', '') }}/${chatbotId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.chatbot = data.data;
                        }
                    });
            }
        },

        loadAnalytics() {
            const urlParams = new URLSearchParams(window.location.search);
            const chatbotId = urlParams.get('id');

            if (!chatbotId) return;

            const params = new URLSearchParams({
                start_date: this.getStartDate(),
                end_date: this.getEndDate()
            });

            fetch(`{{ route('chatbots.analytics', '') }}/${chatbotId}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.metrics = data.data.metrics || {};
                        this.topPages = data.data.top_pages || [];
                        this.flowSteps = data.data.flow_steps || [];
                        this.dropOffPoints = data.data.drop_off_points || [];
                        this.recentConversations = data.data.recent_conversations || [];
                        this.leads = data.data.leads || [];

                        // Calculate max conversations for progress bars
                        this.maxConversations = Math.max(...this.topPages.map(p => p.conversations));

                        this.renderCharts();
                    }
                });
        },

        getStartDate() {
            const now = new Date();
            switch (this.dateRange) {
                case '7d':
                    return new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                case '30d':
                    return new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                case '90d':
                    return new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                default:
                    return new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            }
        },

        getEndDate() {
            return new Date().toISOString().split('T')[0];
        },

        updateDateRange() {
            this.loadAnalytics();
        },

        renderCharts() {
            const ctx = document.getElementById('conversationsChart');
            if (ctx && this.metrics.conversations_over_time) {
                if (this.conversationsChart) {
                    this.conversationsChart.destroy();
                }

                this.conversationsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.metrics.conversations_over_time.map(d => d.date),
                        datasets: [{
                            label: 'Conversations',
                            data: this.metrics.conversations_over_time.map(d => d.count),
                            borderColor: '#3B82F6',
                            backgroundColor: '#3B82F640',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },

        formatDuration(seconds) {
            if (!seconds) return 'N/A';
            if (seconds < 60) return `${Math.round(seconds)}s`;
            if (seconds < 3600) return `${Math.round(seconds / 60)}m`;
            return `${Math.round(seconds / 3600)}h`;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }
    }
}
</script>
@endsection
