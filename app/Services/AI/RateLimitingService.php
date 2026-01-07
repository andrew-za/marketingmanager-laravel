<?php

namespace App\Services\AI;

use App\Models\Organization;
use App\Models\UsageLimit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RateLimitingService
{
    public function checkRateLimit(Organization $organization, string $feature): bool
    {
        $subscription = $organization->subscription;
        
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        $limit = UsageLimit::where('subscription_plan_id', $subscription->plan_id)
            ->where('feature', $feature)
            ->first();

        if (!$limit || $limit->is_unlimited) {
            return true;
        }

        $currentUsage = $this->getCurrentUsage($organization, $feature, $limit->limit_type);
        
        return $currentUsage < $limit->limit_value;
    }

    public function getRemainingQuota(Organization $organization, string $feature): int
    {
        $subscription = $organization->subscription;
        
        if (!$subscription || !$subscription->plan) {
            return 0;
        }

        $limit = UsageLimit::where('subscription_plan_id', $subscription->plan_id)
            ->where('feature', $feature)
            ->first();

        if (!$limit || $limit->is_unlimited) {
            return PHP_INT_MAX;
        }

        $currentUsage = $this->getCurrentUsage($organization, $feature, $limit->limit_type);
        
        return max(0, (int)$limit->limit_value - $currentUsage);
    }

    private function getCurrentUsage(Organization $organization, string $feature, string $limitType): float
    {
        $cacheKey = "usage_{$organization->id}_{$feature}_{$limitType}";
        
        return Cache::remember($cacheKey, 3600, function () use ($organization, $feature, $limitType) {
            $startDate = match($limitType) {
                'daily' => now()->startOfDay(),
                'weekly' => now()->startOfWeek(),
                'monthly' => now()->startOfMonth(),
                'yearly' => now()->startOfYear(),
                default => now()->startOfMonth(),
            };

            return DB::table('usage_tracking')
                ->where('organization_id', $organization->id)
                ->where('feature', $feature)
                ->where('date', '>=', $startDate)
                ->sum('value');
        });
    }

    public function incrementUsage(Organization $organization, string $feature, float $value = 1): void
    {
        $today = now()->toDateString();
        
        DB::table('usage_tracking')->updateOrInsert(
            [
                'organization_id' => $organization->id,
                'feature' => $feature,
                'metric' => 'count',
                'date' => $today,
            ],
            [
                'value' => DB::raw("COALESCE(value, 0) + {$value}"),
                'updated_at' => now(),
            ]
        );

        Cache::forget("usage_{$organization->id}_{$feature}_daily");
        Cache::forget("usage_{$organization->id}_{$feature}_weekly");
        Cache::forget("usage_{$organization->id}_{$feature}_monthly");
        Cache::forget("usage_{$organization->id}_{$feature}_yearly");
    }
}


