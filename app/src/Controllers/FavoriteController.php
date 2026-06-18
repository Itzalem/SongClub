<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\FavoriteService;
use App\Services\UserService;
use App\Repositories\InteractionRepository;
use App\Repositories\UserRepository;
use App\Models\ESongType;
use App\Models\Song;

class FavoriteController extends Controller
{
    public function toggle(array $vars = []): void
    {
        $this->requireAuth();
        $body   = json_decode(file_get_contents('php://input'), true);
        $songId = (int) ($body['song_id'] ?? 0);
        if (!$songId) $this->json(['error' => 'Invalid song'], 400);
        $service = new FavoriteService(new InteractionRepository());
        $added   = $service->toggleFavorite((int) $_SESSION['user_id'], $songId);
        $this->json(['favorited' => $added]);
    }

    public function toggleLike(array $vars = []): void
    {
        $this->requireAuth();
        $body   = json_decode(file_get_contents('php://input'), true);
        $songId = (int) ($body['song_id'] ?? 0);
        if (!$songId) $this->json(['error' => 'Invalid song'], 400);
        $service = new FavoriteService(new InteractionRepository());
        $added   = $service->toggleLike((int) $_SESSION['user_id'], $songId);
        $count   = $service->getLikeCount($songId);
        $this->json(['liked' => $added, 'count' => $count]);
    }

    public function userFavorites(array $vars = []): void
    {
        $this->requireAuth();
        $profileUserId = (int) ($vars['id'] ?? 0);
        $userService   = new UserService(new UserRepository());
        $profileUser   = $userService->getUserById($profileUserId);
        if (!$profileUser) { http_response_code(404); echo 'Not found'; return; }
        $currentUserId  = (int) ($_SESSION['user_id'] ?? 0);
        $isOwner        = ($currentUserId === $profileUserId);
        $favService     = new FavoriteService(new InteractionRepository());
        $songs          = $favService->getFavoritesByUser($profileUserId);
        $viewerLikedIds = [];
        $viewerFavIds   = [];
        if (!$isOwner) {
            $viewerLikedIds = $favService->getLikeIds($currentUserId);
            $viewerFavIds   = $favService->getFavoriteIds($currentUserId);
        }
        $this->render('Songs/FavoriteSongs', [
            'profileUser'    => $profileUser,
            'songs'          => $songs,
            'isOwner'        => $isOwner,
            'viewerLikedIds' => $viewerLikedIds,
            'viewerFavIds'   => $viewerFavIds,
        ]);
    }

    public function userLiked(array $vars = []): void
    {
        $this->requireAuth();
        $profileUserId = (int) ($vars['id'] ?? 0);
        if ((int) $_SESSION['user_id'] !== $profileUserId) {
            header('Location: /profile/' . $profileUserId); exit;
        }
        $favService = new FavoriteService(new InteractionRepository());
        $songs      = $favService->getLikesByUser($profileUserId);
        $this->render('Songs/LikedSongs', ['songs' => $songs]);
    }

    public function export(array $vars = []): void
    {
        $this->requireAuth();
        $userId = (int) ($vars['userId'] ?? 0);
        if ((int) $_SESSION['user_id'] !== $userId && $_SESSION['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
        }
        $favorites = (new InteractionRepository())->getSongsByUser($userId, ESongType::FAVORITE);
        $result    = [];
        foreach ($favorites as $s) {
            $result[] = ['id' => $s->id, 'title' => $s->title, 'artist' => $s->artist,
                         'album' => $s->album, 'genre' => $s->genre, 'link' => $s->link];
        }
        header('Content-Disposition: attachment; filename="favorites.json"');
        $this->json($result);
    }

    // ── REST API ────────────────────────────────────────────────────────────

    // GET /api/users/{id}/favorites?artist=&page=&limit=
    public function apiFavorites(array $vars = []): void
    {
        $userId = (int) ($vars['id'] ?? 0);
        $artist = trim($_GET['artist'] ?? '');
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $repo  = new InteractionRepository();
        $songs = $repo->getSongsByUserFiltered($userId, ESongType::FAVORITE, $artist, $offset, $limit);
        $total = $repo->countByUser($userId, ESongType::FAVORITE, $artist);

        $this->json([
            'data'  => array_map([$this, 'songToArray'], $songs),
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    // GET /api/users/{id}/liked?artist=&page=&limit=  — JWT required, owner or admin only
    public function apiLiked(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $userId    = (int) ($vars['id'] ?? 0);

        if ($tokenData->id !== $userId && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $artist = trim($_GET['artist'] ?? '');
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $repo  = new InteractionRepository();
        $songs = $repo->getSongsByUserFiltered($userId, ESongType::LIKED, $artist, $offset, $limit);
        $total = $repo->countByUser($userId, ESongType::LIKED, $artist);

        $this->json([
            'data'  => array_map([$this, 'songToArray'], $songs),
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    // POST /api/songs/{id}/favorite  — JWT required, toggle
    public function apiToggleFavorite(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $repo      = new InteractionRepository();
        $added     = $repo->toggle($tokenData->id, $songId, ESongType::FAVORITE);
        $this->json(['favorited' => $added]);
    }

    // POST /api/songs/{id}/like  — JWT required, toggle
    public function apiToggleLike(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $repo      = new InteractionRepository();
        $added     = $repo->toggle($tokenData->id, $songId, ESongType::LIKED);
        $count     = $repo->countBySong($songId, ESongType::LIKED);
        $this->json(['liked' => $added, 'count' => $count]);
    }

    private function songToArray(Song $song): array
    {
        return ['id' => $song->id, 'title' => $song->title, 'artist' => $song->artist,
                'album' => $song->album, 'genre' => $song->genre, 'link' => $song->link];
    }
}