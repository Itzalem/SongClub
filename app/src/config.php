<?php

namespace App;

class Config
{
    const DB_SERVER_NAME = 'mysql';
    const DB_NAME = 'SongClub';
    const DB_USERNAME    = 'root';
    const DB_PASSWORD    = 'secret123';
    const JWT_SECRET     = 'songclub_secret_key_change_in_production';
    const JWT_EXPIRES_IN = 3600;
}
