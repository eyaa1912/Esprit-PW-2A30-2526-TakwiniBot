<?php
require_once 'config/database.php';

$pdo = config::getConnexion();
$stmt = $pdo->prepare('UPDATE entretien SET nom_candidat = :nom, poste_cible = :poste WHERE id = 1');
$stmt->execute([
    ':nom' => 'Fedi Medini',
    ':poste' => 'Développeur PHP'
]);

echo 'Candidate updated: Fedi Medini - Développeur PHP';
?>
