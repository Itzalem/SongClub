<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface IUserRepository
{
    public function getUserById(int $id): ?User;
    public function getUserByEmail(string $email): ?User;
    public function getUserByUsername(string $username): ?User;
    public function getAllUsers(): array;
    public function searchUser(string $query): array;
    public function createUser(User $user): int;
    public function updateUser(User $user): int;
    public function updateUserFull(User $user): void;
    public function deleteUser(int $id): void;
}
