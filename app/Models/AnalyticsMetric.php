<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\Traits\HasOrganizationScope;

class AnalyticsMetric extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'metricable_type',
        'metricable_id',
        'metric_name',
        'value',
        'metric_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'metric_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function metricable(): MorphTo
    {
        return $this->morphTo();
    }
}

