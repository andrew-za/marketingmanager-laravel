<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class PaidCampaign extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'campaign_id',
        'name',
        'description',
        'platform',
        'status',
        'budget',
        'spent',
        'currency',
        'budget_type',
        'start_date',
        'end_date',
        'targeting',
        'ad_creative',
        'external_campaign_id',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'targeting' => 'array',
            'ad_creative' => 'array',
            'metrics' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function updateMetrics(array $metrics): void
    {
        $currentMetrics = $this->metrics ?? [];
        $this->update([
            'metrics' => array_merge($currentMetrics, $metrics),
        ]);
    }

    public function updateSpending(float $amount): void
    {
        $this->increment('spent', $amount);
    }

    public function getRemainingBudget(): float
    {
        return max(0, $this->budget - $this->spent);
    }

    public function getPerformanceMetrics(): array
    {
        $metrics = $this->metrics ?? [];
        return [
            'impressions' => $metrics['impressions'] ?? 0,
            'clicks' => $metrics['clicks'] ?? 0,
            'conversions' => $metrics['conversions'] ?? 0,
            'ctr' => $this->calculateCTR(),
            'cpc' => $this->calculateCPC(),
            'cpm' => $this->calculateCPM(),
        ];
    }

    private function calculateCTR(): float
    {
        $impressions = $this->metrics['impressions'] ?? 0;
        $clicks = $this->metrics['clicks'] ?? 0;
        
        return $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
    }

    private function calculateCPC(): float
    {
        $clicks = $this->metrics['clicks'] ?? 0;
        
        return $clicks > 0 ? $this->spent / $clicks : 0;
    }

    private function calculateCPM(): float
    {
        $impressions = $this->metrics['impressions'] ?? 0;
        
        return $impressions > 0 ? ($this->spent / $impressions) * 1000 : 0;
    }
}


