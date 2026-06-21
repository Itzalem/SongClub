<?php

namespace App\Repositories\Interfaces;

use App\Models\ESongType;

interface IInteractionRepository
{
    public function toggle(int $userId, int $songId, ESongType $type): bool;
    public function has(int $userId, int $songId, ESongType $type): bool;
    public function getSongsByUser(int $userId, ESongType $type): array;
    public function getSongsByUserFiltered(int $userId, ESongType $type, string $artist, int $offset, int $limit): array;
    public function getIdsByUser(int $userId, ESongType $type): array;
    public function countBySong(int $songId, ESongType $type): int;
    public function countByUser(int $userId, ESongType $type, string $artist): int;
}