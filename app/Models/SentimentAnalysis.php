<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\Traits\HasOrganizationScope;

class SentimentAnalysis extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'content_id',
        'content_type',
        'sentiment_score',
        'sentiment_label',
        'keywords',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'sentiment_score' => 'decimal:2',
            'keywords' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function content(): MorphTo
    {
        return $this->morphTo('content');
    }
}

