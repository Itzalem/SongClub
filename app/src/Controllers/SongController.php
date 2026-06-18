<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\SongService;
use App\Services\FavoriteService;
use App\Repositories\SongRepository;
use App\Repositories\InteractionRepository;
use App\Models\ESongType;
use App\Models\Song;

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

    // ── REST API methods ────────────────────────────────────────────────────

    // GET /api/songs?artist=queen&page=1&limit=10
    public function apiList(array $vars = []): void
    {
        $artist = trim($_GET['artist'] ?? '');
        $page   = max(1, (int) ($_GET['page']  ?? 1));
        $limit  = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $repo  = new SongRepository();
        $songs = $repo->getSongsFiltered($artist, $offset, $limit);
        $total = $repo->countSongs($artist);

        $this->json([
            'data'  => array_map([$this, 'songToArray'], $songs),
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    // GET /api/songs/{id}
    public function apiShow(array $vars = []): void
    {
        $song = (new SongService(new SongRepository()))->getById((int) $vars['id']);

        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        $this->json($this->songToArray($song));
    }

    // POST /api/songs  — requires JWT
    public function apiCreate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];

        $title  = trim($body['title']  ?? '');
        $artist = trim($body['artist'] ?? '');

        if ($title === '' || $artist === '') {
            $this->json(['error' => 'Title and artist are required.'], 400);
        }

        $service = new SongService(new SongRepository());
        $id      = $service->create($body, $tokenData->id);
        $song    = $service->getById($id);

        $this->json($this->songToArray($song), 201);
    }

    // PUT /api/songs/{id}  — requires JWT, owner or admin
    public function apiUpdate(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $service   = new SongService(new SongRepository());
        $song      = $service->getById((int) $vars['id']);

        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $title  = trim($body['title']  ?? '');
        $artist = trim($body['artist'] ?? '');

        if ($title === '' || $artist === '') {
            $this->json(['error' => 'Title and artist are required.'], 400);
        }

        $body['id'] = $song->id;
        $service->update($body);
        $updated = $service->getById($song->id);

        $this->json($this->songToArray($updated));
    }

    // DELETE /api/songs/{id}  — requires JWT, owner or admin
    public function apiDelete(array $vars = []): void
    {
        $tokenData = $this->validateJWT();
        $service   = new SongService(new SongRepository());
        $song      = $service->getById((int) $vars['id']);

        if (!$song) {
            $this->json(['error' => 'Song not found.'], 404);
        }

        if ($song->created_by !== $tokenData->id && $tokenData->role !== 'admin') {
            $this->json(['error' => 'Forbidden.'], 403);
        }

        $service->delete($song->id);
        $this->json(['message' => 'Song deleted.']);
    }

    private function songToArray(Song $song): array
    {
        return [
            'id'           => $song->id,
            'title'        => $song->title,
            'artist'       => $song->artist,
            'album'        => $song->album,
            'genre'        => $song->genre,
            'link'         => $song->link,
            'created_by'   => $song->created_by,
            'creator_name' => $song->creator_name,
            'created_at'   => $song->created_at,
        ];
    }

    // kept for backward compatibility with old PHP view
    public function apiIndex(array $vars = []): void
    {
        $this->apiList($vars);
    }
}