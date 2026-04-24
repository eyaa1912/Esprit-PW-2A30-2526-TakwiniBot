<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
    echo json_encode(['success' => false, 'error' => 'Aucun fichier ou erreur upload']);
    exit;
}

$file = $_FILES['avatar'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Format non autorisé (jpg, png, gif, webp)']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'Fichier trop grand (max 2MB)']);
    exit;
}

$targetDir  = __DIR__ . '/../view/frontoffice/uploads/avatars/';
$publicPath = 'uploads/avatars/';

if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

// Supprimer l'ancien avatar si existe
$userId = (int) $_SESSION['user']['id'];
try {
    $db   = config::getConnexion();
    $stmt = $db->prepare('SELECT avatar FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $row  = $stmt->fetch();
    if ($row && !empty($row['avatar'])) {
        $oldFile = __DIR__ . '/../view/frontoffice/' . $row['avatar'];
        if (file_exists($oldFile)) @unlink($oldFile);
    }
} catch (Exception $e) {}

$filename   = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$targetFile = $targetDir . $filename;
$publicFile = $publicPath . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'error' => 'Impossible de sauvegarder (permissions ?)']);
    exit;
}

try {
    $db   = config::getConnexion();
    $stmt = $db->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
    $stmt->execute(['avatar' => $publicFile, 'id' => $userId]);
    $_SESSION['user']['avatar'] = $publicFile;
    echo json_encode(['success' => true, 'avatar' => $publicFile]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}
