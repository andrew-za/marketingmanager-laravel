<?php

namespace App\Http\Controllers;

use App\Models\ContentApproval;
use App\Models\ScheduledPost;
use App\Models\Organization;
use App\Policies\ReviewPolicy;
use App\Notifications\ContentApprovalRequested;
use App\Notifications\ContentApproved;
use App\Notifications\ContentRejected;
use App\Events\ContentApprovalStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentApprovalController extends Controller
{
    public function index(Request $request, string $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);
        $policy = new ReviewPolicy();
        if (!$policy->viewAny($request->user(), $organization)) {
            abort(403, 'You do not have permission to view content approvals.');
        }

        $query = ContentApproval::where('organization_id', $organizationId)
            ->whereHas('scheduledPost.campaign', function ($q) {
                $q->whereNotIn('status', ['inactive']);
            });

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $approvals = $query->with(['scheduledPost.campaign', 'scheduledPost.channel', 'requestedBy', 'approvedBy'])
            ->orderBy('requested_at', 'desc')
            ->paginate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $approvals,
            ]);
        }

        return view('review.index', [
            'organizationId' => $organizationId,
        ]);
    }

    public function requestApproval(Request $request, string $organizationId, ScheduledPost $scheduledPost): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $policy = new ReviewPolicy();
        if (!$policy->requestApproval($request->user(), $organization)) {
            abort(403, 'You do not have permission to request content approval.');
        }

        if ($scheduledPost->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled post not found.',
            ], 404);
        }

        $validated = $request->validate([
            'approved_by' => 'required|exists:users,id',
            'comments' => 'nullable|string',
        ]);

        $approval = ContentApproval::create([
            'scheduled_post_id' => $scheduledPost->id,
            'organization_id' => $organizationId,
            'requested_by' => $request->user()->id,
            'approved_by' => $validated['approved_by'],
            'status' => ContentApproval::STATUS_PENDING,
            'comments' => $validated['comments'] ?? null,
            'requested_at' => now(),
        ]);

        $scheduledPost->update(['status' => 'pending']);

        $approver = $approval->approvedBy;
        $approver->notify(new ContentApprovalRequested($approval, $scheduledPost));

        return response()->json([
            'success' => true,
            'data' => $approval->load(['scheduledPost', 'approvedBy', 'requestedBy']),
            'message' => 'Approval request created successfully.',
        ], 201);
    }

    public function approve(Request $request, string $organizationId, ContentApproval $approval): JsonResponse
    {
        if ($approval->scheduledPost->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        $this->authorize('approve', $approval);

        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        $approval->update([
            'status' => ContentApproval::STATUS_APPROVED,
            'reviewed_at' => now(),
            'comments' => $validated['comments'] ?? $approval->comments,
        ]);

        $scheduledPost = $approval->scheduledPost;
        $scheduledPost->update(['status' => 'approved']);

        $creator = $scheduledPost->creator;
        $creator->notify(new ContentApproved($approval, $scheduledPost));

        broadcast(new ContentApprovalStatusChanged($approval->fresh()))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $approval->load(['scheduledPost', 'approvedBy', 'requestedBy']),
            'message' => 'Content approved successfully.',
        ]);
    }

    public function reject(Request $request, string $organizationId, ContentApproval $approval): JsonResponse
    {
        if ($approval->scheduledPost->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Approval not found.',
            ], 404);
        }

        $this->authorize('reject', $approval);

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
            'comments' => 'nullable|string',
        ]);

        $approval->update([
            'status' => ContentApproval::STATUS_REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'comments' => $validated['comments'] ?? $approval->comments,
        ]);

        $scheduledPost = $approval->scheduledPost;
        $scheduledPost->update(['status' => 'pending']);

        $creator = $scheduledPost->creator;
        $creator->notify(new ContentRejected($approval, $scheduledPost));

        broadcast(new ContentApprovalStatusChanged($approval->fresh()))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $approval->load(['scheduledPost', 'approvedBy', 'requestedBy']),
            'message' => 'Content rejected successfully.',
        ]);
    }

    public function show(Request $request, string $organizationId, ContentApproval $approval)
    {
        if ($approval->scheduledPost->organization_id != $organizationId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found.',
                ], 404);
            }
            abort(404);
        }

        $this->authorize('view', $approval);

        $approval->load(['scheduledPost.campaign', 'scheduledPost.channel', 'approvedBy', 'requestedBy']);
        
        $approvalHistory = ContentApproval::where('scheduled_post_id', $approval->scheduled_post_id)
            ->with(['approvedBy', 'requestedBy'])
            ->orderBy('requested_at', 'desc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $approval,
            ]);
        }

        return view('content-approvals.show', [
            'approval' => $approval,
            'approvalHistory' => $approvalHistory,
            'organizationId' => $organizationId,
        ]);
    }

    public function getApprovalHistory(Request $request, string $organizationId, ScheduledPost $scheduledPost): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $policy = new ReviewPolicy();
        if (!$policy->viewHistory($request->user(), $organization)) {
            abort(403, 'You do not have permission to view approval history.');
        }

        if ($scheduledPost->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled post not found.',
            ], 404);
        }

        $approvals = ContentApproval::where('scheduled_post_id', $scheduledPost->id)
            ->with(['approvedBy', 'requestedBy'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }
}

