<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService
    ) {}

    /**
     * Show the two-factor authentication setup page
     */
    public function showSetupForm()
    {
        $user = Auth::user();
        
        if ($user->two_factor_enabled) {
            return redirect()->route('profile.security')->with('info', __('Two-factor authentication is already enabled.'));
        }

        $secret = $this->twoFactorService->generateSecret();
        $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user, $secret);

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Enable two-factor authentication
     */
    public function enable(Request $request)
    {
        $request->validate([
            'secret' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        $secret = $request->input('secret');

        if (!$this->twoFactorService->verify(new User(['two_factor_secret' => encrypt($secret)]), $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => __('The provided code is invalid.'),
            ]);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        $this->twoFactorService->enable($user, $secret, $recoveryCodes);

        return redirect()->route('profile.security')
            ->with('success', __('Two-factor authentication has been enabled.'))
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Disable two-factor authentication
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $this->twoFactorService->disable($user);

        return redirect()->route('profile.security')
            ->with('success', __('Two-factor authentication has been disabled.'));
    }

    /**
     * Show the two-factor challenge form
     */
    public function showChallengeForm()
    {
        return view('auth.two-factor.challenge');
    }

    /**
     * Verify the two-factor code during login
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('login.id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);

        if ($this->twoFactorService->verify($user, $request->input('code'))) {
            Auth::login($user);
            $request->session()->put('two_factor_verified', true);
            $request->session()->forget('login.id');
            return redirect()->intended();
        }

        if ($request->has('recovery_code')) {
            if ($this->twoFactorService->verifyRecoveryCode($user, $request->input('recovery_code'))) {
                Auth::login($user);
                $request->session()->put('two_factor_verified', true);
                $request->session()->forget('login.id');
                return redirect()->intended();
            }
        }

        throw ValidationException::withMessages([
            'code' => __('The provided code is invalid.'),
        ]);
    }
}

