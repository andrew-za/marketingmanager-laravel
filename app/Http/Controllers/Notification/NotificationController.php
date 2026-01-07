<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $this->notificationService->getUserNotifications(
            $request->user(),
            $request->get('limit', 20),
            $request->get('type')
        );

        return \App\Http\Resources\Notification\NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification = $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\Notification\NotificationResource($notification),
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'marked_count' => $count,
            ],
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $this->authorize('delete', $notification);

        $this->notificationService->deleteNotification($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $preferences = \App\Models\NotificationPreference::where('user_id', $request->user()->id)
            ->get()
            ->keyBy('notification_type');

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    public function updatePreference(Request $request): JsonResponse
    {
        $request->validate([
            'notification_type' => ['required', 'string'],
            'email_enabled' => ['boolean'],
            'in_app_enabled' => ['boolean'],
            'push_enabled' => ['boolean'],
        ]);

        $preference = $this->notificationService->updateNotificationPreference(
            $request->user(),
            $request->notification_type,
            $request->boolean('email_enabled', true),
            $request->boolean('in_app_enabled', true),
            $request->boolean('push_enabled', false)
        );

        return response()->json([
            'success' => true,
            'data' => $preference,
            'message' => 'Notification preference updated successfully.',
        ]);
    }
}

