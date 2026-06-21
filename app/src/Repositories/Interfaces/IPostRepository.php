<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;

interface IPostRepository
{
    public function getAllByUserId(int $userId): array;
    public function getFeed(int $offset, int $limit): array;
    public function countFeed(): int;
    public function createPost(Post $post): int;
    public function deletePost(int $id): void;
}