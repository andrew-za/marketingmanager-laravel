<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PinterestPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $accessToken = $connection->access_token;
        $boardId = $scheduledPost->metadata['board_id'] ?? null;

        if (!$boardId) {
            throw new \Exception("Pinterest requires a board_id in metadata");
        }

        if (empty($scheduledPost->metadata['image_url'])) {
            throw new \Exception("Pinterest requires an image URL");
        }

        $imageUrl = $scheduledPost->metadata['image_url'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post('https://api.pinterest.com/v5/pins', [
            'board_id' => $boardId,
            'media_source' => [
                'source_type' => 'image_url',
                'url' => $imageUrl,
            ],
            'title' => substr($scheduledPost->content, 0, 100),
            'description' => $scheduledPost->content,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Pinterest API error: " . $response->body());
        }

        $data = $response->json();
        $pinId = $data['id'] ?? null;

        return [
            'post_id' => $pinId,
            'post_url' => $pinId ? "https://pinterest.com/pin/{$pinId}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }
}


