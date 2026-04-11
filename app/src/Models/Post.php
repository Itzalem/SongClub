<?php

namespace App\Models;

class Post
{
    public int $id;
    public int $user_id;
    public int $song_id;
    public ?string $caption    = null;
    public string $created_at  = '';

    // Joined song fields — populated when fetching with JOIN
    public ?string $song_title  = null;
    public ?string $song_artist = null;
    public ?string $song_album  = null;
    public ?string $song_genre  = null;
    public ?string $song_link   = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
