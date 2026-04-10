<?php

class Comment {
    public int $id;
    public int $post_id;
    public int $user_id;
    public string $content;
    public string $created_at;
    public ?string $username;

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
