<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    // GET /admin/users — admin panel: list all users
    public function index(array $vars = []): void
    {
        $this->requireAdmin();

        $users = (new UserService(new UserRepository()))->getAllUsers();

        $this->render('Admin', ['users' => $users]);
    }

    // POST /admin/users/{id}/delete — admin deletes a user
    public function delete(array $vars = []): void
    {
        $this->requireAdmin();

        $targetId = (int) ($vars['id'] ?? 0);

        // Prevent admin from deleting themselves
        if ($targetId > 0 && $targetId !== (int) $_SESSION['user_id']) {
            (new UserService(new UserRepository()))->deleteUser($targetId);
        }

        header('Location: /admin/users');
        exit;
    }

    // POST /profile/update — update own profile
    public function update(array $vars = []): void
    {
        $this->requireAuth();

        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        if ($username === '') {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById((int) $_SESSION['user_id']);

        if ($user) {
            $user->username       = $username;
            $user->bio            = $bio ?: null;
            $userService->updateUser($user);
            $_SESSION['username'] = $username;
        }

        header('Location: /profile/' . (int) $_SESSION['user_id']);
        exit;
    }

    // GET /api/users/{id} — public profile info
    public function apiShow(array $vars = []): void
    {
        $user = (new UserService(new UserRepository()))->getUserById((int) $vars['id']);
        if (!$user) { $this->json(['error' => 'User not found'], 404); }
        $this->json([
            'id'       => $user->userId,
            'username' => $user->username,
            'email'    => $user->email,
            'bio'      => $user->bio,
            'role'     => $user->role,
        ]);
    }

    // GET /api/admin/users (JWT + admin required)
    public function apiAdminList(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        if ($tokenData->role !== 'admin') { $this->json(['error' => 'Forbidden'], 403); }

        $users  = (new UserService(new UserRepository()))->getAllUsers();
        $result = [];
        foreach ($users as $u) {
            $result[] = [
                'id'       => $u->userId,
                'username' => $u->username,
                'email'    => $u->email,
                'role'     => $u->role,
                'bio'      => $u->bio,
            ];
        }
        $this->json($result);
    }

    // DELETE /api/admin/users/{id} (JWT + admin required)
    public function apiAdminDelete(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        if ($tokenData->role !== 'admin') { $this->json(['error' => 'Forbidden'], 403); }

        $targetId = (int) ($vars['id'] ?? 0);
        if ($targetId === (int) $tokenData->id) {
            $this->json(['error' => 'Cannot delete yourself'], 400);
        }

        (new UserService(new UserRepository()))->deleteUser($targetId);
        $this->json(['deleted' => true]);
    }

    // PUT /api/users/{id} (JWT required, owner only)
    public function apiUpdateProfile(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $userId    = (int) ($vars['id'] ?? 0);

        if ((int) $tokenData->id !== $userId) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($body['username'] ?? '');
        $email    = trim($body['email']    ?? '');
        $bio      = trim($body['bio']      ?? '');

        if ($username === '') { $this->json(['error' => 'Username is required'], 400); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->json(['error' => 'Invalid email address'], 400); }

        $repo = new UserRepository();
        $user = $repo->getUserById($userId);
        if (!$user) { $this->json(['error' => 'User not found'], 404); }

        if ($username !== $user->username && $repo->getUserByUsername($username)) {
            $this->json(['error' => 'Username already taken'], 409);
        }
        if ($email !== $user->email && $repo->getUserByEmail($email)) {
            $this->json(['error' => 'Email already registered'], 409);
        }

        $user->username = $username;
        $user->email    = $email;
        $user->bio      = $bio ?: null;
        (new UserService(new UserRepository()))->updateUser($user);

        // Optional password change
        $currentPassword = $body['current_password'] ?? '';
        $newPassword     = $body['new_password']     ?? '';
        if ($newPassword !== '') {
            if (!password_verify($currentPassword, $user->passwordHash)) {
                $this->json(['error' => 'Current password is incorrect'], 400);
            }
            if (strlen($newPassword) < 6) {
                $this->json(['error' => 'New password must be at least 6 characters'], 400);
            }
            (new UserService(new UserRepository()))->changePassword($userId, $newPassword);
        }

        $this->json(['id' => $userId, 'username' => $username, 'email' => $email, 'bio' => $bio ?: null]);
    }

    // GET /api/users/search?q= — live user search API
    public function search(array $vars = []): void
    {
        $this->requireAuth();

        $query  = trim($_GET['q'] ?? '');
        $users  = (new UserService(new UserRepository()))->search($query);
        $result = [];

        foreach ($users as $u) {
            $result[] = [
                'id'       => $u->userId,
                'username' => htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'),
                'bio'      => htmlspecialchars($u->bio ?? '', ENT_QUOTES, 'UTF-8'),
            ];
        }

        $this->json($result);
    }
}