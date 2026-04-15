<?php
include __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Formation.php';

class FormationController {

    public function showFormation(Formation $formation): void {
        echo "Titre : "       . $formation->getTitre()       . "<br>";
        echo "Durée : "       . $formation->getDuree()       . "<br>";
        echo "Prix : "        . $formation->getPrix()        . " TND<br>";
        echo "Niveau : "      . $formation->getNiveau()      . "<br>";
        echo "Description : " . $formation->getDescription() . "<br>";
    }

    public function listFormations(): mixed {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM formation');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addFormation(Formation $formation): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                INSERT INTO formation (titre, duree, prix, niveau, description)
                VALUES (:titre, :duree, :prix, :niveau, :description)
            ');
            $req->execute([
                'titre'       => $formation->getTitre(),
                'duree'       => $formation->getDuree(),
                'prix'        => $formation->getPrix(),
                'niveau'      => $formation->getNiveau(),
                'description' => $formation->getDescription()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteFormation(int $id): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM formation WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getFormation(int $id): mixed {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM formation WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateFormation(int $id, Formation $formation): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                UPDATE formation SET
                    titre       = :titre,
                    duree       = :duree,
                    prix        = :prix,
                    niveau      = :niveau,
                    description = :description
                WHERE id = :id
            ');
            $req->execute([
                'id'          => $id,
                'titre'       => $formation->getTitre(),
                'duree'       => $formation->getDuree(),
                'prix'        => $formation->getPrix(),
                'niveau'      => $formation->getNiveau(),
                'description' => $formation->getDescription()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>
