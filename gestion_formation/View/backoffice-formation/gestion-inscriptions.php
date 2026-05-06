<?php
require_once __DIR__ . '/../../Controller/Inscriptioncontroller .php';
require_once __DIR__ . '/../../Model/Inscription.php';

$ic = new InscriptionController();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $insc = new Inscription(
            (int)$_POST['user_id'],
            (int)$_POST['formation_id'],
            trim($_POST['nom']),
            trim($_POST['prenom']),
            trim($_POST['email']),
            $_POST['niveau'] ?? null,
            $_POST['mode_formation'] ?? null
        );
        $ic->addInscription($insc);
        header('Location: gestion-inscriptions.php?success=add');
        exit;
    }
    if ($_POST['action'] === 'edit') {
        $insc = new Inscription(
            (int)$_POST['user_id'],
            (int)$_POST['formation_id'],
            trim($_POST['nom']),
            trim($_POST['prenom']),
            trim($_POST['email']),
            $_POST['niveau'] ?? null,
            $_POST['mode_formation'] ?? null,
            (int)$_POST['id']
        );
        $ic->updateInscription((int)$_POST['id'], $insc);
        header('Location: gestion-inscriptions.php?success=edit');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $ic->deleteInscription((int)$_GET['delete']);
    header('Location: gestion-inscriptions.php?success=delete');
    exit;
}

$inscriptions = $ic->listInscriptions()->fetchAll();
$total = count($inscriptions);

// Stats par mode_formation (données réelles)
$modes = ['En ligne', 'Présentiel', 'Hybride'];
$modesCounts = [];
foreach ($modes as $m) {
    $modesCounts[] = count(array_filter($inscriptions, fn($i) => ($i['mode_formation'] ?? '') === $m));
}
$modesAutres = $total - array_sum($modesCounts);

// Stats par niveau (valeurs réelles dans la DB)
$niveauxRaw = array_filter(array_unique(array_column($inscriptions, 'niveau')));
sort($niveauxRaw);
if (empty($niveauxRaw)) $niveauxRaw = ['Aucun'];
$niveauxInsc = array_values($niveauxRaw);
$niveauxInscCounts = [];
foreach ($niveauxInsc as $n) {
    $niveauxInscCounts[] = count(array_filter($inscriptions, fn($i) => ($i['niveau'] ?? '') === $n));
}

// Stats cards (pour les 4 cartes du haut)
$avecMode    = count(array_filter($inscriptions, fn($i) => !empty($i['mode_formation'])));
$sansMode    = $total - $avecMode;
$avecNiveau  = count(array_filter($inscriptions, fn($i) => !empty($i['niveau'])));
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Inscriptions | Gestion</title>
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
                <li class="menu-item"><a href="gestion-formations.php" class="menu-link"><div class="text-truncate">Vue d'ensemble</div></a></li>
                <li class="menu-item active"><a href="gestion-inscriptions.php" class="menu-link"><div class="text-truncate">Inscriptions</div></a></li>
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
            <li class="menu-item"><a href="html/gestion-reclamations.html" class="menu-link"><div class="text-truncate">Réclamations</div></a></li>
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
                <li><a class="dropdown-item" href="html/auth-login-basic.html"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span></a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!-- /Navbar -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
          <h4 class="fw-bold py-3 mb-2">Inscriptions aux formations</h4>
          <p class="text-muted mb-4">Tableaux avec actions Modifier / Supprimer (menu ⋮).</p>

          <?php if (isset($_GET['success'])): ?>
            <?php $msgs = ['add'=>'Inscription ajoutée avec succès.','edit'=>'Inscription modifiée avec succès.','delete'=>'Inscription supprimée avec succès.']; ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
              <?= $msgs[$_GET['success']] ?? 'Opération réussie.' ?>
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
                      <span>Inscriptions</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $total ?></h4>
                      </div>
                      <p class="mb-0">Total inscriptions</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user-plus bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Avec mode</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $avecMode ?></h4>
                      </div>
                      <p class="mb-0">Mode renseigné</p>
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
                      <span>Avec niveau</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $avecNiveau ?></h4>
                      </div>
                      <p class="mb-0">Niveau renseigné</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-book bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Sans mode</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $sansMode ?></h4>
                      </div>
                      <p class="mb-0">Mode non renseigné</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ===== SECTION STATISTIQUES GRAPHIQUES ===== -->
          <div class="row g-4 mb-5">

            <!-- Grande carte : Bar inscriptions par niveau réel -->
            <div class="col-xl-8">
              <div class="card border-0 h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <div>
                      <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Tendances inscriptions</p>
                      <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">Inscriptions par niveau</h6>
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
                    <canvas id="barStatut"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Petite carte : Pie répartition par mode -->
            <div class="col-xl-4">
              <div class="card border-0 h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
                <div class="card-body p-4">
                  <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Meilleures catégories</p>
                  <h6 class="fw-bold mb-3 mt-1" style="color:#1e1b4b;font-size:15px;">Répartition par mode</h6>
                  <div class="d-flex justify-content-center mb-3">
                    <div style="position:relative;height:150px;width:150px;">
                      <canvas id="donutMode"></canvas>
                    </div>
                  </div>
                  <div class="d-flex flex-column gap-2 mt-2">
                    <?php
                    $pieColors  = ['#6366f1','#818cf8','#a5b4fc','#c7d2fe'];
                    $allModes   = $modes; // seulement En ligne, Présentiel, Hybride
                    $allCounts  = $modesCounts;
                    foreach ($allModes as $i => $m):
                      $pct = $total > 0 ? round($allCounts[$i] / $total * 100) : 0;
                    ?>
                    <div class="d-flex align-items-center justify-content-between py-1" style="border-bottom:1px solid #f1f5f9;">
                      <div class="d-flex align-items-center gap-2">
                        <span style="width:9px;height:9px;border-radius:50%;background:<?= $pieColors[$i] ?>;display:inline-block;flex-shrink:0;"></span>
                        <div>
                          <div style="font-size:12px;font-weight:600;color:#1e1b4b;"><?= $m ?></div>
                          <div style="font-size:10px;color:#94a3b8;"><?= $allCounts[$i] ?> inscription<?= $allCounts[$i] > 1 ? 's' : '' ?></div>
                        </div>
                      </div>
                      <span style="font-size:12px;font-weight:700;color:#6366f1;"><?= $pct ?>%</span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($modesAutres > 0): ?>
                    <div class="d-flex align-items-center justify-content-between py-1">
                      <div class="d-flex align-items-center gap-2">
                        <span style="width:9px;height:9px;border-radius:50%;background:#e0e7ff;display:inline-block;flex-shrink:0;"></span>
                        <div>
                          <div style="font-size:12px;font-weight:600;color:#1e1b4b;">Autre</div>
                          <div style="font-size:10px;color:#94a3b8;"><?= $modesAutres ?> inscription<?= $modesAutres > 1 ? 's' : '' ?></div>
                        </div>
                      </div>
                      <span style="font-size:12px;font-weight:700;color:#6366f1;"><?= $total > 0 ? round($modesAutres/$total*100) : 0 ?>%</span>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Carte : Horizontal bar — Inscriptions par mode -->
            <div class="col-xl-6">
              <div class="card border-0" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <div>
                      <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Engagement par mode</p>
                      <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">En ligne vs Présentiel vs Hybride</h6>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                      <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#6366f1;font-weight:600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#6366f1;display:inline-block;"></span>Actuel
                      </span>
                    </div>
                  </div>
                  <div style="position:relative;height:160px;margin-top:16px;">
                    <canvas id="hbarStatut"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Carte : Bar — Inscriptions par niveau -->
            <div class="col-xl-6">
              <div class="card border-0" style="border-radius:14px;box-shadow:0 2px 12px rgba(99,102,241,0.07);">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <div>
                      <p class="text-muted mb-0" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;">Analyse des niveaux</p>
                      <h6 class="fw-bold mb-0 mt-1" style="color:#1e1b4b;font-size:15px;">Inscriptions par niveau de formation</h6>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                      <span class="d-flex align-items-center gap-1" style="font-size:11px;color:#6366f1;font-weight:600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#6366f1;display:inline-block;"></span>Actuel
                      </span>
                    </div>
                  </div>
                  <div style="position:relative;height:160px;margin-top:16px;">
                    <canvas id="lineMode"></canvas>
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
                  <select class="form-select text-capitalize"><option value="">Sélectionner Formation</option></select>
                </div>
                <div class="col-md-4">
                  <select class="form-select text-capitalize"><option value="">Sélectionner Session</option></select>
                </div>
                <div class="col-md-4">
                  <select class="form-select text-capitalize"><option value="">Sélectionner Statut</option><option value="Confirmée">Confirmée</option><option value="En attente">En attente</option><option value="Annulée">Annulée</option></select>
                </div>
              </div>
            </div>
            <div class="card-datatable table-responsive">
              <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                <div class="row mx-2 pt-3 pb-3">
                  <div class="col-md-2 d-flex align-items-center">
                    <select id="inscPerPage" class="form-select w-auto"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                  </div>
                  <div class="col-md-10">
                    <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
                      <div class="dataTables_filter">
                        <input type="search" id="inscSearchInput" class="form-control" placeholder="Rechercher..." />
                      </div>
                      <div class="dt-buttons btn-group flex-wrap align-items-center">

                        <!-- Bouton Trier -->
                        <div class="dropdown me-2">
                          <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-1"
                                  type="button" id="inscSortDropdown"
                                  data-bs-toggle="dropdown" aria-expanded="false"
                                  style="font-weight:500;border-radius:8px;padding:7px 14px;">
                            <i class="bx bx-sort-alt-2 bx-sm"></i>
                            <span id="inscSortLabel">Trier</span>
                          </button>
                          <ul class="dropdown-menu shadow-sm" aria-labelledby="inscSortDropdown" style="min-width:210px;border-radius:10px;border:1px solid #e9ecef;padding:6px;">
                            <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Nom</h6></li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="nom" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-sort-a-z" style="font-size:16px;color:#6366f1;"></i> Nom A → Z
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="nom" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-sort-z-a" style="font-size:16px;color:#6366f1;"></i> Nom Z → A
                              </a>
                            </li>
                            <li><div class="dropdown-divider my-1"></div></li>
                            <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Mode</h6></li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="mode" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-up-arrow-alt" style="font-size:16px;color:#6366f1;"></i> Mode A → Z
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="mode" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-down-arrow-alt" style="font-size:16px;color:#6366f1;"></i> Mode Z → A
                              </a>
                            </li>
                            <li><div class="dropdown-divider my-1"></div></li>
                            <li><h6 class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase;padding:6px 12px 4px;">Formation</h6></li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="formation" data-dir="asc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-filter-alt" style="font-size:16px;color:#6366f1;"></i> ID Formation ↑
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2" href="javascript:void(0);" data-sort="formation" data-dir="desc" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-filter-alt" style="font-size:16px;color:#6366f1;transform:scaleY(-1);display:inline-block;"></i> ID Formation ↓
                              </a>
                            </li>
                            <li><div class="dropdown-divider my-1"></div></li>
                            <li>
                              <a class="dropdown-item insc-sort-item d-flex align-items-center gap-2 rounded-2 text-danger" href="javascript:void(0);" data-sort="reset" style="font-size:13px;padding:7px 12px;">
                                <i class="bx bx-reset" style="font-size:16px;"></i> Réinitialiser
                              </a>
                            </li>
                          </ul>
                        </div>

                        <!-- Bouton Exporter -->
                        <div class="dropdown me-2">
                          <button class="btn btn-primary dropdown-toggle" type="button" id="inscExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="bx bx-export me-1 bx-sm"></i>Exporter</span>
                          </button>
                          <ul class="dropdown-menu" aria-labelledby="inscExportDropdown">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0);" id="inscExportPdfBtn">
                                <i class="bx bxs-file-pdf me-2 text-danger"></i>Exporter en PDF
                              </a>
                            </li>
                          </ul>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
                <table class="table border-top dataTable">
                  <thead>
                    <tr>
                      <th><input type="checkbox" class="form-check-input"></th>
                      <th>STAGIAIRE</th>
                      <th>EMAIL</th>
                      <th>FORMATION ID</th>
                      <th>NIVEAU</th>
                      <th>MODE</th>
                      <th>ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody id="inscTableBody">
                    <?php if (empty($inscriptions)): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">Aucune inscription trouvée.</td></tr>
                    <?php else: ?>
                      <?php foreach ($inscriptions as $insc): ?>
                        <tr>
                          <td><input type="checkbox" class="form-check-input"></td>
                          <td>
                            <div class="d-flex justify-content-start align-items-center user-name">
                              <div class="avatar-wrapper">
                                <div class="avatar avatar-sm me-3">
                                  <span class="avatar-initial rounded-circle bg-label-primary"><?= strtoupper(substr($insc['nom'], 0, 1)) ?></span>
                                </div>
                              </div>
                              <div class="d-flex flex-column">
                                <span class="fw-medium text-heading"><?= htmlspecialchars($insc['nom'] . ' ' . $insc['prenom']) ?></span>
                                <small class="text-muted"><?= htmlspecialchars($insc['email']) ?></small>
                              </div>
                            </div>
                          </td>
                          <td><?= htmlspecialchars($insc['email']) ?></td>
                          <td><?= htmlspecialchars($insc['formation_id']) ?></td>
                          <td><?= htmlspecialchars($insc['niveau'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($insc['mode_formation'] ?? '-') ?></td>
                          <td>
                            <div class="d-flex align-items-center">
                              <a href="gestion-inscriptions.php?delete=<?= $insc['id'] ?>"
                                 class="text-body"
                                 onclick="return confirm('Supprimer cette inscription ?')"
                                 title="Supprimer">
                                <i class="bx bx-trash bx-sm me-2"></i>
                              </a>
                              <button class="btn p-0 text-body border-0 bg-transparent me-2"
                                data-bs-toggle="modal" data-bs-target="#editInscriptionModal"
                                data-id="<?= $insc['id'] ?>"
                                data-user_id="<?= htmlspecialchars($insc['user_id'], ENT_QUOTES) ?>"
                                data-formation_id="<?= htmlspecialchars($insc['formation_id'], ENT_QUOTES) ?>"
                                data-nom="<?= htmlspecialchars($insc['nom'], ENT_QUOTES) ?>"
                                data-prenom="<?= htmlspecialchars($insc['prenom'], ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($insc['email'], ENT_QUOTES) ?>"
                                data-niveau="<?= htmlspecialchars($insc['niveau'] ?? '', ENT_QUOTES) ?>"
                                data-mode_formation="<?= htmlspecialchars($insc['mode_formation'] ?? '', ENT_QUOTES) ?>"
                                title="Modifier">
                                <i class="bx bx-show bx-sm me-2"></i>
                              </button>
                              <div class="dropdown">
                                <a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded bx-sm"></i></a>
                                <div class="dropdown-menu dropdown-menu-end m-0">
                                  <button class="dropdown-item"
                                    data-bs-toggle="modal" data-bs-target="#editInscriptionModal"
                                    data-id="<?= $insc['id'] ?>"
                                    data-user_id="<?= htmlspecialchars($insc['user_id'], ENT_QUOTES) ?>"
                                    data-formation_id="<?= htmlspecialchars($insc['formation_id'], ENT_QUOTES) ?>"
                                    data-nom="<?= htmlspecialchars($insc['nom'], ENT_QUOTES) ?>"
                                    data-prenom="<?= htmlspecialchars($insc['prenom'], ENT_QUOTES) ?>"
                                    data-email="<?= htmlspecialchars($insc['email'], ENT_QUOTES) ?>"
                                    data-niveau="<?= htmlspecialchars($insc['niveau'] ?? '', ENT_QUOTES) ?>"
                                    data-mode_formation="<?= htmlspecialchars($insc['mode_formation'] ?? '', ENT_QUOTES) ?>">
                                    Modifier
                                  </button>
                                  <a href="gestion-inscriptions.php?delete=<?= $insc['id'] ?>"
                                     class="dropdown-item text-danger"
                                     onclick="return confirm('Supprimer cette inscription ?')">
                                    Supprimer
                                  </a>
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
                    <div class="dataTables_info text-muted small">Affichage de 1 à <?= min(10, $total) ?> sur <?= $total ?> entrées</div>
                  </div>
                  <div class="col-sm-12 col-md-6 d-flex justify-content-end">
                    <ul class="pagination pagination-sm m-0">
                      <li class="page-item"><a href="#" class="page-link">Précédent</a></li>
                      <li class="page-item active"><a href="#" class="page-link">1</a></li>
                      <li class="page-item"><a href="#" class="page-link">Suivant</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Edit Inscription -->
          <div class="modal fade" id="editInscriptionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Modifier l'inscription</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="gestion-inscriptions.php">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" id="editId">
                  <div class="modal-body">
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">ID Utilisateur</label>
                        <input type="number" name="user_id" id="editUserId" class="form-control bg-light" readonly />
                        <small class="text-muted">Non modifiable</small>
                      </div>
                      <div class="col-12">
                        <label class="form-label">ID Formation</label>
                        <input type="number" name="formation_id" id="editFormationId" class="form-control bg-light" readonly />
                        <small class="text-muted">Non modifiable</small>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="editNom" class="form-control" required maxlength="100" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="prenom" id="editPrenom" class="form-control" required maxlength="100" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required maxlength="150" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Niveau</label>
                        <input type="text" name="niveau" id="editNiveau" class="form-control" maxlength="100" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Mode Formation</label>
                        <select name="mode_formation" id="editModeFormation" class="form-select">
                          <option value="">-- Sélectionner --</option>
                          <option value="En ligne">En ligne</option>
                          <option value="Présentiel">Présentiel</option>
                          <option value="Hybride">Hybride</option>
                        </select>
                      </div>
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

          <!-- Modal Confirmation Suppression -->
          <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Confirmation</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p class="mb-0">Supprimer cette inscription ?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Annuler</button>
                  <a href="#" id="confirmDeleteBtn" class="btn btn-danger">OK</a>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /container-xxl -->
        <!-- Footer -->
        <footer class="content-footer footer bg-footer-theme">
          <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">© <?php echo date('Y'); ?>, Takwini Platform</div>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ===== DONNÉES PHP → JS =====
const inscStatuts     = <?= json_encode($niveauxInsc) ?>;
const inscCounts      = <?= json_encode($niveauxInscCounts) ?>;
const inscModes       = <?= json_encode($modes) ?>;
const inscModesCounts = <?= json_encode($modesCounts) ?>;
const inscTotal       = <?= $total ?>;

Chart.defaults.font.family = "'Public Sans', sans-serif";
Chart.defaults.color = '#94a3b8';

const C_DARK  = '#6366f1';
const C_LIGHT = '#c7d2fe';
const C_MID   = '#818cf8';
const C_FAINT = '#e0e7ff';

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

// ===== 1. BAR — Inscriptions par statut =====
new Chart(document.getElementById('barStatut'), {
  type: 'bar',
  data: {
    labels: inscStatuts,
    datasets: [
      {
        label: 'Actuel',
        data: inscCounts,
        backgroundColor: C_DARK,
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.45
      },
      {
        label: 'Précédent',
        data: inscCounts.map(v => Math.max(0, v - Math.floor(Math.random() * 2))),
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
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.y} inscription${ctx.parsed.y > 1 ? 's' : ''}` } }
    },
    scales: {
      x: { ...axisStyle, grid: { display: false } },
      y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1, precision: 0 } }
    },
    animation: { duration: 800 }
  }
});

// ===== 2. PIE — Répartition par mode =====
new Chart(document.getElementById('donutMode'), {
  type: 'pie',
  data: {
    labels: inscModes,
    datasets: [{
      data: inscModesCounts,
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
            const pct = inscTotal > 0 ? Math.round(ctx.parsed / inscTotal * 100) : 0;
            return ` ${ctx.label} : ${pct}%`;
          }
        }
      }
    },
    animation: { duration: 800 }
  }
});

// ===== 3. HORIZONTAL BAR — Inscriptions par mode =====
new Chart(document.getElementById('hbarStatut'), {
  type: 'bar',
  data: {
    labels: inscModes,
    datasets: [
      {
        label: 'Actuel',
        data: inscModesCounts,
        backgroundColor: C_DARK,
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
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.x} inscription${ctx.parsed.x > 1 ? 's' : ''}` } }
    },
    scales: {
      x: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1, precision: 0 } },
      y: { ...axisStyle, grid: { display: false } }
    },
    animation: { duration: 800 }
  }
});

// ===== 4. BAR — Inscriptions par niveau =====
new Chart(document.getElementById('lineMode'), {
  type: 'bar',
  data: {
    labels: inscStatuts,
    datasets: [
      {
        label: 'Actuel',
        data: inscCounts,
        backgroundColor: C_MID,
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.55
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { ...tooltipStyle, callbacks: { label: ctx => ` ${ctx.parsed.y} inscription${ctx.parsed.y > 1 ? 's' : ''}` } }
    },
    scales: {
      x: { ...axisStyle, grid: { display: false } },
      y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1, precision: 0 } }
    },
    animation: { duration: 800 }
  }
});

// ===== RECHERCHE =====
document.getElementById('inscSearchInput').addEventListener('input', function() {
  const val = this.value.toLowerCase();
  document.querySelectorAll('#inscTableBody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
  });
});

// ===== TRI =====
const inscSortIcons = {
  'nom-asc': 'Nom A → Z', 'nom-desc': 'Nom Z → A',
  'mode-asc': 'Mode A → Z', 'mode-desc': 'Mode Z → A',
  'formation-asc': 'Formation ID ↑', 'formation-desc': 'Formation ID ↓'
};

document.querySelectorAll('.insc-sort-item').forEach(function(item) {
  item.addEventListener('click', function() {
    const sortBy  = this.dataset.sort;
    const sortDir = this.dataset.dir;

    if (sortBy === 'reset') {
      document.getElementById('inscSortLabel').textContent = 'Trier';
      const tbody = document.getElementById('inscTableBody');
      if (!tbody) return;
      const rows = Array.from(tbody.querySelectorAll('tr[data-orig-idx]'));
      rows.sort((a, b) => parseInt(a.dataset.origIdx) - parseInt(b.dataset.origIdx));
      rows.forEach(r => tbody.appendChild(r));
      document.querySelectorAll('.insc-sort-item').forEach(i => { i.classList.remove('fw-bold'); i.style.background = ''; i.style.color = ''; });
      return;
    }

    const key = sortBy + '-' + sortDir;
    document.getElementById('inscSortLabel').textContent = inscSortIcons[key] || 'Trier';
    document.querySelectorAll('.insc-sort-item').forEach(i => { i.classList.remove('fw-bold'); i.style.background = ''; i.style.color = ''; });
    this.classList.add('fw-bold');
    this.style.background = '#eef2ff';
    this.style.color = '#4338ca';

    const tbody = document.getElementById('inscTableBody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort(function(a, b) {
      const cells_a = a.querySelectorAll('td');
      const cells_b = b.querySelectorAll('td');

      if (sortBy === 'nom') {
        const elA = a.querySelector('.fw-medium.text-heading');
        const elB = b.querySelector('.fw-medium.text-heading');
        const vA = elA ? elA.textContent.trim().toLowerCase() : '';
        const vB = elB ? elB.textContent.trim().toLowerCase() : '';
        return sortDir === 'asc' ? vA.localeCompare(vB, 'fr') : vB.localeCompare(vA, 'fr');
      }
      if (sortBy === 'mode') {
        const vA = cells_a[5] ? cells_a[5].textContent.trim().toLowerCase() : '';
        const vB = cells_b[5] ? cells_b[5].textContent.trim().toLowerCase() : '';
        return sortDir === 'asc' ? vA.localeCompare(vB, 'fr') : vB.localeCompare(vA, 'fr');
      }
      if (sortBy === 'formation') {
        const vA = cells_a[3] ? parseInt(cells_a[3].textContent.trim()) || 0 : 0;
        const vB = cells_b[3] ? parseInt(cells_b[3].textContent.trim()) || 0 : 0;
        return sortDir === 'asc' ? vA - vB : vB - vA;
      }
      return 0;
    });

    rows.forEach(r => tbody.appendChild(r));
  });
});

// Sauvegarder l'ordre original
(function() {
  const tbody = document.getElementById('inscTableBody');
  if (tbody) tbody.querySelectorAll('tr').forEach((r, i) => r.dataset.origIdx = i);
})();

// ===== EXPORT PDF =====
document.getElementById('inscExportPdfBtn').addEventListener('click', function() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

  doc.setFillColor(99, 102, 241);
  doc.rect(0, 0, 297, 20, 'F');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Liste des Inscriptions', 148, 13, { align: 'center' });

  const now = new Date();
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.text('Exporté le : ' + now.toLocaleDateString('fr-FR') + ' à ' + now.toLocaleTimeString('fr-FR'), 148, 19, { align: 'center' });

  const rows = [];
  const tbody = document.getElementById('inscTableBody');
  if (tbody) {
    tbody.querySelectorAll('tr').forEach(function(tr) {
      if (tr.style.display === 'none') return;
      const cells = tr.querySelectorAll('td');
      if (cells.length < 7) return;
      const nomEl = tr.querySelector('.fw-medium.text-heading');
      const nom   = nomEl ? nomEl.textContent.trim() : '';
      const email = cells[2] ? cells[2].textContent.trim() : '';
      const fid   = cells[3] ? cells[3].textContent.trim() : '';
      const niv   = cells[4] ? cells[4].textContent.trim() : '';
      const mode  = cells[5] ? cells[5].textContent.trim() : '';
      rows.push([nom, email, fid, niv, mode]);
    });
  }

  doc.autoTable({
    startY: 25,
    head: [['Stagiaire', 'Email', 'Formation ID', 'Niveau', 'Mode']],
    body: rows,
    theme: 'grid',
    headStyles: { fillColor: [99, 102, 241], textColor: 255, fontStyle: 'bold', fontSize: 10, halign: 'center' },
    bodyStyles: { fontSize: 9, textColor: [50, 50, 50] },
    alternateRowStyles: { fillColor: [245, 245, 255] },
    columnStyles: {
      0: { cellWidth: 55 }, 1: { cellWidth: 65 },
      2: { cellWidth: 35, halign: 'center' },
      3: { cellWidth: 40, halign: 'center' },
      4: { cellWidth: 40, halign: 'center' }
    },
    margin: { left: 10, right: 10 },
    didDrawPage: function(data) {
      const pageCount = doc.internal.getNumberOfPages();
      doc.setFontSize(8);
      doc.setTextColor(150);
      doc.text('Page ' + data.pageNumber + ' / ' + pageCount + '  —  Takwini Platform', 148, doc.internal.pageSize.height - 5, { align: 'center' });
    }
  });

  doc.save('inscriptions_' + now.toISOString().slice(0, 10) + '.pdf');
});

// ===== MODAL MODIFIER — pré-remplissage =====
document.getElementById('editInscriptionModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('editId').value            = btn.dataset.id;
  document.getElementById('editUserId').value        = btn.dataset.user_id;
  document.getElementById('editFormationId').value   = btn.dataset.formation_id;
  document.getElementById('editNom').value           = btn.dataset.nom;
  document.getElementById('editPrenom').value        = btn.dataset.prenom;
  document.getElementById('editEmail').value         = btn.dataset.email;
  document.getElementById('editNiveau').value        = btn.dataset.niveau;
  document.getElementById('editModeFormation').value = btn.dataset.mode_formation;
});

</script>

</body>
</html>
