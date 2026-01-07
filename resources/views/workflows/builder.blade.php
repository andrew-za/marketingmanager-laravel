@extends('layouts.app')

@section('page-title', 'Workflow Builder')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="workflowBuilder()" style="height: calc(100vh - 200px);">
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
                    <h1 class="text-2xl font-semibold text-gray-900" x-text="workflow?.name || 'New Workflow'"></h1>
                    <p class="mt-1 text-sm text-gray-600">Design your automated workflow</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button
                    @click="saveWorkflow"
                    :disabled="saving"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <span x-show="!saving">Save Workflow</span>
                    <span x-show="saving">Saving...</span>
                </button>

                <button
                    @click="testWorkflow"
                    :disabled="testing"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <span x-show="!testing">Test Workflow</span>
                    <span x-show="testing">Testing...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Builder Canvas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="height: calc(100% - 100px);">
        <div class="flex h-full">
            <!-- Toolbox Sidebar -->
            <div class="w-80 bg-gray-50 border-r border-gray-200 p-4 overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Workflow Components</h3>

                <!-- Triggers -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Triggers</h4>
                    <div class="space-y-2">
                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'trigger', 'schedule')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Schedule Trigger</h5>
                                    <p class="text-xs text-gray-500">Run on a schedule</p>
                                </div>
                            </div>
                        </div>

                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'trigger', 'webhook')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Webhook Trigger</h5>
                                    <p class="text-xs text-gray-500">Trigger via API call</p>
                                </div>
                            </div>
                        </div>

                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'trigger', 'event')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V7h-5v5L5 17h5m0 0v5m0-5H5m5 0h5" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Event Trigger</h5>
                                    <p class="text-xs text-gray-500">React to system events</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Actions</h4>
                    <div class="space-y-2">
                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'action', 'send_email')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Send Email</h5>
                                    <p class="text-xs text-gray-500">Send email notification</p>
                                </div>
                            </div>
                        </div>

                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'action', 'create_post')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Create Post</h5>
                                    <p class="text-xs text-gray-500">Create social media post</p>
                                </div>
                            </div>
                        </div>

                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'action', 'update_record')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Update Record</h5>
                                    <p class="text-xs text-gray-500">Update database record</p>
                                </div>
                            </div>
                        </div>

                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'action', 'api_call')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">API Call</h5>
                                    <p class="text-xs text-gray-500">Make HTTP request</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conditions -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Conditions</h4>
                    <div class="space-y-2">
                        <div
                            draggable="true"
                            @dragstart="onDragStart($event, 'condition', 'if_else')"
                            class="bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">If/Else Condition</h5>
                                    <p class="text-xs text-gray-500">Conditional logic</p>
                                </div>
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
                        <h3 class="text-lg font-medium text-gray-900">Workflow Canvas</h3>
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
                                Start
                            </div>
                        </div>

                        <!-- Dropped Components will appear here -->
                        <div id="workflow-nodes" class="relative">
                            <!-- Dynamic nodes will be added here -->
                        </div>

                        <!-- End Node -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                            <div class="bg-red-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg">
                                End
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Select a component to edit its properties</p>
                </div>

                <!-- Node Properties Form -->
                <div x-show="selectedNode" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <input
                            type="text"
                            x-model="selectedNode.label"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Dynamic fields based on node type -->
                    <template x-if="selectedNode.type === 'trigger'">
                        <template x-if="selectedNode.subtype === 'schedule'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Schedule</label>
                                <select x-model="selectedNode.config.schedule_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div x-show="selectedNode.config.schedule_type === 'custom'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cron Expression</label>
                                <input
                                    type="text"
                                    x-model="selectedNode.config.cron"
                                    placeholder="0 9 * * *"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </template>

                        <template x-if="selectedNode.subtype === 'webhook'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                                <input
                                    type="url"
                                    x-model="selectedNode.config.url"
                                    placeholder="https://your-app.com/webhook"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </template>
                    </template>

                    <template x-if="selectedNode.type === 'action'">
                        <template x-if="selectedNode.subtype === 'send_email'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                <input
                                    type="email"
                                    x-model="selectedNode.config.to"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input
                                    type="text"
                                    x-model="selectedNode.config.subject"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </template>

                        <template x-if="selectedNode.subtype === 'api_call'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                                <select x-model="selectedNode.config.method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                <input
                                    type="url"
                                    x-model="selectedNode.config.url"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </template>
                    </template>

                    <!-- Delete Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button
                            @click="deleteNode"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Delete Component
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function workflowBuilder() {
    return {
        workflow: null,
        nodes: [],
        selectedNode: null,
        zoom: 100,
        saving: false,
        testing: false,

        init() {
            // Load workflow data if editing
            this.loadWorkflow();
            this.setupCanvas();
        },

        loadWorkflow() {
            // Load workflow data from URL parameter or create new
            const urlParams = new URLSearchParams(window.location.search);
            const workflowId = urlParams.get('id');

            if (workflowId) {
                fetch(`{{ route('workflows.show', '') }}/${workflowId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.workflow = data.data;
                            this.nodes = data.data.nodes || [];
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
                id: Date.now() // Simple ID generation
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
                    type: data.type,
                    subtype: data.subtype,
                    label: this.getDefaultLabel(data.type, data.subtype),
                    x: x,
                    y: y,
                    config: this.getDefaultConfig(data.type, data.subtype)
                };

                this.nodes.push(node);
                this.renderNodes();
            } catch (error) {
                console.error('Error handling drop:', error);
            }
        },

        getDefaultLabel(type, subtype) {
            const labels = {
                trigger: {
                    schedule: 'Schedule Trigger',
                    webhook: 'Webhook Trigger',
                    event: 'Event Trigger'
                },
                action: {
                    send_email: 'Send Email',
                    create_post: 'Create Post',
                    update_record: 'Update Record',
                    api_call: 'API Call'
                },
                condition: {
                    if_else: 'If/Else Condition'
                }
            };
            return labels[type]?.[subtype] || 'Component';
        },

        getDefaultConfig(type, subtype) {
            const configs = {
                trigger: {
                    schedule: { schedule_type: 'daily', cron: '' },
                    webhook: { url: '' },
                    event: { event_type: '' }
                },
                action: {
                    send_email: { to: '', subject: '', body: '' },
                    create_post: { platform: '', content: '' },
                    update_record: { table: '', field: '', value: '' },
                    api_call: { method: 'GET', url: '', headers: {}, body: '' }
                },
                condition: {
                    if_else: { condition: '', true_path: '', false_path: '' }
                }
            };
            return configs[type]?.[subtype] || {};
        },

        renderNodes() {
            const container = document.getElementById('workflow-nodes');
            container.innerHTML = '';

            this.nodes.forEach(node => {
                const nodeElement = document.createElement('div');
                nodeElement.className = 'absolute cursor-pointer';
                nodeElement.style.left = `${node.x}px`;
                nodeElement.style.top = `${node.y}px`;
                nodeElement.onclick = () => this.selectNode(node);

                const colors = {
                    trigger: 'bg-blue-500',
                    action: 'bg-green-500',
                    condition: 'bg-orange-500'
                };

                nodeElement.innerHTML = `
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4 shadow-lg hover:border-blue-400 transition-colors min-w-32">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-4 h-4 ${colors[node.type]} rounded"></div>
                            <span class="text-xs font-medium text-gray-500 uppercase">${node.type}</span>
                        </div>
                        <div class="text-sm font-medium text-gray-900">${node.label}</div>
                    </div>
                `;

                container.appendChild(nodeElement);
            });
        },

        selectNode(node) {
            this.selectedNode = node;
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

        async saveWorkflow() {
            this.saving = true;

            try {
                const workflowData = {
                    name: this.workflow?.name || 'New Workflow',
                    description: this.workflow?.description || '',
                    type: this.workflow?.type || 'custom',
                    nodes: this.nodes,
                    connections: [] // Will implement connections later
                };

                const url = this.workflow
                    ? `{{ route('workflows.update', '') }}/${this.workflow.id}`
                    : '{{ route('workflows.store') }}';

                const method = this.workflow ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(workflowData)
                });

                const data = await response.json();

                if (data.success) {
                    if (!this.workflow) {
                        this.workflow = data.data;
                        // Update URL to include workflow ID
                        window.history.replaceState(null, null, `?id=${this.workflow.id}`);
                    }
                    alert('Workflow saved successfully!');
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

        async testWorkflow() {
            if (!this.workflow) {
                alert('Please save the workflow first before testing.');
                return;
            }

            this.testing = true;

            try {
                const response = await fetch(`{{ route('workflows.test', '') }}/${this.workflow.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        input_data: {}
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Workflow test completed successfully!');
                } else {
                    alert(data.message || 'Test failed');
                }
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
