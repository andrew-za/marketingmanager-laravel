<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'competitor_id',
        'analysis_type',
        'metrics',
        'insights',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}


