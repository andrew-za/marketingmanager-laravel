@extends('layouts.app')

@section('page-title', 'Workflows')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="workflowManager()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Workflows</h1>
                <p class="mt-1 text-sm text-gray-600">Create and manage automated workflows for your marketing processes</p>
            </div>
            <a
                href="{{ route('workflows.builder') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Workflow
                </span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="sr-only">Search workflows</label>
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
                        @input.debounce.300ms="searchWorkflows"
                        placeholder="Search workflows..."
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
                    @change="filterWorkflows"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Workflows Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading workflows...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && workflows.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No workflows found</h3>
            <p class="mt-1 text-sm text-gray-500" x-text="searchQuery || statusFilter ? 'Try adjusting your search or filters.' : 'Get started by creating your first workflow.'"></p>
            <div class="mt-6" x-show="!searchQuery && !statusFilter">
                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Workflow
                </button>
            </div>
        </div>

        <!-- Workflows List -->
        <div x-show="!loading && workflows.length > 0" class="divide-y divide-gray-200">
            <template x-for="workflow in workflows" :key="workflow.id">
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Workflow Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Workflow Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-medium text-gray-900 truncate" x-text="workflow.name"></h3>
                                    <span
                                        :class="workflow.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-text="workflow.is_active ? 'Active' : 'Inactive'"
                                    ></span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600" x-text="workflow.description || 'No description'"></p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>Created by <span x-text="workflow.creator?.name || 'Unknown'"></span></span>
                                    <span class="mx-2">•</span>
                                    <span x-text="formatDate(workflow.created_at)"></span>
                                    <span class="mx-2">•</span>
                                    <span x-text="workflow.executions_count || 0"></span> executions
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2">
                            <a
                                :href="'{{ route('workflows.builder') }}?id=' + workflow.id"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="Edit workflow"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <button
                                @click="duplicateWorkflow(workflow)"
                                class="text-gray-400 hover:text-gray-600 p-2"
                                title="Duplicate workflow"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>

                            <button
                                @click="toggleWorkflowStatus(workflow)"
                                :class="workflow.is_active ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600'"
                                class="p-2"
                                :title="workflow.is_active ? 'Deactivate workflow' : 'Activate workflow'"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>

                            <button
                                @click="deleteWorkflow(workflow)"
                                class="text-red-400 hover:text-red-600 p-2"
                                title="Delete workflow"
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
function workflowManager() {
    return {
        workflows: [],
        searchQuery: '',
        statusFilter: '',
        loading: false,

        init() {
            this.loadWorkflows();
        },

        loadWorkflows() {
            this.loading = true;

            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.statusFilter) {
                params.append('is_active', this.statusFilter === 'active' ? '1' : '0');
            }

            fetch(`{{ route('workflows.index') }}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.workflows = data.data;
                }
            })
            .catch(error => {
                console.error('Error loading workflows:', error);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        searchWorkflows() {
            this.loadWorkflows();
        },

        filterWorkflows() {
            this.loadWorkflows();
        },

        duplicateWorkflow(workflow) {
            // Redirect to builder with duplicated workflow data
            window.location.href = `{{ route('workflows.builder') }}?duplicate=${workflow.id}`;
        },

        async toggleWorkflowStatus(workflow) {
            try {
                const response = await fetch(`{{ route('workflows.update', '') }}/${workflow.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        is_active: !workflow.is_active
                    })
                });

                const data = await response.json();

                if (data.success) {
                    workflow.is_active = !workflow.is_active;
                } else {
                    alert(data.message || 'Failed to update workflow status');
                }
            } catch (error) {
                console.error('Error updating workflow status:', error);
                alert('Failed to update workflow status');
            }
        },

        async deleteWorkflow(workflow) {
            if (!confirm(`Are you sure you want to delete "${workflow.name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`{{ route('workflows.destroy', '') }}/${workflow.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.loadWorkflows(); // Reload workflows to reflect the deletion
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
