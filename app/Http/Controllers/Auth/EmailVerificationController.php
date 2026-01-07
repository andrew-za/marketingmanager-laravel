<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended();
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended()->with('verified', true);
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended();
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('verification-link-sent'));
    }
}


