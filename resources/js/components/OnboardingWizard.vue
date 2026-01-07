<template>
    <div class="bg-white rounded-lg shadow-xl overflow-hidden flex flex-col h-[calc(100vh-2rem)] max-h-[900px]">
        <!-- Header -->
        <div class="bg-primary-600 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-semibold">Welcome to MarketPulse</h1>
                    <p class="text-sm text-primary-100">Let's get you set up</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    v-if="confirmedData.name"
                    @click="handleSkip"
                    class="px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 rounded-lg transition-colors"
                >
                    Skip for now
                </button>
                <button
                    @click="handleRestart"
                    class="px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 rounded-lg transition-colors"
                >
                    Restart
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Chat Panel -->
            <div class="flex-1 flex flex-col">
                <OnboardingChat
                    :messages="messages"
                    :is-loading="isLoading"
                    :confirmed-data="confirmedData"
                    :action-buttons="currentActionButtons"
                    @send-message="handleSendMessage"
                    @update-confirmed-data="handleUpdateConfirmedData"
                />
            </div>

            <!-- Sidebar with Confirmed Data -->
            <div class="w-80 border-l border-gray-200 bg-gray-50 overflow-y-auto">
                <div class="p-4">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Confirmed Information</h2>
                    <div class="space-y-3">
                        <DataCard
                            v-if="confirmedData.name"
                            label="Organization Name"
                            :value="confirmedData.name"
                            @edit="handleEditData('name')"
                        />
                        <DataCard
                            v-if="confirmedData.website"
                            label="Website"
                            :value="confirmedData.website"
                            @edit="handleEditData('website')"
                        />
                        <DataCard
                            v-if="confirmedData.focus"
                            label="Business Focus"
                            :value="confirmedData.focus"
                            @edit="handleEditData('focus')"
                        />
                        <DataCard
                            v-if="confirmedData.businessModel"
                            label="Business Model"
                            :value="confirmedData.businessModel"
                            @edit="handleEditData('businessModel')"
                        />
                        <div v-if="!hasConfirmedData" class="text-center py-8 text-gray-400 text-sm">
                            <p>No information confirmed yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <Transition name="toast">
        <div v-if="showSuccessToast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-medium">Setup complete! Redirecting...</span>
        </div>
    </Transition>

    <!-- Restart Confirmation Dialog -->
    <Transition name="modal">
        <div v-if="showRestartDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showRestartDialog = false">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Restart Onboarding?</h3>
                <p class="text-gray-600 mb-6">This will clear all your progress. Are you sure you want to restart?</p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showRestartDialog = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmRestart"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Restart
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import OnboardingChat from './OnboardingChat.vue';
import DataCard from './OnboardingDataCard.vue';
import axios from 'axios';

export default {
    name: 'OnboardingWizard',
    components: {
        OnboardingChat,
        DataCard,
    },
    data() {
        return {
            messages: [],
            isLoading: false,
            confirmedData: {
                name: null,
                website: null,
                focus: null,
                businessModel: null,
            },
            showSuccessToast: false,
            showRestartDialog: false,
            isCompleted: false,
            currentActionButtons: [],
        };
    },
    computed: {
        hasConfirmedData() {
            return this.confirmedData.name || this.confirmedData.website || this.confirmedData.focus || this.confirmedData.businessModel;
        },
    },
    mounted() {
        this.loadDraft();
        this.initializeConversation();
    },
    watch: {
        confirmedData: {
            deep: true,
            handler() {
                this.saveDraft();
            },
        },
        messages: {
            deep: true,
            handler() {
                this.saveDraft();
            },
        },
    },
    methods: {
        async initializeConversation() {
            if (this.messages.length === 0) {
                const welcomeMessage = {
                    role: 'assistant',
                    content: "Welcome to MarketPulse! I'm Jenna, your AI marketing partner. I'm here to help you get set up. First, what is the name of your organization?",
                    timestamp: new Date(),
                };
                this.messages.push(welcomeMessage);
            }
        },
        async handleSendMessage(message) {
            this.messages.push({
                role: 'user',
                content: message,
                timestamp: new Date(),
            });

            this.isLoading = true;

            try {
                const response = await axios.post('/api/v1/onboarding/chat', {
                    message: message,
                    confirmedData: this.confirmedData,
                    conversationHistory: this.messages.slice(0, -1).map(m => ({
                        role: m.role,
                        content: m.content,
                    })),
                });

                const aiResponse = response.data.data;

                this.messages.push({
                    role: 'assistant',
                    content: aiResponse.message,
                    timestamp: new Date(),
                });

                if (aiResponse.confirmedData) {
                    this.confirmedData = { ...this.confirmedData, ...aiResponse.confirmedData };
                }

                if (aiResponse.completed) {
                    await this.handleCompletion();
                }

                if (aiResponse.actionButtons) {
                    this.currentActionButtons = aiResponse.actionButtons;
                } else {
                    this.currentActionButtons = [];
                }
            } catch (error) {
                console.error('Error sending message:', error);
                this.messages.push({
                    role: 'assistant',
                    content: "I'm sorry, I encountered an error. Please try again.",
                    timestamp: new Date(),
                });
            } finally {
                this.isLoading = false;
            }
        },
        handleUpdateConfirmedData(data) {
            this.confirmedData = { ...this.confirmedData, ...data };
        },
        handleEditData(field) {
            const fieldLabels = {
                name: 'organization name',
                website: 'website URL',
                focus: 'business focus',
                businessModel: 'business model',
            };

            const editMessage = `I'd like to change the ${fieldLabels[field]}.`;
            this.handleSendMessage(editMessage);
        },
        async handleCompletion() {
            this.isCompleted = true;
            this.showSuccessToast = true;

            try {
                const response = await axios.post('/api/v1/onboarding/complete', {
                    confirmedData: this.confirmedData,
                });

                if (response.data.success && response.data.data.organizationId) {
                    localStorage.removeItem('onboarding_draft');
                    
                    this.triggerConfetti();
                    
                    setTimeout(() => {
                        window.location.href = `/main/${response.data.data.organizationId}/brands`;
                    }, 2000);
                }
            } catch (error) {
                console.error('Error completing onboarding:', error);
                this.showSuccessToast = false;
                this.messages.push({
                    role: 'assistant',
                    content: "I encountered an error completing your setup. Please try again or contact support.",
                    timestamp: new Date(),
                });
            }
        },
        triggerConfetti() {
            import('canvas-confetti').then((confetti) => {
                const duration = 3000;
                const animationEnd = Date.now() + duration;
                const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                const interval = setInterval(function() {
                    const timeLeft = animationEnd - Date.now();

                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }

                    const particleCount = 50 * (timeLeft / duration);
                    confetti.default({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
                    });
                    confetti.default({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
                    });
                }, 250);
            });
        },
        handleSkip() {
            if (this.confirmedData.name) {
                window.location.href = '/main/organizations';
            }
        },
        handleRestart() {
            this.showRestartDialog = true;
        },
        confirmRestart() {
            this.messages = [];
            this.confirmedData = {
                name: null,
                website: null,
                focus: null,
                businessModel: null,
            };
            this.showRestartDialog = false;
            localStorage.removeItem('onboarding_draft');
            this.initializeConversation();
        },
        saveDraft() {
            const draft = {
                messages: this.messages,
                confirmedData: this.confirmedData,
                timestamp: new Date().toISOString(),
            };
            localStorage.setItem('onboarding_draft', JSON.stringify(draft));
        },
        loadDraft() {
            const draftJson = localStorage.getItem('onboarding_draft');
            if (draftJson) {
                try {
                    const draft = JSON.parse(draftJson);
                    this.messages = draft.messages || [];
                    this.confirmedData = draft.confirmedData || {
                        name: null,
                        website: null,
                        focus: null,
                        businessModel: null,
                    };
                } catch (error) {
                    console.error('Error loading draft:', error);
                }
            }
        },
    },
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>

