<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            $user->roles()->attach($memberRole);
        }

        $token = auth('api')->login($user);
        $refreshTokenStr = $this->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshTokenStr,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();
        $refreshTokenStr = $this->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshTokenStr,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);

        $tokenStr = $request->refresh_token;

        $refreshToken = RefreshToken::where('token', hash('sha256', $tokenStr))->first();

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->expires_at->isPast()) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $refreshToken->user;
        
        $refreshToken->update(['revoked' => true]);

        $newAccessToken = auth('api')->login($user);
        $newRefreshTokenStr = $this->generateRefreshToken($user);

        return response()->json([
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshTokenStr,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        $user = auth('api')->user();
        if ($user) {
            $user->refreshTokens()->update(['revoked' => true]);
        }
        
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me()
    {
        $requestInfo = request();
        $user = auth('api')->user()->load('roles.permissions');
        return response()->json($user);
    }

    protected function generateRefreshToken(User $user): string
    {
        $tokenStr = Str::random(60);
        
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $tokenStr),
            'expires_at' => now()->addDays(30),
            'revoked' => false,
        ]);

        return $tokenStr;
    }
}
