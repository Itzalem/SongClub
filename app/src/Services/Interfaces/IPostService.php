<?php

namespace App\Services\Interfaces;

use App\Models\Post;

interface IPostService
{
    public function getLastByUser(int $userId): ?Post;
    public function createPost(int $userId, int $songId, ?string $caption): int;
}
