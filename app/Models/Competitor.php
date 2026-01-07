<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Competitor extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'website',
        'description',
        'social_profiles',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_profiles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_competitors')
            ->withTimestamps();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(CompetitorAnalysis::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CompetitorPost::class);
    }
}

