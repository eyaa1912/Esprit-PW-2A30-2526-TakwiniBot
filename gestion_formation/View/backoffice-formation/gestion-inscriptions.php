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
$confirmees = count(array_filter($inscriptions, fn($i) => $i['niveau'] === 'Confirmée'));
$en_attente = count(array_filter($inscriptions, fn($i) => $i['niveau'] === 'En attente'));
$annulees = count(array_filter($inscriptions, fn($i) => $i['niveau'] === 'Annulée'));
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
                      <span>Confirmées</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $confirmees ?></h4>
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
                        <h4 class="mb-0 me-2"><?= $en_attente ?></h4>
                      </div>
                      <p class="mb-0">Analyse semaine</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five bx-sm"></i></span></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                      <span>Annulées</span>
                      <div class="d-flex align-items-end mt-2">
                        <h4 class="mb-0 me-2"><?= $annulees ?></h4>
                      </div>
                      <p class="mb-0">Analyse semaine</p>
                    </div>
                    <div class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle bx-sm"></i></span></div>
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
                    <select class="form-select w-auto"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                  </div>
                  <div class="col-md-10">
                    <div class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column gap-3">
                      <div class="dataTables_filter">
                        <input type="search" class="form-control" placeholder="Rechercher..." />
                      </div>
                      <div class="dt-buttons btn-group flex-wrap">
                        <button class="btn btn-secondary buttons-collection dropdown-toggle btn-label-secondary mx-3" type="button">
                          <span><i class="bx bx-export me-1 bx-sm"></i>Exporter</span>
                        </button>
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
                  <tbody>
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
                              <a href="#" class="text-body delete-inscription-btn" data-id="<?= $insc['id'] ?>"><i class="bx bx-trash bx-sm me-2"></i></a>
                              <button class="btn p-0 text-body border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#editInscriptionModal" data-id="<?= $insc['id'] ?>" data-user_id="<?= htmlspecialchars($insc['user_id'], ENT_QUOTES) ?>" data-formation_id="<?= htmlspecialchars($insc['formation_id'], ENT_QUOTES) ?>" data-nom="<?= htmlspecialchars($insc['nom'], ENT_QUOTES) ?>" data-prenom="<?= htmlspecialchars($insc['prenom'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($insc['email'], ENT_QUOTES) ?>" data-niveau="<?= htmlspecialchars($insc['niveau'] ?? '', ENT_QUOTES) ?>" data-mode_formation="<?= htmlspecialchars($insc['mode_formation'] ?? '', ENT_QUOTES) ?>"><i class="bx bx-show bx-sm me-2"></i></button>
                              <a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded bx-sm"></i></a>
                              <div class="dropdown-menu dropdown-menu-end m-0">
                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editInscriptionModal" data-id="<?= $insc['id'] ?>" data-user_id="<?= htmlspecialchars($insc['user_id'], ENT_QUOTES) ?>" data-formation_id="<?= htmlspecialchars($insc['formation_id'], ENT_QUOTES) ?>" data-nom="<?= htmlspecialchars($insc['nom'], ENT_QUOTES) ?>" data-prenom="<?= htmlspecialchars($insc['prenom'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($insc['email'], ENT_QUOTES) ?>" data-niveau="<?= htmlspecialchars($insc['niveau'] ?? '', ENT_QUOTES) ?>" data-mode_formation="<?= htmlspecialchars($insc['mode_formation'] ?? '', ENT_QUOTES) ?>">Modifier</button>
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
                        <label class="form-label">ID Utilisateur <span class="text-danger">*</span></label>
                        <input type="number" name="user_id" id="editUserId" class="form-control" required min="1" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">ID Formation <span class="text-danger">*</span></label>
                        <input type="number" name="formation_id" id="editFormationId" class="form-control" required min="1" readonly />
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

<script>
// Pre-fill edit modal
document.getElementById('editInscriptionModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('editId').value = btn.dataset.id;
  document.getElementById('editUserId').value = btn.dataset.user_id;
  document.getElementById('editFormationId').value = btn.dataset.formation_id;
  document.getElementById('editNom').value = btn.dataset.nom;
  document.getElementById('editPrenom').value = btn.dataset.prenom;
  document.getElementById('editEmail').value = btn.dataset.email;
  document.getElementById('editNiveau').value = btn.dataset.niveau;
  document.getElementById('editModeFormation').value = btn.dataset.mode_formation;
});

// Handle delete confirmation modal
document.querySelectorAll('.delete-inscription-btn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const inscriptionId = this.dataset.id;
    const deleteUrl = 'gestion-inscriptions.php?delete=' + inscriptionId;
    document.getElementById('confirmDeleteBtn').href = deleteUrl;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
  });
});
</script>

</body>
</html>
