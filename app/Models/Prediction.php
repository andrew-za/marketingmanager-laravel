<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\Traits\HasOrganizationScope;

class Prediction extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'prediction_model_id',
        'predictable_type',
        'predictable_id',
        'prediction_type',
        'predicted_value',
        'confidence_score',
        'prediction_data',
        'predicted_at',
    ];

    protected function casts(): array
    {
        return [
            'predicted_value' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'prediction_data' => 'array',
            'predicted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(PredictionModel::class, 'prediction_model_id');
    }

    public function predictable(): MorphTo
    {
        return $this->morphTo();
    }
}

