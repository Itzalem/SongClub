<?php

namespace app\Services;

use app\Models\User;

class UserService {
    private $userRepository;

    public function __construct($userRepository) {
        $this->userRepository = $userRepository;
    }

    public function getUserById(int $id): ?User {
        return $this->userRepository->getUserById($id);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function search(string $query): array
    {
        return $this->userRepository->search(trim($query));
    }

     public function login(string $email, string $password): ?User
    {
        $user = $this->userRepo->findByEmail(trim($email));
        if ($user === null) {
            return null;
        }
        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }
        return $user;
    }

    public function register(string $username, string $email, string $password, string $bio = ''): int
    {
        // Server-side validation
        if (empty(trim($username)) || empty(trim($email)) || empty($password)) {
            throw new \InvalidArgumentException('All fields are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Please enter a valid email address.');
        }
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Password must be at least 6 characters.');
        }
        if ($this->userRepository->findByEmail(trim($email)) !== null) {
            throw new \InvalidArgumentException('This email address is already registered.');
        }
        if ($this->userRepository->findByUsername(trim($username)) !== null) {
            throw new \InvalidArgumentException('This username is already taken.');
        }

        $user               = new User();
        $user->username     = trim($username);
        $user->email        = trim($email);
        $user->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $user->bio          = trim($bio);
        $user->role         = 'User';

        return $this->userRepository->createUser($user);
    }

    public function updateUser(User $user): int
    {
        if (!empty($newPassword)) {
            $user->passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        }
        
        return $this->userRepository->updateUser($user);
    }

    public function deleteUser(int $id): void
    {
        $this->userRepository->deleteUser($id);
    }


   
}