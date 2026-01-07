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
        'brand_id',
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
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

    /**
     * Get email campaigns for this campaign
     */
    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    /**
     * Get competitors associated with this campaign
     */
    public function competitors(): BelongsToMany
    {
        return $this->belongsToMany(Competitor::class, 'campaign_competitors')
            ->withTimestamps();
    }

    /**
     * Get products linked to this campaign
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products')
            ->withTimestamps();
    }

    public function isReadyToPublish(): bool
    {
        return $this->status === 'draft' 
            && $this->channels()->count() > 0
            && $this->scheduledPosts()->count() > 0;
    }

    public function submitForReview(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Only draft campaigns can be submitted for review.');
        }
        $this->update(['status' => 'in_review']);
    }

    public function markAsPublished(): void
    {
        if (!in_array($this->status, ['draft', 'in_review'])) {
            throw new \Exception('Only draft or in_review campaigns can be published.');
        }
        $this->update(['status' => 'active']);
    }

    public function pause(): void
    {
        if (!in_array($this->status, ['active', 'draft'])) {
            throw new \Exception('Only active or draft campaigns can be paused.');
        }
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        if ($this->status !== 'paused') {
            throw new \Exception('Only paused campaigns can be resumed.');
        }
        $this->update(['status' => 'active']);
    }

    public function complete(): void
    {
        if ($this->status !== 'active') {
            throw new \Exception('Only active campaigns can be completed.');
        }
        $this->update(['status' => 'completed']);
    }

    public function deactivate(): void
    {
        if (!in_array($this->status, ['active', 'completed', 'paused'])) {
            throw new \Exception('Only active, completed, or paused campaigns can be deactivated.');
        }
        $this->update(['status' => 'inactive']);
    }

    public function reactivate(): void
    {
        if ($this->status !== 'inactive') {
            throw new \Exception('Only inactive campaigns can be reactivated.');
        }
        $this->update(['status' => 'active']);
    }

    public function scopeForBrand($query, ?int $brandId)
    {
        if ($brandId) {
            return $query->where('brand_id', $brandId);
        }
        return $query;
    }

    public function clone(?User $user = null): Campaign
    {
        $clonedCampaign = $this->replicate();
        $clonedCampaign->name = $this->name . ' (Copy)';
        $clonedCampaign->status = 'draft';
        $clonedCampaign->spent = 0;
        $clonedCampaign->created_by = $user?->id ?? $this->created_by;
        $clonedCampaign->save();

        // Clone goals
        foreach ($this->goals as $goal) {
            $clonedGoal = $goal->replicate();
            $clonedGoal->campaign_id = $clonedCampaign->id;
            $clonedGoal->current_value = 0;
            $clonedGoal->save();
        }

        // Clone channel associations
        foreach ($this->channels as $channel) {
            $clonedCampaign->channels()->attach($channel->id, [
                'budget' => $channel->pivot->budget,
                'spent' => 0,
                'status' => 'pending',
            ]);
        }

        return $clonedCampaign->load(['goals', 'channels']);
    }
}

