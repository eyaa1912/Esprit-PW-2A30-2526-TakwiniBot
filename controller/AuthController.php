<?php
require_once __DIR__ . '/../config.php';

class AuthController
{
    /**
     * Gère la connexion / inscription d'un utilisateur.
     *
     * - Si l'e-mail n'existe PAS  → on insère un nouvel enregistrement (statut 'actif')
     * - Si l'e-mail EXISTE déjà   → on met à jour statut = 'actif'
     *                               et on vérifie le mot de passe
     *
     * Retourne un tableau ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function loginOrRegister(string $nom, string $email, string $password): array
    {
        $db = config::getConnexion();

        // 1. Chercher l'utilisateur par e-mail
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // ── CAS 1 : Utilisateur INEXISTANT → inscription ────────────────────────
        if (!$user) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $insert = $db->prepare('
                INSERT INTO users (nom, email, mot_de_passe, role, email_verifie, statut)
                VALUES (:nom, :email, :mdp, :role, :ev, :statut)
            ');
            $insert->execute([
                'nom'    => $nom,
                'email'  => $email,
                'mdp'    => $hashedPassword,
                'role'   => 'candidat',
                'ev'     => 0,
                'statut' => 'actif',
            ]);

            $newId = $db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Compte créé avec succès ! Bienvenue ' . htmlspecialchars($nom) . '.',
                'action'  => 'registered',
                'user'    => ['id' => $newId, 'nom' => $nom, 'email' => $email, 'role' => 'candidat'],
            ];
        }

        // ── CAS 2 : Utilisateur EXISTANT → vérification du mot de passe ─────────
        if (!password_verify($password, $user['mot_de_passe'])) {
            return [
                'success' => false,
                'message' => 'Mot de passe incorrect.',
                'action'  => 'wrong_password',
                'user'    => null,
            ];
        }

        // Mettre le statut à 'actif' (au cas où il était 'inactif')
        $update = $db->prepare("UPDATE users SET statut = 'actif' WHERE id = :id");
        $update->execute(['id' => $user['id']]);

        return [
            'success' => true,
            'message' => 'Connexion réussie ! Bon retour, ' . htmlspecialchars($user['nom']) . '.',
            'action'  => 'logged_in',
            'user'    => [
                'id'    => $user['id'],
                'nom'   => $user['nom'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ];
    }

    /**
     * Déconnexion : met le statut de l'utilisateur à 'inactif'.
     */
    public function logout(int $userId): void
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE users SET statut = 'inactif' WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }
}
