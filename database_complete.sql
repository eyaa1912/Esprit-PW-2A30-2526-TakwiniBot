-- Base de données : projet_takwini
CREATE DATABASE IF NOT EXISTS projet_takwini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_takwini;

-- Supprimer la table si elle existe
DROP TABLE IF EXISTS users;

-- Créer la table users avec toutes les colonnes
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100)  NOT NULL,
    prenom          VARCHAR(100)  DEFAULT '',
    email           VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255)  NOT NULL,
    telephone       VARCHAR(20)   DEFAULT '',
    date_naissance  DATE          DEFAULT NULL,
    adresse         VARCHAR(255)  DEFAULT '',
    sexe            ENUM('homme','femme','') DEFAULT '',
    role            ENUM('candidat','admin') NOT NULL DEFAULT 'candidat',
    email_verifie   TINYINT(1)    NOT NULL DEFAULT 0,
    statut          ENUM('actif','inactif') NOT NULL DEFAULT 'inactif',
    avatar          VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insérer un utilisateur admin par défaut
INSERT INTO users (nom, prenom, email, mot_de_passe, role, statut) 
VALUES ('Admin', 'Super', 'admin@takwini.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif');
-- Mot de passe: password

-- Créer la table formation
CREATE TABLE IF NOT EXISTS formation (
    id_formation    INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(200)  NOT NULL,
    duree           VARCHAR(50)   NOT NULL,
    prix            DECIMAL(10,2) NOT NULL,
    niveau          VARCHAR(50)   NOT NULL,
    description     TEXT          NOT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insérer quelques formations d'exemple
INSERT INTO formation (titre, duree, prix, niveau, description) VALUES
('Développement Web Full Stack', '6 mois', 2500.00, 'Débutant', 'Formation complète en développement web avec HTML, CSS, JavaScript, PHP et MySQL'),
('Data Science avec Python', '4 mois', 3000.00, 'Intermédiaire', 'Apprenez l\'analyse de données, le machine learning et la visualisation avec Python'),
('Marketing Digital', '3 mois', 1500.00, 'Débutant', 'Maîtrisez les stratégies de marketing digital, SEO, réseaux sociaux et publicité en ligne');
