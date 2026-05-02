<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['webauthn_challenge']) || !isset($_SESSION['webauthn_user'])) {
    echo json_encode(['success' => false, 'error' => 'Session expiree']);
    exit;
}

// Vérification basique : le credential_id correspond
$credentialId = $data['credentialId'] ?? '';
$userId       = $_SESSION['webauthn_user']['id'];

try {
    $db   = config::getConnexion();
    $stmt = $db->prepare('SELECT credential_id FROM webauthn_credentials WHERE user_id = :uid LIMIT 1');
    $stmt->execute(['uid' => $userId]);
    $row  = $stmt->fetch();

    if (!$row || $row['credential_id'] !== $credentialId) {
        echo json_encode(['success' => false, 'error' => 'Credential invalide']);
        exit;
    }

    // Mettre à jour sign_count
    $db->prepare('UPDATE webauthn_credentials SET sign_count = sign_count + 1 WHERE user_id = :uid')
       ->execute(['uid' => $userId]);

    // Mettre à jour statut user
    $db->prepare('UPDATE users SET statut = "actif" WHERE id = :id')->execute(['id' => $userId]);

    // Créer session
    $_SESSION['user'] = $_SESSION['webauthn_user'];
    unset($_SESSION['webauthn_challenge'], $_SESSION['webauthn_user']);

    $role = $_SESSION['user']['role'];
    $redirect = $role === 'admin'
        ? '../../view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php'
        : 'formations/index.php';

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
