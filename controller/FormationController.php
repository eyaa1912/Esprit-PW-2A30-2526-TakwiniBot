<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Formation.php';

class FormationController
{
    // ─────────────────────────────────────────────
    //  AFFICHER UNE FORMATION
    // ─────────────────────────────────────────────
    public function showFormation(Formation $formation): void
    {
        echo "Titre : "       . $formation->getTitre()       . "<br>";
        echo "Durée : "       . $formation->getDuree()       . "<br>";
        echo "Prix : "        . $formation->getPrix()        . " TND<br>";
        echo "Niveau : "      . $formation->getNiveau()      . "<br>";
        echo "Description : " . $formation->getDescription() . "<br>";
    }

    // ─────────────────────────────────────────────
    //  LISTER TOUTES LES FORMATIONS
    // ─────────────────────────────────────────────
    public function listFormations(): array
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM formation');
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  AJOUTER UNE FORMATION
    // ─────────────────────────────────────────────
    public function addFormation(Formation $formation): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('INSERT INTO formation VALUES(NULL, :titre, :duree, :prix, :niveau, :description)');
            $req->execute([
                'titre'       => $formation->getTitre(),
                'duree'       => $formation->getDuree(),
                'prix'        => $formation->getPrix(),
                'niveau'      => $formation->getNiveau(),
                'description' => $formation->getDescription(),
            ]);

            return [
                'success' => true,
                'message' => 'Formation ajoutée avec succès.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────
    //  SUPPRIMER UNE FORMATION
    // ─────────────────────────────────────────────
    public function deleteFormation(int $id): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM formation WHERE id_formation = :id');
            $req->execute(['id' => $id]);

            return [
                'success' => true,
                'message' => 'Formation supprimée avec succès.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────
    //  RÉCUPÉRER UNE FORMATION PAR ID
    // ─────────────────────────────────────────────
    public function getFormation(int $id): array|false
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM formation WHERE id_formation = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  METTRE À JOUR UNE FORMATION
    // ─────────────────────────────────────────────
    public function updateFormation(int $id, Formation $formation): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('UPDATE formation SET 
                titre       = :titre,
                duree       = :duree,
                prix        = :prix,
                niveau      = :niveau,
                description = :description
                WHERE id_formation = :id');
            $req->execute([
                'id'          => $id,
                'titre'       => $formation->getTitre(),
                'duree'       => $formation->getDuree(),
                'prix'        => $formation->getPrix(),
                'niveau'      => $formation->getNiveau(),
                'description' => $formation->getDescription(),
            ]);

            return [
                'success' => true,
                'message' => 'Formation mise à jour avec succès.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ];
        }
    }
}

