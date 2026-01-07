@extends('layouts.app')

@section('page-title', 'File Preview')

@section('content')
<div class="container mx-auto px-4 py-6">
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
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $file->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $organization->name }} • Uploaded {{ $file->created_at->format('M j, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a
                    href="{{ route('files.download', [$organization->id, $file->id]) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <span class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download
                    </span>
                </a>

                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="text-gray-600 hover:text-gray-900 focus:outline-none p-2"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <button
                                onclick="window.print()"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Print
                            </button>
                            <button
                                @click="shareFile"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Share
                            </button>
                            <button
                                @click="deleteFile"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- File Preview -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Image Preview -->
                @if(str_starts_with($file->mime_type, 'image/'))
                    <div class="relative bg-gray-50">
                        <img
                            src="{{ $file->file_url }}"
                            alt="{{ $file->name }}"
                            class="w-full h-auto max-h-96 object-contain mx-auto"
                        >

                        <!-- Zoom Controls -->
                        <div class="absolute bottom-4 right-4 flex items-center space-x-2">
                            <button
                                onclick="zoomOut()"
                                class="bg-white bg-opacity-90 text-gray-700 p-2 rounded-full shadow hover:bg-opacity-100"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <button
                                onclick="zoomIn()"
                                class="bg-white bg-opacity-90 text-gray-700 p-2 rounded-full shadow hover:bg-opacity-100"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @elseif($file->mime_type === 'application/pdf')
                    <!-- PDF Preview -->
                    <div class="bg-gray-100 p-8 text-center">
                        <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">PDF Document</h3>
                        <p class="mt-2 text-sm text-gray-600">Click download to view the full document</p>
                        <div class="mt-6">
                            <a
                                href="{{ route('files.download', [$organization->id, $file->id]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Generic File Preview -->
                    <div class="bg-gray-100 p-8 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $file->name }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $file->mime_type }}</p>
                        <div class="mt-6">
                            <a
                                href="{{ route('files.download', [$organization->id, $file->id]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download File
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- File Details -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">File Details</h2>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">File Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->name }}</dd>
                    </div>

                    @if($file->description)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->description }}</dd>
                    </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">File Size</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($file->file_size / 1024, 1) }} KB</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">File Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->mime_type }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Uploaded By</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($file->uploadedBy)
                                {{ $file->uploadedBy->name }}
                            @else
                                Unknown
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Upload Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->created_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>

                    @if($file->width && $file->height)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dimensions</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->width }} × {{ $file->height }} px</dd>
                    </div>
                    @endif

                    @if($file->tags && count($file->tags) > 0)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tags</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach($file->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $tag }}
                                </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>

                <div class="space-y-3">
                    <a
                        href="{{ route('files.download', [$organization->id, $file->id]) }}"
                        class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download
                    </a>

                    <button
                        onclick="copyFileUrl()"
                        class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function zoomIn() {
    const img = document.querySelector('img[alt="{{ $file->name }}"]');
    if (img) {
        const currentScale = img.style.transform ? parseFloat(img.style.transform.replace('scale(', '').replace(')', '')) : 1;
        img.style.transform = `scale(${Math.min(currentScale + 0.25, 3)})`;
        img.style.transformOrigin = 'center center';
    }
}

function zoomOut() {
    const img = document.querySelector('img[alt="{{ $file->name }}"]');
    if (img) {
        const currentScale = img.style.transform ? parseFloat(img.style.transform.replace('scale(', '').replace(')', '')) : 1;
        img.style.transform = `scale(${Math.max(currentScale - 0.25, 0.25)})`;
        img.style.transformOrigin = 'center center';
    }
}

function copyFileUrl() {
    const url = '{{ $file->file_url }}';
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    });
}

async function deleteFile() {
    if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('{{ route('files.destroy', [$organization->id, $file->id]) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = '{{ route('files.index', [$organization->id]) }}?brandId={{ request('brandId') }}';
        } else {
            alert(data.message || 'Delete failed');
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Delete failed. Please try again.');
    }
}

async function shareFile() {
    const email = prompt('Enter email address to share with:');
    if (!email) return;

    // This would need to be implemented in the controller
    alert('File sharing feature coming soon!');
}
</script>
@endsection
