<?php
/**
 * view/backoffice/dashboard.php
 * Point d'entrée unique du backoffice admin.
 * Redirige vers le dashboard principal (sneat).
 */
session_start();

// Protection : seul un admin peut accéder
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /Esprit-PW-2A30-2627-TakwiniBot-gestion_user/Esprit-PW-2A30-2627-TakwiniBot-gestion_user/view/frontoffice/login.php');
    exit;
}

// Redirection vers le dashboard sneat (gestion users + formations)
header('Location: /Esprit-PW-2A30-2627-TakwiniBot-gestion_user/Esprit-PW-2A30-2627-TakwiniBot-gestion_user/view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php');
exit;
