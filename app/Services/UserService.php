<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Get all users
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    /**
     * Get user by ID
     */
    public function getUserById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $data['password'] = bcrypt($data['password']);
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->save();
        return $user;
    }

    /**
     * Update user
     */
    public function updateUser(string $id, array $data): ?User
    {
        $user = User::find($id);
        if ($user) {
            $user->update($data);
        }
        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(string $id): bool
    {
        $user = User::find($id);
        return $user ? $user->delete() : false;
    }
}