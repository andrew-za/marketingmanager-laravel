<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\AiUsageLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AiUsageTrackingService
{
    public function logUsage(
        Organization $organization,
        User $user,
        AiGeneration $aiGeneration,
        string $provider,
        string $model,
        string $type,
        int $tokensUsed,
        float $cost
    ): AiUsageLog {
        $usageLog = AiUsageLog::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'ai_generation_id' => $aiGeneration->id,
            'provider' => $provider,
            'model' => $model,
            'type' => $type,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
            'usage_date' => now()->toDateString(),
        ]);

        DB::table('usage_tracking')->updateOrInsert(
            [
                'organization_id' => $organization->id,
                'feature' => 'ai_generation',
                'metric' => 'tokens',
                'date' => now()->toDateString(),
            ],
            [
                'value' => DB::raw("COALESCE(value, 0) + {$tokensUsed}"),
                'metadata' => json_encode(['provider' => $provider]),
                'updated_at' => now(),
            ]
        );

        DB::table('usage_tracking')->updateOrInsert(
            [
                'organization_id' => $organization->id,
                'feature' => 'ai_generation',
                'metric' => 'cost',
                'date' => now()->toDateString(),
            ],
            [
                'value' => DB::raw("COALESCE(value, 0) + {$cost}"),
                'metadata' => json_encode(['provider' => $provider]),
                'updated_at' => now(),
            ]
        );

        return $usageLog;
    }

    public function getUsageStats(Organization $organization, string $period = 'month'): array
    {
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $stats = DB::table('ai_usage_logs')
            ->where('organization_id', $organization->id)
            ->where('usage_date', '>=', $startDate->toDateString())
            ->selectRaw('
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                COUNT(*) as total_requests,
                provider,
                type
            ')
            ->groupBy('provider', 'type')
            ->get();

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'stats' => $stats,
        ];
    }
}


