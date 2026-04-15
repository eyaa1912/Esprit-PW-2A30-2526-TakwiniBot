<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controller/UtilisateurController.php';

if (isset($_SESSION['user']['id'])) {
    $controller = new UtilisateurController();
    $controller->logout((int) $_SESSION['user']['id']);
}

session_destroy();
header('Location: ../view/frontoffice/login.php');
exit;
