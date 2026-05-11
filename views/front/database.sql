CREATE DATABASE IF NOT EXISTS takwinibot
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE takwinibot;

DROP TABLE IF EXISTS entretien;

CREATE TABLE entretien (
  id_entretien INT AUTO_INCREMENT PRIMARY KEY,
  nom_candidat VARCHAR(100) NOT NULL,
  email_candidat VARCHAR(150) NOT NULL,
  genre ENUM('homme','femme') NOT NULL,
  type_handicap VARCHAR(100) NOT NULL,
  amenagements TEXT NULL,
  type_entretien ENUM('présentiel','visioconférence','téléphonique','LST','hybride') NOT NULL,
  date_entretien DATE NOT NULL,
  heure_entretien TIME NOT NULL,
  poste_cible VARCHAR(150) NOT NULL,
  metier_suggere VARCHAR(150) NULL,
  score_rse TINYINT UNSIGNED NULL,
  remarques TEXT NULL,
  statut ENUM('planifié','en cours','terminé','annulé') NOT NULL DEFAULT 'planifié',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_score_rse CHECK (score_rse BETWEEN 1 AND 5)
) ENGINE=InnoDB;

INSERT INTO entretien (
  nom_candidat,
  email_candidat,
  genre,
  type_handicap,
  amenagements,
  type_entretien,
  date_entretien,
  heure_entretien,
  poste_cible,
  metier_suggere,
  score_rse,
  remarques,
  statut
) VALUES
('Amina Trabelsi', 'amina.trabelsi@example.com', 'femme', 'moteur', 'Accès rampe + bureau réglable', 'présentiel', '2026-04-19', '09:30:00', 'Développeuse PHP', 'Intégratrice web', 5, 'Très bonne préparation, communication claire.', 'planifié'),
('Youssef Ben Ali', 'youssef.benali@example.com', 'homme', 'auditif', 'Interprète LST tunisienne', 'LST', '2026-04-16', '11:00:00', 'Support technique', 'Technicien helpdesk', 4, 'Nécessite un test pratique complémentaire.', 'en cours'),
('Meriem Gharbi', 'meriem.gharbi@example.com', 'femme', 'visuel', 'Lecteur d’écran compatible + documents accessibles', 'visioconférence', '2026-04-22', '14:15:00', 'Chargée RH', 'Assistante RH', 3, 'Profil pertinent, à revoir avec manager RH.', 'planifié'),
('Nidhal Kacem', 'nidhal.kacem@example.com', 'homme', 'cognitif', 'Questions simplifiées, pauses courtes', 'hybride', '2026-04-11', '10:00:00', 'Agent administratif', 'Employé administratif', 2, 'Accompagnement métier recommandé.', 'terminé'),
('Sarra Hajji', 'sarra.hajji@example.com', 'femme', 'moteur', 'Temps supplémentaire pour déplacement', 'présentiel', '2026-04-13', '15:45:00', 'Comptable junior', 'Assistante comptable', 4, 'Bonne maîtrise des bases comptables.', 'terminé'),
('Mohamed Lassoued', 'm.lassoued@example.com', 'homme', 'auditif', 'Sous-titrage en direct', 'visioconférence', '2026-04-18', '16:00:00', 'Commercial B2B', 'Conseiller client', 1, 'Entretien reporté suite à problème technique.', 'annulé'),
('Rim Chouchane', 'rim.chouchane@example.com', 'femme', 'psychique', 'Environnement calme, entretien structuré', 'téléphonique', '2026-04-17', '13:30:00', 'Community manager', 'Rédactrice web', 5, 'Excellente créativité et aisance orale.', 'en cours'),
('Walid Ayari', 'walid.ayari@example.com', 'homme', 'visuel', 'Description vocale des supports', 'téléphonique', '2026-04-10', '08:45:00', 'Data entry', 'Opérateur de saisie', 3, 'Candidat motivé, formation initiale à prévoir.', 'terminé');
