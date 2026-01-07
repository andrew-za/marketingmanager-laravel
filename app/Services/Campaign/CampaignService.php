<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignNotificationService;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function __construct(
        private CampaignRepositoryInterface $repository,
        private CampaignNotificationService $notificationService
    ) {}

    public function createCampaign(array $data, User $user): Campaign
    {
        return DB::transaction(function () use ($data, $user) {
            $campaign = $this->repository->create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);

            $this->notificationService->notifyCampaignCreated($campaign);

            return $campaign;
        });
    }

    public function updateCampaign(Campaign $campaign, array $data): Campaign
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign = $this->repository->update($campaign, $data);

            $this->notificationService->notifyCampaignUpdated($campaign);

            return $campaign;
        });
    }

    public function deleteCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            return $this->repository->delete($campaign);
        });
    }

    public function publishCampaign(Campaign $campaign): void
    {
        if (!$campaign->isReadyToPublish()) {
            throw new \Exception('Campaign is not ready to be published.');
        }

        DB::transaction(function () use ($campaign) {
            $campaign->markAsPublished();
            $this->notificationService->notifyCampaignPublished($campaign);
        });
    }
}


