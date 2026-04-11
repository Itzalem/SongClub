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
    // GET /profile/{id}
    public function show(array $vars = []): void
    {
        $profileUserId = (int) ($vars['id'] ?? 0);

        $userService = new UserService(new UserRepository());
        $user        = $userService->getUserById($profileUserId);

        if (!$user) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

        $vm           = new ProfileVm();
        $vm->user     = $user;
        $vm->isOwner  = ($currentUserId > 0 && $currentUserId === $profileUserId);

        // Last listened post (with joined song data)
        $postService  = new PostService(new PostRepository());
        $vm->lastPost = $postService->getLastByUser($profileUserId);

        // Comments on that post
        if ($vm->lastPost !== null) {
            $commentRepo  = new CommentRepository();
            $vm->comments = $commentRepo->getCommentsByPost($vm->lastPost->id);
        }

        // Favorites (visible to everyone)
        $favService   = new FavoriteService(new InteractionRepository());
        $vm->favorites = $favService->getFavoritesByUser($profileUserId);

        // Liked songs & songs dropdown (owner only)
        if ($vm->isOwner) {
            $vm->likes = $favService->getLikesByUser($profileUserId);
            $vm->songs = (new SongService(new SongRepository()))->getAll();
        }

        $this->render('UserProfile', ['vm' => $vm]);
    }

    // POST /profile/update — delegates to UserController::update
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
