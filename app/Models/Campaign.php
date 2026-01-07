<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Campaign extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'campaign_channels')
            ->withPivot('budget', 'spent', 'status')
            ->withTimestamps();
    }

    public function goals(): HasMany
    {
        return $this->hasMany(CampaignGoal::class);
    }

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public function isReadyToPublish(): bool
    {
        return $this->status === 'draft' 
            && $this->channels()->count() > 0
            && $this->scheduledPosts()->count() > 0;
    }

    public function markAsPublished(): void
    {
        $this->update(['status' => 'active']);
    }
}

