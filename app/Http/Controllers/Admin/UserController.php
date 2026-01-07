<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\AssignRolesRequest;
use App\Models\User;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $userService
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display user list with search and filtering
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'user_type', 'status', 'email_verified']);
        $users = $this->userService->getUsers($filters);

        return view('admin.users.index', compact('users', 'filters'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $activityLogs = $this->userService->getUserActivityLogs($user);
        return view('admin.users.show', compact('user', 'activityLogs'));
    }

    /**
     * Show edit user form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user details
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->updateUser($user, $request->validated());

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(AssignRolesRequest $request, User $user)
    {
        $this->userService->assignRoles(
            $user,
            $request->validated()['role_ids'],
            $request->validated()['organization_id'] ?? null
        );

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Roles assigned successfully.');
    }

    /**
     * Deactivate user
     */
    public function deactivate(User $user)
    {
        $this->userService->deactivateUser($user);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Reactivate user
     */
    public function reactivate(User $user)
    {
        $this->userService->reactivateUser($user);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User reactivated successfully.');
    }
}


