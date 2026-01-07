<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_plan_id',
        'feature',
        'limit_type',
        'limit_value',
        'is_unlimited',
    ];

    protected function casts(): array
    {
        return [
            'limit_value' => 'decimal:2',
            'is_unlimited' => 'boolean',
        ];
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}


