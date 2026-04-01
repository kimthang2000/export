<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshTokenRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->registerUser($request->validated());

        $token = auth('api')->login($user);
        $refreshTokenStr = $this->authService->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshTokenStr,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! $token = auth('api')->attempt($request->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();
        $refreshTokenStr = $this->authService->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshTokenStr,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tokens = $this->authService->refreshAccessToken($request->refresh_token);

        if (!$tokens) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        return response()->json(array_merge($tokens, [
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]));
    }

    public function logout(): JsonResponse
    {
        $this->authService->logoutUser(auth('api')->user());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user()->load('roles.permissions');
        return response()->json($user);
    }
}
