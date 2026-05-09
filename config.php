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
                try {
                    self::$connexion = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                        DB_USER,
                        DB_PASS
                    );
                    self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    die("DB Connection failed: " . $e->getMessage());
                }
            }
            return self::$connexion;
        }
    }
}
