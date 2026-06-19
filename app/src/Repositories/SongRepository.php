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

    public function getSongsFiltered(string $artist, int $offset, int $limit, string $genre = ''): array
    {
        $connection = $this->getConnection();
        $conditions = [];
        $params     = [];

        if ($artist !== '') {
            $conditions[] = 's.artist LIKE :artist';
            $params[':artist'] = '%' . $artist . '%';
        }
        if ($genre !== '') {
            $conditions[] = 's.genre LIKE :genre';
            $params[':genre'] = '%' . $genre . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT s.*, u.username AS creator_name
                  FROM songs s
                  LEFT JOIN users u ON s.created_by = u.id
                  {$where}
                  ORDER BY s.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $statement = $connection->prepare($sql);
        foreach ($params as $key => $val) {
            $statement->bindValue($key, $val, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $songs = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $songs[] = new Song($row);
        }
        return $songs;
    }

    public function countSongs(string $artist, string $genre = ''): int
    {
        $connection = $this->getConnection();
        $conditions = [];
        $params     = [];

        if ($artist !== '') {
            $conditions[] = 'artist LIKE :artist';
            $params[':artist'] = '%' . $artist . '%';
        }
        if ($genre !== '') {
            $conditions[] = 'genre LIKE :genre';
            $params[':genre'] = '%' . $genre . '%';
        }

        $where     = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql       = "SELECT COUNT(*) FROM songs {$where}";
        $statement = $connection->prepare($sql);
        foreach ($params as $key => $val) {
            $statement->bindValue($key, $val, PDO::PARAM_STR);
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
