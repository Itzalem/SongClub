<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;

interface IPostRepository
{
    public function getLastByUserId(int $userId): ?Post;
    public function createPost(Post $post): int;
    public function updatePost(Post $post): int;
    public function deletePost(int $id): void;
}
