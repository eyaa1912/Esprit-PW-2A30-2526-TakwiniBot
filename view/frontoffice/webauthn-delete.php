<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $db = config::getConnexion();
    $db->prepare('DELETE FROM webauthn_credentials WHERE user_id = :uid')
       ->execute(['uid' => $_SESSION['user']['id']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
