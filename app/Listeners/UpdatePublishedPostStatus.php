<?php

namespace App\Listeners;

use App\Models\PublishedPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdatePublishedPostStatus implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        if (isset($event->platform) && isset($event->postId) && isset($event->data)) {
            $publishedPost = PublishedPost::where('platform', $event->platform)
                ->where('external_post_id', $event->postId)
                ->first();

            if ($publishedPost) {
                $publishedPost->update([
                    'status' => 'published',
                    'platform_response' => array_merge($publishedPost->platform_response ?? [], $event->data),
                ]);
            }
        }
    }
}


