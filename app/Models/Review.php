<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Review extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'brand_id',
        'content',
        'platform',
        'rating',
        'author',
        'author_email',
        'date',
        'sentiment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviewSource(): BelongsTo
    {
        return $this->belongsTo(ReviewSource::class, 'platform', 'slug');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ReviewResponse::class);
    }
}


