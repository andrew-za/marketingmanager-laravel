<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $pageId = $connection->account_id;
        $accessToken = $connection->access_token;

        $payload = [
            'message' => $scheduledPost->content,
        ];

        if (!empty($scheduledPost->metadata['images'])) {
            $payload['attached_media'] = $this->uploadImages($scheduledPost, $connection);
        }

        $response = Http::post(
            "https://graph.facebook.com/v18.0/{$pageId}/feed",
            array_merge($payload, ['access_token' => $accessToken])
        );

        if (!$response->successful()) {
            throw new \Exception("Facebook API error: " . $response->body());
        }

        $data = $response->json();
        $postId = $data['id'] ?? null;

        return [
            'post_id' => $postId,
            'post_url' => $postId ? "https://facebook.com/{$postId}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }

    protected function uploadImages(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $mediaIds = [];
        foreach ($scheduledPost->metadata['images'] as $imageUrl) {
            $response = Http::post(
                "https://graph.facebook.com/v18.0/{$connection->account_id}/photos",
                [
                    'url' => $imageUrl,
                    'access_token' => $connection->access_token,
                    'published' => false,
                ]
            );

            if ($response->successful()) {
                $mediaIds[] = ['media_fbid' => $response->json()['id']];
            }
        }

        return $mediaIds;
    }
}


