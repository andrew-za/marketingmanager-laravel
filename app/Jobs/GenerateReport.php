<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\Report;
use App\Models\User;
use App\Services\Analytics\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate Report Job
 * Processes report generation asynchronously
 */
class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Report $report,
        public Organization $organization,
        public User $user
    ) {}

    public function handle(ReportService $reportService): void
    {
        try {
            $reportService->generateReportData($this->report);
            Log::info("Report {$this->report->id} generated successfully");
        } catch (\Exception $e) {
            Log::error("Failed to generate report {$this->report->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateReport job failed after {$this->tries} attempts for report {$this->report->id}: " . $exception->getMessage());
        
        $this->report->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}

