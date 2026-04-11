<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\PostService;
use App\Repositories\PostRepository;

class PostController extends Controller
{
    public function set(array $vars = []): void
    {
        $this->requireAuth();

        $songId  = (int) ($_POST['song_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');

        if ($songId > 0) {
            $service = new PostService(new PostRepository());
            $service->createPost((int) $_SESSION['user_id'], $songId, $caption ?: null);
        }

        header('Location: /profile/' . (int) $_SESSION['user_id']);
        exit;
    }
}
