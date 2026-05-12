<?php
/**
 * controller/FormationController.php
 * CRUD formations — utilise takwini_db.formation
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Formation.php';

class FormationController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    /** Retourne toutes les formations */
    public function listFormations(): array
    {
        return $this->db
            ->query('SELECT * FROM formation ORDER BY date_creation DESC')
            ->fetchAll();
    }

    /** Retourne une formation par son id */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM formation WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Ajoute une formation */
    public function addFormation(Formation $f): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO formation (titre, duree, prix, niveau, description)
             VALUES (:titre, :duree, :prix, :niveau, :description)'
        );
        $stmt->execute([
            'titre'       => $f->getTitre(),
            'duree'       => $f->getDuree(),
            'prix'        => $f->getPrix(),
            'niveau'      => $f->getNiveau(),
            'description' => $f->getDescription(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Met à jour une formation */
    public function updateFormation(int $id, Formation $f): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE formation
             SET titre=:titre, duree=:duree, prix=:prix, niveau=:niveau, description=:description
             WHERE id=:id'
        );
        return $stmt->execute([
            'titre'       => $f->getTitre(),
            'duree'       => $f->getDuree(),
            'prix'        => $f->getPrix(),
            'niveau'      => $f->getNiveau(),
            'description' => $f->getDescription(),
            'id'          => $id,
        ]);
    }

    /** Supprime une formation */
    public function deleteFormation(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM formation WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
