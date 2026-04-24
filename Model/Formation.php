<?php
class Formation {
    private string $titre;
    private string $duree;
    private float $prix;
    private string $niveau;
    private string $description;

    public function __construct(
        string $titre,
        string $duree,
        float $prix,
        string $niveau,
        string $description
    ) {
        $this->titre       = $titre;
        $this->duree       = $duree;
        $this->prix        = $prix;
        $this->niveau      = $niveau;
        $this->description = $description;
    }

    // Getters
    public function getTitre(): string {
        return $this->titre;
    }

    public function getDuree(): string {
        return $this->duree;
    }

    public function getPrix(): float {
        return $this->prix;
    }

    public function getNiveau(): string {
        return $this->niveau;
    }

    public function getDescription(): string {
        return $this->description;
    }

    // Setters
    public function setTitre(string $titre): void {
        $this->titre = $titre;
    }

    public function setDuree(string $duree): void {
        $this->duree = $duree;
    }

    public function setPrix(float $prix): void {
        $this->prix = $prix;
    }

    public function setNiveau(string $niveau): void {
        $this->niveau = $niveau;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function show(): void {
        echo "Titre : $this->titre <br>";
        echo "Durée : $this->duree <br>";
        echo "Prix : $this->prix TND <br>";
        echo "Niveau : $this->niveau <br>";
        echo "Description : $this->description <br>";
    }
}
?>
