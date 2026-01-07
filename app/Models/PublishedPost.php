<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_post_id',
        'connection_id',
        'platform_post_id',
        'status',
        'published_at',
        'engagement_metrics',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'engagement_metrics' => 'array',
        ];
    }

    public function scheduledPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(SocialConnection::class, 'connection_id');
    }
}

