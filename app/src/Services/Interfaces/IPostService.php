<?php

namespace App\Services\Interfaces;

use App\Models\Post;

interface IPostService
{
    /** @return Post[] */
    public function getAllByUser(int $userId): array;
    public function getFeed(int $offset, int $limit): array;
    public function countFeed(): int;
    public function createPost(int $userId, int $songId, ?string $caption): int;
    public function createAndGet(int $userId, int $songId, ?string $caption): ?Post;
}