<template>
    <div class="review-container">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Content Review</h2>
                <p class="text-gray-600 mt-1">Review and approve content for publication</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select 
                        v-model="statusFilter"
                        @change="loadApprovals"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="changes_requested">Changes Requested</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
                    <select 
                        v-model="channelFilter"
                        @change="loadApprovals"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    >
                        <option value="">All Channels</option>
                        <option v-for="channel in availableChannels" :key="channel.id" :value="channel.id">
                            {{ channel.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <select 
                        v-model="authorFilter"
                        @change="loadApprovals"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    >
                        <option value="">All Authors</option>
                        <option v-for="author in availableAuthors" :key="author.id" :value="author.id">
                            {{ author.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <input 
                        type="date"
                        v-model="dateFilter"
                        @change="loadApprovals"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                </div>
            </div>
        </div>

        <!-- Queue Progress Indicator (for bulk review) -->
        <div v-if="bulkReviewMode && pendingQueue.length > 0" class="card mb-6 bg-blue-50 border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-sm font-medium text-blue-900">Bulk Review Mode</p>
                        <p class="text-xs text-blue-700">
                            Reviewing {{ currentQueueIndex + 1 }} of {{ pendingQueue.length }} items
                        </p>
                    </div>
                    <div class="flex-1 max-w-xs">
                        <div class="w-full bg-blue-200 rounded-full h-2">
                            <div 
                                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                :style="{ width: `${((currentQueueIndex + 1) / pendingQueue.length) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
                <button 
                    @click="exitBulkReview"
                    class="text-sm text-blue-700 hover:text-blue-900 font-medium"
                >
                    Exit Bulk Review
                </button>
            </div>
        </div>

        <!-- Content Table -->
        <div class="card">
            <div v-if="loading" class="text-center py-8">
                <p class="text-gray-500">Loading...</p>
            </div>
            <div v-else-if="approvals.length === 0" class="text-center py-8">
                <p class="text-gray-500">No content approvals found</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr 
                            v-for="approval in approvals" 
                            :key="approval.id"
                            :class="[
                                'hover:bg-gray-50',
                                bulkReviewMode && currentApproval?.id === approval.id ? 'bg-blue-50 border-l-4 border-blue-500' : ''
                            ]"
                        >
                            <td class="px-4 py-3 text-sm">
                                {{ approval.scheduled_post?.campaign?.name || 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span 
                                    v-if="approval.scheduled_post?.channel"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                                >
                                    {{ approval.scheduled_post.channel.name }}
                                </span>
                                <span v-else>N/A</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="max-w-xs truncate">
                                    {{ approval.scheduled_post?.content || 'No content' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span 
                                    :class="getStatusBadgeClass(approval.status)"
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                >
                                    {{ formatStatus(approval.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ approval.requested_by?.name || 'Unknown' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ formatDate(approval.requested_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button 
                                        @click="openReviewDialog(approval)"
                                        class="text-primary-600 hover:text-primary-800 text-sm font-medium"
                                    >
                                        Review
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-4 flex items-center justify-between px-4 py-3 border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="loadApprovals(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="btn btn-sm btn-secondary"
                    >
                        Previous
                    </button>
                    <button 
                        @click="loadApprovals(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="btn btn-sm btn-secondary"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Review Dialog -->
        <div 
            v-if="selectedApproval"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeReviewDialog"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-5xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold">Review Content</h3>
                        <p v-if="bulkReviewMode" class="text-sm text-gray-600 mt-1">
                            Item {{ currentQueueIndex + 1 }} of {{ pendingQueue.length }}
                        </p>
                    </div>
                    <button 
                        @click="closeReviewDialog"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Content Information -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Campaign:</span>
                            <span class="ml-2 font-medium">{{ selectedApproval.scheduled_post?.campaign?.name || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Channel:</span>
                            <span class="ml-2 font-medium">{{ selectedApproval.scheduled_post?.channel?.name || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Author:</span>
                            <span class="ml-2 font-medium">{{ selectedApproval.requested_by?.name || 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Requested:</span>
                            <span class="ml-2 font-medium">{{ formatDate(selectedApproval.requested_at) }}</span>
                        </div>
                    </div>

                    <!-- Platform Content Preview -->
                    <div v-if="selectedApproval.scheduled_post?.channel" class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-3">Platform Preview</h4>
                        <platform-content-preview
                            :content="selectedApproval.scheduled_post?.content || ''"
                            :image-url="getPrimaryImage(selectedApproval)"
                            :platforms="[getPlatformType(selectedApproval.scheduled_post?.channel?.platform)]"
                        />
                    </div>

                    <!-- Content Preview (fallback) -->
                    <div v-else class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Content</h4>
                        <div class="prose max-w-none">
                            <p class="whitespace-pre-wrap">{{ selectedApproval.scheduled_post?.content || 'No content' }}</p>
                        </div>
                    </div>

                    <!-- Attachments Preview -->
                    <div v-if="hasAttachments" class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-3">Attachments</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div 
                                v-for="(attachment, idx) in getAttachments(selectedApproval)" 
                                :key="idx"
                                class="border border-gray-200 rounded-lg p-2 cursor-pointer hover:border-primary-500 transition-colors"
                                @click="previewAttachment(attachment)"
                            >
                                <div v-if="isImage(attachment.url)" class="aspect-square bg-gray-100 rounded flex items-center justify-center overflow-hidden">
                                    <img :src="attachment.url" :alt="attachment.name" class="w-full h-full object-cover" />
                                </div>
                                <div v-else-if="isPdf(attachment.url)" class="aspect-square bg-red-100 rounded flex items-center justify-center">
                                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div v-else class="aspect-square bg-gray-100 rounded flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                </div>
                                <p class="text-xs mt-2 truncate text-center">{{ attachment.name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- PDF Annotation Viewer -->
                    <div v-if="hasPdfAnnotations" class="border border-gray-200 rounded-lg p-4 bg-yellow-50">
                        <h4 class="font-medium mb-2 text-yellow-900">PDF Annotations Detected</h4>
                        <p class="text-sm text-yellow-800 mb-3">
                            This PDF contains annotations. Please review them carefully before approving.
                        </p>
                        <div v-for="(annotation, idx) in getPdfAnnotations(selectedApproval)" :key="idx" class="mb-2 p-2 bg-white rounded border border-yellow-200">
                            <p class="text-sm font-medium">{{ annotation.type }}</p>
                            <p class="text-xs text-gray-600">{{ annotation.content }}</p>
                            <p class="text-xs text-gray-500 mt-1">Page {{ annotation.page }}</p>
                        </div>
                    </div>

                    <!-- Approval History -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Approval History</h4>
                        <div class="space-y-2">
                            <div 
                                v-for="history in approvalHistory" 
                                :key="history.id"
                                class="text-sm flex items-center gap-2"
                            >
                                <span class="font-medium">{{ history.approved_by?.name || history.requested_by?.name || 'Unknown' }}</span>
                                <span :class="getStatusBadgeClass(history.status)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                                    {{ formatStatus(history.status) }}
                                </span>
                                <span class="text-gray-400 text-xs">{{ formatDate(history.reviewed_at || history.requested_at) }}</span>
                                <span v-if="history.comments" class="text-gray-600 text-xs">- {{ history.comments }}</span>
                            </div>
                            <div v-if="approvalHistory.length === 0" class="text-sm text-gray-500">
                                No approval history available
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Comments</h4>
                        <textarea
                            v-model="reviewComments"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            rows="3"
                            placeholder="Add your comments..."
                        ></textarea>
                    </div>

                    <!-- Rejection Reason (shown when rejecting) -->
                    <div v-if="showRejectionReason" class="border border-red-200 rounded-lg p-4 bg-red-50">
                        <h4 class="font-medium mb-2 text-red-900">Rejection Reason <span class="text-red-600">*</span></h4>
                        <textarea
                            v-model="rejectionReason"
                            class="w-full px-3 py-2 border border-red-300 rounded-lg"
                            rows="3"
                            placeholder="Please provide a reason for rejection..."
                            required
                        ></textarea>
                    </div>

                    <!-- Actions -->
                    <div v-if="selectedApproval.status === 'pending'" class="flex gap-2 justify-between items-center pt-4 border-t">
                        <div class="flex gap-2">
                            <button 
                                v-if="bulkReviewMode"
                                @click="skipToNext"
                                class="btn btn-secondary"
                                :disabled="processing"
                            >
                                Skip
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="closeReviewDialog"
                                class="btn btn-secondary"
                                :disabled="processing"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="showRejectionReason = !showRejectionReason"
                                v-if="!showRejectionReason"
                                class="btn btn-danger"
                                :disabled="processing"
                            >
                                Reject
                            </button>
                            <button 
                                v-if="showRejectionReason"
                                @click="rejectApproval"
                                class="btn btn-danger"
                                :disabled="processing || !rejectionReason.trim()"
                            >
                                {{ processing ? 'Processing...' : 'Confirm Rejection' }}
                            </button>
                            <button 
                                @click="approveApproval"
                                class="btn btn-success"
                                :disabled="processing"
                            >
                                {{ processing ? 'Processing...' : 'Approve' }}
                            </button>
                            <button 
                                v-if="bulkReviewMode && hasNextInQueue"
                                @click="approveAndNext"
                                class="btn btn-primary"
                                :disabled="processing"
                            >
                                Approve & Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachment Preview Modal -->
        <div 
            v-if="previewAttachmentUrl"
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
            @click="previewAttachmentUrl = null"
        >
            <div class="max-w-4xl max-h-[90vh] overflow-auto bg-white rounded-lg p-4 m-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Attachment Preview</h3>
                    <button 
                        @click="previewAttachmentUrl = null"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="relative">
                    <img v-if="isImage(previewAttachmentUrl)" :src="previewAttachmentUrl" class="max-w-full h-auto" />
                    <iframe 
                        v-else-if="isPdf(previewAttachmentUrl)" 
                        :src="previewAttachmentUrl + '#toolbar=1'" 
                        class="w-full h-screen min-h-[600px]"
                    ></iframe>
                    <div v-else class="p-8 text-center">
                        <p class="text-gray-500">Preview not available for this file type</p>
                        <a :href="previewAttachmentUrl" target="_blank" class="text-primary-600 hover:text-primary-800 mt-2 inline-block">
                            Download file
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import PlatformContentPreview from './PlatformContentPreviewComponent.vue';

export default {
    name: 'ReviewComponent',
    components: {
        'platform-content-preview': PlatformContentPreview
    },
    props: {
        organizationId: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            approvals: [],
            loading: false,
            statusFilter: '',
            channelFilter: '',
            authorFilter: '',
            dateFilter: '',
            selectedApproval: null,
            approvalHistory: [],
            reviewComments: '',
            rejectionReason: '',
            showRejectionReason: false,
            processing: false,
            pagination: null,
            previewAttachmentUrl: null,
            echo: null,
            availableChannels: [],
            availableAuthors: [],
            bulkReviewMode: false,
            pendingQueue: [],
            currentQueueIndex: -1,
            currentApproval: null
        };
    },
    computed: {
        hasAttachments() {
            if (!this.selectedApproval?.scheduled_post) return false;
            const metadata = this.selectedApproval.scheduled_post.metadata || {};
            return metadata.attachments && metadata.attachments.length > 0;
        },
        hasPdfAnnotations() {
            if (!this.selectedApproval?.scheduled_post) return false;
            const metadata = this.selectedApproval.scheduled_post.metadata || {};
            return metadata.pdf_annotations && metadata.pdf_annotations.length > 0;
        },
        hasNextInQueue() {
            return this.bulkReviewMode && this.currentQueueIndex < this.pendingQueue.length - 1;
        }
    },
    mounted() {
        this.loadApprovals();
        this.setupBroadcasting();
    },
    beforeUnmount() {
        if (this.echo) {
            this.echo.disconnect();
        }
    },
    methods: {
        async loadApprovals(page = 1) {
            this.loading = true;
            try {
                const params = { page };
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.channelFilter) {
                    params.channel_id = this.channelFilter;
                }
                if (this.authorFilter) {
                    params.author_id = this.authorFilter;
                }
                if (this.dateFilter) {
                    params.date = this.dateFilter;
                }
                const response = await axios.get(`/main/${this.organizationId}/review`, { params });
                if (response.data.success) {
                    this.approvals = response.data.data.data || [];
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        from: response.data.data.from,
                        to: response.data.data.to,
                        total: response.data.data.total
                    };
                    
                    // Extract unique channels and authors for filters
                    this.extractFilters();
                }
            } catch (error) {
                console.error('Failed to load approvals:', error);
            } finally {
                this.loading = false;
            }
        },
        extractFilters() {
            const channels = new Map();
            const authors = new Map();
            
            this.approvals.forEach(approval => {
                if (approval.scheduled_post?.channel) {
                    channels.set(approval.scheduled_post.channel.id, approval.scheduled_post.channel);
                }
                if (approval.requested_by) {
                    authors.set(approval.requested_by.id, approval.requested_by);
                }
            });
            
            this.availableChannels = Array.from(channels.values());
            this.availableAuthors = Array.from(authors.values());
        },
        async openReviewDialog(approval) {
            this.selectedApproval = approval;
            this.reviewComments = approval.comments || '';
            this.rejectionReason = '';
            this.showRejectionReason = false;
            
            // Check if we should enter bulk review mode
            const pendingCount = this.approvals.filter(a => a.status === 'pending').length;
            if (pendingCount > 1 && approval.status === 'pending' && !this.bulkReviewMode) {
                this.startBulkReview(approval);
            }
            
            try {
                const response = await axios.get(
                    `/main/${this.organizationId}/review/scheduled-posts/${approval.scheduled_post_id}/history`
                );
                if (response.data.success) {
                    this.approvalHistory = response.data.data || [];
                }
            } catch (error) {
                console.error('Failed to load approval history:', error);
            }
        },
        startBulkReview(approval) {
            this.bulkReviewMode = true;
            this.pendingQueue = this.approvals.filter(a => a.status === 'pending');
            this.currentQueueIndex = this.pendingQueue.findIndex(a => a.id === approval.id);
            this.currentApproval = approval;
        },
        exitBulkReview() {
            this.bulkReviewMode = false;
            this.pendingQueue = [];
            this.currentQueueIndex = -1;
            this.currentApproval = null;
        },
        async skipToNext() {
            if (this.hasNextInQueue) {
                this.currentQueueIndex++;
                const nextApproval = this.pendingQueue[this.currentQueueIndex];
                await this.openReviewDialog(nextApproval);
            } else {
                this.exitBulkReview();
                this.closeReviewDialog();
            }
        },
        async approveAndNext() {
            await this.approveApproval(false);
            if (this.hasNextInQueue) {
                this.currentQueueIndex++;
                const nextApproval = this.pendingQueue[this.currentQueueIndex];
                await this.openReviewDialog(nextApproval);
            } else {
                this.exitBulkReview();
                this.closeReviewDialog();
            }
        },
        closeReviewDialog() {
            this.selectedApproval = null;
            this.reviewComments = '';
            this.rejectionReason = '';
            this.showRejectionReason = false;
            if (!this.bulkReviewMode) {
                this.currentApproval = null;
            }
        },
        async approveApproval(closeDialog = true) {
            if (!this.selectedApproval) return;

            this.processing = true;
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${this.selectedApproval.id}/approve`,
                    { comments: this.reviewComments }
                );
                if (response.data.success) {
                    await this.loadApprovals();
                    if (closeDialog) {
                        this.closeReviewDialog();
                    }
                    // Update queue if in bulk mode
                    if (this.bulkReviewMode) {
                        this.pendingQueue = this.pendingQueue.filter(a => a.id !== this.selectedApproval.id);
                        if (this.currentQueueIndex >= this.pendingQueue.length) {
                            this.currentQueueIndex = Math.max(0, this.pendingQueue.length - 1);
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to approve:', error);
                alert('Failed to approve. Please try again.');
            } finally {
                this.processing = false;
            }
        },
        async rejectApproval() {
            if (!this.selectedApproval) return;
            
            if (!this.rejectionReason.trim()) {
                alert('Please provide a rejection reason.');
                return;
            }

            this.processing = true;
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${this.selectedApproval.id}/reject`,
                    { 
                        rejection_reason: this.rejectionReason,
                        comments: this.reviewComments
                    }
                );
                if (response.data.success) {
                    await this.loadApprovals();
                    this.closeReviewDialog();
                    // Update queue if in bulk mode
                    if (this.bulkReviewMode) {
                        this.pendingQueue = this.pendingQueue.filter(a => a.id !== this.selectedApproval.id);
                        if (this.currentQueueIndex >= this.pendingQueue.length) {
                            this.currentQueueIndex = Math.max(0, this.pendingQueue.length - 1);
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to reject:', error);
                alert('Failed to reject. Please try again.');
            } finally {
                this.processing = false;
            }
        },
        getAttachments(approval) {
            if (!approval?.scheduled_post?.metadata) return [];
            return approval.scheduled_post.metadata.attachments || [];
        },
        getPdfAnnotations(approval) {
            if (!approval?.scheduled_post?.metadata) return [];
            return approval.scheduled_post.metadata.pdf_annotations || [];
        },
        getPrimaryImage(approval) {
            const attachments = this.getAttachments(approval);
            const imageAttachment = attachments.find(a => this.isImage(a.url));
            return imageAttachment?.url || null;
        },
        getPlatformType(platform) {
            if (!platform) return 'generic';
            const platformMap = {
                'facebook': 'facebook',
                'instagram': 'instagram',
                'twitter': 'twitter',
                'linkedin': 'linkedin',
                'tiktok': 'tiktok',
                'pinterest': 'pinterest'
            };
            return platformMap[platform.toLowerCase()] || 'generic';
        },
        previewAttachment(attachment) {
            this.previewAttachmentUrl = attachment.url;
        },
        isImage(url) {
            if (!url) return false;
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'];
            return imageExtensions.some(ext => url.toLowerCase().includes(ext));
        },
        isPdf(url) {
            if (!url) return false;
            return url.toLowerCase().includes('.pdf') || url.toLowerCase().includes('application/pdf');
        },
        getStatusBadgeClass(status) {
            const classes = {
                pending: 'bg-yellow-100 text-yellow-800',
                approved: 'bg-green-100 text-green-800',
                rejected: 'bg-red-100 text-red-800',
                changes_requested: 'bg-blue-100 text-blue-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        formatStatus(status) {
            return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        setupBroadcasting() {
            if (typeof window.Echo !== 'undefined') {
                this.echo = window.Echo;
                
                const channel = this.echo.join(`organization.${this.organizationId}`);
                
                channel.listen('.approval.status.changed', () => {
                    this.loadApprovals();
                });
            }
        }
    }
};
</script>

<style scoped>
.prose {
    color: #374151;
}

.review-container {
    max-width: 100%;
}

.card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-secondary {
    background-color: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #d1d5db;
}

.btn-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background-color: #dc2626;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background-color: #059669;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #2563eb;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
