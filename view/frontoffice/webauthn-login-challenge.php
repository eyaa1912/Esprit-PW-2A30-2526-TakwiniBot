<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    echo json_encode(['error' => 'Email requis']);
    exit;
}

try {
    $db   = config::getConnexion();
    $stmt = $db->prepare('SELECT u.id, u.nom, u.email, u.role, u.statut, u.avatar, w.credential_id
                          FROM users u
                          JOIN webauthn_credentials w ON w.user_id = u.id
                          WHERE u.email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['error' => 'Aucune empreinte enregistree pour cet email']);
        exit;
    }

    if ($row['statut'] === 'en_attente') {
        echo json_encode(['error' => 'Compte en attente de validation']);
        exit;
    }

    $challenge = base64_encode(random_bytes(32));
    $_SESSION['webauthn_challenge'] = $challenge;
    $_SESSION['webauthn_user']      = [
        'id'     => $row['id'],
        'nom'    => $row['nom'],
        'email'  => $row['email'],
        'role'   => $row['role'],
        'avatar' => $row['avatar'],
    ];

    echo json_encode([
        'challenge'     => $challenge,
        'credential_id' => $row['credential_id'],
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
