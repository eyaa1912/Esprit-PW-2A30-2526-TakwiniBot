<?php
require_once '../../../config/config.php';

class Produit {
    private int $id;
    private int $categorie_id;
    private string $nom;
    private float $prix;
    private int $stock;
    private string $description;
    private ?string $image;

    public function __construct(
        int $id,
        int $categorie_id,
        string $nom,
        float $prix,
        int $stock,
        string $description,
        ?string $image = null
    ) {
        $this->id = $id;
        $this->categorie_id = $categorie_id;
        $this->nom = $nom;
        $this->prix = $prix;
        $this->stock = $stock;
        $this->description = $description;
        $this->image = $image;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getCategorieId(): int { return $this->categorie_id; }
    public function getNom(): string { return $this->nom; }
    public function getPrix(): float { return $this->prix; }
    public function getStock(): int { return $this->stock; }
    public function getDescription(): string { return $this->description; }
    public function getImage(): ?string { return $this->image; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setCategorieId(int $categorie_id): void { $this->categorie_id = $categorie_id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrix(float $prix): void { $this->prix = $prix; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setImage(?string $image): void { $this->image = $image; }

    // Display method
    public function show(): void {
        echo "ID: $this->id <br>";
        echo "Catégorie ID: $this->categorie_id <br>";
        echo "Nom: $this->nom <br>";
        echo "Prix: $this->prix TND <br>";
        echo "Stock: $this->stock <br>";
        echo "Description: $this->description <br>";
        if ($this->image) {
            echo "Image: $this->image <br>";
        }
    }
}
?>