<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function statusBadgeClass(string $statut): string
{
    return match ($statut) {
        'planifié' => 'status-planifie',
        'en cours' => 'status-encours',
        'terminé' => 'status-termine',
        'annulé' => 'status-annule',
        default => 'status-default',
    };
}

function renderStars(?int $score): string
{
    if ($score === null) {
        return '<span class="text-muted">Non évalué</span>';
    }

    $html = '<span class="stars" aria-label="Score RSE ' . $score . ' sur 5">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star ' . ($i <= $score ? 'filled' : 'empty') . '">★</span>';
    }
    $html .= ' <span class="ms-1 fw-semibold">(' . $score . '/5)</span></span>';

    return $html;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Identifiant entretien invalide.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM entretien WHERE id_entretien = :id');
$stmt->execute([':id' => $id]);
$entretien = $stmt->fetch();

if (!$entretien) {
    $_SESSION['flash_error'] = 'Entretien introuvable.';
    header('Location: index.php');
    exit;
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail entretien - TakwiniBot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="show">
<main class="container py-4">
    <section class="top-bar mb-4">
        <div>
            <h1 class="mb-1">Fiche entretien #<?= (int) $entretien['id_entretien'] ?></h1>
            <p class="text-muted mb-0"><?= e($entretien['nom_candidat']) ?> • <?= e($entretien['email_candidat']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Retour liste</a>
            <a href="edit.php?id=<?= (int) $entretien['id_entretien'] ?>" class="btn btn-takwini">Modifier</a>
        </div>
    </section>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Informations principales</h2>
                    <dl class="row mb-0 detail-grid">
                        <dt class="col-sm-4">Nom candidat</dt>
                        <dd class="col-sm-8"><?= e($entretien['nom_candidat']) ?></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?= e($entretien['email_candidat']) ?></dd>

                        <dt class="col-sm-4">Genre</dt>
                        <dd class="col-sm-8"><?= e(ucfirst($entretien['genre'])) ?></dd>

                        <dt class="col-sm-4">Type de handicap</dt>
                        <dd class="col-sm-8"><?= e($entretien['type_handicap']) ?></dd>

                        <dt class="col-sm-4">Aménagements</dt>
                        <dd class="col-sm-8"><?= $entretien['amenagements'] ? nl2br(e($entretien['amenagements'])) : '<span class="text-muted">Aucun</span>' ?></dd>

                        <dt class="col-sm-4">Type entretien</dt>
                        <dd class="col-sm-8"><?= e($entretien['type_entretien']) ?></dd>

                        <dt class="col-sm-4">Date / heure</dt>
                        <dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($entretien['date_entretien']))) ?> à <?= e(substr($entretien['heure_entretien'], 0, 5)) ?></dd>

                        <dt class="col-sm-4">Poste cible</dt>
                        <dd class="col-sm-8"><?= e($entretien['poste_cible']) ?></dd>

                        <dt class="col-sm-4">Métier suggéré</dt>
                        <dd class="col-sm-8"><?= $entretien['metier_suggere'] ? e($entretien['metier_suggere']) : '<span class="text-muted">Non renseigné</span>' ?></dd>

                        <dt class="col-sm-4">Remarques</dt>
                        <dd class="col-sm-8"><?= $entretien['remarques'] ? nl2br(e($entretien['remarques'])) : '<span class="text-muted">Aucune</span>' ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6">Statut</h2>
                    <span class="status-badge <?= statusBadgeClass($entretien['statut']) ?>">
                        <?= e(ucfirst($entretien['statut'])) ?>
                    </span>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6">Score RSE</h2>
                    <?= renderStars($entretien['score_rse'] !== null ? (int) $entretien['score_rse'] : null) ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6">Traçabilité</h2>
                    <p class="mb-0 text-muted">
                        Créé le <?= e(date('d/m/Y H:i', strtotime($entretien['created_at']))) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
