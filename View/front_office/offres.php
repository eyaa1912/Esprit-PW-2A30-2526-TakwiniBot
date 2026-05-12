<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/OffreController.php';
require_once __DIR__ . '/../../Controller/ContratController.php';
require_once __DIR__ . '/../../Controller/PostulerController.php';

$controller       = new OffreController();
// Supprimer les offres expirées avant d'afficher
$controller->deleteExpiredOffres();
$offres           = $controller->listOffres()->fetchAll();
$contratStats     = ['count' => 0, 'highlight_new' => false];
$derniersContrats = [];
try {
    $cc               = new ContratController();
    $contratStats     = $cc->getPublicStats();
    $derniersContrats = $cc->getLatestContrats(4);
} catch (Throwable $e) {
    // Décommenter pour déboguer : echo $e->getMessage();
}

// ── Traitement du formulaire Postuler ──
$flashSuccess = null;
$flashError   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'postuler') {
    $nom     = trim($_POST['nom']     ?? '');
    $prenom  = trim($_POST['prenom']  ?? '');
    $email   = trim($_POST['email']   ?? '');
    $offreId = (int)($_POST['offre_id'] ?? 0);

    if ($nom === '' || $prenom === '' || $email === '') {
        $flashError = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flashError = 'Adresse email invalide.';
    } elseif ($offreId === 0) {
        $flashError = 'Offre invalide — veuillez réessayer en cliquant sur "Postuler" depuis la liste des offres.';
    } elseif (empty($_FILES['cv']['name'])) {
        $flashError = 'Veuillez joindre votre CV.';
    } else {
        $ext     = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];
        if (!in_array($ext, $allowed)) {
            $flashError = 'Format CV invalide. Acceptés : PDF, DOC, DOCX.';
        } else {
            $uploadDir = __DIR__ . '/../../uploads/cv/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'cv_' . time() . '_' . uniqid() . '.' . $ext;
            $cvPath   = 'uploads/cv/' . $filename;

            if (move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $filename)) {
                require_once __DIR__ . '/../../Controller/PostulerController.php';
                $postuler = new Postuler($nom, $prenom, $email, $offreId, $cvPath);
                $postCtrl = new PostulerController();
                $res      = $postCtrl->addPostuler($postuler);
                $flashSuccess = 'Votre candidature a été envoyée avec succès !';
            } else {
                $flashError = 'Erreur lors de l\'upload du CV.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Takwinibot - Offres</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link href="assets/css/prettyPhoto.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <!-- PDF.js pour extraction texte côté navigateur -->
    <script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
            }
        });
    </script>
    <!-- Mammoth.js pour extraction DOCX -->
    <script src="https://unpkg.com/mammoth@1.6.0/mammoth.browser.min.js"></script>
    <link rel="stylesheet" href="assets/css/accessibility.css">
</head>
<body data-spy="scroll" data-offset="80">

<!-- PRELOADER -->
<div class="preloader">
    <div class="status"><div class="status-mes"></div></div>
</div>

<!-- NAVBAR -->
<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
            <span class="icon-close2 js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
                <h1 class="mb-0 site-logo">
                    <a href="index.php"><img src="assets/img/logo.png" alt=""></a>
                </h1>
            </div>
            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        <li><a href="index.php" class="nav-link">Accueil</a></li>
                        <li><a class="nav-link" href="about.html">À propos</a></li>
                        <li><a href="formation.html" class="nav-link">Formations</a></li>
                        <li><a href="blog.html" class="nav-link">Entretiens</a></li>
                        <li class="active"><a class="nav-link" href="offres.php">Offres</a></li>
                        <li class="nav-reclamation-login">
                            <a class="nav-link" href="front_formulaire_reclamation.html">Contact</a>
                            <a href="login.php" class="login-pill">
                                <?php if (!empty($_SESSION['user_id'])): ?>
                                    <?= htmlspecialchars($_SESSION['user_nom']) ?>
                                <?php else: ?>
                                    Se connecter
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;">
                <a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
            </div>
        </div>
    </div>
</header>

<!-- TITLE -->
<section class="section-top" style="padding:60px 0;background:#f9f9fc;">
    <div class="container text-center">
        <h1>Nos Offres d'Emploi</h1>
    </div>
</section>

<style>
.job-card { transition: all 0.3s ease; }
.job-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); background:#fff; border-radius:8px; }
.contrats-live-counter { display:inline-flex; align-items:center; gap:10px; margin-top:16px; padding:10px 18px; border-radius:999px; background:#fff; border:1px solid #e8e6ff; box-shadow:0 2px 12px rgba(139,131,246,.12); transition:box-shadow .3s,transform .2s; }
.contrats-live-counter--flash { animation:contratCounterFlash 1.1s ease-in-out 2; }
@keyframes contratCounterFlash { 0%,100%{box-shadow:0 2px 12px rgba(139,131,246,.12);transform:scale(1)} 50%{box-shadow:0 0 0 4px rgba(139,131,246,.35);transform:scale(1.02)} }
.contrat-count-num { font-weight:700; font-size:1.35rem; color:#5b52d6; }
.badge-nouveau-contrat { font-size:11px; font-weight:700; letter-spacing:.04em; animation:badgePulse 1.5s ease-in-out infinite; }
@keyframes badgePulse { 0%,100%{opacity:1} 50%{opacity:.65} }
/* Validation formulaire Postuler */
#modalPostuler .invalid-feedback { display:none; color:#dc3545; font-size:13px; margin-top:4px; }
#modalPostuler .is-invalid { border-color:#dc3545 !important; }
#modalPostuler .is-invalid ~ .invalid-feedback { display:block; }
</style>

<!-- OFFRES LIST -->
<section class="offres-section" style="padding:60px 0;background:#f9f9fc;">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-4"></div>
            <div class="col-lg-9 col-md-8">
                <?php if (!empty($offres)) : ?>
                    <?php foreach ($offres as $offre) : ?>
                        <div class="job-card" style="background:transparent;margin-bottom:20px;padding:25px 0;border-bottom:1px solid #ebebeb;display:flex;align-items:center;justify-content:space-between;">
                            <div class="job-info d-flex align-items-center">
                                <div class="job-logo mr-4" style="width:80px;height:80px;border:1px solid #e1e1e1;display:flex;justify-content:center;align-items:center;background:#fff;border-radius:8px;overflow:hidden;">
                                    <?php if (!empty($offre['image'])): ?>
                                        <img src="../../<?= htmlspecialchars($offre['image']) ?>" style="width:80px;height:80px;object-fit:cover;" alt="<?= htmlspecialchars($offre['titre']) ?>">
                                    <?php else: ?>
                                        <img src="assets/img/offres/logo1.jpg" style="max-width:60px;" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="job-details">
                                    <h3 style="font-size:20px;font-weight:500;margin-bottom:8px;color:#333;"><?= htmlspecialchars($offre['titre']) ?></h3>
                                    <div style="color:#888;font-size:14px;" data-type-badge="<?= htmlspecialchars($offre['type']) ?>"><?= htmlspecialchars($offre['type']) ?></div>
                                    <div class="job-description" style="color:#aaa;font-size:13px;"><?= htmlspecialchars($offre['description']) ?></div>
                                    <button class="btn-voir-plus" style="display:none;background:none;border:none;color:#7c3aed;font-size:12px;padding:4px 0;cursor:pointer;text-decoration:underline;">Voir la description ▼</button>
                                </div>
                            </div>
                            <div class="job-action text-right">
                                <button type="button"
                                    class="btn btn-outline-primary mb-2"
                                    style="border-radius:20px;border-color:#8b83f6;color:#8b83f6;"
                                    data-toggle="modal"
                                    data-target="#modalPostuler"
                                    data-offre-id="<?= (int)$offre['id'] ?>"
                                    data-offre-titre="<?= htmlspecialchars($offre['titre'], ENT_QUOTES) ?>">
                                    Postuler
                                </button>
                                <p style="margin:0;color:#888;font-size:13px;">Expire le : <?= htmlspecialchars($offre['dateExpiration'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>Aucune offre disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<?php if (!empty($derniersContrats)) :
    $salaires = []; $durees = [];
    foreach ($derniersContrats as $dc) {
        $s = (float)(preg_replace('/[^0-9.]/ ', '', explode(' ', $dc['salaire'] ?? '0')[0]) ?: 0);
        if ($s > 0) $salaires[] = $s;
        $d = (int)(preg_replace('/[^0-9]/', '', $dc['duree'] ?? '0') ?: 0);
        if ($d > 0) $durees[] = $d;
    }
    $salMoyen  = count($salaires) ? round(array_sum($salaires) / count($salaires)) : 0;
    $dureeMoy  = count($durees)   ? round(array_sum($durees)   / count($durees))   : 0;
    $devArr    = array_unique(array_map(fn($dc) => trim(explode(' ', $dc['salaire'] ?? 'DZD')[1] ?? 'DZD'), $derniersContrats));
    $deviseAff = count($devArr) === 1 ? $devArr[0] : 'DZD';
?>
<section id="derniers-contrats" style="padding:70px 0 80px;background:#f3f4f6;">
    <div class="container">

        <!-- Titre -->
        <div class="text-center mb-5">
            <h2 style="font-family:'Playfair Display',Georgia,serif;font-size:2rem;font-weight:700;color:#1f1f2e;margin-bottom:8px;">
                Derniers Contrats Effectués
            </h2>
            <div style="width:50px;height:3px;background:#7c3aed;margin:0 auto 16px;border-radius:2px;"></div>
        </div>

        <!-- 2 stats -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-3 col-sm-6 mb-3">
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px 20px;text-align:center;box-shadow:0 2px 12px rgba(124,58,237,.07);">
                    <div style="font-size:1.75rem;font-weight:700;color:#7c3aed;font-family:'DM Sans',sans-serif;">
                        <?= number_format($salMoyen, 0, ',', ' ') ?> <?= htmlspecialchars($deviseAff) ?>
                    </div>
                    <div style="font-size:.85rem;color:#6b7280;margin-top:6px;font-family:'DM Sans',sans-serif;">Salaire moyen</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px 20px;text-align:center;box-shadow:0 2px 12px rgba(124,58,237,.07);">
                    <div style="font-size:1.75rem;font-weight:700;color:#7c3aed;font-family:'DM Sans',sans-serif;">
                        <?= $dureeMoy ?> mois
                    </div>
                    <div style="font-size:.85rem;color:#6b7280;margin-top:6px;font-family:'DM Sans',sans-serif;">Durée moyenne</div>
                </div>
            </div>
        </div>

        <!-- 4 cartes -->
        <div class="row">
            <?php foreach ($derniersContrats as $dc) :
                $typeOffre   = strtoupper(trim($dc['offre_type'] ?? 'CDI'));
                $badgeColors = ['CDI'=>'#16a34a','CDD'=>'#d97706','STAGE'=>'#2563eb','FREELANCE'=>'#7c3aed'];
                $badgeColor  = $badgeColors[$typeOffre] ?? '#6b7280';
                $scMap       = ['actif'=>['bg'=>'#dcfce7','txt'=>'#15803d'],'expiré'=>['bg'=>'#fef9c3','txt'=>'#a16207'],'annulé'=>['bg'=>'#fee2e2','txt'=>'#b91c1c']];
                $sc          = $scMap[$dc['statut'] ?? 'actif'] ?? ['bg'=>'#f3f4f6','txt'=>'#374151'];
                $durNum      = (int)(preg_replace('/[^0-9]/', '', $dc['duree'] ?? '0') ?: 0);
                $durMois     = stripos($dc['duree'] ?? '', 'an') !== false ? $durNum * 12 : $durNum;
                $pct         = min(100, $durMois > 0 ? round($durMois / 36 * 100) : 0);
                $salParts    = explode(' ', $dc['salaire'] ?? '0 DZD');
                $salNum      = number_format((float)($salParts[0] ?? 0), 0, ',', ' ');
                $salDev      = $salParts[1] ?? 'DZD';
            ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;height:100%;box-shadow:0 2px 16px rgba(0,0,0,.06);transition:transform .2s,box-shadow .2s;"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 28px rgba(124,58,237,.15)'"
                     onmouseout="this.style.transform='';this.style.boxShadow='0 2px 16px rgba(0,0,0,.06)'">

                    <!-- Badges -->
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <span style="background:<?= $badgeColor ?>;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;font-family:'DM Sans',sans-serif;letter-spacing:.04em;">
                            <?= htmlspecialchars($typeOffre) ?>
                        </span>
                        <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['txt'] ?>;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;font-family:'DM Sans',sans-serif;">
                            <?= htmlspecialchars(ucfirst($dc['statut'] ?? 'actif')) ?>
                        </span>
                    </div>

                    <!-- Titre poste -->
                    <h3 style="font-family:'Playfair Display',Georgia,serif;font-size:1.05rem;font-weight:700;color:#1f1f2e;margin-bottom:4px;line-height:1.35;">
                        <?= htmlspecialchars($dc['offre_titre'] ?? '—') ?>
                    </h3>

                    <!-- Réf offre -->
                    <p style="font-size:.8rem;color:#9ca3af;margin-bottom:16px;font-family:'DM Sans',sans-serif;">
                        Réf. offre #<?= (int)$dc['offre_id'] ?>
                    </p>

                    <!-- Salaire -->
                    <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:14px;">
                        <span style="font-size:1.25rem;font-weight:700;color:#7c3aed;font-family:'DM Sans',sans-serif;"><?= $salNum ?></span>
                        <span style="font-size:.8rem;color:#6b7280;font-family:'DM Sans',sans-serif;"><?= htmlspecialchars($salDev) ?>/mois</span>
                    </div>

                    <!-- Durée + barre -->
                    <div style="margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                            <span style="font-size:.8rem;color:#6b7280;font-family:'DM Sans',sans-serif;">Durée</span>
                            <span style="font-size:.8rem;font-weight:600;color:#374151;font-family:'DM Sans',sans-serif;"><?= htmlspecialchars($dc['duree'] ?? '—') ?></span>
                        </div>
                        <div style="background:#ede9fe;border-radius:999px;height:6px;overflow:hidden;">
                            <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,#7c3aed,#a78bfa);border-radius:999px;"></div>
                        </div>
                    </div>

                    <!-- Date signature -->
                    <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:#9ca3af;font-family:'DM Sans',sans-serif;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Signé le <?= htmlspecialchars($dc['dateCreation'] ?? '—') ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>
<!-- ===== FIN DERNIERS CONTRATS ===== -->

<!-- MODAL POSTULER -->
<div class="modal fade" id="modalPostuler" tabindex="-1" role="dialog" aria-labelledby="modalPostulerLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:16px;">
      <form method="POST" enctype="multipart/form-data" id="formPostuler" novalidate>
        <input type="hidden" name="action" value="postuler">
        <input type="hidden" name="offre_id" id="modal-offre-id">
        <div class="modal-header" style="border-bottom:1px solid #f0f0f0;">
          <h5 class="modal-title" id="modalPostulerLabel" style="font-family:'Playfair Display',serif;color:#1f1f2e;">
            Postuler — <span id="modal-offre-titre"></span>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Indicateur de progression (mode concentration uniquement) -->
        <div id="form-progress" style="display:none;padding:14px 20px 0;background:#f8f9fa;border-bottom:1px solid #e5e7eb;">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
            <div id="dot-1" style="width:30px;height:30px;border-radius:50%;background:#7c3aed;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">1</div>
            <div style="flex:1;height:3px;background:#e5e7eb;border-radius:2px;"><div id="fill-1-2" style="height:100%;background:#7c3aed;width:0%;transition:width .4s;border-radius:2px;"></div></div>
            <div id="dot-2" style="width:30px;height:30px;border-radius:50%;background:#e5e7eb;color:#aaa;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">2</div>
            <div style="flex:1;height:3px;background:#e5e7eb;border-radius:2px;"><div id="fill-2-3" style="height:100%;background:#7c3aed;width:0%;transition:width .4s;border-radius:2px;"></div></div>
            <div id="dot-3" style="width:30px;height:30px;border-radius:50%;background:#e5e7eb;color:#aaa;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">3</div>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:11px;color:#888;margin-bottom:8px;">
            <span>Identité</span><span style="margin-left:20px;">Contact</span><span>CV</span>
          </div>
          <p id="step-label" style="font-size:13px;font-weight:600;color:#7c3aed;margin:0 0 8px;"></p>
        </div>

        <div class="modal-body" style="padding:28px;">
          <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
          <?php endif; ?>
          <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
          <?php endif; ?>

          <!-- ÉTAPE 1 : Identité -->
          <div class="form-step" id="step-1">
            <p class="msg-anxiete" style="display:none;font-size:13px;color:#2563eb;font-weight:500;margin-bottom:12px;">Bienvenue ! Ce formulaire est simple et rapide. Commençons par votre nom.</p>
            <div class="form-group">
              <label class="font-weight-bold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="nom" id="postuler-nom" class="form-control" placeholder="Votre nom">
              <div class="invalid-feedback" id="postuler-nom-error"></div>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Prénom <span class="text-danger">*</span></label>
              <input type="text" name="prenom" id="postuler-prenom" class="form-control" placeholder="Votre prénom">
              <div class="invalid-feedback" id="postuler-prenom-error"></div>
            </div>
          </div>

          <!-- ÉTAPE 2 : Contact (masquée par défaut) -->
          <div class="form-step" id="step-2" style="display:none;">
            <div class="form-group">
              <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" id="postuler-email" class="form-control" placeholder="votre@email.com">
              <div class="invalid-feedback" id="postuler-email-error"></div>
            </div>
            <p class="msg-normal" style="font-size:13px;color:#6b7280;margin-top:8px;">Votre email sera utilisé uniquement pour vous contacter au sujet de votre candidature.</p>
            <p class="msg-anxiete" style="display:none;font-size:13px;color:#2563eb;font-weight:500;margin-top:8px;">Très bien ! Une seule information encore — votre email pour qu'on puisse vous recontacter.</p>
          </div>

          <!-- ÉTAPE 3 : CV (masquée par défaut) -->
          <div class="form-step" id="step-3" style="display:none;">
            <div class="form-group">
              <label class="font-weight-bold">CV <span class="text-danger">*</span></label>
              <input type="file" name="cv" id="postuler-cv" class="form-control-file mt-1" accept=".pdf,.doc,.docx">
              <div class="invalid-feedback" id="postuler-cv-error"></div>
              <small class="text-muted">Formats acceptés : PDF, DOC, DOCX — max 5 Mo</small>
            </div>
            <p class="msg-normal" style="font-size:13px;color:#10b981;font-weight:600;margin-top:8px;">Dernière étape — votre candidature sera envoyée après l'upload de votre CV.</p>
            <p class="msg-anxiete" style="display:none;font-size:13px;color:#2563eb;font-weight:600;margin-top:8px;">Presque terminé ! Joignez votre CV et votre candidature sera envoyée. Vous faites très bien.</p>
          </div>

          <!-- Zone analyse IA -->
          <div id="cv-analyse-zone" style="display:none;margin-top:16px;">
            <div id="cv-analyse-loading" style="display:none;text-align:center;padding:16px;">
              <div class="spinner-border spinner-border-sm" role="status" style="color:#2c3e50;"></div>
              <span style="margin-left:8px;font-size:13px;color:#555;">Analyse de votre CV en cours...</span>
            </div>
            <div id="cv-analyse-result" style="display:none;">
              <div style="border:1px solid #dde3ea;border-radius:6px;overflow:hidden;margin-top:8px;">
                <div style="background:#2c3e50;padding:10px 16px;">
                  <span style="color:#fff;font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;">Rapport d'analyse — CV</span>
                </div>
                <div id="cv-analyse-content" style="padding:16px;font-size:13px;line-height:1.8;color:#2c3e50;max-height:320px;overflow-y:auto;background:#fff;"></div>
              </div>
            </div>
            <div id="cv-analyse-error" style="display:none;font-size:13px;color:#666;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:10px 14px;margin-top:8px;"></div>
          </div>
        </div>

        <div class="modal-footer" style="border-top:1px solid #f0f0f0;justify-content:space-between;">
          <button type="button" id="btn-prev-step" class="btn btn-outline-secondary" style="display:none;">← Retour</button>
          <div>
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Annuler</button>
            <button type="button" id="btn-next-step" class="btn btn-primary" style="background:#7c3aed;border-color:#7c3aed;margin-left:8px;display:none;">
              Suivant →
            </button>
            <button type="submit" id="btn-soumettre" class="btn btn-primary" style="background:#7c3aed;border-color:#7c3aed;margin-left:8px;">
              <span id="btn-soumettre-text">Envoyer ma candidature</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- WIDGET ACCESSIBILITÉ -->
<?php include __DIR__ . '/includes/accessibility.php'; ?>

<!-- FOOTER -->
<footer class="footer-area">
    <div class="container">
        <p class="text-center">Takwinibot &copy; 2026</p>
    </div>
</footer>

<!-- SCRIPTS -->
<script src="assets/js/jquery-1.12.4.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/modernizr-2.8.3.min.js"></script>
<script src="assets/js/jquery.stellar.min.js"></script>
<script src="assets/owlcarousel/js/owl.carousel.min.js"></script>
<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script src="assets/js/slick.min.js"></script>
<script src="assets/js/menu.js"></script>
<script src="assets/js/jquery.sticky.js"></script>
<script src="assets/js/scrolltopcontrol.js"></script>
<script src="assets/js/wow.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
// ── Validation formulaire Postuler ──
$('#formPostuler').on('submit', function (e) {
    var valid = true;

    // Réinitialiser toutes les erreurs
    $(this).find('.is-invalid').removeClass('is-invalid');
    $(this).find('.invalid-feedback').text('').hide();

    function showError(fieldId, msg) {
        $('#' + fieldId).addClass('is-invalid');
        $('#' + fieldId + '-error').text(msg).show();
        valid = false;
    }

    // Nom
    var nom = $.trim($('#postuler-nom').val());
    if (nom === '') {
        showError('postuler-nom', 'Le nom est obligatoire.');
    } else if (nom.length < 2) {
        showError('postuler-nom', 'Le nom doit contenir au moins 2 caractères.');
    }

    // Prénom
    var prenom = $.trim($('#postuler-prenom').val());
    if (prenom === '') {
        showError('postuler-prenom', 'Le prénom est obligatoire.');
    } else if (prenom.length < 2) {
        showError('postuler-prenom', 'Le prénom doit contenir au moins 2 caractères.');
    }

    // Email
    var email = $.trim($('#postuler-email').val());
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('postuler-email', "L'email est obligatoire.");
    } else if (!emailRegex.test(email)) {
        showError('postuler-email', 'Adresse email invalide.');
    }

    // CV
    var cvInput = document.getElementById('postuler-cv');
    if (!cvInput.files || cvInput.files.length === 0) {
        showError('postuler-cv', 'Veuillez joindre votre CV.');
    } else {
        var ext = cvInput.files[0].name.split('.').pop().toLowerCase();
        if (!['pdf','doc','docx'].includes(ext)) {
            showError('postuler-cv', 'Format invalide. Acceptés : PDF, DOC, DOCX.');
        } else if (cvInput.files[0].size > 5 * 1024 * 1024) {
            showError('postuler-cv', 'Le fichier ne doit pas dépasser 5 Mo.');
        }
    }

    if (!valid) {
        e.preventDefault();
        e.stopPropagation();
    }
});

// ── Extraction PDF côté navigateur avec PDF.js + Analyse IA ──
document.getElementById('postuler-cv').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;

    var ext = file.name.split('.').pop().toLowerCase();

    // Réinitialiser la zone
    var zone    = document.getElementById('cv-analyse-zone');
    var loading = document.getElementById('cv-analyse-loading');
    var result  = document.getElementById('cv-analyse-result');
    var content = document.getElementById('cv-analyse-content');
    var errDiv  = document.getElementById('cv-analyse-error');
    var btnSoumettre     = document.getElementById('btn-soumettre');
    var btnSoumettreText = document.getElementById('btn-soumettre-text');

    zone.style.display    = 'block';
    loading.style.display = 'block';
    result.style.display  = 'none';
    errDiv.style.display  = 'none';
    btnSoumettre.disabled = true;
    btnSoumettreText.textContent = '⏳ Extraction en cours...';

    console.log('PDF.js disponible:', typeof pdfjsLib !== 'undefined', '| Extension:', ext);

    if (ext === 'pdf' && typeof pdfjsLib !== 'undefined') {
        // ── Extraction PDF avec PDF.js ──
        var reader = new FileReader();
        reader.onload = function (e) {
            var typedArray = new Uint8Array(e.target.result);
            pdfjsLib.getDocument({ data: typedArray }).promise.then(function (pdf) {
                var textPromises = [];
                for (var i = 1; i <= pdf.numPages; i++) {
                    textPromises.push(
                        pdf.getPage(i).then(function (page) {
                            return page.getTextContent().then(function (tc) {
                                return tc.items.map(function (item) { return item.str; }).join(' ');
                            });
                        })
                    );
                }
                Promise.all(textPromises).then(function (pages) {
                    var fullText = pages.join('\n').trim();
                    console.log('Texte extrait PDF (' + fullText.length + ' chars):', fullText.substring(0, 200));
                    if (fullText.length > 30) {
                        lancerAnalyse(fullText, loading, result, content, errDiv, btnSoumettre, btnSoumettreText);
                    } else {
                        afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText);
                    }
                });
            }).catch(function (err) {
                console.log('Erreur PDF.js:', err);
                afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText);
            });
        };
        reader.readAsArrayBuffer(file);

    } else if ((ext === 'docx') && typeof mammoth !== 'undefined') {
        // ── Extraction DOCX avec Mammoth.js ──
        var reader = new FileReader();
        reader.onload = function (e) {
            mammoth.extractRawText({ arrayBuffer: e.target.result })
                .then(function (res) {
                    var fullText = res.value.trim();
                    console.log('Texte extrait DOCX (' + fullText.length + ' chars):', fullText.substring(0, 200));
                    if (fullText.length > 30) {
                        lancerAnalyse(fullText, loading, result, content, errDiv, btnSoumettre, btnSoumettreText);
                    } else {
                        afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText);
                    }
                })
                .catch(function (err) {
                    console.log('Erreur Mammoth:', err);
                    afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText);
                });
        };
        reader.readAsArrayBuffer(file);

    } else {
        // Format non supporté ou librairie non chargée
        afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText);
    }
});

function lancerAnalyse(cvText, loading, result, content, errDiv, btnSoumettre, btnSoumettreText) {
    btnSoumettreText.textContent = '⏳ Analyse IA en cours...';

    var formData = new FormData();
    formData.append('cv_text', cvText.substring(0, 3000));
    formData.append('offre_titre', $('#modal-offre-titre').text() || '');
    formData.append('offre_type',  '');

    $.ajax({
        url: 'api_analyse_cv.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        timeout: 35000,
        success: function (data) {
            loading.style.display = 'none';
            if (data.success) {
                var texte = data.analyse;

                // Supprimer les emojis résiduels
                texte = texte.replace(/[\u{1F300}-\u{1FAFF}]/gu, '');

                // Formater les sections en titres
                texte = texte.replace(/^(POINTS FORTS|AXES D'AMELIORATION|EVALUATION|RECOMMANDATION)$/gm,
                    '<div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#2c3e50;border-bottom:1px solid #e5e7eb;padding-bottom:4px;margin:14px 0 8px;">$1</div>');

                // Formater les listes
                texte = texte.replace(/^- (.+)$/gm,
                    '<div style="padding:3px 0 3px 12px;border-left:2px solid #2c3e50;margin-bottom:4px;color:#374151;">$1</div>');

                // Formater les items numérotés
                texte = texte.replace(/^(\d+)\. \*\*(.+?)\*\* : (.+)$/gm,
                    '<div style="margin-bottom:8px;"><span style="font-weight:600;color:#2c3e50;">$1. $2</span> <span style="color:#555;">— $3</span></div>');
                texte = texte.replace(/^(\d+)\. (.+?) : (.+)$/gm,
                    '<div style="margin-bottom:8px;"><span style="font-weight:600;color:#2c3e50;">$1. $2</span> <span style="color:#555;">— $3</span></div>');

                // Score
                texte = texte.replace(/^Score : (\d+)\/10$/gm,
                    '<div style="display:inline-block;background:#2c3e50;color:#fff;padding:4px 14px;border-radius:3px;font-weight:700;font-size:14px;margin:4px 0;">$1 / 10</div>');

                // Gras markdown
                texte = texte.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                // Sauts de ligne
                texte = texte.replace(/\n/g, '<br>');

                content.innerHTML = texte;
                result.style.display = 'block';
            } else {
                errDiv.textContent   = data.message || 'Erreur lors de l\'analyse.';
                errDiv.style.display = 'block';
            }
            btnSoumettre.disabled = false;
            btnSoumettreText.textContent = 'Envoyer ma candidature';
        },
        error: function () {
            loading.style.display = 'none';
            errDiv.textContent    = '⚠ Service d\'analyse indisponible.';
            errDiv.style.display  = 'block';
            btnSoumettre.disabled = false;
            btnSoumettreText.textContent = 'Envoyer ma candidature';
        }
    });
}

function afficherErreurExtraction(loading, errDiv, btnSoumettre, btnSoumettreText) {
    loading.style.display = 'none';
    errDiv.textContent   = 'Analyse non disponible pour ce format de fichier.';
    errDiv.style.display = 'block';
    btnSoumettre.disabled = false;
    btnSoumettreText.textContent = 'Envoyer ma candidature';
}

// Activer bouton analyser quand textarea rempli - SUPPRIME

// ── Gestion des étapes du formulaire (toujours actif) ──
var currentStep = 1;

function initSteps() {
    var isAccessMode = document.body.classList.contains('mode-concentration') ||
                       document.body.classList.contains('mode-anxiete');

    if (isAccessMode) {
        // Mode accessibilité — formulaire en 3 étapes
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-3').style.display = 'none';
        document.getElementById('form-progress').style.display = 'block';
        document.getElementById('btn-next-step').style.display = 'inline-block';
        document.getElementById('btn-soumettre').style.display = 'none';
        document.getElementById('btn-prev-step').style.display = 'none';
        updateStepUI(1);
    } else {
        // Mode normal — tout visible en une seule étape
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'block';
        document.getElementById('step-3').style.display = 'block';
        document.getElementById('form-progress').style.display = 'none';
        document.getElementById('btn-next-step').style.display = 'none';
        document.getElementById('btn-prev-step').style.display = 'none';
        document.getElementById('btn-soumettre').style.display = 'inline-block';
    }
}

function updateStepUI(step) {
    var labels = ['', 'Étape 1/3 — Votre identité', 'Étape 2/3 — Vos coordonnées', 'Étape 3/3 — Votre CV'];
    document.getElementById('step-label').textContent = labels[step];

    [1,2,3].forEach(function(i) {
        var dot = document.getElementById('dot-' + i);
        if (i < step)        { dot.style.background = '#10b981'; dot.style.color = '#fff'; }
        else if (i === step) { dot.style.background = '#7c3aed'; dot.style.color = '#fff'; }
        else                 { dot.style.background = '#e5e7eb'; dot.style.color = '#aaa'; }
    });

    document.getElementById('fill-1-2').style.width = step >= 2 ? '100%' : '0%';
    document.getElementById('fill-2-3').style.width = step >= 3 ? '100%' : '0%';
    document.getElementById('btn-prev-step').style.display  = step > 1 ? 'inline-block' : 'none';
    document.getElementById('btn-next-step').style.display  = step < 3 ? 'inline-block' : 'none';
    document.getElementById('btn-soumettre').style.display  = step === 3 ? 'inline-block' : 'none';

    // Messages anxiété
    var isAnxiete = document.body.classList.contains('mode-anxiete');
    document.querySelectorAll('.msg-anxiete').forEach(function(el) {
        el.style.display = isAnxiete ? 'block' : 'none';
    });
    document.querySelectorAll('.msg-normal').forEach(function(el) {
        el.style.display = isAnxiete ? 'none' : 'block';
    });

    // Couleur barre de progression en mode anxiété
    var color = isAnxiete ? '#2563eb' : '#7c3aed';
    document.getElementById('fill-1-2').style.background = color;
    document.getElementById('fill-2-3').style.background = color;
    if (step === document.getElementById('dot-' + step)) {
        document.getElementById('dot-' + step).style.background = color;
    }
}

$('#btn-next-step').on('click', function () {
    var valid = true;

    if (currentStep === 1) {
        var nom    = $.trim($('#postuler-nom').val());
        var prenom = $.trim($('#postuler-prenom').val());
        if (!nom || nom.length < 2) {
            $('#postuler-nom').addClass('is-invalid');
            $('#postuler-nom-error').text('Nom obligatoire (min 2 caractères).').show();
            valid = false;
        } else { $('#postuler-nom').removeClass('is-invalid'); $('#postuler-nom-error').hide(); }
        if (!prenom || prenom.length < 2) {
            $('#postuler-prenom').addClass('is-invalid');
            $('#postuler-prenom-error').text('Prénom obligatoire (min 2 caractères).').show();
            valid = false;
        } else { $('#postuler-prenom').removeClass('is-invalid'); $('#postuler-prenom-error').hide(); }
    } else if (currentStep === 2) {
        var email = $.trim($('#postuler-email').val());
        var re    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !re.test(email)) {
            $('#postuler-email').addClass('is-invalid');
            $('#postuler-email-error').text('Adresse email invalide.').show();
            valid = false;
        } else { $('#postuler-email').removeClass('is-invalid'); $('#postuler-email-error').hide(); }
    }

    if (!valid) return;

    $('#step-' + currentStep).hide();
    currentStep++;
    $('#step-' + currentStep).show();
    updateStepUI(currentStep);
});

$('#btn-prev-step').on('click', function () {
    if (currentStep <= 1) return;
    $('#step-' + currentStep).hide();
    currentStep--;
    $('#step-' + currentStep).show();
    updateStepUI(currentStep);
});

// Réinitialiser à l'ouverture du modal
$('#modalPostuler').on('show.bs.modal', function (e) {
    var btn        = $(e.relatedTarget);
    var offreId    = btn.attr('data-offre-id');
    var offreTitre = btn.attr('data-offre-titre');
    $('#modal-offre-id').val(offreId);
    $('#modal-offre-titre').text(offreTitre);
    $('#modalPostuler .is-invalid').removeClass('is-invalid');
    $('#modalPostuler .invalid-feedback').text('').hide();
    $('#btn-soumettre').prop('disabled', false);
    $('#btn-soumettre-text').text('Envoyer ma candidature');
    $('#cv-analyse-zone').hide();
    $('#postuler-cv').val('');
    // Réinitialiser les étapes
    currentStep = 1;
    $('#step-1').show(); $('#step-2').hide(); $('#step-3').hide();
    initSteps();
});

// ── Description masquée en mode concentration ──
function applyConcentrationDescriptions() {
    if (!document.body.classList.contains('mode-concentration')) return;
    document.querySelectorAll('.job-description').forEach(function(desc) {
        desc.style.display = 'none';
        var btn = desc.nextElementSibling;
        if (btn && btn.classList.contains('btn-voir-plus')) {
            btn.style.display = 'inline-block';
            btn.addEventListener('click', function() {
                if (desc.style.display === 'none') {
                    desc.style.display = 'block';
                    btn.textContent = 'Masquer ▲';
                } else {
                    desc.style.display = 'none';
                    btn.textContent = 'Voir la description ▼';
                }
            });
        }
    });
}

// Appliquer quand le mode change
var origSet = window.AccessWidget ? window.AccessWidget.set : null;
if (origSet) {
    window.AccessWidget.set = function(mode) {
        origSet(mode);
        if (mode === 'concentration') {
            setTimeout(applyConcentrationDescriptions, 100);
        } else {
            // Restaurer les descriptions
            document.querySelectorAll('.job-description').forEach(function(d) { d.style.display = 'block'; });
            document.querySelectorAll('.btn-voir-plus').forEach(function(b) { b.style.display = 'none'; });
        }
    };
}

// Si déjà en mode concentration au chargement
if (document.body.classList.contains('mode-concentration')) {
    applyConcentrationDescriptions();
}

['postuler-nom','postuler-prenom','postuler-email','postuler-cv'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('input',  function() {
            el.classList.remove('is-invalid');
            var err = document.getElementById(id + '-error');
            if (err) { err.textContent = ''; err.style.display = 'none'; }
        });
        el.addEventListener('change', function() {
            el.classList.remove('is-invalid');
            var err = document.getElementById(id + '-error');
            if (err) { err.textContent = ''; err.style.display = 'none'; }
        });
    }
});
</script>

<script>
(function () {
    var pollUrl  = 'api_contrats_stats.php';
    var numEl    = document.getElementById('contrat-count-num');
    var wrap     = document.getElementById('contrats-counter-wrap');
    var badge    = document.getElementById('badge-nouveau-contrat');
    if (!numEl || !wrap) return;
    var lastCount = parseInt(numEl.textContent, 10) || 0;

    function triggerFlash() {
        wrap.classList.remove('contrats-live-counter--flash');
        void wrap.offsetWidth;
        wrap.classList.add('contrats-live-counter--flash');
        setTimeout(function () { wrap.classList.remove('contrats-live-counter--flash'); }, 2400);
    }

    function refresh() {
        if (!window.fetch) return;
        fetch(pollUrl, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var n = parseInt(d.count, 10) || 0;
                var prev = lastCount;
                lastCount = n;
                numEl.textContent = String(n);
                if (n > prev) triggerFlash();
                if (badge) badge.style.display = (!!d.highlight_new || n > prev) ? 'inline-block' : 'none';
            })
            .catch(function () {});
    }

    setInterval(refresh, 5000);
})();
</script>

<script src="assets/js/accessibility.js"></script>
</body>
</html>
