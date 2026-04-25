<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Postuler.php';

class PostulerController
{
    // Toutes les candidatures avec jointure offre
    public function getAllCandidatures(): mixed
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->query(
                'SELECT p.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM postuler p
                 LEFT JOIN offre o ON o.id = p.offre_id
                 ORDER BY p.datePostulation DESC'
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Une candidature par id
    public function getCandidature(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'SELECT p.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM postuler p
                 LEFT JOIN offre o ON o.id = p.offre_id
                 WHERE p.id = :id'
            );
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Soumettre une candidature
    public function createCandidature(Postuler $postuler): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'INSERT INTO postuler (nom, prenom, email, offre_id, cv_path, statut, datePostulation)
                 VALUES (:nom, :prenom, :email, :offre_id, :cv_path, :statut, :datePostulation)'
            );
            $req->execute([
                'nom'             => $postuler->getNom(),
                'prenom'          => $postuler->getPrenom(),
                'email'           => $postuler->getEmail(),
                'offre_id'        => $postuler->getOffreId(),
                'cv_path'         => $postuler->getCvPath(),
                'statut'          => $postuler->getStatut(),
                'datePostulation' => $postuler->getDatePostulation(),
            ]);
            return ['success' => true, 'id' => (int) $db->lastInsertId()];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Changer le statut (admin)
    public function updateStatut(int $id, string $statut): void
    {
        $db = config::getConnexion();
        try {
            $allowed = ['en_attente', 'acceptee', 'refusee'];
            if (!in_array($statut, $allowed)) return;
            $req = $db->prepare('UPDATE postuler SET statut = :statut WHERE id = :id');
            $req->execute(['statut' => $statut, 'id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Supprimer une candidature
    public function deleteCandidature(int $id): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM postuler WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Stats par statut
    public function countByStatut(): array
    {
        $db = config::getConnexion();
        try {
            $stmt   = $db->query("SELECT statut, COUNT(*) AS total FROM postuler GROUP BY statut");
            $rows   = $stmt->fetchAll();
            $counts = ['en_attente' => 0, 'acceptee' => 0, 'refusee' => 0, 'total' => 0];
            foreach ($rows as $row) {
                $counts[$row['statut']] = (int) $row['total'];
                $counts['total']       += (int) $row['total'];
            }
            return $counts;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>
