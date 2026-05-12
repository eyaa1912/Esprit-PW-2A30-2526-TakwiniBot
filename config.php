<?php

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'takwinibot');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

if (!class_exists('config')) {
    class config
    {
        private static ?PDO $connexion = null;

        public static function getConnexion(): PDO
        {
            if (self::$connexion === null) {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

                self::$connexion = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            }
            return self::$connexion;
        }
    }
}
