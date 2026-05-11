<?php
declare(strict_types=1);
include __DIR__ . '/../../Controller/ReclamationController.php';

$controller = new ReclamationController();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listReclamations.php?error=' . urlencode('ID invalide.'));
    exit;
}

try {
    $deleted = $controller->deleteReclamation((int)$_GET['id']);
    if ($deleted) {
        header('Location: listReclamations.php?success=deleted');
    } else {
        header('Location: listReclamations.php?error=' . urlencode('Réclamation introuvable.'));
    }
} catch (Throwable $e) {
    header('Location: listReclamations.php?error=' . urlencode($e->getMessage()));
}
exit;
