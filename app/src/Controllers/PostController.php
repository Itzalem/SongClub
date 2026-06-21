<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\PostService;
use App\Services\CommentService;
use App\Repositories\PostRepository;
use App\Repositories\CommentRepository;

class PostController extends Controller
{
    private PostService    $postService;
    private CommentService $commentService;

    public function __construct()
    {
        $this->postService    = new PostService(new PostRepository());
        $this->commentService = new CommentService(new CommentRepository());
    }

    // POST /last-listened/set (web, session-based)
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

    // GET /api/feed?page=&limit=&user_id=
    public function showFeed(array $vars = []): void
    {
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(10);
        $userId = max(0, (int) ($_GET['user_id'] ?? 0));

        $posts = $this->postService->getFeed($offset, $limit, $userId);
        $total = $this->postService->countFeed($userId);

        $this->jsonPaged(array_map([$this, 'postToArray'], $posts), $page, $limit, $total);
    }

    // POST /api/posts 
    public function createPost(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = $this->getBody();
        $songId    = (int) ($body['song_id'] ?? 0);
        $caption   = trim($body['caption'] ?? '');

        if (!$songId) {
            $this->json(['error' => 'song_id is required.'], 400);
        }

        $post = $this->postService->createAndGet((int) $tokenData->id, $songId, $caption ?: null);

        if (!$post) {
            $this->json(['error' => 'Post could not be created.'], 500);
        }

        $this->json($this->postToArray($post), 201);
    }

    // GET /api/posts/{id}/comments
    public function showComments(array $vars = []): void
    {
        $postId = (int) ($vars['id'] ?? 0);

        if (!$postId) {
            $this->json(['error' => 'Invalid post ID.'], 400);
        }

        $this->json(array_map(fn($c) => [
            'id'         => $c->id,
            'post_id'    => $c->post_id,
            'user_id'    => $c->user_id,
            'username'   => $c->username ?? '',
            'content'    => $c->content,
            'created_at' => $c->created_at,
        ], $this->commentService->getByPost($postId)));
    }

    // POST /api/posts/{id}/comments
    public function createComment(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $postId    = (int) ($vars['id'] ?? 0);
        $body      = $this->getBody();
        $content   = trim($body['content'] ?? '');

        if (!$postId) {
            $this->json(['error' => 'Invalid post ID.'], 400);
        }

        if (!$content) {
            $this->json(['error' => 'Content is required.'], 400);
        }

        $id = $this->commentService->addComment($postId, (int) $tokenData->id, $content);

        $this->json([
            'id'         => $id,
            'post_id'    => $postId,
            'user_id'    => $tokenData->id,
            'username'   => $tokenData->username,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ], 201);
    }

    private function postToArray(\App\Models\Post $p): array
    {
        return [
            'id'            => $p->id,
            'user_id'       => $p->user_id,
            'username'      => $p->username      ?? null,
            'song_id'       => $p->song_id,
            'song_title'    => $p->song_title    ?? null,
            'song_artist'   => $p->song_artist   ?? null,
            'song_genre'    => $p->song_genre    ?? null,
            'song_link'     => $p->song_link     ?? null,
            'caption'       => $p->caption,
            'comment_count' => $p->comment_count ?? 0,
            'created_at'    => $p->created_at,
        ];
    }
}
