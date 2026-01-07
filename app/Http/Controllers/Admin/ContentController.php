<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModerateContentRequest;
use App\Http\Requests\Admin\FlagContentRequest;
use App\Models\ModerationQueue;
use App\Models\ContentFlag;
use App\Models\ScheduledPost;
use App\Services\Admin\ContentModerationService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private ContentModerationService $moderationService
    ) {
    }

    /**
     * Display content moderation queue
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type']);
        $moderationQueue = $this->moderationService->getModerationQueue($filters);
        $contentFlags = ContentFlag::with(['scheduledPost', 'flaggedBy', 'reviewedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.content.index', compact('moderationQueue', 'contentFlags', 'filters'));
    }

    /**
     * Flag content for moderation
     */
    public function flag(FlagContentRequest $request, ScheduledPost $post)
    {
        $this->moderationService->flagScheduledPost(
            $post,
            $request->validated()['reason'],
            $request->validated()['description'] ?? null
        );

        return redirect()->back()
            ->with('success', 'Content flagged for moderation.');
    }

    /**
     * Approve content
     */
    public function approve(ModerateContentRequest $request, ModerationQueue $moderation)
    {
        if ($request->validated()['status'] === 'approved') {
            $this->moderationService->approveContent(
                $moderation,
                $request->validated()['notes'] ?? null
            );
        } else {
            $this->moderationService->rejectContent(
                $moderation,
                $request->validated()['reason'] ?? null
            );
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Content moderation status updated.');
    }

    /**
     * Reject content
     */
    public function reject(ModerateContentRequest $request, ModerationQueue $moderation)
    {
        $this->moderationService->rejectContent(
            $moderation,
            $request->validated()['reason'] ?? null
        );

        return redirect()->route('admin.content.index')
            ->with('success', 'Content rejected.');
    }

    /**
     * Delete content
     */
    public function destroy(ModerationQueue $moderation)
    {
        $this->moderationService->deleteContent($moderation);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content deleted.');
    }

    /**
     * Review content flag
     */
    public function reviewFlag(Request $request, ContentFlag $flag)
    {
        $request->validate([
            'status' => ['required', 'in:reviewed,resolved,dismissed'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $this->moderationService->reviewContentFlag(
            $flag,
            $request->validated()['status'],
            $request->validated()['notes'] ?? null
        );

        return redirect()->route('admin.content.index')
            ->with('success', 'Content flag reviewed.');
    }
}

