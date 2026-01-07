<?php

namespace App\Repositories\Eloquent;

use App\Models\Campaign;
use App\Repositories\Contracts\CampaignRepositoryInterface;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function find(int $id): ?Campaign
    {
        return Campaign::find($id);
    }

    public function create(array $data): Campaign
    {
        return Campaign::create($data);
    }

    public function update(Campaign $campaign, array $data): Campaign
    {
        $campaign->update($data);
        return $campaign->fresh();
    }

    public function delete(Campaign $campaign): bool
    {
        return $campaign->delete();
    }

    public function findByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return Campaign::where('organization_id', $organizationId)->get();
    }
}


