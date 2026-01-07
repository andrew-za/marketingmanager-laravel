<?php

namespace App\Services\SocialMedia;

use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use App\Models\PublishedPost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PlatformPublishingService
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): PublishedPost
    {
        try {
            if (!$connection->isConnected()) {
                throw new \Exception("Connection {$connection->id} is not active");
            }

            $platformService = $this->getPlatformService($connection->platform);
            $result = $platformService->publish($scheduledPost, $connection);

            $publishedPost = PublishedPost::create([
                'scheduled_post_id' => $scheduledPost->id,
                'organization_id' => $scheduledPost->organization_id,
                'social_connection_id' => $connection->id,
                'platform' => $connection->platform,
                'external_post_id' => $result['post_id'] ?? null,
                'external_post_url' => $result['post_url'] ?? null,
                'status' => 'published',
                'published_at' => now(),
                'platform_response' => $result['response'] ?? [],
                'metrics' => $result['metrics'] ?? [],
            ]);

            $scheduledPost->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            return $publishedPost;
        } catch (\Exception $e) {
            Log::error("Publishing failed for post {$scheduledPost->id}: " . $e->getMessage());

            PublishedPost::create([
                'scheduled_post_id' => $scheduledPost->id,
                'organization_id' => $scheduledPost->organization_id,
                'social_connection_id' => $connection->id,
                'platform' => $connection->platform,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getPlatformService(string $platform): Contracts\PlatformServiceInterface
    {
        return match ($platform) {
            'facebook' => app(FacebookPublishingService::class),
            'instagram' => app(InstagramPublishingService::class),
            'linkedin' => app(LinkedInPublishingService::class),
            'twitter' => app(TwitterPublishingService::class),
            'tiktok' => app(TikTokPublishingService::class),
            'pinterest' => app(PinterestPublishingService::class),
            default => throw new \Exception("Unsupported platform: {$platform}"),
        };
    }
}

