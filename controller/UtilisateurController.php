<?php
require_once __DIR__ . '/../Model/Utilisateur.php';

class UtilisateurController
{
<<<<<<< Updated upstream
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
=======
    // ─────────────────────────────────────────────
    //  CONNEXION
    // ─────────────────────────────────────────────
    public function login(string $email, string $password): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE email = :email');
            $req->execute(['email' => $email]);
            $user = $req->fetch();

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

            if ($user['statut'] === 'en_attente') {
                return [
                    'success' => false,
                    'action'  => 'en_attente',
                    'message' => 'Votre compte est en attente de validation par un administrateur.',
                    'user'    => null,
                ];
            }

            if ($user['statut'] === 'suspendu') {
                return [
                    'success' => false,
                    'action'  => 'suspendu',
                    'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
                    'user'    => null,
                ];
            }

            $updateReq = $db->prepare('UPDATE users SET statut = :statut WHERE id = :id');
            $updateReq->execute(['statut' => 'actif', 'id' => $user['id']]);

            return [
                'success' => true,
                'action'  => 'logged_in',
                'message' => 'Connexion réussie ! Bon retour, ' . htmlspecialchars($user['nom']) . '.',
                'user'    => [
                    'id'     => $user['id'],
                    'nom'    => $user['nom'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'avatar' => $user['avatar'] ?? null,
                ],
>>>>>>> Stashed changes
            ];
        }

<<<<<<< Updated upstream
        if (!password_verify($password, $user['mot_de_passe'])) {
            return [
                'success' => false,
                'action'  => 'wrong_password',
                'message' => 'Mot de passe incorrect.',
                'user'    => null,
=======
    // ─────────────────────────────────────────────
    //  INSCRIPTION
    // ─────────────────────────────────────────────
    public function register(
        string  $nom,
        string  $prenom,
        string  $email,
        string  $password,
        string  $telephone      = '',
        string  $sexe           = '',
        string  $date_naissance = '',
        string  $adresse        = '',
        int     $handicap       = 0,
        ?string $type_handicap  = null
    ): array {
        $db = config::getConnexion();
        try {
            $checkReq = $db->prepare('SELECT * FROM users WHERE email = :email');
            $checkReq->execute(['email' => $email]);
            $existing = $checkReq->fetch();

            if ($existing) {
                return [
                    'success' => false,
                    'action'  => 'already_exists',
                    'message' => 'Cet email est déjà utilisé. Veuillez vous connecter.',
                    'user'    => null,
                ];
            }

            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req    = $db->prepare(
                'INSERT INTO users
                    (nom, prenom, email, mot_de_passe, telephone, date_naissance, adresse, sexe, role, email_verifie, statut, handicap, type_handicap)
                 VALUES
                    (:nom, :prenom, :email, :mdp, :telephone, :dob, :adresse, :sexe, :role, :ev, :statut, :handicap, :type_handicap)'
            );
            $req->execute([
                'nom'           => $nom,
                'prenom'        => $prenom        ?: null,
                'email'         => $email,
                'mdp'           => $hashed,
                'telephone'     => $telephone     ?: null,
                'dob'           => $date_naissance ?: null,
                'adresse'       => $adresse       ?: null,
                'sexe'          => $sexe          ?: null,
                'role'          => 'candidat',
                'ev'            => 0,
                'statut'        => 'actif',
                'handicap'      => $handicap,
                'type_handicap' => $type_handicap,
            ]);

            $newId = $db->lastInsertId();

            return [
                'success' => true,
                'action'  => 'registered',
                'message' => 'Compte créé avec succès ! Bienvenue ' . htmlspecialchars($prenom ?: $nom) . '.',
                'user'    => [
                    'id'     => $newId,
                    'nom'    => $nom,
                    'email'  => $email,
                    'role'   => 'candidat',
                    'avatar' => null,
                ],
>>>>>>> Stashed changes
            ];
        }
<<<<<<< Updated upstream

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
=======
    }

    // ─────────────────────────────────────────────
    //  AFFICHER UN UTILISATEUR
    // ─────────────────────────────────────────────
    public function showUtilisateur(Utilisateur $utilisateur): void
    {
        echo "Nom : "            . $utilisateur->getNom()           . "<br>";
        echo "Prénom : "         . $utilisateur->getPrenom()        . "<br>";
        echo "Email : "          . $utilisateur->getEmail()         . "<br>";
        echo "Téléphone : "      . $utilisateur->getTelephone()     . "<br>";
        echo "Sexe : "           . $utilisateur->getSexe()          . "<br>";
        echo "Date naissance : " . $utilisateur->getDateNaissance() . "<br>";
        echo "Adresse : "        . $utilisateur->getAdresse()       . "<br>";
        echo "Rôle : "           . $utilisateur->getRole()          . "<br>";
        echo "Statut : "         . $utilisateur->getStatut()        . "<br>";
    }

    // ─────────────────────────────────────────────
    //  LISTER TOUS LES UTILISATEURS
    // ─────────────────────────────────────────────
    public function getAll(): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->query('SELECT * FROM users ORDER BY id DESC');
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
>>>>>>> Stashed changes
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $this->model->update($id, $nom, $email, $hashed);

        return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
    }

<<<<<<< Updated upstream
    public function deleteUser(int $id): array
    {
        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
=======
    // ─────────────────────────────────────────────
    //  RÉCUPÉRER UN UTILISATEUR PAR ID
    // ─────────────────────────────────────────────
    public function getById(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
>>>>>>> Stashed changes
        }

<<<<<<< Updated upstream
        $this->model->delete($id);
        return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
=======
    // ─────────────────────────────────────────────
    //  METTRE À JOUR UN UTILISATEUR
    // ─────────────────────────────────────────────
    public function updateUser(int $id, string $nom, string $email, string $password): array
    {
        $db = config::getConnexion();
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req    = $db->prepare(
                'UPDATE users SET
                    nom         = :nom,
                    email       = :email,
                    mot_de_passe = :password
                 WHERE id = :id'
            );
            $req->execute([
                'id'       => $id,
                'nom'      => $nom,
                'email'    => $email,
                'password' => $hashed,
            ]);
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────
    //  SUPPRIMER UN UTILISATEUR
    // ─────────────────────────────────────────────
    public function deleteUser(int $id): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
>>>>>>> Stashed changes
    }

    // ─────────────────────────────────────────────
    //  AJOUTER UN UTILISATEUR (legacy)
    // ─────────────────────────────────────────────
    public function addUtilisateur(Utilisateur $utilisateur): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'INSERT INTO utilisateur
                    VALUES(NULL, :nom, :prenom, :email, :mot_de_passe, :telephone, :sexe, :date_naissance, :adresse, :role, :statut, NULL)'
            );
            $req->execute([
                'nom'            => $utilisateur->getNom(),
                'prenom'         => $utilisateur->getPrenom(),
                'email'          => $utilisateur->getEmail(),
                'mot_de_passe'   => $utilisateur->getMotDePasse(),
                'telephone'      => $utilisateur->getTelephone(),
                'sexe'           => $utilisateur->getSexe(),
                'date_naissance' => $utilisateur->getDateNaissance(),
                'adresse'        => $utilisateur->getAdresse(),
                'role'           => $utilisateur->getRole(),
                'statut'         => $utilisateur->getStatut(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  SUPPRIMER UN UTILISATEUR (legacy)
    // ─────────────────────────────────────────────
    public function deleteUtilisateur(int $id): void
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM utilisateur WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  RÉCUPÉRER UN UTILISATEUR (legacy)
    // ─────────────────────────────────────────────
    public function getUtilisateur(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM utilisateur WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
