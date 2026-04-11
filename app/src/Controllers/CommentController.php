<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Repositories\CommentRepository;

class CommentController extends Controller
{
    // POST /comments/store — AJAX: add a comment to a last-listened post
    public function store(array $vars = []): void
    {
        $this->requireAuth();

        $body    = json_decode(file_get_contents('php://input'), true);
        $postId  = (int) ($body['post_id'] ?? 0);
        $content = trim($body['content'] ?? '');

        if ($postId === 0 || $content === '') {
            $this->json(['error' => 'Invalid input'], 422);
        }

        $repo      = new CommentRepository();
        $commentId = $repo->createComment($postId, (int) $_SESSION['user_id'], $content);

        $this->json([
            'id'         => $commentId,
            'username'   => htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'),
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
