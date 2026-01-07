<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'distribution_type',
        'distribution_key',
        'settings',
        'sent_count',
        'opened_count',
        'completed_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sent_count' => 'integer',
            'opened_count' => 'integer',
            'completed_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}

