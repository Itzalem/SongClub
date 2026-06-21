<?php

namespace App\Services\Interfaces;

interface ICommentService
{
    public function getByPost(int $postId): array;
    public function addComment(int $postId, int $userId, string $content): int;
}