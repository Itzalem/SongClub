<?php

namespace App\Repositories;

use PDO;
use App\Framework\Repository;
use App\Repositories\Interfaces\IUserRepository;
use App\Models\User;

class UserRepository extends Repository implements IUserRepository
{
    public function buildUserModel(array $userData): User
    {
        $user               = new User();
        $user->userId       = $userData['id'];
        $user->username     = $userData['username'];
        $user->email        = $userData['email'];
        $user->passwordHash = $userData['password'];
        $user->bio          = $userData['bio'];
        $user->role         = $userData['role'];
        return $user;
    }

    public function getUserById(int $id): ?User
    {
        $sql        = "SELECT * FROM users WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$userData) {
            return null;
        }
        return $this->buildUserModel($userData);
    }

    public function getUserByEmail(string $email): ?User
    {
        $sql        = "SELECT * FROM users WHERE email = :email";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$userData) {
            return null;
        }
        return $this->buildUserModel($userData);
    }

    public function getUserByUsername(string $username): ?User
    {
        $sql        = "SELECT * FROM users WHERE username = :username";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$userData) {
            return null;
        }
        return $this->buildUserModel($userData);
    }

    public function getAllUsers(): array
    {
        $sql        = "SELECT * FROM users ORDER BY created_at DESC";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->execute();
        $rows  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->buildUserModel($row);
        }
        return $users;
    }

    public function searchUser(string $query): array
    {
        $sql        = "SELECT * FROM users WHERE username LIKE :q1 OR email LIKE :q2";
        $like       = '%' . $query . '%';
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':q1', $like, PDO::PARAM_STR);
        $statement->bindParam(':q2', $like, PDO::PARAM_STR);
        $statement->execute();
        $rows  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->buildUserModel($row);
        }
        return $users;
    }

    public function createUser(User $user): int
    {
        $sql = "INSERT INTO users (username, email, password, bio, role)
                VALUES (:username, :email, :password, :bio, :role)";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->bindParam(':username', $user->username);
        $statement->bindParam(':email',    $user->email);
        $statement->bindParam(':password', $user->passwordHash);
        $statement->bindParam(':bio',      $user->bio);
        $statement->bindParam(':role',     $user->role);

        $statement->execute();

        return (int) $connection->lastInsertId();
    }

    public function updateUser(User $user): int
    {
        $sql = "UPDATE users
                SET username = :username, bio = :bio, email = :email
                WHERE id = :id";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->bindParam(':username', $user->username);
        $statement->bindParam(':bio',      $user->bio);
        $statement->bindParam(':email',    $user->email);
        $statement->bindParam(':id',       $user->userId, PDO::PARAM_INT);

        $statement->execute();

        return $user->userId;
    }

    public function updatePasswordHash(int $userId, string $hash): void
    {
        $sql        = "UPDATE users SET password = :password WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':password', $hash);
        $statement->bindParam(':id',       $userId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function deleteUser(int $id): void
    {
        $sql        = "DELETE FROM users WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}
