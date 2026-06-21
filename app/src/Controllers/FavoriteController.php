<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\FavoriteService;
use App\Services\UserService;
use App\Repositories\InteractionRepository;
use App\Repositories\UserRepository;
use App\Models\ESongType;

class FavoriteController extends Controller
{
    private FavoriteService $favoriteService;

    public function __construct()
    {
        $this->favoriteService = new FavoriteService(new InteractionRepository());
    }

    public function toggle(array $vars = []): void
    {
        $this->requireAuth();

        $body   = $this->getBody();
        $songId = (int) ($body['song_id'] ?? 0);

        if (!$songId) {
            $this->json(['error' => 'Invalid song ID.'], 400);
        }

        $added = $this->favoriteService->toggleFavorite((int) $_SESSION['user_id'], $songId);
        $this->json(['favorited' => $added]);
    }

    public function toggleLike(array $vars = []): void
    {
        $this->requireAuth();

        $body   = $this->getBody();
        $songId = (int) ($body['song_id'] ?? 0);

        if (!$songId) {
            $this->json(['error' => 'Invalid song ID.'], 400);
        }

        $added = $this->favoriteService->toggleLike((int) $_SESSION['user_id'], $songId);
        $count = $this->favoriteService->getLikeCount($songId);
        $this->json(['liked' => $added, 'count' => $count]);
    }

    // GET /profile/{id}/favorites
    public function userFavorites(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);

        $userService = new UserService(new UserRepository());
        $profileUser = $userService->getUserById($profileUserId);

        if (!$profileUser) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $isOwner       = ($currentUserId === $profileUserId);

        $songs = $this->favoriteService->getFavoritesByUser($profileUserId);

        $viewerLikedIds = [];
        $viewerFavIds   = [];
        if (!$isOwner) {
            $viewerLikedIds = $this->favoriteService->getLikeIds($currentUserId);
            $viewerFavIds   = $this->favoriteService->getFavoriteIds($currentUserId);
        }

        $this->render('Songs/FavoriteSongs', [
            'profileUser'    => $profileUser,
            'songs'          => $songs,
            'isOwner'        => $isOwner,
            'viewerLikedIds' => $viewerLikedIds,
            'viewerFavIds'   => $viewerFavIds,
        ]);
    }

    // GET /profile/{id}/liked
    public function userLiked(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);

        if ((int) $_SESSION['user_id'] !== $profileUserId) {
            header('Location: /profile/' . $profileUserId);
            exit;
        }

        $songs = $this->favoriteService->getLikesByUser($profileUserId);

        $this->render('Songs/LikedSongs', ['songs' => $songs]);
    }

    // GET /api/users/{id}/favorites?artist=&page=&limit=
    public function apiFavorites(array $vars = []): void
    {
        $userId = (int) ($vars['id'] ?? 0);
        $artist = trim($_GET['artist'] ?? '');
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(10);

        $repo  = new InteractionRepository();
        $songs = $repo->getSongsByUserFiltered($userId, ESongType::FAVORITE, $artist, $offset, $limit);
        $total = $repo->countByUser($userId, ESongType::FAVORITE, $artist);
        $pages = (int) ceil($total / $limit) ?: 1;

        $this->json([
            'data' => array_map([$this, 'songToArray'], $songs),
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $pages],
        ]);
    }

    // GET /api/users/{id}/liked?artist=&page=&limit= (JWT required, owner/admin only)
    public function apiLiked(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $userId    = (int) ($vars['id'] ?? 0);

        if ($tokenData->id !== $userId && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $artist = trim($_GET['artist'] ?? '');
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(10);

        $repo  = new InteractionRepository();
        $songs = $repo->getSongsByUserFiltered($userId, ESongType::LIKED, $artist, $offset, $limit);
        $total = $repo->countByUser($userId, ESongType::LIKED, $artist);
        $pages = (int) ceil($total / $limit) ?: 1;

        $this->json([
            'data' => array_map([$this, 'songToArray'], $songs),
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $pages],
        ]);
    }

    // POST /api/songs/{id}/favorite (JWT required)
    public function apiToggleFavorite(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $added     = $this->favoriteService->toggleFavorite((int) $tokenData->id, $songId);
        $this->json(['favorited' => $added]);
    }

    // POST /api/songs/{id}/like (JWT required)
    public function apiToggleLike(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $added     = $this->favoriteService->toggleLike((int) $tokenData->id, $songId);
        $count     = $this->favoriteService->getLikeCount($songId);
        $this->json(['liked' => $added, 'count' => $count]);
    }

    private function songToArray(\App\Models\Song $s): array
    {
        return [
            'id'     => $s->id,
            'title'  => $s->title,
            'artist' => $s->artist,
            'album'  => $s->album,
            'genre'  => $s->genre,
            'link'   => $s->link,
        ];
    }

    // API - export favorites as JSON download
    public function export(array $vars = []): void
    {
        $this->requireAuth();

        $userId = (int) ($vars['userId'] ?? 0);

        if ((int) $_SESSION['user_id'] !== $userId && $_SESSION['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $favorites = $this->favoriteService->getFavoritesByUser($userId);

        header('Content-Disposition: attachment; filename="favorites.json"');
        $this->json(array_map([$this, 'songToArray'], $favorites));
    }
}