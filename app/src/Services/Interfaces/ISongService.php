<?php

namespace app\src\Services\Interfaces;

use app\Models\User;
use app\Models\ESongType;
use app\Models\Song;

interface ISongService
{
    public function getSongsByUser(User $user, ESongType $songType): array;
    public function getAllSongs(): array;
    public function getSongsById(int $id): ?Song;
    public function createSong(array $data, int $userId): int;
    public function updateSong(array $data): bool;
    public function deleteSong(int $id): void;
}