<?php

namespace App\Services;

use App\Services\Interfaces\ISongService;
use App\Repositories\Interfaces\ISongRepository;
use App\Models\Song;

class SongService implements ISongService
{
    private ISongRepository $songRepository;

    public function __construct(ISongRepository $songRepository)
    {
        $this->songRepository = $songRepository;
    }

    public function getAll(): array
    {
        return $this->songRepository->getAllSongs();
    }

    public function getById(int $id): ?Song
    {
        return $this->songRepository->getSongsById($id);
    }

    public function getSongsFiltered(string $artist, int $offset, int $limit): array
    {
        return $this->songRepository->getSongsFiltered($artist, $offset, $limit);
    }

    public function countSongs(string $artist): int
    {
        return $this->songRepository->countSongs($artist);
    }

    public function create(array $data, int $userId): int
    {
        $song             = new Song();
        $song->title      = trim($data['title']);
        $song->artist     = trim($data['artist']);
        $song->album      = !empty($data['album']) ? trim($data['album']) : null;
        $song->genre      = !empty($data['genre']) ? trim($data['genre']) : null;
        $song->link       = !empty($data['link'])  ? trim($data['link'])  : null;
        $song->created_by = $userId;
        return $this->songRepository->createSong($song);
    }

    public function update(array $data): bool
    {
        $song         = new Song();
        $song->id     = (int) $data['id'];
        $song->title  = trim($data['title']);
        $song->artist = trim($data['artist']);
        $song->album  = !empty($data['album']) ? trim($data['album']) : null;
        $song->genre  = !empty($data['genre']) ? trim($data['genre']) : null;
        $song->link   = !empty($data['link'])  ? trim($data['link'])  : null;
        return $this->songRepository->updateSong($song);
    }

    public function delete(int $id): void
    {
        $this->songRepository->deleteSong($id);
    }
}