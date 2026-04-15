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
$avances    = count(array_filter($formations, fn($f) => $f['niveau'] === 'Avancé'));
$debutants  = count(array_filter($formations, fn($f) => $f['niveau'] === 'Débutant'));
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
          <span class="app-brand-logo demo"><img src="assets/img/favicon/tak.png" alt="Takwinibot" style="width:56px;height:56px;object-fit:contain;"></span>
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
                <li class="menu-item"><a href="html/gestion-inscriptions.html" class="menu-link"><div class="text-truncate">Inscriptions</div></a></li>
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
            <div class="dt-buttons btn-group flex-wrap">
              <button class="btn btn-secondary buttons-collection dropdown-toggle btn-label-secondary mx-3" type="button">
                <span><i class="bx bx-export me-1 bx-sm"></i>Exporter</span>
              </button>
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
</script>
</body>
</html>
