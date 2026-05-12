<?php
/**
 * controller/InscriptionController.php
 * CRUD inscriptions — utilise takwini_db.inscription
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Inscription.php';

class InscriptionController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    /** Retourne toutes les inscriptions */
    public function listInscriptions(): array
    {
        return $this->db
            ->query('SELECT i.*, f.titre AS formation_titre
                     FROM inscription i
                     LEFT JOIN formation f ON f.id = i.formation_id
                     ORDER BY i.created_at DESC')
            ->fetchAll();
    }

    /** Retourne une inscription par son id */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, f.titre AS formation_titre
             FROM inscription i
             LEFT JOIN formation f ON f.id = i.formation_id
             WHERE i.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Ajoute une inscription (depuis le front office) */
    public function addInscription(Inscription $insc): array
    {
        // Vérifier doublon
        $chk = $this->db->prepare(
            'SELECT id FROM inscription WHERE user_id=:u AND formation_id=:f'
        );
        $chk->execute(['u' => $insc->getUserId(), 'f' => $insc->getFormationId()]);
        if ($chk->fetch()) {
            return ['success' => false, 'message' => 'Vous êtes déjà inscrit à cette formation.'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO inscription (user_id, formation_id, nom, prenom, email, niveau, mode_formation)
             VALUES (:u, :f, :n, :p, :e, :nv, :m)'
        );
        $stmt->execute([
            'u'  => $insc->getUserId(),
            'f'  => $insc->getFormationId(),
            'n'  => $insc->getNom(),
            'p'  => $insc->getPrenom(),
            'e'  => $insc->getEmail(),
            'nv' => $insc->getNiveau(),
            'm'  => $insc->getModeFormation(),
        ]);

        return ['success' => true, 'message' => 'Inscription réussie !', 'id' => (int)$this->db->lastInsertId()];
    }

    /** Met à jour une inscription (admin seulement) */
    public function updateInscription(int $id, Inscription $insc): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inscription
             SET nom=:n, prenom=:p, email=:e, niveau=:nv, mode_formation=:m
             WHERE id=:id'
        );
        return $stmt->execute([
            'n'  => $insc->getNom(),
            'p'  => $insc->getPrenom(),
            'e'  => $insc->getEmail(),
            'nv' => $insc->getNiveau(),
            'm'  => $insc->getModeFormation(),
            'id' => $id,
        ]);
    }

    /** Supprime une inscription (admin seulement) */
    public function deleteInscription(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM inscription WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
