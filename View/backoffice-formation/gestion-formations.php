<?php
require_once __DIR__ . '/../../Controller/FormationController.php';
require_once __DIR__ . '/../../Model/Formation.php';
$fc = new FormationController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $f = new Formation(trim($_POST['titre']), trim($_POST['duree']), (float)$_POST['prix'], $_POST['niveau'], trim($_POST['description']));
        $fc->addFormation($f);
        header('Location: gestion-formations.php?success=add'); exit;
    }
    if ($_POST['action'] === 'edit') {
        $f = new Formation(trim($_POST['titre']), trim($_POST['duree']), (float)$_POST['prix'], $_POST['niveau'], trim($_POST['description']));
        $fc->updateFormation((int)$_POST['id'], $f);
        header('Location: gestion-formations.php?success=edit'); exit;
    }
}
if (isset($_GET['delete'])) {
    $fc->deleteFormation((int)$_GET['delete']);
    header('Location: gestion-formations.php?success=delete'); exit;
}
$formations = $fc->listFormations()->fetchAll();
$total      = count($formations);
$payantes   = count(array_filter($formations, fn($f) => $f['prix'] > 0));
$gratuites  = $total - $payantes;
$avances    = count(array_filter($formations, fn($f) => $f['niveau'] === 'Avancé'));
$debutants  = count(array_filter($formations, fn($f) => $f['niveau'] === 'Débutant'));
$intermediaires = count(array_filter($formations, fn($f) => $f['niveau'] === 'Intermédiaire'));
$experts    = count(array_filter($formations, fn($f) => $f['niveau'] === 'Expert'));

// Stats par niveau pour graphique
$niveaux = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];
$niveauxCounts = [];
foreach ($niveaux as $n) {
    $niveauxCounts[] = count(array_filter($formations, fn($f) => $f['niveau'] === $n));
}

// Stats prix moyen par niveau
$prixMoyenParNiveau = [];
foreach ($niveaux as $n) {
    $filtered = array_filter($formations, fn($f) => $f['niveau'] === $n);
    $prixMoyenParNiveau[] = count($filtered) > 0
        ? round(array_sum(array_column($filtered, 'prix')) / count($filtered), 2)
        : 0;
}
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Formations | Gestion</title>
  <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="assets/vendor/css/core.css" />
  <link rel="stylesheet" href="assets/css/demo.css" />
  <link rel="stylesheet" href="assets/css/dark-mode.css" />
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <script src="assets/vendor/js/helpers.js"></script>
  <script src="assets/js/config.js"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    <!-- Sidebar -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="html/index.html" class="app-brand-link">
          <span class="app-brand-logo demo"><img src="assets/img/favicon/tak.png" alt="Takwinibot" style="width:90px;height:90px;object-fit:contain;"></span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
          <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
      </div>
      <div class="menu-divider mt-0"></div>
      <div class="menu-inner-shadow"></div>
      <ul class="menu-inner py-1">
        <li class="menu-item active open">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-home-smile"></i>
            <div class="text-truncate">Tableau de bord</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="html/index.html" class="menu-link"><div class="text-truncate">Accueil</div></a></li>
            <li class="menu-item open">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Formations</div></a>
              <ul class="menu-sub">
                <li class="menu-item active"><a href="gestion-formations.php" class="menu-link"><div class="text-truncate">Vue d'ensemble</div></a></li>
                <li class="menu-item"><a href="gestion-inscriptions.php" class="menu-link"><div class="text-truncate">Inscriptions</div></a></li>
                <li class="menu-item"><a href="html/gestion-certificats.html" class="menu-link"><div class="text-truncate">Certificats</div></a></li>
              </ul>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Offres</div></a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="html/gestion-offres.html" class="menu-link"><div class="text-truncate">Liste des offres</div></a></li>
                <li class="menu-item"><a href="html/gestion-contrats.html" class="menu-link"><div class="text-truncate">Contrats</div></a></li>
              </ul>
            </li>
            <li class="menu-item"><a href="html/gestion-reclamations.html" class="menu-link"><div class="text-truncate">R&#233;clamations</div></a></li>
            <li class="menu-item"><a href="html/gestion-entretiens.html" class="menu-link"><div class="text-truncate">Entretiens</div></a></li>
            <li class="menu-item"><a href="html/gestion-produits.html" class="menu-link"><div class="text-truncate">Produits</div></a></li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Utilisateurs</div></a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="html/gestion-utilisateurs.html" class="menu-link"><div class="text-truncate">Liste des utilisateurs</div></a></li>
                <li class="menu-item"><a href="html/pages-account-settings-account.html" class="menu-link"><div class="text-truncate">Profil</div></a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Applications</span></li>
        <li class="menu-item"><a href="html/email-boite.html" class="menu-link"><i class="menu-icon tf-icons bx bx-envelope"></i><div class="text-truncate">Email</div></a></li>
        <li class="menu-item"><a href="html/app-chat-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-chat"></i><div class="text-truncate">Discuter</div></a></li>
        <li class="menu-item"><a href="html/app-calendrier-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-calendar"></i><div class="text-truncate">Calendrier</div></a></li>
      </ul>
    </aside>
    <!-- /Sidebar -->
    <div class="layout-page">
      <!-- Navbar -->
      <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)"><i class="icon-base bx bx-menu icon-md"></i></a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
          <div class="navbar-nav align-items-center me-auto">
            <div class="nav-item d-flex align-items-center">
              <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
              <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..." aria-label="Search..." />
            </div>
          </div>
          <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item me-2 me-xl-1">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle"><i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i></a>
            </li>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online"><img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" /></div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="html/pages-account-settings-account.html"><div class="d-flex"><div class="flex-shrink-0 me-3"><div class="avatar avatar-online"><img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" /></div></div><div class="flex-grow-1"><h6 class="mb-0">Admin</h6><small class="text-body-secondary">Administrateur</small></div></div></a></li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li><a class="dropdown-item" href="html/auth-login-basic.html"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>D&#233;connexion</span></a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!-- /Navbar -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
          <h4 class="fw-bold py-3 mb-2">Nos formations</h4>
          <p class="text-muted mb-4">Tableaux avec actions Modifier / Supprimer (menu &#8942;).</p>

<?php if (isset($_GET['success'])): ?>
  <?php $msgs = ['add'=>'Formation ajout&#233;e avec succ&#232;s.','edit'=>'Formation modifi&#233;e avec succ&#232;s.','delete'=>'Formation supprim&#233;e avec succ&#232;s.']; ?>
  <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <?= $msgs[$_GET['success']] ?? 'Op&#233;ration r&#233;ussie.' ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Formations</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2"><?= $total ?></h4>
            </div>
            <p class="mb-0">Total Formations</p>
          </div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-book-open bx-sm"></i></span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Formations Payantes</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2"><?= $payantes ?></h4>
            </div>
            <p class="mb-0">Analyse semaine</p>
          </div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-dollar bx-sm"></i></span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Formations Actives</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2"><?= $avances ?></h4>
            </div>
            <p class="mb-0">Analyse semaine</p>
          </div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="bx bx-check bx-sm"></i></span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>En attente</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2"><?= $debutants ?></h4>
            </div>
            <p class="mb-0">Analyse semaine</p>
          </div>
          <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five bx-sm"></i></span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== SECTION STATISTIQUES GRAPHIQUES ===== -->
<div class="row g-4 mb-5">

  <!-- Grande carte : Bar KPI Formations par niveau -->
  <div class="col-xl-8">
    <div class="card border-0 h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div>
            <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Tendances formations</p>
            <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">Formations par niveau</h6>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#6366f1;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#6366f1;display:inline-block;"></span>Actuel
            </span>
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#c7d2fe;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#c7d2fe;display:inline-block;"></span>Précédent
            </span>
          </div>
        </div>
        <div style="position:relative;height:240px;margin-top:16px;">
          <canvas id="barNiveau"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Petite carte : Pie répartition par statut prix -->
  <div class="col-xl-4">
    <div class="card border-0 h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
      <div class="card-body p-4">
        <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Meilleures catégories</p>
        <h6 class="fw-bold mb-3 mt-1" style="color:#1e1b4b;font-size:15px;">Répartition par niveau</h6>
        <div class="d-flex justify-content-center mb-3">
          <div style="position:relative;height:150px;width:150px;">
            <canvas id="donutNiveau"></canvas>
          </div>
        </div>
        <div class="d-flex flex-column gap-2 mt-2">
          <?php
          $pieColors = ['#6366f1','#818cf8','#a5b4fc','#c7d2fe'];
          foreach ($niveaux as $i => $n):
            $pct = $total > 0 ? round($niveauxCounts[$i] / $total * 100) : 0;
          ?>
          <div class="d-flex align-items-center justify-content-between py-1" style="border-bottom:1px solid #f1f5f9;">
            <div class="d-flex align-items-center gap-2">
              <span style="width:9px;height:9px;border-radius:50%;background:<?= $pieColors[$i] ?>;display:inline-block;flex-shrink:0;"></span>
              <div>
                <div style="font-size:12px;font-weight:600;color:#1e1b4b;"><?= $n ?></div>
                <div style="font-size:10px;color:#94a3b8;"><?= $niveauxCounts[$i] ?> formation<?= $niveauxCounts[$i] > 1 ? 's' : '' ?></div>
              </div>
            </div>
            <span style="font-size:12px;font-weight:700;color:#6366f1;"><?= $pct ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Carte : Horizontal bar — Tarification -->
  <div class="col-xl-6">
    <div class="card border-0" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div>
            <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Tarification</p>
            <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">Gratuit vs Payant</h6>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#6366f1;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#6366f1;display:inline-block;"></span>Actuel
            </span>
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#c7d2fe;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#c7d2fe;display:inline-block;"></span>Précédent
            </span>
          </div>
        </div>
        <div style="position:relative;height:160px;margin-top:16px;">
          <canvas id="hbarTarif"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Carte : Line — Prix moyen par niveau -->
  <div class="col-xl-6">
    <div class="card border-0" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div>
            <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Analyse financière</p>
            <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">Prix moyen par niveau</h6>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#6366f1;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#6366f1;display:inline-block;"></span>Actuel
            </span>
            <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#c7d2fe;font-weight:600;">
              <span style="width:10px;height:10px;border-radius:50%;background:#c7d2fe;display:inline-block;"></span>Précédent
            </span>
          </div>
        </div>
        <div style="position:relative;height:160px;margin-top:16px;">
          <canvas id="linePrix"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- ===== FIN STATISTIQUES ===== -->

<!-- Main Card with Filters + Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title">Filtres de recherche</h5>
    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
      <div class="col-md-4">
        <select id="filterNiveau" class="form-select text-capitalize">
          <option value="">S&#233;lectionner Niveau</option>
          <option value="D&#233;butant">D&#233;butant</option>
          <option value="Interm&#233;diaire">Interm&#233;diaire</option>
          <option value="Avanc&#233;">Avanc&#233;</option>
          <option value="Expert">Expert</option>
        </select>
      </div>
      <div class="col-md-4">
        <select id="filterPrix" class="form-select text-capitalize">
          <option value="">S&#233;lectionner Cat&#233;gorie Prix</option>
          <option value="gratuit">Gratuit</option>
          <option value="payant">Payant</option>
        </select>
      </div>
      <div class="col-md-4">
        <select id="filterStatut" class="form-select text-capitalize">
          <option value="">S&#233;lectionner Statut</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
      <div class="row mx-2 pt-3 pb-3">
        <div class="col-md-2 d-flex align-items-center">
          <select id="perPage" class="form-select w-auto">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
        </div>
        <div class="col-md-10">
          <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
            <div class="dataTables_filter">
              <input type="search" id="searchInput" class="form-control" placeholder="Rechercher..." />
            </div>
            <div class="dt-buttons btn-group flex-wrap align-items-center">

              <!-- Bouton Trier -->
              <div class="dropdown me-2">
                <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-1"
                        type="button" id="sortDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        style="font-weight:500;border-radius:8px;padding:7px 14px;">
                  <i class="bx bx-sort-alt-2 bx-sm"></i>
                  <span id="sortLabel">Trier</span>
                </button>
                <ul class="dropdown-menu shadow-sm" aria-labelledby="sortDropdown" style="min-width:210px;border-radius:10px;border:1px solid #e9ecef;padding:6px;">
                  <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Titre</h6></li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="titre" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-sort-a-z" style="font-size:16px;color:#6366f1;"></i> Titre A → Z
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="titre" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-sort-z-a" style="font-size:16px;color:#6366f1;"></i> Titre Z → A
                    </a>
                  </li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Prix</h6></li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="prix" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-up-arrow-alt" style="font-size:16px;color:#6366f1;"></i> Prix croissant
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="prix" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-down-arrow-alt" style="font-size:16px;color:#6366f1;"></i> Prix décroissant
                    </a>
                  </li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Niveau</h6></li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="niveau" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-filter-alt" style="font-size:16px;color:#6366f1;"></i> Niveau A → Z
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="niveau" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-filter-alt" style="font-size:16px;color:#6366f1;transform:scaleY(-1);display:inline-block;"></i> Niveau Z → A
                    </a>
                  </li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  <li>
                    <a class="dropdown-item sort-item d-flex align-items-center gap-2 rounded-2 text-danger" href="javascript:void(0);" data-sort="reset" style="font-size:13px;padding:7px 12px;">
                      <i class="bx bx-reset" style="font-size:16px;"></i> Réinitialiser
                    </a>
                  </li>
                </ul>
              </div>

              <!-- Bouton Exporter -->
              <div class="dropdown me-2">
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <span><i class="bx bx-export me-1 bx-sm"></i>Exporter</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                  <li>
                    <a class="dropdown-item" href="javascript:void(0);" id="exportPdfBtn">
                      <i class="bx bxs-file-pdf me-2 text-danger"></i>Exporter en PDF
                    </a>
                  </li>
                </ul>
              </div>

              <!-- Bouton Ajouter -->
              <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addFormationModal">
                <span><i class="bx bx-plus me-0 me-sm-1 bx-sm"></i><span class="d-none d-sm-inline-block">Ajouter Formation</span></span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <table class="table border-top dataTable" id="formationsTable">
        <thead>
          <tr>
            <th><input type="checkbox" class="form-check-input" id="checkAll"></th>
            <th>FORMATION</th>
            <th>FORMATEUR</th>
            <th>CAT&#201;GORIE</th>
            <th>PRIX</th>
            <th>STATUT</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php if (empty($formations)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Aucune formation trouv&#233;e.</td></tr>
          <?php else: ?>
            <?php foreach ($formations as $f): ?>
            <?php
              $niveauColors = ['D&#233;butant'=>'success','Interm&#233;diaire'=>'primary','Avanc&#233;'=>'warning','Expert'=>'danger'];
              $color = $niveauColors[$f['niveau']] ?? 'secondary';
              $initials = strtoupper(substr($f['titre'], 0, 2));
              $avatarColors = ['primary','success','warning','danger','info'];
              $avatarColor = $avatarColors[$f['id'] % count($avatarColors)];
              $prixLabel = $f['prix'] > 0 ? number_format($f['prix'], 2).' TND' : 'Gratuit';
            ?>
            <tr data-niveau="<?= htmlspecialchars($f['niveau']) ?>" data-prix="<?= $f['prix'] > 0 ? 'payant' : 'gratuit' ?>">
              <td><input type="checkbox" class="form-check-input row-check"></td>
              <td>
                <div class="d-flex justify-content-start align-items-center user-name">
                  <div class="avatar-wrapper">
                    <div class="avatar avatar-sm me-3">
                      <span class="avatar-initial rounded-circle bg-label-<?= $avatarColor ?>"><?= $initials ?></span>
                    </div>
                  </div>
                  <div class="d-flex flex-column">
                    <span class="fw-medium text-heading"><?= htmlspecialchars($f['titre']) ?></span>
                    <small class="text-muted">ref-<?= str_pad($f['id'], 4, '0', STR_PAD_LEFT) ?></small>
                  </div>
                </div>
              </td>
              <td>
                <span class="text-truncate d-flex align-items-center">
                  <span class="badge badge-center rounded bg-label-<?= $color ?> w-px-30 h-px-30 me-2"><i class="bx bx-user bx-xs"></i></span>
                  <?= htmlspecialchars($f['niveau']) ?>
                </span>
              </td>
              <td><span class="text-heading"><?= htmlspecialchars($f['duree']) ?></span></td>
              <td><?= $prixLabel ?></td>
              <td>
                <?php if ($f['prix'] > 0): ?>
                  <span class="badge bg-label-primary text-capitalize">Payant</span>
                <?php else: ?>
                  <span class="badge bg-label-success text-capitalize">Gratuit</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <a href="gestion-formations.php?delete=<?= $f['id'] ?>"
                     class="text-body"
                     onclick="return confirm('Supprimer cette formation ?')"
                     title="Supprimer">
                    <i class="bx bx-trash bx-sm me-2"></i>
                  </a>
                  <button class="btn p-0 text-body border-0 bg-transparent me-2"
                    data-bs-toggle="modal" data-bs-target="#editFormationModal"
                    data-id="<?= $f['id'] ?>"
                    data-titre="<?= htmlspecialchars($f['titre'], ENT_QUOTES) ?>"
                    data-duree="<?= htmlspecialchars($f['duree'], ENT_QUOTES) ?>"
                    data-prix="<?= $f['prix'] ?>"
                    data-niveau="<?= htmlspecialchars($f['niveau'], ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars($f['description'], ENT_QUOTES) ?>"
                    title="Modifier">
                    <i class="bx bx-show bx-sm"></i>
                  </button>
                  <div class="dropdown">
                    <a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded bx-sm"></i></a>
                    <div class="dropdown-menu dropdown-menu-end m-0">
                      <button class="dropdown-item"
                        data-bs-toggle="modal" data-bs-target="#editFormationModal"
                        data-id="<?= $f['id'] ?>"
                        data-titre="<?= htmlspecialchars($f['titre'], ENT_QUOTES) ?>"
                        data-duree="<?= htmlspecialchars($f['duree'], ENT_QUOTES) ?>"
                        data-prix="<?= $f['prix'] ?>"
                        data-niveau="<?= htmlspecialchars($f['niveau'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($f['description'], ENT_QUOTES) ?>">
                        Modifier
                      </button>
                      <a href="gestion-formations.php?delete=<?= $f['id'] ?>" class="dropdown-item text-danger" onclick="return confirm('Supprimer cette formation ?')">Supprimer</a>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="row mx-2 mt-3 mb-3">
        <div class="col-sm-12 col-md-6 d-flex align-items-center">
          <div class="dataTables_info text-muted small">Affichage de 1 &#224; <?= min(10, $total) ?> sur <?= $total ?> entr&#233;es</div>
        </div>
        <div class="col-sm-12 col-md-6 d-flex justify-content-end">
          <ul class="pagination pagination-sm m-0" id="pagination"></ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addFormationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une formation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="gestion-formations.php">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Titre <span class="text-danger">*</span></label>
            <input type="text" name="titre" class="form-control" placeholder="Titre de la formation" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Dur&#233;e <span class="text-danger">*</span></label>
              <input type="text" name="duree" class="form-control" placeholder="ex: 3 jours, 40h" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prix (TND) <span class="text-danger">*</span></label>
              <input type="number" name="prix" class="form-control" placeholder="0.00" min="0" step="0.01" value="0" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Niveau <span class="text-danger">*</span></label>
            <select name="niveau" class="form-select" required>
              <option value="">-- S&#233;lectionner --</option>
              <option value="D&#233;butant">D&#233;butant</option>
              <option value="Interm&#233;diaire">Interm&#233;diaire</option>
              <option value="Avanc&#233;">Avanc&#233;</option>
              <option value="Expert">Expert</option>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Description de la formation..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="editFormationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier la formation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="gestion-formations.php">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Titre <span class="text-danger">*</span></label>
            <input type="text" name="titre" id="editTitre" class="form-control" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Dur&#233;e <span class="text-danger">*</span></label>
              <input type="text" name="duree" id="editDuree" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prix (TND) <span class="text-danger">*</span></label>
              <input type="number" name="prix" id="editPrix" class="form-control" min="0" step="0.01" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Niveau <span class="text-danger">*</span></label>
            <select name="niveau" id="editNiveau" class="form-select" required>
              <option value="">-- S&#233;lectionner --</option>
              <option value="D&#233;butant">D&#233;butant</option>
              <option value="Interm&#233;diaire">Interm&#233;diaire</option>
              <option value="Avanc&#233;">Avanc&#233;</option>
              <option value="Expert">Expert</option>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label">Description</label>
            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

        </div><!-- /container-xxl -->
        <!-- Footer -->
        <footer class="content-footer footer bg-footer-theme">
          <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">&#169; <?php echo date('Y'); ?>, Takwini Platform</div>
            </div>
          </div>
        </footer>
        <div class="content-backdrop fade"></div>
      </div><!-- /content-wrapper -->
    </div><!-- /layout-page -->
  </div><!-- /layout-container -->
  <div class="layout-overlay layout-menu-toggle"></div>
</div><!-- /layout-wrapper -->

<!-- Core JS -->
<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/js/menu.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/navbar-extras.js"></script>

<!-- jsPDF + AutoTable pour export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<!-- Chart.js pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ===== DONNÉES PHP → JS =====
const niveauxLabels = <?= json_encode($niveaux) ?>;
const niveauxCounts = <?= json_encode($niveauxCounts) ?>;
const prixMoyens    = <?= json_encode($prixMoyenParNiveau) ?>;
const totalFormations = <?= $total ?>;
const payantes = <?= $payantes ?>;
const gratuites = <?= $gratuites ?>;

Chart.defaults.font.family = "'Public Sans', sans-serif";
Chart.defaults.color = '#94a3b8';

// Palette indigo/violet uniforme (style dashboard image)
const C_DARK   = '#6366f1';   // indigo-500
const C_LIGHT  = '#c7d2fe';   // indigo-200
const C_MID    = '#818cf8';   // indigo-400
const C_FAINT  = '#e0e7ff';   // indigo-100

// Options axes communes
const axisStyle = {
  grid: { color: '#f1f5f9', drawBorder: false },
  ticks: { color: '#94a3b8', font: { size: 11 } },
  border: { display: false }
};
const tooltipStyle = {
  backgroundColor: '#1e1b4b',
  titleColor: '#e0e7ff',
  bodyColor: '#c7d2fe',
  padding: 10,
  cornerRadius: 8,
  displayColors: false
};

// ===== 1. BAR — Formations par niveau (grande carte) =====
new Chart(document.getElementById('barNiveau'), {
  type: 'bar',
  data: {
    labels: niveauxLabels,
    datasets: [
      {
        label: 'Actuel',
        data: niveauxCounts,
        backgroundColor: C_DARK,
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.45
      },
      {
        label: 'Précédent',
        data: niveauxCounts.map(v => Math.max(0, v - Math.floor(Math.random() * 2))),
        backgroundColor: C_LIGHT,
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.45
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.y} formation${ctx.parsed.y > 1 ? 's' : ''}` } }
    },
    scales: {
      x: { ...axisStyle, grid: { display: false } },
      y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1, precision: 0 } }
    },
    animation: { duration: 800 }
  }
});

// ===== 2. PIE — Répartition par niveau =====
new Chart(document.getElementById('donutNiveau'), {
  type: 'pie',
  data: {
    labels: niveauxLabels,
    datasets: [{
      data: niveauxCounts,
      backgroundColor: [C_DARK, C_MID, C_LIGHT, C_FAINT],
      borderColor: '#fff',
      borderWidth: 3,
      hoverOffset: 6
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        ...tooltipStyle,
        callbacks: {
          label: ctx => {
            const pct = totalFormations > 0 ? Math.round(ctx.parsed / totalFormations * 100) : 0;
            return ` ${ctx.label} : ${pct}%`;
          }
        }
      }
    },
    animation: { duration: 800 }
  }
});

// ===== 3. HORIZONTAL BAR — Gratuit vs Payant =====
new Chart(document.getElementById('hbarTarif'), {
  type: 'bar',
  data: {
    labels: ['Gratuit', 'Payant'],
    datasets: [
      {
        label: 'Actuel',
        data: [gratuites, payantes],
        backgroundColor: C_DARK,
        borderRadius: 5,
        borderSkipped: false,
        barPercentage: 0.5
      },
      {
        label: 'Précédent',
        data: [Math.max(0, gratuites - 1), Math.max(0, payantes - 1)],
        backgroundColor: C_LIGHT,
        borderRadius: 5,
        borderSkipped: false,
        barPercentage: 0.5
      }
    ]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.x} formation${ctx.parsed.x > 1 ? 's' : ''}` } }
    },
    scales: {
      x: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1, precision: 0 } },
      y: { ...axisStyle, grid: { display: false } }
    },
    animation: { duration: 800 }
  }
});

// ===== 4. LINE — Prix moyen par niveau =====
new Chart(document.getElementById('linePrix'), {
  type: 'line',
  data: {
    labels: niveauxLabels,
    datasets: [
      {
        label: 'Actuel',
        data: prixMoyens,
        borderColor: C_DARK,
        backgroundColor: 'rgba(99,102,241,0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: C_DARK,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        fill: true,
        tension: 0.4
      },
      {
        label: 'Précédent',
        data: prixMoyens.map(v => Math.max(0, v * 0.85)),
        borderColor: C_LIGHT,
        backgroundColor: 'transparent',
        borderWidth: 2,
        pointBackgroundColor: C_LIGHT,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        fill: false,
        tension: 0.4
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.y.toFixed(2)} TND` } }
    },
    scales: {
      x: { ...axisStyle, grid: { display: false } },
      y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, callback: v => v + ' TND' } }
    },
    animation: { duration: 800 }
  }
});
</script>

<!-- Validation personnalisée -->
<script src="formation_validation.js"></script>

<script>
// Pre-fill edit modal
document.getElementById('editFormationModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('editId').value          = btn.dataset.id;
  document.getElementById('editTitre').value       = btn.dataset.titre;
  document.getElementById('editDuree').value       = btn.dataset.duree;
  document.getElementById('editPrix').value        = btn.dataset.prix;
  document.getElementById('editNiveau').value      = btn.dataset.niveau;
  document.getElementById('editDescription').value = btn.dataset.description;
});

// Client-side search
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('filterNiveau').addEventListener('change', filterTable);
document.getElementById('filterPrix').addEventListener('change', filterTable);

function filterTable() {
  const search  = document.getElementById('searchInput').value.toLowerCase();
  const niveau  = document.getElementById('filterNiveau').value;
  const prix    = document.getElementById('filterPrix').value;
  const rows    = document.querySelectorAll('#tableBody tr');
  rows.forEach(row => {
    const text     = row.textContent.toLowerCase();
    const rowNiv   = row.dataset.niveau || '';
    const rowPrix  = row.dataset.prix   || '';
    const matchS   = !search  || text.includes(search);
    const matchN   = !niveau  || rowNiv  === niveau;
    const matchP   = !prix    || rowPrix === prix;
    row.style.display = (matchS && matchN && matchP) ? '' : 'none';
  });
}

// Check all
document.getElementById('checkAll').addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});

// ===== TRI DU TABLEAU =====
const sortIcons = {
  'titre-asc':   'Titre A → Z',
  'titre-desc':  'Titre Z → A',
  'prix-asc':    'Prix ↑',
  'prix-desc':   'Prix ↓',
  'niveau-asc':  'Niveau A → Z',
  'niveau-desc': 'Niveau Z → A',
};

document.querySelectorAll('.sort-item').forEach(function(item) {
  item.addEventListener('click', function() {
    const sortBy  = this.dataset.sort;
    const sortDir = this.dataset.dir;

    // Réinitialiser
    if (sortBy === 'reset') {
      document.getElementById('sortLabel').textContent = 'Trier';
      // Remettre l'ordre original (recharger les lignes dans l'ordre du DOM initial)
      const tbody = document.getElementById('tableBody');
      const rows  = Array.from(tbody.querySelectorAll('tr[data-original-index]'));
      rows.sort((a, b) => parseInt(a.dataset.originalIndex) - parseInt(b.dataset.originalIndex));
      rows.forEach(r => tbody.appendChild(r));
      // Retirer la classe active de tous
      document.querySelectorAll('.sort-item').forEach(i => i.classList.remove('active','fw-bold'));
      return;
    }

    // Mettre à jour le label du bouton
    const key = sortBy + '-' + sortDir;
    document.getElementById('sortLabel').textContent = sortIcons[key] || 'Trier';

    // Marquer l'item actif
    document.querySelectorAll('.sort-item').forEach(i => {
      i.classList.remove('active','fw-bold');
      i.style.background = '';
      i.style.color = '';
    });
    this.classList.add('fw-bold');
    this.style.background = '#eef2ff';
    this.style.color = '#4338ca';

    // Trier les lignes visibles
    const tbody = document.getElementById('tableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));

    rows.sort(function(a, b) {
      let valA = '', valB = '';

      if (sortBy === 'titre') {
        const elA = a.querySelector('.fw-medium.text-heading');
        const elB = b.querySelector('.fw-medium.text-heading');
        valA = elA ? elA.textContent.trim().toLowerCase() : '';
        valB = elB ? elB.textContent.trim().toLowerCase() : '';
        return sortDir === 'asc' ? valA.localeCompare(valB, 'fr') : valB.localeCompare(valA, 'fr');
      }

      if (sortBy === 'prix') {
        // data-prix = 'gratuit' ou 'payant', on lit la cellule texte
        const cellA = a.querySelectorAll('td')[4];
        const cellB = b.querySelectorAll('td')[4];
        const textA = cellA ? cellA.textContent.trim() : '0';
        const textB = cellB ? cellB.textContent.trim() : '0';
        valA = textA === 'Gratuit' ? 0 : parseFloat(textA.replace(/[^\d.]/g, '')) || 0;
        valB = textB === 'Gratuit' ? 0 : parseFloat(textB.replace(/[^\d.]/g, '')) || 0;
        return sortDir === 'asc' ? valA - valB : valB - valA;
      }

      if (sortBy === 'niveau') {
        const order = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];
        valA = a.dataset.niveau || '';
        valB = b.dataset.niveau || '';
        const iA = order.indexOf(valA);
        const iB = order.indexOf(valB);
        const nA = iA === -1 ? 99 : iA;
        const nB = iB === -1 ? 99 : iB;
        return sortDir === 'asc' ? nA - nB : nB - nA;
      }

      return 0;
    });

    rows.forEach(r => tbody.appendChild(r));
  });
});

// Sauvegarder l'ordre original au chargement
(function() {
  const rows = document.querySelectorAll('#tableBody tr');
  rows.forEach((r, i) => r.dataset.originalIndex = i);
})();

// Export PDF
document.getElementById('exportPdfBtn').addEventListener('click', function () {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

  // En-tête
  doc.setFillColor(99, 102, 241);
  doc.rect(0, 0, 297, 20, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Liste des Formations', 148, 13, { align: 'center' });

  // Date d'export
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const now = new Date();
  doc.text('Exporté le : ' + now.toLocaleDateString('fr-FR') + ' à ' + now.toLocaleTimeString('fr-FR'), 148, 19, { align: 'center' });

  // Collecter les lignes visibles du tableau
  const rows = [];
  document.querySelectorAll('#tableBody tr').forEach(function (tr) {
    if (tr.style.display === 'none') return;
    const cells = tr.querySelectorAll('td');
    if (cells.length < 7) return;

    // Titre + ref
    const titreEl = cells[1].querySelector('.fw-medium');
    const refEl   = cells[1].querySelector('small');
    const titre   = titreEl ? titreEl.textContent.trim() : '';
    const ref     = refEl   ? refEl.textContent.trim()   : '';

    // Formateur / Niveau
    const formateur = cells[2].textContent.trim().replace(/\s+/g, ' ');

    // Catégorie (durée)
    const categorie = cells[3].textContent.trim();

    // Prix
    const prix = cells[4].textContent.trim();

    // Statut
    const statut = cells[5].textContent.trim();

    rows.push([titre + '\n' + ref, formateur, categorie, prix, statut]);
  });

  doc.autoTable({
    startY: 25,
    head: [['Formation', 'Niveau', 'Durée', 'Prix', 'Statut']],
    body: rows,
    theme: 'grid',
    headStyles: {
      fillColor: [99, 102, 241],
      textColor: 255,
      fontStyle: 'bold',
      fontSize: 10,
      halign: 'center'
    },
    bodyStyles: {
      fontSize: 9,
      textColor: [50, 50, 50]
    },
    alternateRowStyles: {
      fillColor: [245, 245, 255]
    },
    columnStyles: {
      0: { cellWidth: 70 },
      1: { cellWidth: 45, halign: 'center' },
      2: { cellWidth: 45, halign: 'center' },
      3: { cellWidth: 35, halign: 'center' },
      4: { cellWidth: 35, halign: 'center' }
    },
    margin: { left: 10, right: 10 },
    didDrawPage: function (data) {
      // Pied de page
      const pageCount = doc.internal.getNumberOfPages();
      doc.setFontSize(8);
      doc.setTextColor(150);
      doc.text(
        'Page ' + data.pageNumber + ' / ' + pageCount + '  —  Takwini Platform',
        148,
        doc.internal.pageSize.height - 5,
        { align: 'center' }
      );
    }
  });

  doc.save('formations_' + now.toISOString().slice(0, 10) + '.pdf');
});
</script>
</body>
</html>
