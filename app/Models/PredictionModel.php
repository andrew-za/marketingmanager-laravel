<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class PredictionModel extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'model_config',
        'training_data',
        'accuracy',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'model_config' => 'array',
            'training_data' => 'array',
            'accuracy' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'prediction_model_id');
    }
}

