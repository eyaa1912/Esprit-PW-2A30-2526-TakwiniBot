-- Schéma SQL pour les pages :
--   views/front/Hilux-1.0.0/formations/front_mes_reclamations.html
--   views/back/sneat-final/html/gestion-reclamations.html
--
-- Ces écrans consomment controllers/ReclamationController.php ; la connexion
-- par défaut du projet est definie dans config/config.php (dbname=projet_takwini).

CREATE DATABASE IF NOT EXISTS projet_takwini
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE projet_takwini;

SET NAMES utf8mb4;

-- Utilisateurs : jointures (nom, prenom, email) et resolution admin (role = 'admin').
CREATE TABLE IF NOT EXISTS users (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    nom                     VARCHAR(100)  NOT NULL,
    prenom                  VARCHAR(100)  DEFAULT NULL,
    email                   VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe            VARCHAR(255)  NOT NULL,
    telephone               VARCHAR(20)   DEFAULT NULL,
    sexe                    ENUM('homme','femme') DEFAULT NULL,
    date_naissance          DATE          DEFAULT NULL,
    adresse                 VARCHAR(255)  DEFAULT NULL,
    handicap                TINYINT(1)    NOT NULL DEFAULT 0,
    type_handicap           VARCHAR(100)  DEFAULT NULL,
    role                    ENUM('candidat','recruteur','admin') NOT NULL DEFAULT 'candidat',
    statut                  ENUM('actif','inactif','suspendu','en_attente') NOT NULL DEFAULT 'inactif',
    email_verifie           TINYINT(1)    NOT NULL DEFAULT 0,
    avatar                  VARCHAR(255)  DEFAULT NULL,
    entreprise              VARCHAR(150)  DEFAULT NULL,
    matricule_fiscal        VARCHAR(30)   DEFAULT NULL,
    secteur                 VARCHAR(100)  DEFAULT NULL,
    document_entreprise     VARCHAR(255)  DEFAULT NULL,
    reset_token_hash        VARCHAR(255)  DEFAULT NULL,
    reset_token_expires_at  DATETIME      DEFAULT NULL,
    created_at              TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS formulaire_reclamation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(100) NOT NULL,
  date_creation DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_formulaire_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reclamation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  formulaire_id INT NOT NULL,
  sujet VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_reclamation_user (user_id),
  INDEX idx_reclamation_formulaire (formulaire_id),
  INDEX idx_reclamation_statut (statut),
  CONSTRAINT fk_reclamation_formulaire
    FOREIGN KEY (formulaire_id) REFERENCES formulaire_reclamation (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reclamation_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reponse (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT NOT NULL,
  admin_id INT NOT NULL,
  contenu TEXT NOT NULL,
  date_reponse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reponse_reclamation (reclamation_id),
  INDEX idx_reponse_admin (admin_id),
  CONSTRAINT fk_reponse_reclamation
    FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reponse_admin
    FOREIGN KEY (admin_id) REFERENCES users (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reclamation_satisfaction (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT NOT NULL,
  user_id INT NULL,
  emotion VARCHAR(30) NOT NULL,
  commentaire TEXT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_reclamation_satisfaction (reclamation_id),
  INDEX idx_reclamation_satisfaction_emotion (emotion),
  INDEX idx_reclamation_satisfaction_reclamation (reclamation_id),
  CONSTRAINT fk_reclamation_satisfaction_reclamation
    FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reclamation_satisfaction_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publication (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  titre VARCHAR(200) NOT NULL,
  contenu TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  statut ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_publication_statut (statut),
  INDEX idx_publication_user (user_id),
  CONSTRAINT fk_publication_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reclamation_vocale (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  sujet VARCHAR(200) NOT NULL,
  audio_path VARCHAR(255) NOT NULL,
  duree_secondes INT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reclamation_vocale_user (user_id),
  INDEX idx_reclamation_vocale_date (date_creation),
  CONSTRAINT fk_reclamation_vocale_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
