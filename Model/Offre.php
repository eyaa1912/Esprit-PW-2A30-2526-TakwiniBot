<?php

require_once __DIR__ . 'C:\xampp\htdocs\takwinibot\config.php'; // adapte le chemin si besoin

class Offre
{
    private ?int $id = null;
    private string $titre;
    private string $description;
    private string $type;
    private string $datePublication;

    // ====================== GETTERS ======================
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDatePublication(): string
    {
        return $this->datePublication;
    }

    // ====================== SETTERS ======================
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = htmlspecialchars(trim($titre), ENT_QUOTES, 'UTF-8');
    }

    public function setDescription(string $description): void
    {
        $this->description = htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
    }

    public function setType(string $type): void
    {
        $this->type = htmlspecialchars(trim($type), ENT_QUOTES, 'UTF-8');
    }

    public function setDatePublication(string $datePublication): void
    {
        $this->datePublication = $datePublication;
    }

    // ====================== HYDRATION ======================
    public function hydrate(array $data): self
    {
        if (isset($data['id'])) {
            $this->setId((int)$data['id']);
        }

        if (isset($data['titre'])) {
            $this->setTitre($data['titre']);
        }

        if (isset($data['description'])) {
            $this->setDescription($data['description']);
        }

        if (isset($data['type'])) {
            $this->setType($data['type']);
        }

        if (isset($data['datePublication'])) {
            $this->setDatePublication($data['datePublication']);
        }

        return $this;
    }
}