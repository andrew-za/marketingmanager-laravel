<?php

namespace App\Http\Controllers;

use App\Services\Auth\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ProfileController extends Controller
{
    public function __construct(
        private SessionService $sessionService
    ) {}

    /**
     * Show user profile page
     */
    public function show()
    {
        $user = Auth::user();
        $sessions = $this->sessionService->getActiveSessions($user);

        return view('profile.show', [
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'timezone' => ['required', 'string', 'timezone'],
            'locale' => ['nullable', 'string', 'max:10'],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', __('Profile updated successfully.'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('profile.show')
            ->with('success', __('Password updated successfully.'));
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($user->avatar && file_exists(public_path('storage/' . $user->avatar))) {
            unlink(public_path('storage/' . $user->avatar));
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()->route('profile.show')
            ->with('success', __('Avatar updated successfully.'));
    }

    /**
     * Show security settings page
     */
    public function showSecurity()
    {
        $user = Auth::user();
        $sessions = $this->sessionService->getActiveSessions($user);

        return view('profile.security', [
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke a session
     */
    public function revokeSession(Request $request, string $sessionId)
    {
        $user = Auth::user();

        if ($this->sessionService->revokeSession($user, $sessionId)) {
            return redirect()->route('profile.security')
                ->with('success', __('Session revoked successfully.'));
        }

        return redirect()->route('profile.security')
            ->with('error', __('Failed to revoke session.'));
    }

    /**
     * Revoke all other sessions
     */
    public function revokeOtherSessions(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        $this->sessionService->revokeOtherSessions($user, $currentSessionId);

        return redirect()->route('profile.security')
            ->with('success', __('All other sessions have been revoked.'));
    }
}


