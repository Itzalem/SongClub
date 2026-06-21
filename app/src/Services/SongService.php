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

    public function getSongsFiltered(string $artist, int $offset, int $limit, string $genre = ''): array
    {
        return $this->songRepository->getSongsFiltered($artist, $offset, $limit, $genre);
    }

    public function countSongs(string $artist, string $genre = ''): int
    {
        return $this->songRepository->countSongs($artist, $genre);
    }

    public function create(array $data, int $userId): int
    {
        $song             = $this->buildSong($data);
        $song->created_by = $userId;
        return $this->songRepository->createSong($song);
    }

    public function update(array $data): bool
    {
        $song     = $this->buildSong($data);
        $song->id = (int) $data['id'];
        return $this->songRepository->updateSong($song);
    }

    public function delete(int $id): void
    {
        $this->songRepository->deleteSong($id);
    }

    private function buildSong(array $data): Song
    {
        $song         = new Song();
        $song->title  = trim($data['title']);
        $song->artist = trim($data['artist']);
        $song->album  = !empty($data['album']) ? trim($data['album']) : null;
        $song->genre  = !empty($data['genre']) ? trim($data['genre']) : null;
        $song->link   = !empty($data['link'])  ? trim($data['link'])  : null;
        return $song;
    }
}