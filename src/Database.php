<?php

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host   = $_ENV['DB_HOST']   ?? 'localhost';
            $dbname = $_ENV['DB_NAME']   ?? 'todo_app';
            $user   = $_ENV['DB_USER']   ?? 'root';
            $pass   = $_ENV['DB_PASS']   ?? '';

            self::$instance = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }
        return self::$instance;
    }
}
