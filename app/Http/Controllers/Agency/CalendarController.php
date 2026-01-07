<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\ScheduledPost;
use App\Models\Campaign;
use App\Services\AgencyService;
use Illuminate\Http\Request;

/**
 * Agency Calendar Controller
 * Handles aggregated calendar view across all client organizations
 */
class CalendarController extends Controller
{
    public function __construct(
        private AgencyService $agencyService
    ) {}

    /**
     * Display aggregated calendar view
     */
    public function index(Request $request, Agency $agency)
    {
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        
        $startDate = $request->get('start', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end', now()->endOfMonth()->toDateString());

        // Get scheduled posts from all client organizations
        $scheduledPosts = ScheduledPost::whereIn('organization_id', $clientOrganizationIds)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['organization', 'campaign', 'channels'])
            ->get();

        // Get campaign launches
        $campaigns = Campaign::whereIn('organization_id', $clientOrganizationIds)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->with('organization')
            ->get();

        return view('agency.calendar.index', [
            'agency' => $agency,
            'scheduledPosts' => $scheduledPosts,
            'campaigns' => $campaigns,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'clientOrganizationIds' => $clientOrganizationIds,
        ]);
    }
}

