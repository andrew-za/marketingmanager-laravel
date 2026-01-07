<?php

namespace App\Services;

use App\Models\ContentApproval;
use App\Models\ScheduledPost;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function getPendingReviewsForUser(int $organizationId, ?int $userId = null, int $limit = 10): Collection
    {
        $query = ContentApproval::where('organization_id', $organizationId)
            ->where('status', ContentApproval::STATUS_PENDING)
            ->with([
                'scheduledPost.campaign',
                'scheduledPost.channel',
                'scheduledPost.creator',
                'requestedBy',
            ]);

        if ($userId) {
            $query->where('approved_by', $userId);
        }

        return $query->orderBy('requested_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPendingReviewsForOrganization(int $organizationId, ?string $status = null, int $limit = 50): Collection
    {
        $query = ContentApproval::where('organization_id', $organizationId)
            ->whereHas('scheduledPost.campaign', function ($q) {
                $q->whereNotIn('status', ['inactive']);
            })
            ->with([
                'scheduledPost.campaign',
                'scheduledPost.channel',
                'scheduledPost.creator',
                'requestedBy',
                'approvedBy',
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('requested_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function approveContent(ContentApproval $approval, int $userId, ?string $comments = null): ContentApproval
    {
        return DB::transaction(function () use ($approval, $userId, $comments) {
            $approval->update([
                'status' => ContentApproval::STATUS_APPROVED,
                'approved_by' => $userId,
                'reviewed_at' => now(),
                'comments' => $comments ?? $approval->comments,
            ]);

            $approval->scheduledPost->update(['status' => 'approved']);

            return $approval->load(['scheduledPost', 'approvedBy']);
        });
    }

    public function rejectContent(ContentApproval $approval, int $userId, string $rejectionReason, ?string $comments = null): ContentApproval
    {
        return DB::transaction(function () use ($approval, $userId, $rejectionReason, $comments) {
            $approval->update([
                'status' => ContentApproval::STATUS_REJECTED,
                'approved_by' => $userId,
                'reviewed_at' => now(),
                'rejection_reason' => $rejectionReason,
                'comments' => $comments ?? $approval->comments,
            ]);

            $approval->scheduledPost->update(['status' => 'pending']);

            return $approval->load(['scheduledPost', 'approvedBy']);
        });
    }

    public function requestChanges(ContentApproval $approval, int $userId, string $reason, ?string $comments = null): ContentApproval
    {
        return DB::transaction(function () use ($approval, $userId, $reason, $comments) {
            $approval->update([
                'status' => ContentApproval::STATUS_CHANGES_REQUESTED,
                'approved_by' => $userId,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'comments' => $comments ?? $approval->comments,
            ]);

            $approval->scheduledPost->update(['status' => 'pending']);

            return $approval->load(['scheduledPost', 'approvedBy']);
        });
    }

    public function getReviewStats(int $organizationId): array
    {
        return [
            'pending' => ContentApproval::where('organization_id', $organizationId)
                ->where('status', ContentApproval::STATUS_PENDING)
                ->whereHas('scheduledPost.campaign', function ($q) {
                    $q->whereNotIn('status', ['inactive']);
                })
                ->count(),
            'approved' => ContentApproval::where('organization_id', $organizationId)
                ->where('status', ContentApproval::STATUS_APPROVED)
                ->count(),
            'rejected' => ContentApproval::where('organization_id', $organizationId)
                ->where('status', ContentApproval::STATUS_REJECTED)
                ->count(),
        ];
    }
}

