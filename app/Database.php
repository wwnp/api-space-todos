<?php

namespace App;

use PDO;

class Database
{
    public static $instanse;
    public PDO $pdo;
    public string $host;
    public string $name;
    public string $user;
    public string $password;
    public function __construct(string $host, string $name, string $user, string $password)
    {
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->password = $password;
    }

    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }

    // public static function getInstanse(): self
    // {
    //     if (self::$instanse === null) {
    //         self::$instanse = 
    //     }
    // }
}
