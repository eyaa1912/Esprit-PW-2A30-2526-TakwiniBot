<?php
require_once __DIR__ . '/../Model/Utilisateur.php';

class UtilisateurController
{
    private Utilisateur $model;

    public function __construct()
    {
        $this->model = new Utilisateur();
    }

    // ─────────────────────────────────────────────
    //  LOGIN / REGISTER
    // ─────────────────────────────────────────────

    /**
     * CONNEXION : l'email doit exister + mot de passe correct
     */
    public function login(string $email, string $password): array
    {
        $user = $this->model->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'action'  => 'not_found',
                'message' => 'Aucun compte trouvé avec cet email. Veuillez vous inscrire.',
                'user'    => null,
            ];
        }

        if (!password_verify($password, $user['mot_de_passe'])) {
            return [
                'success' => false,
                'action'  => 'wrong_password',
                'message' => 'Mot de passe incorrect.',
                'user'    => null,
            ];
        }

        $this->model->updateStatut((int) $user['id'], 'actif');

        return [
            'success' => true,
            'action'  => 'logged_in',
            'message' => 'Connexion réussie ! Bon retour, ' . htmlspecialchars($user['nom']) . '.',
            'user'    => [
                'id'    => $user['id'],
                'nom'   => $user['nom'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ];
    }

    /**
     * INSCRIPTION : l'email ne doit pas déjà exister
     */
    public function register(string $nom, string $email, string $password): array
    {
        $existing = $this->model->findByEmail($email);

        if ($existing) {
            return [
                'success' => false,
                'action'  => 'already_exists',
                'message' => 'Cet email est déjà utilisé. Veuillez vous connecter.',
                'user'    => null,
            ];
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $newId  = $this->model->create($nom, $email, $hashed);

        return [
            'success' => true,
            'action'  => 'registered',
            'message' => 'Compte créé avec succès ! Bienvenue ' . htmlspecialchars($nom) . '.',
            'user'    => ['id' => $newId, 'nom' => $nom, 'email' => $email, 'role' => 'candidat'],
        ];
    }

    // ─────────────────────────────────────────────
    //  LOGOUT
    // ─────────────────────────────────────────────

    /** Passe le statut à 'inactif' */
    public function logout(int $userId): void
    {
        $this->model->updateStatut($userId, 'inactif');
    }

    // ─────────────────────────────────────────────
    //  CRUD
    // ─────────────────────────────────────────────

    public function getAll(): array
    {
        return $this->model->findAll();
    }

    public function getById(int $id): array|false
    {
        return $this->model->findById($id);
    }

    public function updateUser(int $id, string $nom, string $email, string $password): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $this->model->update($id, $nom, $email, $hashed);

        return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
    }

    public function deleteUser(int $id): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $this->model->delete($id);
        return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
    }
}
