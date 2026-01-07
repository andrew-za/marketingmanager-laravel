<?php

namespace App\Services\Admin;

use App\Models\ModerationQueue;
use App\Models\ContentFlag;
use App\Models\ScheduledPost;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ContentModerationService
{
    /**
     * Get moderation queue items
     */
    public function getModerationQueue(array $filters = [], int $perPage = 15)
    {
        $query = ModerationQueue::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->with(['moderatable', 'flaggedBy', 'reviewedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Flag content for moderation
     */
    public function flagContent($moderatable, string $type, ?int $flaggedBy = null, ?string $reason = null): ModerationQueue
    {
        $flaggedBy = $flaggedBy ?? auth()->id();

        $moderationItem = ModerationQueue::create([
            'moderatable_type' => get_class($moderatable),
            'moderatable_id' => $moderatable->id,
            'type' => $type,
            'status' => 'pending',
            'flagged_by' => $flaggedBy,
            'reason' => $reason,
        ]);

        ActivityLog::log('content_flagged', $moderatable, auth()->user(), [
            'type' => $type,
            'reason' => $reason,
        ], "Content flagged for moderation");

        return $moderationItem;
    }

    /**
     * Flag scheduled post
     */
    public function flagScheduledPost(ScheduledPost $post, string $reason, ?string $description = null, ?int $flaggedBy = null): ContentFlag
    {
        $flaggedBy = $flaggedBy ?? auth()->id();

        $flag = ContentFlag::create([
            'scheduled_post_id' => $post->id,
            'organization_id' => $post->organization_id,
            'flagged_by' => $flaggedBy,
            'reason' => $reason,
            'description' => $description,
            'status' => 'pending',
        ]);

        $this->flagContent($post, 'content', $flaggedBy, $reason);

        ActivityLog::log('post_flagged', $post, auth()->user(), [
            'reason' => $reason,
        ], "Scheduled post flagged for moderation");

        return $flag;
    }

    /**
     * Approve content
     */
    public function approveContent(ModerationQueue $moderationItem, ?string $notes = null): ModerationQueue
    {
        DB::transaction(function () use ($moderationItem, $notes) {
            $moderationItem->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $moderatable = $moderationItem->moderatable;
            if ($moderatable && $moderatable instanceof ScheduledPost) {
                $moderatable->update(['status' => 'approved']);
            }

            ActivityLog::log('content_approved', $moderatable, auth()->user(), [
                'notes' => $notes,
            ], "Content approved in moderation queue");
        });

        return $moderationItem->fresh();
    }

    /**
     * Reject content
     */
    public function rejectContent(ModerationQueue $moderationItem, ?string $reason = null): ModerationQueue
    {
        DB::transaction(function () use ($moderationItem, $reason) {
            $moderationItem->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'reason' => $reason ?? $moderationItem->reason,
            ]);

            $moderatable = $moderationItem->moderatable;
            if ($moderatable && $moderatable instanceof ScheduledPost) {
                $moderatable->update(['status' => 'rejected']);
            }

            ActivityLog::log('content_rejected', $moderatable, auth()->user(), [
                'reason' => $reason,
            ], "Content rejected in moderation queue");
        });

        return $moderationItem->fresh();
    }

    /**
     * Delete content
     */
    public function deleteContent(ModerationQueue $moderationItem): void
    {
        DB::transaction(function () use ($moderationItem) {
            $content = $moderationItem->moderatable;

            if ($content) {
                ActivityLog::log('content_deleted', $content, auth()->user(), [], "Content deleted from moderation queue");
                $content->delete();
            }

            $moderationItem->delete();
        });
    }

    /**
     * Review content flag
     */
    public function reviewContentFlag(ContentFlag $flag, string $status, ?string $notes = null): ContentFlag
    {
        $flag->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($status === 'resolved' && $flag->scheduledPost) {
            $flag->scheduledPost->update(['status' => 'approved']);
        }

        ActivityLog::log('flag_reviewed', $flag->scheduledPost, auth()->user(), [
            'status' => $status,
            'notes' => $notes,
        ], "Content flag reviewed");

        return $flag->fresh();
    }
}

