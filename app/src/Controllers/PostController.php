<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\PostService;
use App\Repositories\PostRepository;
use App\Repositories\CommentRepository;
use App\Models\Post;

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

    // GET /api/feed?page=&limit=
    public function apiFeed(array $vars = []): void
    {
        $limit  = max(1, min(50, (int) ($_GET['limit'] ?? 10)));
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $offset = ($page - 1) * $limit;

        $repo  = new PostRepository();
        $posts = $repo->getFeed($offset, $limit);
        $total = $repo->countFeed();
        $pages = (int) ceil($total / $limit) ?: 1;

        $data = [];
        foreach ($posts as $p) {
            $data[] = [
                'id'            => $p->id,
                'user_id'       => $p->user_id,
                'username'      => $p->username,
                'song_id'       => $p->song_id,
                'song_title'    => $p->song_title,
                'song_artist'   => $p->song_artist,
                'song_genre'    => $p->song_genre,
                'song_link'     => $p->song_link,
                'caption'       => $p->caption,
                'comment_count' => $p->comment_count,
                'created_at'    => $p->created_at,
            ];
        }

        $this->json([
            'data' => $data,
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $pages],
        ]);
    }

    // POST /api/posts (JWT required)
    public function apiSet(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $songId    = (int) ($body['song_id'] ?? 0);
        $caption   = trim($body['caption'] ?? '');

        if (!$songId) { $this->json(['error' => 'song_id required'], 400); }

        $service = new PostService(new PostRepository());
        $id      = $service->createPost((int) $tokenData->id, $songId, $caption ?: null);

        $this->json(['id' => $id, 'created' => true], 201);
    }

    // GET /api/posts/{id}/comments
    public function apiGetComments(array $vars = []): void
    {
        $postId   = (int) ($vars['id'] ?? 0);
        $comments = (new CommentRepository())->getCommentsByPost($postId);
        $data     = [];
        foreach ($comments as $c) {
            $data[] = [
                'id'         => $c->id,
                'post_id'    => $c->post_id,
                'user_id'    => $c->user_id,
                'username'   => $c->username ?? '',
                'content'    => $c->content,
                'created_at' => $c->created_at,
            ];
        }
        $this->json($data);
    }

    // POST /api/posts/{id}/comments (JWT required)
    public function apiAddComment(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $postId    = (int) ($vars['id'] ?? 0);
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $content   = trim($body['content'] ?? '');

        if (!$content) { $this->json(['error' => 'content required'], 400); }

        $repo = new CommentRepository();
        $id   = $repo->createComment($postId, (int) $tokenData->id, $content);

        $this->json([
            'id'         => $id,
            'post_id'    => $postId,
            'user_id'    => $tokenData->id,
            'username'   => $tokenData->username,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ], 201);
    }
}