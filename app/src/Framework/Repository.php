<?php

namespace App\Framework;

use App\Config;
use PDO;        
use PDOException;

abstract class Repository
{
    private ?PDO $connection = null; 

    // create and return a PDO connection to the database 
    protected function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $connectionString = 'mysql:host=' . Config::DB_SERVER_NAME . ';dbname=' . Config::DB_NAME . ';charset=utf8mb4';

                $this->connection = new PDO(
                    $connectionString,
                    Config::DB_USERNAME,
                    Config::DB_PASSWORD
                );
                
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } catch (PDOException $e) {
                die("Connection error: " . $e->getMessage());
            }
        }

        return $this->connection;
    }
}