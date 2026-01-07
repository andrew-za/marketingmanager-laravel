<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use App\Models\PublishedPost;
use App\Services\SocialMedia\PlatformPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ScheduledPost $scheduledPost,
        public SocialConnection $connection
    ) {
    }

    public function handle(PlatformPublishingService $publishingService): void
    {
        try {
            $publishingService->publish($this->scheduledPost, $this->connection);
            Log::info("Successfully published post {$this->scheduledPost->id} to {$this->connection->platform}");
        } catch (\Exception $e) {
            Log::error("Failed to publish post {$this->scheduledPost->id}: " . $e->getMessage());

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->scheduledPost->update(['status' => 'failed']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed after {$this->tries} attempts for post {$this->scheduledPost->id}: " . $exception->getMessage());

        $this->scheduledPost->update(['status' => 'failed']);

        PublishedPost::where('scheduled_post_id', $this->scheduledPost->id)
            ->where('social_connection_id', $this->connection->id)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
    }
}

