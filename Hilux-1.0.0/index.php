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
        'terminé' => 'label label-success',
        'annulé' => 'label label-danger',
        default => 'label label-default',
    };
}

$genres = ['homme', 'femme'];
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];

$genre = $_GET['genre'] ?? '';
$statut = $_GET['statut'] ?? '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$parPage = 10;

if ($genre !== '' && !in_array($genre, $genres, true)) $genre = '';
if ($statut !== '' && !in_array($statut, $statuts, true)) $statut = '';

$conditions = [];
$params = [];
if ($genre !== '') { $conditions[] = 'genre = :genre'; $params[':genre'] = $genre; }
if ($statut !== '') { $conditions[] = 'statut = :statut'; $params[':statut'] = $statut; }
$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM entretien {$whereSql}");
foreach ($params as $key => $value) $countStmt->bindValue($key, $value);
$countStmt->execute();
$total = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $parPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $parPage;

$listStmt = $pdo->prepare("
    SELECT id, nom_candidat, email_candidat, genre, type_entretien_id,
           date_entretien, heure_entretien, poste_cible, score_rse, statut, type_handicap
    FROM entretien {$whereSql}
    ORDER BY date_entretien DESC, heure_entretien DESC, id DESC
    LIMIT :limite OFFSET :decalage
");
foreach ($params as $key => $value) $listStmt->bindValue($key, $value);
$listStmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
$listStmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
$listStmt->execute();
$entretiens = $listStmt->fetchAll();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$assets = '/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/assets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Entretiens - TakwiniBot</title>
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
                <h1>Gestion Entretiens</h1>
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

        <!-- Filtres -->
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="get" class="form-inline">
                    <div class="form-group mr-2">
                        <label class="mr-1">Genre :</label>
                        <select name="genre" class="form-control form-control-sm">
                            <option value="">Tous</option>
                            <?php foreach ($genres as $item): ?>
                                <option value="<?= e($item) ?>" <?= $genre === $item ? 'selected' : '' ?>><?= e(ucfirst($item)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-1">Statut :</label>
                        <select name="statut" class="form-control form-control-sm">
                            <option value="">Tous</option>
                            <?php foreach ($statuts as $item): ?>
                                <option value="<?= e($item) ?>" <?= $statut === $item ? 'selected' : '' ?>><?= e(ucfirst($item)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-default" style="background:#30b5e1;color:#fff;">Filtrer</button>
                    <a href="index.php" class="btn btn-sm btn-default ml-1">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-8"><h2>Liste des entretiens <small class="text-muted">(<?= $total ?> résultat(s))</small></h2></div>
            <div class="col-md-4 text-right">
                <a href="create.php" class="btn btn-default btn-lg" style="background:#30b5e1;color:#fff;">+ Ajouter un entretien</a>
            </div>
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
                        <th>Poste cible</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($entretiens)): ?>
                    <tr><td colspan="8" class="text-center">Aucun entretien disponible.</td></tr>
                <?php else: ?>
                    <?php foreach ($entretiens as $entretien): ?>
                        <tr>
                            <td><?= (int) $entretien['id'] ?></td>
                            <td>
                                <strong><?= e($entretien['nom_candidat']) ?></strong><br>
                                <small class="text-muted"><?= e($entretien['email_candidat']) ?></small><br>
                                <small><?= e(ucfirst($entretien['genre'])) ?> | Handicap: <?= e($entretien['type_handicap']) ?></small>
                            </td>
                            <td><?= e((string) $entretien['type_entretien_id']) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($entretien['date_entretien']))) ?></td>
                            <td><?= e(substr($entretien['heure_entretien'], 0, 5)) ?></td>
                            <td><?= e($entretien['poste_cible']) ?></td>
                            <td><span class="<?= e(badgeClass($entretien['statut'])) ?>"><?= e(ucfirst($entretien['statut'])) ?></span></td>
                            <td>
                                <a href="show.php?id=<?= (int) $entretien['id'] ?>" class="btn btn-sm btn-default">Voir</a>
                                <a href="edit.php?id=<?= (int) $entretien['id'] ?>" class="btn btn-sm btn-info">Modifier</a>
                                <form method="post" action="delete.php?id=<?= (int) $entretien['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Supprimer cet entretien ?');">
                                    <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                    <input type="hidden" name="return_genre" value="<?= e($genre) ?>">
                                    <input type="hidden" name="return_statut" value="<?= e($statut) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination">
                    <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                        <a href="<?= $page > 1 ? 'index.php?page=' . ($page-1) . ($genre ? '&genre='.e($genre) : '') . ($statut ? '&statut='.e($statut) : '') : '#' ?>">«</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?= $i === $page ? 'active' : '' ?>">
                            <a href="index.php?page=<?= $i ?><?= $genre ? '&genre='.e($genre) : '' ?><?= $statut ? '&statut='.e($statut) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="<?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a href="<?= $page < $totalPages ? 'index.php?page=' . ($page+1) . ($genre ? '&genre='.e($genre) : '') . ($statut ? '&statut='.e($statut) : '') : '#' ?>">»</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

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
