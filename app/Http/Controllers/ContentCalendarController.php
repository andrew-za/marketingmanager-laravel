<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\ScheduledPost;
use App\Services\Scheduling\RecurringScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContentCalendarController extends Controller
{
    public function __construct(
        private RecurringScheduleService $recurringScheduleService
    ) {}
    public function index(Request $request, string $organizationId)
    {
        $startDate = $request->get('start') ? Carbon::parse($request->get('start')) : now()->startOfMonth();
        $endDate = $request->get('end') ? Carbon::parse($request->get('end')) : now()->endOfMonth();

        $events = CalendarEvent::where('organization_id', $organizationId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with(['campaign', 'scheduledPost', 'creator'])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'type' => $event->type,
                    'start' => $event->start_time->toIso8601String(),
                    'end' => $event->end_time?->toIso8601String(),
                    'allDay' => $event->is_all_day,
                    'timezone' => $event->timezone,
                    'campaign_id' => $event->campaign_id,
                    'scheduled_post_id' => $event->scheduled_post_id,
                ];
            });

        $scheduledPosts = ScheduledPost::where('organization_id', $organizationId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['campaign', 'channel', 'creator'])
            ->get()
            ->map(function ($post) {
                return [
                    'id' => 'post_' . $post->id,
                    'title' => $post->campaign?->name ?? 'Scheduled Post',
                    'description' => substr($post->content, 0, 100),
                    'type' => 'post',
                    'start' => $post->scheduled_at->toIso8601String(),
                    'end' => $post->scheduled_at->copy()->addMinutes(30)->toIso8601String(),
                    'allDay' => false,
                    'campaign_id' => $post->campaign_id,
                    'scheduled_post_id' => $post->id,
                    'status' => $post->status,
                ];
            });

        $data = $events->merge($scheduledPosts)->values();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('content-calendar.index', [
            'organizationId' => $organizationId,
        ]);
    }

    public function store(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:post,campaign,meeting,deadline,reminder,custom',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'timezone' => 'sometimes|string',
            'is_all_day' => 'boolean',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'scheduled_post_id' => 'nullable|exists:scheduled_posts,id',
            'metadata' => 'nullable|array',
        ]);

        $event = CalendarEvent::create([
            ...$validated,
            'organization_id' => $organizationId,
            'created_by' => $request->user()->id,
            'timezone' => $validated['timezone'] ?? $request->user()->timezone ?? 'UTC',
        ]);

        return response()->json([
            'success' => true,
            'data' => $event->load(['campaign', 'scheduledPost', 'creator']),
            'message' => 'Calendar event created successfully.',
        ], 201);
    }

    public function update(Request $request, string $organizationId, CalendarEvent $event): JsonResponse
    {
        if ($event->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:post,campaign,meeting,deadline,reminder,custom',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'nullable|date|after:start_time',
            'timezone' => 'sometimes|string',
            'is_all_day' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'data' => $event->load(['campaign', 'scheduledPost', 'creator']),
            'message' => 'Calendar event updated successfully.',
        ]);
    }

    public function destroy(string $organizationId, CalendarEvent $event): JsonResponse
    {
        if ($event->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully.',
        ]);
    }

    public function bulkSchedule(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'posts' => 'required|array|min:1',
            'posts.*.channel_id' => 'required|exists:channels,id',
            'posts.*.content' => 'required|string',
            'posts.*.scheduled_at' => 'required|date',
            'posts.*.campaign_id' => 'nullable|exists:campaigns,id',
            'posts.*.metadata' => 'nullable|array',
        ]);

        $scheduledPosts = [];
        foreach ($validated['posts'] as $postData) {
            $scheduledPosts[] = ScheduledPost::create([
                'organization_id' => $organizationId,
                'campaign_id' => $postData['campaign_id'] ?? null,
                'channel_id' => $postData['channel_id'],
                'content' => $postData['content'],
                'scheduled_at' => $postData['scheduled_at'],
                'status' => 'pending',
                'metadata' => $postData['metadata'] ?? [],
                'created_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $scheduledPosts,
            'message' => count($scheduledPosts) . ' posts scheduled successfully.',
        ], 201);
    }

    public function createRecurringSchedule(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:channels,id',
            'content' => 'required|string',
            'scheduled_at' => 'required|date',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'metadata' => 'nullable|array',
            'recurrence' => 'required|array',
            'recurrence.type' => 'required|in:daily,weekly,monthly,custom',
            'recurrence.interval' => 'required|integer|min:1',
            'recurrence.end_date' => 'nullable|date|after:scheduled_at',
            'recurrence.count' => 'nullable|integer|min:1',
            'recurrence.days_of_week' => 'nullable|array',
            'recurrence.days_of_week.*' => 'integer|min:0|max:6',
            'recurrence.day_of_month' => 'nullable|integer|min:1|max:31',
            'recurrence.custom_pattern' => 'nullable|string',
        ]);

        $scheduledPost = ScheduledPost::create([
            'organization_id' => $organizationId,
            'campaign_id' => $validated['campaign_id'] ?? null,
            'channel_id' => $validated['channel_id'],
            'content' => $validated['content'],
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'pending',
            'metadata' => $validated['metadata'] ?? [],
            'created_by' => $request->user()->id,
        ]);

        $recurringPost = $this->recurringScheduleService->createRecurringPost(
            $scheduledPost,
            $validated['recurrence']
        );
        
        $scheduledPost->delete();

        return response()->json([
            'success' => true,
            'data' => $recurringPost->load(['campaign', 'channel', 'recurringInstances']),
            'message' => 'Recurring schedule created successfully.',
        ], 201);
    }

    public function cancelRecurringSchedule(string $organizationId, ScheduledPost $scheduledPost): JsonResponse
    {
        if ($scheduledPost->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled post not found.',
            ], 404);
        }

        if (!$scheduledPost->is_recurring) {
            return response()->json([
                'success' => false,
                'message' => 'This is not a recurring schedule.',
            ], 400);
        }

        $this->recurringScheduleService->cancelRecurringSchedule($scheduledPost);

        return response()->json([
            'success' => true,
            'message' => 'Recurring schedule cancelled successfully.',
        ]);
    }
}

