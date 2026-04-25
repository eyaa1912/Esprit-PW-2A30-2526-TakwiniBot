<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../../view/frontoffice/login.php'); exit;
}

$db      = config::getConnexion();
$message = '';
$error   = '';

// Valider ou rejeter
if (isset($_GET['valider'])) {
    $db->prepare('UPDATE users SET statut = "actif" WHERE id = :id AND role = "recruteur"')
       ->execute(['id' => (int)$_GET['valider']]);
    $message = 'Recruteur validé avec succès !';
}
if (isset($_GET['rejeter'])) {
    $db->prepare('UPDATE users SET statut = "suspendu" WHERE id = :id AND role = "recruteur"')
       ->execute(['id' => (int)$_GET['rejeter']]);
    $message = 'Recruteur rejeté.';
}

$recruteurs = $db->query('SELECT * FROM users WHERE role = "recruteur" ORDER BY id DESC')->fetchAll();
$enAttente  = array_filter($recruteurs, fn($r) => $r['statut'] === 'en_attente');
$valides    = array_filter($recruteurs, fn($r) => $r['statut'] === 'actif');

$__av = $_SESSION['user']['avatar'] ?? '';
$__navAvatar = !empty($__av) ? '../../../../../view/frontoffice/' . $__av : '../assets/img/avatars/1.png';
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gestion Recruteurs | Takwini</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
    <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
    <link rel="stylesheet" href="../assets/css/demo.css"/>
    <link rel="stylesheet" href="../assets/css/dark-mode.css"/>
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
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
                    <span class="app-brand-logo demo"><img src="../assets/img/favicon/tak.png" alt="Takwini" style="width:56px;height:56px;object-fit:contain;"></span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
                </a>
            </div>
            <div class="menu-divider mt-0"></div>
            <ul class="menu-inner py-1">
                <li class="menu-item"><a href="index.php" class="menu-link"><i class="menu-icon tf-icons bx bx-home-smile"></i><div class="text-truncate">Tableau de bord</div></a></li>
                <li class="menu-item"><a href="gestion-utilisateurs.php" class="menu-link"><i class="menu-icon tf-icons bx bx-group"></i><div class="text-truncate">Utilisateurs</div></a></li>
                <li class="menu-item active"><a href="gestion-recruteurs.php" class="menu-link"><i class="menu-icon tf-icons bx bx-briefcase"></i><div class="text-truncate">Recruteurs</div></a></li>
                <li class="menu-item"><a href="changer-motdepasse.php" class="menu-link"><i class="menu-icon tf-icons bx bx-lock"></i><div class="text-truncate">Mot de passe</div></a></li>
                <li class="menu-item"><a href="../../../../../view/frontoffice/formations/index.php" class="menu-link" target="_blank"><i class="menu-icon tf-icons bx bx-globe"></i><div class="text-truncate">Voir le site</div></a></li>
                <li class="menu-item"><a href="../../../../../controller/logout.php" class="menu-link"><i class="menu-icon tf-icons bx bx-power-off"></i><div class="text-truncate">Déconnexion</div></a></li>
            </ul>
        </aside>

        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="navbar-nav-right d-flex align-items-center justify-content-end">
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <li class="nav-item me-2">
                            <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle">
                                <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="mon-profil.php">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3"><div class="avatar avatar-online"><img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/></div></div>
                                            <div class="flex-grow-1"><h6 class="mb-0"><?= htmlspecialchars($_SESSION['user']['nom']) ?></h6><small class="text-body-secondary">Admin</small></div>
                                        </div>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li><a class="dropdown-item" href="mon-profil.php"><i class="icon-base bx bx-user icon-md me-3"></i><span>Mon profil</span></a></li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li><a class="dropdown-item" href="../../../../../controller/logout.php"><i class="icon-base bx bx-power-off icon-md me-3"></i><span>Déconnexion</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="fw-bold py-3 mb-4"><i class="bx bx-briefcase me-2"></i>Gestion des Recruteurs</h4>

                    <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible mb-4">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="row g-4 mb-4">
                        <div class="col-sm-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="text-warning"><?= count($enAttente) ?></h2>
                                    <p class="mb-0">En attente de validation</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="text-success"><?= count($valides) ?></h2>
                                    <p class="mb-0">Recruteurs validés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2><?= count($recruteurs) ?></h2>
                                    <p class="mb-0">Total recruteurs</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- En attente -->
                    <?php if (count($enAttente) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-warning"><i class="bx bx-time me-2"></i>En attente de validation (<?= count($enAttente) ?>)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead><tr><th>Nom</th><th>Email</th><th>Entreprise</th><th>Matricule</th><th>Secteur</th><th>Document</th><th>Actions</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($enAttente as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['nom']) ?> <?= htmlspecialchars($r['prenom'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($r['email']) ?></td>
                                        <td><?= htmlspecialchars($r['entreprise'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['matricule_fiscal'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['secteur'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($r['document_entreprise'])): ?>
                                                <a href="../../../../../view/frontoffice/<?= htmlspecialchars($r['document_entreprise']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-file me-1"></i>Voir
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="gestion-recruteurs.php?valider=<?= $r['id'] ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Valider ce recruteur ?')">
                                                <i class="bx bx-check me-1"></i>Valider
                                            </a>
                                            <a href="gestion-recruteurs.php?rejeter=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Rejeter ce recruteur ?')">
                                                <i class="bx bx-x me-1"></i>Rejeter
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Validés -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 text-success"><i class="bx bx-check-circle me-2"></i>Recruteurs validés (<?= count($valides) ?>)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead><tr><th>Nom</th><th>Email</th><th>Entreprise</th><th>Matricule</th><th>Secteur</th><th>Actions</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($valides as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['nom']) ?> <?= htmlspecialchars($r['prenom'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($r['email']) ?></td>
                                        <td><?= htmlspecialchars($r['entreprise'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['matricule_fiscal'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['secteur'] ?? '-') ?></td>
                                        <td>
                                            <a href="gestion-recruteurs.php?rejeter=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspendre ce recruteur ?')">
                                                <i class="bx bx-block me-1"></i>Suspendre
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl"><div class="footer-container d-flex align-items-center justify-content-between py-4"><div>© <?= date('Y') ?>, Takwini</div></div></div>
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
</body>
</html>
