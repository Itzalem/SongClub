<?php

namespace App\Models;

class Comment
{
    public int     $id;
    public int     $post_id;
    public int     $user_id;
    public string  $content;
    public string  $created_at;
    public ?string $username   = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
