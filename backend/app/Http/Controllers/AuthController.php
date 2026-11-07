<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Checks email and password, then returns a new Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        // Missing or malformed fields -> 422 (handled by Laravel).
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Wrong email or password -> 401.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('Login failed', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        Log::info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * POST /api/logout
     * Deletes only the token used for this request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    /**
     * GET /api/user
     * Returns the logged-in user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
