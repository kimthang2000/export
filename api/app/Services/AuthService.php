<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Handle user registration logic.
     *
     * @param array $data
     * @return User
     */
    public function registerUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            $user->roles()->attach($memberRole);
        }

        return $user;
    }

    /**
     * Generate a new refresh token.
     *
     * @param User $user
     * @return string
     */
    public function generateRefreshToken(User $user): string
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

    /**
     * Refresh the access and refresh tokens using the old refresh token.
     *
     * @param string $tokenStr
     * @return array|null
     */
    public function refreshAccessToken(string $tokenStr): ?array
    {
        $refreshToken = RefreshToken::where('token', hash('sha256', $tokenStr))->first();

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->expires_at->isPast()) {
            return null;
        }

        $user = $refreshToken->user;
        
        // Revoke old refresh token
        $refreshToken->update(['revoked' => true]);

        // Generate new tokens
        $newAccessToken = auth('api')->login($user);
        $newRefreshTokenStr = $this->generateRefreshToken($user);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshTokenStr,
        ];
    }

    /**
     * Logout the user and revoke all refresh tokens.
     *
     * @param User|null $user
     * @return void
     */
    public function logoutUser(?User $user): void
    {
        if ($user) {
            // Revoke all refresh tokens for the user
            $user->refreshTokens()->update(['revoked' => true]);
        }
        
        auth('api')->logout();
    }
}
