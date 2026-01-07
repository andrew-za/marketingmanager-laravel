<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Activity Log Service
 * Handles activity log filtering, search, and retrieval
 */
class ActivityLogService
{
    /**
     * Get activity logs with filtering and search
     */
    public function getActivityLogs(
        ?Organization $organization = null,
        array $filters = [],
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = ActivityLog::query();

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by action
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filter by model type
        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Search in description
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        return $query->with(['user', 'organization', 'model'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get activity logs for user
     */
    public function getUserActivityLogs(
        User $user,
        ?Organization $organization = null,
        array $filters = [],
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = ActivityLog::where('user_id', $user->id);

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        // Apply filters
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with(['organization', 'model'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get activity logs for model
     */
    public function getModelActivityLogs(
        string $modelType,
        int $modelId,
        ?Organization $organization = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = ActivityLog::where('model_type', $modelType)
            ->where('model_id', $modelId);

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        return $query->with(['user', 'organization'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get activity log statistics
     */
    public function getStatistics(
        ?Organization $organization = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $query = ActivityLog::query();

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $totalLogs = $query->count();
        $actionsByType = (clone $query)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $logsByModel = (clone $query)
            ->whereNotNull('model_type')
            ->selectRaw('model_type, COUNT(*) as count')
            ->groupBy('model_type')
            ->pluck('count', 'model_type')
            ->toArray();

        $topUsers = (clone $query)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->with('user:id,name,email')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'user_email' => $item->user->email ?? null,
                    'count' => $item->count,
                ];
            })
            ->toArray();

        return [
            'total_logs' => $totalLogs,
            'actions_by_type' => $actionsByType,
            'logs_by_model' => $logsByModel,
            'top_users' => $topUsers,
        ];
    }

    /**
     * Get available actions for filtering
     */
    public function getAvailableActions(?Organization $organization = null): Collection
    {
        $query = ActivityLog::select('action')->distinct();

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        return $query->orderBy('action')->pluck('action');
    }

    /**
     * Get available model types for filtering
     */
    public function getAvailableModelTypes(?Organization $organization = null): Collection
    {
        $query = ActivityLog::select('model_type')
            ->whereNotNull('model_type')
            ->distinct();

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        return $query->orderBy('model_type')->pluck('model_type');
    }
}

