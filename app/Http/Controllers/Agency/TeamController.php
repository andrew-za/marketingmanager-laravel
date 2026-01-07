<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Services\AgencyTeamService;
use App\Http\Requests\Agency\AddTeamMemberRequest;
use App\Http\Requests\Agency\UpdateTeamMemberRoleRequest;
use App\Http\Requests\Agency\ManageClientAccessRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    /**
     * Grant client access to team member
     */
    public function grantClientAccess(
        Agency $agency,
        User $user,
        Organization $organization
    ): JsonResponse {
        try {
            $this->teamService->grantClientAccess($agency, $user, $organization);

            return response()->json([
                'success' => true,
                'message' => 'Client access granted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Revoke client access from team member
     */
    public function revokeClientAccess(
        Agency $agency,
        User $user,
        Organization $organization
    ): JsonResponse {
        $this->teamService->revokeClientAccess($agency, $user, $organization);

        return response()->json([
            'success' => true,
            'message' => 'Client access revoked successfully.',
        ]);
    }

    /**
     * Bulk grant client access to team member
     */
    public function bulkGrantClientAccess(
        ManageClientAccessRequest $request,
        Agency $agency,
        User $user
    ): JsonResponse {
        $granted = $this->teamService->bulkGrantClientAccess(
            $agency,
            $user,
            $request->validated()['organization_ids']
        );

        return response()->json([
            'success' => true,
            'message' => "Client access granted to {$granted} organization(s).",
            'data' => [
                'granted_count' => $granted,
            ],
        ]);
    }

    /**
     * Bulk revoke client access from team member
     */
    public function bulkRevokeClientAccess(
        ManageClientAccessRequest $request,
        Agency $agency,
        User $user
    ): JsonResponse {
        $revoked = $this->teamService->bulkRevokeClientAccess(
            $agency,
            $user,
            $request->validated()['organization_ids']
        );

        return response()->json([
            'success' => true,
            'message' => "Client access revoked from {$revoked} organization(s).",
            'data' => [
                'revoked_count' => $revoked,
            ],
        ]);
    }

    /**
     * Get accessible clients for team member
     */
    public function getAccessibleClients(
        Agency $agency,
        User $user
    ): JsonResponse {
        $clients = $this->teamService->getAccessibleClients($agency, $user);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Get team members with access to client
     */
    public function getTeamMembersWithClientAccess(
        Agency $agency,
        Organization $organization
    ): JsonResponse {
        $teamMembers = $this->teamService->getTeamMembersWithClientAccess($agency, $organization);

        return response()->json([
            'success' => true,
            'data' => $teamMembers,
        ]);
    }
}

