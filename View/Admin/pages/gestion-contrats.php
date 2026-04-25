<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/ContratController.php';
require_once __DIR__ . '/../../../Controller/OffreController.php';

$contratCtrl = new ContratController();
$offreCtrl   = new OffreController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $contrat = new Contrat(
            (int)($_POST['offre_id'] ?? 0),
            trim($_POST['salaire'] ?? ''),
            trim($_POST['duree'] ?? ''),
            trim($_POST['dateCreation'] ?? date('Y-m-d')),
            trim($_POST['statut'] ?? 'actif')
        );
        $res = $contratCtrl->addContrat((int)($_POST['offre_id'] ?? 0), $contrat);
        if (!empty($res['success'])) {
            header('Location: gestion-contrats.php');
            exit;
        }
        $flashError = $res['message'] ?? 'Erreur lors de la création.';
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $contrat = new Contrat(
            (int)($_POST['offre_id'] ?? 0),
            trim($_POST['salaire'] ?? ''),
            trim($_POST['duree'] ?? ''),
            trim($_POST['dateCreation'] ?? date('Y-m-d')),
            trim($_POST['statut'] ?? 'actif')
        );
        $res = $contratCtrl->updateContrat($id, (int)($_POST['offre_id'] ?? 0), $contrat);
        if (!empty($res['success'])) {
            header('Location: gestion-contrats.php');
            exit;
        }
        $flashError = $res['message'] ?? 'Erreur lors de la mise à jour.';
    }

    if ($action === 'delete') {
        $contratCtrl->deleteContrat((int)($_POST['id'] ?? 0));
        header('Location: gestion-contrats.php');
        exit;
    }
}

$contrats          = $contratCtrl->listContrats()->fetchAll();
$totalContrats     = $contratCtrl->countContrats();
$totalActifs       = count(array_filter($contrats, fn($c) => ($c['statut'] ?? '') === 'actif'));
$totalExpires      = count(array_filter($contrats, fn($c) => ($c['statut'] ?? '') === 'expiré'));
$totalAnnules      = count(array_filter($contrats, fn($c) => ($c['statut'] ?? '') === 'annulé'));
$offres            = $offreCtrl->listOffres()->fetchAll();
$offresSansContrat = $contratCtrl->offresSansContrat();
$flashError        = $flashError ?? null;

function badge_statut(?string $s): string {
    $map = ['actif' => 'success', 'expiré' => 'warning', 'annulé' => 'danger'];
    $cls = $map[$s ?? ''] ?? 'secondary';
    return '<span class="badge bg-label-' . $cls . '">' . htmlspecialchars($s ?: '—', ENT_QUOTES, 'UTF-8') . '</span>';
}
?>


<!doctype html>

<html
  lang="en"
  class="layout-menu-fixed layout-compact"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Contrats | Tableaux</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/css/dark-mode.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="../assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo"><img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:56px;height:56px;object-fit:contain;"></span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Tableau de bord : accueil + modules plateforme (pas de démos externes Académie / e-commerce) -->
            <li class="menu-item active open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Tableau de bord</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="index.html" class="menu-link">
                    <div class="text-truncate">Accueil</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-formations.html" class="menu-link">
                        <div class="text-truncate">Vue d&apos;ensemble</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-formations.html#sessions" class="menu-link">
                        <div class="text-truncate">Nos formations</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-inscriptions.html" class="menu-link">
                        <div class="text-truncate">Inscriptions</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-certificats.html" class="menu-link">
                        <div class="text-truncate">Certificats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item open">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Offres</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-offres.php" class="menu-link">
                        <div class="text-truncate">Liste des offres</div>
                      </a>
                    </li>
                    <li class="menu-item active">
                      <a href="gestion-contrats.php" class="menu-link">
                        <div class="text-truncate">Contrats</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-candidatures.php" class="menu-link">
                        <div class="text-truncate">Postuler</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-entretiens.html" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-produits.html" class="menu-link">
                    <div class="text-truncate">Produits</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-utilisateurs.html" class="menu-link">
                        <div class="text-truncate">Liste des utilisateurs</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="pages-account-settings-account.html" class="menu-link">
                        <div class="text-truncate">Profil</div>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>

            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Applications</span>
            </li>
            <li class="menu-item">
              <a href="email-boite.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div class="text-truncate">Email</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-chat-local.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chat"></i>
                <div class="text-truncate">Discuter</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-calendrier-local.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div class="text-truncate">Calendrier</div>
              </a>
            </li>

            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                <div class="text-truncate">Authentification</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="auth-login-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Connexion</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-register-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Inscription</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-forgot-password-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Mot de passe oublié</div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar">
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
                  <input
                    type="text"
                    class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none"
                    placeholder="Search..."
                    aria-label="Search..." />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Langue">
                    <i class="icon-base bx bx-globe icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-lang="fr">Français</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-lang="en">English</a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Disposition du menu">
                    <i class="icon-base bx bx-layout icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-layout="vertical">Menu vertical</a>
                    </li>
                    <li>
                      <a
                        class="dropdown-item"
                        href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/horizontal-menu-template/"
                        target="_blank"
                        rel="noopener"
                        >Menu horizontal <span class="badge bg-label-primary ms-1 text-uppercase fs-tiny">Pro</span></a
                      >
                    </li>
                  </ul>
                </li>
<!-- app-toolbar-extras -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Raccourcis">
                    <i class="icon-base bx bx-grid-alt icon-md"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end border-0 p-0">
                    <div class="dropdown-shortcuts-list">
                      <div class="row row-bordered g-0">
                        <div class="dropdown-shortcuts-item col-6 border-end border-bottom">
                          <a href="gestion-formations.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-book-open icon-md"></i></span>
                            <small class="text-body-secondary">Formations</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-bottom">
                          <a href="gestion-offres.php" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-briefcase icon-md"></i></span>
                            <small class="text-body-secondary">Offres</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-end">
                          <a href="gestion-reclamations.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-error-circle icon-md"></i></span>
                            <small class="text-body-secondary">Réclamations</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6">
                          <a href="gestion-produits.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-cart icon-md"></i></span>
                            <small class="text-body-secondary">Produits</small>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications">
                    <span class="position-relative">
                      <i class="icon-base bx bx-bell icon-md"></i>
                      <span
                        class="badge-notifications position-absolute" style="width:9px;height:9px;background:#ff3e1d;border-radius:50%;border:2px solid #fff;top:2px;right:-1px;"
                        >3</span
                      >
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-notifications-list">
                    <li class="dropdown-menu-header border-bottom">
                      <div class="d-flex align-items-center px-3 py-3">
                        <h6 class="mb-0">Notifications</h6>
                      </div>
                    </li>
                    <li class="list-group list-group-flush">
                      <a href="gestion-reclamations.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-primary"><i class="icon-base bx bx-error icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Réclamation à traiter</p>
                            <small class="text-body-secondary">Plateforme</small>
                          </div>
                        </div>
                      </a>
                      <a href="gestion-entretiens.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-warning"><i class="icon-base bx bx-calendar icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Entretien planifié demain</p>
                            <small class="text-body-secondary">Rappel</small>
                          </div>
                        </div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème clair ou sombre">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Couleurs et apparence">
                    <i class="icon-base bx bx-cog icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Couleur principale</h6></li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary-reset="1">Par défaut (Sneat)</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#696cff" data-app-primary-rgb="105, 108, 255">Violet</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#71dd37" data-app-primary-rgb="113, 221, 55">Vert</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#03c3ec" data-app-primary-rgb="3, 195, 236">Cyan</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#ff3e1d" data-app-primary-rgb="255, 62, 29">Rouge</a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html"
                        ><i class="icon-base bx bx-user icon-sm me-2"></i>Paramètres du compte</a
                      >
                    </li>
                  </ul>
                </li>


                <!-- Place this tag where you want the button to render. -->
                <li class="nav-item lh-1 me-4">
                  <a
                    class="github-button"
                    href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free"
                    data-icon="octicon-star"
                    data-size="large"
                    data-show-count="true"
                    aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
                    >Star</a
                  >
                </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a
                    class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-body-secondary">Admin</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i
                          ><span class="flex-grow-1 align-middle">Billing Plan</span>
                          <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="auth-login-basic.html">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-2">Gestion des contrats</h4>
  <p class="text-muted mb-4">Tableau des contrats liés aux offres. Le type (CDI/CDD/Stage) est défini au niveau de l'offre.</p>

  <?php if (!empty($flashError)): ?>
    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
      <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stat cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Contrats</span>
              <div class="d-flex align-items-end mt-2">
                <h4 class="mb-0 me-2" id="stat-total"><?= (int)$totalContrats ?></h4>
              </div>
              <p class="mb-0">Total contrats</p>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-file bx-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Actifs</span>
              <div class="d-flex align-items-end mt-2">
                <h4 class="mb-0 me-2"><?= (int)$totalActifs ?></h4>
              </div>
              <p class="mb-0">Contrats actifs</p>
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
              <span>Expirés</span>
              <div class="d-flex align-items-end mt-2">
                <h4 class="mb-0 me-2"><?= (int)$totalExpires ?></h4>
              </div>
              <p class="mb-0">Contrats expirés</p>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time bx-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span>Annulés</span>
              <div class="d-flex align-items-end mt-2">
                <h4 class="mb-0 me-2"><?= (int)$totalAnnules ?></h4>
              </div>
              <p class="mb-0">Contrats annulés</p>
            </div>
            <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x bx-sm"></i></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Table card -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title">Liste des contrats</h5>
      <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
        <div class="col-md-4">
          <select class="form-select text-capitalize" id="filter-statut">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="expiré">Expiré</option>
            <option value="annulé">Annulé</option>
          </select>
        </div>
        <div class="col-md-4">
          <input type="date" class="form-control" id="filter-date" placeholder="Filtrer par date de création">
        </div>
        <div class="col-md-4"></div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <div class="dataTables_wrapper dt-bootstrap5 no-footer">
        <div class="row mx-2 pt-3 pb-3">
          <div class="col-md-2 d-flex align-items-center">
            <select class="form-select w-auto" id="filter-perpage"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="0">Tous</option></select>
          </div>
          <div class="col-md-10">
            <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
              <div class="dataTables_filter">
                <input type="search" class="form-control" placeholder="Rechercher par nom d'offre..." id="search-contrat" />
              </div>
              <div class="dt-buttons btn-group flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contratModal" id="btn-add">
                  + Ajouter Contrat
                </button>
              </div>
            </div>
          </div>
        </div>

        <table class="table border-top dataTable" id="contrat-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>ID Offre</th>
              <th data-col="offre_titre">Offre (titre)</th>
              <th>Salaire</th>
              <th>Durée</th>
              <th data-col="date">Date création</th>
              <th data-col="statut">Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($contrats)): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">Aucun contrat enregistré.</td></tr>
            <?php else: ?>
              <?php foreach ($contrats as $c): ?>
                <?php
                  $dc = $c['dateCreation'] ?? '';
                  $rowJson = htmlspecialchars(json_encode([
                    'id'           => (int)$c['id'],
                    'offre_id'     => (int)$c['offre_id'],
                    'offre_titre'  => $c['offre_titre'] ?? '',
                    'offre_type'   => $c['offre_type'] ?? '',
                    'salaire'      => (string)($c['salaire'] ?? ''),
                    'duree'        => (string)($c['duree'] ?? ''),
                    'dateCreation' => (string)$dc,
                    'statut'       => (string)($c['statut'] ?? 'actif'),
                  ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                <tr
                  data-offre-titre="<?= htmlspecialchars(strtolower($c['offre_titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                  data-statut="<?= htmlspecialchars($c['statut'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  data-date="<?= htmlspecialchars($dc, ENT_QUOTES, 'UTF-8') ?>">
                  <td><span class="fw-medium"><?= (int)$c['id'] ?></span></td>
                  <td><?= (int)$c['offre_id'] ?></td>
                  <td><?= htmlspecialchars((string)($c['offre_titre'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string)($c['salaire'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string)($c['duree'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string)$dc, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= badge_statut($c['statut'] ?? '') ?></td>
                  <td>
                    <button class="btn btn-sm btn-info me-1" title="Voir" data-contrat="<?= $rowJson ?>" onclick="voirContrat(this)">
                      <i class="bx bx-show"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-1" data-contrat="<?= $rowJson ?>" onclick="editContrat(this)">
                      Edit
                    </button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce contrat ?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="row mx-2 mt-3 mb-3">
          <div class="col-sm-12 col-md-6 d-flex align-items-center">
            <div class="dataTables_info text-muted small" id="contrat-info">Total : <?= (int)$totalContrats ?> contrat(s)</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->

<script>
// ===== FILTRAGE CONTRATS =====
(function () {
    var tbody      = document.querySelector('#contrat-table tbody');
    var allRows    = Array.from(tbody.querySelectorAll('tr[data-statut]'));
    var infoEl     = document.getElementById('contrat-info');
    var perPageSel = document.getElementById('filter-perpage');

    function applyFilters() {
        var statut  = document.getElementById('filter-statut').value.toLowerCase();
        var date    = document.getElementById('filter-date').value;
        var search  = document.getElementById('search-contrat').value.toLowerCase().trim();
        var perPage = parseInt(perPageSel.value) || 0;

        var visible = 0;
        allRows.forEach(function (row) {
            var matchStatut = !statut  || row.dataset.statut === statut;
            var matchDate   = !date    || row.dataset.date === date;
            var matchSearch = !search  || row.dataset.offreTitre.includes(search);

            var show = matchStatut && matchDate && matchSearch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Pagination simple : masquer les lignes au-delà de perPage
        if (perPage > 0) {
            var shown = 0;
            allRows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    shown++;
                    if (shown > perPage) row.style.display = 'none';
                }
            });
            visible = Math.min(visible, perPage);
        }

        infoEl.textContent = visible + ' contrat(s) affiché(s)';
    }

    document.getElementById('filter-statut').addEventListener('change', applyFilters);
    document.getElementById('filter-date').addEventListener('change', applyFilters);
    document.getElementById('search-contrat').addEventListener('input', applyFilters);
    perPageSel.addEventListener('change', applyFilters);

    // Bouton reset filtres
    var btnReset = document.createElement('button');
    btnReset.className = 'btn btn-outline-secondary btn-sm ms-2';
    btnReset.textContent = 'Réinitialiser';
    btnReset.type = 'button';
    btnReset.addEventListener('click', function () {
        document.getElementById('filter-statut').value = '';
        document.getElementById('filter-date').value = '';
        document.getElementById('search-contrat').value = '';
        perPageSel.value = '10';
        applyFilters();
    });
    document.getElementById('filter-date').parentNode.appendChild(btnReset);
})();
</script>

<!-- MODAL Formulaire (Ajouter / Modifier) -->
<div class="modal fade" id="contratModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="contratForm" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="contratModalTitle">Nouveau contrat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" id="contrat_action" value="create">
          <input type="hidden" name="id"     id="contrat_id"     value="">

          <div class="mb-3">
            <label class="form-label">Offre associée <span class="text-danger">*</span></label>
            <select name="offre_id" id="contrat_offre_id" class="form-select">
              <option value="">— Choisir une offre —</option>
              <?php foreach ($offresSansContrat as $o): ?>
                <option value="<?= (int)$o['id'] ?>"
                        data-titre="<?= htmlspecialchars($o['titre'], ENT_QUOTES, 'UTF-8') ?>"
                        data-type="<?= htmlspecialchars($o['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  #<?= (int)$o['id'] ?> — <?= htmlspecialchars($o['titre'], ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($o['type'])): ?>(<?= htmlspecialchars($o['type'], ENT_QUOTES, 'UTF-8') ?>)<?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback" id="err_offre_id"></div>
            <small class="text-muted">Seules les offres sans contrat sont listées.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Salaire <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" min="0" step="0.01" name="salaire" id="contrat_salaire"
                     class="form-control" placeholder="ex. 45000">
              <select name="devise" id="contrat_devise" class="form-select" style="max-width:110px;">
                <option value="DZD">DZD — Dinar algérien</option>
                <option value="EUR">EUR — Euro</option>
                <option value="USD">USD — Dollar US</option>
                <option value="GBP">GBP — Livre sterling</option>
                <option value="MAD">MAD — Dirham marocain</option>
                <option value="TND">TND — Dinar tunisien</option>
                <option value="SAR">SAR — Riyal saoudien</option>
                <option value="AED">AED — Dirham EAU</option>
              </select>
            </div>
            <div class="invalid-feedback d-block" id="err_salaire"></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Durée <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" min="1" step="1" name="duree_valeur" id="contrat_duree_valeur"
                     class="form-control" placeholder="ex. 12">
              <select name="duree_unite" id="contrat_duree_unite" class="form-select" style="max-width:130px;">
                <option value="mois">Mois</option>
                <option value="années">Années</option>
              </select>
            </div>
            <div class="invalid-feedback d-block" id="err_duree"></div>
            <!-- champ caché qui reçoit la valeur combinée envoyée au serveur -->
            <input type="hidden" name="duree" id="contrat_duree">
          </div>

          <div class="mb-3">
            <label class="form-label">Date de création <span class="text-danger">*</span></label>
            <input type="text" name="dateCreation" id="contrat_date" class="form-control" placeholder="AAAA-MM-JJ">
            <div class="invalid-feedback" id="err_date"></div>
          </div>

          <div class="mb-0">
            <label class="form-label">Statut</label>
            <select name="statut" id="contrat_statut" class="form-select">
              <option value="actif">Actif</option>
              <option value="expiré">Expiré</option>
              <option value="annulé">Annulé</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL Observation (lecture seule) -->
<div class="modal fade" id="contratViewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Détail du contrat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">ID contrat</dt>   <dd class="col-sm-8" id="v_id">—</dd>
          <dt class="col-sm-4">ID offre</dt>     <dd class="col-sm-8" id="v_offre_id">—</dd>
          <dt class="col-sm-4">Offre</dt>        <dd class="col-sm-8" id="v_offre_titre">—</dd>
          <dt class="col-sm-4">Type offre</dt>   <dd class="col-sm-8" id="v_offre_type">—</dd>
          <dt class="col-sm-4">Salaire</dt>      <dd class="col-sm-8" id="v_salaire">—</dd>
          <dt class="col-sm-4">Durée</dt>        <dd class="col-sm-8" id="v_duree">—</dd>
          <dt class="col-sm-4">Date création</dt><dd class="col-sm-8" id="v_date">—</dd>
          <dt class="col-sm-4">Statut</dt>       <dd class="col-sm-8" id="v_statut">—</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
// ===== HELPERS =====
function clearContratErrors() {
    ['contrat_offre_id','contrat_salaire','contrat_duree_valeur'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });
    ['err_offre_id','err_salaire','err_duree','err_date'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.textContent = '';
    });
}

function showContratError(fieldId, errId, msg) {
    var el = document.getElementById(fieldId);
    var er = document.getElementById(errId);
    if (el) el.classList.add('is-invalid');
    if (er) er.textContent = msg;
}

function isValidDate(str) {
    var r = /^\d{4}-\d{2}-\d{2}$/;
    if (!r.test(str)) return false;
    var d = new Date(str);
    return d instanceof Date && !isNaN(d);
}

// Construit la valeur combinée "12 mois" ou "2 années" dans le champ caché
function buildDureeHidden() {
    var val   = document.getElementById('contrat_duree_valeur').value.trim();
    var unite = document.getElementById('contrat_duree_unite').value;
    document.getElementById('contrat_duree').value = val ? (val + ' ' + unite) : '';
}

// Construit la valeur combinée "45000 DZD" dans le champ salaire
// (on stocke montant + devise dans la colonne salaire sous forme "45000 DZD")
// Le champ name="salaire" envoie le montant, name="devise" envoie la devise
// Le controller les concatène côté serveur — voir ContratController

// ===== RESET MODAL =====
function resetContratModal() {
    document.getElementById('contratForm').reset();
    document.getElementById('contrat_action').value = 'create';
    document.getElementById('contrat_id').value = '';
    document.getElementById('contratModalTitle').textContent = 'Nouveau contrat';
    clearContratErrors();
    document.getElementById('contrat_date').value = new Date().toISOString().slice(0,10);
    document.getElementById('contrat_duree').value = '';
    // Supprimer les options temporaires ajoutées en mode édition
    var sel = document.getElementById('contrat_offre_id');
    var toRemove = sel.querySelectorAll('[data-edit-only]');
    toRemove.forEach(function(o) { sel.removeChild(o); });
}

document.getElementById('btn-add').addEventListener('click', resetContratModal);

// ===== VOIR =====
window.voirContrat = function(btn) {
    var d = JSON.parse(btn.getAttribute('data-contrat'));
    document.getElementById('v_id').textContent          = d.id;
    document.getElementById('v_offre_id').textContent    = d.offre_id;
    document.getElementById('v_offre_titre').textContent = d.offre_titre || '—';
    document.getElementById('v_offre_type').textContent  = d.offre_type  || '—';
    document.getElementById('v_salaire').textContent     = d.salaire;
    document.getElementById('v_duree').textContent       = d.duree || '—';
    document.getElementById('v_date').textContent        = d.dateCreation || '—';
    document.getElementById('v_statut').textContent      = d.statut || '—';
    new bootstrap.Modal(document.getElementById('contratViewModal')).show();
};

// ===== EDIT =====
// Décompose "12 mois" → valeur=12, unité=mois
function parseDuree(str) {
    str = (str || '').trim();
    var m = str.match(/^(\d+)\s*(mois|années|annees|ans|année|an)$/i);
    if (m) {
        var u = m[2].toLowerCase();
        if (u === 'ans' || u === 'an' || u === 'annees' || u === 'année') u = 'années';
        return { val: m[1], unite: u };
    }
    return { val: str, unite: 'mois' };
}

window.editContrat = function(btn) {
    var d = JSON.parse(btn.getAttribute('data-contrat'));
    resetContratModal();
    document.getElementById('contrat_action').value          = 'update';
    document.getElementById('contrat_id').value              = d.id;
    document.getElementById('contratModalTitle').textContent = 'Modifier contrat #' + d.id;

    // Injecter l'offre déjà liée dans le select si elle n'y est pas (offre déjà prise)
    var sel = document.getElementById('contrat_offre_id');
    var exists = false;
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === String(d.offre_id)) { exists = true; break; }
    }
    if (!exists) {
        var opt = document.createElement('option');
        opt.value = d.offre_id;
        opt.textContent = '#' + d.offre_id + ' — ' + (d.offre_titre || 'Offre #' + d.offre_id);
        opt.setAttribute('data-edit-only', '1'); // marquée pour suppression au reset
        sel.appendChild(opt);
    }
    sel.value = String(d.offre_id);

    // Salaire : décompose "45000 DZD" → montant + devise
    var salStr = String(d.salaire || '');
    var salParts = salStr.match(/^([\d.]+)\s*([A-Z]{3})?$/);
    if (salParts) {
        document.getElementById('contrat_salaire').value = salParts[1];
        if (salParts[2]) document.getElementById('contrat_devise').value = salParts[2];
    } else {
        document.getElementById('contrat_salaire').value = salStr;
    }

    // Durée : décompose "12 mois" → valeur + unité
    var dp = parseDuree(d.duree);
    document.getElementById('contrat_duree_valeur').value = dp.val;
    document.getElementById('contrat_duree_unite').value  = dp.unite;
    document.getElementById('contrat_duree').value        = d.duree;

    document.getElementById('contrat_date').value   = (d.dateCreation || '').slice(0,10);
    document.getElementById('contrat_statut').value = d.statut;
    new bootstrap.Modal(document.getElementById('contratModal')).show();
};

// ===== VALIDATION SUBMIT =====
document.getElementById('contratForm').addEventListener('submit', function(e) {
    clearContratErrors();
    var ok = true;

    // Offre
    if (!document.getElementById('contrat_offre_id').value) {
        showContratError('contrat_offre_id','err_offre_id','Choisissez une offre existante.');
        ok = false;
    }

    // Salaire
    var sal = parseFloat(document.getElementById('contrat_salaire').value);
    if (isNaN(sal) || sal < 0) {
        showContratError('contrat_salaire','err_salaire','Salaire invalide (nombre positif attendu).');
        ok = false;
    }

    // Durée : construire le champ caché avant envoi
    var durVal = document.getElementById('contrat_duree_valeur').value.trim();
    if (!durVal || isNaN(parseInt(durVal)) || parseInt(durVal) < 1) {
        showContratError('contrat_duree_valeur','err_duree','Entrez une durée valide (nombre entier positif).');
        ok = false;
    } else {
        buildDureeHidden();
    }

    // Date
    var dt = document.getElementById('contrat_date').value.trim();
    if (!dt) {
        showContratError('contrat_date','err_date','La date est obligatoire.');
        ok = false;
    } else if (!isValidDate(dt)) {
        showContratError('contrat_date','err_date','Format invalide (AAAA-MM-JJ attendu).');
        ok = false;
    }

    if (!ok) e.preventDefault();
});

// Effacer erreur à la saisie
['contrat_offre_id','contrat_salaire','contrat_duree_valeur','contrat_date'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('input',  function() { el.classList.remove('is-invalid'); });
        el.addEventListener('change', function() { el.classList.remove('is-invalid'); });
    }
});
</script>

            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    &copy;
                    <script>
                      document.write(new Date().getFullYear());
                    </script>
                    , made with &#10084;&#65039; by
                    <a href="https://themeselection.com" target="_blank" class="footer-link">ThemeSelection</a>
                  </div>
                  <div class="d-none d-lg-inline-block">
                    <a
                      href="https://themeselection.com/item/category/admin-templates/"
                      target="_blank"
                      class="footer-link me-4"
                      >Admin Templates</a
                    >

                    <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>
                    <a
                      href="https://themeselection.com/item/category/bootstrap-admin-templates/"
                      target="_blank"
                      class="footer-link me-4"
                      >Bootstrap Dashboard</a
                    >

                    <a
                      href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/documentation/"
                      target="_blank"
                      class="footer-link me-4"
                      >Documentation</a
                    >

                    <a
                      href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free/issues"
                      target="_blank"
                      class="footer-link"
                      >Support</a
                    >
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <div class="buy-now">
      <a
        href="https://themeselection.com/item/sneat-dashboard-pro-bootstrap/"
        target="_blank"
        class="btn btn-danger btn-buy-now"
        >Upgrade to Pro</a
      >
    </div>

    <!-- Core JS -->

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>

    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>

    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Main JS -->

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/navbar-extras.js"></script>


    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>
