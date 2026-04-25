<?php

include __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Contrat.php';

class ContratController
{
    public function showContrat(Contrat $contrat): void
    {
        echo "Offre ID : "      . $contrat->getOffreId()      . "<br>";
        echo "Salaire : "       . $contrat->getSalaire()      . "<br>";
        echo "Durée : "         . $contrat->getDuree()        . "<br>";
        echo "Date création : " . $contrat->getDateCreation() . "<br>";
        echo "Statut : "        . $contrat->getStatut()       . "<br>";
    }

    public function listContrats(): mixed
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query(
                'SELECT c.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM contrat c
                 LEFT JOIN offre o ON o.id = c.offre_id
                 ORDER BY c.id DESC'
            );
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addContrat(int $offreId, Contrat $contrat): array
    {
        $db = config::getConnexion();
        try {
            // Vérifier que l'offre existe
            $chk = $db->prepare('SELECT COUNT(*) FROM offre WHERE id = :id');
            $chk->execute(['id' => $offreId]);
            if ((int) $chk->fetchColumn() === 0) {
                return ['success' => false, 'message' => "L'offre ID $offreId n'existe pas."];
            }

            // Vérifier qu'aucun contrat n'existe déjà pour cette offre
            $chk2 = $db->prepare('SELECT COUNT(*) FROM contrat WHERE offre_id = :id');
            $chk2->execute(['id' => $offreId]);
            if ((int) $chk2->fetchColumn() > 0) {
                return ['success' => false, 'message' => "Un contrat existe déjà pour l'offre #$offreId."];
            }

            $req = $db->prepare(
                'INSERT INTO contrat (offre_id, salaire, duree, dateCreation, statut)
                 VALUES (:offre_id, :salaire, :duree, :dateCreation, :statut)'
            );
            $req->execute([
                'offre_id'     => $offreId,
                'salaire'      => $contrat->getSalaire(),
                'duree'        => $contrat->getDuree(),
                'dateCreation' => $contrat->getDateCreation(),
                'statut'       => $contrat->getStatut(),
            ]);
            return ['success' => true, 'id' => (int) $db->lastInsertId()];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteContrat(int $id): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM contrat WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getContrat(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'SELECT c.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM contrat c
                 LEFT JOIN offre o ON o.id = c.offre_id
                 WHERE c.id = :id'
            );
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateContrat(int $id, int $offreId, Contrat $contrat): array
    {
        $db = config::getConnexion();
        try {
            // Vérifier qu'un autre contrat n'existe pas déjà pour cette offre
            $chk = $db->prepare('SELECT COUNT(*) FROM contrat WHERE offre_id = :oid AND id != :id');
            $chk->execute(['oid' => $offreId, 'id' => $id]);
            if ((int) $chk->fetchColumn() > 0) {
                return ['success' => false, 'message' => "Un autre contrat existe déjà pour l'offre #$offreId."];
            }

            $req = $db->prepare(
                'UPDATE contrat SET
                    offre_id     = :offre_id,
                    salaire      = :salaire,
                    duree        = :duree,
                    dateCreation = :dateCreation,
                    statut       = :statut
                 WHERE id = :id'
            );
            $req->execute([
                'id'           => $id,
                'offre_id'     => $offreId,
                'salaire'      => $contrat->getSalaire(),
                'duree'        => $contrat->getDuree(),
                'dateCreation' => $contrat->getDateCreation(),
                'statut'       => $contrat->getStatut(),
            ]);
            return ['success' => true];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function countContrats(): int
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT COUNT(*) FROM contrat');
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getLatestContrats(int $limit = 4): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'SELECT c.*, o.titre AS offre_titre, o.type AS offre_type
                 FROM contrat c
                 LEFT JOIN offre o ON o.id = c.offre_id
                 ORDER BY c.id DESC
                 LIMIT :lim'
            );
            $req->bindValue(':lim', $limit, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function offresSansContrat(int $excludeId = 0): mixed
    {
        $db = config::getConnexion();
        try {
            if ($excludeId > 0) {
                $req = $db->prepare(
                    'SELECT o.id, o.titre, o.type FROM offre o
                     WHERE o.id NOT IN (SELECT offre_id FROM contrat WHERE id != :excl)
                     ORDER BY o.titre'
                );
                $req->execute(['excl' => $excludeId]);
            } else {
                $req = $db->prepare(
                    'SELECT o.id, o.titre, o.type FROM offre o
                     WHERE o.id NOT IN (SELECT offre_id FROM contrat)
                     ORDER BY o.titre'
                );
                $req->execute();
            }
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getPublicStats(): array
    {
        $db = config::getConnexion();
        try {
            $count  = $this->countContrats();
            $stmt   = $db->query('SELECT id, dateCreation FROM contrat ORDER BY id DESC LIMIT 1');
            $latest = $stmt->fetch() ?: null;

            $highlightNew = false;
            if ($latest && !empty($latest['dateCreation'])) {
                $ts = strtotime((string) $latest['dateCreation']);
                if ($ts) {
                    $highlightNew = (time() - $ts) < 172800;
                }
            }

            return [
                'count'         => $count,
                'latest_id'     => $latest ? (int) $latest['id'] : null,
                'highlight_new' => $highlightNew,
            ];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>
