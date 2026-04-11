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

    // Nuevo método para obtener todos los posts
    public function getAllByUser(int $userId): array
    {
        return $this->repo->getAllByUserId($userId);
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
