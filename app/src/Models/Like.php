<?php

class Like extends Interaction {
    public $likeId;
    public $userId;        //User who likes the song and adds it to its list
    public $targetUserId;  //User who recieves the like

   

    public function __construct($data = []) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
}