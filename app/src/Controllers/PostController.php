<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Models\Post;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Services\PostService;

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

    // ── REST API ────────────────────────────────────────────────────────────

    // GET /api/feed?page=&limit=
    public function apiFeed(array $vars = []): void
    {
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $repo  = new PostRepository();
        $posts = $repo->getFeed($offset, $limit);
        $total = $repo->countFeed();

        $this->json([
            'data'  => array_map([$this, 'postToArray'], $posts),
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    // POST /api/posts  — JWT required
    public function apiSet(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $songId    = (int) ($body['song_id'] ?? 0);
        $caption   = trim($body['caption'] ?? '');

        if (!$songId) $this->json(['error' => 'song_id is required.'], 400);

        $service = new PostService(new PostRepository());
        $service->createPost($tokenData->id, $songId, $caption ?: null);

        $post = (new PostRepository())->getLastByUserId($tokenData->id);
        $this->json($this->postToArray($post), 201);
    }

    // GET /api/posts/{id}/comments
    public function apiGetComments(array $vars = []): void
    {
        $postId   = (int) ($vars['id'] ?? 0);
        $repo     = new CommentRepository();
        $comments = $repo->getCommentsByPost($postId);

        $this->json(array_map(fn($c) => [
            'id'         => $c->id,
            'username'   => $c->username,
            'content'    => $c->content,
            'created_at' => $c->created_at,
        ], $comments));
    }

    // POST /api/posts/{id}/comments  — JWT required
    public function apiAddComment(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $postId    = (int) ($vars['id'] ?? 0);
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $content   = trim($body['content'] ?? '');

        if (!$postId || $content === '') {
            $this->json(['error' => 'post_id and content are required.'], 400);
        }

        $repo      = new CommentRepository();
        $commentId = $repo->createComment($postId, $tokenData->id, $content);

        $this->json([
            'id'         => $commentId,
            'post_id'    => $postId,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ], 201);
    }

    private function postToArray(Post $post): array
    {
        return [
            'id'            => $post->id,
            'user_id'       => $post->user_id,
            'username'      => $post->username      ?? null,
            'song_id'       => $post->song_id,
            'song_title'    => $post->song_title    ?? null,
            'song_artist'   => $post->song_artist   ?? null,
            'song_album'    => $post->song_album     ?? null,
            'song_genre'    => $post->song_genre    ?? null,
            'song_link'     => $post->song_link     ?? null,
            'caption'       => $post->caption,
            'comment_count' => $post->comment_count ?? 0,
            'created_at'    => $post->created_at,
        ];
    }
}