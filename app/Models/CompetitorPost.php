<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'competitor_id',
        'platform',
        'platform_post_id',
        'content',
        'published_at',
        'engagement_metrics',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'engagement_metrics' => 'array',
            'metadata' => 'array',
        ];
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}


