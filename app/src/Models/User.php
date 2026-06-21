<?php

namespace App\Models;

class User
{
    public ?int    $userId       = null;
    public ?string $username     = null;
    public ?string $bio          = null;
    public ?string $email        = null;
    public ?string $passwordHash = null;
    public ?string $role         = null;   

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
