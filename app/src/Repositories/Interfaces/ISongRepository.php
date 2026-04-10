<?php

namespace app\src\Repositories\Interfaces;

use app\Models\User;
use app\Models\ESongType;

interface ISongRepository
{
    public function getAllSongs(): array;
    public function getSongsById(int $id): ?Song;
    public function getSongsByUser(User $user, ESongType $songType): array ;
    public function createSong(Song $song): int;
    public function updateSong(Song $song): bool;
    public function deleteSong(int $id): void;
}