<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class AiUsageLog extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'ai_generation_id',
        'provider',
        'model',
        'type',
        'tokens_used',
        'cost',
        'usage_date',
    ];

    protected function casts(): array
    {
        return [
            'tokens_used' => 'integer',
            'cost' => 'decimal:4',
            'usage_date' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiGeneration(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class);
    }
}


