<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\SongService;
use App\Services\FavoriteService;
use App\Repositories\SongRepository;
use App\Repositories\InteractionRepository;
use App\Models\ESongType;

class SongController extends Controller
{
    public function index(array $vars = []): void
    {
        $service  = new SongService(new SongRepository());
        $songs    = $service->getAll();
        $likedIds = [];
        $favIds   = [];

        if (isset($_SESSION['user_id'])) {
            $favService = new FavoriteService(new InteractionRepository());
            $likedIds   = $favService->getLikeIds((int) $_SESSION['user_id']);
            $favIds     = $favService->getFavoriteIds((int) $_SESSION['user_id']);
        }

        $this->render('Index', [
            'songs'    => $songs,
            'likedIds' => $likedIds,
            'favIds'   => $favIds,
        ]);
    }

    public function show(array $vars = []): void
    {
        $service = new SongService(new SongRepository());
        $song    = $service->getById((int) $vars['id']);

        if (!$song) {
            http_response_code(404);
            echo '<p style="padding:2rem">Song not found. <a href="/songs">Back</a></p>';
            return;
        }

        $favService = new FavoriteService(new InteractionRepository());
        $likeCount  = $favService->getLikeCount($song->id);
        $isLiked    = false;
        $isFav      = false;

        if (isset($_SESSION['user_id'])) {
            $repo    = new InteractionRepository();
            $isLiked = $repo->has((int) $_SESSION['user_id'], $song->id, ESongType::LIKED);
            $isFav   = $repo->has((int) $_SESSION['user_id'], $song->id, ESongType::FAVORITE);
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
                $service = new SongService(new SongRepository());
                $service->create($_POST, (int) $_SESSION['user_id']);
                header('Location: /songs');
                exit;
            }
        }

        $this->render('Songs/CreateSong', ['error' => $error]);
    }

    public function edit(array $vars = []): void
    {
        $this->requireAuth();
        $service = new SongService(new SongRepository());
        $song    = $service->getById((int) $vars['id']);

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
                $service->update($_POST);
                header('Location: /songs/' . $song->id);
                exit;
            }
        }

        $this->render('Songs/EditSong', ['song' => $song, 'error' => $error]);
    }

    public function delete(array $vars = []): void
    {
        $this->requireAuth();
        $service = new SongService(new SongRepository());
        $song    = $service->getById((int) $vars['id']);

        if ($song && ((int) $song->created_by === (int) $_SESSION['user_id'] || $_SESSION['role'] === 'admin')) {
            $service->delete((int) $vars['id']);
        }

        header('Location: /songs');
        exit;
    }

    // GET /api/songs?artist=&genre=&page=&limit=
    public function apiList(array $vars = []): void
    {
        $artist = trim($_GET['artist'] ?? '');
        $genre  = trim($_GET['genre']  ?? '');
        $limit  = max(1, min(50, (int) ($_GET['limit'] ?? 9)));
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $offset = ($page - 1) * $limit;

        $repo  = new SongRepository();
        $songs = $repo->getSongsFiltered($artist, $offset, $limit, $genre);
        $total = $repo->countSongs($artist, $genre);
        $pages = (int) ceil($total / $limit);

        $this->json([
            'data' => array_map([$this, 'songToArray'], $songs),
            'meta' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $pages],
        ]);
    }

    // GET /api/songs/{id}
    public function apiShow(array $vars = []): void
    {
        $repo = new SongRepository();
        $song = $repo->getSongsById((int) $vars['id']);
        if (!$song) { $this->json(['error' => 'Not found'], 404); }

        $favService = new FavoriteService(new InteractionRepository());
        $data       = $this->songToArray($song);
        $data['like_count'] = $favService->getLikeCount($song->id);

        $this->json($data);
    }

    // POST /api/songs (JWT required)
    public function apiCreate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];

        $title  = trim($body['title']  ?? '');
        $artist = trim($body['artist'] ?? '');
        if ($title === '' || $artist === '') { $this->json(['error' => 'Title and artist are required'], 400); }

        $service = new SongService(new SongRepository());
        $id      = $service->create($body, (int) $tokenData->id);

        $song = (new SongRepository())->getSongsById($id);
        $this->json($this->songToArray($song), 201);
    }

    // PUT /api/songs/{id} (JWT required)
    public function apiUpdate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $repo      = new SongRepository();
        $song      = $repo->getSongsById((int) $vars['id']);

        if (!$song) { $this->json(['error' => 'Not found'], 404); }
        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        (new SongService($repo))->update(array_merge($body, ['id' => $song->id]));

        $this->json($this->songToArray($repo->getSongsById($song->id)));
    }

    // DELETE /api/songs/{id} (JWT required)
    public function apiDelete(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $repo      = new SongRepository();
        $song      = $repo->getSongsById((int) $vars['id']);

        if (!$song) { $this->json(['error' => 'Not found'], 404); }
        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
        }

        (new SongService($repo))->delete($song->id);
        $this->json(['deleted' => true]);
    }

    // API — returns all songs as JSON (legacy, kept for backward compatibility)
    public function apiIndex(array $vars = []): void
    {
        $this->apiList($vars);
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