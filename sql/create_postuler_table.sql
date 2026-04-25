CREATE TABLE IF NOT EXISTS `postuler` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`         INT(11)      NOT NULL,
  `offre_id`        INT(11)      NOT NULL,
  `cv_path`         VARCHAR(255) NOT NULL,
  `statut`          ENUM('en_attente','acceptée','refusée') NOT NULL DEFAULT 'en_attente',
  `datePostulation` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`)  REFERENCES `user`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`offre_id`) REFERENCES `offre`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
