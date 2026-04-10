<?php

class Interaction {

    public int $user_id;
    public int $song_id; //to show interactions per songs
    public string $created_at;

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
}