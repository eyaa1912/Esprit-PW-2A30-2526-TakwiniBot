<?php

class Entretien {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère tous les entretiens avec les informations du type d'entretien
     */
    public function getAllEntretiens($filters = []) {
        $sql = "SELECT 
                    e.id_entretien,
                    e.nom_candidat,
                    e.email_candidat,
                    e.genre,
                    e.type_handicap,
                    e.amenagements,
                    e.type_entretien_id,
                    e.date_entretien,
                    e.heure_entretien,
                    e.poste_cible,
                    e.metier_suggere,
                    e.score_rse,
                    e.remarques,
                    e.statut,
                    e.created_at,
                    e.status,
                    e.id_type_entretien,
                    te.libelle as type_libelle,
                    te.duree_prevue,
                    te.description as type_description,
                    te.nom as type_nom
                FROM entretien e
                LEFT JOIN type_entretien te ON e.type_entretien_id = te.id_type_entretien";
        
        $conditions = [];
        $params = [];
        
        // Filtres
        if (!empty($filters['type_entretien_id'])) {
            $conditions[] = "e.type_entretien_id = :type_entretien_id";
            $params[':type_entretien_id'] = $filters['type_entretien_id'];
        }
        
        if (!empty($filters['statut'])) {
            $conditions[] = "e.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY e.date_entretien DESC, e.heure_entretien DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un entretien par son ID
     */
    public function getEntretienById($id) {
        $sql = "SELECT 
                    e.*,
                    te.libelle as type_libelle,
                    te.duree_prevue,
                    te.description as type_description,
                    te.nom as type_nom
                FROM entretien e
                LEFT JOIN type_entretien te ON e.type_entretien_id = te.id_type_entretien
                WHERE e.id_entretien = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Statistiques pour les cards
     */
    public function getStats() {
        $stats = [];
        
        // Total entretiens
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM entretien");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // À venir (planifiés et date >= aujourd'hui)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as a_venir FROM entretien WHERE statut = 'planifié' AND date_entretien >= CURDATE()");
        $stmt->execute();
        $stats['a_venir'] = $stmt->fetch(PDO::FETCH_ASSOC)['a_venir'];
        
        // Terminés
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as termines FROM entretien WHERE statut = 'terminé'");
        $stmt->execute();
        $stats['termines'] = $stmt->fetch(PDO::FETCH_ASSOC)['termines'];
        
        // Annulés
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as annules FROM entretien WHERE statut = 'annulé'");
        $stmt->execute();
        $stats['annules'] = $stmt->fetch(PDO::FETCH_ASSOC)['annules'];
        
        return $stats;
    }
    
    /**
     * Récupère tous les types d'entretien pour les filtres
     */
    public function getTypesEntretien() {
        $sql = "SELECT id_type_entretien, libelle FROM type_entretien ORDER BY libelle";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouvel entretien
     */
    public function createEntretien($data) {
        $sql = "INSERT INTO entretien (
                    nom_candidat, email_candidat, genre, type_handicap, amenagements,
                    type_entretien_id, date_entretien, heure_entretien, poste_cible,
                    metier_suggere, score_rse, remarques, statut, created_at, status, id_type_entretien
                ) VALUES (
                    :nom_candidat, :email_candidat, :genre, :type_handicap, :amenagements,
                    :type_entretien_id, :date_entretien, :heure_entretien, :poste_cible,
                    :metier_suggere, :score_rse, :remarques, :statut, NOW(), :status, :id_type_entretien
                )";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Met à jour un entretien
     */
    public function updateEntretien($id, $data) {
        $sql = "UPDATE entretien SET 
                    nom_candidat = :nom_candidat,
                    email_candidat = :email_candidat,
                    genre = :genre,
                    type_handicap = :type_handicap,
                    amenagements = :amenagements,
                    type_entretien_id = :type_entretien_id,
                    date_entretien = :date_entretien,
                    heure_entretien = :heure_entretien,
                    poste_cible = :poste_cible,
                    metier_suggere = :metier_suggere,
                    score_rse = :score_rse,
                    remarques = :remarques,
                    statut = :statut,
                    status = :status,
                    id_type_entretien = :id_type_entretien
                WHERE id_entretien = :id";
        
        $data[':id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Supprime un entretien
     */
    public function deleteEntretien($id) {
        $sql = "DELETE FROM entretien WHERE id_entretien = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Formate la date et l'heure pour l'affichage
     */
    public static function formatDateHeure($date, $heure) {
        if (empty($date)) return '';
        
        $dateObj = new DateTime($date);
        $dateFormatted = $dateObj->format('d/m');
        
        if (!empty($heure)) {
            $heureObj = new DateTime($heure);
            $heureFormatted = $heureObj->format('H\hi');
            return $dateFormatted . ' ' . $heureFormatted;
        }
        
        return $dateFormatted;
    }
    
    /**
     * Retourne la classe CSS pour le badge de statut
     */
    public static function getStatutBadgeClass($statut) {
        switch (strtolower($statut)) {
            case 'planifié':
                return 'bg-label-warning';
            case 'en cours':
                return 'bg-label-info';
            case 'terminé':
                return 'bg-label-success';
            case 'annulé':
                return 'bg-label-danger';
            default:
                return 'bg-label-secondary';
        }
    }
}