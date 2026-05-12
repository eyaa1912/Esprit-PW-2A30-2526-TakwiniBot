<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Utilisateur.php';

class UtilisateurController
{
    public function login(string $email, string $password): array
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE email = :email');
            $req->execute(['email' => $email]);
            $user = $req->fetch();
            if (!$user) {
                return ['success'=>false,'action'=>'not_found','message'=>'Aucun compte trouve avec cet email.','user'=>null];
            }
            if (!password_verify($password, $user['mot_de_passe'])) {
                return ['success'=>false,'action'=>'wrong_password','message'=>'Mot de passe incorrect.','user'=>null];
            }
            if ($user['statut'] === 'en_attente') {
                return ['success'=>false,'action'=>'en_attente','message'=>'Votre compte est en attente de validation par un administrateur.','user'=>null];
            }
            if ($user['statut'] === 'suspendu') {
                return ['success'=>false,'action'=>'suspendu','message'=>'Votre compte a ete suspendu.','user'=>null];
            }
            $db->prepare('UPDATE users SET statut = :s WHERE id = :id')->execute(['s'=>'actif','id'=>$user['id']]);
            return ['success'=>true,'action'=>'logged_in','message'=>'Connexion reussie !','user'=>['id'=>$user['id'],'nom'=>$user['nom'],'email'=>$user['email'],'role'=>$user['role'],'avatar'=>$user['avatar']??null]];
        } catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }
    }

    public function register(string $nom, string $prenom, string $email, string $password, string $telephone='', string $sexe='', string $date_naissance='', string $adresse='', int $handicap=0, ?string $type_handicap=null, ?string $face_descriptor=null): array
    {
        $db = config::getConnexion();
        try {
            $check = $db->prepare('SELECT * FROM users WHERE email = :email');
            $check->execute(['email' => $email]);
            if ($check->fetch()) {
                return ['success'=>false,'action'=>'already_exists','message'=>'Cet email est deja utilise.','user'=>null];
            }
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req = $db->prepare('INSERT INTO users (nom,prenom,email,mot_de_passe,telephone,date_naissance,adresse,sexe,role,email_verifie,statut,handicap,type_handicap) VALUES (:nom,:prenom,:email,:mdp,:telephone,:dob,:adresse,:sexe,:role,:ev,:statut,:handicap,:type_handicap)');
            $req->execute(['nom'=>$nom,'prenom'=>$prenom?:null,'email'=>$email,'mdp'=>$hashed,'telephone'=>$telephone?:null,'dob'=>$date_naissance?:null,'adresse'=>$adresse?:null,'sexe'=>$sexe?:null,'role'=>'candidat','ev'=>0,'statut'=>'actif','handicap'=>$handicap,'type_handicap'=>$type_handicap]);
            $newId = $db->lastInsertId();

            if (!empty($face_descriptor)) {
                $db->prepare('INSERT INTO face_descriptors (user_id, descriptor) VALUES (:uid, :desc)')
                   ->execute(['uid' => $newId, 'desc' => $face_descriptor]);
            }

            return ['success'=>true,'action'=>'registered','message'=>'Compte cree avec succes !','user'=>['id'=>$newId,'nom'=>$nom,'email'=>$email,'role'=>'candidat','avatar'=>null]];
        } catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }
    }

    public function getAll(): array
    {
        $db = config::getConnexion();
        try {
            return $db->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
        } catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }
    }

    public function getById(int $id): mixed
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }
    }

    public function updateUser(int $id, string $nom, string $email, string $password): array
    {
        $db = config::getConnexion();
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $req = $db->prepare('UPDATE users SET nom=:nom,email=:email,mot_de_passe=:password WHERE id=:id');
            $req->execute(['id'=>$id,'nom'=>$nom,'email'=>$email,'password'=>$hashed]);
            return ['success'=>true,'message'=>'Utilisateur mis a jour avec succes.'];
        } catch (Exception $e) { return ['success'=>false,'message'=>'Erreur : '.$e->getMessage()]; }
    }

    public function deleteUser(int $id): array
    {
        $db = config::getConnexion();
        try {
            $db->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
            return ['success'=>true,'message'=>'Utilisateur supprime avec succes.'];
        } catch (Exception $e) { return ['success'=>false,'message'=>'Erreur : '.$e->getMessage()]; }
    }
}

