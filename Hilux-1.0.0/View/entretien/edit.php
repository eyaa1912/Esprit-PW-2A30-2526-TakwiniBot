<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../Controller/EntretienController.php';
require_once __DIR__ . '/../../Model/Entretien.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$controller = new \EntretienController();
$typeEntretiens = $controller->listTypeEntretiens()->fetchAll();
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];
$genres = ['homme', 'femme'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Entretien invalide.';
    header('Location: list.php');
    exit;
}

$current = $controller->getEntretien($id);
if (!$current) {
    $_SESSION['flash_error'] = 'Entretien introuvable.';
    header('Location: list.php');
    exit;
}

$data = [
    'nom_candidat' => (string) $current['nom_candidat'],
    'email_candidat' => (string) $current['email_candidat'],
    'genre' => (string) $current['genre'],
    'type_handicap' => (string) $current['type_handicap'],
    'amenagements' => (string) ($current['amenagements'] ?? ''),
    'type_entretien_id' => (string) $current['type_entretien_id'],
    'date_entretien' => (string) $current['date_entretien'],
    'heure_entretien' => substr((string) $current['heure_entretien'], 0, 5),
    'poste_cible' => (string) $current['poste_cible'],
    'metier_suggere' => (string) ($current['metier_suggere'] ?? ''),
    'score_rse' => $current['score_rse'] !== null ? (string) $current['score_rse'] : '',
    'remarques' => (string) ($current['remarques'] ?? ''),
    'statut' => (string) $current['statut'],
    'has_handicap' => ((string) $current['type_handicap'] !== '' && (string) $current['type_handicap'] !== 'aucun') ? '1' : '0',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    $data['has_handicap'] = isset($_POST['has_handicap']) ? '1' : '0';

    if ($data['nom_candidat'] === '') {
        $errors['nom_candidat'] = 'Le nom du candidat est requis.';
    }
    if ($data['email_candidat'] === '' || !filter_var($data['email_candidat'], FILTER_VALIDATE_EMAIL)) {
        $errors['email_candidat'] = 'Veuillez saisir un email valide.';
    }
    if (!in_array($data['genre'], $genres, true)) {
        $errors['genre'] = 'Veuillez sélectionner un genre valide.';
    }
    if ($data['has_handicap'] === '1' && $data['type_handicap'] === '') {
        $errors['type_handicap'] = 'Veuillez préciser le type de handicap.';
    }
    if ((int) $data['type_entretien_id'] <= 0) {
        $errors['type_entretien_id'] = "Veuillez sélectionner un type d'entretien.";
    }
    if ($data['date_entretien'] === '') {
        $errors['date_entretien'] = "La date de l'entretien est requise.";
    }
    if ($data['heure_entretien'] === '') {
        $errors['heure_entretien'] = "L'heure de l'entretien est requise.";
    }
    if ($data['poste_cible'] === '') {
        $errors['poste_cible'] = 'Le poste cible est requis.';
    }
    if (!in_array($data['statut'], $statuts, true)) {
        $errors['statut'] = 'Veuillez sélectionner un statut valide.';
    }

    $score = null;
    if ($data['score_rse'] !== '') {
        $score = filter_var($data['score_rse'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($score === false) {
            $errors['score_rse'] = 'Le score RSE doit être compris entre 1 et 5.';
        }
    }

    if (!$errors) {
        $entretien = new \Entretien(
            $id,
            $data['nom_candidat'],
            $data['email_candidat'],
            $data['genre'],
            $data['has_handicap'] === '1' ? $data['type_handicap'] : 'aucun',
            $data['has_handicap'] === '1' && $data['amenagements'] !== '' ? $data['amenagements'] : null,
            (int) $data['type_entretien_id'],
            $data['date_entretien'],
            $data['heure_entretien'],
            $data['poste_cible'],
            $data['metier_suggere'] !== '' ? $data['metier_suggere'] : null,
            $score !== false ? ($score !== null ? (int) $score : null) : null,
            $data['remarques'] !== '' ? $data['remarques'] : null,
            $data['statut']
        );

        $controller->updateEntretien($id, $entretien);
        $_SESSION['flash_success'] = 'Entretien mis à jour avec succès.';
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un entretien - TakwiniBot</title>
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

<section class="section-top"><div class="container"><div class="col-lg-10 offset-lg-1 col-xs-12 text-center"><div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0"><h1>Modifier l'entretien #<?= (int) $id ?></h1></div></div></div></section>

<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1 col-md-12">
                <div class="single_agent_info">
                    <form method="post" action="edit.php?id=<?= (int) $id ?>">
                        <div class="row">
                            <div class="col-md-6 form-group"><label for="nom_candidat">Nom candidat *</label><input type="text" id="nom_candidat" name="nom_candidat" class="form-control" value="<?= e($data['nom_candidat']) ?>"><?php if (isset($errors['nom_candidat'])): ?><small class="text-danger"><?= e($errors['nom_candidat']) ?></small><?php endif; ?></div>
                            <div class="col-md-6 form-group"><label for="email_candidat">Email candidat *</label><input type="email" id="email_candidat" name="email_candidat" class="form-control" value="<?= e($data['email_candidat']) ?>"><?php if (isset($errors['email_candidat'])): ?><small class="text-danger"><?= e($errors['email_candidat']) ?></small><?php endif; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group"><label for="genre">Genre *</label><select id="genre" name="genre" class="form-control"><?php foreach ($genres as $genre): ?><option value="<?= e($genre) ?>" <?= $data['genre'] === $genre ? 'selected' : '' ?>><?= e(ucfirst($genre)) ?></option><?php endforeach; ?></select><?php if (isset($errors['genre'])): ?><small class="text-danger"><?= e($errors['genre']) ?></small><?php endif; ?></div>
                            <div class="col-md-4 form-group"><label for="type_entretien_id">Type d'entretien *</label><select id="type_entretien_id" name="type_entretien_id" class="form-control"><option value="">Sélectionner</option><?php foreach ($typeEntretiens as $type): ?><option value="<?= (int) $type['id_type_entretien'] ?>" <?= (string) $data['type_entretien_id'] === (string) $type['id_type_entretien'] ? 'selected' : '' ?>><?= e((string) ($type['nom'] ?? $type['libelle'])) ?></option><?php endforeach; ?></select><?php if (isset($errors['type_entretien_id'])): ?><small class="text-danger"><?= e($errors['type_entretien_id']) ?></small><?php endif; ?></div>
                            <div class="col-md-4 form-group"><label for="statut">Statut *</label><select id="statut" name="statut" class="form-control"><?php foreach ($statuts as $statut): ?><option value="<?= e($statut) ?>" <?= $data['statut'] === $statut ? 'selected' : '' ?>><?= e($statut) ?></option><?php endforeach; ?></select><?php if (isset($errors['statut'])): ?><small class="text-danger"><?= e($errors['statut']) ?></small><?php endif; ?></div>
                        </div>

                        <div class="form-group"><label><input type="checkbox" id="has_handicap" name="has_handicap" value="1" <?= $data['has_handicap'] === '1' ? 'checked' : '' ?>> Candidat en situation de handicap</label></div>

                        <div id="handicap-fields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 form-group"><label for="type_handicap">Type de handicap *</label><input type="text" id="type_handicap" name="type_handicap" class="form-control" value="<?= e($data['type_handicap']) ?>"><?php if (isset($errors['type_handicap'])): ?><small class="text-danger"><?= e($errors['type_handicap']) ?></small><?php endif; ?></div>
                                <div class="col-md-6 form-group"><label for="amenagements">Aménagements</label><input type="text" id="amenagements" name="amenagements" class="form-control" value="<?= e($data['amenagements']) ?>"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group"><label for="date_entretien">Date entretien *</label><input type="date" id="date_entretien" name="date_entretien" class="form-control" value="<?= e($data['date_entretien']) ?>"><?php if (isset($errors['date_entretien'])): ?><small class="text-danger"><?= e($errors['date_entretien']) ?></small><?php endif; ?></div>
                            <div class="col-md-6 form-group"><label for="heure_entretien">Heure entretien *</label><input type="time" id="heure_entretien" name="heure_entretien" class="form-control" value="<?= e($data['heure_entretien']) ?>"><?php if (isset($errors['heure_entretien'])): ?><small class="text-danger"><?= e($errors['heure_entretien']) ?></small><?php endif; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group"><label for="poste_cible">Poste cible *</label><input type="text" id="poste_cible" name="poste_cible" class="form-control" value="<?= e($data['poste_cible']) ?>"><?php if (isset($errors['poste_cible'])): ?><small class="text-danger"><?= e($errors['poste_cible']) ?></small><?php endif; ?></div>
                            <div class="col-md-6 form-group"><label for="metier_suggere">Métier suggéré</label><input type="text" id="metier_suggere" name="metier_suggere" class="form-control" value="<?= e($data['metier_suggere']) ?>"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group"><label for="score_rse">Score RSE</label><input type="number" id="score_rse" name="score_rse" min="1" max="5" class="form-control" value="<?= e($data['score_rse']) ?>"><?php if (isset($errors['score_rse'])): ?><small class="text-danger"><?= e($errors['score_rse']) ?></small><?php endif; ?></div>
                            <div class="col-md-6 form-group"><label for="remarques">Remarques</label><textarea id="remarques" name="remarques" rows="3" class="form-control"><?= e($data['remarques']) ?></textarea></div>
                        </div>

                        <a href="list.php" class="btn btn-default">Retour</a>
                        <button type="submit" class="btn btn-default" style="background:#30b5e1;color:#fff;">Mettre à jour</button>
                    </form>
                </div>
            </div>
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
<script>
(function () {
    const checkbox = document.getElementById('has_handicap');
    const fields = document.getElementById('handicap-fields');
    const handicapInput = document.getElementById('type_handicap');

    function toggleHandicap() {
        const active = checkbox.checked;
        fields.style.display = active ? 'block' : 'none';
        handicapInput.required = active;
        if (!active && handicapInput.value === '') {
            handicapInput.value = 'aucun';
        }
    }

    checkbox.addEventListener('change', toggleHandicap);
    toggleHandicap();
})();
</script>
</body>
</html>
