<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Social Media Integration scheduled tasks
Schedule::group(function () {
    // Monitor social media connections (every hour)
    Schedule::command('social:monitor-connections')->hourly();
    
    // Refresh expired social media tokens (every hour)
    Schedule::command('social:tokens-refresh')->hourly();
})->name('social-media');

// Publishing scheduled tasks
Schedule::group(function () {
    // Publish scheduled posts (every minute)
    Schedule::call(function () {
        $scheduledPosts = \App\Models\ScheduledPost::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with('socialConnections')
            ->get();

        foreach ($scheduledPosts as $post) {
            foreach ($post->socialConnections as $connection) {
                \App\Jobs\PublishScheduledPost::dispatch($post, $connection);
            }
        }
    })->everyMinute()->name('publish-scheduled-posts');
})->name('publishing');

// Email Campaign scheduled tasks
Schedule::group(function () {
    // Process email campaign queue (every 5 minutes)
    Schedule::call(function () {
        \App\Jobs\ProcessEmailCampaignQueue::dispatch();
    })->everyFiveMinutes()->name('process-email-campaigns');
})->name('email-marketing');

// Report Generation scheduled tasks
Schedule::group(function () {
    // Generate scheduled reports (daily at 8 AM)
    Schedule::call(function () {
        $schedules = \App\Models\ReportSchedule::where('next_run_at', '<=', now())
            ->with(['report.organization', 'report.creator'])
            ->get();

        foreach ($schedules as $schedule) {
            $report = $schedule->report;
            $organization = $report->organization;
            $user = $report->creator;

            if ($organization && $user) {
                \App\Jobs\GenerateReport::dispatch($report, $organization, $user);
            }

            // Update next run time based on frequency
            $nextRunAt = match ($schedule->frequency) {
                'daily' => \Carbon\Carbon::now()->addDay()->startOfDay(),
                'weekly' => \Carbon\Carbon::now()->addWeek()->startOfWeek(),
                'monthly' => \Carbon\Carbon::now()->addMonth()->startOfMonth(),
                default => \Carbon\Carbon::now()->addDay(),
            };

            $schedule->update([
                'next_run_at' => $nextRunAt,
                'last_run_at' => now(),
            ]);
        }
    })->dailyAt('08:00')->name('generate-scheduled-reports');
})->name('reporting');

// Competitor Monitoring scheduled tasks
Schedule::group(function () {
    // Monitor competitors (daily at 6 AM)
    Schedule::call(function () {
        \App\Jobs\MonitorCompetitors::dispatch();
    })->dailyAt('06:00')->name('monitor-competitors');
})->name('competitor-monitoring');

// Agency Billing scheduled tasks
Schedule::group(function () {
    // Send invoice reminders (daily at 9 AM)
    Schedule::call(function () {
        $agencies = \App\Models\Agency::all();
        foreach ($agencies as $agency) {
            $clientOrganizationIds = app(\App\Services\AgencyService::class)
                ->getClientOrganizationIds($agency);
            
            if (!empty($clientOrganizationIds)) {
                \App\Jobs\SendInvoiceReminders::dispatch($clientOrganizationIds);
            }
        }
    })->dailyAt('09:00')->name('invoice-reminders');
})->name('agency-billing');


