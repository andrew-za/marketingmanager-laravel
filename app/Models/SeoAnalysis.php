<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class SeoAnalysis extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'url',
        'meta_tags',
        'keywords',
        'word_count',
        'reading_time',
        'recommendations',
        'seo_score',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'meta_tags' => 'array',
            'keywords' => 'array',
            'recommendations' => 'array',
            'word_count' => 'integer',
            'reading_time' => 'integer',
            'seo_score' => 'decimal:2',
            'analyzed_at' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}


