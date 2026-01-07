<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ScheduledPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentManagementController extends Controller
{
    public function index(Request $request, string $organizationId, Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        $scheduledPosts = $campaign->scheduledPosts()
            ->with(['channel', 'creator', 'approvals'])
            ->orderBy('scheduled_at')
            ->paginate();

        return response()->json([
            'success' => true,
            'data' => $scheduledPosts,
        ]);
    }

    public function store(Request $request, string $organizationId, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        // Handle bulk posts creation
        if ($request->has('posts') && is_array($request->posts)) {
            $validated = $request->validate([
                'posts' => 'required|array',
                'posts.*.channel' => 'required|string',
                'posts.*.platform' => 'required|string',
                'posts.*.content' => 'required|string',
                'posts.*.hashtags' => 'nullable|array',
                'posts.*.scheduledAt' => 'nullable|date',
            ]);

            $scheduledPosts = [];
            $channels = \App\Models\Channel::where('organization_id', $organizationId)
                ->get()
                ->keyBy(function ($channel) {
                    return strtolower($channel->platform);
                });

            foreach ($validated['posts'] as $postData) {
                $channel = $channels->get(strtolower($postData['platform']));
                if (!$channel) continue;

                $scheduledPost = ScheduledPost::create([
                    'organization_id' => $organizationId,
                    'campaign_id' => $campaign->id,
                    'channel_id' => $channel->id,
                    'content' => $postData['content'],
                    'scheduled_at' => $postData['scheduledAt'] ?? now()->addDay(),
                    'status' => 'pending',
                    'metadata' => [
                        'hashtags' => $postData['hashtags'] ?? [],
                        'image_suggestions' => $postData['imageSuggestions'] ?? [],
                    ],
                    'created_by' => $request->user()->id,
                ]);

                $scheduledPosts[] = $scheduledPost->load(['channel', 'creator']);
            }

            return response()->json([
                'success' => true,
                'data' => $scheduledPosts,
                'message' => 'Content scheduled successfully.',
            ], 201);
        }

        // Handle single post creation
        $validated = $request->validate([
            'channel_id' => 'required|exists:channels,id',
            'content' => 'required|string',
            'scheduled_at' => 'required|date',
            'metadata' => 'nullable|array',
        ]);

        $scheduledPost = ScheduledPost::create([
            'organization_id' => $organizationId,
            'campaign_id' => $campaign->id,
            'channel_id' => $validated['channel_id'],
            'content' => $validated['content'],
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'pending',
            'metadata' => $validated['metadata'] ?? [],
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $scheduledPost->load(['channel', 'creator']),
            'message' => 'Content scheduled successfully.',
        ], 201);
    }

    public function update(Request $request, string $organizationId, Campaign $campaign, ScheduledPost $scheduledPost): JsonResponse
    {
        $this->authorize('update', $campaign);

        if ($scheduledPost->campaign_id !== $campaign->id) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled post does not belong to this campaign.',
            ], 404);
        }

        $validated = $request->validate([
            'content' => 'sometimes|required|string',
            'scheduled_at' => 'sometimes|required|date',
            'status' => 'sometimes|in:pending,approved,published,failed',
            'metadata' => 'nullable|array',
        ]);

        $scheduledPost->update($validated);

        return response()->json([
            'success' => true,
            'data' => $scheduledPost->load(['channel', 'creator']),
            'message' => 'Content updated successfully.',
        ]);
    }

    public function destroy(string $organizationId, Campaign $campaign, ScheduledPost $scheduledPost): JsonResponse
    {
        $this->authorize('update', $campaign);

        if ($scheduledPost->campaign_id !== $campaign->id) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled post does not belong to this campaign.',
            ], 404);
        }

        $scheduledPost->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully.',
        ]);
    }
}


