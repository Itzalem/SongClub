<?php

namespace App\ViewModels;

use app\Models\Song;
use app\Models\User;

class ProfileVm {
    public User $user;     

    public Song $lastSong;    

    /** @var \App\Models\Comment[] */
    public $comments;

    /** @var \App\Models\Song[] */   
    public $favorites; 

    /** @var \App\Models\Song[] */  
    public $likes;

    public $isOwner;     
}