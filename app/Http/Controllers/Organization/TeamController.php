<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Organization\TeamService;
use App\Http\Requests\Organization\AddTeamMemberRequest;
use App\Http\Requests\Organization\InviteTeamMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organization Team Controller
 * Handles team member management
 * Requires organization admin access
 */
class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService
    ) {}

    /**
     * Display team member list
     */
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $teamMembers = $this->teamService->getTeamMembers($organization);
        $availableRoles = $this->teamService->getAvailableRoles();

        return view('organization.team.index', [
            'organization' => $organization,
            'teamMembers' => $teamMembers,
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Add team member
     */
    public function addMember(AddTeamMemberRequest $request, Organization $organization): JsonResponse
    {
        $this->teamService->addTeamMember(
            $organization,
            $request->input('user_id'),
            $request->input('role_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Team member added successfully.',
        ], 201);
    }

    /**
     * Remove team member
     */
    public function removeMember(Request $request, Organization $organization, int $userId): JsonResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $this->teamService->removeTeamMember($organization, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Team member removed successfully.',
        ]);
    }

    /**
     * Update team member role
     */
    public function updateRole(Request $request, Organization $organization, int $userId): JsonResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $this->teamService->updateTeamMemberRole(
            $organization,
            $userId,
            $request->input('role_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Team member role updated successfully.',
        ]);
    }

    /**
     * Invite team member
     */
    public function invite(InviteTeamMemberRequest $request, Organization $organization): JsonResponse
    {
        $this->teamService->inviteTeamMember(
            $organization,
            $request->input('email'),
            $request->input('role_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully.',
        ], 201);
    }

    /**
     * Get available roles
     */
    public function getRoles(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $roles = $this->teamService->getAvailableRoles();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}

