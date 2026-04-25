<?php

class Postuler
{
    private string $nom;
    private string $prenom;
    private string $email;
    private int    $offreId;
    private string $cvPath;
    private string $statut;
    private string $datePostulation;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        int    $offreId,
        string $cvPath,
        string $statut          = 'en_attente',
        string $datePostulation = ''
    ) {
        $this->nom             = $nom;
        $this->prenom          = $prenom;
        $this->email           = $email;
        $this->offreId         = $offreId;
        $this->cvPath          = $cvPath;
        $this->statut          = $statut;
        $this->datePostulation = $datePostulation ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getNom(): string            { return $this->nom; }
    public function getPrenom(): string         { return $this->prenom; }
    public function getEmail(): string          { return $this->email; }
    public function getOffreId(): int           { return $this->offreId; }
    public function getCvPath(): string         { return $this->cvPath; }
    public function getStatut(): string         { return $this->statut; }
    public function getDatePostulation(): string { return $this->datePostulation; }

    // Setters
    public function setNom(string $nom): void       { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void   { $this->email = $email; }
    public function setOffreId(int $offreId): void  { $this->offreId = $offreId; }
    public function setCvPath(string $cvPath): void { $this->cvPath = $cvPath; }
    public function setStatut(string $statut): void
    {
        $allowed = ['en_attente', 'acceptee', 'refusee'];
        $this->statut = in_array($statut, $allowed) ? $statut : 'en_attente';
    }
    public function setDatePostulation(string $date): void { $this->datePostulation = $date; }
}
?>
