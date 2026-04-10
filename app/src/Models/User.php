<?php

namespace App\Models;

class User {
    public $userId;
    public $username;
    public $bio;
    public $email;
    public $passwordHash;
    public ERole $role;

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
