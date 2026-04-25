<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/OffreController.php';

$controller = new OffreController();

/* ================= CRUD ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $offre = new Offre(
            trim($_POST['titre'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['type'] ?? ''),
            trim($_POST['datePublication'] ?? '')
        );
        $controller->addOffre($offre);
        header("Location: gestion-offres.php");
        exit;
    }

    if ($_POST['action'] === 'update') {
        $offre = new Offre(
            trim($_POST['titre'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['type'] ?? ''),
            trim($_POST['datePublication'] ?? '')
        );
        $controller->updateOffre((int)$_POST['id'], $offre);
        header("Location: gestion-offres.php");
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $controller->deleteOffre((int)$_POST['id']);
        header("Location: gestion-offres.php");
        exit;
    }
}

$offres = $controller->listOffres()->fetchAll();
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

    <title>Offres | Tableaux</title>

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
                    <li class="menu-item active">
                      <a href="gestion-offres.php" class="menu-link">
                        <div class="text-truncate">Liste des offres</div>
                      </a>
                    </li>
                    <li class="menu-item">
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
              <h4 class="fw-bold py-3 mb-2">Gestion des offres</h4>
              <p class="text-muted mb-4">Tableaux avec actions Modifier / Supprimer (menu ⋮).</p>

              <div class="row g-6 mb-6">
                <div class="col-sm-6 col-xl-3">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                          <span>Offres</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">2,156</h4>
                            <small class="text-success">(+19%)</small>
                          </div>
                          <p class="mb-0">Total offres</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-briefcase bx-sm"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                          <span>Publiées</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">1,480</h4>
                            <small class="text-success">(+14%)</small>
                          </div>
                          <p class="mb-0">Analyse semaine</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-success">
                            <i class="bx bx-check bx-sm"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                          <span>Brouillons</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">312</h4>
                            <small class="text-success">(+28%)</small>
                          </div>
                          <p class="mb-0">Analyse semaine</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-edit bx-sm"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                          <span>Archivées</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">364</h4>
                            <small class="text-danger">(-4%)</small>
                          </div>
                          <p class="mb-0">Analyse semaine</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-danger">
                            <i class="bx bx-archive bx-sm"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="card-header border-bottom">
                  <h5 class="card-title">Filtres de recherche</h5>
                  <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                    <div class="col-md-4">
                      <select class="form-select text-capitalize" id="filter-type">
                        <option value="">Tous les types</option>
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                        <option value="Freelance">Freelance</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <input type="date" class="form-control" id="filter-date-offre" placeholder="Filtrer par date de publication">
                    </div>
                    <div class="col-md-4"></div>
                  </div>
                </div>
                <div class="card-datatable table-responsive">
                  <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="row mx-2 pt-3 pb-3">
                      <div class="col-md-2 d-flex align-items-center">
                        <select class="form-select w-auto" id="filter-perpage-offre"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="0">Tous</option></select>
                      </div>
                      <div class="col-md-10">
                        <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
                          <div class="dataTables_filter">
                            <input type="search" class="form-control" placeholder="Rechercher par titre d'offre..." id="search-offre" />
                          </div>
                          <div class="dt-buttons btn-group flex-wrap">
                    
    <!-- BUTTON ADD -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#offreModal">
        + Ajouter Offre
    </button>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <table class="table border-top dataTable" id="offre-table">
                 <thead>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Type</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        </thead>

 <tbody>
        <?php foreach ($offres as $o): ?>
            <tr
              data-titre="<?= htmlspecialchars(strtolower($o['titre']), ENT_QUOTES, 'UTF-8') ?>"
              data-type="<?= htmlspecialchars(strtolower($o['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              data-date="<?= htmlspecialchars($o['datePublication'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <td><?= htmlspecialchars($o['titre']) ?></td>
                <td><?= htmlspecialchars($o['description']) ?></td>
                <td><?= htmlspecialchars($o['type']) ?></td>
                <td><?= htmlspecialchars($o['datePublication']) ?></td>

                <td>
                    <!-- EDIT -->
                    <button class="btn btn-sm btn-warning"
                        onclick='editOffre(<?= json_encode($o) ?>)'>
                        Edit
                    </button>

                    <!-- DELETE -->
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete ?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>


        <!-- ================= MODAL ================= -->
<div class="modal fade" id="offreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST" id="offreForm" novalidate>

        <div class="modal-header">
          <h5 class="modal-title">Offre</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <input type="hidden" name="id" id="id">
            <input type="hidden" name="action" id="action" value="create">

            <div class="mb-2">
                <label>Titre</label>
                <input type="text" name="titre" id="titre" class="form-control" novalidate>
                <div class="invalid-feedback" id="titre-error"></div>
            </div>

            <div class="mb-2">
                <label>Description</label>
                <textarea name="description" id="description" class="form-control" novalidate></textarea>
                <div class="invalid-feedback" id="description-error"></div>
            </div>

            <div class="mb-2">
                <label>Type</label>
                <select name="type" id="type" class="form-control" novalidate>
                    <option value="">-- Sélectionner --</option>
                    <option>CDI</option>
                    <option>CDD</option>
                    <option>Stage</option>
                </select>
                <div class="invalid-feedback" id="type-error"></div>
            </div>

            <div class="mb-2">
                <label>Date</label>
                <input type="text" name="datePublication" id="datePublication" class="form-control" placeholder="AAAA-MM-JJ" novalidate>
                <div class="invalid-feedback" id="datePublication-error"></div>
            </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>

      </form>

    </div>
  </div>
</div>



<!-- ================= JS ================= -->
<script>

function editOffre(o) {
    document.getElementById('id').value = o.id;
    document.getElementById('titre').value = o.titre;
    document.getElementById('description').value = o.description;
    document.getElementById('type').value = o.type;
    document.getElementById('datePublication').value = o.datePublication;

    document.getElementById('action').value = 'update';

    let modal = new bootstrap.Modal(document.getElementById('offreModal'));
    modal.show();
}

// reset when opening for new
document.querySelector('[data-bs-target="#offreModal"]').addEventListener('click', () => {
    document.getElementById('offreForm').reset();
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    clearErrors();
});

</script>






                    </table>
                    <div class="row mx-2 mt-3 mb-3">
                      <div class="col-sm-12 col-md-6 d-flex align-items-center">
                        <div class="dataTables_info text-muted small">Affichage de 1 à 10 sur 50 entrées</div>
                      </div>
                      <div class="col-sm-12 col-md-6 d-flex justify-content-end">
                        <ul class="pagination pagination-sm m-0">
                          <li class="page-item disabled"><a href="#" class="page-link">Précédent</a></li>
                          <li class="page-item active"><a href="#" class="page-link">1</a></li>
                          <li class="page-item"><a href="#" class="page-link">2</a></li>
                          <li class="page-item"><a href="#" class="page-link">3</a></li>
                          <li class="page-item"><a href="#" class="page-link">Suivant</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>



<script>

// ================= FILL EDIT =================
function fillEditModal(data) {
    document.getElementById('offre_id').value = data.id;
    document.getElementById('titre').value = data.titre;
    document.getElementById('description').value = data.description;
    document.getElementById('type').value = data.type;
    document.getElementById('datePublication').value = data.datePublication;

    document.getElementById('form_action').value = 'update';

    let modal = new bootstrap.Modal(document.getElementById('addOffreModal'));
    modal.show();
}

// ================= CLIENT VALIDATION JS =================

function clearErrors() {
    ['titre', 'description', 'type', 'datePublication'].forEach(function(field) {
        var el = document.getElementById(field);
        var err = document.getElementById(field + '-error');
        if (el) el.classList.remove('is-invalid');
        if (err) err.textContent = '';
    });
}

function showError(fieldId, message) {
    var el = document.getElementById(fieldId);
    var err = document.getElementById(fieldId + '-error');
    if (el) el.classList.add('is-invalid');
    if (err) err.textContent = message;
}

function isValidDate(str) {
    // Accepte le format AAAA-MM-JJ
    var regex = /^\d{4}-\d{2}-\d{2}$/;
    if (!regex.test(str)) return false;
    var d = new Date(str);
    return d instanceof Date && !isNaN(d);
}

document.getElementById("offreForm").addEventListener("submit", function(e) {
    clearErrors();

    var titre       = document.getElementById("titre").value.trim();
    var description = document.getElementById("description").value.trim();
    var type        = document.getElementById("type").value;
    var date        = document.getElementById("datePublication").value.trim();

    var valid = true;

    if (titre.length === 0) {
        showError('titre', 'Le titre est obligatoire.');
        valid = false;
    } else if (titre.length < 3) {
        showError('titre', 'Le titre doit contenir au moins 3 caractères.');
        valid = false;
    }

    if (description.length === 0) {
        showError('description', 'La description est obligatoire.');
        valid = false;
    } else if (description.length < 10) {
        showError('description', 'La description doit contenir au moins 10 caractères.');
        valid = false;
    }

    if (!type) {
        showError('type', 'Veuillez sélectionner un type.');
        valid = false;
    }

    if (date.length === 0) {
        showError('datePublication', 'La date est obligatoire.');
        valid = false;
    } else if (!isValidDate(date)) {
        showError('datePublication', 'Format de date invalide (AAAA-MM-JJ attendu).');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
    }
});

// Effacer l'erreur d'un champ dès que l'utilisateur commence à le corriger
['titre', 'description', 'type', 'datePublication'].forEach(function(field) {
    var el = document.getElementById(field);
    if (el) {
        el.addEventListener('input', function() {
            el.classList.remove('is-invalid');
            var err = document.getElementById(field + '-error');
            if (err) err.textContent = '';
        });
        el.addEventListener('change', function() {
            el.classList.remove('is-invalid');
            var err = document.getElementById(field + '-error');
            if (err) err.textContent = '';
        });
    }
});

</script>

<script>
// ===== FILTRAGE OFFRES =====
(function () {
    var tbody      = document.querySelector('#offre-table tbody');
    if (!tbody) return;
    var allRows    = Array.from(tbody.querySelectorAll('tr[data-titre]'));
    var perPageSel = document.getElementById('filter-perpage-offre');

    function applyFilters() {
        var type    = document.getElementById('filter-type').value.toLowerCase();
        var date    = document.getElementById('filter-date-offre').value;
        var search  = document.getElementById('search-offre').value.toLowerCase().trim();
        var perPage = parseInt(perPageSel.value) || 0;

        var visible = 0;
        allRows.forEach(function (row) {
            var matchType   = !type   || row.dataset.type === type;
            var matchDate   = !date   || row.dataset.date === date;
            var matchSearch = !search || row.dataset.titre.includes(search);

            var show = matchType && matchDate && matchSearch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (perPage > 0) {
            var shown = 0;
            allRows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    shown++;
                    if (shown > perPage) row.style.display = 'none';
                }
            });
        }
    }

    document.getElementById('filter-type').addEventListener('change', applyFilters);
    document.getElementById('filter-date-offre').addEventListener('change', applyFilters);
    document.getElementById('search-offre').addEventListener('input', applyFilters);
    perPageSel.addEventListener('change', applyFilters);

    // Bouton reset
    var btnReset = document.createElement('button');
    btnReset.className = 'btn btn-outline-secondary btn-sm ms-2';
    btnReset.textContent = 'Réinitialiser';
    btnReset.type = 'button';
    btnReset.addEventListener('click', function () {
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-date-offre').value = '';
        document.getElementById('search-offre').value = '';
        perPageSel.value = '10';
        applyFilters();
    });
    document.getElementById('filter-date-offre').parentNode.appendChild(btnReset);
})();
</script>



            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    ©
                    <script>
                      document.write(new Date().getFullYear());
                    </script>
                    , made with ❤️ by
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
