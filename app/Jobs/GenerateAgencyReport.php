<?php

namespace App\Jobs;

use App\Models\Agency;
use App\Models\Organization;
use App\Services\AgencyReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate Agency Report Job
 * Processes AI-powered report generation asynchronously
 */
class GenerateAgencyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Agency $agency,
        public Organization $organization,
        public string $reportType
    ) {}

    public function handle(AgencyReportService $reportService): array
    {
        try {
            $report = $reportService->generateReport(
                $this->agency,
                $this->organization,
                $this->reportType
            );

            Log::info("Agency report generated successfully for organization {$this->organization->id}");

            return $report;
        } catch (\Exception $e) {
            Log::error("Failed to generate agency report: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateAgencyReport job failed after {$this->tries} attempts for organization {$this->organization->id}: " . $exception->getMessage());
    }
}

