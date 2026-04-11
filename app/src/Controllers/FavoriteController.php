<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\FavoriteService;
use App\Repositories\InteractionRepository;
use App\Models\ESongType;

class FavoriteController extends Controller
{
    public function toggle(array $vars = []): void
    {
        $this->requireAuth();

        $body   = json_decode(file_get_contents('php://input'), true);
        $songId = (int) ($body['song_id'] ?? 0);

        if (!$songId) {
            $this->json(['error' => 'Invalid song'], 400);
        }

        $service = new FavoriteService(new InteractionRepository());
        $added   = $service->toggleFavorite((int) $_SESSION['user_id'], $songId);

        $this->json(['favorited' => $added]);
    }

    public function toggleLike(array $vars = []): void
    {
        $this->requireAuth();

        $body   = json_decode(file_get_contents('php://input'), true);
        $songId = (int) ($body['song_id'] ?? 0);

        if (!$songId) {
            $this->json(['error' => 'Invalid song'], 400);
        }

        $service = new FavoriteService(new InteractionRepository());
        $added   = $service->toggleLike((int) $_SESSION['user_id'], $songId);
        $count   = $service->getLikeCount($songId);

        $this->json(['liked' => $added, 'count' => $count]);
    }

    // API — export favorites as JSON download
    public function export(array $vars = []): void
    {
        $this->requireAuth();

        $userId = (int) ($vars['userId'] ?? 0);

        if ((int) $_SESSION['user_id'] !== $userId && $_SESSION['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $favorites = (new InteractionRepository())->getSongsByUser($userId, ESongType::FAVORITE);

        $result = [];
        foreach ($favorites as $s) {
            $result[] = [
                'id'     => $s->id,
                'title'  => $s->title,
                'artist' => $s->artist,
                'album'  => $s->album,
                'genre'  => $s->genre,
                'link'   => $s->link,
            ];
        }

        header('Content-Disposition: attachment; filename="favorites.json"');
        $this->json($result);
    }
}
