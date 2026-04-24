<?php
class Utilisateur {
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

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $mot_de_passe,
        string $telephone = '',
        string $sexe = '',
        string $date_naissance = '',
        string $adresse = '',
        string $role = 'candidat',
        string $statut = 'inactif'
    ) {
        $this->nom             = $nom;
        $this->prenom          = $prenom;
        $this->email           = $email;
        $this->mot_de_passe    = $mot_de_passe;
        $this->telephone       = $telephone;
        $this->sexe            = $sexe;
        $this->date_naissance  = $date_naissance;
        $this->adresse         = $adresse;
        $this->role            = $role;
        $this->statut          = $statut;
    }

    // Getters
    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getMotDePasse(): string {
        return $this->mot_de_passe;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function getSexe(): string {
        return $this->sexe;
    }

    public function getDateNaissance(): string {
        return $this->date_naissance;
    }

    public function getAdresse(): string {
        return $this->adresse;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    // Setters
    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setMotDePasse(string $mot_de_passe): void {
        $this->mot_de_passe = $mot_de_passe;
    }

    public function setTelephone(string $telephone): void {
        $this->telephone = $telephone;
    }

    public function setSexe(string $sexe): void {
        $this->sexe = $sexe;
    }

    public function setDateNaissance(string $date_naissance): void {
        $this->date_naissance = $date_naissance;
    }

    public function setAdresse(string $adresse): void {
        $this->adresse = $adresse;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

    public function show(): void {
        echo "Nom : $this->nom <br>";
        echo "Prénom : $this->prenom <br>";
        echo "Email : $this->email <br>";
        echo "Téléphone : $this->telephone <br>";
        echo "Sexe : $this->sexe <br>";
        echo "Date de naissance : $this->date_naissance <br>";
        echo "Adresse : $this->adresse <br>";
        echo "Rôle : $this->role <br>";
        echo "Statut : $this->statut <br>";
    }
}
?>
