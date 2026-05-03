<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';

header('Content-Type: application/json');

// Sécurité : admin uniquement
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID invalide.']);
    exit;
}

try {
    $db = config::getConnexion();

    // Récupérer le statut actuel
    $stmt = $db->prepare('SELECT statut, nom, prenom FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit;
    }

    // Basculer : suspendu ↔ actif
    $newStatut = ($user['statut'] === 'suspendu') ? 'actif' : 'suspendu';

    $db->prepare('UPDATE users SET statut = :statut WHERE id = :id')
       ->execute(['statut' => $newStatut, 'id' => $id]);

    $nom = trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''));
    $msg = $newStatut === 'suspendu'
        ? "Utilisateur « {$nom} » banni avec succès."
        : "Utilisateur « {$nom} » débanni avec succès.";

    echo json_encode(['success' => true, 'newStatut' => $newStatut, 'message' => $msg]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
