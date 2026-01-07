<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Report;
use App\Services\Analytics\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * List all reports
     */
    public function index(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $reports = Report::where('organization_id', $organizationId)
            ->with('creator')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Create new report
     */
    public function store(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:campaign,social_media,email,overall,custom'],
            'config' => ['nullable', 'array'],
            'schedule' => ['nullable', 'array'],
        ]);

        $report = $this->reportService->createReport(
            $organization,
            $request->user(),
            $request->only(['name', 'type', 'config', 'schedule'])
        );

        return response()->json([
            'success' => true,
            'data' => $report->load('creator'),
        ], 201);
    }

    /**
     * Show report
     */
    public function show(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->with('creator', 'schedules', 'shares')
            ->findOrFail($reportId);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update report
     */
    public function update(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:campaign,social_media,email,overall,custom'],
            'config' => ['sometimes', 'array'],
            'schedule' => ['sometimes', 'array'],
        ]);

        $report = $this->reportService->updateReport(
            $report,
            $request->only(['name', 'type', 'config', 'schedule'])
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Delete report
     */
    public function destroy(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully.',
        ]);
    }

    /**
     * Generate report data
     */
    public function generate(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $data = $this->reportService->generateReportData($report, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Schedule report
     */
    public function schedule(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $request->validate([
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'next_run_at' => ['nullable', 'date'],
        ]);

        $nextRunAt = $request->next_run_at ? Carbon::parse($request->next_run_at) : null;

        $schedule = $this->reportService->scheduleReport(
            $report,
            $request->frequency,
            $nextRunAt
        );

        return response()->json([
            'success' => true,
            'data' => $schedule->load('report'),
        ], 201);
    }

    /**
     * Share report
     */
    public function share(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'in:view,edit,delete'],
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);

        $share = $this->reportService->shareReport(
            $report,
            $user,
            $request->permissions
        );

        return response()->json([
            'success' => true,
            'data' => $share->load('sharedWith'),
        ], 201);
    }

    /**
     * Export report
     */
    public function export(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = Report::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        $request->validate([
            'format' => ['required', 'string', 'in:pdf,excel,csv'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $export = $this->reportService->exportReport(
            $report,
            $request->format,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'format' => $request->format,
                'export' => $export,
            ],
        ]);
    }
}

