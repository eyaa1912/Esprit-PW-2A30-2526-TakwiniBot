<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../controller/UtilisateurController.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: auth-login-basic.html');
    exit;
}

$controller = new UtilisateurController();
$userId = (int) $_SESSION['user']['id'];
$user = $controller->getById($userId);
$message = '';
$error = '';

// ── UPDATE PROFIL ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profil') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    if (empty($nom) || empty($email)) {
        $error = 'Nom et email sont obligatoires.';
    } else {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('UPDATE users SET nom=:nom, prenom=:prenom, email=:email, telephone=:tel, adresse=:adresse WHERE id=:id');
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'tel' => $telephone ?: null,
                'adresse' => $adresse ?: null,
                'id' => $userId
            ]);
            
            // Mettre à jour la session
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['email'] = $email;
            
            $user = $controller->getById($userId);
            $message = 'Profil mis à jour avec succès !';
        } catch (Exception $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

// ── UPLOAD AVATAR ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Format non autorisé. Utilisez JPG, PNG ou GIF.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $error = 'Fichier trop volumineux. Maximum 2 MB.';
        } else {
            $uploadDir = __DIR__ . '/../assets/img/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                try {
                    $db = config::getConnexion();
                    $stmt = $db->prepare('UPDATE users SET avatar=:avatar WHERE id=:id');
                    $stmt->execute([
                        'avatar' => 'assets/img/avatars/' . $newFilename,
                        'id' => $userId
                    ]);
                    
                    $_SESSION['user']['avatar'] = 'assets/img/avatars/' . $newFilename;
                    $user = $controller->getById($userId);
                    $message = 'Avatar mis à jour avec succès !';
                } catch (Exception $e) {
                    $error = 'Erreur base de données : ' . $e->getMessage();
                }
            } else {
                $error = 'Erreur lors de l\'upload du fichier.';
            }
        }
    } else {
        $error = 'Aucun fichier sélectionné ou erreur d\'upload.';
    }
}

// ── DELETE ACCOUNT ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    if (isset($_POST['confirm'])) {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            session_destroy();
            header('Location: ../../../../../view/frontoffice/login.php');
            exit;
        } catch (Exception $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

$av = $user['avatar'] ?? '';
if (empty($av)) {
    $avatarSrc = '../assets/img/avatars/1.png';
} elseif (strpos($av, 'assets/img/avatars/') !== false) {
    $avatarSrc = '../' . $av;
} elseif (strpos($av, 'uploads/avatars/') !== false) {
    $avatarSrc = '../../../../../view/frontoffice/' . $av;
} else {
    $avatarSrc = '../assets/img/avatars/1.png';
}
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Mon Profil | Plateforme</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/css/dark-mode.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    
    <!-- Custom Dropdown Styles -->
    <link rel="stylesheet" href="../assets/css/custom-dropdown.css" />
    <link rel="stylesheet" href="../assets/css/ripple-effect.css" />
    <link rel="stylesheet" href="../assets/css/logout-green.css" />
    
    <style>
        .profile-card {
            background: linear-gradient(135deg, rgba(105, 108, 255, 0.05) 0%, rgba(105, 108, 255, 0.02) 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .avatar-upload {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
        }
        .avatar-upload img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #696cff;
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
        }
        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #696cff;
            color: white;
            border: 3px solid white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .avatar-upload-btn:hover {
            background: #5a5ddb;
            transform: scale(1.1);
        }
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .form-section h5 {
            color: #696cff;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .btn-save {
            background: linear-gradient(135deg, #696cff 0%, #5a5ddb 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
        }
        html[data-theme="dark"] .form-section {
            background: #2b2c40;
        }
    </style>
    
    <script>try{var t=localStorage.getItem("app.theme");if(t==="dark")document.documentElement.setAttribute("data-bs-theme","dark");}catch(e){}</script>
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- MENU LATÉRAL -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.php" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:200px;height:76px;object-fit:contain;">
                        </span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
                    </a>
                </div>
                <div class="menu-divider mt-0"></div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-smile"></i>
                            <div class="text-truncate">Tableau de bord</div>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="mon-profil.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div class="text-truncate">Mon Profil</div>
                        </a>
                    </li>
                </ul>
            </aside>
            
            <!-- LAYOUT PAGE -->
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
                            <!-- USER DROPDOWN -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="<?= htmlspecialchars($avatarSrc) ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="mon-profil.php">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="<?= htmlspecialchars($avatarSrc) ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?= htmlspecialchars($user['nom'] ?? '') ?></h6>
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
                
                <!-- CONTENT -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <h4 class="mb-4">Mon Profil</h4>
                        
                        <!-- Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i><?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- AVATAR -->
                        <div class="profile-card">
                            <form method="POST" enctype="multipart/form-data" id="avatarForm">
                                <input type="hidden" name="action" value="upload_avatar">
                                <div class="avatar-upload">
                                    <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Avatar" id="avatarPreview" />
                                    <label for="avatarInput" class="avatar-upload-btn">
                                        <i class="bx bx-camera"></i>
                                    </label>
                                    <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;" onchange="previewAndUpload(this)">
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-1"><?= htmlspecialchars($user['nom'] ?? '') ?> <?= htmlspecialchars($user['prenom'] ?? '') ?></h5>
                                    <p class="text-body-secondary mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                    <small class="text-body-secondary">Cliquez sur l'icône caméra pour changer votre avatar</small>
                                </div>
                            </form>
                        </div>
                        
                        <!-- FORMULAIRE PROFIL -->
                        <div class="form-section">
                            <h5><i class="bx bx-user me-2"></i>Informations Personnelles</h5>
                            <form method="POST" action="mon-profil.php">
                                <input type="hidden" name="action" value="update_profil">
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="telephone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-save">
                                            <i class="bx bx-save me-2"></i>Enregistrer les modifications
                                        </button>
                                        <a href="index.php" class="btn btn-outline-secondary ms-2">
                                            <i class="bx bx-x me-2"></i>Annuler
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- DELETE ACCOUNT -->
                        <div class="form-section mt-4" style="border: 1px solid #ffd5d5;">
                            <h5 style="color:#e53935;"><i class="bx bx-trash me-2"></i>Delete Account</h5>
                            <div class="alert" style="background:#fff5f5;border-left:4px solid #e53935;border-radius:8px;padding:15px;margin-bottom:20px;">
                                <p class="mb-1" style="font-weight:600;color:#333;">Are you sure you want to delete your account?</p>
                                <p class="mb-0" style="color:#666;font-size:13px;">Once you delete your account, there is no going back. Please be certain.</p>
                            </div>
                            <form method="POST" action="mon-profil.php" onsubmit="return confirmDelete()">
                                <input type="hidden" name="action" value="delete_account">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirmDeactivation" name="confirm" required>
                                    <label class="form-check-label" for="confirmDeactivation" style="font-size:14px;">
                                        I confirm my account deactivation
                                    </label>
                                </div>
                                <button type="submit" class="btn" style="background:#e53935;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:600;">
                                    <i class="bx bx-trash me-2"></i>Delete Account
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/js/menu.js"></script>
    
    <!-- Custom Dropdown Behavior -->
    <script src="../assets/js/custom-dropdown.js"></script>
    
    <script>
    function confirmDelete() {
        return confirm('Are you absolutely sure? This action cannot be undone!');
    }

    function previewAndUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
            
            // Auto-submit le formulaire
            setTimeout(function() {
                document.getElementById('avatarForm').submit();
            }, 500);
        }
    }
    
    // Auto-hide alerts après 5 secondes
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    </script>
</body>
</html>






