<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';

// Protection admin → redirige vers login backoffice
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login-admin.php');
    exit;
}

$db = config::getConnexion();

// ── STATISTIQUES ──────────────────────────────────────────────────────────────
$totalUsers      = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalCandidats  = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "candidat"')->fetchColumn();
$totalRecruteurs = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "recruteur"')->fetchColumn();
$recruteursAttente = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "recruteur" AND statut = "en_attente"')->fetchColumn();
$totalFormations = (int) $db->query('SELECT COUNT(*) FROM formation')->fetchColumn();

// Admin info
$adminNom = htmlspecialchars($_SESSION['user']['nom'] ?? 'Admin');
$__av     = $_SESSION['user']['avatar'] ?? '';
$__navAvatar = !empty($__av)
    ? '../../../../../view/frontoffice/' . $__av
    : '../assets/img/avatars/1.png';
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
  <title>Tableau de bord | Takwini Admin</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
  <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
  <link rel="stylesheet" href="../assets/css/demo.css"/>
  <link rel="stylesheet" href="../assets/css/dark-mode.css"/>
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
  <script src="../assets/vendor/js/helpers.js"></script>
  <script src="../assets/js/config.js"></script>
  <style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 22px; }
    .welcome-banner { background: linear-gradient(135deg, #696cff 0%, #9155fd 100%); border-radius: 12px; color: #fff; }
    .welcome-banner .display-6 { font-size: 1.6rem; }
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- ── SIDEBAR ────────────────────────────────────────────────────────── -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:72px;height:72px;object-fit:contain;">
          </span>
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
            <li class="menu-item active"><a href="index.php" class="menu-link"><div class="text-truncate">Accueil</div></a></li>
            <li class="menu-item"><a href="gestion-formations.html" class="menu-link"><div class="text-truncate">Formations</div></a></li>
            <li class="menu-item"><a href="gestion-offres.html" class="menu-link"><div class="text-truncate">Offres</div></a></li>
            <li class="menu-item"><a href="gestion-reclamations.html" class="menu-link"><div class="text-truncate">Réclamations</div></a></li>
            <li class="menu-item"><a href="gestion-entretiens.html" class="menu-link"><div class="text-truncate">Entretiens</div></a></li>
            <li class="menu-item"><a href="gestion-produits.html" class="menu-link"><div class="text-truncate">Produits</div></a></li>
            <li class="menu-item open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <div class="text-truncate">Utilisateurs</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="gestion-utilisateurs.php" class="menu-link"><div class="text-truncate">Liste des utilisateurs</div></a></li>
                <li class="menu-item"><a href="gestion-recruteurs.php" class="menu-link"><div class="text-truncate">Liste des recruteurs</div></a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Applications</span></li>
        <li class="menu-item"><a href="email-boite.html" class="menu-link"><i class="menu-icon tf-icons bx bx-envelope"></i><div class="text-truncate">Email</div></a></li>
        <li class="menu-item"><a href="app-chat-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-chat"></i><div class="text-truncate">Discuter</div></a></li>
        <li class="menu-item"><a href="app-calendrier-local.html" class="menu-link"><i class="menu-icon tf-icons bx bx-calendar"></i><div class="text-truncate">Calendrier</div></a></li>
        <li class="menu-item">
          <a href="../../../../../view/frontoffice/formations/index.php" class="menu-link" target="_blank">
            <i class="menu-icon tf-icons bx bx-globe"></i>
            <div class="text-truncate">Voir le site</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../../../../../controller/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-power-off"></i>
            <div class="text-truncate">Déconnexion</div>
          </a>
        </li>
      </ul>
    </aside>
    <!-- / Sidebar -->

    <div class="layout-page">

      <!-- ── NAVBAR ──────────────────────────────────────────────────────── -->
      <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
          </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
          <!-- Search -->
          <div class="navbar-nav align-items-center me-auto">
            <div class="nav-item d-flex align-items-center">
              <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
              <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Rechercher..." aria-label="Rechercher..."/>
            </div>
          </div>

          <ul class="navbar-nav flex-row align-items-center ms-md-auto">

            <!-- Dark mode toggle -->
            <li class="nav-item me-2 me-xl-1">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
              </a>
            </li>

            <!-- Notification bell -->
            <?php include __DIR__ . '/notifications.php'; ?>

            <!-- User dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                  <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="#">
                    <div class="d-flex">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                          <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?= $adminNom ?></h6>
                        <small class="text-body-secondary">Admin</small>
                      </div>
                    </div>
                  </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <a class="dropdown-item" href="../../../../../controller/logout.php">
                    <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span>
                  </a>
                </li>
              </ul>
            </li>

          </ul>
        </div>
      </nav>
      <!-- / Navbar -->

      <!-- ── CONTENT ─────────────────────────────────────────────────────── -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

          <!-- Welcome banner -->
          <div class="welcome-banner p-5 mb-6 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background:linear-gradient(135deg,#696cff 0%,#9155fd 60%,#ce9ffc 100%);border-radius:16px;color:#fff;">
            <div>
              <p class="mb-1 text-white-50" style="font-size:13px;letter-spacing:1px;text-transform:uppercase;font-weight:600;">Tableau de bord</p>
              <h3 class="mb-1 text-white fw-800">Bienvenue, <?= $adminNom ?></h3>
              <p class="mb-0" style="color:rgba(255,255,255,.7);font-size:14px;">Voici un aperçu de la plateforme Takwinibot.</p>
            </div>
            <img src="../assets/img/favicon/tak.png" alt="Takwini" style="width:80px;height:80px;object-fit:contain;opacity:.9;filter:drop-shadow(0 4px 12px rgba(0,0,0,.2));">
          </div>

          <!-- ── STATS CARDS ──────────────────────────────────────────────── -->
          <div class="row g-4 mb-6">

            <!-- Total utilisateurs -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Total utilisateurs</span>
                      <div class="d-flex align-items-end mt-2">
                        <h3 class="mb-0 me-2"><?= $totalUsers ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">Tous rôles confondus</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-group bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Candidats -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Candidats</span>
                      <div class="d-flex align-items-end mt-2">
                        <h3 class="mb-0 me-2 text-success"><?= $totalCandidats ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">Inscrits comme candidat</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-success">
                        <i class="bx bx-user bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recruteurs -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Recruteurs</span>
                      <div class="d-flex align-items-end mt-2">
                        <h3 class="mb-0 me-2 text-info"><?= $totalRecruteurs ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">Comptes recruteur</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-info">
                        <i class="bx bx-briefcase bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recruteurs en attente -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Recruteurs en attente</span>
                      <div class="d-flex align-items-end mt-2">
                        <h3 class="mb-0 me-2 text-warning"><?= $recruteursAttente ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">
                        <a href="gestion-recruteurs.php" class="text-warning">Voir les demandes →</a>
                      </p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-warning">
                        <i class="bx bx-time bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formations -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Formations</span>
                      <div class="d-flex align-items-end mt-2">
                        <h3 class="mb-0 me-2 text-danger"><?= $totalFormations ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">Formations disponibles</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-danger">
                        <i class="bx bx-book-open bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Admins -->
            <div class="col-sm-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span class="text-muted fw-medium">Administrateurs</span>
                      <div class="d-flex align-items-end mt-2">
                        <?php
                          $totalAdmins = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
                        ?>
                        <h3 class="mb-0 me-2"><?= $totalAdmins ?></h3>
                      </div>
                      <p class="mb-0 mt-1 small text-muted">Comptes admin</p>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-secondary">
                        <i class="bx bx-crown bx-sm"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- / Stats cards -->

          <!-- ── CHARTS ROW ───────────────────────────────────────────────── -->
          <div class="row g-4 mb-6">

            <!-- Répartition des rôles (donut) -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header">
                  <h5 class="mb-0">Répartition des rôles</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                  <canvas id="rolesDonut" style="max-height:260px;"></canvas>
                </div>
              </div>
            </div>

            <!-- Activité rapide (bar) -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header">
                  <h5 class="mb-0">Vue d'ensemble</h5>
                </div>
                <div class="card-body">
                  <canvas id="overviewBar" style="max-height:260px;"></canvas>
                </div>
              </div>
            </div>

          </div>

          <!-- ── QUICK ACTIONS ────────────────────────────────────────────── -->
          <div class="card mb-6">
            <div class="card-header">
              <h5 class="mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-6 col-md-3">
                  <a href="gestion-utilisateurs.php" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3 gap-2">
                    <i class="bx bx-group" style="font-size:24px;"></i>
                    <span>Utilisateurs</span>
                  </a>
                </div>
                <div class="col-6 col-md-3">
                  <a href="gestion-recruteurs.php" class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3 gap-2 position-relative">
                    <i class="bx bx-briefcase" style="font-size:24px;"></i>
                    <span>Recruteurs</span>
                    <?php if ($recruteursAttente > 0): ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 m-1"><?= $recruteursAttente ?></span>
                    <?php endif; ?>
                  </a>
                </div>
                <div class="col-6 col-md-3">
                  <a href="changer-motdepasse.php" class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3 gap-2">
                    <i class="bx bx-lock" style="font-size:24px;"></i>
                    <span>Mot de passe</span>
                  </a>
                </div>
                <div class="col-6 col-md-3">
                  <a href="../../../../../controller/logout.php" class="btn btn-outline-danger w-100 d-flex flex-column align-items-center py-3 gap-2">
                    <i class="bx bx-power-off" style="font-size:24px;"></i>
                    <span>Déconnexion</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- ── FOOTER ──────────────────────────────────────────────────────── -->
        <footer class="content-footer footer bg-footer-theme">
          <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">
                &copy; <script>document.write(new Date().getFullYear())</script> Takwini — Plateforme d'emploi &amp; formation
              </div>
            </div>
          </div>
        </footer>
        <div class="content-backdrop fade"></div>
      </div>
      <!-- / Content wrapper -->

    </div>
    <!-- / Layout page -->
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- Scripts -->
<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/navbar-extras.js"></script>

<script>
// ── Donut chart: répartition des rôles ──────────────────────────────────────
new Chart(document.getElementById('rolesDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Candidats', 'Recruteurs', 'Admins'],
        datasets: [{
            data: [<?= $totalCandidats ?>, <?= $totalRecruteurs ?>, <?= $totalAdmins ?>],
            backgroundColor: ['#71dd37', '#03c3ec', '#696cff'],
            borderWidth: 2,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 16, usePointStyle: true }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.label + ' : ' + ctx.parsed + ' utilisateur(s)'
                }
            }
        }
    }
});

// ── Bar chart: vue d'ensemble ────────────────────────────────────────────────
new Chart(document.getElementById('overviewBar'), {
    type: 'bar',
    data: {
        labels: ['Utilisateurs', 'Candidats', 'Recruteurs', 'En attente', 'Formations'],
        datasets: [{
            label: 'Nombre',
            data: [
                <?= $totalUsers ?>,
                <?= $totalCandidats ?>,
                <?= $totalRecruteurs ?>,
                <?= $recruteursAttente ?>,
                <?= $totalFormations ?>
            ],
            backgroundColor: ['#696cff', '#71dd37', '#03c3ec', '#ffab00', '#ff3e1d'],
            borderRadius: 6,
            borderSkipped: false,
            barThickness: 40
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>


