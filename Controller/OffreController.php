<?php

include __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Offre.php';

class OffreController
{
    public function showOffre(Offre $offre): void
    {
        echo "Titre : "            . $offre->getTitre()           . "<br>";
        echo "Description : "      . $offre->getDescription()     . "<br>";
        echo "Type : "             . $offre->getType()            . "<br>";
        echo "Date publication : " . $offre->getDatePublication() . "<br>";
    }

    public function listOffres(): mixed
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM offre');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addOffre(Offre $offre): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'INSERT INTO offre VALUES(NULL, :titre, :description, :type, :datePublication)'
            );
            $req->execute([
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'type'            => $offre->getType(),
                'datePublication' => $offre->getDatePublication(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteOffre(int $id): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM offre WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getOffre(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM offre WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateOffre(int $id, Offre $offre): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'UPDATE offre SET
                    titre           = :titre,
                    description     = :description,
                    type            = :type,
                    datePublication = :datePublication
                 WHERE id = :id'
            );
            $req->execute([
                'id'              => $id,
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'type'            => $offre->getType(),
                'datePublication' => $offre->getDatePublication(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>
