<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->authenticated($request, Auth::user());
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        return match($user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'agency' => redirect()->route('agency.dashboard', ['agencyId' => $user->primaryAgency()?->id ?? 1]),
            'customer' => redirect()->route('main.organizations'),
            default => redirect()->route('main.organizations'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

