<?php
require_once __DIR__ . '/../config.php';

class Utilisateur
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    /** Cherche un utilisateur par email */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /** Cherche un utilisateur par id */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /** Retourne tous les utilisateurs */
    public function findAll(): array
    {
        return $this->db->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
    }

    /** Insère un nouvel utilisateur, retourne l'id inséré */
    public function create(string $nom, string $email, string $hashedPassword): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO users (nom, email, mot_de_passe, role, email_verifie, statut)
            VALUES (:nom, :email, :mdp, :role, :ev, :statut)
        ');
        $stmt->execute([
            'nom'    => $nom,
            'email'  => $email,
            'mdp'    => $hashedPassword,
            'role'   => 'candidat',
            'ev'     => 0,
            'statut' => 'actif',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Met à jour le statut d'un utilisateur */
    public function updateStatut(int $id, string $statut): void
    {
        $stmt = $this->db->prepare('UPDATE users SET statut = :statut WHERE id = :id');
        $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    /** Met à jour nom, email, mot de passe */
    public function update(int $id, string $nom, string $email, string $hashedPassword): void
    {
        $stmt = $this->db->prepare('
            UPDATE users SET nom = :nom, email = :email, mot_de_passe = :mdp WHERE id = :id
        ');
        $stmt->execute(['nom' => $nom, 'email' => $email, 'mdp' => $hashedPassword, 'id' => $id]);
    }

    /** Supprime un utilisateur */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
