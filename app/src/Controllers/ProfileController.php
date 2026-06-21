<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\UserService;
use App\Services\FavoriteService;
use App\Services\PostService;
use App\Services\SongService;
use App\Services\CommentService;
use App\Repositories\UserRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\PostRepository;
use App\Repositories\SongRepository;
use App\Repositories\CommentRepository;
use App\ViewModels\ProfileVm;

class ProfileController extends Controller
{
    private UserService     $userService;
    private PostService     $postService;
    private CommentService  $commentService;
    private FavoriteService $favoriteService;
    private SongService     $songService;

    public function __construct()
    {
        $this->userService     = new UserService(new UserRepository());
        $this->postService     = new PostService(new PostRepository());
        $this->commentService  = new CommentService(new CommentRepository());
        $this->favoriteService = new FavoriteService(new InteractionRepository());
        $this->songService     = new SongService(new SongRepository());
    }

    // GET /profile/{id}
    public function show(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);
        $user          = $this->userService->getUserById($profileUserId);

        if (!$user) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

        $vm          = new ProfileVm();
        $vm->user    = $user;
        $vm->isOwner = ($currentUserId > 0 && $currentUserId === $profileUserId);
        $vm->posts   = $this->loadPostsWithComments($profileUserId);
        $vm->favorites = $this->favoriteService->getFavoritesByUser($profileUserId);

        if ($vm->isOwner) {
            $vm->likes = $this->favoriteService->getLikesByUser($profileUserId);
            $vm->songs = $this->songService->getAll();
        }

        $this->render('UserProfile', ['vm' => $vm]);
    }

    // GET /user/{id}
    public function userView(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);
        $user          = $this->userService->getUserById($profileUserId);

        if (!$user) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $this->render('User/View', [
            'user'     => $user,
            'isOwner'  => ((int) ($_SESSION['user_id'] ?? 0) === $profileUserId),
            'editMode' => false,
            'error'    => null,
            'success'  => null,
        ]);
    }

    // GET /profile/edit
    public function editForm(array $vars = []): void
    {
        $this->requireAuth();

        $user = $this->userService->getUserById((int) $_SESSION['user_id']);

        if (!$user) {
            header('Location: /');
            exit;
        }

        $error   = $_SESSION['edit_error']   ?? null;
        $success = $_SESSION['edit_success'] ?? null;
        unset($_SESSION['edit_error'], $_SESSION['edit_success']);

        $this->render('User/View', [
            'user'     => $user,
            'isOwner'  => true,
            'editMode' => true,
            'error'    => $error,
            'success'  => $success,
        ]);
    }

    // POST /profile/edit
    public function editSave(array $vars = []): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $user   = $this->userService->getUserById($userId);

        if (!$user) {
            header('Location: /');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        try {
            $this->userService->updateProfile($user, $username, $email, $bio);
            $_SESSION['username'] = $username;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['edit_error'] = $e->getMessage();
            header('Location: /profile/edit');
            exit;
        }

        $newPassword = $_POST['new_password'] ?? '';

        if ($newPassword !== '') {
            $error = $this->validatePasswordChange(
                $user->passwordHash,
                $_POST['current_password'] ?? '',
                $newPassword,
                $_POST['confirm_password'] ?? ''
            );

            if ($error) {
                $_SESSION['edit_error'] = $error;
                header('Location: /profile/edit');
                exit;
            }

            $this->userService->changePassword($userId, $newPassword);
        }

        header('Location: /user/' . $userId);
        exit;
    }

    // POST /profile/update 
    public function update(array $vars = []): void
    {
        $this->requireAuth();

        $username = trim($_POST['username'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');
        $userId   = (int) $_SESSION['user_id'];
        $user     = $this->userService->getUserById($userId);

        if ($user && $username !== '') {
            try {
                $this->userService->updateProfile($user, $username, $user->email, $bio);
                $_SESSION['username'] = $username;
            } catch (\InvalidArgumentException) {
            }
        }

        header('Location: /profile/' . $userId);
        exit;
    }

    private function loadPostsWithComments(int $userId): array
    {
        $posts = $this->postService->getAllByUser($userId);
        foreach ($posts as $post) {
            $post->comments = $this->commentService->getByPost($post->id);
        }
        return $posts;
    }

    private function validatePasswordChange(string $hash, string $current, string $new, string $confirm): ?string
    {
        if (!password_verify($current, $hash)) {
            return 'Current password is incorrect.';
        }
        if (strlen($new) < 6) {
            return 'New password must be at least 6 characters.';
        }
        if ($new !== $confirm) {
            return 'New passwords do not match.';
        }
        return null;
    }
}
