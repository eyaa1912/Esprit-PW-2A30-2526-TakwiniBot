<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

// Protection admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../view/frontoffice/login.php');
    exit;
}

$controller = new UtilisateurController();
$message    = '';
$error      = '';
$editUser   = null;

// ── SUPPRESSION ──────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $result  = $controller->deleteUser((int) $_GET['delete']);
    $message = $result['success'] ? $result['message'] : '';
    $error   = $result['success'] ? '' : $result['message'];
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
        $result  = $controller->updateUser($id, $nom, $email, $password);
        $message = $result['success'] ? $result['message'] : '';
        $error   = $result['success'] ? '' : $result['message'];
    }
}

$users  = $controller->getAll();
$total  = count($users);
$actifs = count(array_filter($users, fn($u) => $u['statut'] === 'actif'));
$inactifs = $total - $actifs;
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../backoffice/sneat-plateforme-finale/sneat-final/assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
  <title>Gestion Utilisateurs | Takwini</title>
  <link rel="icon" type="image/x-icon" href="sneat-plateforme-finale/sneat-final/assets/img/favicon/tak.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="sneat-plateforme-finale/sneat-final/assets/vendor/fonts/iconify-icons.css"/>
  <link rel="stylesheet" href="sneat-plateforme-finale/sneat-final/assets/vendor/css/core.css"/>
  <link rel="stylesheet" href="sneat-plateforme-finale/sneat-final/assets/css/demo.css"/>
  <link rel="stylesheet" href="sneat-plateforme-finale/sneat-final/assets/css/dark-mode.css"/>
  <link rel="stylesheet" href="sneat-plateforme-finale/sneat-final/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
  <script src="sneat-plateforme-finale/sneat-final/assets/vendor/js/helpers.js"></script>
  <script src="sneat-plateforme-finale/sneat-final/assets/js/config.js"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- ── MENU LATERAL ──────────────────────────────────────────────────── -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="sneat-plateforme-finale/sneat-final/html/index.html" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="sneat-plateforme-finale/sneat-final/assets/img/favicon/tak.png" alt="Takwini" style="width:46px;height:46px;object-fit:contain;">
          </span>
          <span class="app-brand-text demo menu-text fw-bold ms-2">Takwini</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
          <i class="bx bx-chevron-left d-block align-middle"></i>
        </a>
      </div>
      <div class="menu-divider mt-0"></div>
      <div class="menu-inner-shadow"></div>
      <ul class="menu-inner py-1">
        <li class="menu-item">
          <a href="sneat-plateforme-finale/sneat-final/html/index.html" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-smile"></i>
            <div class="text-truncate">Tableau de bord</div>
          </a>
        </li>
        <li class="menu-item active open">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div class="text-truncate">Utilisateurs</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item active">
              <a href="utilisateurs.php" class="menu-link">
                <div class="text-truncate">Liste des utilisateurs</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="sneat-plateforme-finale/sneat-final/html/gestion-formations.html" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-open"></i>
            <div class="text-truncate">Formations</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="sneat-plateforme-finale/sneat-final/html/gestion-offres.html" class="menu-link">
            <i class="menu-icon tf-icons bx bx-briefcase"></i>
            <div class="text-truncate">Offres</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="sneat-plateforme-finale/sneat-final/html/gestion-reclamations.html" class="menu-link">
            <i class="menu-icon tf-icons bx bx-error-circle"></i>
            <div class="text-truncate">Réclamations</div>
          </a>
        </li>
        <li class="menu-divider my-1"></li>
        <li class="menu-item">
          <a href="../../Model/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-power-off"></i>
            <div class="text-truncate">Déconnexion</div>
          </a>
        </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- ── CONTENU PRINCIPAL ─────────────────────────────────────────────── -->
    <div class="layout-page">

      <!-- Navbar -->
      <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
          </a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
          <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item me-2">
              <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
              </a>
            </li>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                  <img src="sneat-plateforme-finale/sneat-final/assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle"/>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="#">
                    <div class="d-flex">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                          <img src="sneat-plateforme-finale/sneat-final/assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle"/>
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
                  <a class="dropdown-item" href="../../Model/logout.php">
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
        <div class="container-xxl flex-grow-1 container-p-y">

          <h4 class="fw-bold py-3 mb-2">
            <span class="text-muted fw-light">Admin /</span> Gestion des Utilisateurs
          </h4>

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

          <!-- Statistiques -->
          <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <span class="text-muted">Total utilisateurs</span>
                      <h4 class="mb-0 mt-1"><?= $total ?></h4>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <span class="text-muted">Actifs</span>
                      <h4 class="mb-0 mt-1 text-success"><?= $actifs ?></h4>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div>
                      <span class="text-muted">Inactifs</span>
                      <h4 class="mb-0 mt-1 text-danger"><?= $inactifs ?></h4>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-user-x bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulaire de modification (affiché si ?edit=X) -->
          <?php if ($editUser): ?>
          <div class="card mb-6">
            <div class="card-header">
              <h5 class="mb-0"><i class="bx bx-edit me-2"></i>Modifier l'utilisateur #<?= $editUser['id'] ?></h5>
            </div>
            <div class="card-body">
              <form method="POST" action="utilisateurs.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($editUser['nom']) ?>" required>
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
                <div class="mt-3 d-flex gap-2">
                  <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Enregistrer</button>
                  <a href="utilisateurs.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
              </form>
            </div>
          </div>
          <?php endif; ?>

          <!-- Tableau des utilisateurs -->
          <div class="card">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
              <h5 class="card-title mb-0">Liste des utilisateurs</h5>
              <input type="text" id="searchInput" class="form-control w-auto" placeholder="Rechercher...">
            </div>
            <div class="table-responsive">
              <table class="table table-hover" id="usersTable">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm">
                          <span class="avatar-initial rounded-circle bg-label-<?= $u['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= strtoupper(mb_substr($u['nom'] ?? 'U', 0, 1)) ?>
                          </span>
                        </div>
                        <span><?= htmlspecialchars($u['nom'] ?? '-') ?></span>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                      <span class="badge bg-label-<?= $u['role'] === 'admin' ? 'primary' : 'info' ?>">
                        <?= htmlspecialchars($u['role']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-label-<?= $u['statut'] === 'actif' ? 'success' : 'danger' ?>">
                        <?= htmlspecialchars($u['statut']) ?>
                      </span>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <a href="utilisateurs.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                          <i class="bx bx-edit"></i>
                        </a>
                        <a href="utilisateurs.php?delete=<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           title="Supprimer"
                           onclick="return confirm('Supprimer cet utilisateur ?')">
                          <i class="bx bx-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <!-- / Tableau -->

        </div>
        <!-- Content -->
        <div class="content-backdrop fade"></div>
      </div>
      <!-- / Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
</div>

<script src="sneat-plateforme-finale/sneat-final/assets/vendor/libs/jquery/jquery.js"></script>
<script src="sneat-plateforme-finale/sneat-final/assets/vendor/libs/popper/popper.js"></script>
<script src="sneat-plateforme-finale/sneat-final/assets/vendor/js/bootstrap.js"></script>
<script src="sneat-plateforme-finale/sneat-final/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="sneat-plateforme-finale/sneat-final/assets/vendor/js/menu.js"></script>
<script src="sneat-plateforme-finale/sneat-final/assets/js/main.js"></script>

<script>
  // Recherche en temps réel
  document.getElementById('searchInput').addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
  });
</script>
</body>
</html>
