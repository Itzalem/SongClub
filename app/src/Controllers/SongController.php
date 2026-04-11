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

        $this->render('songs/index', [
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

        $this->render('songs/show', [
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

        $this->render('songs/create', ['error' => $error]);
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

        $this->render('songs/edit', ['song' => $song, 'error' => $error]);
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

    // API
    public function apiIndex(array $vars = []): void
    {
        $songs  = (new SongRepository())->findAll();
        $result = [];
        foreach ($songs as $s) {
            $result[] = [
                'id'     => $s->id,
                'title'  => $s->title,
                'artist' => $s->artist,
                'album'  => $s->album,
                'genre'  => $s->genre,
                'link'   => $s->link,
            ];
        }
        $this->json($result);
    }
}
