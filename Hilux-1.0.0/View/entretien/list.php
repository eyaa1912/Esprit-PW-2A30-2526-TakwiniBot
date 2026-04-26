<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../Controller/EntretienController.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function badgeClass(string $statut): string
{
    return match ($statut) {
        'planifié' => 'label label-warning',
        'en cours' => 'label label-info',
        'terminé' => 'label label-success',
        'annulé' => 'label label-danger',
        default => 'label label-default',
    };
}

$controller = new \EntretienController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    if ($deleteId > 0) {
        $controller->deleteEntretien($deleteId);
        $_SESSION['flash_success'] = 'Entretien supprimé avec succès.';
    }

    header('Location: list.php');
    exit;
}

$entretiens = $controller->listEntretiens()->fetchAll();
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Entretien - TakwiniBot</title>
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/css/fonts.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/css/menu.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/css/style.css">
    <link rel="stylesheet" href="../../Hilux-1.0.0/Hilux-1.0.0/assets/css/responsive.css">
</head>
<body data-spy="scroll" data-offset="80">
<div class="preloader"><div class="status"><div class="status-mes"></div></div></div>
<div class="site-mobile-menu site-navbar-target"><div class="site-mobile-menu-header"><div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div></div><div class="site-mobile-menu-body"></div></div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
                <h1 class="mb-0 site-logo"><a href="../../Hilux-1.0.0/Hilux-1.0.0/index.html"><img src="../../Hilux-1.0.0/Hilux-1.0.0/assets/img/logo.png" alt=""></a></h1>
            </div>
            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        <li><a class="nav-link" href="../../Hilux-1.0.0/Hilux-1.0.0/index.html">Home</a></li>
                        <li><a class="nav-link" href="../../Hilux-1.0.0/Hilux-1.0.0/about.html">about</a></li>
                        <li><a class="nav-link" href="../../Hilux-1.0.0/Hilux-1.0.0/formations/formation.html">Formations</a></li>
                        <li><a href="../../Hilux-1.0.0/Hilux-1.0.0/gallery.html">Produits</a></li>
                        <li><a href="add.php" class="nav-link">Entretien</a></li>
                        <li><a class="nav-link" href="../../Hilux-1.0.0/Hilux-1.0.0/offres.html">Offres</a></li>
                        <li><a class="nav-link" href="../../Hilux-1.0.0/Hilux-1.0.0/front_mes_reclamations.html">Réclamations</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;"><a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a></div>
        </div>
    </div>
</header>

<section class="section-top"><div class="container"><div class="col-lg-10 offset-lg-1 col-xs-12 text-center"><div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0"><h1>Gestion Entretien</h1></div></div></div></section>

<section class="section-padding">
    <div class="container">
        <?php if ($flashSuccess): ?><div class="alert alert-success"><?= e((string) $flashSuccess) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-danger"><?= e((string) $flashError) ?></div><?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-8"><h2>Liste des entretiens</h2></div>
            <div class="col-md-4 text-right"><a href="add.php" class="btn btn-default btn-lg" style="background:#30b5e1;color:#fff;">Ajouter un entretien</a></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Candidat</th>
                        <th>Type d'entretien</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($entretiens)): ?>
                    <tr><td colspan="7" class="text-center">Aucun entretien disponible.</td></tr>
                <?php else: ?>
                    <?php foreach ($entretiens as $entretien): ?>
                        <tr>
                            <td><?= (int) $entretien['id_entretien'] ?></td>
                            <td>
                                <strong><?= e((string) $entretien['nom_candidat']) ?></strong><br>
                                <small class="text-muted"><?= e((string) $entretien['email_candidat']) ?></small><br>
                                <small><?= e((string) $entretien['genre']) ?> | Handicap: <?= e((string) $entretien['type_handicap']) ?></small>
                            </td>
                            <td><?= e((string) ($entretien['type_entretien_libelle'] ?? '')) ?></td>
                            <td><?= e((string) $entretien['date_entretien']) ?></td>
                            <td><?= e(substr((string) $entretien['heure_entretien'], 0, 5)) ?></td>
                            <td><span class="<?= e(badgeClass((string) $entretien['statut'])) ?>"><?= e((string) $entretien['statut']) ?></span></td>
                            <td>
                                <a href="edit.php?id=<?= (int) $entretien['id_entretien'] ?>" class="btn btn-sm btn-info">Modifier</a>
                                <form method="post" action="list.php" style="display:inline-block;" onsubmit="return confirm('Confirmer la suppression de cet entretien ?');">
                                    <input type="hidden" name="delete_id" value="<?= (int) $entretien['id_entretien'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<footer class="footer-area"><div class="container"><div class="row text-center"><div class="col-lg-12 col-sm-12 col-xs-12 wow zoomIn"><p class="footer_copyright">Takwinibot &copy; 2026 All Rights Reserved. Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p></div></div></div></footer>

<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/jquery-1.12.4.min.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/modernizr-2.8.3.min.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/jquery.sticky.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/menu.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/wow.min.js"></script>
<script src="../../Hilux-1.0.0/Hilux-1.0.0/assets/js/scripts.js"></script>
</body>
</html>

