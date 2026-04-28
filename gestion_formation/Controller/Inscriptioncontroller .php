<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Inscription.php';

class InscriptionController {

    public function showInscription(Inscription $inscription): void {
        echo "User ID : "    . $inscription->getUserId()        . "<br>";
        echo "Formation ID : " . $inscription->getFormationId() . "<br>";
        echo "Nom : "        . $inscription->getNom()           . "<br>";
        echo "Prénom : "     . $inscription->getPrenom()        . "<br>";
        echo "Email : "      . $inscription->getEmail()         . "<br>";
        echo "Niveau : "     . ($inscription->getNiveau() ?? 'Non spécifié') . "<br>";
        echo "Mode : "       . ($inscription->getModeFormation() ?? 'Non spécifié') . "<br>";
    }

    public function listInscriptions(): mixed {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM inscription');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addInscription(Inscription $inscription): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                INSERT INTO inscription (user_id, formation_id, nom, prenom, email, niveau, mode_formation)
                VALUES (:user_id, :formation_id, :nom, :prenom, :email, :niveau, :mode_formation)
            ');
            $req->execute([
                'user_id'        => $inscription->getUserId(),
                'formation_id'   => $inscription->getFormationId(),
                'nom'            => $inscription->getNom(),
                'prenom'         => $inscription->getPrenom(),
                'email'          => $inscription->getEmail(),
                'niveau'         => $inscription->getNiveau(),
                'mode_formation' => $inscription->getModeFormation(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteInscription(int $id): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM inscription WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getInscription(int $id): mixed {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM inscription WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateInscription(int $id, Inscription $inscription): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                UPDATE inscription SET
                    user_id        = :user_id,
                    formation_id   = :formation_id,
                    nom            = :nom,
                    prenom         = :prenom,
                    email          = :email,
                    niveau         = :niveau,
                    mode_formation = :mode_formation
                WHERE id = :id
            ');
            $req->execute([
                'id'             => $id,
                'user_id'        => $inscription->getUserId(),
                'formation_id'   => $inscription->getFormationId(),
                'nom'            => $inscription->getNom(),
                'prenom'         => $inscription->getPrenom(),
                'email'          => $inscription->getEmail(),
                'niveau'         => $inscription->getNiveau(),
                'mode_formation' => $inscription->getModeFormation(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>