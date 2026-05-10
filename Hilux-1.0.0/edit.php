<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';
$pdo = config::getConnexion();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Identifiant invalide.';
    header('Location: index.php');
    exit;
}

$typesEntretien = ['présentiel', 'visioconférence', 'téléphonique', 'LST', 'hybride'];
$genres = ['homme', 'femme'];
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];

$selectStmt = $pdo->prepare('SELECT * FROM entretien WHERE id = :id');
$selectStmt->execute([':id' => $id]);
$entretien = $selectStmt->fetch();

if (!$entretien) {
    $_SESSION['flash_error'] = 'Entretien introuvable.';
    header('Location: index.php');
    exit;
}

$data = [
    'nom_candidat'      => (string) $entretien['nom_candidat'],
    'email_candidat'    => (string) $entretien['email_candidat'],
    'genre'             => (string) $entretien['genre'],
    'type_handicap'     => (string) ($entretien['type_handicap'] ?? ''),
    'amenagements'      => (string) ($entretien['amenagements'] ?? ''),
    'type_entretien_id' => (string) ($entretien['type_entretien_id'] ?? ''),
    'date_entretien'    => (string) $entretien['date_entretien'],
    'heure_entretien'   => substr((string) $entretien['heure_entretien'], 0, 5),
    'poste_cible'       => (string) $entretien['poste_cible'],
    'metier_suggere'    => (string) ($entretien['metier_suggere'] ?? ''),
    'score_rse'         => $entretien['score_rse'] !== null ? (string) $entretien['score_rse'] : '',
    'remarques'         => (string) ($entretien['remarques'] ?? ''),
    'statut'            => (string) $entretien['statut'],
    'has_handicap'      => ($entretien['type_handicap'] !== null && $entretien['type_handicap'] !== 'aucun') ? '1' : '0',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $data['has_handicap'] = isset($_POST['has_handicap']) ? '1' : '0';

    if ($data['nom_candidat'] === '') $errors['nom_candidat'] = 'Le nom du candidat est requis.';
    if ($data['email_candidat'] === '' || !filter_var($data['email_candidat'], FILTER_VALIDATE_EMAIL))
        $errors['email_candidat'] = 'Veuillez saisir un email valide.';
    if (!in_array($data['genre'], $genres, true)) $errors['genre'] = 'Genre invalide.';
    if ($data['has_handicap'] === '1' && $data['type_handicap'] === '')
        $errors['type_handicap'] = 'Veuillez préciser le type de handicap.';
    if ($data['type_entretien_id'] === '') $errors['type_entretien_id'] = "Veuillez sélectionner un type d'entretien.";
    if ($data['date_entretien'] === '') $errors['date_entretien'] = "La date est requise.";
    if ($data['heure_entretien'] === '') $errors['heure_entretien'] = "L'heure est requise.";
    if ($data['poste_cible'] === '') $errors['poste_cible'] = 'Le poste cible est requis.';
    if (!in_array($data['statut'], $statuts, true)) $errors['statut'] = 'Statut invalide.';

    $score = null;
    if ($data['score_rse'] !== '') {
        $score = filter_var($data['score_rse'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($score === false) $errors['score_rse'] = 'Le score RSE doit être entre 1 et 5.';
    }

    if (!$errors) {
        $updateStmt = $pdo->prepare('UPDATE entretien SET
            nom_candidat = :nom_candidat, email_candidat = :email_candidat, genre = :genre,
            type_handicap = :type_handicap, amenagements = :amenagements, type_entretien_id = :type_entretien_id,
            date_entretien = :date_entretien, heure_entretien = :heure_entretien, poste_cible = :poste_cible,
            metier_suggere = :metier_suggere, score_rse = :score_rse, remarques = :remarques, statut = :statut
            WHERE id = :id');

        $updateStmt->execute([
            ':nom_candidat'      => $data['nom_candidat'],
            ':email_candidat'    => $data['email_candidat'],
            ':genre'             => $data['genre'],
            ':type_handicap'     => $data['has_handicap'] === '1' ? $data['type_handicap'] : 'aucun',
            ':amenagements'      => $data['has_handicap'] === '1' && $data['amenagements'] !== '' ? $data['amenagements'] : null,
            ':type_entretien_id' => (int) $data['type_entretien_id'],
            ':date_entretien'    => $data['date_entretien'],
            ':heure_entretien'   => $data['heure_entretien'],
            ':poste_cible'       => $data['poste_cible'],
            ':metier_suggere'    => $data['metier_suggere'] !== '' ? $data['metier_suggere'] : null,
            ':score_rse'         => $score !== false && $score !== null ? (int) $score : null,
            ':remarques'         => $data['remarques'] !== '' ? $data['remarques'] : null,
            ':statut'            => $data['statut'],
            ':id'                => $id,
        ]);

        $_SESSION['flash_success'] = 'Entretien mis à jour avec succès.';
        header('Location: show.php?id=' . $id);
        exit;
    }
}

$assets = '/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/Hilux-1.0.0/assets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier l'entretien #<?= $id ?> - TakwiniBot</title>
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
                <h1>Modifier l'entretien #<?= $id ?></h1>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1 col-md-12">
                <div class="single_agent_info">

                    <?php if (isset($errors['global'])): ?>
                        <div class="alert alert-danger"><?= e($errors['global']) ?></div>
                    <?php endif; ?>

                    <form method="post" action="edit.php?id=<?= $id ?>">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="nom_candidat">Nom candidat *</label>
                                <input type="text" id="nom_candidat" name="nom_candidat" class="form-control" value="<?= e($data['nom_candidat']) ?>" data-hint="Veuillez entrer votre nom complet">
                                <?php if (isset($errors['nom_candidat'])): ?><small class="text-danger"><?= e($errors['nom_candidat']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email_candidat">Email candidat *</label>
                                <input type="email" id="email_candidat" name="email_candidat" class="form-control" value="<?= e($data['email_candidat']) ?>" data-hint="Entrez votre adresse email valide">
                                <?php if (isset($errors['email_candidat'])): ?><small class="text-danger"><?= e($errors['email_candidat']) ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="genre">Genre *</label>
                                <select id="genre" name="genre" class="form-control" data-hint="Sélectionnez votre genre">
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?= e($genre) ?>" <?= $data['genre'] === $genre ? 'selected' : '' ?>><?= e(ucfirst($genre)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['genre'])): ?><small class="text-danger"><?= e($errors['genre']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="type_entretien_id">Type d'entretien *</label>
                                <select id="type_entretien_id" name="type_entretien_id" class="form-control" data-hint="Choisissez le type d'entretien">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($typesEntretien as $i => $type): ?>
                                        <option value="<?= $i+1 ?>" <?= $data['type_entretien_id'] === (string)($i+1) ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['type_entretien_id'])): ?><small class="text-danger"><?= e($errors['type_entretien_id']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="statut">Statut *</label>
                                <select id="statut" name="statut" class="form-control" data-hint="Sélectionnez le statut de l'entretien">
                                    <?php foreach ($statuts as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $data['statut'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['statut'])): ?><small class="text-danger"><?= e($errors['statut']) ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><input type="checkbox" id="has_handicap" name="has_handicap" value="1" <?= $data['has_handicap'] === '1' ? 'checked' : '' ?>> Candidat en situation de handicap</label>
                        </div>

                        <div id="handicap-fields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="type_handicap">Type de handicap *</label>
                                    <input type="text" id="type_handicap" name="type_handicap" class="form-control" value="<?= e($data['type_handicap'] !== 'aucun' ? $data['type_handicap'] : '') ?>" data-hint="Précisez votre type de handicap">
                                    <?php if (isset($errors['type_handicap'])): ?><small class="text-danger"><?= e($errors['type_handicap']) ?></small><?php endif; ?>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="amenagements">Aménagements</label>
                                    <input type="text" id="amenagements" name="amenagements" class="form-control" value="<?= e($data['amenagements']) ?>" data-hint="Décrivez les aménagements nécessaires">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="date_entretien">Date entretien *</label>
                                <input type="date" id="date_entretien" name="date_entretien" class="form-control" value="<?= e($data['date_entretien']) ?>" data-hint="Sélectionnez la date de l'entretien">
                                <?php if (isset($errors['date_entretien'])): ?><small class="text-danger"><?= e($errors['date_entretien']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="heure_entretien">Heure entretien *</label>
                                <input type="time" id="heure_entretien" name="heure_entretien" class="form-control" value="<?= e($data['heure_entretien']) ?>" data-hint="Choisissez l'heure de l'entretien">
                                <?php if (isset($errors['heure_entretien'])): ?><small class="text-danger"><?= e($errors['heure_entretien']) ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="poste_cible">Poste cible *</label>
                                <input type="text" id="poste_cible" name="poste_cible" class="form-control" value="<?= e($data['poste_cible']) ?>" data-hint="Entrez le poste cible">
                                <?php if (isset($errors['poste_cible'])): ?><small class="text-danger"><?= e($errors['poste_cible']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="metier_suggere">Métier suggéré</label>
                                <input type="text" id="metier_suggere" name="metier_suggere" class="form-control" value="<?= e($data['metier_suggere']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="score_rse">Score RSE (1-5)</label>
                                <input type="number" id="score_rse" name="score_rse" min="1" max="5" class="form-control" value="<?= e($data['score_rse']) ?>" data-hint="Entrez un score entre 1 et 5">
                                <?php if (isset($errors['score_rse'])): ?><small class="text-danger"><?= e($errors['score_rse']) ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="remarques">Remarques</label>
                                <textarea id="remarques" name="remarques" rows="3" class="form-control" data-hint="Ajoutez vos remarques supplémentaires"><?= e($data['remarques']) ?></textarea>
                            </div>
                        </div>

                        <a href="show.php?id=<?= $id ?>" class="btn btn-default">Retour</a>
                        <button type="submit" class="btn btn-default" style="background:#30b5e1;color:#fff;">Mettre à jour</button>
                    </form>
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
<script>
(function () {
    const checkbox = document.getElementById('has_handicap');
    const fields = document.getElementById('handicap-fields');
    const handicapInput = document.getElementById('type_handicap');
    function toggleHandicap() {
        const active = checkbox.checked;
        fields.style.display = active ? 'block' : 'none';
        handicapInput.required = active;
    }
    checkbox.addEventListener('change', toggleHandicap);
    toggleHandicap();
})();
</script>
</body>
</html>
