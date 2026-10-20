<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Attempt login with credentials
     */
    protected function attemptLogin(array $credentials): ?User
    {
        $user = $this->userService->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Token-based login (for API/mobile clients)
     */
    public function tokenLogin(array $credentials): ?array
    {
        $user = $this->attemptLogin($credentials);

        if (!$user) {
            return null;
        }

        // Revoke all previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'user' => $this->userService->formatUserResponse($user),
            'token' => $token,
        ];
    }

    /**
     * Register a new customer and return with token
     */
    public function register(array $data): array
    {
        $user = $this->userService->registerCustomer($data);

        // Create token for immediate login
        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'user' => $this->userService->formatUserResponse($user),
            'token' => $token,
        ];
    }

    /**
     * Logout user by revoking current token
     */
    public function logout(Request $request): void
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(User $user): array
    {
        return [
            'user' => $this->userService->formatUserResponse($user),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): array
    {
        $updatedUser = $this->userService->updateUserProfile($user, $data);

        return [
            'user' => $this->userService->formatUserResponse($updatedUser),
        ];
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        return true;
    }
}
