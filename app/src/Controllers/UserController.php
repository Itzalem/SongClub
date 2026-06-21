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
    public function showUser(array $vars = []): void
    {
        $user = $this->userService->getUserById((int) $vars['id']);

        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
        }

        $this->json([
            'id'       => $user->userId,
            'username' => $user->username,
            'email'    => $user->email,
            'bio'      => $user->bio,
            'role'     => $user->role,
        ]);
    }

    // PUT /api/users/{id} (JWT required, owner only)
    public function updateProfile(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $userId    = (int) ($vars['id'] ?? 0);

        if ((int) $tokenData->id !== $userId) {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $body     = $this->getBody();
        $username = trim($body['username'] ?? '');
        $email    = trim($body['email']    ?? '');
        $bio      = trim($body['bio']      ?? '');

        if ($username === '') { $this->json(['error' => 'Username is required.'], 400); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->json(['error' => 'Invalid email address.'], 400); }

        $repo = new UserRepository();
        $user = $repo->getUserById($userId);
        if (!$user) { $this->json(['error' => 'User not found.'], 404); }

        if ($username !== $user->username && $repo->getUserByUsername($username)) {
            $this->json(['error' => 'Username already taken.'], 409);
        }
        if ($email !== $user->email && $repo->getUserByEmail($email)) {
            $this->json(['error' => 'Email already registered.'], 409);
        }

        $user->username = $username;
        $user->email    = $email;
        $user->bio      = $bio ?: null;
        $this->userService->updateUser($user);

        $currentPassword = $body['current_password'] ?? '';
        $newPassword     = $body['new_password']     ?? '';
        if ($newPassword !== '') {
            if (!password_verify($currentPassword, $user->passwordHash)) {
                $this->json(['error' => 'Current password is incorrect.'], 400);
            }
            if (strlen($newPassword) < 6) {
                $this->json(['error' => 'New password must be at least 6 characters.'], 400);
            }
            $this->userService->changePassword($userId, $newPassword);
        }

        $this->json(['id' => $userId, 'username' => $username, 'email' => $email, 'bio' => $bio ?: null]);
    }

    // GET /api/users/search....(search users by username)
    public function searchUsers(array $vars = []): void
    {
        $this->requireAuth();

        $users = $this->userService->search(trim($_GET['q'] ?? ''));

        $this->json(array_map(fn($u) => [
            'id'       => $u->userId,
            'username' => htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'),
            'bio'      => htmlspecialchars($u->bio ?? '', ENT_QUOTES, 'UTF-8'),
        ], $users));
    }

    // GET /api/admin/users 
    public function listUsers(array $vars = []): void
    {
        $this->requireJwtAdmin();

        $this->json(array_map(fn($u) => [
            'id'       => $u->userId,
            'username' => $u->username,
            'email'    => $u->email,
            'role'     => $u->role,
            'bio'      => $u->bio,
        ], $this->userService->getAllUsers()));
    }

    // DELETE /api/admin/users/{id} 
    public function removeUser(array $vars = []): void
    {
        $tokenData = $this->requireJwtAdmin();

        $targetId = (int) ($vars['id'] ?? 0);

        if ($targetId === (int) $tokenData->id) {
            $this->json(['error' => 'Cannot delete yourself.'], 400);
        }

        $this->userService->deleteUser($targetId);
        $this->json(['deleted' => true]);
    }
}
