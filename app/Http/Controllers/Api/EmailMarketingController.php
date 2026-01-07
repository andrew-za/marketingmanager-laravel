<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailMarketing\EmailCampaignController as BaseEmailCampaignController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Email Marketing API Controller
 */
class EmailMarketingController extends Controller
{
    public function __construct(
        private BaseEmailCampaignController $emailCampaignController
    ) {}

    /**
     * List email campaigns
     */
    public function campaigns(Request $request)
    {
        return $this->emailCampaignController->index($request);
    }

    /**
     * Create email campaign
     */
    public function createCampaign(Request $request): JsonResponse
    {
        return $this->emailCampaignController->store($request);
    }

    /**
     * Get email campaign
     */
    public function getCampaign(Request $request, $campaignId): JsonResponse
    {
        $campaign = \App\Models\EmailCampaign::findOrFail($campaignId);
        return $this->emailCampaignController->show($request, $campaign);
    }

    /**
     * Update email campaign
     */
    public function updateCampaign(Request $request, $campaignId): JsonResponse
    {
        $campaign = \App\Models\EmailCampaign::findOrFail($campaignId);
        return $this->emailCampaignController->update($request, $campaign);
    }

    /**
     * Delete email campaign
     */
    public function deleteCampaign(Request $request, $campaignId): JsonResponse
    {
        $campaign = \App\Models\EmailCampaign::findOrFail($campaignId);
        return $this->emailCampaignController->destroy($request, $campaign);
    }
}

