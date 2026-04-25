<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Utilisateur.php';

class UtilisateurController {
    
    public function login(string $email, string $password): array {
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
                    'user'    => null
                ];
            }
            
            if (!password_verify($password, $user['mot_de_passe'])) {
                return [
                    'success' => false,
                    'action'  => 'wrong_password',
                    'message' => 'Mot de passe incorrect.',
                    'user'    => null
                ];
            }

            // Vérifier statut
            if ($user['statut'] === 'en_attente') {
                return [
                    'success' => false,
                    'action'  => 'en_attente',
                    'message' => 'Votre compte est en attente de validation par un administrateur.',
                    'user'    => null
                ];
            }

            if ($user['statut'] === 'suspendu') {
                return [
                    'success' => false,
                    'action'  => 'suspendu',
                    'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
                    'user'    => null
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
                    'avatar' => $user['avatar'] ?? null
                ]
            ];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function register(
        string $nom,
        string $prenom,
        string $email,
        string $password,
        string $telephone = '',
        string $sexe = '',
        string $date_naissance = '',
        string $adresse = '',
        int $handicap = 0,
        ?string $type_handicap = null
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
                    'user'    => null
                ];
            }
            
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req = $db->prepare('INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, date_naissance, adresse, sexe, role, email_verifie, statut, handicap, type_handicap) VALUES (:nom, :prenom, :email, :mdp, :telephone, :dob, :adresse, :sexe, :role, :ev, :statut, :handicap, :type_handicap)');
            $req->execute([
                'nom'           => $nom,
                'prenom'        => $prenom ?: null,
                'email'         => $email,
                'mdp'           => $hashed,
                'telephone'     => $telephone ?: null,
                'dob'           => $date_naissance ?: null,
                'adresse'       => $adresse ?: null,
                'sexe'          => $sexe ?: null,
                'role'          => 'candidat',
                'ev'            => 0,
                'statut'        => 'actif',
                'handicap'      => $handicap,
                'type_handicap' => $type_handicap
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
                    'avatar' => null
                ]
            ];
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    
    public function showUtilisateur(Utilisateur $utilisateur): void {
        echo "Nom : "             . $utilisateur->getNom()           . "<br>";
        echo "Prénom : "          . $utilisateur->getPrenom()        . "<br>";
        echo "Email : "           . $utilisateur->getEmail()         . "<br>";
        echo "Téléphone : "       . $utilisateur->getTelephone()     . "<br>";
        echo "Sexe : "            . $utilisateur->getSexe()          . "<br>";
        echo "Date naissance : "  . $utilisateur->getDateNaissance() . "<br>";
        echo "Adresse : "         . $utilisateur->getAdresse()       . "<br>";
        echo "Rôle : "            . $utilisateur->getRole()          . "<br>";
        echo "Statut : "          . $utilisateur->getStatut()        . "<br>";
    }

    public function listUtilisateurs(): mixed {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM utilisateur');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addUtilisateur(Utilisateur $utilisateur): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('INSERT INTO utilisateur VALUES(NULL, :nom, :prenom, :email, :mot_de_passe, :telephone, :sexe, :date_naissance, :adresse, :role, :statut, NULL)');
            $req->execute([
                'nom'             => $utilisateur->getNom(),
                'prenom'          => $utilisateur->getPrenom(),
                'email'           => $utilisateur->getEmail(),
                'mot_de_passe'    => $utilisateur->getMotDePasse(),
                'telephone'       => $utilisateur->getTelephone(),
                'sexe'            => $utilisateur->getSexe(),
                'date_naissance'  => $utilisateur->getDateNaissance(),
                'adresse'         => $utilisateur->getAdresse(),
                'role'            => $utilisateur->getRole(),
                'statut'          => $utilisateur->getStatut()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteUtilisateur(int $id): void {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM utilisateur WHERE id = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getUtilisateur(int $id): mixed {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM utilisateur WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getAll(): array {
        $db = config::getConnexion();
        try {
            $req = $db->query('SELECT * FROM users ORDER BY id DESC');
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getById(int $id): mixed {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateUser(int $id, string $nom, string $email, string $password): array {
        $db = config::getConnexion();
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req = $db->prepare('UPDATE users SET nom = :nom, email = :email, mot_de_passe = :password WHERE id = :id');
            $req->execute(['id' => $id, 'nom' => $nom, 'email' => $email, 'password' => $hashed]);
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function deleteUser(int $id): array {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
}
?>
