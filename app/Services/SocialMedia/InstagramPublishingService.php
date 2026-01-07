<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $instagramAccountId = $connection->account_id;
        $accessToken = $connection->access_token;

        if (!empty($scheduledPost->metadata['images'])) {
            return $this->publishImagePost($scheduledPost, $connection, $instagramAccountId, $accessToken);
        }

        return $this->publishCarouselPost($scheduledPost, $connection, $instagramAccountId, $accessToken);
    }

    protected function publishImagePost(
        ScheduledPost $scheduledPost,
        SocialConnection $connection,
        string $accountId,
        string $accessToken
    ): array {
        $imageUrl = $scheduledPost->metadata['images'][0] ?? null;

        if (!$imageUrl) {
            throw new \Exception("Instagram requires at least one image");
        }

        $creationResponse = Http::post(
            "https://graph.facebook.com/v18.0/{$accountId}/media",
            [
                'image_url' => $imageUrl,
                'caption' => $scheduledPost->content,
                'access_token' => $accessToken,
            ]
        );

        if (!$creationResponse->successful()) {
            throw new \Exception("Instagram media creation failed: " . $creationResponse->body());
        }

        $creationId = $creationResponse->json()['id'];

        $publishResponse = Http::post(
            "https://graph.facebook.com/v18.0/{$accountId}/media_publish",
            [
                'creation_id' => $creationId,
                'access_token' => $accessToken,
            ]
        );

        if (!$publishResponse->successful()) {
            throw new \Exception("Instagram publish failed: " . $publishResponse->body());
        }

        $data = $publishResponse->json();

        return [
            'post_id' => $data['id'] ?? null,
            'post_url' => $data['id'] ? "https://instagram.com/p/{$data['id']}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }

    protected function publishCarouselPost(
        ScheduledPost $scheduledPost,
        SocialConnection $connection,
        string $accountId,
        string $accessToken
    ): array {
        $imageUrls = $scheduledPost->metadata['images'] ?? [];

        if (empty($imageUrls)) {
            throw new \Exception("Instagram requires at least one image");
        }

        $children = [];
        foreach ($imageUrls as $imageUrl) {
            $response = Http::post(
                "https://graph.facebook.com/v18.0/{$accountId}/media",
                [
                    'image_url' => $imageUrl,
                    'is_carousel_item' => true,
                    'access_token' => $accessToken,
                ]
            );

            if ($response->successful()) {
                $children[] = $response->json()['id'];
            }
        }

        $creationResponse = Http::post(
            "https://graph.facebook.com/v18.0/{$accountId}/media",
            [
                'media_type' => 'CAROUSEL',
                'children' => implode(',', $children),
                'caption' => $scheduledPost->content,
                'access_token' => $accessToken,
            ]
        );

        if (!$creationResponse->successful()) {
            throw new \Exception("Instagram carousel creation failed: " . $creationResponse->body());
        }

        $creationId = $creationResponse->json()['id'];

        $publishResponse = Http::post(
            "https://graph.facebook.com/v18.0/{$accountId}/media_publish",
            [
                'creation_id' => $creationId,
                'access_token' => $accessToken,
            ]
        );

        if (!$publishResponse->successful()) {
            throw new \Exception("Instagram publish failed: " . $publishResponse->body());
        }

        $data = $publishResponse->json();

        return [
            'post_id' => $data['id'] ?? null,
            'post_url' => $data['id'] ? "https://instagram.com/p/{$data['id']}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }
}


