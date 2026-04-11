<?php

namespace App\Services;

use App\Services\Interfaces\IPostService;
use App\Repositories\Interfaces\IPostRepository;
use App\Models\Post;

class PostService implements IPostService
{
    private IPostRepository $repo;

    public function __construct(IPostRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getLastByUser(int $userId): ?Post
    {
        return $this->repo->getLastByUserId($userId);
    }

    public function createPost(int $userId, int $songId, ?string $caption): int
    {
        $post          = new Post();
        $post->user_id = $userId;
        $post->song_id = $songId;
        $post->caption = $caption;
        return $this->repo->createPost($post);
    }
}
