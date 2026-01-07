<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class SentimentTrend extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'date',
        'average_sentiment',
        'positive_count',
        'negative_count',
        'neutral_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'average_sentiment' => 'decimal:2',
            'positive_count' => 'integer',
            'negative_count' => 'integer',
            'neutral_count' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

