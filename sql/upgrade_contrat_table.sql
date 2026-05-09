-- Mise à jour table `contrat` pour la gestion (dateCreation, statut).
-- Exécuter une fois dans phpMyAdmin ou : mysql -u root -p takwinibot < sql/upgrade_contrat_table.sql
-- Si les colonnes existent déjà, ignorez les erreurs « Duplicate column ».

ALTER TABLE `contrat`
  ADD COLUMN `dateCreation` DATE NULL AFTER `duree`,
  ADD COLUMN `statut` VARCHAR(20) NOT NULL DEFAULT 'actif' AFTER `dateCreation`;

UPDATE `contrat` SET `dateCreation` = CURDATE() WHERE `dateCreation` IS NULL;
