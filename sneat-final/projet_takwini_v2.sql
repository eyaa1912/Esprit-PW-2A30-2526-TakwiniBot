-- ============================================================
-- phpMyAdmin SQL Dump — projet_takwini (v2)
-- Toutes les tables originales + entretien & type_entretien
-- mis à jour pour correspondre au formulaire MVC
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- Créer / sélectionner la base
-- ============================================================
CREATE DATABASE IF NOT EXISTS `projet_takwini`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `projet_takwini`;

-- ============================================================
-- Désactiver les FK le temps de recréer les tables
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE : categorie
-- ============================================================
DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id`  bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100)        NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : users
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`               varchar(100)        NOT NULL,
  `prenom`            varchar(100)        DEFAULT NULL,
  `email`             varchar(150)        NOT NULL,
  `mot_de_passe`      varchar(255)        NOT NULL,
  `role`              enum('candidat','admin','recruteur') NOT NULL DEFAULT 'candidat',
  `email_verifie`     tinyint(1)          DEFAULT 0,
  `statut`            enum('actif','inactif','suspendu')   NOT NULL DEFAULT 'actif',
  `date_creation`     timestamp           NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp           NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role`   (`role`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : profil
-- ============================================================
DROP TABLE IF EXISTS `profil`;
CREATE TABLE `profil` (
  `id`                bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           bigint(20) UNSIGNED NOT NULL,
  `competences`       text                DEFAULT NULL,
  `experience`        text                DEFAULT NULL,
  `handicap`          varchar(100)        DEFAULT NULL,
  `bio`               text                DEFAULT NULL,
  `date_creation`     timestamp           NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp           NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : formation
-- ============================================================
DROP TABLE IF EXISTS `formation`;
CREATE TABLE `formation` (
  `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre`         varchar(200)        NOT NULL,
  `duree`         varchar(50)         DEFAULT NULL,
  `prix`          decimal(10,2)       NOT NULL DEFAULT 0.00,
  `niveau`        varchar(100)        DEFAULT NULL,
  `description`   text                DEFAULT NULL,
  `date_creation` timestamp           NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_titre_formation` (`titre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : inscription
-- ============================================================
DROP TABLE IF EXISTS `inscription`;
CREATE TABLE `inscription` (
  `id`               bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          bigint(20) UNSIGNED NOT NULL,
  `formation_id`     bigint(20) UNSIGNED NOT NULL,
  `date_inscription` datetime            DEFAULT current_timestamp(),
  `statut`           enum('en_cours','terminee','abandonnee') DEFAULT 'en_cours',
  `progression`      tinyint(3) UNSIGNED DEFAULT 0 CHECK (`progression` BETWEEN 0 AND 100),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_formation` (`user_id`,`formation_id`),
  KEY `formation_id` (`formation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : certificat
-- ============================================================
DROP TABLE IF EXISTS `certificat`;
CREATE TABLE `certificat` (
  `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inscription_id`  bigint(20) UNSIGNED NOT NULL,
  `date_obtention`  date                DEFAULT NULL,
  `code_certificat` varchar(50)         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inscription_id`  (`inscription_id`),
  UNIQUE KEY `code_certificat` (`code_certificat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : offre
-- ============================================================
DROP TABLE IF EXISTS `offre`;
CREATE TABLE `offre` (
  `id`               bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre`            varchar(200)        NOT NULL,
  `description`      text                DEFAULT NULL,
  `type`             varchar(100)        DEFAULT NULL,
  `date_publication` date                DEFAULT (curdate()),
  PRIMARY KEY (`id`),
  KEY `idx_titre_offre` (`titre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : contrat
-- ============================================================
DROP TABLE IF EXISTS `contrat`;
CREATE TABLE `contrat` (
  `id`       bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offre_id` bigint(20) UNSIGNED NOT NULL,
  `type`     varchar(100)        DEFAULT NULL,
  `salaire`  decimal(10,2)       DEFAULT NULL,
  `duree`    varchar(50)         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offre_id` (`offre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : categorie produit (déjà définie) + produit
-- ============================================================
DROP TABLE IF EXISTS `produit`;
CREATE TABLE `produit` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `categorie_id` bigint(20) UNSIGNED NOT NULL,
  `nom`          varchar(150)        NOT NULL,
  `prix`         decimal(10,2)       NOT NULL,
  `stock`        int(10) UNSIGNED    DEFAULT 0,
  `description`  text                DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categorie` (`categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : commande
-- ============================================================
DROP TABLE IF EXISTS `commande`;
CREATE TABLE `commande` (
  `id`             bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        bigint(20) UNSIGNED NOT NULL,
  `date_commande`  datetime            DEFAULT current_timestamp(),
  `statut`         enum('en_attente','payee','expediee','livree','annulee') DEFAULT 'en_attente',
  `montant_total`  decimal(10,2)       DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_user_commande`   (`user_id`),
  KEY `idx_statut_commande` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : ligne_commande
-- ============================================================
DROP TABLE IF EXISTS `ligne_commande`;
CREATE TABLE `ligne_commande` (
  `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `commande_id`   bigint(20) UNSIGNED NOT NULL,
  `produit_id`    bigint(20) UNSIGNED NOT NULL,
  `quantite`      int(10) UNSIGNED    NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2)       NOT NULL,
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`),
  KEY `produit_id`  (`produit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : formulaire_reclamation
-- ============================================================
DROP TABLE IF EXISTS `formulaire_reclamation`;
CREATE TABLE `formulaire_reclamation` (
  `id`       bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`     varchar(100)        NOT NULL,
  `champs`   text                DEFAULT NULL,
  `est_actif` tinyint(1)         DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : reclamation
-- ============================================================
DROP TABLE IF EXISTS `reclamation`;
CREATE TABLE `reclamation` (
  `id`                bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           bigint(20) UNSIGNED NOT NULL,
  `formulaire_id`     bigint(20) UNSIGNED NOT NULL,
  `sujet`             varchar(200)        NOT NULL,
  `message`           text                NOT NULL,
  `statut`            enum('en_attente','en_cours','traite') DEFAULT 'en_attente',
  `date_creation`     timestamp           NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp           NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id`       (`user_id`),
  KEY `formulaire_id` (`formulaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : reponse
-- ============================================================
DROP TABLE IF EXISTS `reponse`;
CREATE TABLE `reponse` (
  `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reclamation_id`  bigint(20) UNSIGNED NOT NULL,
  `admin_id`        bigint(20) UNSIGNED NOT NULL,
  `contenu`         text                NOT NULL,
  `date_reponse`    timestamp           NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reclamation_id` (`reclamation_id`),
  KEY `admin_id`       (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : type_entretien  ← enrichie avec données réelles
-- ============================================================
DROP TABLE IF EXISTS `type_entretien`;
CREATE TABLE `type_entretien` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`      varchar(100)        NOT NULL,
  `duree_prevue` int(10) UNSIGNED    DEFAULT NULL COMMENT 'Durée en minutes',
  `description`  text                DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `type_entretien` (`libelle`, `duree_prevue`, `description`) VALUES
('présentiel',      60,  'Entretien en face à face dans les locaux de l\'entreprise.'),
('visioconférence', 45,  'Entretien à distance via un outil de vidéoconférence (Zoom, Teams, etc.).'),
('téléphonique',    30,  'Entretien rapide par appel téléphonique.'),
('LST',             60,  'Entretien en Langue des Signes Tunisienne avec interprète.'),
('hybride',         60,  'Combinaison présentiel et visioconférence selon les besoins du candidat.');

-- ============================================================
-- TABLE : entretien  ← reconstruite pour le formulaire MVC
-- ============================================================
DROP TABLE IF EXISTS `entretien`;
CREATE TABLE `entretien` (
  `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Informations candidat
  `nom_candidat`    varchar(100)        NOT NULL,
  `email_candidat`  varchar(150)        NOT NULL,
  `genre`           enum('homme','femme') NOT NULL DEFAULT 'homme',
  `type_handicap`   varchar(100)        NOT NULL,
  `amenagements`    text                DEFAULT NULL,

  -- Détails de l'entretien
  `type_entretien_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK → type_entretien.id',
  `date_entretien`  date                NOT NULL,
  `heure_entretien` time                NOT NULL,

  -- Poste & évaluation
  `poste_cible`     varchar(150)        NOT NULL,
  `metier_suggere`  varchar(150)        DEFAULT NULL,
  `score_rse`       tinyint(3) UNSIGNED DEFAULT NULL
                    CHECK (`score_rse` BETWEEN 1 AND 5),
  `remarques`       text                DEFAULT NULL,

  -- Statut & horodatage
  `statut`          enum('planifié','en cours','terminé','annulé')
                    NOT NULL DEFAULT 'planifié',
  `created_at`      datetime            NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),
  KEY `idx_type_entretien`  (`type_entretien_id`),
  KEY `idx_date_entretien`  (`date_entretien`),
  KEY `idx_statut_entretien`(`statut`),
  KEY `idx_email_candidat`  (`email_candidat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test
INSERT INTO `entretien`
  (nom_candidat, email_candidat, genre, type_handicap, amenagements,
   type_entretien_id, date_entretien, heure_entretien,
   poste_cible, metier_suggere, score_rse, remarques, statut)
VALUES
  ('Amina Trabelsi',   'amina.trabelsi@example.com',  'femme', 'moteur',    'Accès rampe + bureau réglable',           1, '2026-04-19', '09:30:00', 'Développeuse PHP',    'Intégratrice web',      5, 'Très bonne préparation, communication claire.',         'planifié'),
  ('Youssef Ben Ali',  'youssef.benali@example.com',  'homme', 'auditif',   'Interprète LST tunisienne',               4, '2026-04-16', '11:00:00', 'Support technique',   'Technicien helpdesk',   4, 'Nécessite un test pratique complémentaire.',            'en cours'),
  ('Meriem Gharbi',    'meriem.gharbi@example.com',   'femme', 'visuel',    'Lecteur d\'écran + documents accessibles', 2, '2026-04-22', '14:15:00', 'Chargée RH',          'Assistante RH',         3, 'Profil pertinent, à revoir avec manager RH.',           'planifié'),
  ('Nidhal Kacem',     'nidhal.kacem@example.com',    'homme', 'cognitif',  'Questions simplifiées, pauses courtes',   5, '2026-04-11', '10:00:00', 'Agent administratif', 'Employé administratif', 2, 'Accompagnement métier recommandé.',                     'terminé'),
  ('Sarra Hajji',      'sarra.hajji@example.com',     'femme', 'moteur',    'Temps supplémentaire pour déplacement',   1, '2026-04-13', '15:45:00', 'Comptable junior',    'Assistante comptable',  4, 'Bonne maîtrise des bases comptables.',                  'terminé'),
  ('Mohamed Lassoued', 'm.lassoued@example.com',      'homme', 'auditif',   'Sous-titrage en direct',                  2, '2026-04-18', '16:00:00', 'Commercial B2B',      'Conseiller client',     1, 'Entretien reporté suite à problème technique.',         'annulé'),
  ('Rim Chouchane',    'rim.chouchane@example.com',   'femme', 'psychique', 'Environnement calme, entretien structuré',3, '2026-04-17', '13:30:00', 'Community manager',   'Rédactrice web',        5, 'Excellente créativité et aisance orale.',               'en cours'),
  ('Walid Ayari',      'walid.ayari@example.com',     'homme', 'visuel',    'Description vocale des supports',         3, '2026-04-10', '08:45:00', 'Data entry',          'Opérateur de saisie',   3, 'Candidat motivé, formation initiale à prévoir.',        'terminé');

-- ============================================================
-- Réactiver les FK et ajouter les contraintes
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE `profil`
  ADD CONSTRAINT `profil_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1`
    FOREIGN KEY (`user_id`)      REFERENCES `users`    (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscription_ibfk_2`
    FOREIGN KEY (`formation_id`) REFERENCES `formation`(`id`) ON DELETE CASCADE;

ALTER TABLE `certificat`
  ADD CONSTRAINT `certificat_ibfk_1`
    FOREIGN KEY (`inscription_id`) REFERENCES `inscription`(`id`) ON DELETE CASCADE;

ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1`
    FOREIGN KEY (`offre_id`) REFERENCES `offre`(`id`) ON DELETE CASCADE;

ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1`
    FOREIGN KEY (`categorie_id`) REFERENCES `categorie`(`id`);

ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;

ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `ligne_commande_ibfk_1`
    FOREIGN KEY (`commande_id`) REFERENCES `commande`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ligne_commande_ibfk_2`
    FOREIGN KEY (`produit_id`)  REFERENCES `produit` (`id`);

ALTER TABLE `reclamation`
  ADD CONSTRAINT `reclamation_ibfk_1`
    FOREIGN KEY (`user_id`)       REFERENCES `users`                  (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reclamation_ibfk_2`
    FOREIGN KEY (`formulaire_id`) REFERENCES `formulaire_reclamation` (`id`);

ALTER TABLE `reponse`
  ADD CONSTRAINT `reponse_ibfk_1`
    FOREIGN KEY (`reclamation_id`) REFERENCES `reclamation`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reponse_ibfk_2`
    FOREIGN KEY (`admin_id`)       REFERENCES `users`       (`id`);

ALTER TABLE `entretien`
  ADD CONSTRAINT `entretien_ibfk_type`
    FOREIGN KEY (`type_entretien_id`) REFERENCES `type_entretien`(`id`);

COMMIT;
