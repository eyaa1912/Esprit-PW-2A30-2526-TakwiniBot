<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../controller/UtilisateurController.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../../../../view/frontoffice/login.php');
    exit;
}

$userId  = (int) $_SESSION['user']['id'];
$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (strlen($new) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($new !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $db   = config::getConnexion();
            $stmt = $db->prepare('SELECT mot_de_passe FROM users WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            if (!password_verify($current, $user['mot_de_passe'])) {
                $error = 'Mot de passe actuel incorrect.';
            } else {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $stmt   = $db->prepare('UPDATE users SET mot_de_passe = :mdp WHERE id = :id');
                $stmt->execute(['mdp' => $hashed, 'id' => $userId]);
                $message = 'Mot de passe changé avec succès !';
            }
        } catch (Exception $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

$__av = $_SESSION['user']['avatar'] ?? '';
$__navAvatar = !empty($__av) ? '../../../../../view/frontoffice/' . $__av : '../assets/img/avatars/1.png';
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>Changer mot de passe | Takwini</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
    <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
    <link rel="stylesheet" href="../assets/css/demo.css"/>
    <link rel="stylesheet" href="../assets/css/dark-mode.css"/>
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
    <link rel="stylesheet" href="../assets/css/custom-dropdown.css"/>
    <link rel="stylesheet" href="../assets/css/logout-green.css"/>
    <script>try{var t=localStorage.getItem("app.theme");if(t==="dark")document.documentElement.setAttribute("data-bs-theme","dark");}catch(e){}</script>
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
                <li class="menu-item">
                    <a href="index.php" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-home-smile"></i>
                        <div class="text-truncate">Tableau de bord</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="gestion-utilisateurs.php" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-group"></i>
                        <div class="text-truncate">Utilisateurs</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="mon-profil.php" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div class="text-truncate">Mon profil</div>
                    </a>
                </li>
                <li class="menu-item active">
                    <a href="changer-motdepasse.php" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-lock"></i>
                        <div class="text-truncate">Mot de passe</div>
                    </a>
                </li>
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

        <div class="layout-page">
            <!-- NAVBAR -->
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
                                    <img src="<?= $__navAvatar ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"/>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="mon-profil.php">
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

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="fw-bold py-3 mb-4">Changer le mot de passe</h4>

                    <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible mb-4">
                        <i class="bx bx-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible mb-4">
                        <i class="bx bx-error-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bx bx-lock me-2"></i>Modifier le mot de passe</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="changer-motdepasse.php">
                                        <div class="mb-4">
                                            <label class="form-label">Mot de passe actuel</label>
                                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Nouveau mot de passe</label>
                                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 caractères" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                                            <input type="password" name="confirm_password" class="form-control" placeholder="Répétez le nouveau mot de passe" required>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Enregistrer
                                            </button>
                                            <a href="mon-profil.php" class="btn btn-outline-secondary">Annuler</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div class="footer-container d-flex align-items-center justify-content-between py-4">
                            <div>© <?= date('Y') ?>, Takwini</div>
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
</body>
</html>




