@extends('layouts.app')

@section('page-title', 'Chatbot Builder')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="chatbotBuilder()" style="height: calc(100vh - 200px);">
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
                    <h1 class="text-2xl font-semibold text-gray-900" x-text="chatbot?.name || 'New Chatbot'"></h1>
                    <p class="mt-1 text-sm text-gray-600">Design your AI-powered chatbot conversation flow</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button
                    @click="saveChatbot"
                    :disabled="saving"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <span x-show="!saving">Save Chatbot</span>
                    <span x-show="saving">Saving...</span>
                </button>

                <button
                    @click="testChatbot"
                    :disabled="testing"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <span x-show="!testing">Test Chatbot</span>
                    <span x-show="testing">Testing...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Builder Interface -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="height: calc(100% - 100px);">
        <div class="flex h-full">
            <!-- Sidebar -->
            <div class="w-80 bg-gray-50 border-r border-gray-200 p-4 overflow-y-auto">
                <!-- Tabs -->
                <div class="mb-6">
                    <nav class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                        <button
                            @click="activeTab = 'flow'"
                            :class="activeTab === 'flow' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors"
                        >
                            Conversation Flow
                        </button>
                        <button
                            @click="activeTab = 'settings'"
                            :class="activeTab === 'settings' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors"
                        >
                            Settings
                        </button>
                    </nav>
                </div>

                <!-- Conversation Flow Tab -->
                <div x-show="activeTab === 'flow'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Message Types</h3>
                        <div class="space-y-3">
                            <div
                                draggable="true"
                                @dragstart="onDragStart('message', 'text')"
                                class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">Text Message</h5>
                                        <p class="text-xs text-gray-500">Send a text response</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                draggable="true"
                                @dragstart="onDragStart('message', 'quick_replies')"
                                class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">Quick Replies</h5>
                                        <p class="text-xs text-gray-500">Show button options</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                draggable="true"
                                @dragstart="onDragStart('message', 'input')"
                                class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">User Input</h5>
                                        <p class="text-xs text-gray-500">Collect user information</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                draggable="true"
                                @dragstart="onDragStart('message', 'condition')"
                                class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">Condition</h5>
                                        <p class="text-xs text-gray-500">Branch conversation</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>

                        <div class="space-y-4">
                            <!-- Initial Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Message</label>
                                <textarea
                                    x-model="chatbotSettings.initial_message"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Hi! How can I help you today?"
                                ></textarea>
                            </div>

                            <!-- Primary Color -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                                <input
                                    type="color"
                                    x-model="chatbotSettings.primary_color"
                                    class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            <!-- Fallback Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fallback Message</label>
                                <textarea
                                    x-model="chatbotSettings.fallback_message"
                                    rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="I'm sorry, I didn't understand that. Can you try rephrasing?"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Lead Capture</h3>

                        <div class="space-y-4">
                            <!-- Collect Email -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    x-model="chatbotSettings.collect_email"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label class="ml-2 block text-sm text-gray-900">
                                    Collect email addresses
                                </label>
                            </div>

                            <!-- Collect Phone -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    x-model="chatbotSettings.collect_phone"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label class="ml-2 block text-sm text-gray-900">
                                    Collect phone numbers
                                </label>
                            </div>

                            <!-- Lead Email -->
                            <div x-show="chatbotSettings.collect_email || chatbotSettings.collect_phone">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Send leads to</label>
                                <input
                                    type="email"
                                    x-model="chatbotSettings.lead_email"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="leads@yourcompany.com"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="flex-1 relative overflow-hidden">
                <!-- Canvas Header -->
                <div class="bg-white border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Conversation Flow</h3>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="zoomIn"
                                class="p-2 text-gray-400 hover:text-gray-600 border border-gray-200 rounded"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <button
                                @click="zoomOut"
                                class="p-2 text-gray-400 hover:text-gray-600 border border-gray-200 rounded"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="text-sm text-gray-500" x-text="`Zoom: ${zoom}%`"></span>
                        </div>
                    </div>
                </div>

                <!-- Canvas Area -->
                <div
                    id="canvas"
                    class="relative w-full h-full overflow-auto bg-gray-50"
                    @drop="onDrop"
                    @dragover.prevent
                >
                    <div
                        id="canvas-content"
                        class="relative min-h-full min-w-full p-8"
                        :style="`transform: scale(${zoom / 100}); transform-origin: top left;`"
                    >
                        <!-- Start Node -->
                        <div class="absolute top-8 left-1/2 transform -translate-x-1/2">
                            <div class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg">
                                Start Conversation
                            </div>
                        </div>

                        <!-- Dynamic nodes will be added here -->
                        <div id="flow-nodes" class="relative">
                            <!-- Flow nodes will be rendered here -->
                        </div>

                        <!-- End Node -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                            <div class="bg-red-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg">
                                End Conversation
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Panel -->
            <div class="w-80 bg-white border-l border-gray-200 p-4 overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Properties</h3>

                <div x-show="!selectedNode" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Select a message to edit its properties</p>
                </div>

                <!-- Node Properties Form -->
                <div x-show="selectedNode" class="space-y-4">
                    <!-- Text Message -->
                    <template x-if="selectedNode.type === 'text'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message Text</label>
                            <textarea
                                x-model="selectedNode.content"
                                rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter your message..."
                            ></textarea>
                        </div>
                    </template>

                    <!-- Quick Replies -->
                    <template x-if="selectedNode.type === 'quick_replies'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message Text</label>
                            <textarea
                                x-model="selectedNode.content"
                                rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter your message..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quick Reply Options</label>
                            <div class="space-y-2">
                                <template x-for="(reply, index) in selectedNode.replies" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="text"
                                            :x-model="selectedNode.replies[index]"
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            placeholder="Reply option..."
                                        >
                                        <button
                                            @click="removeQuickReply(index)"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button
                                @click="addQuickReply"
                                class="mt-2 text-sm text-blue-600 hover:text-blue-800"
                            >
                                + Add reply option
                            </button>
                        </div>
                    </template>

                    <!-- User Input -->
                    <template x-if="selectedNode.type === 'input'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Input Label</label>
                            <input
                                type="text"
                                x-model="selectedNode.label"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., What's your email address?"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Input Type</label>
                            <select
                                x-model="selectedNode.input_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="number">Number</option>
                            </select>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                x-model="selectedNode.required"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label class="ml-2 block text-sm text-gray-900">
                                Required field
                            </label>
                        </div>
                    </template>

                    <!-- Condition -->
                    <template x-if="selectedNode.type === 'condition'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                            <select
                                x-model="selectedNode.condition"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="contains">Contains text</option>
                                <option value="equals">Equals</option>
                                <option value="starts_with">Starts with</option>
                                <option value="ends_with">Ends with</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                            <input
                                type="text"
                                x-model="selectedNode.condition_value"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Value to check for..."
                            >
                        </div>
                    </template>

                    <!-- Delete Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button
                            @click="deleteNode"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Delete Message
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chatbotBuilder() {
    return {
        chatbot: null,
        nodes: [],
        selectedNode: null,
        zoom: 100,
        activeTab: 'flow',
        saving: false,
        testing: false,
        chatbotSettings: {
            initial_message: 'Hi! How can I help you today?',
            primary_color: '#3B82F6',
            fallback_message: "I'm sorry, I didn't understand that. Can you try rephrasing?",
            collect_email: false,
            collect_phone: false,
            lead_email: ''
        },

        init() {
            // Load chatbot data if editing
            this.loadChatbot();
            this.setupCanvas();
        },

        loadChatbot() {
            // Load chatbot data from URL parameter or create new
            const urlParams = new URLSearchParams(window.location.search);
            const chatbotId = urlParams.get('id');

            if (chatbotId) {
                fetch(`{{ route('chatbots.show', '') }}/${chatbotId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.chatbot = data.data;
                            this.nodes = data.data.flow || [];
                            this.chatbotSettings = { ...this.chatbotSettings, ...data.data.settings };
                            this.renderNodes();
                        }
                    });
            }
        },

        setupCanvas() {
            // Make canvas pannable and zoomable
            const canvas = document.getElementById('canvas');
            let isDragging = false;
            let startX, startY, scrollLeft, scrollTop;

            canvas.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.pageX - canvas.offsetLeft;
                startY = e.pageY - canvas.offsetTop;
                scrollLeft = canvas.scrollLeft;
                scrollTop = canvas.scrollTop;
            });

            canvas.addEventListener('mouseleave', () => {
                isDragging = false;
            });

            canvas.addEventListener('mouseup', () => {
                isDragging = false;
            });

            canvas.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - canvas.offsetLeft;
                const y = e.pageY - canvas.offsetTop;
                const walkX = (x - startX) * 2;
                const walkY = (y - startY) * 2;
                canvas.scrollLeft = scrollLeft - walkX;
                canvas.scrollTop = scrollTop - walkY;
            });
        },

        onDragStart(event, type, subtype) {
            event.dataTransfer.setData('application/json', JSON.stringify({
                type: type,
                subtype: subtype,
                id: Date.now()
            }));
        },

        onDrop(event) {
            event.preventDefault();

            try {
                const data = JSON.parse(event.dataTransfer.getData('application/json'));
                const rect = event.currentTarget.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                const node = {
                    id: data.id,
                    type: data.subtype,
                    x: x,
                    y: y,
                    ...this.getDefaultNodeConfig(data.subtype)
                };

                this.nodes.push(node);
                this.renderNodes();
            } catch (error) {
                console.error('Error handling drop:', error);
            }
        },

        getDefaultNodeConfig(type) {
            const configs = {
                text: { content: 'Enter your message here...' },
                quick_replies: { content: 'Choose an option:', replies: ['Option 1', 'Option 2'] },
                input: { label: 'Please provide your information:', input_type: 'text', required: false },
                condition: { condition: 'contains', condition_value: '' }
            };
            return configs[type] || {};
        },

        renderNodes() {
            const container = document.getElementById('flow-nodes');
            container.innerHTML = '';

            this.nodes.forEach(node => {
                const nodeElement = document.createElement('div');
                nodeElement.className = 'absolute cursor-pointer';
                nodeElement.style.left = `${node.x}px`;
                nodeElement.style.top = `${node.y}px`;
                nodeElement.onclick = () => this.selectNode(node);

                const colors = {
                    text: 'bg-blue-500',
                    quick_replies: 'bg-green-500',
                    input: 'bg-yellow-500',
                    condition: 'bg-orange-500'
                };

                const labels = {
                    text: 'Text',
                    quick_replies: 'Quick Replies',
                    input: 'Input',
                    condition: 'Condition'
                };

                nodeElement.innerHTML = `
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4 shadow-lg hover:border-blue-400 transition-colors min-w-48">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-4 h-4 ${colors[node.type]} rounded"></div>
                            <span class="text-xs font-medium text-gray-500 uppercase">${labels[node.type]}</span>
                        </div>
                        <div class="text-sm text-gray-900 line-clamp-2">
                            ${this.getNodePreview(node)}
                        </div>
                    </div>
                `;

                container.appendChild(nodeElement);
            });
        },

        getNodePreview(node) {
            switch (node.type) {
                case 'text':
                    return node.content || 'Enter message...';
                case 'quick_replies':
                    return node.content || 'Choose option...';
                case 'input':
                    return node.label || 'Enter information...';
                case 'condition':
                    return `${node.condition} "${node.condition_value}"`;
                default:
                    return 'Configure message...';
            }
        },

        selectNode(node) {
            this.selectedNode = node;
        },

        addQuickReply() {
            if (!this.selectedNode.replies) {
                this.selectedNode.replies = [];
            }
            this.selectedNode.replies.push('');
            this.renderNodes();
        },

        removeQuickReply(index) {
            this.selectedNode.replies.splice(index, 1);
            this.renderNodes();
        },

        deleteNode() {
            if (this.selectedNode) {
                this.nodes = this.nodes.filter(node => node.id !== this.selectedNode.id);
                this.selectedNode = null;
                this.renderNodes();
            }
        },

        zoomIn() {
            if (this.zoom < 200) {
                this.zoom += 25;
            }
        },

        zoomOut() {
            if (this.zoom > 25) {
                this.zoom -= 25;
            }
        },

        async saveChatbot() {
            this.saving = true;

            try {
                const chatbotData = {
                    name: this.chatbot?.name || 'New Chatbot',
                    description: this.chatbot?.description || '',
                    initial_message: this.chatbotSettings.initial_message,
                    primary_color: this.chatbotSettings.primary_color,
                    fallback_message: this.chatbotSettings.fallback_message,
                    settings: this.chatbotSettings,
                    flow: this.nodes
                };

                const url = this.chatbot
                    ? `{{ route('chatbots.update', '') }}/${this.chatbot.id}`
                    : '{{ route('chatbots.store') }}';

                const method = this.chatbot ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(chatbotData)
                });

                const data = await response.json();

                if (data.success) {
                    if (!this.chatbot) {
                        this.chatbot = data.data;
                        // Update URL to include chatbot ID
                        window.history.replaceState(null, null, `?id=${this.chatbot.id}`);
                    }
                    alert('Chatbot saved successfully!');
                } else {
                    alert(data.message || 'Save failed');
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Save failed. Please try again.');
            } finally {
                this.saving = false;
            }
        },

        async testChatbot() {
            if (!this.chatbot) {
                alert('Please save the chatbot first before testing.');
                return;
            }

            this.testing = true;

            try {
                // Open test window or modal
                const testWindow = window.open('', '_blank', 'width=400,height=600');
                testWindow.document.write(`
                    <html>
                        <head>
                            <title>Test Chatbot</title>
                            <script src="https://cdn.tailwindcss.com"></script>
                        </head>
                        <body class="bg-gray-100 p-4">
                            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="bg-blue-600 text-white p-4">
                                    <h3 class="font-medium">Test Chatbot</h3>
                                </div>
                                <div id="chat-messages" class="p-4 h-96 overflow-y-auto">
                                    <div class="mb-4">
                                        <div class="bg-gray-200 rounded-lg p-3 max-w-xs">
                                            ${this.chatbotSettings.initial_message}
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 border-t">
                                    <input
                                        type="text"
                                        id="message-input"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Type a message..."
                                        onkeypress="if(event.key === 'Enter') sendMessage()"
                                    >
                                </div>
                            </div>
                            <script>
                                function sendMessage() {
                                    const input = document.getElementById('message-input');
                                    const message = input.value.trim();
                                    if (!message) return;

                                    const messagesDiv = document.getElementById('chat-messages');
                                    messagesDiv.innerHTML += \`
                                        <div class="mb-4 text-right">
                                            <div class="bg-blue-600 text-white rounded-lg p-3 max-w-xs inline-block">
                                                \${message}
                                            </div>
                                        </div>
                                    \`;

                                    // Simulate bot response
                                    setTimeout(() => {
                                        messagesDiv.innerHTML += \`
                                            <div class="mb-4">
                                                <div class="bg-gray-200 rounded-lg p-3 max-w-xs">
                                                    Thanks for your message! This is a test response.
                                                </div>
                                            </div>
                                        \`;
                                        messagesDiv.scrollTop = messagesDiv.scrollHeight;
                                    }, 1000);

                                    input.value = '';
                                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                                }
                            </script>
                        </body>
                    </html>
                `);

                testWindow.focus();
            } catch (error) {
                console.error('Test error:', error);
                alert('Test failed. Please try again.');
            } finally {
                this.testing = false;
            }
        }
    }
}
</script>
@endsection
