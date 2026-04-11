<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IInteractionRepository;
use App\Models\Song;
use App\Models\ESongType;
use PDO;

class InteractionRepository extends Repository implements IInteractionRepository
{
    private function getTable(ESongType $type): string
    {
        return $type === ESongType::FAVORITE ? 'favorites' : 'likes';
    }

    public function toggle(int $userId, int $songId, ESongType $type): bool
    {
        if ($this->has($userId, $songId, $type)) {
            $this->remove($userId, $songId, $type);
            return false;
        }
        $this->add($userId, $songId, $type);
        return true;
    }

    public function has(int $userId, int $songId, ESongType $type): bool
    {
        $table      = $this->getTable($type);
        $sql        = "SELECT COUNT(*) FROM {$table}
                       WHERE user_id = :userId AND song_id = :songId";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->bindParam(':songId', $songId, PDO::PARAM_INT);
        $statement->execute();
        return (int) $statement->fetchColumn() > 0;
    }

    private function add(int $userId, int $songId, ESongType $type): void
    {
        $table      = $this->getTable($type);
        $sql        = "INSERT IGNORE INTO {$table} (user_id, song_id)
                       VALUES (:userId, :songId)";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->bindParam(':songId', $songId, PDO::PARAM_INT);
        $statement->execute();
    }

    private function remove(int $userId, int $songId, ESongType $type): void
    {
        $table      = $this->getTable($type);
        $sql        = "DELETE FROM {$table}
                       WHERE user_id = :userId AND song_id = :songId";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->bindParam(':songId', $songId, PDO::PARAM_INT);
        $statement->execute();
    }

    public function getSongsByUser(int $userId, ESongType $type): array
    {
        $table      = $this->getTable($type);
        $sql        = "SELECT s.* FROM songs s
                       JOIN {$table} i ON s.id = i.song_id
                       WHERE i.user_id = :userId
                       ORDER BY i.created_at DESC";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();
        $rows  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $songs = [];
        foreach ($rows as $row) {
            $songs[] = new Song($row);
        }
        return $songs;
    }

    public function getIdsByUser(int $userId, ESongType $type): array
    {
        $table      = $this->getTable($type);
        $sql        = "SELECT song_id FROM {$table} WHERE user_id = :userId";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function countBySong(int $songId, ESongType $type): int
    {
        $table      = $this->getTable($type);
        $sql        = "SELECT COUNT(*) FROM {$table} WHERE song_id = :songId";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':songId', $songId, PDO::PARAM_INT);
        $statement->execute();
        return (int) $statement->fetchColumn();
    }
}
