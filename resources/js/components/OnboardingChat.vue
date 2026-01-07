<template>
    <div class="flex flex-col h-full">
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4" ref="messagesContainer">
            <div
                v-for="(message, index) in messages"
                :key="index"
                :class="[
                    'flex items-start space-x-3',
                    message.role === 'user' ? 'flex-row-reverse space-x-reverse' : ''
                ]"
            >
                <!-- Avatar -->
                <div
                    :class="[
                        'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                        message.role === 'assistant' ? 'bg-primary-100' : 'bg-gray-200'
                    ]"
                >
                    <svg
                        v-if="message.role === 'assistant'"
                        class="w-6 h-6 text-primary-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <svg
                        v-else
                        class="w-6 h-6 text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>

                <!-- Message Content -->
                <div
                    :class="[
                        'flex-1 rounded-lg px-4 py-3 max-w-[70%]',
                        message.role === 'assistant'
                            ? 'bg-gray-100 text-gray-900'
                            : 'bg-primary-600 text-white'
                    ]"
                >
                    <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="isLoading" class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1 rounded-lg px-4 py-3 bg-gray-100">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Action Buttons (for business model selection) -->
        <div v-if="actionButtons && actionButtons.length > 0" class="px-6 py-3 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="button in actionButtons"
                    :key="button.value"
                    @click="handleActionButton(button.value)"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ button.label }}
                </button>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 p-4 bg-white">
            <form @submit.prevent="handleSubmit" class="flex space-x-3">
                <input
                    v-model="inputMessage"
                    type="text"
                    placeholder="Type your answer..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    :disabled="isLoading"
                />
                <button
                    type="submit"
                    :disabled="!inputMessage.trim() || isLoading"
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                >
                    Send
                </button>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OnboardingChat',
    props: {
        messages: {
            type: Array,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        confirmedData: {
            type: Object,
            default: () => ({}),
        },
        actionButtons: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            inputMessage: '',
        };
    },
    watch: {
        messages() {
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
    },
    mounted() {
        this.scrollToBottom();
    },
    methods: {
        handleSubmit() {
            if (!this.inputMessage.trim() || this.isLoading) return;

            this.$emit('send-message', this.inputMessage.trim());
            this.inputMessage = '';
        },
        handleActionButton(value) {
            this.$emit('send-message', value);
        },
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
    },
};
</script>

<style scoped>
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}
</style>

