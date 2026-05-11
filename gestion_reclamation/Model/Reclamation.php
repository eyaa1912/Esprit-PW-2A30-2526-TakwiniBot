<?php
declare(strict_types=1);

class Reclamation
{
    private ?int $id;
    private ?int $userId;
    private ?int $formulaireId;
    private string $sujet;
    private string $message;
    private string $statut;
    private ?string $dateCreation;
    private ?string $dateModification;

    public function __construct(
        ?int $id = null,
        ?int $userId = null,
        ?int $formulaireId = null,
        string $sujet = '',
        string $message = '',
        string $statut = 'en_attente',
        ?string $dateCreation = null,
        ?string $dateModification = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->formulaireId = $formulaireId;
        $this->sujet = $sujet;
        $this->message = $message;
        $this->statut = $statut;
        $this->dateCreation = $dateCreation;
        $this->dateModification = $dateModification;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getFormulaireId(): ?int
    {
        return $this->formulaireId;
    }

    public function setFormulaireId(?int $formulaireId): void
    {
        $this->formulaireId = $formulaireId;
    }

    public function getSujet(): string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): void
    {
        $this->sujet = $sujet;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    public function getDateModification(): ?string
    {
        return $this->dateModification;
    }

    public function setDateModification(?string $dateModification): void
    {
        $this->dateModification = $dateModification;
    }

    public function show(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'formulaire_id' => $this->formulaireId,
            'sujet' => $this->sujet,
            'message' => $this->message,
            'statut' => $this->statut,
            'date_creation' => $this->dateCreation,
            'date_modification' => $this->dateModification,
        ];
    }
}
