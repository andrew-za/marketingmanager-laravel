@extends('layouts.app')

@section('page-title', 'Chatbots')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="chatbotManager()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Chatbots</h1>
                <p class="mt-1 text-sm text-gray-600">Create and manage AI-powered chatbots for your website</p>
            </div>
            <a
                href="{{ route('chatbots.builder') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Chatbot
                </span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="sr-only">Search chatbots</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="search"
                        x-model="searchQuery"
                        @input.debounce.300ms="searchChatbots"
                        placeholder="Search chatbots..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Status Filter -->
            <div class="sm:w-48">
                <label for="status-filter" class="sr-only">Filter by status</label>
                <select
                    id="status-filter"
                    x-model="statusFilter"
                    @change="filterChatbots"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Chatbots Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading chatbots...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && chatbots.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No chatbots found</h3>
            <p class="mt-1 text-sm text-gray-500" x-text="searchQuery || statusFilter ? 'Try adjusting your search or filters.' : 'Get started by creating your first chatbot.'"></p>
            <div class="mt-6" x-show="!searchQuery && !statusFilter">
                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Chatbot
                </button>
            </div>
        </div>

        <!-- Chatbots List -->
        <div x-show="!loading && chatbots.length > 0" class="divide-y divide-gray-200">
            <template x-for="chatbot in chatbots" :key="chatbot.id">
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Chatbot Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Chatbot Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-medium text-gray-900 truncate" x-text="chatbot.name"></h3>
                                    <span
                                        :class="chatbot.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-text="chatbot.is_active ? 'Active' : 'Inactive'"
                                    ></span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600" x-text="chatbot.description || 'No description'"></p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>Created by <span x-text="chatbot.creator?.name || 'Unknown'"></span></span>
                                    <span class="mx-2">•</span>
                                    <span x-text="formatDate(chatbot.created_at)"></span>
                                    <span class="mx-2">•</span>
                                    <span x-text="chatbot.conversations_count || 0"></span> conversations
                                    <span class="mx-2">•</span>
                                    <span x-text="chatbot.leads_count || 0"></span> leads
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2">
                            <a
                                :href="'{{ route('chatbots.show', '') }}/' + chatbot.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="View chatbot"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <a
                                :href="'{{ route('chatbots.deployment') }}?id=' + chatbot.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="Deploy chatbot"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </a>

                            <a
                                :href="'{{ route('chatbots.analytics') }}?id=' + chatbot.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="View analytics"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </a>

                            <a
                                :href="'{{ route('chatbots.builder') }}?id=' + chatbot.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="Edit chatbot"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <a
                                :href="'{{ route('chatbots.builder') }}?duplicate=' + chatbot.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="Duplicate chatbot"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </a>

                            <button
                                @click="toggleChatbotStatus(chatbot)"
                                :class="chatbot.is_active ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600'"
                                class="p-2"
                                :title="chatbot.is_active ? 'Deactivate chatbot' : 'Activate chatbot'"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>

                            <button
                                @click="deleteChatbot(chatbot)"
                                class="text-red-400 hover:text-red-600 p-2"
                                title="Delete chatbot"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function chatbotManager() {
    return {
        chatbots: [],
        searchQuery: '',
        statusFilter: '',
        loading: false,

        init() {
            this.loadChatbots();
        },

        loadChatbots() {
            this.loading = true;

            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.statusFilter) {
                params.append('is_active', this.statusFilter === 'active' ? '1' : '0');
            }

            fetch(`{{ route('chatbots.index') }}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.chatbots = data.data;
                }
            })
            .catch(error => {
                console.error('Error loading chatbots:', error);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        searchChatbots() {
            this.loadChatbots();
        },

        filterChatbots() {
            this.loadChatbots();
        },

        duplicateChatbot(chatbot) {
            // Redirect to builder with duplicated chatbot data
            window.location.href = `{{ route('chatbots.builder') }}?duplicate=${chatbot.id}`;
        },

        async toggleChatbotStatus(chatbot) {
            try {
                const response = await fetch(`{{ route('chatbots.update', '') }}/${chatbot.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        is_active: !chatbot.is_active
                    })
                });

                const data = await response.json();

                if (data.success) {
                    chatbot.is_active = !chatbot.is_active;
                } else {
                    alert(data.message || 'Failed to update chatbot status');
                }
            } catch (error) {
                console.error('Error updating chatbot status:', error);
                alert('Failed to update chatbot status');
            }
        },

        async deleteChatbot(chatbot) {
            if (!confirm(`Are you sure you want to delete "${chatbot.name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`{{ route('chatbots.destroy', '') }}/${chatbot.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.loadChatbots(); // Reload chatbots to reflect the deletion
                } else {
                    alert(data.message || 'Delete failed');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Delete failed. Please try again.');
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }
    }
}
</script>
@endsection
