<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_page_id',
        'variant_id',
        'analytics_date',
        'visits',
        'unique_visitors',
        'conversions',
        'conversion_rate',
        'bounce_rate',
        'avg_session_duration',
    ];

    protected function casts(): array
    {
        return [
            'analytics_date' => 'date',
            'conversion_rate' => 'decimal:2',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(LandingPageVariant::class, 'variant_id');
    }
}

