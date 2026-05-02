<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecte']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Donnees invalides']);
    exit;
}

$credentialId = $data['credentialId'] ?? '';
$publicKey    = $data['publicKey']    ?? '';
$userId       = (int) $_SESSION['user']['id'];

if (empty($credentialId) || empty($publicKey)) {
    echo json_encode(['success' => false, 'error' => 'Donnees manquantes']);
    exit;
}

try {
    $db = config::getConnexion();
    // Supprimer ancienne credential si existe
    $db->prepare('DELETE FROM webauthn_credentials WHERE user_id = :uid')->execute(['uid' => $userId]);
    // Sauvegarder nouvelle
    $db->prepare('INSERT INTO webauthn_credentials (user_id, credential_id, public_key, sign_count) VALUES (:uid, :cid, :pk, 0)')
       ->execute(['uid' => $userId, 'cid' => $credentialId, 'pk' => $publicKey]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
