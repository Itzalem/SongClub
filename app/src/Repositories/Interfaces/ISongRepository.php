<?php

namespace App\Repositories\Interfaces;

use App\Models\Song;

interface ISongRepository
{
    public function getAllSongs(): array;
    public function getSongsById(int $id): ?Song;
    public function createSong(Song $song): int;
    public function updateSong(Song $song): bool;
    public function deleteSong(int $id): void;
}
