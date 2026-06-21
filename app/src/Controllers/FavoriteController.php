<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\FavoriteService;
use App\Services\UserService;
use App\Repositories\InteractionRepository;
use App\Repositories\UserRepository;

class FavoriteController extends Controller
{
    private FavoriteService $favoriteService;
    private UserService     $userService;

    public function __construct()
    {
        $this->favoriteService = new FavoriteService(new InteractionRepository());
        $this->userService     = new UserService(new UserRepository());
    }

    // POST /favorites/toggle 
    public function toggle(array $vars = []): void
    {
        $this->requireAuth();

        $songId = (int) ($this->getBody()['song_id'] ?? 0);

        if (!$songId) {
            $this->json(['error' => 'Invalid song ID.'], 400);
        }

        $added = $this->favoriteService->toggleFavorite((int) $_SESSION['user_id'], $songId);
        $this->json(['favorited' => $added]);
    }

    // POST /likes/toggle 
    public function toggleLike(array $vars = []): void
    {
        $this->requireAuth();

        $songId = (int) ($this->getBody()['song_id'] ?? 0);

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
        $profileUser   = $this->userService->getUserById($profileUserId);

        if (!$profileUser) {
            http_response_code(404);
            echo '<p style="padding:2rem">User not found. <a href="/">Home</a></p>';
            return;
        }

        $currentUserId  = (int) ($_SESSION['user_id'] ?? 0);
        $isOwner        = ($currentUserId === $profileUserId);
        $songs          = $this->favoriteService->getFavoritesByUser($profileUserId);
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

    // GET /profile/{id}/liked (only the owner can view it)
    public function userLiked(array $vars = []): void
    {
        $this->requireAuth();

        $profileUserId = (int) ($vars['id'] ?? 0);

        if ((int) $_SESSION['user_id'] !== $profileUserId) {
            header('Location: /profile/' . $profileUserId);
            exit;
        }

        $this->render('Songs/LikedSongs', [
            'songs' => $this->favoriteService->getLikesByUser($profileUserId),
        ]);
    }

    // GET /api/users/{id}/favorites...with filters
    public function showFavorites(array $vars = []): void
    {
        $userId = (int) ($vars['id'] ?? 0);
        $artist = trim($_GET['artist'] ?? '');
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(10);

        $songs = $this->favoriteService->getFavoritesFiltered($userId, $artist, $offset, $limit);
        $total = $this->favoriteService->countFavorites($userId, $artist);

        $this->jsonPaged(array_map([$this, 'songToArray'], $songs), $page, $limit, $total);
    }

    // GET /api/users/{id}/liked....withf filters
    public function showLiked(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $userId    = (int) ($vars['id'] ?? 0);

        if ($tokenData->id !== $userId && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $artist = trim($_GET['artist'] ?? '');
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(10);

        $songs = $this->favoriteService->getLikedFiltered($userId, $artist, $offset, $limit);
        $total = $this->favoriteService->countLiked($userId, $artist);

        $this->jsonPaged(array_map([$this, 'songToArray'], $songs), $page, $limit, $total);
    }

    // POST /api/songs/{id}/favorite 
    public function favoriteSong(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $added     = $this->favoriteService->toggleFavorite((int) $tokenData->id, $songId);
        $this->json(['favorited' => $added]);
    }

    // POST /api/songs/{id}/like 
    public function likeSong(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $songId    = (int) ($vars['id'] ?? 0);
        $added     = $this->favoriteService->toggleLike((int) $tokenData->id, $songId);
        $count     = $this->favoriteService->getLikeCount($songId);
        $this->json(['liked' => $added, 'count' => $count]);
    }

    // GET /api/favorites/{userId} (here is the export to JSON)
    public function export(array $vars = []): void
    {
        $this->requireAuth();

        $userId = (int) ($vars['userId'] ?? 0);

        if ((int) $_SESSION['user_id'] !== $userId && $_SESSION['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        header('Content-Disposition: attachment; filename="favorites.json"');
        $this->json(array_map([$this, 'songToArray'], $this->favoriteService->getFavoritesByUser($userId)));
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
}
