<?php

class Contrat
{
    private int    $offreId;
    private string $salaire;
    private string $duree;
    private string $dateCreation;
    private string $statut;

    public function __construct(
        int    $offreId,
        string $salaire,
        string $duree,
        string $dateCreation,
        string $statut = 'actif'
    ) {
        $this->offreId      = $offreId;
        $this->salaire      = $salaire;
        $this->duree        = $duree;
        $this->dateCreation = $dateCreation;
        $this->statut       = $statut;
    }

    // Getters
    public function getOffreId(): int         { return $this->offreId; }
    public function getSalaire(): string      { return $this->salaire; }
    public function getDuree(): string        { return $this->duree; }
    public function getDateCreation(): string { return $this->dateCreation; }
    public function getStatut(): string       { return $this->statut; }

    // Setters
    public function setOffreId(int $offreId): void          { $this->offreId = $offreId; }
    public function setSalaire(string $salaire): void       { $this->salaire = $salaire; }
    public function setDuree(string $duree): void           { $this->duree = $duree; }
    public function setDateCreation(string $date): void     { $this->dateCreation = $date; }
    public function setStatut(string $statut): void         { $this->statut = $statut; }

    public function show(): void
    {
        echo "Offre ID : $this->offreId <br>";
        echo "Salaire : $this->salaire <br>";
        echo "Durée : $this->duree <br>";
        echo "Date de création : $this->dateCreation <br>";
        echo "Statut : $this->statut <br>";
    }
}
?>
