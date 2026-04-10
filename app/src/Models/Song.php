<?php

namespace app\Models;

class Song {
    public int $id;
    public string $title;
    public string $artist;
    public ?string $album;
    public ?string $genre;
    public ?string $link;
    public string $created_at;

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
public function jsonSerialize(): mixed
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'artist' => $this->artist,
            'album'  => $this->album ?? null,
            'genre'  => $this->genre ?? null,
            'link'   => $this->link ?? null,
        ];
    }
}
