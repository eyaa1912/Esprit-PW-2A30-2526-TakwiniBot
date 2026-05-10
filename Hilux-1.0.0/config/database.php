<?php

class config
{
    private static $pdo = null;
    private static $host = '127.0.0.1';
    private static $port = '3306';
    private static $user = 'root';
    private static $password = '';
    private static $database = 'projet_takwini';

    // OAuth and mail credentials should be set via environment variables (see .env.example)
    private static $googleClientId = null;
    private static $googleClientSecret = null;
    private static $googleRedirectUri = 'http://localhost:8080/Recettes/CONTROLLER/AuthController.php?action=google_callback';
    private static $mailFrom = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$database . ';charset=utf8mb4',
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    public static function getGoogleClientId()
    {
        return trim((string) self::$googleClientId);
    }

    public static function getGoogleClientSecret()
    {
        return trim((string) self::$googleClientSecret);
    }

    public static function getGoogleRedirectUri()
    {
        return trim((string) self::$googleRedirectUri);
    }

    public static function getMailFrom()
    {
        return trim((string) self::$mailFrom);
    }
}

