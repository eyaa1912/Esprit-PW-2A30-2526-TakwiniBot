<?php

require_once __DIR__ . '/../config.php';

class OffreController
{
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    // ================= INDEX =================
    public function index()
    {
        $sql = "SELECT * FROM offre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ================= CREATE =================
    public function store($data)
    {
        $sql = "INSERT INTO offre (titre, description, type, datePublication)
                VALUES (:titre, :description, :type, :datePublication)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'type' => $data['type'],
            'datePublication' => $data['datePublication']
        ]);
    }

    // ================= DELETE =================
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM offre WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ================= UPDATE =================
    public function update($id, $data)
    {
        $sql = "UPDATE offre SET titre=?, description=?, type=?, datePublication=? WHERE id=?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['type'],
            $data['datePublication'],
            $id
        ]);
    }
}