<?php
// Vue : liste des entretiens
// Variables disponibles : $entretiens, $stats, $typesEntretien, $statuts
?>
<!doctype html>
<html
  lang="fr"
  class="layout-menu-fixed layout-compact"
  data-assets-path="./assets/"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Entretiens | Takwini</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="./assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="./assets/vendor/css/core.css" />
    <link rel="stylesheet" href="./assets/css/demo.css" />
    <link rel="stylesheet" href="./assets/css/dark-mode.css" />
    <link rel="stylesheet" href="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="./assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="./assets/vendor/js/helpers.js"></script>
    <script src="./assets/js/config.js"></script>
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="./html/index.html" class="app-brand-link">
              <span class="app-brand-logo demo"><img src="./assets/img/favicon/tak.png" alt="Takwinibot" style="width:56px;height:56px;object-fit:contain;"></span>
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
                <li class="menu-item">
                  <a href="./html/index.html" class="menu-link">
                    <div class="text-truncate">Accueil</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="./html/gestion-formations.html" class="menu-link">
                        <div class="text-truncate">Vue d&apos;ensemble</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="./html/gestion-inscriptions.html" class="menu-link">
                        <div class="text-truncate">Inscriptions</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="./html/gestion-certificats.html" class="menu-link">
                        <div class="text-truncate">Certificats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Offres</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="./html/gestion-offres.html" class="menu-link">
                        <div class="text-truncate">Liste des offres</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="./html/gestion-contrats.html" class="menu-link">
                        <div class="text-truncate">Contrats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="./html/gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>
                <li class="menu-item active">
                  <a href="./gestion-entretiens.php" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="./html/gestion-produits.html" class="menu-link">
                    <div class="text-truncate">Produits</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="./html/gestion-utilisateurs.html" class="menu-link">
                        <div class="text-truncate">Liste des utilisateurs</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="./html/pages-account-settings-account.html" class="menu-link">
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
              <a href="./html/email-boite.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div class="text-truncate">Email</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="./html/app-chat-local.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chat"></i>
                <div class="text-truncate">Discuter</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="./html/app-calendrier-local.html" class="menu-link">
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
                  <a href="./html/auth-login-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Connexion</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="./html/auth-register-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Inscription</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="./html/auth-forgot-password-basic.html" class="menu-link" target="_blank">
                    <div class="text-truncate">Mot de passe oublié</div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout page -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
              </a>
            </div>
            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
              <div class="navbar-nav align-items-center me-auto">
                <div class="nav-item d-flex align-items-center">
                  <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                  <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Search..." aria-label="Search..." />
                </div>
              </div>
              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème clair ou sombre">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="./assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="./html/pages-account-settings-account.html">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="./assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0">Admin</h6>
                            <small class="text-body-secondary">Takwini</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                      <a class="dropdown-item" href="./html/auth-login-basic.html">
                        <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-2">Entretiens</h4>
              <p class="text-muted mb-4">Gestion des entretiens candidats.</p>

              <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible mb-4" role="alert">
                  <?php
                    $msgs = ['created' => 'Entretien créé avec succès.', 'updated' => 'Entretien mis à jour.', 'deleted' => 'Entretien supprimé.'];
                    echo htmlspecialchars($msgs[$_GET['success']] ?? 'Opération réussie.');
                  ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                  Une erreur est survenue. Veuillez réessayer.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <!-- Cards stats -->
              <div class="row g-6 mb-6">
                <div class="col-sm-6 col-xl-3">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                          <span>Entretiens</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2"><?= htmlspecialchars($stats['total']) ?></h4>
                          </div>
                          <p class="mb-0">Total entretiens</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-calendar bx-sm"></i>
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
                          <span>À venir</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2"><?= htmlspecialchars($stats['a_venir']) ?></h4>
                          </div>
                          <p class="mb-0">Planifiés à venir</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-time-five bx-sm"></i>
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
                          <span>Terminés</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2"><?= htmlspecialchars($stats['termines']) ?></h4>
                          </div>
                          <p class="mb-0">Entretiens terminés</p>
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
                          <span>Annulés</span>
                          <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2"><?= htmlspecialchars($stats['annules']) ?></h4>
                          </div>
                          <p class="mb-0">Entretiens annulés</p>
                        </div>
                        <div class="avatar">
                          <span class="avatar-initial rounded bg-label-danger">
                            <i class="bx bx-x-circle bx-sm"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /Cards stats -->

              <div class="card" id="types">
                <div class="card-header border-bottom">
                  <h5 class="card-title">Filtres de recherche</h5>
                  <form method="GET" action="./gestion-entretiens.php">
                    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                      <div class="col-md-4">
                        <select name="type_entretien_id" class="form-select text-capitalize" onchange="this.form.submit()">
                          <option value="">Sélectionner Type</option>
                          <?php foreach ($typesEntretien as $type): ?>
                            <option value="<?= htmlspecialchars($type['id_type_entretien']) ?>"
                              <?= (isset($_GET['type_entretien_id']) && $_GET['type_entretien_id'] == $type['id_type_entretien']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($type['libelle']) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select name="statut" class="form-select text-capitalize" onchange="this.form.submit()">
                          <option value="">Sélectionner Statut</option>
                          <?php foreach ($statuts as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"
                              <?= (isset($_GET['statut']) && $_GET['statut'] === $s) ? 'selected' : '' ?>>
                              <?= htmlspecialchars(ucfirst($s)) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4 d-flex align-items-center">
                        <?php if (!empty($_GET['type_entretien_id']) || !empty($_GET['statut'])): ?>
                          <a href="./gestion-entretiens.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-x me-1"></i>Réinitialiser
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="card-datatable table-responsive">
                  <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="row mx-2 pt-3 pb-3">
                      <div class="col-md-10 offset-md-2">
                        <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
                          <div class="dt-buttons btn-group flex-wrap">
                            <a href="./gestion-entretiens.php?action=create" class="btn btn-primary">
                              <span><i class="bx bx-plus me-0 me-sm-1 bx-sm"></i><span class="d-none d-sm-inline-block">Ajouter Entretien</span></span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>

                    <table class="table border-top dataTable">
                      <thead>
                        <tr>
                          <th><input type="checkbox" class="form-check-input"></th>
                          <th>CANDIDAT</th>
                          <th>POSTE</th>
                          <th>TYPE</th>
                          <th>DURÉE</th>
                          <th>DATE</th>
                          <th>STATUT</th>
                          <th>ACTIONS</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($entretiens)): ?>
                          <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucun entretien trouvé.</td>
                          </tr>
                        <?php else: ?>
                          <?php foreach ($entretiens as $e): ?>
                            <?php
                              $parts = explode(' ', trim($e['nom_candidat']));
                              $initiales = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                              $badgeClass = Entretien::getStatutBadgeClass($e['statut']);
                              $dateFormatee = Entretien::formatDateHeure($e['date_entretien'], $e['heure_entretien']);
                              $duree = !empty($e['duree_prevue']) ? htmlspecialchars($e['duree_prevue']) . ' min' : '—';
                            ?>
                            <tr>
                              <td><input type="checkbox" class="form-check-input"></td>
                              <td>
                                <div class="d-flex justify-content-start align-items-center user-name">
                                  <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                      <span class="avatar-initial rounded-circle bg-label-primary"><?= htmlspecialchars($initiales) ?></span>
                                    </div>
                                  </div>
                                  <div class="d-flex flex-column">
                                    <span class="fw-medium text-heading"><?= htmlspecialchars($e['nom_candidat']) ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($e['email_candidat']) ?></small>
                                  </div>
                                </div>
                              </td>
                              <td><span class="text-heading"><?= htmlspecialchars($e['poste_cible'] ?? '—') ?></span></td>
                              <td>
                                <span class="text-truncate d-flex align-items-center">
                                  <span class="badge badge-center rounded bg-label-primary w-px-30 h-px-30 me-2">
                                    <i class="bx bx-briefcase bx-xs"></i>
                                  </span>
                                  <?= htmlspecialchars($e['type_libelle'] ?? '—') ?>
                                </span>
                              </td>
                              <td><?= $duree ?></td>
                              <td><?= htmlspecialchars($dateFormatee) ?></td>
                              <td>
                                <span class="badge <?= $badgeClass ?> text-capitalize">
                                  <?= htmlspecialchars($e['statut']) ?>
                                </span>
                              </td>
                              <td>
                                <div class="d-flex align-items-center">
                                  <a href="./gestion-entretiens.php?action=show&id=<?= (int)$e['id_entretien'] ?>" class="text-body" title="Voir">
                                    <i class="bx bx-show bx-sm me-2"></i>
                                  </a>
                                  <a href="./gestion-entretiens.php?action=edit&id=<?= (int)$e['id_entretien'] ?>" class="text-body" title="Modifier">
                                    <i class="bx bx-edit bx-sm me-2"></i>
                                  </a>
                                  <form method="POST" action="./gestion-entretiens.php?action=delete&id=<?= (int)$e['id_entretien'] ?>" style="display:inline;"
                                        onsubmit="return confirm('Confirmer la suppression de cet entretien ?');">
                                    <button type="submit" class="btn btn-link text-body p-0" title="Supprimer">
                                      <i class="bx bx-trash bx-sm"></i>
                                    </button>
                                  </form>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>

                    <div class="row mx-2 mt-3 mb-3">
                      <div class="col-sm-12 col-md-6 d-flex align-items-center">
                        <div class="dataTables_info text-muted small">
                          <?= count($entretiens) ?> entretien<?= count($entretiens) > 1 ? 's' : '' ?> affiché<?= count($entretiens) > 1 ? 's' : '' ?>
                        </div>
                      </div>
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
                    &copy; <?= date('Y') ?>, Takwini &mdash; Plateforme inclusive
                  </div>
                </div>
              </div>
            </footer>

            <div class="content-backdrop fade"></div>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script src="./assets/vendor/libs/jquery/jquery.js"></script>
    <script src="./assets/vendor/libs/popper/popper.js"></script>
    <script src="./assets/vendor/js/bootstrap.js"></script>
    <script src="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="./assets/vendor/js/menu.js"></script>
    <script src="./assets/js/main.js"></script>
    <script src="./assets/js/navbar-extras.js"></script>
  </body>
</html>
