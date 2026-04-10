<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\ISongRepository;
use App\Models\Song;
use App\Models\User;
use App\Models\ESongType;
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


    public function getSongsByUser(User $user, ESongType $songType): array
    {
        if($songType === ESongType::FAVORITE) {
       $sql = "SELECT s.id, s.title, s.artist, s.album, s.genre, s.link 
            FROM SONGS s
            JOIN FAVORITES f ON s.id = f.song_id
            WHERE f.user_id = :userId";
        
    }
    else if($songType === ESongType::LIKED) {
        $sql = "SELECT s.id, s.title, s.artist, s.album, s.genre, s.link 
            FROM SONGS s
            JOIN LIKES f ON s.id = f.song_id
            WHERE f.user_id = :userId";
    }
        $connection = $this->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam(':userId', $user->userId, PDO::PARAM_INT);
        $statement->execute();
        $rows  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $songs = [];
        foreach ($rows as $row) {
            $songs[] = new Song($row);
        }
        return $songs;

        

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
