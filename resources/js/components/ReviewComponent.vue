<template>
    <div class="review-container">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Content Review</h2>
                <p class="text-gray-600 mt-1">Review and approve content for publication</p>
            </div>
            <div class="flex gap-2">
                <select 
                    v-model="statusFilter"
                    @change="loadApprovals"
                    class="px-4 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="changes_requested">Changes Requested</option>
                </select>
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
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-sm">
                                {{ approval.scheduled_post?.campaign?.name || 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ approval.scheduled_post?.channel?.name || 'N/A' }}
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
                                {{ approval.requested_by_user?.name || 'Unknown' }}
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
            @click.self="selectedApproval = null"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold">Review Content</h3>
                    <button 
                        @click="selectedApproval = null"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Content Preview -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Content</h4>
                        <div class="prose max-w-none">
                            <p class="whitespace-pre-wrap">{{ selectedApproval.scheduled_post?.content || 'No content' }}</p>
                        </div>
                    </div>

                    <!-- Attachments Preview -->
                    <div v-if="hasAttachments" class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Attachments</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div 
                                v-for="(attachment, idx) in getAttachments(selectedApproval)" 
                                :key="idx"
                                class="border border-gray-200 rounded-lg p-2 cursor-pointer hover:border-primary-500"
                                @click="previewAttachment(attachment)"
                            >
                                <div v-if="isImage(attachment.url)" class="aspect-square bg-gray-100 rounded flex items-center justify-center overflow-hidden">
                                    <img :src="attachment.url" :alt="attachment.name" class="w-full h-full object-cover" />
                                </div>
                                <div v-else-if="isPdf(attachment.url)" class="aspect-square bg-red-100 rounded flex items-center justify-center">
                                    <span class="text-red-600 text-2xl">📄</span>
                                </div>
                                <div v-else class="aspect-square bg-gray-100 rounded flex items-center justify-center">
                                    <span class="text-gray-600 text-2xl">📎</span>
                                </div>
                                <p class="text-xs mt-2 truncate">{{ attachment.name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Approval History -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium mb-2">Approval History</h4>
                        <div class="space-y-2">
                            <div 
                                v-for="history in approvalHistory" 
                                :key="history.id"
                                class="text-sm"
                            >
                                <span class="font-medium">{{ history.approved_by_user?.name || 'Unknown' }}</span>
                                <span class="text-gray-600">{{ formatStatus(history.status) }}</span>
                                <span class="text-gray-400 text-xs ml-2">{{ formatDate(history.reviewed_at || history.requested_at) }}</span>
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

                    <!-- Actions -->
                    <div v-if="selectedApproval.status === 'pending'" class="flex gap-2 justify-end">
                        <button 
                            @click="selectedApproval = null"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="rejectApproval"
                            class="btn btn-danger"
                            :disabled="processing"
                        >
                            {{ processing ? 'Processing...' : 'Reject' }}
                        </button>
                        <button 
                            @click="approveApproval"
                            class="btn btn-success"
                            :disabled="processing"
                        >
                            {{ processing ? 'Processing...' : 'Approve' }}
                        </button>
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
            <div class="max-w-4xl max-h-[90vh] overflow-auto bg-white rounded-lg p-4">
                <div class="flex justify-end mb-2">
                    <button 
                        @click="previewAttachmentUrl = null"
                        class="text-white hover:text-gray-300"
                    >
                        ✕
                    </button>
                </div>
                <img v-if="isImage(previewAttachmentUrl)" :src="previewAttachmentUrl" class="max-w-full" />
                <iframe v-else-if="isPdf(previewAttachmentUrl)" :src="previewAttachmentUrl" class="w-full h-screen"></iframe>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ReviewComponent',
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
            selectedApproval: null,
            approvalHistory: [],
            reviewComments: '',
            processing: false,
            pagination: null,
            previewAttachmentUrl: null,
            echo: null
        };
    },
    computed: {
        hasAttachments() {
            if (!this.selectedApproval?.scheduled_post) return false;
            const metadata = this.selectedApproval.scheduled_post.metadata || {};
            return metadata.attachments && metadata.attachments.length > 0;
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
                }
            } catch (error) {
                console.error('Failed to load approvals:', error);
            } finally {
                this.loading = false;
            }
        },
        async openReviewDialog(approval) {
            this.selectedApproval = approval;
            this.reviewComments = approval.comments || '';
            
            try {
                const response = await axios.get(
                    `/main/${this.organizationId}/review/${approval.scheduled_post_id}/history`
                );
                if (response.data.success) {
                    this.approvalHistory = response.data.data || [];
                }
            } catch (error) {
                console.error('Failed to load approval history:', error);
            }
        },
        async approveApproval() {
            if (!this.selectedApproval) return;

            this.processing = true;
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${this.selectedApproval.id}/approve`,
                    { comments: this.reviewComments }
                );
                if (response.data.success) {
                    await this.loadApprovals();
                    this.selectedApproval = null;
                    this.reviewComments = '';
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

            const reason = prompt('Please provide a rejection reason:');
            if (!reason) return;

            this.processing = true;
            try {
                const response = await axios.post(
                    `/main/${this.organizationId}/review/${this.selectedApproval.id}/reject`,
                    { 
                        rejection_reason: reason,
                        comments: this.reviewComments
                    }
                );
                if (response.data.success) {
                    await this.loadApprovals();
                    this.selectedApproval = null;
                    this.reviewComments = '';
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
            return url.toLowerCase().includes('.pdf');
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
</style>

