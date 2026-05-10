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
$typeEntretiens = $controller->listTypeEntretiens();
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];
$genres = ['homme', 'femme'];

// Get flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
$lastEntretienId = $_SESSION['last_entretien_id'] ?? null;
$interviewLink = $_SESSION['interview_link'] ?? null;
$interviewToken = $_SESSION['interview_token'] ?? null;
unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);
unset($_SESSION['last_entretien_id']);
unset($_SESSION['interview_link']);
unset($_SESSION['interview_token']);

$data = [
    'nom_candidat' => '',
    'email_candidat' => '',
    'genre' => 'homme',
    'type_handicap' => '',
    'amenagements' => '',
    'type_entretien_id' => '',
    'date_entretien' => '',
    'heure_entretien' => '',
    'poste_cible' => '',
    'metier_suggere' => '',
    'score_rse' => '',
    'remarques' => '',
    'statut' => 'planifié',
    'has_handicap' => '0',
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
            null,
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

        $controller->addEntretien($entretien);
        $_SESSION['flash_success'] = 'Candidat enregistré avec succès!';
        
        // Get the last inserted ID
        $pdo = config::getConnexion();
        $sql = "SELECT id FROM entretien ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $lastId = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lastId) {
            $_SESSION['last_entretien_id'] = $lastId['id'];
        }
        
        // Reset form data
        $data = [
            'nom_candidat' => '',
            'email_candidat' => '',
            'genre' => 'homme',
            'type_handicap' => '',
            'amenagements' => '',
            'type_entretien_id' => '',
            'date_entretien' => '',
            'heure_entretien' => '',
            'poste_cible' => '',
            'metier_suggere' => '',
            'score_rse' => '',
            'remarques' => '',
            'statut' => 'planifié',
            'has_handicap' => '0',
        ];
        
        header('Location: add.php');
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
    <title>Ajouter un entretien - TakwiniBot</title>
    <link rel="stylesheet" href="../../assets-theme/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets-theme/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets-theme/fonts/themify-icons.css">
    <link rel="stylesheet" href="../../assets-theme/css/fonts.css">
    <link rel="stylesheet" href="../../assets-theme/css/menu.css">
    <link rel="stylesheet" href="../../assets-theme/css/style.css">
    <link rel="stylesheet" href="../../assets-theme/css/responsive.css">
    <style>
        /* Modal Styles */
        .suggestions-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .suggestions-modal.active {
            display: block;
        }

        .suggestions-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .suggestions-modal-content {
            position: relative;
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .suggestions-modal-header {
            padding: 20px 30px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #6f42c1 0%, #30b5e1 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .suggestions-modal-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .suggestions-modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: white;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .suggestions-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .suggestions-modal-body {
            padding: 30px;
        }

        .suggestions-intro {
            margin-bottom: 20px;
            color: #666;
            font-size: 16px;
        }

        .suggestions-cards {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .suggestion-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: #fafafa;
        }

        .suggestion-card:hover {
            border-color: #30b5e1;
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(48, 181, 225, 0.2);
        }

        .suggestion-card.selected {
            border-color: #6f42c1;
            background: #f5f0ff;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);
        }

        .suggestion-card-badge {
            position: absolute;
            top: -10px;
            left: 20px;
            background: linear-gradient(135deg, #6f42c1 0%, #30b5e1 100%);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .suggestion-card-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 10px 0 10px 0;
        }

        .suggestion-card-description {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        .suggestions-modal-footer {
            padding: 20px 30px;
            border-top: 2px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            background: #fafafa;
            border-radius: 0 0 12px 12px;
        }

        .btn-modal-cancel,
        .btn-modal-confirm {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-modal-cancel {
            background: #e0e0e0;
            color: #666;
        }

        .btn-modal-cancel:hover {
            background: #d0d0d0;
        }

        .btn-modal-confirm {
            background: linear-gradient(135deg, #6f42c1 0%, #30b5e1 100%);
            color: white;
        }

        .btn-modal-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.4);
        }

        .btn-modal-confirm:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .suggestions-modal-content {
                margin: 20px;
                max-width: calc(100% - 40px);
            }

            .suggestions-modal-header h3 {
                font-size: 20px;
            }

            .suggestion-card-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
<div class="preloader"><div class="status"><div class="status-mes"></div></div></div>
<div class="site-mobile-menu site-navbar-target"><div class="site-mobile-menu-header"><div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div></div><div class="site-mobile-menu-body"></div></div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
                <h1 class="mb-0 site-logo"><a href="../../index.html"><img src="../../assets-theme/img/logo.png" alt=""></a></h1>
            </div>
            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        <li><a class="nav-link" href="../../index.html">Home</a></li>
                        <li><a class="nav-link" href="../../about.html">about</a></li>
                        <li><a class="nav-link" href="../../formations/formation.html">Formations</a></li>
                        <li><a href="../../gallery.html">Produits</a></li>
                        <li><a href="add.php" class="nav-link">Entretien</a></li>
                        <li><a class="nav-link" href="../../offres.html">Offres</a></li>
                        <li><a class="nav-link" href="../../front_mes_reclamations.html">Réclamations</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;"><a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a></div>
        </div>
    </div>
</header>

<section class="section-top"><div class="container"><div class="col-lg-10 offset-lg-1 col-xs-12 text-center"><div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0"><h1>Ajouter un entretien</h1></div></div></div></section>

<section class="section-padding">
    <div class="container">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <i class="fa fa-check-circle"></i> <?= e((string) $flashSuccess) ?>
            </div>
            <?php if ($interviewLink): ?>
                <div class="alert alert-info" style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border: 1px solid #90caf9; border-radius: 5px;">
                    <strong>Interview Link:</strong><br>
                    <code style="word-break: break-all; display: block; margin-top: 10px; padding: 10px; background: white; border-radius: 3px;">
                        <?= e($interviewLink) ?>
                    </code>
                    <small style="display: block; margin-top: 10px; color: #666;">
                        Token: <code><?= e($interviewToken) ?></code>
                    </small>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa fa-exclamation-circle"></i> <?= e((string) $flashError) ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1 col-md-12">
                <div class="single_agent_info">
                    <form method="post" action="add.php" novalidate>
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
                            <div class="col-md-6 form-group">
                                <label for="metier_suggere">Métier suggéré</label>
                                <div class="input-group">
                                    <input type="text" id="metier_suggere" name="metier_suggere" class="form-control" value="<?= e($data['metier_suggere']) ?>">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" style="background:#1f3c88;color:#fff;" data-metier-suggest>
                                            Suggest with AI
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group"><label for="score_rse">Score RSE (1-5)</label><input type="number" id="score_rse" name="score_rse" class="form-control" min="1" max="5" value="<?= e($data['score_rse']) ?>"><?php if (isset($errors['score_rse'])): ?><small class="text-danger"><?= e($errors['score_rse']) ?></small><?php endif; ?></div>
                            <div class="col-md-6 form-group"><label for="remarques">Remarques</label><textarea id="remarques" name="remarques" rows="3" class="form-control"><?= e($data['remarques']) ?></textarea></div>
                        </div>

                        <a href="list.php" class="btn btn-default">Retour</a>
                        <button type="submit" class="btn btn-default" style="background:#30b5e1;color:#fff;">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer-area"><div class="container"><div class="row text-center"><div class="col-lg-12 col-sm-12 col-xs-12 wow zoomIn"><p class="footer_copyright">Takwinibot &copy; 2026 All Rights Reserved. Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p></div></div></div></footer>

<?php include __DIR__ . '/_metier_suggestion_modal.php'; ?>
<script src="../../assets-theme/js/jquery-1.12.4.min.js"></script>
<script src="../../assets-theme/bootstrap/js/bootstrap.min.js"></script>
<script src="../../assets-theme/js/modernizr-2.8.3.min.js"></script>
<script src="../../assets-theme/js/jquery.mixitup.js"></script>
<script src="../../assets-theme/js/jquery.prettyPhoto.js"></script>
<script src="../../assets-theme/js/jquery.magnific-popup.min.js"></script>
<script src="../../assets-theme/js/jquery.flexslider-min.js"></script>
<script src="../../assets-theme/js/jquery.mb.YTPlayer.min.js"></script>
<script src="../../assets-theme/owlcarousel/js/owl.carousel.min.js"></script>
<script src="../../assets-theme/js/slick.min.js"></script>
<script src="../../assets-theme/js/jquery.stellar.min.js"></script>
<script src="../../assets-theme/js/jquery.sticky.js"></script>
<script src="../../assets-theme/js/menu.js"></script>
<script src="../../assets-theme/js/wow.min.js"></script>
<script src="../../assets-theme/js/scripts.js"></script>
<script>
(function () {
    const checkbox = document.getElementById('has_handicap');
    const fields = document.getElementById('handicap-fields');
    const handicapInput = document.getElementById('type_handicap');

    function toggleHandicap() {
        const active = checkbox.checked;
        fields.style.display = active ? 'block' : 'none';
        if (!active) {
            handicapInput.value = 'aucun';
        }
    }

    checkbox.addEventListener('change', toggleHandicap);
    toggleHandicap();
})();
</script>
</body>
</html>

