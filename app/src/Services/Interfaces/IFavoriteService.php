<?php

namespace App\Services\Interfaces;

interface IFavoriteService
{
    public function toggleFavorite(int $userId, int $songId): bool;
    public function toggleLike(int $userId, int $songId): bool;
    public function getFavoritesByUser(int $userId): array;
    public function getLikesByUser(int $userId): array;
    public function getFavoriteIds(int $userId): array;
    public function getLikeIds(int $userId): array;
    public function getLikeCount(int $songId): int;
}
