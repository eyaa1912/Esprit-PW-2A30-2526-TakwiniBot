<?php
<<<<<<< Updated upstream
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
=======
class Utilisateur
{
    //  PROPRIÉTÉS
    private string $nom;
    private string $prenom;
    private string $email;
    private string $mot_de_passe;
    private string $telephone;
    private string $sexe;
    private string $date_naissance;
    private string $adresse;
    private string $role;
    private string $statut;

   
    //  CONSTRUCTEUR
    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $mot_de_passe,
        string $telephone      = '',
        string $sexe           = '',
        string $date_naissance = '',
        string $adresse        = '',
        string $role           = 'candidat',
        string $statut         = 'inactif'
    ) {
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->mot_de_passe   = $mot_de_passe;
        $this->telephone      = $telephone;
        $this->sexe           = $sexe;
        $this->date_naissance = $date_naissance;
        $this->adresse        = $adresse;
        $this->role           = $role;
        $this->statut         = $statut;
    }

    //  GETTERS
    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasse(): string
    {
        return $this->mot_de_passe;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getSexe(): string
    {
        return $this->sexe;
    }

    public function getDateNaissance(): string
    {
        return $this->date_naissance;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }
    
    //  SETTERS
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setMotDePasse(string $mot_de_passe): void
    {
        $this->mot_de_passe = $mot_de_passe;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function setSexe(string $sexe): void
    {
        $this->sexe = $sexe;
    }

    public function setDateNaissance(string $date_naissance): void
    {
        $this->date_naissance = $date_naissance;
    }

    public function setAdresse(string $adresse): void
    {
        $this->adresse = $adresse;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    //  AFFICHAGE
    
    public function show(): void
    {
        echo "Nom : "              . $this->nom            . "<br>";
        echo "Prénom : "           . $this->prenom         . "<br>";
        echo "Email : "            . $this->email          . "<br>";
        echo "Téléphone : "        . $this->telephone      . "<br>";
        echo "Sexe : "             . $this->sexe           . "<br>";
        echo "Date de naissance : ". $this->date_naissance . "<br>";
        echo "Adresse : "          . $this->adresse        . "<br>";
        echo "Rôle : "             . $this->role           . "<br>";
        echo "Statut : "           . $this->statut         . "<br>";
>>>>>>> Stashed changes
    }
}
