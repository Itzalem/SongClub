<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\SongService;
use App\Services\FavoriteService;
use App\Repositories\SongRepository;
use App\Repositories\InteractionRepository;

class SongController extends Controller
{
    private SongService $songService;
    private FavoriteService $favoriteService;

    public function __construct()
    {
        $this->songService     = new SongService(new SongRepository());
        $this->favoriteService = new FavoriteService(new InteractionRepository());
    }

    public function index(array $vars = []): void
    {
        $songs    = $this->songService->getAll();
        $likedIds = [];
        $favIds   = [];

        if (isset($_SESSION['user_id'])) {
            $likedIds = $this->favoriteService->getLikeIds((int) $_SESSION['user_id']);
            $favIds   = $this->favoriteService->getFavoriteIds((int) $_SESSION['user_id']);
        }

        $this->render('Index', [
            'songs'    => $songs,
            'likedIds' => $likedIds,
            'favIds'   => $favIds,
        ]);
    }

    public function show(array $vars = []): void
    {
        $song = $this->songService->getById((int) $vars['id']);

        if (!$song) {
            http_response_code(404);
            echo '<p style="padding:2rem">Song not found. <a href="/songs">Back</a></p>';
            return;
        }

        $likeCount = $this->favoriteService->getLikeCount($song->id);
        $isLiked   = false;
        $isFav     = false;

        if (isset($_SESSION['user_id'])) {
            $isLiked = in_array($song->id, $this->favoriteService->getLikeIds((int) $_SESSION['user_id']));
            $isFav   = in_array($song->id, $this->favoriteService->getFavoriteIds((int) $_SESSION['user_id']));
        }

        $this->render('Songs/Show', [
            'song'      => $song,
            'likeCount' => $likeCount,
            'isLiked'   => $isLiked,
            'isFav'     => $isFav,
        ]);
    }

    public function create(array $vars = []): void
    {
        $this->requireAuth();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title  = trim($_POST['title']  ?? '');
            $artist = trim($_POST['artist'] ?? '');

            if ($title === '' || $artist === '') {
                $error = 'Title and artist are required.';
            } else {
                $this->songService->create($_POST, (int) $_SESSION['user_id']);
                header('Location: /songs');
                exit;
            }
        }

        $this->render('Songs/CreateSong', ['error' => $error]);
    }

    public function edit(array $vars = []): void
    {
        $this->requireAuth();
        $song = $this->songService->getById((int) $vars['id']);

        if (!$song) {
            header('Location: /songs');
            exit;
        }

        if ((int) $song->created_by !== (int) $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            header('Location: /songs');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title  = trim($_POST['title']  ?? '');
            $artist = trim($_POST['artist'] ?? '');

            if ($title === '' || $artist === '') {
                $error = 'Title and artist are required.';
            } else {
                $_POST['id'] = $song->id;
                $this->songService->update($_POST);
                header('Location: /songs/' . $song->id);
                exit;
            }
        }

        $this->render('Songs/EditSong', ['song' => $song, 'error' => $error]);
    }

    public function delete(array $vars = []): void
    {
        $this->requireAuth();
        $song = $this->songService->getById((int) $vars['id']);

        if ($song && ((int) $song->created_by === (int) $_SESSION['user_id'] || $_SESSION['role'] === 'admin')) {
            $this->songService->delete((int) $vars['id']);
        }

        header('Location: /songs');
        exit;
    }

    // GET /api/songs?artist=&page=&limit=
    public function apiList(array $vars = []): void
    {
        $artist = trim($_GET['artist'] ?? '');
        ['page' => $page, 'limit' => $limit, 'offset' => $offset] = $this->getPagination(9);

        $songs = $this->songService->getSongsFiltered($artist, $offset, $limit);
        $total = $this->songService->countSongs($artist);
        $pages = (int) ceil($total / $limit) ?: 1;

        $this->json([
            'data' => array_map([$this, 'songToArray'], $songs),
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $pages],
        ]);
    }

    // GET /api/songs/{id}
    public function apiShow(array $vars = []): void
    {
        $song = $this->songService->getById((int) $vars['id']);
        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        $data               = $this->songToArray($song);
        $data['like_count'] = $this->favoriteService->getLikeCount($song->id);

        $this->json($data);
    }

    // POST /api/songs (JWT required)
    public function apiCreate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = $this->getBody();
        $title     = trim($body['title']  ?? '');
        $artist    = trim($body['artist'] ?? '');

        if ($title === '' || $artist === '') {
            $this->json(['error' => 'Title and artist are required.'], 400);
        }

        $id   = $this->songService->create($body, (int) $tokenData->id);
        $song = $this->songService->getById($id);

        $this->json($this->songToArray($song), 201);
    }

    // PUT /api/songs/{id} (JWT required)
    public function apiUpdate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $song      = $this->songService->getById((int) $vars['id']);

        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $body = $this->getBody();
        $this->songService->update(array_merge($body, ['id' => $song->id]));

        $this->json($this->songToArray($this->songService->getById($song->id)));
    }

    // DELETE /api/songs/{id} (JWT required)
    public function apiDelete(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $song      = $this->songService->getById((int) $vars['id']);

        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $this->songService->delete($song->id);
        $this->json(['deleted' => true]);
    }

    private function songToArray(\App\Models\Song $s): array
    {
        return [
            'id'           => $s->id,
            'title'        => $s->title,
            'artist'       => $s->artist,
            'album'        => $s->album,
            'genre'        => $s->genre,
            'link'         => $s->link,
            'created_by'   => $s->created_by,
            'creator_name' => $s->creator_name ?? null,
        ];
    }
}