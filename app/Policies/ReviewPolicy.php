<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContentApproval;
use App\Models\Organization;

/**
 * Review Policy
 * Controls access to content approval/review functionality
 * Only assigned reviewers can approve/reject content
 */
class ReviewPolicy
{
    /**
     * Determine if user can view any content approvals
     */
    public function viewAny(User $user, Organization $organization = null): bool
    {
        if (!$organization) {
            $organizationId = request()->route('organizationId');
            $organization = $organizationId ? Organization::find($organizationId) : null;
        }

        if (!$organization) {
            return false;
        }

        // All users with access to organization can view approvals
        return $user->hasAccessToOrganization($organization->id);
    }

    /**
     * Determine if user can view a specific content approval
     */
    public function view(User $user, ContentApproval $approval): bool
    {
        if (!$user->hasAccessToOrganization($approval->organization_id)) {
            return false;
        }

        // User can view if they are the requester, approver, or have organization access
        return $approval->requested_by === $user->id 
            || $approval->approved_by === $user->id
            || $user->hasAccessToOrganization($approval->organization_id);
    }

    /**
     * Determine if user can request approval for content
     */
    public function requestApproval(User $user, Organization $organization = null): bool
    {
        if (!$organization) {
            $organizationId = request()->route('organizationId');
            $organization = $organizationId ? Organization::find($organizationId) : null;
        }

        if (!$organization) {
            return false;
        }

        // Client role cannot request approvals
        if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
            return false;
        }

        return $user->hasAccessToOrganization($organization->id);
    }

    /**
     * Determine if user can approve content
     * Only the assigned reviewer can approve
     */
    public function approve(User $user, ContentApproval $approval): bool
    {
        if (!$user->hasAccessToOrganization($approval->organization_id)) {
            return false;
        }

        // Only the assigned approver can approve
        if ($approval->approved_by !== $user->id) {
            return false;
        }

        // Can only approve pending approvals
        if (!$approval->isPending()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can reject content
     * Only the assigned reviewer can reject
     */
    public function reject(User $user, ContentApproval $approval): bool
    {
        if (!$user->hasAccessToOrganization($approval->organization_id)) {
            return false;
        }

        // Only the assigned approver can reject
        if ($approval->approved_by !== $user->id) {
            return false;
        }

        // Can only reject pending approvals
        if (!$approval->isPending()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can view approval history
     */
    public function viewHistory(User $user, Organization $organization = null): bool
    {
        if (!$organization) {
            $organizationId = request()->route('organizationId');
            $organization = $organizationId ? Organization::find($organizationId) : null;
        }

        if (!$organization) {
            return false;
        }

        return $user->hasAccessToOrganization($organization->id);
    }
}

