<?php

namespace App\Services;

use App\Services\Interfaces\IFavoriteService;
use App\Repositories\Interfaces\IInteractionRepository;
use App\Models\ESongType;

class FavoriteService implements IFavoriteService
{
    private IInteractionRepository $repo;

    public function __construct(IInteractionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function toggleFavorite(int $userId, int $songId): bool
    {
        return $this->repo->toggle($userId, $songId, ESongType::FAVORITE);
    }

    public function toggleLike(int $userId, int $songId): bool
    {
        return $this->repo->toggle($userId, $songId, ESongType::LIKED);
    }

    public function getFavoritesByUser(int $userId): array
    {
        return $this->repo->getSongsByUser($userId, ESongType::FAVORITE);
    }

    public function getLikesByUser(int $userId): array
    {
        return $this->repo->getSongsByUser($userId, ESongType::LIKED);
    }

    public function getFavoriteIds(int $userId): array
    {
        return $this->repo->getIdsByUser($userId, ESongType::FAVORITE);
    }

    public function getLikeIds(int $userId): array
    {
        return $this->repo->getIdsByUser($userId, ESongType::LIKED);
    }

    public function getLikeCount(int $songId): int
    {
        return $this->repo->countBySong($songId, ESongType::LIKED);
    }
}
