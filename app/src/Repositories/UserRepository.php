<?php

namespace app\src\Repositories;

use PDO;
use app\Framework\Repository;
use app\src\Repositories\Interfaces\IUserRepository;
use app\Models\User;    

class UserRepository extends Repository implements IUserRepository
{
    /*

    public function getUserByUsername(string $username): ?array
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ?: null;
    }*/

    public function buildUserModel(array $userData): User
    {
        $user = new User();
        $user->userId = $userData['id'];
        $user->username = $userData['username'];
        $user->email = $userData['email'];
        $user->passwordHash = $userData['password'];
        $user->bio = $userData['bio'];
        $user->role = $userData['role'];
        return $user;
    }

    public function getUserById(int $id): ?User
    {
        $sql = "SELECT * FROM USERS WHERE id = :id";

        $connection = $this->getConnection();
        $statement = $connection->prepare($sql);

        $statement->bindParam(':id', $id, PDO::PARAM_INT);//por que ::

        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
         
        return $this->buildUserModel($userData) ?: null; //why 
    }

    public function getUserByEmail(string $email): ?User
    {
        $sql        = "SELECT * FROM users WHERE email = :email";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        return $this->buildUserModel($userData) ?: null;
    }

    public function getUserByUsername(string $username): ?User
    {
        $sql        = "SELECT * FROM users WHERE username = :username";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        return $this->buildUserModel($userData) ?: null;
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
        // 1. Definimos el SQL con marcadores para evitar Inyección SQL [cite: 1233]
        $sql = "INSERT INTO USERS (username, email, password, bio, role) 
                VALUES (:username, :email, :password, :bio, :role)";

        // 2. Preparamos la sentencia usando el método de la clase base [cite: 1231, 1811]
        $connection = $this->getConnection();
        $statement = $connection->prepare($sql);

        $hashedPassword = password_hash($user->passwordHash, PASSWORD_BCRYPT);

        // 3. Vinculamos los datos del objeto UserModel a los marcadores [cite: 1250-1252]
        $statement->bindParam(':username', $user->username);
        $statement->bindParam(':email', $user->email);
        $statement->bindParam(':password', $hashedPassword); // Este ya debe venir hasheado del Service
        $statement->bindParam(':bio', $user->bio);
        $statement->bindParam(':role', $user->role);

        // 4. Ejecutamos la orden en la base de datos [cite: 1254]
        $statement->execute();

        return (int)$connection->lastInsertId();
    }

    public function updateUser(User $user): int
    {
        // 1. Definimos el SQL con marcadores para evitar Inyección SQL [cite: 1233]
        $sql = "UPDATE USERS SET username = :username, email = :email, password = :password, bio = :bio, role = :role WHERE id = :id";


        // 2. Preparamos la sentencia usando el método de la clase base [cite: 1231, 1811]
        $connection = $this->getConnection();
        $statement = $connection->prepare($sql);

        // 3. Vinculamos los datos del objeto UserModel a los marcadores [cite: 1250-1252]
        $statement->bindParam(':username', $user->username);
        $statement->bindParam(':email', $user->email);
        $statement->bindParam(':password', $hashedPassword); // Este ya debe venir hasheado del Service
        $statement->bindParam(':bio', $user->bio);
        $statement->bindParam(':role', $user->role);

        // 4. Ejecutamos la orden en la base de datos [cite: 1254]
        $statement->execute();

        return (int)$connection->lastInsertId();
    }

    public function deleteUser(int $id): void
    {
        $sql = "DELETE FROM USERS WHERE id = :id";

        $connection = $this->getConnection();
        $statement = $connection->prepare($sql);

        $statement->bindParam(':id', $id, PDO::PARAM_INT);

        $statement->execute();
    }

    
}