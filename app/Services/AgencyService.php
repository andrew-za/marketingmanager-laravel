<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Agency Service
 * Handles agency-related business logic
 */
class AgencyService
{
    /**
     * Get all client organizations for an agency
     */
    public function getClientOrganizations(Agency $agency, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = "agency.{$agency->id}.clients." . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($agency, $filters) {
            $query = $agency->clientOrganizations()
                ->withCount('users')
                ->orderBy('name');

            // Apply search filter
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->wherePivot('status', $filters['status']);
            }

            return $query->paginate($filters['per_page'] ?? 15);
        });
    }

    /**
     * Get a specific client organization
     */
    public function getClientOrganization(Agency $agency, int $organizationId): ?Organization
    {
        return $agency->clientOrganizations()
            ->withCount('users')
            ->find($organizationId);
    }

    /**
     * Get all client organization IDs for an agency
     */
    public function getClientOrganizationIds(Agency $agency): array
    {
        $cacheKey = "agency.{$agency->id}.client_ids";
        
        return Cache::remember($cacheKey, 600, function () use ($agency) {
            return $agency->clientOrganizations()
                ->pluck('organizations.id')
                ->toArray();
        });
    }

    /**
     * Clear agency client cache
     */
    public function clearClientCache(Agency $agency): void
    {
        Cache::forget("agency.{$agency->id}.client_ids");
        Cache::tags(["agency.{$agency->id}.clients"])->flush();
    }
}

