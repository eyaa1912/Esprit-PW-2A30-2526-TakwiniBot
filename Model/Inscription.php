<?php
/**
 * Model/Inscription.php
 * Représente une inscription à une formation.
 */
class Inscription
{
    private int    $id;
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
        ?string $niveau        = null,
        ?string $mode_formation = null,
        int    $id             = 0
    ) {
        $this->id             = $id;
        $this->user_id        = $user_id;
        $this->formation_id   = $formation_id;
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->niveau         = $niveau;
        $this->mode_formation = $mode_formation;
    }

    public function getId(): int            { return $this->id; }
    public function getUserId(): int        { return $this->user_id; }
    public function getFormationId(): int   { return $this->formation_id; }
    public function getNom(): string        { return $this->nom; }
    public function getPrenom(): string     { return $this->prenom; }
    public function getEmail(): string      { return $this->email; }
    public function getNiveau(): ?string    { return $this->niveau; }
    public function getModeFormation(): ?string { return $this->mode_formation; }

    public function setNiveau(?string $niveau): void           { $this->niveau = $niveau; }
    public function setModeFormation(?string $mode): void      { $this->mode_formation = $mode; }
}
