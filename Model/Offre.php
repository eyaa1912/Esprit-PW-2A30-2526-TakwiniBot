<?php

class Offre
{
    private string $titre;
    private string $description;
    private string $type;
    private string $datePublication;

    public function __construct(
        string $titre,
        string $description,
        string $type,
        string $datePublication
    ) {
        $this->titre           = $titre;
        $this->description     = $description;
        $this->type            = $type;
        $this->datePublication = $datePublication;
    }

    // Getters
    public function getTitre(): string           { return $this->titre; }
    public function getDescription(): string     { return $this->description; }
    public function getType(): string            { return $this->type; }
    public function getDatePublication(): string { return $this->datePublication; }

    // Setters
    public function setTitre(string $titre): void                     { $this->titre = $titre; }
    public function setDescription(string $description): void         { $this->description = $description; }
    public function setType(string $type): void                       { $this->type = $type; }
    public function setDatePublication(string $datePublication): void { $this->datePublication = $datePublication; }

    public function show(): void
    {
        echo "Titre : $this->titre <br>";
        echo "Description : $this->description <br>";
        echo "Type : $this->type <br>";
        echo "Date de publication : $this->datePublication <br>";
    }
}
?>
