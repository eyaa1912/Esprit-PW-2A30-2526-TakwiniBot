-- Renommer la table user en users et ajouter le champ prenom
RENAME TABLE `user` TO `users`;

ALTER TABLE `users`
  ADD COLUMN `prenom` varchar(100) NOT NULL DEFAULT '' AFTER `nom`;
