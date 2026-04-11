<?php

namespace App\ViewModels;

use App\Models\User;
use App\Models\Post;
use App\Models\Song;

class ProfileVm
{
    public User $user;

    public ?Post $lastPost = null;

    /** @var \App\Models\Comment[] */
    public array $comments = [];

    /** @var \App\Models\Song[] */
    public array $favorites = [];

    /** @var \App\Models\Song[] */
    public array $likes = [];

    /** @var \App\Models\Song[] — songs created by the user (for "set last listened" dropdown) */
    public array $songs = [];

    public bool $isOwner = false;
}
