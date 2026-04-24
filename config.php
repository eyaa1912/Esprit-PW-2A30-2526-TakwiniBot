<?php
class config
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $host   = 'localhost';
            $dbname = 'projet_takwini';
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
