<?php

namespace App\Repositories;

use PDO;
use App\Framework\Repository;
use App\Repositories\Interfaces\ICommentRepository;
use App\Models\Comment;

class CommentRepository extends Repository implements ICommentRepository
{
    public function getCommentsByPost(int $postId): array
    {
        $sql = "SELECT c.*, u.username
                FROM comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at ASC";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $statement->execute();

        $rows     = $statement->fetchAll(PDO::FETCH_ASSOC);
        $comments = [];
        foreach ($rows as $row) {
            $comments[] = new Comment($row);
        }
        return $comments;
    }

    public function createComment(int $postId, int $userId, string $content): int
    {
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at)
                VALUES (:post_id, :user_id, :content, NOW())";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':content', $content);
        $statement->execute();

        return (int) $connection->lastInsertId();
    }
}
