<?php

namespace App\Services\Scheduling;

use App\Models\ScheduledPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringScheduleService
{
    public function createRecurringPost(ScheduledPost $scheduledPost, array $recurrenceConfig): ScheduledPost
    {
        return DB::transaction(function () use ($scheduledPost, $recurrenceConfig) {
            $parentPost = $scheduledPost->replicate();
            $parentPost->is_recurring = true;
            $parentPost->recurrence_type = $recurrenceConfig['type'];
            $parentPost->recurrence_config = $recurrenceConfig;
            $parentPost->recurrence_end_date = $recurrenceConfig['end_date'] ?? null;
            $parentPost->recurrence_count = $recurrenceConfig['count'] ?? null;
            $parentPost->parent_post_id = null;
            $parentPost->save();

            $this->generateRecurringInstances($parentPost, $recurrenceConfig);

            return $parentPost;
        });
    }

    public function generateRecurringInstances(ScheduledPost $parentPost, array $recurrenceConfig): void
    {
        $startDate = Carbon::parse($parentPost->scheduled_at);
        $endDate = $recurrenceConfig['end_date'] 
            ? Carbon::parse($recurrenceConfig['end_date']) 
            : ($recurrenceConfig['count'] ? null : now()->addYear());
        
        $count = $recurrenceConfig['count'] ?? null;
        $interval = $recurrenceConfig['interval'] ?? 1;
        $daysOfWeek = $recurrenceConfig['days_of_week'] ?? null;
        $dayOfMonth = $recurrenceConfig['day_of_month'] ?? null;

        $instances = [];
        $currentDate = $startDate->copy();
        $generatedCount = 0;

        while (true) {
            if ($endDate && $currentDate->gt($endDate)) {
                break;
            }

            if ($count && $generatedCount >= $count) {
                break;
            }

            if ($currentDate->gt($startDate)) {
                $instances[] = $this->createRecurringInstance($parentPost, $currentDate);
                $generatedCount++;
            }

            $currentDate = $this->getNextOccurrence($currentDate, $recurrenceConfig, $interval, $daysOfWeek, $dayOfMonth);
            
            if ($currentDate->eq($startDate)) {
                break;
            }
        }

        ScheduledPost::insert($instances);
    }

    private function createRecurringInstance(ScheduledPost $parentPost, Carbon $scheduledAt): array
    {
        return [
            'organization_id' => $parentPost->organization_id,
            'campaign_id' => $parentPost->campaign_id,
            'channel_id' => $parentPost->channel_id,
            'content' => $parentPost->content,
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'metadata' => $parentPost->metadata,
            'created_by' => $parentPost->created_by,
            'is_recurring' => false,
            'parent_post_id' => $parentPost->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function getNextOccurrence(Carbon $currentDate, array $config, int $interval, ?array $daysOfWeek, ?int $dayOfMonth): Carbon
    {
        $nextDate = $currentDate->copy();

        switch ($config['type']) {
            case 'daily':
                $nextDate->addDays($interval);
                break;

            case 'weekly':
                if ($daysOfWeek) {
                    $nextDate = $this->getNextWeekday($nextDate, $daysOfWeek, $interval);
                } else {
                    $nextDate->addWeeks($interval);
                }
                break;

            case 'monthly':
                if ($dayOfMonth) {
                    $nextDate->addMonths($interval);
                    $nextDate->day($dayOfMonth);
                } else {
                    $nextDate->addMonths($interval);
                }
                break;

            case 'custom':
                if (isset($config['custom_pattern'])) {
                    $nextDate = $this->applyCustomPattern($nextDate, $config['custom_pattern'], $interval);
                }
                break;
        }

        return $nextDate;
    }

    private function getNextWeekday(Carbon $date, array $daysOfWeek, int $interval): Carbon
    {
        $currentDay = $date->dayOfWeek;
        $nextDay = null;

        sort($daysOfWeek);

        foreach ($daysOfWeek as $day) {
            if ($day > $currentDay) {
                $nextDay = $day;
                break;
            }
        }

        if ($nextDay === null) {
            $nextDay = $daysOfWeek[0];
            $date->addWeeks($interval);
        }

        $date->next($this->getDayName($nextDay));

        return $date;
    }

    private function getDayName(int $dayOfWeek): string
    {
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        return $days[$dayOfWeek];
    }

    private function applyCustomPattern(Carbon $date, string $pattern, int $interval): Carbon
    {
        if (preg_match('/(\d+)([dwmy])/', $pattern, $matches)) {
            $amount = (int) $matches[1] * $interval;
            $unit = $matches[2];

            return match ($unit) {
                'd' => $date->addDays($amount),
                'w' => $date->addWeeks($amount),
                'm' => $date->addMonths($amount),
                'y' => $date->addYears($amount),
                default => $date,
            };
        }

        return $date;
    }

    public function cancelRecurringSchedule(ScheduledPost $parentPost): void
    {
        DB::transaction(function () use ($parentPost) {
            $parentPost->update(['is_recurring' => false]);
            
            ScheduledPost::where('parent_post_id', $parentPost->id)
                ->where('status', 'pending')
                ->delete();
        });
    }

    public function updateRecurringSchedule(ScheduledPost $parentPost, array $recurrenceConfig): ScheduledPost
    {
        return DB::transaction(function () use ($parentPost, $recurrenceConfig) {
            $this->cancelRecurringSchedule($parentPost);
            
            $parentPost->update([
                'is_recurring' => true,
                'recurrence_type' => $recurrenceConfig['type'],
                'recurrence_config' => $recurrenceConfig,
                'recurrence_end_date' => $recurrenceConfig['end_date'] ?? null,
                'recurrence_count' => $recurrenceConfig['count'] ?? null,
            ]);

            $this->generateRecurringInstances($parentPost, $recurrenceConfig);

            return $parentPost->fresh();
        });
    }
}

