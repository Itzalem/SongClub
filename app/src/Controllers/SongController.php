<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Services\SongService;
use App\Repositories\SongRepository;

class SongController extends Controller
{
    private $songService;

    public function __construct(SongService $songService) {
        $this->songService = $songService;
    }

    public function index(array $vars = []): void
    {
        $songs   = $this->songService->getAllSongs();
        $this->render('songs/index', ['songs' => $songs]);
    }
    
    public function show(array $vars = []): void
    {
        $song    = $this->songService->getSongsById((int) $vars['id']);

        if (!$song) {
            http_response_code(404);
            echo '<p style="padding:2rem">Song not found. <a href="/songs">Back</a></p>';
            return;
        }

        $this->render('songs/show', ['song' => $song]);
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
                $this->songService->createSong($_POST, (int) $_SESSION['user_id']);
                header('Location: /songs');
                exit;
            }
        }

        $this->render('songs/create', ['error' => $error]);
    }

    public function edit(array $vars = []): void
    {
        $this->requireAuth();
        $song    = $this->songService->getSongsById((int) $vars['id']);

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
                $this->songService->updateSong($_POST);
                header('Location: /songs/' . $song->id);
                exit;
            }
        }

        $this->render('songs/edit', ['song' => $song, 'error' => $error]);
    }

    public function delete(array $vars = []): void
    {
        $this->requireAuth();
        $song    = $this->songService->getSongsById((int) $vars['id']);

        if ($song && ((int) $song->created_by === (int) $_SESSION['user_id'] || $_SESSION['role'] === 'admin')) {
            $this->songService->deleteSong((int) $vars['id']);
        }

        header('Location: /songs');
        exit;
    }
}
