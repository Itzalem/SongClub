<?php

namespace App\Repositories;

use PDO;
use App\Framework\Repository;
use App\Repositories\Interfaces\IPostRepository;
use App\Models\Post;

class PostRepository extends Repository implements IPostRepository
{
    public function buildPostModel(array $postData): Post
    {
        $post             = new Post();
        $post->id         = (int) $postData['id'];
        $post->user_id    = (int) $postData['user_id'];
        $post->song_id    = (int) $postData['song_id'];
        $post->caption    = $postData['caption'] ?? null;
        $post->created_at = $postData['created_at'] ?? '';
        return $post;
    }

    public function getAllByUserId(int $userId): array
    {
        $sql = "SELECT p.*, s.title AS song_title, s.artist AS song_artist,
                       s.album AS song_album, s.genre AS song_genre, s.link AS song_link
                FROM posts p
                JOIN songs s ON p.song_id = s.id
                WHERE p.user_id = :id
                ORDER BY p.created_at DESC";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        $statement->execute();

        $posts = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function getLastByUserId(int $userId): ?Post
    {
        $sql = "SELECT p.*, s.title AS song_title, s.artist AS song_artist,
                       s.album AS song_album, s.genre AS song_genre, s.link AS song_link
                FROM posts p
                JOIN songs s ON p.song_id = s.id
                WHERE p.user_id = :id
                ORDER BY p.created_at DESC
                LIMIT 1";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? new Post($row) : null;
    }

    // Social feed: all users' last listened posts with user + song info
    public function getFeed(int $offset, int $limit): array
    {
        $sql = "SELECT p.*, 
                       s.title AS song_title, s.artist AS song_artist,
                       s.album AS song_album, s.genre AS song_genre, s.link AS song_link,
                       u.username,
                       (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
                FROM posts p
                JOIN songs s ON p.song_id = s.id
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':limit',  $limit,  PDO::PARAM_INT);
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $posts = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function countFeed(): int
    {
        $statement = $this->getConnection()->query("SELECT COUNT(*) FROM posts");
        return (int) $statement->fetchColumn();
    }

    public function createPost(Post $post): int
    {
        $connection = $this->getConnection();

        $sql = "INSERT INTO posts (caption, user_id, song_id, created_at)
                VALUES (:caption, :user_id, :song_id, NOW())
                ON DUPLICATE KEY UPDATE song_id = :song_id2, caption = :caption2, created_at = NOW()";

        $statement = $connection->prepare($sql);
        $statement->bindParam(':caption',  $post->caption);
        $statement->bindParam(':user_id',  $post->user_id,  PDO::PARAM_INT);
        $statement->bindParam(':song_id',  $post->song_id,  PDO::PARAM_INT);
        $statement->bindParam(':song_id2', $post->song_id,  PDO::PARAM_INT);
        $statement->bindParam(':caption2', $post->caption);
        $statement->execute();

        return (int) $connection->lastInsertId();
    }

    public function updatePost(Post $post): int
    {
        $sql        = "UPDATE posts SET caption = :caption, song_id = :song_id WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':caption', $post->caption);
        $statement->bindParam(':song_id', $post->song_id, PDO::PARAM_INT);
        $statement->bindParam(':id',      $post->id,      PDO::PARAM_INT);
        $statement->execute();
        return $post->id;
    }

    public function deletePost(int $id): void
    {
        $sql        = "DELETE FROM posts WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}