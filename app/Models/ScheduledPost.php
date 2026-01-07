<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class ScheduledPost extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'campaign_id',
        'channel_id',
        'content',
        'scheduled_at',
        'published_at',
        'status',
        'metadata',
        'created_by',
        'is_recurring',
        'recurrence_type',
        'recurrence_config',
        'recurrence_end_date',
        'recurrence_count',
        'parent_post_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'metadata' => 'array',
            'is_recurring' => 'boolean',
            'recurrence_config' => 'array',
            'recurrence_end_date' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publishedPosts(): HasMany
    {
        return $this->hasMany(PublishedPost::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ContentApproval::class, 'scheduled_post_id');
    }

    public function parentPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class, 'parent_post_id');
    }

    public function recurringInstances(): HasMany
    {
        return $this->hasMany(ScheduledPost::class, 'parent_post_id');
    }

    public function markAsPublished(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], ['error' => $errorMessage]),
        ]);
    }
}

