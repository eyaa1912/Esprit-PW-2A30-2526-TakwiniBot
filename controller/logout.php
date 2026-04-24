<?php
session_start();

$role = $_SESSION['user']['role'] ?? 'candidat';

session_destroy();

if ($role === 'admin') {
    header('Location: ../view/frontoffice/login.php');
} else {
    header('Location: ../view/frontoffice/formations/index.php');
}
exit;
