<?php

class Inscription {
    private ?int   $id = null;
    private int    $user_id;
    private int    $formation_id;
    private string $nom;
    private string $prenom;
    private string $email;
    private ?string $niveau;
    private ?string $mode_formation;

    public function __construct(
        int    $user_id,
        int    $formation_id,
        string $nom,
        string $prenom,
        string $email,
        ?string $niveau = null,
        ?string $mode_formation = null,
        ?int   $id = null
    ) {
        $this->user_id        = $user_id;
        $this->formation_id   = $formation_id;
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->niveau         = $niveau;
        $this->mode_formation = $mode_formation;
        $this->id             = $id;
    }

    // Getters
    public function getId(): ?int                { return $this->id; }
    public function getUserId(): int             { return $this->user_id; }
    public function getFormationId(): int        { return $this->formation_id; }
    public function getNom(): string             { return $this->nom; }
    public function getPrenom(): string          { return $this->prenom; }
    public function getEmail(): string           { return $this->email; }
    public function getNiveau(): ?string         { return $this->niveau; }
    public function getModeFormation(): ?string  { return $this->mode_formation; }

    // Setters
    public function setId(?int $id): void                       { $this->id = $id; }
    public function setUserId(int $user_id): void               { $this->user_id = $user_id; }
    public function setFormationId(int $formation_id): void     { $this->formation_id = $formation_id; }
    public function setNom(string $nom): void                   { $this->nom = $nom; }
    public function setPrenom(string $prenom): void             { $this->prenom = $prenom; }
    public function setEmail(string $email): void               { $this->email = $email; }
    public function setNiveau(?string $niveau): void            { $this->niveau = $niveau; }
    public function setModeFormation(?string $mode): void       { $this->mode_formation = $mode; }

    public function show(): void {
        echo "User ID : $this->user_id <br>";
        echo "Formation ID : $this->formation_id <br>";
        echo "Nom : $this->nom <br>";
        echo "Prénom : $this->prenom <br>";
        echo "Email : $this->email <br>";
        echo "Niveau : " . ($this->niveau ?? 'Non spécifié') . "<br>";
        echo "Mode : " . ($this->mode_formation ?? 'Non spécifié') . "<br>";
    }
}
?>