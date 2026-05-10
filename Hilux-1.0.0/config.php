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

    // Email Configuration (SMTP)
    private static $smtpHost = 'smtp.gmail.com';
    private static $smtpPort = 587;
    private static $smtpUser = ''; // Set your Gmail address here
    private static $smtpPassword = ''; // Set your Gmail app password here
    private static $smtpFromEmail = ''; // Set your Gmail address here
    private static $smtpFromName = 'TakwiniBot';

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

    public static function getSmtpHost()
    {
        return trim((string) self::$smtpHost);
    }

    public static function getSmtpPort()
    {
        return (int) self::$smtpPort;
    }

    public static function getSmtpUser()
    {
        return trim((string) self::$smtpUser);
    }

    public static function getSmtpPassword()
    {
        return trim((string) self::$smtpPassword);
    }

    public static function getSmtpFromEmail()
    {
        return trim((string) self::$smtpFromEmail);
    }

    public static function getSmtpFromName()
    {
        return trim((string) self::$smtpFromName);
    }
}
?>
