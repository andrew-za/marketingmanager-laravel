<?php

namespace App\Services\Analytics;

use App\Models\Report;
use App\Models\ReportSchedule;
use App\Models\ReportShare;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Create a new report
     */
    public function createReport(
        Organization $organization,
        User $user,
        array $data
    ): Report {
        return Report::create([
            'organization_id' => $organization->id,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'custom',
            'config' => $data['config'] ?? [],
            'schedule' => $data['schedule'] ?? null,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Update report
     */
    public function updateReport(
        Report $report,
        array $data
    ): Report {
        $report->update([
            'name' => $data['name'] ?? $report->name,
            'type' => $data['type'] ?? $report->type,
            'config' => $data['config'] ?? $report->config,
            'schedule' => $data['schedule'] ?? $report->schedule,
        ]);

        return $report->fresh();
    }

    /**
     * Generate report data
     */
    public function generateReportData(
        Report $report,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $config = $report->config ?? [];
        $type = $report->type;

        $data = match ($type) {
            'campaign' => $this->generateCampaignReport($report->organization, $startDate, $endDate, $config),
            'social_media' => $this->generateSocialMediaReport($report->organization, $startDate, $endDate, $config),
            'email' => $this->generateEmailReport($report->organization, $startDate, $endDate, $config),
            'overall' => $this->generateOverallReport($report->organization, $startDate, $endDate, $config),
            default => $this->generateCustomReport($report->organization, $startDate, $endDate, $config),
        };

        $report->update([
            'last_generated_at' => Carbon::now(),
        ]);

        return $data;
    }

    /**
     * Schedule report generation
     */
    public function scheduleReport(
        Report $report,
        string $frequency,
        ?Carbon $nextRunAt = null
    ): ReportSchedule {
        $nextRunAt = $nextRunAt ?? $this->calculateNextRun($frequency);

        return ReportSchedule::create([
            'report_id' => $report->id,
            'frequency' => $frequency,
            'next_run_at' => $nextRunAt,
        ]);
    }

    /**
     * Share report with user
     */
    public function shareReport(
        Report $report,
        User $user,
        array $permissions = ['view']
    ): ReportShare {
        return ReportShare::updateOrCreate(
            [
                'report_id' => $report->id,
                'shared_with_user_id' => $user->id,
            ],
            [
                'permissions' => $permissions,
            ]
        );
    }

    /**
     * Export report to format
     */
    public function exportReport(
        Report $report,
        string $format = 'pdf',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): string {
        $data = $this->generateReportData($report, $startDate, $endDate);

        return match ($format) {
            'pdf' => $this->exportToPdf($report, $data),
            'excel' => $this->exportToExcel($report, $data),
            'csv' => $this->exportToCsv($report, $data),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Generate campaign report
     */
    private function generateCampaignReport(
        Organization $organization,
        Carbon $startDate,
        Carbon $endDate,
        array $config
    ): array {
        $campaignIds = $config['campaign_ids'] ?? [];
        
        $query = \App\Models\Campaign::where('organization_id', $organization->id)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if (!empty($campaignIds)) {
            $query->whereIn('id', $campaignIds);
        }

        $campaigns = $query->get();

        return [
            'type' => 'campaign',
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'campaigns' => $campaigns->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'budget' => $campaign->budget,
                    'spent' => $campaign->spent,
                    'posts_count' => $campaign->scheduledPosts()->count(),
                ];
            }),
            'summary' => [
                'total_campaigns' => $campaigns->count(),
                'total_budget' => $campaigns->sum('budget'),
                'total_spent' => $campaigns->sum('spent'),
            ],
        ];
    }

    /**
     * Generate social media report
     */
    private function generateSocialMediaReport(
        Organization $organization,
        Carbon $startDate,
        Carbon $endDate,
        array $config
    ): array {
        $analyticsService = app(AnalyticsService::class);
        $engagement = $analyticsService->getSocialMediaEngagement($organization, $startDate, $endDate);

        return [
            'type' => 'social_media',
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'metrics' => $engagement,
        ];
    }

    /**
     * Generate email report
     */
    private function generateEmailReport(
        Organization $organization,
        Carbon $startDate,
        Carbon $endDate,
        array $config
    ): array {
        $campaigns = \App\Models\EmailCampaign::where('organization_id', $organization->id)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();

        return [
            'type' => 'email',
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'campaigns' => $campaigns->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'recipients_count' => $campaign->recipients()->count(),
                    'sent_count' => $campaign->recipients()->where('status', 'sent')->count(),
                    'opened_count' => $campaign->recipients()->where('opened_at', '!=', null)->count(),
                    'clicked_count' => $campaign->recipients()->where('clicked_at', '!=', null)->count(),
                ];
            }),
            'summary' => [
                'total_campaigns' => $campaigns->count(),
                'total_sent' => $campaigns->sum(function ($c) {
                    return $c->recipients()->where('status', 'sent')->count();
                }),
                'total_opened' => $campaigns->sum(function ($c) {
                    return $c->recipients()->where('opened_at', '!=', null)->count();
                }),
                'total_clicked' => $campaigns->sum(function ($c) {
                    return $c->recipients()->where('clicked_at', '!=', null)->count();
                }),
            ],
        ];
    }

    /**
     * Generate overall report
     */
    private function generateOverallReport(
        Organization $organization,
        Carbon $startDate,
        Carbon $endDate,
        array $config
    ): array {
        $analyticsService = app(AnalyticsService::class);
        
        return [
            'type' => 'overall',
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'social_media' => $analyticsService->getSocialMediaEngagement($organization, $startDate, $endDate),
            'campaigns' => $this->generateCampaignReport($organization, $startDate, $endDate, []),
            'email' => $this->generateEmailReport($organization, $startDate, $endDate, []),
        ];
    }

    /**
     * Generate custom report
     */
    private function generateCustomReport(
        Organization $organization,
        Carbon $startDate,
        Carbon $endDate,
        array $config
    ): array {
        return [
            'type' => 'custom',
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'data' => $config,
        ];
    }

    /**
     * Calculate next run time based on frequency
     */
    private function calculateNextRun(string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => Carbon::now()->addDay()->startOfDay(),
            'weekly' => Carbon::now()->addWeek()->startOfWeek(),
            'monthly' => Carbon::now()->addMonth()->startOfMonth(),
            default => Carbon::now()->addDay(),
        };
    }

    /**
     * Export to PDF
     */
    private function exportToPdf(Report $report, array $data): string
    {
        // TODO: Implement PDF export using DomPDF or Snappy
        return json_encode($data);
    }

    /**
     * Export to Excel
     */
    private function exportToExcel(Report $report, array $data): string
    {
        // TODO: Implement Excel export using Laravel Excel
        return json_encode($data);
    }

    /**
     * Export to CSV
     */
    private function exportToCsv(Report $report, array $data): string
    {
        // TODO: Implement CSV export
        return json_encode($data);
    }
}

