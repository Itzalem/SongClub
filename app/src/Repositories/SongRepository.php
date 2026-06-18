<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\ISongRepository;
use App\Models\Song;
use PDO;

class SongRepository extends Repository implements ISongRepository
{
    public function getAllSongs(): array
    {
        $sql        = "SELECT s.*, u.username AS creator_name
                       FROM songs s
                       LEFT JOIN users u ON s.created_by = u.id
                       ORDER BY s.created_at DESC";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->execute();
        $rows  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $songs = [];
        foreach ($rows as $row) {
            $songs[] = new Song($row);
        }
        return $songs;
    }

    public function getSongsFiltered(string $artist, int $offset, int $limit): array
    {
        $connection = $this->getConnection();

        if ($artist !== '') {
            $sql       = "SELECT s.*, u.username AS creator_name
                          FROM songs s
                          LEFT JOIN users u ON s.created_by = u.id
                          WHERE s.artist LIKE :artist
                          ORDER BY s.created_at DESC
                          LIMIT :limit OFFSET :offset";
            $statement = $connection->prepare($sql);
            $like      = '%' . $artist . '%';
            $statement->bindParam(':artist', $like,   PDO::PARAM_STR);
        } else {
            $sql       = "SELECT s.*, u.username AS creator_name
                          FROM songs s
                          LEFT JOIN users u ON s.created_by = u.id
                          ORDER BY s.created_at DESC
                          LIMIT :limit OFFSET :offset";
            $statement = $connection->prepare($sql);
        }

        $statement->bindParam(':limit',  $limit,  PDO::PARAM_INT);
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $songs = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $songs[] = new Song($row);
        }
        return $songs;
    }

    public function countSongs(string $artist): int
    {
        $connection = $this->getConnection();

        if ($artist !== '') {
            $sql       = "SELECT COUNT(*) FROM songs WHERE artist LIKE :artist";
            $statement = $connection->prepare($sql);
            $like      = '%' . $artist . '%';
            $statement->bindParam(':artist', $like, PDO::PARAM_STR);
        } else {
            $sql       = "SELECT COUNT(*) FROM songs";
            $statement = $connection->prepare($sql);
        }

        $statement->execute();
        return (int) $statement->fetchColumn();
    }

    public function getSongsById(int $id): ?Song
    {
        $sql        = "SELECT s.*, u.username AS creator_name
                       FROM songs s
                       LEFT JOIN users u ON s.created_by = u.id
                       WHERE s.id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? new Song($row) : null;
    }

    public function createSong(Song $song): int
    {
        $sql        = "INSERT INTO songs (title, artist, album, genre, link, created_by)
                       VALUES (:title, :artist, :album, :genre, :link, :created_by)";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':title',      $song->title,      PDO::PARAM_STR);
        $statement->bindParam(':artist',     $song->artist,     PDO::PARAM_STR);
        $statement->bindParam(':album',      $song->album,      PDO::PARAM_STR);
        $statement->bindParam(':genre',      $song->genre,      PDO::PARAM_STR);
        $statement->bindParam(':link',       $song->link,       PDO::PARAM_STR);
        $statement->bindParam(':created_by', $song->created_by, PDO::PARAM_INT);
        $statement->execute();
        return (int) $connection->lastInsertId();
    }

    public function updateSong(Song $song): bool
    {
        $sql        = "UPDATE songs
                       SET title = :title, artist = :artist, album = :album,
                           genre = :genre, link = :link
                       WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':title',  $song->title,  PDO::PARAM_STR);
        $statement->bindParam(':artist', $song->artist, PDO::PARAM_STR);
        $statement->bindParam(':album',  $song->album,  PDO::PARAM_STR);
        $statement->bindParam(':genre',  $song->genre,  PDO::PARAM_STR);
        $statement->bindParam(':link',   $song->link,   PDO::PARAM_STR);
        $statement->bindParam(':id',     $song->id,     PDO::PARAM_INT);
        return $statement->execute();
    }

    public function deleteSong(int $id): void
    {
        $sql        = "DELETE FROM songs WHERE id = :id";
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
}