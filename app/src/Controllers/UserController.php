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