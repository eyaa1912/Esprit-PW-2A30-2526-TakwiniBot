<?php
declare(strict_types=1);

class TypeEntretien {
    private ?int $id_type_entretien;
    private string $libelle;
    private ?int $duree_prevue;
    private ?string $description;
    private ?string $nom;

    public function __construct(
        ?int $id_type_entretien,
        string $libelle,
        ?int $duree_prevue,
        ?string $description,
        ?string $nom = null
    ) {
        $this->id_type_entretien = $id_type_entretien;
        $this->libelle = $libelle;
        $this->duree_prevue = $duree_prevue;
        $this->description = $description;
        $this->nom = $nom;
    }

    public function getIdTypeEntretien(): ?int { return $this->id_type_entretien; }
    public function getLibelle(): string { return $this->libelle; }
    public function getDureePrevue(): ?int { return $this->duree_prevue; }
    public function getDescription(): ?string { return $this->description; }
    public function getNom(): ?string { return $this->nom; }

    public function setIdTypeEntretien(?int $id_type_entretien): void { $this->id_type_entretien = $id_type_entretien; }
    public function setLibelle(string $libelle): void { $this->libelle = $libelle; }
    public function setDureePrevue(?int $duree_prevue): void { $this->duree_prevue = $duree_prevue; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
}
