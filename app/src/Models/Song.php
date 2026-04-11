<?php

namespace App\Models;

class Song
{
    public int     $id;
    public string  $title;
    public string  $artist;
    public ?string $album        = null;
    public ?string $genre        = null;
    public ?string $link         = null;
    public int     $created_by;
    public string  $created_at;
    public ?string $creator_name = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
