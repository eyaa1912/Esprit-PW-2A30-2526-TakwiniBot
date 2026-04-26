<?php
declare(strict_types=1);

include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Entretien.php';
require_once __DIR__ . '/../Model/TypeEntretien.php';

class EntretienController {
    public function listEntretiens(): mixed {
        $db = config::getConnexion();

        try {
            $sql = 'SELECT e.*, te.libelle AS type_entretien_libelle, te.duree_prevue AS type_entretien_duree_prevue, te.description AS type_entretien_description
                    FROM entretien e
                    LEFT JOIN type_entretien te ON te.id_type_entretien = e.type_entretien_id
                    ORDER BY e.date_entretien DESC, e.heure_entretien DESC, e.id_entretien DESC';

            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addEntretien(Entretien $e): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('INSERT INTO entretien (
                nom_candidat,
                email_candidat,
                genre,
                type_handicap,
                amenagements,
                type_entretien_id,
                date_entretien,
                heure_entretien,
                poste_cible,
                metier_suggere,
                score_rse,
                remarques,
                statut,
                created_at,
                status,
                id_type_entretien
            ) VALUES (
                :nom_candidat,
                :email_candidat,
                :genre,
                :type_handicap,
                :amenagements,
                :type_entretien_id,
                :date_entretien,
                :heure_entretien,
                :poste_cible,
                :metier_suggere,
                :score_rse,
                :remarques,
                :statut,
                NOW(),
                :status,
                :id_type_entretien
            )');

            $stmt->execute([
                'nom_candidat' => $e->getNomCandidat(),
                'email_candidat' => $e->getEmailCandidat(),
                'genre' => $e->getGenre(),
                'type_handicap' => $e->getTypeHandicap(),
                'amenagements' => $e->getAmenagements(),
                'type_entretien_id' => $e->getTypeEntretienId(),
                'date_entretien' => $e->getDateEntretien(),
                'heure_entretien' => $e->getHeureEntretien(),
                'poste_cible' => $e->getPosteCible(),
                'metier_suggere' => $e->getMetierSuggere(),
                'score_rse' => $e->getScoreRse(),
                'remarques' => $e->getRemarques(),
                'statut' => $e->getStatut(),
                'status' => 'actif',
                'id_type_entretien' => $e->getTypeEntretienId(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteEntretien(int $id): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('DELETE FROM entretien WHERE id_entretien = :id');
            $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getEntretien(int $id): mixed {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('SELECT e.*, te.libelle AS type_entretien_libelle, te.duree_prevue AS type_entretien_duree_prevue, te.description AS type_entretien_description
                                  FROM entretien e
                                  LEFT JOIN type_entretien te ON te.id_type_entretien = e.type_entretien_id
                                  WHERE e.id_entretien = :id');
            $stmt->execute(['id' => $id]);

            return $stmt->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateEntretien(int $id, Entretien $e): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('UPDATE entretien SET
                nom_candidat = :nom_candidat,
                email_candidat = :email_candidat,
                genre = :genre,
                type_handicap = :type_handicap,
                amenagements = :amenagements,
                type_entretien_id = :type_entretien_id,
                date_entretien = :date_entretien,
                heure_entretien = :heure_entretien,
                poste_cible = :poste_cible,
                metier_suggere = :metier_suggere,
                score_rse = :score_rse,
                remarques = :remarques,
                statut = :statut,
                id_type_entretien = :id_type_entretien
                WHERE id_entretien = :id');

            $stmt->execute([
                'id' => $id,
                'nom_candidat' => $e->getNomCandidat(),
                'email_candidat' => $e->getEmailCandidat(),
                'genre' => $e->getGenre(),
                'type_handicap' => $e->getTypeHandicap(),
                'amenagements' => $e->getAmenagements(),
                'type_entretien_id' => $e->getTypeEntretienId(),
                'date_entretien' => $e->getDateEntretien(),
                'heure_entretien' => $e->getHeureEntretien(),
                'poste_cible' => $e->getPosteCible(),
                'metier_suggere' => $e->getMetierSuggere(),
                'score_rse' => $e->getScoreRse(),
                'remarques' => $e->getRemarques(),
                'statut' => $e->getStatut(),
                'id_type_entretien' => $e->getTypeEntretienId(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function listTypeEntretiens(): mixed {
        $db = config::getConnexion();

        try {
            return $db->query('SELECT * FROM type_entretien ORDER BY libelle ASC');
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
