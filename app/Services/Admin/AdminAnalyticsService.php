<?php

namespace App\Services\Admin;

use App\Models\AiUsageLog;
use App\Models\UsageTracking;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    /**
     * Get AI usage costing analytics
     */
    public function getAiUsageCosting(array $filters = []): array
    {
        $query = AiUsageLog::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('usage_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('usage_date', '<=', $filters['date_to']);
        }

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        $totalCost = $query->sum('cost');
        $totalTokens = $query->sum('tokens_used');
        $totalRequests = $query->count();

        $costByProvider = $query->clone()
            ->select('provider', DB::raw('SUM(cost) as total_cost'), DB::raw('SUM(tokens_used) as total_tokens'))
            ->groupBy('provider')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->provider => [
                    'cost' => $item->total_cost,
                    'tokens' => $item->total_tokens,
                ]];
            })
            ->toArray();

        $costByOrganization = $query->clone()
            ->select('organization_id', DB::raw('SUM(cost) as total_cost'))
            ->with('organization:id,name')
            ->groupBy('organization_id')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'organization_id' => $item->organization_id,
                    'organization_name' => $item->organization->name ?? 'Unknown',
                    'cost' => $item->total_cost,
                ];
            })
            ->toArray();

        return [
            'total_cost' => $totalCost,
            'total_tokens' => $totalTokens,
            'total_requests' => $totalRequests,
            'cost_by_provider' => $costByProvider,
            'top_organizations' => $costByOrganization,
        ];
    }

    /**
     * Get platform-wide analytics
     */
    public function getPlatformAnalytics(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'new_this_month' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'organizations' => [
                'total' => Organization::count(),
                'active' => Organization::where('status', 'active')->count(),
                'new_this_month' => Organization::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'content' => [
                'total_posts' => \App\Models\ScheduledPost::count(),
                'published_posts' => \App\Models\PublishedPost::count(),
                'pending_moderation' => \App\Models\ModerationQueue::where('status', 'pending')->count(),
            ],
            'ai_usage' => $this->getAiUsageCosting($filters),
        ];
    }

    /**
     * Get system health metrics
     */
    public function getSystemHealth(): array
    {
        $recentErrors = \App\Models\SystemLog::where('level', 'error')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $recentWarnings = \App\Models\SystemLog::where('level', 'warning')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->where('failed_at', '>=', now()->subHours(24))->count();

        return [
            'status' => $recentErrors > 10 ? 'critical' : ($recentErrors > 5 ? 'warning' : 'healthy'),
            'recent_errors' => $recentErrors,
            'recent_warnings' => $recentWarnings,
            'pending_jobs' => $pendingJobs,
            'failed_jobs_24h' => $failedJobs,
            'database_size' => $this->getDatabaseSize(),
            'cache_status' => $this->getCacheStatus(),
        ];
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = DB::selectOne("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ");
            return ($result->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get cache status
     */
    private function getCacheStatus(): array
    {
        try {
            \Illuminate\Support\Facades\Cache::put('health_check', 'ok', 60);
            $status = \Illuminate\Support\Facades\Cache::get('health_check') === 'ok';
            return [
                'status' => $status ? 'operational' : 'degraded',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
            ];
        }
    }
}

