<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send Notifications Job
 * Processes notification sending asynchronously
 */
class SendNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public array $userIds,
        public string $type,
        public string $message,
        public ?string $notifiableType = null,
        public ?int $notifiableId = null,
        public string $priority = 'medium',
        public ?array $data = null
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        try {
            foreach ($this->userIds as $userId) {
                $user = User::find($userId);
                
                if (!$user) {
                    Log::warning("User {$userId} not found for notification");
                    continue;
                }

                if (!$notificationService->shouldSendNotification($user, $this->type, 'in_app')) {
                    continue;
                }

                $notificationService->createNotification(
                    $user,
                    $this->type,
                    $this->message,
                    $this->notifiableType,
                    $this->notifiableId,
                    $this->priority,
                    $this->data
                );
            }

            Log::info("Notifications sent successfully to " . count($this->userIds) . " users");
        } catch (\Exception $e) {
            Log::error("Failed to send notifications: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendNotifications job failed after {$this->tries} attempts: " . $exception->getMessage());
    }
}

