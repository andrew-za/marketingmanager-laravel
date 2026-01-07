<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPageVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_page_id',
        'name',
        'html_content',
        'page_data',
        'traffic_percentage',
        'conversions',
        'visits',
        'is_winner',
    ];

    protected function casts(): array
    {
        return [
            'page_data' => 'array',
            'is_winner' => 'boolean',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PageAnalytics::class, 'variant_id');
    }
}

