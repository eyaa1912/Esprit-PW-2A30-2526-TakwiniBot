<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/AuthController.php';

if (isset($_SESSION['user']['id'])) {
    $controller = new AuthController();
    $controller->logout((int) $_SESSION['user']['id']);
}

session_destroy();
header('Location: ../View/login.html');
exit;
