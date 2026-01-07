<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $accessToken = $connection->access_token;

        if (empty($scheduledPost->metadata['video_url'])) {
            throw new \Exception("TikTok requires a video URL");
        }

        $videoUrl = $scheduledPost->metadata['video_url'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
            'post_info' => [
                'title' => $scheduledPost->content,
                'privacy_level' => 'PUBLIC_TO_EVERYONE',
                'disable_duet' => false,
                'disable_comment' => false,
                'disable_stitch' => false,
                'video_cover_timestamp_ms' => 1000,
            ],
            'source_info' => [
                'source' => 'FILE_UPLOAD',
                'video_url' => $videoUrl,
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception("TikTok API error: " . $response->body());
        }

        $data = $response->json();
        $publishId = $data['data']['publish_id'] ?? null;

        return [
            'post_id' => $publishId,
            'post_url' => $publishId ? "https://tiktok.com/@{$connection->account_name}/video/{$publishId}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }
}


