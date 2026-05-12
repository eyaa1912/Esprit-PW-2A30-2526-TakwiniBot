<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

$data       = json_decode(file_get_contents('php://input'), true);
$descriptor = $data['descriptor'] ?? null;
$email      = $data['email'] ?? null;

if (!$descriptor || !is_array($descriptor) || count($descriptor) !== 128) {
    echo json_encode(['success' => false, 'error' => 'Descripteur invalide']);
    exit;
}

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Email non fourni']);
    exit;
}

try {
    $db   = config::getConnexion();
    $stmt = $db->prepare('SELECT fd.user_id, fd.descriptor, u.nom, u.email, u.role, u.statut, u.avatar
                        FROM face_descriptors fd
                        JOIN users u ON u.id = fd.user_id 
                        WHERE u.email = :email');
    $stmt->execute(['email' => $email]);
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        echo json_encode(['success' => false, 'error' => 'Aucun visage enregistre pour cet email']);
        exit;
    }

    $bestMatch   = null;
    $bestDistance = 0.50; // seuil max
    $minFoundDistance = 999;

    foreach ($rows as $row) {
        $stored = json_decode($row['descriptor'], true);
        if (!$stored || count($stored) !== 128) continue;

        // Calcul distance euclidienne
        $sum = 0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $descriptor[$i] - $stored[$i];
            $sum += $diff * $diff;
        }
        $distance = sqrt($sum);

        if ($distance < $minFoundDistance) {
            $minFoundDistance = $distance;
        }

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $bestMatch    = $row;
        }
    }

    if (!$bestMatch) {
        $dist_str = ($minFoundDistance == 999) ? "N/A" : round($minFoundDistance, 3);
        echo json_encode(['success' => false, 'error' => "Visage non reconnu. (Distance calculée : " . $dist_str . ", Seuil : 0.50)"]);
        exit;
    }

    if ($bestMatch['statut'] === 'en_attente') {
        echo json_encode(['success' => false, 'error' => 'Compte en attente de validation']);
        exit;
    }
    if ($bestMatch['statut'] === 'suspendu') {
        echo json_encode(['success' => false, 'error' => 'Compte suspendu']);
        exit;
    }

    // Mettre à jour statut
    $db->prepare('UPDATE users SET statut = "actif" WHERE id = :id')
       ->execute(['id' => $bestMatch['user_id']]);

    // Créer session
    $_SESSION['user'] = [
        'id'     => $bestMatch['user_id'],
        'nom'    => $bestMatch['nom'],
        'email'  => $bestMatch['email'],
        'role'   => $bestMatch['role'],
        'avatar' => $bestMatch['avatar'],
    ];

    $redirect = $bestMatch['role'] === 'admin'
        ? '/Esprit-PW-2A30-2627-TakwiniBot-gestion_user/Esprit-PW-2A30-2627-TakwiniBot-gestion_user/view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php'
        : '/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/gestion_formation/View/front_office/formations/index.php';

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
