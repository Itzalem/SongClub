<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface IUserService
{
    public function getUserById(int $id): ?User;
    public function getAllUsers(): array;
    public function search(string $query): array;
    public function login(string $email, string $password): ?User;
    public function register(string $username, string $email, string $password, string $bio = ''): int;
    public function updateUser(User $user): int;
    public function deleteUser(int $id): void;
}
