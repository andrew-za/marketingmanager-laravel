<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentResource;
use App\Models\ScheduledPost;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Content API Controller
 */
class ContentController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * List content items
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        $brandId = $request->query('brand_id');

        $content = ScheduledPost::where('organization_id', $organizationId)
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->with(['campaign', 'channels', 'creator'])
            ->paginate();

        return ContentResource::collection($content);
    }

    /**
     * Get content details
     */
    public function show(Request $request, ScheduledPost $content)
    {
        $this->authorize('view', $content);

        return response()->json([
            'success' => true,
            'data' => new ContentResource($content->load(['campaign', 'channels', 'creator', 'approvals'])),
            'message' => 'Content retrieved successfully',
        ]);
    }

    /**
     * Approve content
     */
    public function approve(Request $request, ScheduledPost $content)
    {
        $this->authorize('approve', $content);

        $this->reviewService->approveContent($content, $request->user(), $request->input('comment'));

        return response()->json([
            'success' => true,
            'data' => new ContentResource($content->fresh()),
            'message' => 'Content approved successfully',
        ]);
    }

    /**
     * Reject content
     */
    public function reject(Request $request, ScheduledPost $content)
    {
        $this->authorize('approve', $content);

        $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $this->reviewService->rejectContent($content, $request->user(), $request->input('comment'));

        return response()->json([
            'success' => true,
            'data' => new ContentResource($content->fresh()),
            'message' => 'Content rejected successfully',
        ]);
    }
}

