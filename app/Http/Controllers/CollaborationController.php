<?php

namespace App\Http\Controllers;

use App\Models\ChatTopic;
use App\Models\ChatMessage;
use App\Models\ActivityLog;
use App\Services\ReviewService;
use App\Services\Chat\ChatService;
use App\Events\ChatMessageSent;
use App\Events\ChatTopicCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollaborationController extends Controller
{
    public function __construct(
        private ReviewService $reviewService,
        private ChatService $chatService
    ) {}

    public function index(Request $request, string $organizationId)
    {
        if ($request->expectsJson()) {
            $user = $request->user();
            
            $topics = ChatTopic::where('organization_id', $organizationId)
                ->where(function ($query) use ($user) {
                    $query->where('is_private', false)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('participants', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->with(['latestMessage', 'participants'])
                ->withCount(['messages', 'participants'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($topic) use ($user) {
                    $topic->unread_count = $topic->unreadCountForUser($user->id);
                    return $topic;
                });

            $pendingReviews = $this->reviewService->getPendingReviewsForUser($organizationId, $user->id, 3);

            $recentActivity = ActivityLog::where('organization_id', $organizationId)
                ->with(['user', 'model'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'topics' => $topics,
                    'pending_reviews' => $pendingReviews,
                    'recent_activity' => $recentActivity,
                ],
            ]);
        }

        return view('collaboration.index', [
            'organizationId' => $organizationId,
        ]);
    }

    public function showTopic(Request $request, string $organizationId, ChatTopic $topic): JsonResponse
    {
        $user = $request->user();

        if ($topic->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found.',
            ], 404);
        }

        $messages = ChatMessage::where('chat_topic_id', $topic->id)
            ->with(['user', 'replyTo', 'reactions.user'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        $participant = $topic->participants()->where('user_id', $user->id)->first();
        if ($participant) {
            $participant->markAsRead();
        } else {
            $topic->participants()->create([
                'user_id' => $user->id,
                'last_read_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'topic' => $topic->load(['creator', 'participants.user']),
                'messages' => $messages,
            ],
        ]);
    }

    public function storeMessage(Request $request, string $organizationId, ChatTopic $topic): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'reply_to' => 'nullable|exists:chat_messages,id',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        if ($topic->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found.',
            ], 404);
        }

        $message = DB::transaction(function () use ($topic, $validated, $request) {
            $attachments = [];
            
            if ($request->hasFile('files')) {
                $attachments = $this->chatService->handleFileUploads($request->file('files'), $topic);
            }

            $message = ChatMessage::create([
                'chat_topic_id' => $topic->id,
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
                'reply_to' => $validated['reply_to'] ?? null,
                'attachments' => !empty($attachments) ? $attachments : null,
            ]);

            $mentionedUserIds = $this->chatService->parseMentions($validated['message'], $topic);
            if (!empty($mentionedUserIds)) {
                $this->chatService->sendMentionNotifications($message, $mentionedUserIds, $request->user());
            }

            $topic->touch();

            ActivityLog::log('chat_message_created', $message, $request->user(), [
                'topic_id' => $topic->id,
                'topic_name' => $topic->name,
            ]);

            $message->load(['user', 'replyTo', 'topic', 'reactions.user']);
            
            broadcast(new ChatMessageSent($message))->toOthers();

            return $message;
        });

        return response()->json([
            'success' => true,
            'data' => $message,
            'message' => 'Message sent successfully.',
        ], 201);
    }

    public function createTopic(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:channel,direct,group',
            'is_private' => 'boolean',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $topic = DB::transaction(function () use ($validated, $organizationId, $request) {
            $topic = ChatTopic::create([
                'organization_id' => $organizationId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'is_private' => $validated['is_private'] ?? false,
                'created_by' => $request->user()->id,
            ]);

            $participantIds = $validated['participant_ids'] ?? [];
            $participantIds[] = $request->user()->id;

            foreach (array_unique($participantIds) as $userId) {
                $topic->participants()->create([
                    'user_id' => $userId,
                ]);
            }

            ActivityLog::log('chat_topic_created', $topic, $request->user(), [
                'topic_name' => $topic->name,
            ]);

            $topic->load(['creator', 'participants.user']);
            
            broadcast(new ChatTopicCreated($topic))->toOthers();

            return $topic;
        });

        return response()->json([
            'success' => true,
            'data' => $topic,
            'message' => 'Topic created successfully.',
        ], 201);
    }
}


