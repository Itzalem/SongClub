<?php

class Post {
    public int $id;
    public int $user_id;
    public int $song_id;
    public ?string $caption;
    public string $created_at;

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
