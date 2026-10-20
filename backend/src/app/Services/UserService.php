<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerCustomer(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
        ];

        // Handle photo upload
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $userData['photo'] = $data['photo']->store('profile-photos', 'public');
        }

        $user = $this->userRepository->create($userData);

        $user->assignRole('customer');

        return $user;
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function updateUserProfile(User $user, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
        ];

        // Handle photo removal
        if (isset($data['remove_photo']) && $data['remove_photo']) {
            // Delete old photo if exists
            if (!empty($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $updateData['photo'] = null;
        }
        // Handle new photo upload
        elseif (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            // Delete old photo if exists
            if (!empty($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $updateData['photo'] = $data['photo']->store('profile-photos', 'public');
        }

        return $this->userRepository->update($user, $updateData);
    }

    public function formatUserResponse(User $user): array
    {
        // Generate full URL for photo
        $photoUrl = null;
        if (!empty($user->photo)) {
            $photoUrl = url(Storage::url($user->photo));
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'photo' => $photoUrl,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
