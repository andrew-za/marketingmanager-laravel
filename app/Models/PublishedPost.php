<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class PublishedPost extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'scheduled_post_id',
        'organization_id',
        'social_connection_id',
        'platform',
        'external_post_id',
        'external_post_url',
        'status',
        'published_at',
        'platform_response',
        'metrics',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'platform_response' => 'array',
            'metrics' => 'array',
        ];
    }

    public function scheduledPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function socialConnection(): BelongsTo
    {
        return $this->belongsTo(SocialConnection::class, 'social_connection_id');
    }
}

