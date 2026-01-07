<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\CampaignGoal;
use Illuminate\Support\Facades\DB;

class CampaignGoalService
{
    public function createGoal(Campaign $campaign, array $data): CampaignGoal
    {
        return DB::transaction(function () use ($campaign, $data) {
            return CampaignGoal::create([
                'campaign_id' => $campaign->id,
                'metric_type' => $data['metric_type'],
                'target_value' => $data['target_value'],
                'current_value' => $data['current_value'] ?? 0,
            ]);
        });
    }

    public function updateGoal(CampaignGoal $goal, array $data): CampaignGoal
    {
        $goal->update($data);
        return $goal->fresh();
    }

    public function updateGoalProgress(CampaignGoal $goal, float $value): CampaignGoal
    {
        $goal->update(['current_value' => $value]);
        return $goal->fresh();
    }

    public function incrementGoalProgress(CampaignGoal $goal, float $increment = 1): CampaignGoal
    {
        $goal->increment('current_value', $increment);
        return $goal->fresh();
    }

    public function getGoalProgress(CampaignGoal $goal): float
    {
        if ($goal->target_value == 0) {
            return 0;
        }

        return min(100, ($goal->current_value / $goal->target_value) * 100);
    }

    public function isGoalCompleted(CampaignGoal $goal): bool
    {
        return $goal->current_value >= $goal->target_value;
    }

    public function getCampaignGoalsSummary(Campaign $campaign): array
    {
        $goals = $campaign->goals;
        
        $totalGoals = $goals->count();
        $completedGoals = $goals->filter(function ($goal) {
            return $this->isGoalCompleted($goal);
        })->count();

        $averageProgress = $goals->isEmpty() ? 0 : $goals->avg(function ($goal) {
            return $this->getGoalProgress($goal);
        });

        return [
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'pending_goals' => $totalGoals - $completedGoals,
            'completion_rate' => $totalGoals > 0 ? ($completedGoals / $totalGoals) * 100 : 0,
            'average_progress' => round($averageProgress, 2),
            'goals' => $goals->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'metric_type' => $goal->metric_type,
                    'target_value' => $goal->target_value,
                    'current_value' => $goal->current_value,
                    'progress' => $this->getGoalProgress($goal),
                    'is_completed' => $this->isGoalCompleted($goal),
                ];
            }),
        ];
    }

    public function syncGoalsFromMetrics(Campaign $campaign, array $metrics): void
    {
        foreach ($metrics as $metricType => $value) {
            $goal = $campaign->goals()->where('metric_type', $metricType)->first();
            
            if ($goal) {
                $this->updateGoalProgress($goal, $value);
            }
        }
    }
}


