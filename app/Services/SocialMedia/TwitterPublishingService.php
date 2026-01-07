<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $accessToken = $connection->access_token;
        $userId = $connection->account_id;

        $payload = [
            'text' => $scheduledPost->content,
        ];

        if (!empty($scheduledPost->metadata['images'])) {
            $mediaIds = $this->uploadImages($scheduledPost, $connection);
            if (!empty($mediaIds)) {
                $payload['media'] = ['media_ids' => $mediaIds];
            }
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://api.twitter.com/2/tweets", $payload);

        if (!$response->successful()) {
            throw new \Exception("Twitter API error: " . $response->body());
        }

        $data = $response->json();
        $tweetId = $data['data']['id'] ?? null;

        return [
            'post_id' => $tweetId,
            'post_url' => $tweetId ? "https://twitter.com/{$userId}/status/{$tweetId}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }

    protected function uploadImages(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $mediaIds = [];
        foreach ($scheduledPost->metadata['images'] as $imageUrl) {
            $imageData = file_get_contents($imageUrl);
            $base64Image = base64_encode($imageData);

            $initResponse = Http::withHeaders([
                'Authorization' => "Bearer {$connection->access_token}",
            ])->post('https://upload.twitter.com/1.1/media/upload.json', [
                'command' => 'INIT',
                'media_type' => 'image/jpeg',
                'total_bytes' => strlen($imageData),
            ]);

            if (!$initResponse->successful()) {
                continue;
            }

            $mediaId = $initResponse->json()['media_id_string'];

            Http::withHeaders([
                'Authorization' => "Bearer {$connection->access_token}",
            ])->post('https://upload.twitter.com/1.1/media/upload.json', [
                'command' => 'APPEND',
                'media_id' => $mediaId,
                'media_data' => $base64Image,
                'segment_index' => 0,
            ]);

            $finalizeResponse = Http::withHeaders([
                'Authorization' => "Bearer {$connection->access_token}",
            ])->post('https://upload.twitter.com/1.1/media/upload.json', [
                'command' => 'FINALIZE',
                'media_id' => $mediaId,
            ]);

            if ($finalizeResponse->successful()) {
                $mediaIds[] = $mediaId;
            }
        }

        return $mediaIds;
    }
}


