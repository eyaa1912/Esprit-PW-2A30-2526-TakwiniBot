<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../controller/UtilisateurController.php';

// Protection admin → redirige vers login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../../view/frontoffice/login.php');
    exit;
}

$controller = new UtilisateurController();
$message    = '';
$error      = '';
$editUser   = null;

// ── SUPPRESSION ───────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $r = $controller->deleteUser((int) $_GET['delete']);
    $message = $r['success'] ? $r['message'] : '';
    $error   = $r['success'] ? '' : $r['message'];
}

// ── CHARGEMENT POUR MODIFICATION ─────────────────────────────────────────────
if (isset($_GET['edit'])) {
    $editUser = $controller->getById((int) $_GET['edit']);
}

// ── SAUVEGARDE MODIFICATION ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id       = (int) $_POST['id'];
    $nom      = trim($_POST['nom']   ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';
    if (empty($nom) || empty($email) || empty($password)) {
        $error    = 'Tous les champs sont obligatoires.';
        $editUser = $controller->getById($id);
    } else {
        $r = $controller->updateUser($id, $nom, $email, $password);
        $message = $r['success'] ? $r['message'] : '';
        $error   = $r['success'] ? '' : $r['message'];
    }
}

// ── AJOUT UTILISATEUR ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $nom      = trim($_POST['nom']   ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';
    $role     = $_POST['role']       ?? 'candidat';
    if (empty($nom) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $r = $controller->register($nom, '', $email, $password);
        $message = $r['success'] ? $r['message'] : '';
        $error   = $r['success'] ? '' : $r['message'];
    }
}

$users    = $controller->getAll();
$total    = count($users);
$actifs   = count(array_filter($users, fn($u) => $u['statut'] === 'actif'));
$inactifs = $total - $actifs;
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
  <title>Utilisateurs | Takwini</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
  <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
  <link rel="stylesheet" href="../assets/css/demo.css"/>
  <link rel="stylesheet" href="../assets/css/dark-mode.css"/>
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
  <script>try{var t=localStorage.getItem("app.theme");if(t==="dark")document.documentElement.setAttribute("data-bs-theme","dark");}catch(e){}</script>
    <script src="../assets/vendor/js/helpers.js"></script>
  <script src="../assets/js/config.js"></script>

    <!-- Custom Dropdown Styles -->
    <link rel="stylesheet" href="../assets/css/custom-dropdown.css" />
    <link rel="stylesheet" href="../assets/css/ripple-effect.css" />
    <link rel="stylesheet" href="../assets/css/logout-green.css" />

  </head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- ── MENU ──────────────────────────────────────────────────────────── -->
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
                <li class="menu-item active">
                  <a href="gestion-utilisateurs.php" class="menu-link"><div class="text-truncate">Liste des utilisateurs</div></a>
                </li>
                <li class="menu-item">
                  <a href="gestion-recruteurs.php" class="menu-link"><div class="text-truncate">Recruteurs</div></a>
                </li>
                <li class="menu-item">
                  <a href="pages-account-settings-account.php" class="menu-link"><div class="text-truncate">Profil</div></a>
                </li>
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
    <!-- / Menu -->

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
              <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Search..." aria-label="Search..."/>
            </div>
          </div>
          <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item me-2 me-xl-1">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
              </a>
            </li>
            <?php include 'notifications.php'; ?>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <?php 
                $__av = $_SESSION['user']['avatar'] ?? '';
                if (!empty($__av)) {
                    // avatar stocké comme "uploads/avatars/xxx.png" ou "assets/img/avatars/xxx.png"
                    if (strpos($__av, 'assets/img/avatars/') !== false) {
                        $__navAvatar = '../' . $__av;
                    } else {
                        $__navAvatar = '../../../../../view/frontoffice/' . $__av;
                    }
                } else {
                    $__navAvatar = '../assets/img/avatars/1.png';
                }
                ?>
                <div class="avatar avatar-online">
                  <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item logout-btn" href="#">
                    <div class="d-flex">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                          <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($_SESSION['user']['nom']) ?></h6>
                        <small class="text-body-secondary">Admin</small>
                      </div>
                    </div>
                  </a>
                </li>
                <li><div class="dropdown-divider my-1"></div></li>
                <li>
                  <a class="dropdown-item" href="mon-profil.php">
                    <i class="icon-base bx bx-user icon-md me-3"></i><span>Mon profil</span>
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

      <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

          <h4 class="fw-bold py-3 mb-2">Utilisateurs</h4>
          <p class="text-muted mb-4">Tableaux avec actions Modifier / Supprimer.</p>

          <?php if ($message): ?>
          <div class="alert alert-success alert-dismissible mb-4" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>
          <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <!-- Statistiques réelles -->
          <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Utilisateurs</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $total ?></h4>
                      </div>
                      <p class="mb-0">Total utilisateurs</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user bx-sm"></i></span></div>
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
                        <h4 class="mb-0 me-2 text-success"><?= $actifs ?></h4>
                      </div>
                      <p class="mb-0">Connectés</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Inactifs</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2 text-danger"><?= $inactifs ?></h4>
                      </div>
                      <p class="mb-0">Déconnectés</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-user-x bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Admins</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></h4>
                      </div>
                      <p class="mb-0">Administrateurs</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-crown bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulaire modification (inline si ?edit=X) -->
          <?php if ($editUser): ?>
          <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h5 class="mb-0"><i class="bx bx-edit me-2"></i>Modifier l'utilisateur #<?= $editUser['id'] ?></h5>
              <a href="gestion-utilisateurs.php" class="btn btn-sm btn-outline-secondary">Annuler</a>
            </div>
            <div class="card-body">
              <form method="POST" action="gestion-utilisateurs.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($editUser['nom'] ?? '') ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe" required>
                  </div>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button>
                </div>
              </form>
            </div>
          </div>
          <?php endif; ?>

          <!-- Tableau -->
          <div class="card">
            <div class="card-header border-bottom">
              <h5 class="card-title">Filtres de recherche</h5>
              <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-4">
                  <select class="form-select text-capitalize" id="filterRole">
                    <option value="">Sélectionner Rôle</option>
                    <option value="admin">Admin</option>
                    <option value="candidat">Candidat</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select class="form-select text-capitalize" id="filterStatut">
                    <option value="">Sélectionner Statut</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                </div>
              </div>
            </div>
            <div class="card-datatable table-responsive">
              <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                <div class="row mx-2 pt-3 pb-3">
                  <div class="col-md-10 d-flex justify-content-end">
                    <div class="dt-buttons btn-group flex-wrap">
                      <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addUtilisateurModal">
                        <span><i class="bx bx-plus me-0 me-sm-1 bx-sm"></i><span class="d-none d-sm-inline-block">Ajouter Utilisateur</span></span>
                      </button>
                    </div>
                  </div>
                </div>

                <table class="table border-top dataTable" id="usersTable">
                  <thead>
                    <tr>
                      <th><input type="checkbox" class="form-check-input" id="checkAll"></th>
                      <th>UTILISATEUR</th>
                      <th>RÔLE</th>
                      <th>STATUT</th>
                      <th>ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($users as $u):
                    $initiale = strtoupper(mb_substr($u['nom'] ?? 'U', 0, 1));
                    $avatarColors = ['primary','success','warning','info','danger','secondary'];
                    $color = $avatarColors[$u['id'] % count($avatarColors)];
                  ?>
                    <tr data-role="<?= $u['role'] ?>" data-statut="<?= $u['statut'] ?>">
                      <td><input type="checkbox" class="form-check-input row-check"></td>
                      <td>
                        <div class="d-flex justify-content-start align-items-center user-name">
                          <div class="avatar-wrapper">
                            <div class="avatar avatar-sm me-3">
                              <span class="avatar-initial rounded-circle bg-label-<?= $color ?>"><?= $initiale ?></span>
                            </div>
                          </div>
                          <div class="d-flex flex-column">
                            <span class="fw-medium text-heading"><?= htmlspecialchars($u['nom'] ?? '-') ?></span>
                            <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="text-truncate d-flex align-items-center">
                          <span class="badge badge-center rounded bg-label-<?= $u['role'] === 'admin' ? 'primary' : 'success' ?> w-px-30 h-px-30 me-2">
                            <i class="bx <?= $u['role'] === 'admin' ? 'bx-crown' : 'bx-user' ?> bx-xs"></i>
                          </span>
                          <?= ucfirst($u['role']) ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-label-<?= $u['statut'] === 'actif' ? 'success' : 'secondary' ?> text-capitalize">
                          <?= $u['statut'] ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <a href="gestion-utilisateurs.php?delete=<?= $u['id'] ?>"
                             class="text-body"
                             title="Supprimer"
                             onclick="return confirm('Supprimer <?= htmlspecialchars(addslashes($u['nom'] ?? '')) ?> ?')">
                            <i class="bx bx-trash bx-sm me-2"></i>
                          </a>
                          <a href="gestion-utilisateurs.php?edit=<?= $u['id'] ?>" class="text-body" title="Modifier">
                            <i class="bx bx-edit bx-sm me-2"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>

                <div class="row mx-2 mt-3 mb-3">
                  <div class="col-sm-12 col-md-6 d-flex align-items-center">
                    <div class="dataTables_info text-muted small">
                      Total : <?= $total ?> utilisateur(s)
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Ajouter Utilisateur -->
          <div class="modal fade" id="addUtilisateurModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Ajouter un utilisateur</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="gestion-utilisateurs.php">
                  <input type="hidden" name="action" value="add">
                  <div class="modal-body">
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="nom" class="form-control" placeholder="Nom et prénom" required/>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@exemple.com" required/>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" required/>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-select">
                          <option value="candidat">Candidat</option>
                          <option value="admin">Admin</option>
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

        </div>
        <!-- Footer -->
        <footer class="content-footer footer bg-footer-theme">
          <div class="container-xxl">
            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">© <script>document.write(new Date().getFullYear())</script>, Takwini</div>
            </div>
          </div>
        </footer>
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
<script src="../assets/js/navbar-extras.js"></script>
    <script src="../assets/js/i18n.js"></script>

<script>
// Recherche + filtres en temps réel
function filterTable() {
  const search  = document.getElementById('searchInput').value.toLowerCase();
  const role    = document.getElementById('filterRole').value.toLowerCase();
  const statut  = document.getElementById('filterStatut').value.toLowerCase();

  document.querySelectorAll('#usersTable tbody tr').forEach(row => {
    const text   = row.textContent.toLowerCase();
    const rRole  = row.dataset.role;
    const rStat  = row.dataset.statut;
    const matchS = text.includes(search);
    const matchR = !role   || rRole  === role;
    const matchT = !statut || rStat  === statut;
    row.style.display = (matchS && matchR && matchT) ? '' : 'none';
  });
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('filterRole').addEventListener('change', filterTable);
document.getElementById('filterStatut').addEventListener('change', filterTable);

// Checkbox tout sélectionner
document.getElementById('checkAll').addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
</script>

    <!-- Custom Dropdown Behavior -->
    <script src="../assets/js/custom-dropdown.js"></script>


  </body>
</html>







