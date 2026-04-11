<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\PostService;
use App\Repositories\PostRepository;
use App\Repositories\CommentRepository;

class PostController extends Controller
{
    private $postService;
    private $commentRepository;

    public function __construct(PostService $postService) {
        $this->postService = $postService;
        $this->commentRepository = new CommentRepository();
    }


    public function set(array $vars = []): void
    {
        $this->requireAuth();

        $songId  = (int) ($_POST['song_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');

        if ($songId > 0) {
            $this->postService->createPost((int) $_SESSION['user_id'], $songId, $caption ?: null);
        }

        header('Location: /profile/' . (int) $_SESSION['user_id']);
        exit;
    }

    public function storeComment(array $vars = []): void
    {
        $this->requireAuth();

        $body    = json_decode(file_get_contents('php://input'), true);
        $postId  = (int) ($body['post_id'] ?? 0);
        $content = trim($body['content'] ?? '');

        if ($postId === 0 || $content === '') {
            $this->json(['error' => 'Invalid input'], 422);
        }

        $commentId = $this->commentRepository->createComment($postId, (int) $_SESSION['user_id'], $content);

        $this->json([
            'id'         => $commentId,
            'username'   => $_SESSION['username'],
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
