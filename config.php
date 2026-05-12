<?php
// Chemin de base du projet (détecté automatiquement)
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Remonte jusqu'à la racine du projet (le dossier contenant config.php)
    $docRoot   = str_replace('\\', '/', realpath(__DIR__));
    $htdocs    = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $relative  = ltrim(str_replace($htdocs, '', $docRoot), '/');
    define('BASE_URL', '/' . $relative);
}

class config
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $host   = 'localhost';
            $dbname = 'takwini_db';
            $user   = 'root';
            $pass   = '';
            $dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            self::$connexion = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$connexion;
    }
}
