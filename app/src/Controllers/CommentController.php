<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\CommentService;
use App\Repositories\CommentRepository;

class CommentController extends Controller
{
    private CommentService $commentService;

    public function __construct()
    {
        $this->commentService = new CommentService(new CommentRepository());
    }

    // POST /comments/store 
    public function store(array $vars = []): void
    {
        $this->requireAuth();

        $body    = $this->getBody();
        $postId  = (int) ($body['post_id'] ?? 0);
        $content = trim($body['content'] ?? '');

        if (!$postId || $content === '') {
            $this->json(['error' => 'Invalid input.'], 422);
        }

        $commentId = $this->commentService->addComment($postId, (int) $_SESSION['user_id'], $content);

        $this->json([
            'id'         => $commentId,
            'username'   => htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'),
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}