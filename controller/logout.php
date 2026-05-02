<?php
session_start();

// Mettre le statut inactif si connecté
if (isset($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../config.php';
    try {
        $db = config::getConnexion();
        $db->prepare('UPDATE users SET statut = "inactif" WHERE id = :id')
           ->execute(['id' => (int) $_SESSION['user']['id']]);
    } catch (Exception $e) {
        // silencieux
    }
}

session_destroy();
header('Location: /gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/formations/index.php');
exit;
