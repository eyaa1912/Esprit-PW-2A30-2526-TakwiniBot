<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Offre.php';

class OffreController
{
    public function showOffre(Offre $offre): void
    {
        echo "Titre : "            . $offre->getTitre()           . "<br>";
        echo "Description : "      . $offre->getDescription()     . "<br>";
        echo "Type : "             . $offre->getType()            . "<br>";
        echo "Date publication : " . $offre->getDatePublication() . "<br>";
        echo "Image : "            . $offre->getImage()           . "<br>";
    }

    public function listOffres(): mixed
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM offre ORDER BY dateExpiration DESC');
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
                'INSERT INTO offre (titre, description, type, dateExpiration, image)
                 VALUES (:titre, :description, :type, :datePublication, :image)'
            );
            $req->execute([
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'type'            => $offre->getType(),
                'datePublication' => $offre->getDatePublication(),
                'image'           => $offre->getImage(),
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

    public function deleteExpiredOffres(): void
    {
        $db = config::getConnexion();
        try {
            $db->exec("DELETE FROM offre WHERE dateExpiration < CURDATE()");
        } catch (Exception $e) {
            // Silencieux — ne pas bloquer la page
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
                    dateExpiration  = :datePublication,
                    image           = :image
                 WHERE id = :id'
            );
            $req->execute([
                'id'              => $id,
                'titre'           => $offre->getTitre(),
                'description'     => $offre->getDescription(),
                'type'            => $offre->getType(),
                'datePublication' => $offre->getDatePublication(),
                'image'           => $offre->getImage(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>
