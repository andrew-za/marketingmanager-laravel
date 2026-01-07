<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function createNotification(
        User $user,
        string $type,
        string $message,
        ?string $notifiableType = null,
        ?int $notifiableId = null,
        string $priority = 'medium',
        ?array $data = null
    ): Notification {
        return DB::transaction(function () use ($user, $type, $message, $notifiableType, $notifiableId, $priority, $data) {
            return Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'message' => $message,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
                'priority' => $priority,
                'is_read' => false,
                'data' => $data,
            ]);
        });
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();
        return $notification->fresh();
    }

    public function markAllAsRead(User $user): int
    {
        return DB::transaction(function () use ($user) {
            return Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        });
    }

    public function deleteNotification(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function getUserNotifications(User $user, int $limit = 20, ?string $type = null)
    {
        $query = Notification::where('user_id', $user->id)
            ->with('notifiable')
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($limit);
    }

    public function getNotificationPreference(User $user, string $notificationType): ?NotificationPreference
    {
        return NotificationPreference::where('user_id', $user->id)
            ->where('notification_type', $notificationType)
            ->first();
    }

    public function updateNotificationPreference(
        User $user,
        string $notificationType,
        bool $emailEnabled = true,
        bool $inAppEnabled = true,
        bool $pushEnabled = false
    ): NotificationPreference {
        return DB::transaction(function () use ($user, $notificationType, $emailEnabled, $inAppEnabled, $pushEnabled) {
            return NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type' => $notificationType,
                ],
                [
                    'email_enabled' => $emailEnabled,
                    'in_app_enabled' => $inAppEnabled,
                    'push_enabled' => $pushEnabled,
                ]
            );
        });
    }

    public function shouldSendNotification(User $user, string $notificationType, string $channel = 'in_app'): bool
    {
        $preference = $this->getNotificationPreference($user, $notificationType);

        if (!$preference) {
            return $channel === 'in_app';
        }

        return match ($channel) {
            'email' => $preference->email_enabled,
            'push' => $preference->push_enabled,
            'in_app' => $preference->in_app_enabled,
            default => false,
        };
    }
}

