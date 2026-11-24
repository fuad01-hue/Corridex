<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse(
                'Invalid email or password credentials.',
                ['credentials' => ['The provided credentials do not match our records.']],
                'AUTHENTICATION_FAILED',
                401
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $userData = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully.',
            'data'    => [
                'token' => $token,
                'user'  => $userData,
            ],
            // Top-level aliases for backward-compatible clients
            'token'   => $token,
            'user'    => $userData,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Token revoked and user logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->successResponse([
            'id'    => $request->user()->id,
            'name'  => $request->user()->name,
            'email' => $request->user()->email,
            'role'  => $request->user()->role,
        ], 'Authenticated user profile retrieved successfully.');
    }
}
