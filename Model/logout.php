<?php
session_start();
require_once __DIR__ . '/../config.php';

// Mettre le statut inactif si l'utilisateur est connecté
if (isset($_SESSION['user']['id'])) {
    try {
        $db = config::getConnexion();
        $db->prepare("UPDATE users SET statut = 'inactif' WHERE id = :id")
           ->execute(['id' => (int)$_SESSION['user']['id']]);
    } catch (Exception $e) {
        // Silencieux — on déconnecte quand même
    }
}

session_destroy();
header('Location: /Esprit-PW-2A30-2627-TakwiniBot-gestion_user/Esprit-PW-2A30-2627-TakwiniBot-gestion_user/view/frontoffice/formations/index.php');
exit;
