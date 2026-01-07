<?php

namespace App\Services\SocialMedia;

use App\Services\SocialMedia\Contracts\PlatformServiceInterface;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInPublishingService implements PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $personUrn = $connection->account_id;
        $accessToken = $connection->access_token;

        $payload = [
            'author' => "urn:li:person:{$personUrn}",
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $scheduledPost->content,
                    ],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        if (!empty($scheduledPost->metadata['images'])) {
            $mediaUrns = $this->uploadImages($scheduledPost, $connection);
            if (!empty($mediaUrns)) {
                $payload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
                    'id' => $mediaUrns[0],
                ];
                $payload['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory'] = 'IMAGE';
            }
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->post('https://api.linkedin.com/v2/ugcPosts', $payload);

        if (!$response->successful()) {
            throw new \Exception("LinkedIn API error: " . $response->body());
        }

        $data = $response->json();
        $postId = $data['id'] ?? null;

        return [
            'post_id' => $postId,
            'post_url' => $postId ? "https://linkedin.com/feed/update/{$postId}" : null,
            'response' => $data,
            'metrics' => [],
        ];
    }

    protected function uploadImages(ScheduledPost $scheduledPost, SocialConnection $connection): array
    {
        $mediaUrns = [];
        foreach ($scheduledPost->metadata['images'] as $imageUrl) {
            $registerResponse = Http::withHeaders([
                'Authorization' => "Bearer {$connection->access_token}",
                'Content-Type' => 'application/json',
            ])->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
                'registerUploadRequest' => [
                    'recipes' => ['urn:li:digitalmediaRecipe:feedshare-image'],
                    'owner' => "urn:li:person:{$connection->account_id}",
                    'serviceRelationships' => [
                        [
                            'relationshipType' => 'OWNER',
                            'identifier' => 'urn:li:userGeneratedContent',
                        ],
                    ],
                ],
            ]);

            if ($registerResponse->successful()) {
                $uploadUrl = $registerResponse->json()['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
                $asset = $registerResponse->json()['value']['asset'];

                Http::put($uploadUrl, file_get_contents($imageUrl));

                $mediaUrns[] = $asset;
            }
        }

        return $mediaUrns;
    }
}


