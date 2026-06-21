<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService(new UserRepository());
    }

    // GET /admin/users
    public function index(array $vars = []): void
    {
        $this->requireAdmin();
        $this->render('Admin', ['users' => $this->userService->getAllUsers()]);
    }

    // POST /admin/users/{id}/delete
    public function delete(array $vars = []): void
    {
        $this->requireAdmin();

        $targetId = (int) ($vars['id'] ?? 0);

        if ($targetId > 0 && $targetId !== (int) $_SESSION['user_id']) {
            $this->userService->deleteUser($targetId);
        }

        header('Location: /admin/users');
        exit;
    }

    // POST /profile/update
    public function update(array $vars = []): void
    {
        $this->requireAuth();

        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        if ($username === '') {
            header('Location: /profile/' . (int) $_SESSION['user_id']);
            exit;
        }

        $user = $this->userService->getUserById((int) $_SESSION['user_id']);

        if ($user) {
            $user->username       = $username;
            $user->bio            = $bio ?: null;
            $this->userService->updateUser($user);
            $_SESSION['username'] = $username;
        }

        header('Location: /profile/' . (int) $_SESSION['user_id']);
        exit;
    }

    // GET /api/users/{id}
    public function apiShow(array $vars = []): void
    {
        $user = $this->userService->getUserById((int) $vars['id']);
        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
        }
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
        if ($tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $users = $this->userService->getAllUsers();
        $this->json(array_map(fn($u) => [
            'id'       => $u->userId,
            'username' => $u->username,
            'email'    => $u->email,
            'role'     => $u->role,
            'bio'      => $u->bio,
        ], $users));
    }

    // DELETE /api/admin/users/{id} (JWT + admin required)
    public function apiAdminDelete(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        if ($tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $targetId = (int) ($vars['id'] ?? 0);
        if ($targetId === (int) $tokenData->id) {
            $this->json(['error' => 'Cannot delete yourself.'], 400);
        }

        $this->userService->deleteUser($targetId);
        $this->json(['deleted' => true]);
    }

    // GET /api/users/search?q=
    public function search(array $vars = []): void
    {
        $this->requireAuth();

        $query = trim($_GET['q'] ?? '');
        $users = $this->userService->search($query);

        $this->json(array_map(fn($u) => [
            'id'       => $u->userId,
            'username' => htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'),
            'bio'      => htmlspecialchars($u->bio ?? '', ENT_QUOTES, 'UTF-8'),
        ], $users));
    }
}