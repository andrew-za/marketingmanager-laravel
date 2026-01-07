@extends('layouts.app')

@section('page-title', 'Chatbot Deployment')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="chatbotDeployment()" x-init="init()">
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
                    <h1 class="text-2xl font-semibold text-gray-900" x-text="chatbot?.name || 'Chatbot Deployment'"></h1>
                    <p class="mt-1 text-sm text-gray-600">Deploy your chatbot to websites and manage integrations</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button
                    @click="generateEmbedCode"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                >
                    Generate Embed Code
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Deployment Methods -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">Deployment Methods</h2>

                <div class="space-y-6">
                    <!-- Website Embed -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 9c-1.657 0-3-4.03-3-9s1.343-9 3-9m0 18c1.657 0 3-4.03 3-9s-1.343-9-3-9" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Website Embed</h3>
                                    <p class="text-xs text-gray-500">Add chatbot to any website</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Recommended
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Embed Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Embed Code</label>
                                <div class="relative">
                                    <textarea
                                        x-model="embedCode"
                                        readonly
                                        rows="6"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 font-mono text-sm"
                                        placeholder="Embed code will appear here..."
                                    ></textarea>
                                    <button
                                        @click="copyEmbedCode"
                                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                                        title="Copy to clipboard"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Copy and paste this code into your website's &lt;head&gt; tag or before the closing &lt;/body&gt; tag.
                                </p>
                            </div>

                            <!-- Position Settings -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                    <select x-model="embedSettings.position" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="bottom-right">Bottom Right</option>
                                        <option value="bottom-left">Bottom Left</option>
                                        <option value="top-right">Top Right</option>
                                        <option value="top-left">Top Left</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                    <select x-model="embedSettings.size" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="small">Small</option>
                                        <option value="medium">Medium</option>
                                        <option value="large">Large</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Integration -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">API Integration</h3>
                                <p class="text-xs text-gray-500">Integrate with your backend systems</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- API Key -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="password"
                                        x-model="apiKey"
                                        readonly
                                        class="flex-1 rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        placeholder="api-key-here"
                                    >
                                    <button
                                        @click="regenerateApiKey"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                                    >
                                        Regenerate
                                    </button>
                                </div>
                            </div>

                            <!-- Webhook URL -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                                <input
                                    type="url"
                                    x-model="webhookUrl"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="https://your-app.com/webhooks/chatbot"
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    Receive notifications when leads are captured or conversations end.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- WordPress Plugin -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">WordPress Plugin</h3>
                                <p class="text-xs text-gray-500">Easy integration for WordPress sites</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-3">
                                    Download our WordPress plugin for seamless integration with your WordPress website.
                                </p>
                                <button class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-sm">
                                    Download WordPress Plugin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deployment Status & Analytics -->
        <div class="lg:col-span-1">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Deployment Status</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        <span
                            :class="chatbot?.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            x-text="chatbot?.is_active ? 'Active' : 'Inactive'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm text-gray-900" x-text="formatDate(chatbot?.updated_at)"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Deployments</span>
                        <span class="text-sm font-medium text-gray-900" x-text="deploymentCount || 0"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Stats</h3>

                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Conversations</span>
                            <span class="text-sm font-medium text-gray-900" x-text="stats?.conversations || 0"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Leads Captured</span>
                            <span class="text-sm font-medium text-gray-900" x-text="stats?.leads || 0"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 60%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Avg. Response Time</span>
                            <span class="text-sm font-medium text-gray-900" x-text="stats?.avg_response_time || 'N/A'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-yellow-600 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Deployments -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Deployments</h3>

                <div class="space-y-3" x-show="recentDeployments.length > 0">
                    <template x-for="deployment in recentDeployments" :key="deployment.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="deployment.website"></p>
                                <p class="text-xs text-gray-500" x-text="formatDate(deployment.created_at)"></p>
                            </div>
                            <span
                                :class="deployment.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                x-text="deployment.status"
                            ></span>
                        </div>
                    </template>
                </div>

                <div x-show="recentDeployments.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No deployments yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Chatbot Modal -->
    <div
        x-show="showTestModal"
        @click.away="showTestModal = false"
        x-transition
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none;"
    >
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Test Your Chatbot</h3>

                <div class="bg-gray-100 rounded-lg p-4 mb-4">
                    <div id="test-chat-messages" class="space-y-3 max-h-64 overflow-y-auto mb-4">
                        <div class="flex justify-start">
                            <div class="bg-white rounded-lg p-3 max-w-xs shadow-sm">
                                <p class="text-sm" x-text="chatbot?.initial_message || 'Hi! How can I help you today?'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <input
                            type="text"
                            id="test-message-input"
                            @keypress.enter="sendTestMessage"
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            placeholder="Type a test message..."
                        >
                        <button
                            @click="sendTestMessage"
                            class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700"
                        >
                            Send
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        @click="showTestModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chatbotDeployment() {
    return {
        chatbot: null,
        embedCode: '',
        embedSettings: {
            position: 'bottom-right',
            size: 'medium'
        },
        apiKey: '',
        webhookUrl: '',
        deploymentCount: 0,
        stats: {},
        recentDeployments: [],
        showTestModal: false,

        init() {
            this.loadChatbot();
            this.generateEmbedCode();
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
                            this.stats = data.data.stats || {};
                            this.deploymentCount = data.data.deployments_count || 0;
                            this.recentDeployments = data.data.recent_deployments || [];
                        }
                    });
            }
        },

        generateEmbedCode() {
            if (!this.chatbot) return;

            const baseUrl = window.location.origin;
            this.embedCode = `<script>
  (function() {
    var script = document.createElement('script');
    script.src = '${baseUrl}/js/chatbot-widget.js';
    script.async = true;
    script.onload = function() {
      window.ChatbotWidget.init({
        chatbotId: '${this.chatbot.id}',
        position: '${this.embedSettings.position}',
        size: '${this.embedSettings.size}',
        primaryColor: '${this.chatbot.primary_color || '#3B82F6'}'
      });
    };
    document.head.appendChild(script);
  })();
</script>`;
        },

        copyEmbedCode() {
            navigator.clipboard.writeText(this.embedCode).then(() => {
                // Show success message
                alert('Embed code copied to clipboard!');
            });
        },

        async regenerateApiKey() {
            if (!confirm('Are you sure you want to regenerate the API key? This will break any existing integrations.')) {
                return;
            }

            try {
                const response = await fetch(`{{ route('chatbots.update', '') }}/${this.chatbot?.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        regenerate_api_key: true
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.apiKey = data.data.api_key;
                    alert('API key regenerated successfully!');
                } else {
                    alert(data.message || 'Failed to regenerate API key');
                }
            } catch (error) {
                console.error('API key regeneration error:', error);
                alert('Failed to regenerate API key');
            }
        },

        sendTestMessage() {
            const input = document.getElementById('test-message-input');
            const message = input.value.trim();
            if (!message) return;

            const messagesDiv = document.getElementById('test-chat-messages');

            // Add user message
            messagesDiv.innerHTML += `
                <div class="flex justify-end">
                    <div class="bg-blue-600 text-white rounded-lg p-3 max-w-xs shadow-sm">
                        <p class="text-sm">${message}</p>
                    </div>
                </div>
            `;

            // Simulate bot response
            setTimeout(() => {
                messagesDiv.innerHTML += `
                    <div class="flex justify-start">
                        <div class="bg-white rounded-lg p-3 max-w-xs shadow-sm">
                            <p class="text-sm">Thanks for your test message! This is a simulated response.</p>
                        </div>
                    </div>
                `;
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }, 1000);

            input.value = '';
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        },

        formatDate(dateString) {
            if (!dateString) return 'Never';
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }
    }
}
</script>
@endsection
