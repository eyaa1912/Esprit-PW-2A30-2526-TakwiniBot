-- Base de données : projet_takwini
CREATE DATABASE IF NOT EXISTS projet_takwini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_takwini;

CREATE TABLE IF NOT EXISTS users (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nom            VARCHAR(100)  NOT NULL,
    email          VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe   VARCHAR(255)  NOT NULL,
    role           ENUM('candidat','admin') NOT NULL DEFAULT 'candidat',
    email_verifie  TINYINT(1)    NOT NULL DEFAULT 0,
    statut         ENUM('actif','inactif') NOT NULL DEFAULT 'inactif',
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
