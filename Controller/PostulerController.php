<?php

include __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Postuler.php';

class PostulerController
{
    public function showPostuler(Postuler $postuler): void
    {
        echo "Nom : "              . $postuler->getNom()             . "<br>";
        echo "Prénom : "           . $postuler->getPrenom()          . "<br>";
        echo "Email : "            . $postuler->getEmail()           . "<br>";
        echo "Offre ID : "         . $postuler->getOffreId()         . "<br>";
        echo "CV : "               . $postuler->getCvPath()          . "<br>";
        echo "Statut : "           . $postuler->getStatut()          . "<br>";
        echo "Date postulation : " . $postuler->getDatePostulation() . "<br>";
    }

    public function listPostulers(): mixed
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query(
                'SELECT p.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM postuler p
                 LEFT JOIN offre o ON o.id = p.offre_id
                 ORDER BY p.datePostulation DESC'
            );
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addPostuler(Postuler $postuler): void
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
                'datePostulation' => $postuler->getDatePostulation() ?: date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deletePostuler(int $id): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM postuler WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getPostuler(int $id): mixed
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

    public function updateStatut(int $id, string $statut): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('UPDATE postuler SET statut = :statut WHERE id = :id');
            $req->execute(['statut' => $statut, 'id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

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
