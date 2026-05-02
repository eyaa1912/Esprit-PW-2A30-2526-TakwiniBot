<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Non connecte']);
    exit;
}

$challenge = base64_encode(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

echo json_encode([
    'challenge' => $challenge,
    'user_id'   => $_SESSION['user']['id'],
    'user_name' => $_SESSION['user']['email'],
    'user_display' => $_SESSION['user']['nom'],
]);
