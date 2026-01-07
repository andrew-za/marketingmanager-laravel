<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Organization;
use App\Models\Report;
use App\Services\AgencyService;
use App\Services\AgencyReportService;
use App\Services\PdfService;
use App\Jobs\GenerateAgencyReport;
use Illuminate\Http\Request;

/**
 * Agency Report Controller
 * Handles report generation for agency clients
 */
class ReportController extends Controller
{
    public function __construct(
        private AgencyService $agencyService,
        private AgencyReportService $reportService,
        private PdfService $pdfService
    ) {}

    /**
     * Display report generation interface
     */
    public function index(Request $request, Agency $agency)
    {
        $clients = $agency->clientOrganizations()
            ->orderBy('name')
            ->get();

        $reports = Report::where('organization_id', $agency->clientOrganizations()->pluck('id'))
            ->where('type', 'like', 'agency_%')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('agency.reports.index', [
            'agency' => $agency,
            'clients' => $clients,
            'reports' => $reports,
        ]);
    }

    /**
     * Generate report for a client
     */
    public function generate(Request $request, Agency $agency)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'report_type' => 'required|in:weekly,monthly,quarterly',
        ]);

        $organization = Organization::findOrFail($request->organization_id);
        
        // Verify organization belongs to agency
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        if (!in_array($organization->id, $clientOrganizationIds)) {
            abort(403, 'Organization does not belong to agency clients.');
        }

        // Dispatch job for async processing
        GenerateAgencyReport::dispatch($agency, $organization, $request->report_type);

        return redirect()->route('agency.reports', ['agency' => $agency])
            ->with('success', 'Report generation started. It will be available shortly.');
    }

    /**
     * Download report PDF
     */
    public function download(Agency $agency, Report $report)
    {
        // Verify report belongs to agency client
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        if (!in_array($report->organization_id, $clientOrganizationIds)) {
            abort(403, 'Report does not belong to agency clients.');
        }

        $filename = "report-{$report->id}-{$report->created_at->format('Y-m-d')}.pdf";
        
        return $this->pdfService->downloadReportPdf($report->data, $filename);
    }
}

