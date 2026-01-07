@extends('layouts.app')

@section('page-title', 'Product Catalog Generator')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="productCatalog()">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Product Catalog Generator</h1>
        <p class="mt-1 text-sm text-gray-600">Generate product catalog content and descriptions using AI</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Generate Catalog</h2>
                
                <form @submit.prevent="generateCatalog" class="space-y-4">
                    <!-- Brand Selection -->
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Brand (Optional)
                        </label>
                        <select 
                            id="brand_id" 
                            x-model="form.brand_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Select a brand...</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Selection -->
                    <div>
                        <label for="products" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Products
                        </label>
                        <div class="border border-gray-300 rounded-md p-3 max-h-64 overflow-y-auto">
                            @if($products->isEmpty())
                                <p class="text-sm text-gray-500">No products available. Create products first.</p>
                            @else
                                @foreach($products as $product)
                                    <label class="flex items-start space-x-2 py-2 hover:bg-gray-50 px-2 rounded cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            value="{{ $product->id }}"
                                            x-model="form.product_ids"
                                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                                            @if($product->brand)
                                                <span class="text-xs text-gray-500 block">{{ $product->brand->name }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-500" x-text="`${form.product_ids.length} product(s) selected`"></p>
                    </div>

                    <!-- Format Selection -->
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-1">
                            Catalog Format
                        </label>
                        <select 
                            id="format" 
                            x-model="form.format"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="standard">Standard</option>
                            <option value="detailed">Detailed</option>
                            <option value="minimal">Minimal</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <button 
                            type="submit"
                            :disabled="loading || form.product_ids.length === 0"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!loading">Generate Catalog</span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Generating...
                            </span>
                        </button>

                        <button 
                            type="button"
                            @click="generateDescriptions"
                            :disabled="loading || form.product_ids.length === 0"
                            class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Generate Descriptions Only
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Generated Catalog</h2>
                    <div class="flex space-x-2" x-show="catalogContent || descriptions.length > 0">
                        <button 
                            @click="exportCatalog"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            Export Catalog
                        </button>
                        <button 
                            x-show="descriptions.length > 0"
                            @click="exportDescriptions"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            Export Descriptions
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-4 text-sm text-gray-600" x-text="loadingMessage"></p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && !catalogContent && descriptions.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No catalog generated yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Select products and click "Generate Catalog" to get started.</p>
                </div>

                <!-- Catalog Content -->
                <div x-show="!loading && catalogContent" class="prose max-w-none">
                    <div class="whitespace-pre-wrap text-sm text-gray-700 bg-gray-50 rounded-lg p-4 border border-gray-200" x-text="catalogContent"></div>
                </div>

                <!-- Product Descriptions -->
                <div x-show="!loading && descriptions.length > 0" class="space-y-4 mt-6">
                    <h3 class="text-md font-medium text-gray-900">Product Descriptions</h3>
                    <template x-for="(desc, index) in descriptions" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2" x-text="desc.product_name"></h4>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="desc.description"></p>
                            <button 
                                @click="copyDescription(desc.description)"
                                class="mt-2 text-xs text-blue-600 hover:text-blue-700"
                            >
                                Copy Description
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Error Message -->
                <div x-show="error" class="mt-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800" x-text="error"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productCatalog() {
    return {
        form: {
            brand_id: '',
            product_ids: [],
            format: 'standard'
        },
        catalogContent: '',
        descriptions: [],
        loading: false,
        loadingMessage: 'Generating catalog...',
        error: null,

        async generateCatalog() {
            if (this.form.product_ids.length === 0) {
                this.error = 'Please select at least one product.';
                return;
            }

            this.loading = true;
            this.loadingMessage = 'Generating catalog...';
            this.error = null;
            this.catalogContent = '';
            this.descriptions = [];

            try {
                const response = await fetch(`/main/{{ $organizationId }}/ai/product-catalog/generate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.catalogContent = data.data.generated_content || data.data.content || '';
                } else {
                    this.error = data.message || 'Failed to generate catalog. Please try again.';
                }
            } catch (error) {
                this.error = 'An error occurred while generating catalog. Please try again.';
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async generateDescriptions() {
            if (this.form.product_ids.length === 0) {
                this.error = 'Please select at least one product.';
                return;
            }

            this.loading = true;
            this.loadingMessage = 'Generating product descriptions...';
            this.error = null;
            this.catalogContent = '';
            this.descriptions = [];

            try {
                const response = await fetch(`/main/{{ $organizationId }}/ai/product-catalog/descriptions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.descriptions = data.data || [];
                } else {
                    this.error = data.message || 'Failed to generate descriptions. Please try again.';
                }
            } catch (error) {
                this.error = 'An error occurred while generating descriptions. Please try again.';
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async copyDescription(description) {
            try {
                await navigator.clipboard.writeText(description);
                // You could add a toast notification here
            } catch (error) {
                console.error('Failed to copy description:', error);
            }
        },

        exportCatalog() {
            if (!this.catalogContent) return;

            const blob = new Blob([this.catalogContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'product-catalog.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        exportDescriptions() {
            if (this.descriptions.length === 0) return;

            const content = this.descriptions.map(desc => 
                `Product: ${desc.product_name}\n${desc.description}\n\n`
            ).join('---\n\n');

            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'product-descriptions.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }
}
</script>
@endsection

