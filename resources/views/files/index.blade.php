@extends('layouts.app')

@section('page-title', 'Files')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="fileManager()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $brand->name }} Files</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your brand files, images, and documents</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Upload Button -->
                <button
                    @click="showUploadModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <span class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Upload Files
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="sr-only">Search files</label>
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
                        @input.debounce.300ms="searchFiles"
                        placeholder="Search files..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Type Filter -->
            <div class="sm:w-48">
                <label for="type-filter" class="sr-only">Filter by type</label>
                <select
                    id="type-filter"
                    x-model="typeFilter"
                    @change="filterFiles"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading files...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && files.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No files found</h3>
            <p class="mt-1 text-sm text-gray-500" x-text="searchQuery || typeFilter ? 'Try adjusting your search or filters.' : 'Get started by uploading your first file.'"></p>
            <div class="mt-6" x-show="!searchQuery && !typeFilter">
                <button
                    @click="showUploadModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload Files
                </button>
            </div>
        </div>

        <!-- Files Grid -->
        <div x-show="!loading && files.length > 0" class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="file in files" :key="file.id">
                    <div class="group relative bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <!-- File Preview -->
                        <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                            <template x-if="isImage(file)">
                                <img
                                    :src="file.file_url"
                                    :alt="file.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                >
                            </template>
                            <template x-if="!isImage(file)">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-xs text-gray-500 uppercase" x-text="getFileExtension(file.name)"></p>
                                </div>
                            </template>
                        </div>

                        <!-- File Info -->
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></h3>
                                    <p class="text-xs text-gray-500 mt-1" x-text="formatFileSize(file.file_size)"></p>
                                    <p class="text-xs text-gray-400 mt-1" x-text="formatDate(file.created_at)"></p>
                                </div>

                                <!-- Actions Menu -->
                                <div class="ml-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="relative" x-data="{ open: false }">
                                        <button
                                            @click="open = !open"
                                            class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        <div
                                            x-show="open"
                                            @click.away="open = false"
                                            x-transition
                                            class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                                        >
                                            <div class="py-1">
                                                <a
                                                    :href="'{{ route('files.show', [$organization->id, '']) }}/' + file.id"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Preview
                                                </a>
                                                <a
                                                    :href="'{{ route('files.download', [$organization->id, '']) }}/' + file.id"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Download
                                                </a>
                                                <button
                                                    @click="deleteFile(file); open = false"
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tags -->
                            <template x-if="file.tags && file.tags.length > 0">
                                <div class="flex flex-wrap gap-1 mt-3">
                                    <template x-for="tag in file.tags.slice(0, 3)" :key="tag">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800" x-text="tag"></span>
                                    </template>
                                    <template x-if="file.tags.length > 3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800" x-text="'+' + (file.tags.length - 3)"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Selection Overlay -->
                        <div
                            x-show="selectedFiles.includes(file.id)"
                            class="absolute inset-0 bg-blue-600 bg-opacity-20 border-2 border-blue-600 rounded-lg pointer-events-none"
                        >
                            <div class="absolute top-2 right-2 bg-blue-600 text-white rounded-full p-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex items-center justify-between" x-show="files.length > 0">
                <div class="text-sm text-gray-700">
                    Showing <span x-text="files.length"></span> files
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="loadPreviousPage"
                        :disabled="!hasPreviousPage"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button
                        @click="loadNextPage"
                        :disabled="!hasNextPage"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div
        x-show="showUploadModal"
        @click.away="showUploadModal = false"
        x-transition
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none;"
    >
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Files</h3>

                <form @submit.prevent="uploadFiles" class="space-y-4">
                    <!-- File Upload -->
                    <div>
                        <label for="files" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Files
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload files</span>
                                        <input id="files" name="files" type="file" multiple class="sr-only" @change="handleFileSelect">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, PDF up to 10MB each</p>
                            </div>
                        </div>

                        <!-- Selected Files Preview -->
                        <div x-show="selectedFilesForUpload.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Selected Files:</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="(file, index) in selectedFilesForUpload" :key="index">
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm text-gray-900" x-text="file.name"></span>
                                            <span class="text-xs text-gray-500 ml-2" x-text="formatFileSize(file.size)"></span>
                                        </div>
                                        <button
                                            @click="removeFileFromUpload(index)"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="cancelUpload"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="uploading || selectedFilesForUpload.length === 0"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <span x-show="!uploading">Upload Files</span>
                            <span x-show="uploading">Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function fileManager() {
    return {
        organizationId: '{{ $organization->id }}',
        brandId: '{{ $brand->id }}',
        files: @json($files->items()),
        searchQuery: '',
        typeFilter: '',
        loading: false,
        showUploadModal: false,
        selectedFilesForUpload: [],
        uploading: false,
        currentPage: {{ $files->currentPage() }},
        hasNextPage: {{ $files->hasMorePages() ? 'true' : 'false' }},
        hasPreviousPage: {{ $files->currentPage() > 1 ? 'true' : 'false' }},
        selectedFiles: [],

        init() {
            // Load initial files
            this.loadFiles();
        },

        loadFiles() {
            this.loading = true;

            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.typeFilter) params.append('type', this.typeFilter);
            params.append('page', this.currentPage);

            fetch(`{{ route('files.index', [$organization->id]) }}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.files = data.data;
                    this.hasNextPage = data.next_page_url !== null;
                    this.hasPreviousPage = data.prev_page_url !== null;
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        searchFiles() {
            this.currentPage = 1;
            this.loadFiles();
        },

        filterFiles() {
            this.currentPage = 1;
            this.loadFiles();
        },

        loadNextPage() {
            if (this.hasNextPage) {
                this.currentPage++;
                this.loadFiles();
            }
        },

        loadPreviousPage() {
            if (this.hasPreviousPage) {
                this.currentPage--;
                this.loadFiles();
            }
        },

        isImage(file) {
            return file.mime_type && file.mime_type.startsWith('image/');
        },

        handleImageError(event) {
            event.target.style.display = 'none';
            event.target.nextElementSibling.style.display = 'block';
        },

        getFileExtension(filename) {
            return filename.split('.').pop().toUpperCase();
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.selectedFilesForUpload = files;
        },

        removeFileFromUpload(index) {
            this.selectedFilesForUpload.splice(index, 1);
        },

        async uploadFiles() {
            if (this.selectedFilesForUpload.length === 0) return;

            this.uploading = true;

            try {
                const formData = new FormData();
                formData.append('brandId', this.brandId);

                this.selectedFilesForUpload.forEach(file => {
                    formData.append('file', file);
                });

                const response = await fetch(`{{ route('files.store', $organization->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showUploadModal = false;
                    this.selectedFilesForUpload = [];
                    this.loadFiles(); // Reload files to show the new uploads
                } else {
                    alert(data.message || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Upload failed. Please try again.');
            } finally {
                this.uploading = false;
            }
        },

        cancelUpload() {
            this.showUploadModal = false;
            this.selectedFilesForUpload = [];
        },

        async deleteFile(file) {
            if (!confirm(`Are you sure you want to delete "${file.name}"?`)) {
                return;
            }

            try {
                const response = await fetch(`{{ route('files.destroy', [$organization->id, file.id]) }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.loadFiles(); // Reload files to reflect the deletion
                } else {
                    alert(data.message || 'Delete failed');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Delete failed. Please try again.');
            }
        }
    }
}
</script>
@endsection
