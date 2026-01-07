@extends('layouts.app')

@section('page-title', 'Brand Assets')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="brandAssets()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $brand->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Manage brand assets, guidelines, and visual elements</p>
            </div>
            <button 
                @click="showUploadModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Asset
                </span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Brand Guidelines Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Brand Guidelines</h2>
                
                <!-- Brand Logo -->
                @if($guidelines['logo_url'])
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex items-center justify-center">
                        <img src="{{ $guidelines['logo_url'] }}" alt="{{ $brand->name }} Logo" class="max-h-32 max-w-full object-contain">
                    </div>
                </div>
                @endif

                <!-- Summary -->
                @if($guidelines['summary'])
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Summary</label>
                    <p class="text-sm text-gray-600">{{ $guidelines['summary'] }}</p>
                </div>
                @endif

                <!-- Tone of Voice -->
                @if($guidelines['tone_of_voice'])
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tone of Voice</label>
                    <p class="text-sm text-gray-600">{{ $guidelines['tone_of_voice'] }}</p>
                </div>
                @endif

                <!-- Audience -->
                @if($guidelines['audience'])
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                    <p class="text-sm text-gray-600">{{ $guidelines['audience'] }}</p>
                </div>
                @endif

                <!-- Guidelines -->
                @if($guidelines['guidelines'])
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Guidelines</label>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $guidelines['guidelines'] }}</p>
                </div>
                @endif

                <!-- Keywords -->
                @if(!empty($guidelines['keywords']))
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keywords</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($guidelines['keywords'] as $keyword)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $keyword }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Avoid Keywords -->
                @if(!empty($guidelines['avoid_keywords']))
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Avoid Keywords</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($guidelines['avoid_keywords'] as $keyword)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $keyword }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Assets Section -->
        <div class="lg:col-span-2">
            <!-- Asset Type Tabs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button 
                            @click="activeTab = 'logo'"
                            :class="activeTab === 'logo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Logos
                            <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="getAssetCount('logo')"></span>
                        </button>
                        <button 
                            @click="activeTab = 'image'"
                            :class="activeTab === 'image' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Images
                            <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="getAssetCount('image')"></span>
                        </button>
                        <button 
                            @click="activeTab = 'font'"
                            :class="activeTab === 'font' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Fonts
                            <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="getAssetCount('font')"></span>
                        </button>
                        <button 
                            @click="activeTab = 'color'"
                            :class="activeTab === 'color' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Colors
                            <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="getAssetCount('color')"></span>
                        </button>
                        <button 
                            @click="activeTab = 'other'"
                            :class="activeTab === 'other' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Other
                            <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="getAssetCount('other')"></span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Assets Grid -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Empty State -->
                <div x-show="getCurrentAssets().length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ $brand->name }} assets</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new asset.</p>
                    <div class="mt-6">
                        <button 
                            @click="showUploadModal = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Asset
                        </button>
                    </div>
                </div>

                <!-- Assets Grid -->
                <div x-show="getCurrentAssets().length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="asset in getCurrentAssets()" :key="asset.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors group">
                            <!-- Asset Preview -->
                            <div class="aspect-square bg-gray-50 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                <template x-if="asset.url && (asset.type === 'logo' || asset.type === 'image')">
                                    <img :src="asset.url" :alt="asset.name" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="asset.type === 'color' && asset.url">
                                    <div class="w-full h-full rounded-lg" :style="'background-color: ' + asset.url"></div>
                                </template>
                                <template x-if="!asset.url || (asset.type !== 'logo' && asset.type !== 'image' && asset.type !== 'color')">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </template>
                            </div>
                            
                            <!-- Asset Info -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate" x-text="asset.name"></h3>
                                    <p class="text-xs text-gray-500 capitalize mt-1" x-text="asset.type"></p>
                                    <template x-if="asset.tags && asset.tags.length > 0">
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            <template x-for="tag in asset.tags" :key="tag">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800" x-text="tag"></span>
                                            </template>
                                        </div>
                                    </template>
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
                                                <button 
                                                    @click="editAsset(asset); open = false"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Edit
                                                </button>
                                                <button 
                                                    @click="deleteAsset(asset); open = false"
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
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload/Edit Modal -->
    <div 
        x-show="showUploadModal"
        @click.away="showUploadModal = false"
        x-transition
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none;"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="editingAsset ? 'Edit Asset' : 'Add New Asset'"></h3>
                
                <form @submit.prevent="saveAsset" class="space-y-4">
                    <!-- Asset Name -->
                    <div>
                        <label for="asset_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Asset Name
                        </label>
                        <input 
                            type="text" 
                            id="asset_name"
                            x-model="form.name"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="e.g., Primary Logo"
                        >
                    </div>

                    <!-- Asset Type -->
                    <div>
                        <label for="asset_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Type
                        </label>
                        <select 
                            id="asset_type"
                            x-model="form.type"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="logo">Logo</option>
                            <option value="image">Image</option>
                            <option value="font">Font</option>
                            <option value="color">Color</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- File Upload or URL -->
                    <div>
                        <label for="asset_file" class="block text-sm font-medium text-gray-700 mb-1">
                            <span x-show="form.type !== 'color'">File</span>
                            <span x-show="form.type === 'color'">Color Value (Hex/RGB)</span>
                        </label>
                        <template x-if="form.type === 'color'">
                            <input 
                                type="text" 
                                id="asset_color"
                                x-model="form.url"
                                placeholder="#FF5733 or rgb(255, 87, 51)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </template>
                        <template x-if="form.type !== 'color'">
                            <div>
                                <input 
                                    type="file" 
                                    id="asset_file"
                                    @change="handleFileChange"
                                    :accept="form.type === 'font' ? '.ttf,.otf,.woff,.woff2' : 'image/*'"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">Or enter a URL</p>
                                <input 
                                    type="url" 
                                    x-model="form.url"
                                    placeholder="https://example.com/asset.png"
                                    class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </template>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label for="asset_tags" class="block text-sm font-medium text-gray-700 mb-1">
                            Tags (comma-separated)
                        </label>
                        <input 
                            type="text" 
                            id="asset_tags"
                            x-model="form.tags"
                            placeholder="primary, logo, brand"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4">
                        <button 
                            type="button"
                            @click="cancelEdit"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="loading"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <span x-show="!loading" x-text="editingAsset ? 'Update' : 'Add Asset'"></span>
                            <span x-show="loading">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function brandAssets() {
    return {
        organizationId: '{{ $organizationId }}',
        brandId: '{{ $brand->id }}',
        activeTab: 'logo',
        showUploadModal: false,
        editingAsset: null,
        loading: false,
        error: null,
        assets: @json($assetsGrouped),
        form: {
            name: '',
            type: 'logo',
            file: null,
            url: '',
            tags: ''
        },

        getCurrentAssets() {
            return this.assets[this.activeTab] || [];
        },

        getAssetCount(type) {
            return (this.assets[type] || []).length;
        },

        handleFileChange(event) {
            this.form.file = event.target.files[0];
            this.form.url = '';
        },

        editAsset(asset) {
            this.editingAsset = asset;
            this.form.name = asset.name;
            this.form.type = asset.type;
            this.form.url = asset.url || '';
            this.form.tags = (asset.tags || []).join(', ');
            this.form.file = null;
            this.showUploadModal = true;
        },

        cancelEdit() {
            this.editingAsset = null;
            this.showUploadModal = false;
            this.form = {
                name: '',
                type: 'logo',
                file: null,
                url: '',
                tags: ''
            };
        },

        async saveAsset() {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('type', this.form.type);
                
                if (this.form.file) {
                    formData.append('file', this.form.file);
                } else if (this.form.url) {
                    formData.append('url', this.form.url);
                }
                
                if (this.form.tags) {
                    const tags = this.form.tags.split(',').map(t => t.trim()).filter(t => t);
                    formData.append('tags', JSON.stringify(tags));
                }

                const url = this.editingAsset 
                    ? `/main/${this.organizationId}/brands/${this.brandId}/assets/${this.editingAsset.id}`
                    : `/main/${this.organizationId}/brands/${this.brandId}/assets`;
                
                // Laravel requires _method field for PUT requests
                if (this.editingAsset) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Reload page to show updated assets
                    window.location.reload();
                } else {
                    this.error = data.message || 'Failed to save asset. Please try again.';
                }
            } catch (error) {
                this.error = 'An error occurred while saving the asset. Please try again.';
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async deleteAsset(asset) {
            if (!confirm(`Are you sure you want to delete "${asset.name}"?`)) {
                return;
            }

            try {
                const response = await fetch(`/main/${this.organizationId}/brands/${this.brandId}/assets/${asset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Remove asset from local array
                    const typeAssets = this.assets[asset.type] || [];
                    const index = typeAssets.findIndex(a => a.id === asset.id);
                    if (index > -1) {
                        typeAssets.splice(index, 1);
                    }
                } else {
                    alert(data.message || 'Failed to delete asset. Please try again.');
                }
            } catch (error) {
                alert('An error occurred while deleting the asset. Please try again.');
                console.error('Error:', error);
            }
        }
    }
}
</script>
@endsection

