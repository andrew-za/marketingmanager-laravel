<template>
    <div
        class="bg-white rounded-lg border border-gray-200 p-4 hover:border-primary-300 transition-colors group relative"
        @mouseenter="showEdit = true"
        @mouseleave="showEdit = false"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-xs font-medium text-gray-500 uppercase mb-1">{{ label }}</p>
                <p class="text-sm text-gray-900 font-medium">{{ value }}</p>
            </div>
            <button
                v-if="showEdit"
                @click="$emit('edit')"
                class="ml-2 p-1 text-gray-400 hover:text-primary-600 transition-colors"
                title="Edit"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OnboardingDataCard',
    props: {
        label: {
            type: String,
            required: true,
        },
        value: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            showEdit: false,
        };
    },
};
</script>

