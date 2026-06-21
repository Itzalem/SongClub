<?php

namespace App\Services;

use App\Services\Interfaces\ICommentService;
use App\Repositories\Interfaces\ICommentRepository;

class CommentService implements ICommentService
{
    private ICommentRepository $repo;

    public function __construct(ICommentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getByPost(int $postId): array
    {
        return $this->repo->getCommentsByPost($postId);
    }

    public function addComment(int $postId, int $userId, string $content): int
    {
        return $this->repo->createComment($postId, $userId, $content);
    }
}