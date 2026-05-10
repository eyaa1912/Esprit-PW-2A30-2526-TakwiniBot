<?php

require_once __DIR__ . '/../models/Entretien.php';
require_once __DIR__ . '/../config/database.php';

class EntretienController {
    private $entretienModel;
    
    public function __construct() {
        $database = new \Database();
        $pdo = $database->getConnection();
        $this->entretienModel = new \Entretien($pdo);
    }
    
    /**
     * Affiche la page de gestion des entretiens
     */
    public function index() {
        // Récupération des filtres
        $filters = [];
        if (isset($_GET['type_entretien_id']) && !empty($_GET['type_entretien_id'])) {
            $filters['type_entretien_id'] = $_GET['type_entretien_id'];
        }
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $filters['statut'] = $_GET['statut'];
        }
        if (isset($_GET['sort']) && in_array($_GET['sort'], ['candidat_asc', 'candidat_desc'], true)) {
            $filters['sort'] = $_GET['sort'];
        }
        
        // Récupération des données
        $entretiens = $this->entretienModel->getAllEntretiens($filters);
        $stats = $this->entretienModel->getStats();
        $statsByPoste = $this->entretienModel->getStatsByPoste();
        $typesEntretien = $this->entretienModel->getTypesEntretien();
        
        // Statuts disponibles
        $statuts = ['planifié', 'en cours', 'terminé', 'annulé'];
        
        // Inclusion de la vue
        include __DIR__ . '/../views/entretiens/index.php';
    }
    
    /**
     * Affiche les détails d'un entretien
     */
    public function show($id) {
        $entretien = $this->entretienModel->getEntretienById($id);
        
        if (!$entretien) {
            header('HTTP/1.0 404 Not Found');
            echo "Entretien non trouvé";
            return;
        }
        
        include __DIR__ . '/../views/entretiens/show.php';
    }
    
    /**
     * Crée un nouvel entretien
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':nom_candidat' => htmlspecialchars($_POST['nom_candidat'] ?? ''),
                ':email_candidat' => htmlspecialchars($_POST['email_candidat'] ?? ''),
                ':genre' => htmlspecialchars($_POST['genre'] ?? ''),
                ':type_handicap' => htmlspecialchars($_POST['type_handicap'] ?? ''),
                ':amenagements' => htmlspecialchars($_POST['amenagements'] ?? ''),
                ':type_entretien_id' => (int)($_POST['type_entretien_id'] ?? 0),
                ':date_entretien' => $_POST['date_entretien'] ?? null,
                ':heure_entretien' => $_POST['heure_entretien'] ?? null,
                ':poste_cible' => htmlspecialchars($_POST['poste_cible'] ?? ''),
                ':metier_suggere' => htmlspecialchars($_POST['metier_suggere'] ?? ''),
                ':score_rse' => (int)($_POST['score_rse'] ?? 0),
                ':remarques' => htmlspecialchars($_POST['remarques'] ?? ''),
                ':statut' => htmlspecialchars($_POST['statut'] ?? 'planifié')
            ];
            
            if ($this->entretienModel->createEntretien($data)) {
                header('Location: gestion-entretiens.php?success=created');
                exit;
            } else {
                header('Location: gestion-entretiens.php?error=create_failed');
                exit;
            }
        }
        
        $typesEntretien = $this->entretienModel->getTypesEntretien();
        include __DIR__ . '/../views/entretiens/create.php';
    }
    
    /**
     * Met à jour un entretien
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':nom_candidat' => htmlspecialchars($_POST['nom_candidat'] ?? ''),
                ':email_candidat' => htmlspecialchars($_POST['email_candidat'] ?? ''),
                ':genre' => htmlspecialchars($_POST['genre'] ?? ''),
                ':type_handicap' => htmlspecialchars($_POST['type_handicap'] ?? ''),
                ':amenagements' => htmlspecialchars($_POST['amenagements'] ?? ''),
                ':type_entretien_id' => (int)($_POST['type_entretien_id'] ?? 0),
                ':date_entretien' => $_POST['date_entretien'] ?? null,
                ':heure_entretien' => $_POST['heure_entretien'] ?? null,
                ':poste_cible' => htmlspecialchars($_POST['poste_cible'] ?? ''),
                ':metier_suggere' => htmlspecialchars($_POST['metier_suggere'] ?? ''),
                ':score_rse' => (int)($_POST['score_rse'] ?? 0),
                ':remarques' => htmlspecialchars($_POST['remarques'] ?? ''),
                ':statut' => htmlspecialchars($_POST['statut'] ?? 'planifié')
            ];
            
            if ($this->entretienModel->updateEntretien($id, $data)) {
                header('Location: gestion-entretiens.php?success=updated');
                exit;
            } else {
                header('Location: gestion-entretiens.php?error=update_failed');
                exit;
            }
        }
        
        $entretien = $this->entretienModel->getEntretienById($id);
        $typesEntretien = $this->entretienModel->getTypesEntretien();
        
        if (!$entretien) {
            header('HTTP/1.0 404 Not Found');
            echo "Entretien non trouvé";
            return;
        }
        
        include __DIR__ . '/../views/entretiens/edit.php';
    }
    
    /**
     * Supprime un entretien
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->entretienModel->deleteEntretien($id)) {
                header('Location: gestion-entretiens.php?success=deleted');
                exit;
            } else {
                header('Location: gestion-entretiens.php?error=delete_failed');
                exit;
            }
        }
        
        // Afficher une page de confirmation si nécessaire
        $entretien = $this->entretienModel->getEntretienById($id);
        
        if (!$entretien) {
            header('HTTP/1.0 404 Not Found');
            echo "Entretien non trouvé";
            return;
        }
        
        include __DIR__ . '/../views/entretiens/delete.php';
    }
    
    /**
     * API pour récupérer les entretiens en JSON (pour AJAX)
     */
    public function api() {
        header('Content-Type: application/json');
        
        $filters = [];
        if (isset($_GET['type_entretien_id']) && !empty($_GET['type_entretien_id'])) {
            $filters['type_entretien_id'] = $_GET['type_entretien_id'];
        }
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $filters['statut'] = $_GET['statut'];
        }
        $entretiens = $this->entretienModel->getAllEntretiens($filters);
        
        echo json_encode([
            'success' => true,
            'data' => $entretiens
        ]);
    }

    /**
     * Vue exportable pour impression / PDF
     */
    public function printView() {
        $filters = [];
        if (isset($_GET['type_entretien_id']) && !empty($_GET['type_entretien_id'])) {
            $filters['type_entretien_id'] = $_GET['type_entretien_id'];
        }
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $filters['statut'] = $_GET['statut'];
        }
        if (isset($_GET['sort']) && in_array($_GET['sort'], ['candidat_asc', 'candidat_desc'], true)) {
            $filters['sort'] = $_GET['sort'];
        }

        $entretiens = $this->entretienModel->getAllEntretiens($filters);
        include __DIR__ . '/../views/entretiens/print.php';
    }
}