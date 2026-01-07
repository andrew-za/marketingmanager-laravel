@extends('layouts.app')

@section('page-title', 'Label Inspiration')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="labelInspiration()">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Label Inspiration</h1>
        <p class="mt-1 text-sm text-gray-600">Generate creative label ideas and taglines for your products and campaigns</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Generate Labels</h2>
                
                <form @submit.prevent="generateLabels" class="space-y-4">
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
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Product (Optional)
                        </label>
                        <select 
                            id="product_id" 
                            x-model="form.product_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Select a product...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Context Input -->
                    <div>
                        <label for="context" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Context
                        </label>
                        <textarea 
                            id="context" 
                            x-model="form.context"
                            rows="3"
                            placeholder="Describe the style, tone, or specific requirements for the labels..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <!-- Variation Count -->
                    <div>
                        <label for="variation_count" class="block text-sm font-medium text-gray-700 mb-1">
                            Number of Variations
                        </label>
                        <input 
                            type="number" 
                            id="variation_count" 
                            x-model="form.variation_count"
                            min="3" 
                            max="10" 
                            value="5"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Generate Button -->
                    <button 
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">Generate Labels</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Generated Labels</h2>
                    <button 
                        x-show="labels.length > 0"
                        @click="exportLabels"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Export
                    </button>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-4 text-sm text-gray-600">Generating creative labels...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && labels.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No labels generated yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Fill out the form and click "Generate Labels" to get started.</p>
                </div>

                <!-- Labels Grid -->
                <div x-show="!loading && labels.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(label, index) in labels" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between">
                                <p class="text-sm text-gray-900 font-medium" x-text="label"></p>
                                <button 
                                    @click="copyLabel(label)"
                                    class="ml-2 text-gray-400 hover:text-gray-600"
                                    title="Copy label"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
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
function labelInspiration() {
    return {
        form: {
            brand_id: '',
            product_id: '',
            context: '',
            variation_count: 5
        },
        labels: [],
        loading: false,
        error: null,

        async generateLabels() {
            this.loading = true;
            this.error = null;
            this.labels = [];

            try {
                const response = await fetch(`/main/{{ $organizationId }}/ai/label-inspiration/generate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.labels = data.data.labels || [];
                } else {
                    this.error = data.message || 'Failed to generate labels. Please try again.';
                }
            } catch (error) {
                this.error = 'An error occurred while generating labels. Please try again.';
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async copyLabel(label) {
            try {
                await navigator.clipboard.writeText(label);
                // You could add a toast notification here
            } catch (error) {
                console.error('Failed to copy label:', error);
            }
        },

        exportLabels() {
            if (this.labels.length === 0) return;

            const content = this.labels.join('\n');
            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'label-inspiration.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }
}
</script>
@endsection

