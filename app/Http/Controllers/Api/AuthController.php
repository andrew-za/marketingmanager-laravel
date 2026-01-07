<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * API Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'customer',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'message' => 'User registered successfully',
        ], 201);
    }

    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
            ],
            'message' => 'Token refreshed successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()),
            'message' => 'User retrieved successfully',
        ]);
    }
}

