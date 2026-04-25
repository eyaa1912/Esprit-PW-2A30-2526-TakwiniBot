<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/PostulerController.php';

$ctrl = new PostulerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_statut') {
        $ctrl->updateStatut((int)($_POST['id'] ?? 0), $_POST['statut'] ?? 'en_attente');
        header('Location: gestion-candidatures.php');
        exit;
    }

    if ($action === 'delete') {
        $ctrl->deleteCandidature((int)($_POST['id'] ?? 0));
        header('Location: gestion-candidatures.php');
        exit;
    }
}

$candidatures = $ctrl->getAllCandidatures();
$counts       = $ctrl->countByStatut();

function badge_statut_c(string $s): string {
    $map    = ['en_attente' => 'warning', 'acceptee' => 'success', 'refusee' => 'danger'];
    $labels = ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée'];
    $cls    = $map[$s] ?? 'secondary';
    $label  = $labels[$s] ?? $s;
    return '<span class="badge bg-label-' . $cls . '">' . htmlspecialchars($label) . '</span>';
}
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Postuler | Admin</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="../assets/vendor/css/core.css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />
  <link rel="stylesheet" href="../assets/css/dark-mode.css" />
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <script src="../assets/vendor/js/helpers.js"></script>
  <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- MENU -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
          <span class="app-brand-logo demo"><img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:56px;height:56px;object-fit:contain;"></span>
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
            <li class="menu-item"><a href="index.php" class="menu-link"><div class="text-truncate">Accueil</div></a></li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Formations</div></a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="gestion-formations.html" class="menu-link"><div class="text-truncate">Vue d'ensemble</div></a></li>
                <li class="menu-item"><a href="gestion-inscriptions.html" class="menu-link"><div class="text-truncate">Inscriptions</div></a></li>
                <li class="menu-item"><a href="gestion-certificats.html" class="menu-link"><div class="text-truncate">Certificats</div></a></li>
              </ul>
            </li>
            <li class="menu-item open">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Offres</div></a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="gestion-offres.php" class="menu-link"><div class="text-truncate">Liste des offres</div></a></li>
                <li class="menu-item"><a href="gestion-contrats.php" class="menu-link"><div class="text-truncate">Contrats</div></a></li>
                <li class="menu-item active"><a href="gestion-candidatures.php" class="menu-link"><div class="text-truncate">Postuler</div></a></li>
              </ul>
            </li>
            <li class="menu-item"><a href="gestion-reclamations.html" class="menu-link"><div class="text-truncate">Réclamations</div></a></li>
            <li class="menu-item"><a href="gestion-entretiens.html" class="menu-link"><div class="text-truncate">Entretiens</div></a></li>
            <li class="menu-item"><a href="gestion-produits.html" class="menu-link"><div class="text-truncate">Produits</div></a></li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle"><div class="text-truncate">Utilisateurs</div></a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="gestion-utilisateurs.html" class="menu-link"><div class="text-truncate">Liste des utilisateurs</div></a></li>
                <li class="menu-item"><a href="pages-account-settings-account.html" class="menu-link"><div class="text-truncate">Profil</div></a></li>
              </ul>
            </li>
          </ul>
        </li>
      </ul>
    </aside>

    <div class="layout-page">
      <!-- NAVBAR -->
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
              <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Rechercher..." />
            </div>
          </div>
          <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item me-2 me-xl-1">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
              </a>
            </li>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                  <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="auth-login-basic.html"><i class="icon-base bx bx-power-off icon-md me-3"></i>Déconnexion</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>

      <!-- CONTENT -->
      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

          <h4 class="fw-bold py-3 mb-2">Gestion des candidatures</h4>
          <p class="text-muted mb-4">Liste des candidatures déposées via le formulaire "Postuler".</p>

          <!-- Stat cards -->
          <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Total</span>
                      <div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= (int)$counts['total'] ?></h4></div>
                      <p class="mb-0">Candidatures</p>
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
                      <span>En attente</span>
                      <div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= (int)$counts['en_attente'] ?></h4></div>
                      <p class="mb-0">À traiter</p>
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
                      <span>Acceptées</span>
                      <div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= (int)$counts['acceptee'] ?></h4></div>
                      <p class="mb-0">Candidatures acceptées</p>
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
                      <span>Refusées</span>
                      <div class="d-flex align-items-end mt-2"><h4 class="mb-0 me-2"><?= (int)$counts['refusee'] ?></h4></div>
                      <p class="mb-0">Candidatures refusées</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filtres -->
          <div class="card mb-4">
            <div class="card-body py-3">
              <div class="row g-3 align-items-center">
                <div class="col-md-3">
                  <select class="form-select" id="filter-statut">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="acceptee">Acceptée</option>
                    <option value="refusee">Refusée</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <input type="text" class="form-control" id="filter-search" placeholder="Rechercher par nom, email, offre...">
                </div>
              </div>
            </div>
          </div>

          <!-- Tableau -->
          <div class="card">
            <div class="card-datatable table-responsive">
              <table class="table table-hover" id="table-candidatures">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Offre</th>
                    <th>Type</th>
                    <th>CV</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($candidatures)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-5">Aucune candidature pour le moment.</td></tr>
                  <?php else: ?>
                    <?php foreach ($candidatures as $c): ?>
                    <tr data-statut="<?= htmlspecialchars($c['statut']) ?>">
                      <td><?= (int)$c['id'] ?></td>
                      <td><?= htmlspecialchars($c['nom']) ?></td>
                      <td><?= htmlspecialchars($c['prenom']) ?></td>
                      <td><?= htmlspecialchars($c['email']) ?></td>
                      <td><?= htmlspecialchars($c['offre_titre'] ?? '—') ?></td>
                      <td><span class="badge bg-label-primary"><?= htmlspecialchars($c['offre_type'] ?? '—') ?></span></td>
                      <td>
                        <?php if (!empty($c['cv_path'])): ?>
                          <a href="../../../<?= htmlspecialchars($c['cv_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-download me-1"></i>Voir CV
                          </a>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['datePostulation']))) ?></td>
                      <td><?= badge_statut_c($c['statut']) ?></td>
                      <td>
                        <div class="dropdown">
                          <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                              <form method="POST">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="statut" value="acceptee">
                                <button type="submit" class="dropdown-item text-success">
                                  <i class="bx bx-check me-2"></i>Accepter
                                </button>
                              </form>
                            </li>
                            <li>
                              <form method="POST">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="statut" value="refusee">
                                <button type="submit" class="dropdown-item text-warning">
                                  <i class="bx bx-x me-2"></i>Refuser
                                </button>
                              </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" onsubmit="return confirm('Supprimer cette candidature ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                  <i class="bx bx-trash me-2"></i>Supprimer
                                </button>
                              </form>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
        <div class="content-backdrop fade"></div>
      </div>
    </div>
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
</div>

<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<script src="../assets/js/main.js"></script>

<script>
document.getElementById('filter-statut').addEventListener('change', filterTable);
document.getElementById('filter-search').addEventListener('input', filterTable);

function filterTable() {
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var search = document.getElementById('filter-search').value.toLowerCase();
    document.querySelectorAll('#table-candidatures tbody tr[data-statut]').forEach(function(row) {
        var matchS = !statut || row.dataset.statut === statut;
        var matchT = !search || row.textContent.toLowerCase().includes(search);
        row.style.display = (matchS && matchT) ? '' : 'none';
    });
}
</script>
</body>
</html>
