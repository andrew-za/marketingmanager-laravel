<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\PublishedPost;
use App\Models\SocialConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleFacebook(Request $request)
    {
        $data = $request->all();

        if ($request->get('hub_mode') === 'subscribe' && $request->get('hub_verify_token') === config('services.facebook.webhook_verify_token')) {
            return response($request->get('hub_challenge'), 200);
        }

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        $this->processFacebookChange($change);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    protected function processFacebookChange(array $change): void
    {
        if ($change['field'] === 'feed') {
            $postId = $change['value']['post_id'] ?? null;
            if ($postId) {
                $this->updatePostStatus('facebook', $postId, $change['value']);
            }
        }
    }

    public function handleInstagram(Request $request)
    {
        $data = $request->all();

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $message) {
                        $this->processInstagramMessage($message);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    protected function processInstagramMessage(array $message): void
    {
        if (isset($message['post_id'])) {
            $this->updatePostStatus('instagram', $message['post_id'], $message);
        }
    }

    public function handleLinkedIn(Request $request)
    {
        $data = $request->all();

        if (isset($data['eventType']) && $data['eventType'] === 'POST_CREATED') {
            $postId = $data['postId'] ?? null;
            if ($postId) {
                $this->updatePostStatus('linkedin', $postId, $data);
            }
        }

        return response()->json(['success' => true]);
    }

    public function handleTwitter(Request $request)
    {
        $data = $request->all();

        if (isset($data['tweet_create_events'])) {
            foreach ($data['tweet_create_events'] as $tweet) {
                $tweetId = $tweet['id_str'] ?? null;
                if ($tweetId) {
                    $this->updatePostStatus('twitter', $tweetId, $tweet);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function handleTikTok(Request $request)
    {
        $data = $request->all();

        if (isset($data['event'])) {
            $event = $data['event'];
            if ($event['event_type'] === 'video.publish.complete') {
                $publishId = $event['publish_id'] ?? null;
                if ($publishId) {
                    $this->updatePostStatus('tiktok', $publishId, $event);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function handlePinterest(Request $request)
    {
        $data = $request->all();

        if (isset($data['data']['id'])) {
            $pinId = $data['data']['id'];
            $this->updatePostStatus('pinterest', $pinId, $data);
        }

        return response()->json(['success' => true]);
    }

    protected function updatePostStatus(string $platform, string $postId, array $data): void
    {
        $publishedPost = PublishedPost::where('platform', $platform)
            ->where('external_post_id', $postId)
            ->first();

        if ($publishedPost) {
            $publishedPost->update([
                'status' => 'published',
                'platform_response' => array_merge($publishedPost->platform_response ?? [], $data),
                'metrics' => $this->extractMetrics($platform, $data),
            ]);
        }
    }

    protected function extractMetrics(string $platform, array $data): array
    {
        return match ($platform) {
            'facebook' => [
                'likes' => $data['reactions']['summary']['total_count'] ?? 0,
                'comments' => $data['comments']['summary']['total_count'] ?? 0,
                'shares' => $data['shares']['count'] ?? 0,
            ],
            'instagram' => [
                'likes' => $data['like_count'] ?? 0,
                'comments' => $data['comments_count'] ?? 0,
            ],
            'linkedin' => [
                'likes' => $data['numLikes'] ?? 0,
                'comments' => $data['numComments'] ?? 0,
                'shares' => $data['numShares'] ?? 0,
            ],
            'twitter' => [
                'likes' => $data['favorite_count'] ?? 0,
                'retweets' => $data['retweet_count'] ?? 0,
                'replies' => $data['reply_count'] ?? 0,
            ],
            'tiktok' => [
                'likes' => $data['like_count'] ?? 0,
                'comments' => $data['comment_count'] ?? 0,
                'shares' => $data['share_count'] ?? 0,
                'views' => $data['view_count'] ?? 0,
            ],
            'pinterest' => [
                'saves' => $data['pin_metrics']['pin_saves'] ?? 0,
                'clicks' => $data['pin_metrics']['pin_clicks'] ?? 0,
            ],
            default => [],
        };
    }
}


