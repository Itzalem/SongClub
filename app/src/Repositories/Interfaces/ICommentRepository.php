<?php

namespace App\Repositories\Interfaces;

interface ICommentRepository
{
    public function getCommentsByPost(int $postId): array;
    public function createComment(int $postId, int $userId, string $content): int;
}
