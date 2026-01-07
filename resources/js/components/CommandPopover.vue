<template>
    <div class="fixed bottom-6 right-6 z-50">
        <button
            @click="toggleDialog"
            class="bg-primary-600 text-white rounded-full p-4 shadow-lg hover:bg-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            aria-label="Open AI Assistant"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </button>

        <div
            v-if="isOpen"
            v-click-outside="closeDialog"
            class="absolute bottom-20 right-0 w-96 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden"
        >
            <div class="bg-primary-600 text-white p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">AI Assistant</h3>
                    <button
                        @click="closeDialog"
                        class="text-white hover:text-gray-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-4 max-h-96 overflow-y-auto">
                <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                    <p>Ask me anything about your marketing campaigns!</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        :class="[
                            'flex',
                            message.role === 'user' ? 'justify-end' : 'justify-start'
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[80%] rounded-lg px-4 py-2',
                                message.role === 'user'
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-gray-100 text-gray-900'
                            ]"
                        >
                            <p class="text-sm">{{ message.content }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 p-4">
                <form @submit.prevent="sendMessage" class="flex space-x-2">
                    <input
                        v-model="inputMessage"
                        type="text"
                        placeholder="Type your question..."
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    />
                    <button
                        type="submit"
                        :disabled="!inputMessage.trim()"
                        class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CommandPopover',
    data() {
        return {
            isOpen: false,
            inputMessage: '',
            messages: []
        };
    },
    directives: {
        'click-outside': {
            mounted(el, binding) {
                el.clickOutsideEvent = (event) => {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value();
                    }
                };
                document.addEventListener('click', el.clickOutsideEvent);
            },
            unmounted(el) {
                document.removeEventListener('click', el.clickOutsideEvent);
            }
        }
    },
    methods: {
        toggleDialog() {
            this.isOpen = !this.isOpen;
        },
        closeDialog() {
            this.isOpen = false;
        },
        async sendMessage() {
            if (!this.inputMessage.trim()) return;

            const userMessage = {
                role: 'user',
                content: this.inputMessage
            };

            this.messages.push(userMessage);
            const currentMessage = this.inputMessage;
            this.inputMessage = '';

            // TODO: Integrate with AI service API
            // For now, just echo back
            setTimeout(() => {
                this.messages.push({
                    role: 'assistant',
                    content: `I received your message: "${currentMessage}". AI integration coming soon!`
                });
            }, 500);
        }
    }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>

