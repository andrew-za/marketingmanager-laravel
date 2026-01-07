<?php

namespace App\Http\Resources\Campaign;

use App\Http\Resources\LocalizedResource;
use Illuminate\Http\Request;

/**
 * Campaign resource with localization support
 */
class LocalizedCampaignResource extends LocalizedResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return $this->withLocaleMetadata([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            
            // Status with translation
            'status' => [
                'value' => $this->status,
                'label' => $this->trans("campaign.status_{$this->status}"),
            ],
            
            // Budget with currency formatting
            'budget' => [
                'raw' => $this->budget,
                'formatted' => $this->formatCurrency($this->budget),
            ],
            
            // Metrics with number formatting
            'metrics' => [
                'impressions' => [
                    'raw' => $this->impressions,
                    'formatted' => $this->formatNumber($this->impressions, 0),
                ],
                'clicks' => [
                    'raw' => $this->clicks,
                    'formatted' => $this->formatNumber($this->clicks, 0),
                ],
                'ctr' => [
                    'raw' => $this->ctr,
                    'formatted' => $this->formatNumber($this->ctr, 2) . '%',
                ],
            ],
            
            // Dates with localized formatting
            'dates' => [
                'start_date' => $this->formatDate($this->start_date, 'F j, Y'),
                'end_date' => $this->formatDate($this->end_date, 'F j, Y'),
                'created_at' => $this->formatDateTime($this->created_at, 'F j, Y g:i A'),
                'updated_at' => $this->formatDateTime($this->updated_at, 'F j, Y g:i A'),
            ],
            
            // Related resources
            'organization' => [
                'id' => $this->organization_id,
                'name' => $this->organization->name,
            ],
        ]);
    }

    /**
     * Get additional data that should be returned with the resource array
     */
    public function with(Request $request): array
    {
        return [
            'translations' => [
                'campaign' => $this->trans('campaign.campaign'),
                'campaigns' => $this->trans('campaign.campaigns'),
                'budget' => $this->trans('campaign.campaign_budget'),
                'status' => $this->trans('campaign.campaign_status'),
            ],
        ];
    }
}

