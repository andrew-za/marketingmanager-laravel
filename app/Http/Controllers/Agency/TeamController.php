<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Services\AgencyTeamService;
use App\Http\Requests\Agency\AddTeamMemberRequest;
use App\Http\Requests\Agency\UpdateTeamMemberRoleRequest;
use Illuminate\Http\Request;

/**
 * Agency Team Controller
 * Handles agency team member management
 * Requires agency admin access
 */
class TeamController extends Controller
{
    public function __construct(
        private AgencyTeamService $teamService
    ) {}

    /**
     * Display team member list
     */
    public function index(Request $request, Agency $agency)
    {
        $teamMembers = $this->teamService->getTeamMembers($agency);

        return view('agency.team.index', [
            'agency' => $agency,
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Add team member to agency
     */
    public function store(AddTeamMemberRequest $request, Agency $agency)
    {
        $user = User::findOrFail($request->user_id);
        
        $this->teamService->addTeamMember($agency, $user, $request->role);

        return redirect()->route('agency.team', ['agency' => $agency])
            ->with('success', 'Team member added successfully.');
    }

    /**
     * Update team member role
     */
    public function update(UpdateTeamMemberRoleRequest $request, Agency $agency, User $user)
    {
        $this->teamService->updateTeamMemberRole($agency, $user, $request->role);

        return redirect()->route('agency.team', ['agency' => $agency])
            ->with('success', 'Team member role updated successfully.');
    }

    /**
     * Remove team member from agency
     */
    public function destroy(Agency $agency, User $user)
    {
        $this->teamService->removeTeamMember($agency, $user);

        return redirect()->route('agency.team', ['agency' => $agency])
            ->with('success', 'Team member removed successfully.');
    }
}

