<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Action non autorisée.';
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Identifiant entretien invalide.';
    header('Location: index.php');
    exit;
}

$returnPage = isset($_POST['return_page']) ? max(1, (int) $_POST['return_page']) : 1;
$returnGenre = trim((string) ($_POST['return_genre'] ?? ''));
$returnStatut = trim((string) ($_POST['return_statut'] ?? ''));
$allowedGenres = ['homme', 'femme'];
$allowedStatuts = ['planifié', 'en cours', 'terminé', 'annulé'];

if (!in_array($returnGenre, $allowedGenres, true)) {
    $returnGenre = '';
}
if (!in_array($returnStatut, $allowedStatuts, true)) {
    $returnStatut = '';
}

$redirectQuery = ['page' => $returnPage];
if ($returnGenre !== '') {
    $redirectQuery['genre'] = $returnGenre;
}
if ($returnStatut !== '') {
    $redirectQuery['statut'] = $returnStatut;
}
$redirectUrl = 'index.php?' . http_build_query($redirectQuery);

$selectStmt = $pdo->prepare('SELECT nom_candidat FROM entretien WHERE id_entretien = :id');
$selectStmt->execute([':id' => $id]);
$entretien = $selectStmt->fetch();

if (!$entretien) {
    $_SESSION['flash_error'] = 'Entretien introuvable ou déjà supprimé.';
    header('Location: ' . $redirectUrl);
    exit;
}

$deleteStmt = $pdo->prepare('DELETE FROM entretien WHERE id_entretien = :id');
$deleteStmt->execute([':id' => $id]);

if ($deleteStmt->rowCount() > 0) {
    $_SESSION['flash_success'] = 'Entretien de ' . $entretien['nom_candidat'] . ' supprimé avec succès.';
} else {
    $_SESSION['flash_error'] = 'La suppression a échoué. Veuillez réessayer.';
}

header('Location: ' . $redirectUrl);
exit;
