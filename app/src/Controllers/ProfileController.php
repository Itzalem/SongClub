<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Services\FavoriteService;
use App\Services\PostService;
use App\Services\SongService;
use App\Repositories\UserRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\PostRepository;
use App\Repositories\SongRepository;
use App\Repositories\CommentRepository;
use App\ViewModels\ProfileVm;

class ProfileController extends Controller
{
    // GET /profile/{id} — home / feed view
    public function show(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById($profileUserId);

        if (!$user) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

        $vm          = new ProfileVm();
        $vm->user    = $user;
        $vm->isOwner = ($currentUserId > 0 && $currentUserId === $profileUserId);

        // Last listened post (with joined song data)
        $postService  = new PostService(new PostRepository());
        $vm->lastPost = $postService->getLastByUser($profileUserId);

        // Comments on that post
        if ($vm->lastPost !== null) {
            $commentRepo  = new CommentRepository();
            $vm->comments = $commentRepo->getCommentsByPost($vm->lastPost->id);
        }

        // Favorites (visible to everyone)
        $favService    = new FavoriteService(new InteractionRepository());
        $vm->favorites = $favService->getFavoritesByUser($profileUserId);

        // Liked songs & songs dropdown (owner only)
        if ($vm->isOwner) {
            $vm->likes = $favService->getLikesByUser($profileUserId);
            $vm->songs = (new SongService(new SongRepository()))->getAll();
        }

        $this->render('UserProfile', ['vm' => $vm]);
    }

    // GET /user/{id} — read-only profile page (accessible via nav username link)
    public function userView(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById($profileUserId);

        if (!$user) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $isOwner = ((int) ($_SESSION['user_id'] ?? 0) === $profileUserId);

        $this->render('User/View', [
            'user'     => $user,
            'isOwner'  => $isOwner,
            'editMode' => false,
            'error'    => null,
            'success'  => null,
        ]);
    }

    // GET /profile/edit — edit profile form
    public function editForm(array $vars = []): void
    {
        $this->requireAuth();

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById((int) $_SESSION['user_id']);

        if (!$user) {
            header('Location: /');
            exit;
        }

        $this->render('User/View', [
            'user'     => $user,
            'isOwner'  => true,
            'editMode' => true,
            'error'    => $_SESSION['edit_error']   ?? null,
            'success'  => $_SESSION['edit_success'] ?? null,
        ]);

        unset($_SESSION['edit_error'], $_SESSION['edit_success']);
    }

    // POST /profile/edit — save profile changes
    public function editSave(array $vars = []): void
    {
        $this->requireAuth();

        $userId      = (int) $_SESSION['user_id'];
        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById($userId);

        if (!$user) {
            header('Location: /');
            exit;
        }

        $username        = trim($_POST['username']         ?? '');
        $email           = trim($_POST['email']            ?? '');
        $bio             = trim($_POST['bio']              ?? '');
        $currentPassword = $_POST['current_password']     ?? '';
        $newPassword     = $_POST['new_password']         ?? '';
        $confirmPassword = $_POST['confirm_password']     ?? '';

        // Validate basics
        if ($username === '') {
            $_SESSION['edit_error'] = 'Username cannot be empty.';
            header('Location: /profile/edit');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['edit_error'] = 'Please enter a valid email address.';
            header('Location: /profile/edit');
            exit;
        }

        // Check username uniqueness (if changed)
        if ($username !== $user->username) {
            $existing = (new UserRepository())->getUserByUsername($username);
            if ($existing !== null) {
                $_SESSION['edit_error'] = 'That username is already taken.';
                header('Location: /profile/edit');
                exit;
            }
        }

        // Check email uniqueness (if changed)
        if ($email !== $user->email) {
            $existing = (new UserRepository())->getUserByEmail($email);
            if ($existing !== null) {
                $_SESSION['edit_error'] = 'That email is already registered.';
                header('Location: /profile/edit');
                exit;
            }
        }

        // Update username, email, bio
        $user->username = $username;
        $user->email    = $email;
        $user->bio      = $bio ?: null;
        $userService->updateUser($user);
        $_SESSION['username'] = $username;

        // Optionally change password
        if ($newPassword !== '') {
            if (!password_verify($currentPassword, $user->passwordHash)) {
                $_SESSION['edit_error'] = 'Current password is incorrect.';
                header('Location: /profile/edit');
                exit;
            }
            if (strlen($newPassword) < 6) {
                $_SESSION['edit_error'] = 'New password must be at least 6 characters.';
                header('Location: /profile/edit');
                exit;
            }
            if ($newPassword !== $confirmPassword) {
                $_SESSION['edit_error'] = 'New passwords do not match.';
                header('Location: /profile/edit');
                exit;
            }
            $userService->changePassword($userId, $newPassword);
        }

        header('Location: /user/' . $userId);
        exit;
    }

    // POST /profile/update — legacy endpoint kept for compatibility
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
}
