<?php

namespace App\Services\Interfaces;

use App\Models\Post;

interface IPostService
{
    /** @return Post[] */
    public function getAllByUser(int $userId): array;
    public function createPost(int $userId, int $songId, ?string $caption): int;
}
