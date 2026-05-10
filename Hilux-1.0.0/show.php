<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';
$pdo = config::getConnexion();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function badgeClass(string $statut): string
{
    return match ($statut) {
        'planifié' => 'label label-warning',
        'en cours' => 'label label-info',
        'terminé'  => 'label label-success',
        'annulé'   => 'label label-danger',
        default    => 'label label-default',
    };
}

function renderStars(?int $score): string
{
    if ($score === null) return '<span class="text-muted">Non évalué</span>';
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span style="color:' . ($i <= $score ? '#f5a623' : '#ccc') . ';font-size:1.3em;">★</span>';
    }
    return $html . ' <strong>(' . $score . '/5)</strong>';
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Identifiant invalide.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM entretien WHERE id = :id');
$stmt->execute([':id' => $id]);
$entretien = $stmt->fetch();

if (!$entretien) {
    $_SESSION['flash_error'] = 'Entretien introuvable.';
    header('Location: index.php');
    exit;
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$assets = '/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/assets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fiche entretien #<?= $id ?> - TakwiniBot</title>
    <link rel="stylesheet" href="<?= $assets ?>/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assets ?>/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="<?= $assets ?>/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/fonts.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/menu.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/style.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/responsive.css">
</head>
<body data-spy="scroll" data-offset="80">

<div class="preloader"><div class="status"><div class="status-mes"></div></div></div>
<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
                <h1 class="mb-0 site-logo"><a href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/index.html"><img src="<?= $assets ?>/img/logo.png" alt="TakwiniBot"></a></h1>
            </div>
            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/index.html">Home</a></li>
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/about.html">About</a></li>
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/formations/formation.html">Formations</a></li>
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/gallery.html">Produits</a></li>
                        <li><a href="index.php" class="nav-link active">Entretien</a></li>
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/offres.html">Offres</a></li>
                        <li><a class="nav-link" href="/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/front_mes_reclamations.html">Réclamations</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;">
                <a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
            </div>
        </div>
    </div>
</header>

<section class="section-top">
    <div class="container">
        <div class="col-lg-10 offset-lg-1 col-xs-12 text-center">
            <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
                <h1>Fiche entretien #<?= $id ?></h1>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <a href="index.php" class="btn btn-default">Retour liste</a>
                <a href="edit.php?id=<?= $id ?>" class="btn btn-default" style="background:#30b5e1;color:#fff;">Modifier</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Informations du candidat</h3></div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr><th style="width:35%">Nom candidat</th><td><?= e($entretien['nom_candidat']) ?></td></tr>
                            <tr><th>Email</th><td><?= e($entretien['email_candidat']) ?></td></tr>
                            <tr><th>Genre</th><td><?= e(ucfirst($entretien['genre'])) ?></td></tr>
                            <tr><th>Type de handicap</th><td><?= e($entretien['type_handicap'] ?? 'Aucun') ?></td></tr>
                            <tr><th>Aménagements</th><td><?= $entretien['amenagements'] ? nl2br(e($entretien['amenagements'])) : '<span class="text-muted">Aucun</span>' ?></td></tr>
                            <tr><th>Type d'entretien</th><td><?= e((string) ($entretien['type_entretien_id'] ?? '')) ?></td></tr>
                            <tr><th>Date / Heure</th><td><?= e(date('d/m/Y', strtotime($entretien['date_entretien']))) ?> à <?= e(substr($entretien['heure_entretien'], 0, 5)) ?></td></tr>
                            <tr><th>Poste cible</th><td><?= e($entretien['poste_cible']) ?></td></tr>
                            <tr><th>Métier suggéré</th><td><?= $entretien['metier_suggere'] ? e($entretien['metier_suggere']) : '<span class="text-muted">Non renseigné</span>' ?></td></tr>
                            <tr><th>Remarques</th><td><?= $entretien['remarques'] ? nl2br(e($entretien['remarques'])) : '<span class="text-muted">Aucune</span>' ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Statut</h3></div>
                    <div class="panel-body">
                        <span class="<?= e(badgeClass($entretien['statut'])) ?>" style="font-size:1.1em;padding:6px 12px;">
                            <?= e(ucfirst($entretien['statut'])) ?>
                        </span>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Score RSE</h3></div>
                    <div class="panel-body">
                        <?= renderStars($entretien['score_rse'] !== null ? (int) $entretien['score_rse'] : null) ?>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Traçabilité</h3></div>
                    <div class="panel-body">
                        <p class="text-muted mb-0">Créé le <?= e(date('d/m/Y H:i', strtotime($entretien['created_at']))) ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<footer class="footer-area">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-12 col-sm-12 col-xs-12 wow zoomIn">
                <p class="footer_copyright">Takwinibot &copy; 2026 All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="<?= $assets ?>/js/jquery-1.12.4.min.js"></script>
<script src="<?= $assets ?>/bootstrap/js/bootstrap.min.js"></script>
<script src="<?= $assets ?>/js/modernizr-2.8.3.min.js"></script>
<script src="<?= $assets ?>/js/jquery.sticky.js"></script>
<script src="<?= $assets ?>/js/menu.js"></script>
<script src="<?= $assets ?>/js/wow.min.js"></script>
<script src="<?= $assets ?>/js/scripts.js"></script>
</body>
</html>
