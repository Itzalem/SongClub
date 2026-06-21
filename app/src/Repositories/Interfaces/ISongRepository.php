<?php

namespace App\Repositories\Interfaces;

use App\Models\Song;

interface ISongRepository
{
    public function getAllSongs(): array;
    public function getSongsById(int $id): ?Song;
    public function getSongsFiltered(string $artist, int $offset, int $limit): array;
    public function countSongs(string $artist): int;
    public function createSong(Song $song): int;
    public function updateSong(Song $song): bool;
    public function deleteSong(int $id): void;
}