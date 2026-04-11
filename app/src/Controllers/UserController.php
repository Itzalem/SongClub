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
