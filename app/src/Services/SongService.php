<?php

namespace App\Services;

use app\Models\User;
use app\Models\ESongType;
use app\src\Services\Interfaces\ISongService;
use App\Models\Song;

class SongService implements ISongService
{
    private $songRepository;
    //private $postRepository;

    public function __construct($songRepository, $postRepository)
    {
        $this->songRepository = $songRepository;
        //$this->postRepository = $postRepository;
    }

     public function getAllSongs(): array
    {
        return $this->songRepository->getAllSongsAll();
    }

    public function getSongsById(int $id): ?Song
    {
        return $this->songRepository->getSongsById($id);
    } 

    public function getSongsByUser(User $user, ESongType $songType): array 
    {
        return $this->songRepository->getSongsByUser($user, $songType);
    }

    public function createSong(array $data, int $userId): int
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

    public function updateSong(array $data): bool
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

    public function deleteSong(int $id): void
    {
        $this->songRepository->deleteSong($id);
    }
}