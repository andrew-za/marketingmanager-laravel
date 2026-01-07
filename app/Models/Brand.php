<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Brand extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'summary',
        'audience',
        'guidelines',
        'tone_of_voice',
        'keywords',
        'avoid_keywords',
        'logo',
        'status',
        'business_model',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'avoid_keywords' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BrandAsset::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}


