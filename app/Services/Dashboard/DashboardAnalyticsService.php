<?php

namespace App\Services\Dashboard;

use App\Models\Campaign;
use App\Models\ScheduledPost;
use App\Models\PublishedPost;
use App\Models\Task;
use App\Models\PaidCampaign;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardAnalyticsService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function getKPIs(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $cacheKey = "dashboard_kpis_{$organizationId}_" . ($startDate?->format('Y-m-d') ?? 'all') . '_' . ($endDate?->format('Y-m-d') ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId, $startDate, $endDate) {
            return [
                'active_campaigns' => $this->getActiveCampaignsCount($organizationId),
                'total_campaigns' => $this->getTotalCampaignsCount($organizationId, $startDate, $endDate),
                'scheduled_posts' => $this->getScheduledPostsCount($organizationId, $startDate, $endDate),
                'published_posts' => $this->getPublishedPostsCount($organizationId, $startDate, $endDate),
                'pending_tasks' => $this->getPendingTasksCount($organizationId),
                'total_engagement' => $this->getTotalEngagement($organizationId, $startDate, $endDate),
                'campaign_spend' => $this->getCampaignSpend($organizationId, $startDate, $endDate),
                'paid_campaigns_budget' => $this->getPaidCampaignsBudget($organizationId),
            ];
        });
    }

    public function getCampaignPerformance(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $cacheKey = "campaign_performance_{$organizationId}_" . ($startDate?->format('Y-m-d') ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId, $startDate, $endDate) {
            $query = Campaign::where('organization_id', $organizationId);

            if ($startDate) {
                $query->where('start_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('end_date', '<=', $endDate);
            }

            $campaigns = $query->with(['goals', 'scheduledPosts'])->get();

            return [
                'total_campaigns' => $campaigns->count(),
                'active_campaigns' => $campaigns->where('status', 'active')->count(),
                'completed_campaigns' => $campaigns->where('status', 'completed')->count(),
                'total_budget' => $campaigns->sum('budget'),
                'total_spent' => $campaigns->sum('spent'),
                'average_roi' => $this->calculateAverageROI($campaigns),
                'goal_completion_rate' => $this->calculateGoalCompletionRate($campaigns),
            ];
        });
    }

    public function getContentCalendarPreview(int $organizationId, int $days = 7): array
    {
        $cacheKey = "content_calendar_preview_{$organizationId}_{$days}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId, $days) {
            $startDate = now();
            $endDate = now()->addDays($days);

            return ScheduledPost::where('organization_id', $organizationId)
                ->whereBetween('scheduled_at', [$startDate, $endDate])
                ->with(['campaign', 'channel', 'creator'])
                ->orderBy('scheduled_at')
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->campaign?->name ?? 'Standalone Post',
                        'channel' => $post->channel->name ?? 'Unknown',
                        'scheduled_at' => $post->scheduled_at->format('Y-m-d H:i'),
                        'status' => $post->status,
                        'content_preview' => substr($post->content, 0, 100),
                    ];
                })
                ->toArray();
        });
    }

    public function getPendingTasks(int $organizationId, int $limit = 10): array
    {
        $cacheKey = "pending_tasks_{$organizationId}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId, $limit) {
            return Task::where('organization_id', $organizationId)
                ->whereIn('status', ['todo', 'in_progress', 'review'])
                ->with(['assignee', 'project', 'creator'])
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'due_date' => $task->due_date?->format('Y-m-d'),
                        'assignee' => $task->assignee?->name,
                        'is_overdue' => $task->isOverdue(),
                    ];
                })
                ->toArray();
        });
    }

    public function getActivityFeed(int $organizationId, int $limit = 20): array
    {
        $cacheKey = "activity_feed_{$organizationId}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId, $limit) {
            return ActivityLog::where('organization_id', $organizationId)
                ->with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'description' => $log->description,
                        'user' => $log->user?->name ?? 'System',
                        'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    ];
                })
                ->toArray();
        });
    }

    private function getActiveCampaignsCount(int $organizationId): int
    {
        return Campaign::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();
    }

    private function getTotalCampaignsCount(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = Campaign::where('organization_id', $organizationId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->count();
    }

    private function getScheduledPostsCount(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = ScheduledPost::where('organization_id', $organizationId)
            ->where('status', 'pending');

        if ($startDate) {
            $query->where('scheduled_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('scheduled_at', '<=', $endDate);
        }

        return $query->count();
    }

    private function getPublishedPostsCount(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = PublishedPost::whereHas('scheduledPost', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });

        if ($startDate) {
            $query->where('published_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('published_at', '<=', $endDate);
        }

        return $query->count();
    }

    private function getPendingTasksCount(int $organizationId): int
    {
        return Task::where('organization_id', $organizationId)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->count();
    }

    private function getTotalEngagement(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = PublishedPost::whereHas('scheduledPost', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });

        if ($startDate) {
            $query->where('published_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('published_at', '<=', $endDate);
        }

        return $query->get()
            ->sum(function ($post) {
                $metrics = $post->engagement_metrics ?? [];
                return ($metrics['likes'] ?? 0) + 
                       ($metrics['comments'] ?? 0) + 
                       ($metrics['shares'] ?? 0) + 
                       ($metrics['clicks'] ?? 0);
            });
    }

    private function getCampaignSpend(int $organizationId, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = Campaign::where('organization_id', $organizationId);

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
        }

        return $query->sum('spent');
    }

    private function getPaidCampaignsBudget(int $organizationId): float
    {
        return PaidCampaign::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->sum('budget');
    }

    private function calculateAverageROI($campaigns): float
    {
        if ($campaigns->isEmpty()) {
            return 0;
        }

        $totalROI = $campaigns->sum(function ($campaign) {
            if ($campaign->spent == 0) {
                return 0;
            }
            return (($campaign->budget - $campaign->spent) / $campaign->spent) * 100;
        });

        return $totalROI / $campaigns->count();
    }

    private function calculateGoalCompletionRate($campaigns): float
    {
        $totalGoals = 0;
        $completedGoals = 0;

        foreach ($campaigns as $campaign) {
            foreach ($campaign->goals as $goal) {
                $totalGoals++;
                if ($goal->current_value >= $goal->target_value) {
                    $completedGoals++;
                }
            }
        }

        return $totalGoals > 0 ? ($completedGoals / $totalGoals) * 100 : 0;
    }

    public function clearCache(int $organizationId): void
    {
        $patterns = [
            "dashboard_kpis_{$organizationId}_*",
            "campaign_performance_{$organizationId}_*",
            "content_calendar_preview_{$organizationId}_*",
            "pending_tasks_{$organizationId}_*",
            "activity_feed_{$organizationId}_*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}

