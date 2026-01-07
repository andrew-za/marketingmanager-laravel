<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class UsageTracking extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'usage_tracking';

    protected $fillable = [
        'organization_id',
        'feature',
        'metric',
        'value',
        'date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

