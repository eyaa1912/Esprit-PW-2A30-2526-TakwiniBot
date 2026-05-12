<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecte']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$descriptor = $data['descriptor'] ?? null;

if (!$descriptor || !is_array($descriptor) || count($descriptor) !== 128) {
    echo json_encode(['success' => false, 'error' => 'Descripteur invalide']);
    exit;
}

try {
    $db     = config::getConnexion();
    $userId = (int) $_SESSION['user']['id'];

    // Supprimer ancien descripteur
    $db->prepare('DELETE FROM face_descriptors WHERE user_id = :uid')->execute(['uid' => $userId]);

    // Sauvegarder nouveau
    $db->prepare('INSERT INTO face_descriptors (user_id, descriptor) VALUES (:uid, :desc)')
       ->execute(['uid' => $userId, 'desc' => json_encode($descriptor)]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
