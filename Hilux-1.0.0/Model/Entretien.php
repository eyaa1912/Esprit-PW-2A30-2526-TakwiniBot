<?php
declare(strict_types=1);

class Entretien {
    private ?int $id_entretien;
    private string $nom_candidat;
    private string $email_candidat;
    private string $genre;
    private string $type_handicap;
    private ?string $amenagements;
    private int $type_entretien_id;
    private string $date_entretien;
    private string $heure_entretien;
    private string $poste_cible;
    private ?string $metier_suggere;
    private ?int $score_rse;
    private ?string $remarques;
    private string $statut;
    private ?string $created_at;

    public function __construct(
        ?int $id_entretien,
        string $nom_candidat,
        string $email_candidat,
        string $genre,
        string $type_handicap,
        ?string $amenagements,
        int $type_entretien_id,
        string $date_entretien,
        string $heure_entretien,
        string $poste_cible,
        ?string $metier_suggere,
        ?int $score_rse,
        ?string $remarques,
        string $statut,
        ?string $created_at = null
    ) {
        $this->id_entretien = $id_entretien;
        $this->nom_candidat = $nom_candidat;
        $this->email_candidat = $email_candidat;
        $this->genre = $genre;
        $this->type_handicap = $type_handicap;
        $this->amenagements = $amenagements;
        $this->type_entretien_id = $type_entretien_id;
        $this->date_entretien = $date_entretien;
        $this->heure_entretien = $heure_entretien;
        $this->poste_cible = $poste_cible;
        $this->metier_suggere = $metier_suggere;
        $this->score_rse = $score_rse;
        $this->remarques = $remarques;
        $this->statut = $statut;
        $this->created_at = $created_at;
    }

    public function getIdEntretien(): ?int { return $this->id_entretien; }
    public function getNomCandidat(): string { return $this->nom_candidat; }
    public function getEmailCandidat(): string { return $this->email_candidat; }
    public function getGenre(): string { return $this->genre; }
    public function getTypeHandicap(): string { return $this->type_handicap; }
    public function getAmenagements(): ?string { return $this->amenagements; }
    public function getTypeEntretienId(): int { return $this->type_entretien_id; }
    public function getDateEntretien(): string { return $this->date_entretien; }
    public function getHeureEntretien(): string { return $this->heure_entretien; }
    public function getPosteCible(): string { return $this->poste_cible; }
    public function getMetierSuggere(): ?string { return $this->metier_suggere; }
    public function getScoreRse(): ?int { return $this->score_rse; }
    public function getRemarques(): ?string { return $this->remarques; }
    public function getStatut(): string { return $this->statut; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setIdEntretien(?int $id_entretien): void { $this->id_entretien = $id_entretien; }
    public function setNomCandidat(string $nom_candidat): void { $this->nom_candidat = $nom_candidat; }
    public function setEmailCandidat(string $email_candidat): void { $this->email_candidat = $email_candidat; }
    public function setGenre(string $genre): void { $this->genre = $genre; }
    public function setTypeHandicap(string $type_handicap): void { $this->type_handicap = $type_handicap; }
    public function setAmenagements(?string $amenagements): void { $this->amenagements = $amenagements; }
    public function setTypeEntretienId(int $type_entretien_id): void { $this->type_entretien_id = $type_entretien_id; }
    public function setDateEntretien(string $date_entretien): void { $this->date_entretien = $date_entretien; }
    public function setHeureEntretien(string $heure_entretien): void { $this->heure_entretien = $heure_entretien; }
    public function setPosteCible(string $poste_cible): void { $this->poste_cible = $poste_cible; }
    public function setMetierSuggere(?string $metier_suggere): void { $this->metier_suggere = $metier_suggere; }
    public function setScoreRse(?int $score_rse): void { $this->score_rse = $score_rse; }
    public function setRemarques(?string $remarques): void { $this->remarques = $remarques; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}
