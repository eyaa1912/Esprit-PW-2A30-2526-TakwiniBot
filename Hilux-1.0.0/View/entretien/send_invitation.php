<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../Controller/EmailController.php';
require_once __DIR__ . '/../../config/database.php';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$entretienId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$emailController = new EmailController();

if ($entretienId <= 0) {
    $_SESSION['flash_error'] = 'Invalid interview ID';
    header('Location: add.php');
    exit;
}

// Send invitation email
$result = $emailController->sendInterviewInvitation($entretienId);

if (isset($result['success']) && $result['success']) {
    $_SESSION['flash_success'] = 'Token généré avec succès! ' . ($result['emailSent'] ? 'Email envoyé à ' . e($result['email']) : 'Email non configuré - lien disponible ci-dessous');
    $_SESSION['interview_link'] = $result['link'];
    $_SESSION['interview_token'] = $result['token'];
    header('Location: add.php');
    exit;
} else {
    $_SESSION['flash_error'] = $result['error'] ?? 'Failed to generate token';
    header('Location: add.php');
    exit;
}
