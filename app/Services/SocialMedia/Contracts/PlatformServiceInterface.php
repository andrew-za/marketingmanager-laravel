<?php

namespace App\Services\SocialMedia\Contracts;

use App\Models\ScheduledPost;
use App\Models\SocialConnection;

interface PlatformServiceInterface
{
    public function publish(ScheduledPost $scheduledPost, SocialConnection $connection): array;
}


