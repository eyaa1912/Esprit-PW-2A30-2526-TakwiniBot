<!doctype html>

<html
  lang="fr"
  class="layout-menu-fixed layout-compact"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Tableau de bord | Plateforme de Formation</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/css/dark-mode.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <style>
      /* Offcanvas Parametres */
      #parametresOffcanvas { width: 340px; }
      .param-section-label {
        display: inline-block;
        background: rgba(105,108,255,.12);
        color: #696cff;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 2px 12px;
        border-radius: 20px;
        margin-bottom: 1rem;
      }
      .color-swatch {
        width: 36px; height: 36px;
        border-radius: 8px; cursor: pointer;
        border: 2.5px solid transparent;
        transition: border-color .15s;
        position: relative;
        display: flex; align-items: center; justify-content: center;
      }
      .color-swatch.active, .color-swatch:hover { border-color: #696cff; }
      .theme-btn {
        flex: 1; border: 1.5px solid #dbdade;
        border-radius: 8px; padding: 10px 6px 7px;
        cursor: pointer; text-align: center;
        background: var(--bs-body-bg, #fff);
        transition: border-color .15s, box-shadow .15s;
      }
      .theme-btn.active, .theme-btn:hover {
        border-color: #696cff;
        box-shadow: 0 0 0 1px #696cff;
      }
      .theme-btn i { font-size: 20px; display: block; margin-bottom: 4px; }
      .theme-btn small { font-size: .75rem; }
      .skin-box {
        flex: 1; border: 1.5px solid #dbdade;
        border-radius: 8px; overflow: hidden;
        cursor: pointer; padding: 7px;
        transition: border-color .15s;
        background: #f8f7fa;
      }
      .skin-box.active, .skin-box:hover { border-color: #696cff; }
      .skin-box .sb-row { height: 5px; background: #ddd; border-radius: 3px; margin-bottom: 3px; }
      .skin-box .sb-side { width: 24px; min-height: 36px; background: #e4e4e4; border-radius: 3px; }
      /* Notification list scroll */
      .notif-list { max-height: 320px; overflow-y: auto; }
      /* Stats tabs compact */
      #statsTabNav .nav-link { font-size: .78rem; padding: 5px 11px; }
    </style>

    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        <!-- ══════════════════════ MENU ══════════════════════ -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo"><img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:200px;height:76px;object-fit:contain;"></span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>
          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">

            <!-- Tableau de bord -->
            <li class="menu-item active open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Tableau de bord</div>
              </a>
              <ul class="menu-sub">

                <li class="menu-item active">
                  <a href="index.html" class="menu-link">
                    <div class="text-truncate">Accueil</div>
                  </a>
                </li>

                <!-- Formations -->
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-formations.html" class="menu-link">
                        <div class="text-truncate">Formation</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-inscriptions.html" class="menu-link">
                        <div class="text-truncate">Inscription</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-certificats.html" class="menu-link">
                        <div class="text-truncate">Certificat</div>
                      </a>
                    </li>
                  </ul>
                </li>

                <!-- Entretiens -->
                <li class="menu-item">
                  <a href="gestion-entretiens.html" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>

                <!-- Utilisateurs -->
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

                <!-- Offres -->
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Offres</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-offres.php" class="menu-link">
                        <div class="text-truncate">Liste offres</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-contrats.html" class="menu-link">
                        <div class="text-truncate">Contrat</div>
                      </a>
                    </li>
                  </ul>
                </li>

                <!-- Produits -->
                <li class="menu-item">
                  <a href="gestion-produits.html" class="menu-link">
                    <div class="text-truncate">Produits</div>
                  </a>
                </li>

                <!-- Réclamations -->
                <li class="menu-item">
                  <a href="gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>

              </ul>
            </li>

            <!-- Applications -->
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

            <!-- Auth -->
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

        <!-- ══════════════════════ LAYOUT PAGE ══════════════════════ -->
        <div class="layout-page">

          <!-- NAVBAR -->
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
                  <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Rechercher..." aria-label="Rechercher..." />
                </div>
              </div>

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">

                <!-- Globe langue -->
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Langue">
                    <i class="icon-base bx bx-globe icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0);" data-app-lang="fr">Français</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);" data-app-lang="en">English</a></li>
                  </ul>
                </li>

                <!-- Thème clair/sombre -->
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>

                <!-- Raccourcis grille -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-1">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Raccourcis">
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
                          <a href="gestion-entretiens.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-calendar-check icon-md"></i></span>
                            <small class="text-body-secondary">Entretiens</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-end">
                          <a href="gestion-offres.php" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-briefcase icon-md"></i></span>
                            <small class="text-body-secondary">Offres</small>
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

                <!-- NOTIFICATIONS -->
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
                    <span class="position-relative">
                      <i class="icon-base bx bx-bell icon-md"></i>
                      <span class="badge-notifications position-absolute" style="width:9px;height:9px;background:#ff3e1d;border-radius:50%;border:2px solid #fff;top:2px;right:-1px;" id="notif-count">5</span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width:340px; max-width:360px;">
                    <li class="border-bottom px-3 py-3">
                      <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">Notifications</h6>
                        <span class="badge bg-label-primary rounded-pill" id="notif-badge">5 nouvelles</span>
                      </div>
                    </li>
                    <li class="notif-list">
                      <a href="gestion-formations.html" class="list-group-item list-group-item-action dropdown-notifications-item px-3 py-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar avatar-sm bg-label-primary flex-shrink-0"><i class="icon-base bx bx-book-open icon-sm"></i></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small fw-medium">Nouvelle inscription formation</p>
                            <small class="text-body-secondary">Ahmed Ben Ali — il y a 5 min</small>
                          </div>
                          <button type="button" class="btn-close btn-close-sm flex-shrink-0 mt-1" aria-label="Fermer" onclick="dismissNotif(this,event)"></button>
                        </div>
                      </a>
                      <a href="gestion-entretiens.html" class="list-group-item list-group-item-action dropdown-notifications-item px-3 py-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar avatar-sm bg-label-warning flex-shrink-0"><i class="icon-base bx bx-calendar icon-sm"></i></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small fw-medium">Entretien planifié demain</p>
                            <small class="text-body-secondary">10h00 — Salle B · Rappel</small>
                          </div>
                          <button type="button" class="btn-close btn-close-sm flex-shrink-0 mt-1" aria-label="Fermer" onclick="dismissNotif(this,event)"></button>
                        </div>
                      </a>
                      <a href="gestion-utilisateurs.html" class="list-group-item list-group-item-action dropdown-notifications-item px-3 py-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar avatar-sm bg-label-success flex-shrink-0"><i class="icon-base bx bx-user-check icon-sm"></i></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small fw-medium">Nouvel utilisateur inscrit</p>
                            <small class="text-body-secondary">Meriem Trabelsi — il y a 1 h</small>
                          </div>
                          <button type="button" class="btn-close btn-close-sm flex-shrink-0 mt-1" aria-label="Fermer" onclick="dismissNotif(this,event)"></button>
                        </div>
                      </a>
                      <a href="gestion-offres.php" class="list-group-item list-group-item-action dropdown-notifications-item px-3 py-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar avatar-sm bg-label-info flex-shrink-0"><i class="icon-base bx bx-briefcase icon-sm"></i></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small fw-medium">Nouvelle offre soumise</p>
                            <small class="text-body-secondary">Offre #2024-089 — à valider</small>
                          </div>
                          <button type="button" class="btn-close btn-close-sm flex-shrink-0 mt-1" aria-label="Fermer" onclick="dismissNotif(this,event)"></button>
                        </div>
                      </a>
                      <a href="gestion-reclamations.html" class="list-group-item list-group-item-action dropdown-notifications-item px-3 py-3">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar avatar-sm bg-label-danger flex-shrink-0"><i class="icon-base bx bx-error-circle icon-sm"></i></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small fw-medium">Réclamation à traiter</p>
                            <small class="text-body-secondary">Priorité haute — Plateforme</small>
                          </div>
                          <button type="button" class="btn-close btn-close-sm flex-shrink-0 mt-1" aria-label="Fermer" onclick="dismissNotif(this,event)"></button>
                        </div>
                      </a>
                    </li>
                    <li class="border-top text-center px-3 py-2">
                      <a href="javascript:void(0);" class="small text-primary fw-medium" onclick="markAllRead()">Tout marquer comme lu</a>
                    </li>
                  </ul>
                </li>

                <!-- PARAMÈTRES (offcanvas) -->
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#parametresOffcanvas"
                    aria-controls="parametresOffcanvas"
                    aria-label="Paramètres">
                    <i class="icon-base bx bx-cog icon-md"></i>
                  </a>
                </li>

                <!-- USER -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
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
                            <small class="text-body-secondary">Administrateur</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>Mon profil</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="pages-account-settings-account.html">
                        <i class="icon-base bx bx-cog icon-md me-3"></i><span>Paramètres</span>
                      </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                      <a class="dropdown-item" href="auth-login-basic.html">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span>
                      </a>
                    </li>
                  </ul>
                </li>

              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- ══════════════════════ CONTENT ══════════════════════ -->
          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">

              <!-- Bienvenue -->
              <div class="row">
                <div class="col-xxl-8 mb-6 order-0">
                  <div class="card">
                    <div class="d-flex align-items-start row">
                      <div class="col-sm-7">
                        <div class="card-body">
                          <h5 class="card-title text-primary mb-3">Bienvenue sur la plateforme 🎉</h5>
                          <p class="mb-6">Gérez vos formations, entretiens, utilisateurs, offres et produits depuis cet espace centralisé.</p>
                          <a href="gestion-formations.html" class="btn btn-sm btn-outline-primary">Voir les formations</a>
                        </div>
                      </div>
                      <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-6">
                          <img src="../assets/img/illustrations/man-with-laptop.png" height="175" alt="Tableau de bord" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
                  <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                      <div class="card h-100">
                        <div class="card-body">
                          <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                              <img src="../assets/img/icons/unicons/chart-success.png" alt="Formations" class="rounded" />
                            </div>
                          </div>
                          <p class="mb-1">Formations actives</p>
                          <h4 class="card-title mb-3">24</h4>
                          <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> +3 ce mois</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-6">
                      <div class="card h-100">
                        <div class="card-body">
                          <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                              <img src="../assets/img/icons/unicons/wallet-info.png" alt="Utilisateurs" class="rounded" />
                            </div>
                          </div>
                          <p class="mb-1">Utilisateurs inscrits</p>
                          <h4 class="card-title mb-3">1 248</h4>
                          <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> +12.4%</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ── STATISTIQUES avec onglets ── -->
              <div class="row mb-6">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3 pb-0">
                      <h5 class="card-title mb-3">Statistiques</h5>
                      <ul class="nav nav-pills mb-3" id="statsTabNav" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#stats-formations" type="button" role="tab" aria-selected="true">Formations</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#stats-entretiens" type="button" role="tab" aria-selected="false">Entretiens</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#stats-utilisateurs" type="button" role="tab" aria-selected="false">Utilisateurs</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#stats-offres" type="button" role="tab" aria-selected="false">Offres</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#stats-produits" type="button" role="tab" aria-selected="false">Produits</button>
                        </li>
                      </ul>
                    </div>
                    <div class="card-body pt-3">
                      <div class="tab-content">

                        <!-- Formations -->
                        <div class="tab-pane fade show active" id="stats-formations" role="tabpanel">
                          <div class="row g-4">
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-primary"><i class="icon-base bx bx-book-open icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Total</small><h5 class="mb-0">24</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-success"><i class="icon-base bx bx-check-circle icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Terminées</small><h5 class="mb-0">18</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-warning"><i class="icon-base bx bx-time icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">En cours</small><h5 class="mb-0">6</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-info"><i class="icon-base bx bx-award icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Certificats émis</small><h5 class="mb-0">142</h5></div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Entretiens -->
                        <div class="tab-pane fade" id="stats-entretiens" role="tabpanel">
                          <div class="row g-4">
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-primary"><i class="icon-base bx bx-calendar icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Total</small><h5 class="mb-0">58</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-success"><i class="icon-base bx bx-check-double icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Effectués</small><h5 class="mb-0">41</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-warning"><i class="icon-base bx bx-alarm icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">À venir</small><h5 class="mb-0">17</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-danger"><i class="icon-base bx bx-x-circle icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Annulés</small><h5 class="mb-0">3</h5></div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Utilisateurs -->
                        <div class="tab-pane fade" id="stats-utilisateurs" role="tabpanel">
                          <div class="row g-4">
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-primary"><i class="icon-base bx bx-group icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Total</small><h5 class="mb-0">1 248</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-success"><i class="icon-base bx bx-user-check icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Actifs</small><h5 class="mb-0">1 104</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-warning"><i class="icon-base bx bx-user-plus icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Nouveaux</small><h5 class="mb-0">38</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-danger"><i class="icon-base bx bx-user-x icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Inactifs</small><h5 class="mb-0">144</h5></div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Offres -->
                        <div class="tab-pane fade" id="stats-offres" role="tabpanel">
                          <div class="row g-4">
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-primary"><i class="icon-base bx bx-briefcase icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Total offres</small><h5 class="mb-0">87</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-success"><i class="icon-base bx bx-check-shield icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Contrats signés</small><h5 class="mb-0">52</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-warning"><i class="icon-base bx bx-hourglass icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">En attente</small><h5 class="mb-0">21</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-danger"><i class="icon-base bx bx-block icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Refusées</small><h5 class="mb-0">14</h5></div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Produits -->
                        <div class="tab-pane fade" id="stats-produits" role="tabpanel">
                          <div class="row g-4">
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-primary"><i class="icon-base bx bx-package icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Total</small><h5 class="mb-0">312</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-success"><i class="icon-base bx bx-store icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">En stock</small><h5 class="mb-0">274</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-warning"><i class="icon-base bx bx-error icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Stock faible</small><h5 class="mb-0">28</h5></div>
                              </div>
                            </div>
                            <div class="col-6 col-md-3">
                              <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-label-danger"><i class="icon-base bx bx-x-circle icon-md"></i></div>
                                <div><small class="text-body-secondary d-block">Épuisés</small><h5 class="mb-0">10</h5></div>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Revenus + Transactions -->
              <div class="row">
                <div class="col-12 col-xxl-8 mb-6 total-revenue">
                  <div class="card">
                    <div class="row row-bordered g-0">
                      <div class="col-lg-8">
                        <div class="card-header d-flex align-items-center justify-content-between">
                          <div class="card-title mb-0"><h5 class="m-0 me-2">Revenus totaux</h5></div>
                          <div class="dropdown">
                            <button class="btn p-0" type="button" id="totalRevenue" data-bs-toggle="dropdown">
                              <i class="icon-base bx bx-dots-vertical-rounded icon-lg text-body-secondary"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                              <a class="dropdown-item" href="javascript:void(0);">Tout sélectionner</a>
                              <a class="dropdown-item" href="javascript:void(0);">Actualiser</a>
                            </div>
                          </div>
                        </div>
                        <div id="totalRevenueChart" class="px-3"></div>
                      </div>
                      <div class="col-lg-4">
                        <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                          <div class="text-center mb-6">
                            <div class="btn-group">
                              <button type="button" class="btn btn-outline-primary">
                                <script>document.write(new Date().getFullYear()-1);</script>
                              </button>
                              <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                <span class="visually-hidden">Toggle</span>
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);">2022</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);">2023</a></li>
                              </ul>
                            </div>
                          </div>
                          <div id="growthChart"></div>
                          <div class="text-center fw-medium my-6">62% Croissance</div>
                          <div class="d-flex gap-11 justify-content-between">
                            <div class="d-flex">
                              <div class="avatar me-2">
                                <span class="avatar-initial rounded-2 bg-label-primary"><i class="icon-base bx bx-dollar icon-lg text-primary"></i></span>
                              </div>
                              <div class="d-flex flex-column">
                                <small><script>document.write(new Date().getFullYear()-1);</script></small>
                                <h6 class="mb-0">32,5k</h6>
                              </div>
                            </div>
                            <div class="d-flex">
                              <div class="avatar me-2">
                                <span class="avatar-initial rounded-2 bg-label-info"><i class="icon-base bx bx-wallet icon-lg text-info"></i></span>
                              </div>
                              <div class="d-flex flex-column">
                                <small><script>document.write(new Date().getFullYear()-2);</script></small>
                                <h6 class="mb-0">41,2k</h6>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-6">
                  <div class="card h-100">
                    <div class="card-header"><h5 class="card-title m-0">Transactions récentes</h5></div>
                    <div class="card-body pt-4">
                      <ul class="p-0 m-0">
                        <li class="d-flex align-items-center mb-5">
                          <div class="avatar flex-shrink-0 me-3"><img src="../assets/img/icons/unicons/paypal.png" alt="Paypal" class="rounded" /></div>
                          <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2"><small class="d-block">Paypal</small><h6 class="fw-normal mb-0">Transfert</h6></div>
                            <div class="d-flex align-items-center gap-2"><h6 class="fw-normal mb-0 text-success">+82.6</h6><span class="text-body-secondary">TND</span></div>
                          </div>
                        </li>
                        <li class="d-flex align-items-center mb-5">
                          <div class="avatar flex-shrink-0 me-3"><img src="../assets/img/icons/unicons/wallet.png" alt="Portefeuille" class="rounded" /></div>
                          <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2"><small class="d-block">Portefeuille</small><h6 class="fw-normal mb-0">Formation</h6></div>
                            <div class="d-flex align-items-center gap-2"><h6 class="fw-normal mb-0 text-success">+270.69</h6><span class="text-body-secondary">TND</span></div>
                          </div>
                        </li>
                        <li class="d-flex align-items-center">
                          <div class="avatar flex-shrink-0 me-3"><img src="../assets/img/icons/unicons/cc-primary.png" alt="Carte" class="rounded" /></div>
                          <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2"><small class="d-block">Carte bancaire</small><h6 class="fw-normal mb-0">Remboursement</h6></div>
                            <div class="d-flex align-items-center gap-2"><h6 class="fw-normal mb-0 text-danger">-92.45</h6><span class="text-body-secondary">TND</span></div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <!-- / Content -->

            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    © <script>document.write(new Date().getFullYear());</script> Plateforme de Formation
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
    <!-- / Layout wrapper -->


    <!-- ══════════════════════ OFFCANVAS PARAMÈTRES ══════════════════════ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="parametresOffcanvas" aria-labelledby="parametresLabel">
      <div class="offcanvas-header border-bottom px-4 py-3">
        <div>
          <h5 class="offcanvas-title mb-0 fw-semibold" id="parametresLabel">Modèle de Personnalisation</h5>
          <small class="text-body-secondary">Personnalisez et prévisualisez en temps réel</small>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" onclick="resetCustomizer()" title="Réinitialiser">
            <i class="icon-base bx bx-revision icon-md"></i>
          </button>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
        </div>
      </div>

      <div class="offcanvas-body px-4 py-4">

        <!-- Thématisation -->
        <div class="param-section-label">Thématisation</div>

        <p class="small fw-semibold mb-2 mt-1">Couleur primaire</p>
        <div class="d-flex gap-2 flex-wrap mb-4" id="colorSwatches">
          <div class="color-swatch active" style="background:#696cff;" data-hex="#696cff" data-rgb="105,108,255" onclick="setColor(this)" title="Violet"></div>
          <div class="color-swatch" style="background:#20c997;" data-hex="#20c997" data-rgb="32,201,151" onclick="setColor(this)" title="Vert"></div>
          <div class="color-swatch" style="background:#fd7e14;" data-hex="#fd7e14" data-rgb="253,126,20" onclick="setColor(this)" title="Orange"></div>
          <div class="color-swatch" style="background:#e83e8c;" data-hex="#e83e8c" data-rgb="232,62,140" onclick="setColor(this)" title="Rose"></div>
          <div class="color-swatch" style="background:#0dcaf0;" data-hex="#0dcaf0" data-rgb="13,202,240" onclick="setColor(this)" title="Cyan"></div>
          <div class="color-swatch" style="background:#f8f7fa;border-color:#dbdade;" title="Personnalisée" onclick="document.getElementById('customColorPicker').click()">
            <i class="bx bxs-color-fill" style="font-size:16px;color:#adb5bd;"></i>
            <input type="color" id="customColorPicker" style="position:absolute;opacity:0;width:0;height:0;" onchange="setCustomColor(this.value)">
          </div>
        </div>

        <p class="small fw-semibold mb-2">Thème</p>
        <div class="d-flex gap-2 mb-4">
          <div class="theme-btn active" id="themeLight" onclick="setTheme('light')">
            <i class="bx bx-sun text-warning"></i><small>Light</small>
          </div>
          <div class="theme-btn" id="themeDark" onclick="setTheme('dark')">
            <i class="bx bx-moon"></i><small>Dark</small>
          </div>
          <div class="theme-btn" id="themeSystem" onclick="setTheme('system')">
            <i class="bx bx-desktop"></i><small>System</small>
          </div>
        </div>

        <p class="small fw-semibold mb-2">Peaux</p>
        <div class="d-flex gap-3 mb-4">
          <div class="skin-box active" id="skinDefault" onclick="setSkin('default')">
            <div class="d-flex gap-1">
              <div class="sb-side"></div>
              <div class="flex-grow-1"><div class="sb-row" style="width:90%"></div><div class="sb-row" style="width:65%"></div><div class="sb-row" style="width:78%"></div></div>
            </div>
            <small class="d-block text-center mt-2 text-body-secondary" style="font-size:.7rem;">Default</small>
          </div>
          <div class="skin-box" id="skinBordered" onclick="setSkin('bordered')">
            <div class="d-flex gap-1" style="border:1.5px solid #ccc;border-radius:4px;padding:3px;">
              <div class="sb-side" style="border-right:1px solid #ccc;"></div>
              <div class="flex-grow-1"><div class="sb-row" style="width:90%;background:#c8c8c8;"></div><div class="sb-row" style="width:60%;background:#c8c8c8;"></div><div class="sb-row" style="width:75%;background:#c8c8c8;"></div></div>
            </div>
            <small class="d-block text-center mt-2 text-body-secondary" style="font-size:.7rem;">Bordered</small>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <span class="small fw-semibold">Demi-foncé</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="semiDarkToggle" onchange="toggleSemiDark(this)">
          </div>
        </div>

        <hr class="my-3">

        <!-- Disposition -->
        <div class="param-section-label">Disposition</div>

        <p class="small fw-semibold mb-2 mt-1">Menu (Navigation)</p>
        <div class="d-flex gap-2 mb-4">
          <div class="theme-btn active" id="navVertical" onclick="setNav('vertical')">
            <i class="bx bx-layout"></i><small>Vertical</small>
          </div>
          <div class="theme-btn" id="navHorizontal" onclick="setNav('horizontal')">
            <i class="bx bx-dock-top"></i><small>Horizontal</small>
          </div>
        </div>

        <p class="small fw-semibold mb-2">Largeur du contenu</p>
        <div class="d-flex gap-2 mb-4">
          <div class="theme-btn active" id="widthCompact" onclick="setWidth('compact')">
            <i class="bx bx-expand-alt"></i><small>Compact</small>
          </div>
          <div class="theme-btn" id="widthWide" onclick="setWidth('wide')">
            <i class="bx bx-fullscreen"></i><small>Large</small>
          </div>
        </div>

      </div>
    </div>
    <!-- / Offcanvas Paramètres -->


    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/navbar-extras.js"></script>
    <script src="../assets/js/dashboards-analytics.js"></script>

    <script>
    'use strict';

    /* ── Notifications ── */
    var notifCount = 5;

    function updateBadge() {
      var badge = document.getElementById('notif-count');
      var label = document.getElementById('notif-badge');
      if (notifCount > 0) {
        badge.style.display = '';
        badge.textContent = notifCount;
        label.textContent = notifCount + ' nouvelle' + (notifCount > 1 ? 's' : '');
      } else {
        badge.style.display = 'none';
        if (label) label.textContent = 'Aucune';
      }
    }

    function dismissNotif(btn, e) {
      e.preventDefault();
      e.stopPropagation();
      var item = btn.closest('.dropdown-notifications-item');
      if (item) {
        item.remove();
        notifCount = Math.max(0, notifCount - 1);
        updateBadge();
      }
    }

    function markAllRead() {
      document.querySelectorAll('.dropdown-notifications-item').forEach(function(el){ el.remove(); });
      notifCount = 0;
      updateBadge();
    }

    /* ── Customizer ── */
    function setColor(el) {
      document.querySelectorAll('#colorSwatches .color-swatch').forEach(function(s){ s.classList.remove('active'); });
      el.classList.add('active');
      var hex = el.dataset.hex;
      var rgb = el.dataset.rgb;
      if (hex && rgb) {
        document.documentElement.style.setProperty('--bs-primary', hex);
        document.documentElement.style.setProperty('--bs-primary-rgb', rgb);
        try { localStorage.setItem('app.primaryHex', hex); localStorage.setItem('app.primaryRgb', rgb); } catch(e){}
      }
    }

    function setCustomColor(hex) {
      var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
      document.documentElement.style.setProperty('--bs-primary', hex);
      document.documentElement.style.setProperty('--bs-primary-rgb', r+','+g+','+b);
      try { localStorage.setItem('app.primaryHex', hex); localStorage.setItem('app.primaryRgb', r+','+g+','+b); } catch(e){}
    }

    function setTheme(mode) {
      ['themeLight','themeDark','themeSystem'].forEach(function(id){ var el=document.getElementById(id); if(el) el.classList.remove('active'); });
      var themeMap = {light:'themeLight', dark:'themeDark', system:'themeSystem'};
      var activeEl = document.getElementById(themeMap[mode]);
      if (activeEl) activeEl.classList.add('active');
      if (mode === 'system') mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-bs-theme', mode);
      var icon = document.getElementById('app-theme-toggle-icon');
      if (icon) icon.className = 'icon-base bx ' + (mode==='dark'?'bx-sun':'bx-moon') + ' icon-md';
      try { localStorage.setItem('app.theme', mode); } catch(e){}
    }

    function setSkin(skin) {
      ['skinDefault','skinBordered'].forEach(function(id){ var el=document.getElementById(id); if(el) el.classList.remove('active'); });
      var target = document.getElementById(skin==='bordered'?'skinBordered':'skinDefault');
      if (target) target.classList.add('active');
    }

    function toggleSemiDark(el) {
      document.documentElement.classList.toggle('layout-menu-dark', el.checked);
    }

    function setNav(type) {
      ['navVertical','navHorizontal'].forEach(function(id){ var el=document.getElementById(id); if(el) el.classList.remove('active'); });
      var target = document.getElementById(type==='horizontal'?'navHorizontal':'navVertical');
      if (target) target.classList.add('active');
    }

    function setWidth(type) {
      ['widthCompact','widthWide'].forEach(function(id){ var el=document.getElementById(id); if(el) el.classList.remove('active'); });
      var target = document.getElementById(type==='wide'?'widthWide':'widthCompact');
      if (target) target.classList.add('active');
      document.documentElement.classList.toggle('layout-wide', type==='wide');
    }

    function resetCustomizer() {
      document.documentElement.style.removeProperty('--bs-primary');
      document.documentElement.style.removeProperty('--bs-primary-rgb');
      try { localStorage.removeItem('app.primaryHex'); localStorage.removeItem('app.primaryRgb'); } catch(e){}
      setTheme('light');
      setSkin('default');
      setNav('vertical');
      setWidth('compact');
      document.querySelectorAll('#colorSwatches .color-swatch').forEach(function(s,i){ s.classList.toggle('active',i===0); });
      var toggle = document.getElementById('semiDarkToggle');
      if (toggle) { toggle.checked = false; document.documentElement.classList.remove('layout-menu-dark'); }
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Navbar theme toggle
      var btn = document.getElementById('app-theme-toggle');
      if (btn) {
        btn.addEventListener('click', function() {
          var cur = document.documentElement.getAttribute('data-bs-theme') || 'light';
          setTheme(cur === 'dark' ? 'light' : 'dark');
        });
      }
      // Restore saved primary color
      try {
        var hex = localStorage.getItem('app.primaryHex');
        var rgb = localStorage.getItem('app.primaryRgb');
        if (hex && rgb) {
          document.documentElement.style.setProperty('--bs-primary', hex);
          document.documentElement.style.setProperty('--bs-primary-rgb', rgb);
          document.querySelectorAll('#colorSwatches .color-swatch[data-hex]').forEach(function(s){
            s.classList.toggle('active', s.getAttribute('data-hex') === hex);
          });
        }
      } catch(e){}
    });
    </script>

  </body>
</html>
