<?php

namespace App\Repositories\Contracts;

use App\Models\Campaign;

interface CampaignRepositoryInterface
{
    public function find(int $id): ?Campaign;
    public function create(array $data): Campaign;
    public function update(Campaign $campaign, array $data): Campaign;
    public function delete(Campaign $campaign): bool;
    public function findByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection;
}

