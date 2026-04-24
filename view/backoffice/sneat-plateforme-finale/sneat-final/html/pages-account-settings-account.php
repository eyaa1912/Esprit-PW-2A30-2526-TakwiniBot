<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../controller/UtilisateurController.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: auth-login-basic.html');
    exit;
}

$controller = new UtilisateurController();
$userId     = (int) $_SESSION['user']['id'];
$user       = $controller->getById($userId);
$message    = '';
$error      = '';

// ── UPDATE PROFIL ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profil') {
    $nom            = trim($_POST['nom']            ?? '');
    $prenom         = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $telephone      = trim($_POST['telephone']      ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $sexe           = $_POST['sexe']                ?? '';
    $date_naissance = $_POST['date_naissance']      ?? '';
    if (empty($nom) || empty($email)) {
        $error = 'Nom et email sont obligatoires.';
    } else {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('UPDATE users SET nom=:nom, prenom=:prenom, email=:email, telephone=:tel, adresse=:adresse, sexe=:sexe, date_naissance=:dob WHERE id=:id');
            $stmt->execute(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'tel'=>$telephone ?: null,'adresse'=>$adresse ?: null,'sexe'=>$sexe ?: null,'dob'=>$date_naissance ?: null,'id'=>$userId]);
            $_SESSION['user']['nom']   = $nom;
            $_SESSION['user']['email'] = $email;
            $user    = $controller->getById($userId);
            $message = 'Profil mis à jour avec succès !';
        } catch (Exception $e) { $error = 'Erreur : ' . $e->getMessage(); }
    }
}

// ── UPLOAD AVATAR ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format non autorisé.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $error = 'Fichier trop grand (max 2MB).';
        } else {
            $dir = __DIR__ . '/../../../../../view/frontoffice/uploads/avatars/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                $avatarPath = 'uploads/avatars/' . $filename;
                $db = config::getConnexion();
                $db->prepare('UPDATE users SET avatar=:a WHERE id=:id')->execute(['a'=>$avatarPath,'id'=>$userId]);
                $_SESSION['user']['avatar'] = $avatarPath;
                $user = $controller->getById($userId);
                $message = 'Avatar mis à jour !';
            } else {
                $error = 'Impossible de sauvegarder le fichier.';
            }
        }
    }
}

$av = $user['avatar'] ?? '';
$avatarFull = !empty($av) ? '../../../../../view/frontoffice/' . $av : '../assets/img/avatars/1.png';
?>
<!doctype html>

<html
  lang="en"
  class="layout-menu-fixed layout-compact"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Profil Admin | Takwini</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/css/dark-mode.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <!-- Page CSS -->

    <!-- Custom Dropdown Styles -->
    <link rel="stylesheet" href="../assets/css/custom-dropdown.css" />
    <link rel="stylesheet" href="../assets/css/ripple-effect.css" />
    <link rel="stylesheet" href="../assets/css/logout-green.css" />

    <!-- Helpers -->
    <script>try{var t=localStorage.getItem("app.theme");if(t==="dark")document.documentElement.setAttribute("data-bs-theme","dark");}catch(e){}</script>
    <script src="../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="../assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.php" class="app-brand-link">
              <span class="app-brand-logo demo"><img src="../assets/img/favicon/tak.png" alt="Takwinibot" style="width:200px;height:76px;object-fit:contain;"></span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>

          <div class="menu-inner-shadow"></div>

                                        <ul class="menu-inner py-1">
            <!-- Tableau de bord : accueil + modules plateforme (pas de démos externes Académie / e-commerce) -->
            <li class="menu-item active open">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Tableau de bord</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="index.php" class="menu-link">
                    <div class="text-truncate">Accueil</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Formations</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-formations.html" class="menu-link">
                        <div class="text-truncate">Vue d&apos;ensemble</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-formations.html#sessions" class="menu-link">
                        <div class="text-truncate">Nos formations</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-inscriptions.html" class="menu-link">
                        <div class="text-truncate">Inscriptions</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-certificats.html" class="menu-link">
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
                      <a href="gestion-offres.html" class="menu-link">
                        <div class="text-truncate">Liste des offres</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="gestion-contrats.html" class="menu-link">
                        <div class="text-truncate">Contrats</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="gestion-reclamations.html" class="menu-link">
                    <div class="text-truncate">Réclamations</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-entretiens.html" class="menu-link">
                    <div class="text-truncate">Entretiens</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="gestion-produits.html" class="menu-link">
                    <div class="text-truncate">Produits</div>
                  </a>
                </li>
                <li class="menu-item open">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div class="text-truncate">Utilisateurs</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="gestion-utilisateurs.php" class="menu-link">
                        <div class="text-truncate">Liste des utilisateurs</div>
                      </a>
                    </li>
                    <li class="menu-item active">
                      <a href="pages-account-settings-account.php" class="menu-link">
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

            <li class="menu-item"><a href="changer-motdepasse.php" class="menu-link"><i class="menu-icon tf-icons bx bx-lock"></i><div class="text-truncate">Mot de passe</div></a></li>
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar">
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
                  <input
                    type="text"
                    class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none"
                    placeholder="Search..."
                    aria-label="Search..." />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Langue">
                    <i class="icon-base bx bx-globe icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item logout-btn" href="javascript:void(0);" data-app-lang="fr">Français</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-lang="en">English</a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Disposition du menu">
                    <i class="icon-base bx bx-layout icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-layout="vertical">Menu vertical</a>
                    </li>
                    <li>
                      <a
                        class="dropdown-item"
                        href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/html/horizontal-menu-template/"
                        target="_blank"
                        rel="noopener"
                        >Menu horizontal <span class="badge bg-label-primary ms-1 text-uppercase fs-tiny">Pro</span></a
                      >
                    </li>
                  </ul>
                </li>
<!-- app-toolbar-extras -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Raccourcis">
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
                          <a href="gestion-offres.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-briefcase icon-md"></i></span>
                            <small class="text-body-secondary">Offres</small>
                          </a>
                        </div>
                        <div class="dropdown-shortcuts-item col-6 border-end">
                          <a href="gestion-reclamations.html" class="d-flex flex-column align-items-center justify-content-center gap-2 py-4 text-body">
                            <span class="dropdown-shortcuts-icon rounded-circle d-inline-flex"><i class="icon-base bx bx-error-circle icon-md"></i></span>
                            <small class="text-body-secondary">Réclamations</small>
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
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications">
                    <span class="position-relative">
                      <i class="icon-base bx bx-bell icon-md"></i>
                      <span
                        class="badge-notifications position-absolute" style="width:9px;height:9px;background:#ff3e1d;border-radius:50%;border:2px solid #fff;top:2px;right:-1px;"
                        >3</span
                      >
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-notifications-list">
                    <li class="dropdown-menu-header border-bottom">
                      <div class="d-flex align-items-center px-3 py-3">
                        <h6 class="mb-0">Notifications</h6>
                      </div>
                    </li>
                    <li class="list-group list-group-flush">
                      <a href="gestion-reclamations.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-primary"><i class="icon-base bx bx-error icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Réclamation à traiter</p>
                            <small class="text-body-secondary">Plateforme</small>
                          </div>
                        </div>
                      </a>
                      <a href="gestion-entretiens.html" class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex align-items-center gap-3">
                          <div class="flex-shrink-0"><div class="avatar avatar-sm bg-label-warning"><i class="icon-base bx bx-calendar icon-sm"></i></div></div>
                          <div class="flex-grow-1">
                            <p class="mb-0 small">Entretien planifié demain</p>
                            <small class="text-body-secondary">Rappel</small>
                          </div>
                        </div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item me-2 me-xl-1">
                  <a class="nav-link" href="javascript:void(0);" id="app-theme-toggle" aria-label="Basculer thème clair ou sombre">
                    <i class="icon-base bx bx-moon icon-md" id="app-theme-toggle-icon"></i>
                  </a>
                </li>
                <li class="nav-item dropdown me-2 me-xl-1">
                  <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Couleurs et apparence">
                    <i class="icon-base bx bx-cog icon-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Couleur principale</h6></li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary-reset="1">Par défaut (Sneat)</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#696cff" data-app-primary-rgb="105, 108, 255">Violet</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#71dd37" data-app-primary-rgb="113, 221, 55">Vert</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#03c3ec" data-app-primary-rgb="3, 195, 236">Cyan</a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-app-primary="#ff3e1d" data-app-primary-rgb="255, 62, 29">Rouge</a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                      <a class="dropdown-item" href="mon-profil.php"
                        ><i class="icon-base bx bx-user icon-sm me-2"></i>Paramètres du compte</a
                      >
                    </li>
                  </ul>
                </li>


                <!-- Place this tag where you want the button to render. -->
                <li class="nav-item lh-1 me-4">
                  <a
                    class="github-button"
                    href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free"
                    data-icon="octicon-star"
                    data-size="large"
                    data-show-count="true"
                    aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
                    >Star</a
                  >
                </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="<?= htmlspecialchars($avatarFull) ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="mon-profil.php">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="<?= htmlspecialchars($avatarFull) ?>" alt class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" />
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

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <div class="col-md-12">
                  <div class="nav-align-top">
                    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
                      <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"
                          ><i class="icon-base bx bx-user icon-sm me-1_5"></i> Account</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="pages-account-settings-notifications.html"
                          ><i class="icon-base bx bx-bell icon-sm me-1_5"></i> Notifications</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="pages-account-settings-connections.html"
                          ><i class="icon-base bx bx-link-alt icon-sm me-1_5"></i> Connections</a
                        >
                      </li>
                    </ul>
                  </div>
                  <div class="card mb-6">
                    <!-- Account -->
                    <div class="card-body">
                      <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
                        <img
                          src="<?= htmlspecialchars($avatarFull) ?>"
                          alt="user-avatar"
                          class="d-block w-px-100 h-px-100 rounded"
                          id="uploadedAvatar" />
                        <div class="button-wrapper">
                          <form method="POST" enctype="multipart/form-data" style="display:inline;">
                            <input type="hidden" name="action" value="upload_avatar">
                            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                              <span class="d-none d-sm-block">Changer la photo</span>
                              <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                              <input type="file" id="upload" name="avatar" class="account-file-input" hidden accept="image/png, image/jpeg, image/gif, image/webp" onchange="this.form.submit()"/>
                            </label>
                          </form>
                          <div>JPG, GIF ou PNG. Max 2MB</div>
                        </div>
                      </div>
                    </div>
                    <div class="card-body pt-4">
                      <form id="formAccountSettings" method="POST" action="pages-account-settings-account.php">
                        <input type="hidden" name="action" value="update_profil">
                        <?php if ($message): ?>
                          <div class="alert alert-success mb-4"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                          <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <div class="row g-6">
                          <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input class="form-control" type="text" id="nom" name="nom"
                              value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required autofocus />
                          </div>
                          <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input class="form-control" type="text" id="prenom" name="prenom"
                              value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" />
                          </div>
                          <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input class="form-control" type="email" id="email" name="email"
                              value="<?= htmlspecialchars($user['email'] ?? '') ?>" required />
                          </div>
                          <div class="col-md-6">
                            <label for="phoneNumber" class="form-label">Téléphone</label>
                            <div class="input-group input-group-merge">
                              <span class="input-group-text">TN (+216)</span>
                              <input type="text" id="phoneNumber" name="telephone" class="form-control"
                                value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" placeholder="XX XXX XXX" />
                            </div>
                          </div>
                          <div class="col-md-12">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse"
                              value="<?= htmlspecialchars($user['adresse'] ?? '') ?>" placeholder="Adresse complète" />
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Sexe</label>
                            <select name="sexe" class="form-select">
                              <option value="">-- Choisir --</option>
                              <option value="homme" <?= (($user['sexe'] ?? '') === 'homme') ? 'selected' : '' ?>>Homme</option>
                              <option value="femme" <?= (($user['sexe'] ?? '') === 'femme') ? 'selected' : '' ?>>Femme</option>
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>"/>
                          </div>
                        </div>
                        <div class="mt-6">
                          <button type="submit" class="btn btn-primary me-3">Enregistrer</button>
                          <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                        </div>
                      </form>
                    </div>
                    <!-- /Account -->
                  </div>
                  <div class="card">
                    <h5 class="card-header">Delete Account</h5>
                    <div class="card-body">
                      <div class="mb-6 col-12 mb-0">
                        <div class="alert alert-warning">
                          <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                          <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                      </div>
                      <form id="formAccountDeactivation" onsubmit="return false">
                        <div class="form-check my-8 ms-2">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            name="accountActivation"
                            id="accountActivation" />
                          <label class="form-check-label" for="accountActivation"
                            >I confirm my account deactivation</label
                          >
                        </div>
                        <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    ©
                    <script>
                      document.write(new Date().getFullYear());
                    </script>
                    , made with ❤️ by
                    <a href="https://themeselection.com" target="_blank" class="footer-link">ThemeSelection</a>
                  </div>
                  <div class="d-none d-lg-inline-block">
                    <a
                      href="https://themeselection.com/item/category/admin-templates/"
                      target="_blank"
                      class="footer-link me-4"
                      >Admin Templates</a
                    >

                    <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>
                    <a
                      href="https://themeselection.com/item/category/bootstrap-admin-templates/"
                      target="_blank"
                      class="footer-link me-4"
                      >Bootstrap Dashboard</a
                    >

                    <a
                      href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/documentation/"
                      target="_blank"
                      class="footer-link me-4"
                      >Documentation</a
                    >

                    <a
                      href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free/issues"
                      target="_blank"
                      class="footer-link"
                      >Support</a
                    >
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <div class="buy-now">
      <a
        href="https://themeselection.com/item/sneat-dashboard-pro-bootstrap/"
        target="_blank"
        class="btn btn-danger btn-buy-now"
        >Upgrade to Pro</a
      >
    </div>

    <!-- Core JS -->

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>

    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>

    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/navbar-extras.js"></script>
    <script src="../assets/js/i18n.js"></script>

    <!-- Page JS -->
    <script src="../assets/js/pages-account-settings-account.js"></script>

    <!-- Traduction FR / EN -->
    <script>
      const translations = {
        fr: {
          // Navbar
          'Search...': 'Rechercher...',
          // Menu sidebar
          'Tableau de bord': 'Tableau de bord',
          'Accueil': 'Accueil',
          'Formations': 'Formations',
          'Offres': 'Offres',
          'Réclamations': 'Réclamations',
          'Entretiens': 'Entretiens',
          'Produits': 'Produits',
          'Utilisateurs': 'Utilisateurs',
          'Profil': 'Profil',
          'Applications': 'Applications',
          'Email': 'Email',
          'Discuter': 'Discuter',
          'Calendrier': 'Calendrier',
          'Authentification': 'Authentification',
          'Connexion': 'Connexion',
          'Inscription': 'Inscription',
          'Mot de passe oublié': 'Mot de passe oublié',
          // Page content
          'Account': 'Compte',
          'Notifications': 'Notifications',
          'Connections': 'Connexions',
          'Upload new photo': 'Télécharger une photo',
          'Reset': 'Réinitialiser',
          'Allowed JPG, GIF or PNG. Max size of 800K': 'JPG, GIF ou PNG autorisés. Taille max 800K',
          'First Name': 'Prénom',
          'Last Name': 'Nom',
          'E-mail': 'E-mail',
          'Organization': 'Organisation',
          'Phone Number': 'Numéro de téléphone',
          'Address': 'Adresse',
          'State': 'État',
          'Zip Code': 'Code postal',
          'Country': 'Pays',
          'Language': 'Langue',
          'Time zone': 'Fuseau horaire',
          'Currency': 'Devise',
          'Save changes': 'Enregistrer',
          'Cancel': 'Annuler',
          'Danger Zone': 'Zone dangereuse',
          'Delete Account': 'Supprimer le compte',
          'Log Out': 'Déconnexion',
          'My Profile': 'Mon profil',
          'Settings': 'Paramètres',
          'Billing Plan': 'Abonnement',
        },
        en: {
          // Navbar
          'Rechercher...': 'Search...',
          // Menu sidebar
          'Tableau de bord': 'Dashboard',
          'Accueil': 'Home',
          'Formations': 'Trainings',
          'Offres': 'Job Offers',
          'Réclamations': 'Complaints',
          'Entretiens': 'Interviews',
          'Produits': 'Products',
          'Utilisateurs': 'Users',
          'Profil': 'Profile',
          'Applications': 'Applications',
          'Email': 'Email',
          'Discuter': 'Chat',
          'Calendrier': 'Calendar',
          'Authentification': 'Authentication',
          'Connexion': 'Login',
          'Inscription': 'Register',
          'Mot de passe oublié': 'Forgot Password',
          // Page content
          'Compte': 'Account',
          'Connexions': 'Connections',
          'Télécharger une photo': 'Upload new photo',
          'Réinitialiser': 'Reset',
          'JPG, GIF ou PNG autorisés. Taille max 800K': 'Allowed JPG, GIF or PNG. Max size of 800K',
          'Prénom': 'First Name',
          'Nom': 'Last Name',
          'Organisation': 'Organization',
          'Numéro de téléphone': 'Phone Number',
          'Adresse': 'Address',
          'État': 'State',
          'Code postal': 'Zip Code',
          'Pays': 'Country',
          'Langue': 'Language',
          'Fuseau horaire': 'Time zone',
          'Devise': 'Currency',
          'Enregistrer': 'Save changes',
          'Annuler': 'Cancel',
          'Zone dangereuse': 'Danger Zone',
          'Supprimer le compte': 'Delete Account',
          'Déconnexion': 'Log Out',
          'Mon profil': 'My Profile',
          'Paramètres': 'Settings',
          'Abonnement': 'Billing Plan',
        }
      };

      function translatePage(lang) {
        const dict = translations[lang];
        if (!dict) return;

        // Traduire tous les noeuds texte dans les éléments ciblés
        const selectors = [
          '.menu-inner .text-truncate',
          '.menu-header-text',
          'label.form-label',
          '.btn',
          'h5', 'h6', 'small',
          '.nav-link span',
          '.dropdown-item span',
          '.dropdown-item',
          'input[placeholder]',
          '.card-header h5',
          '.card-body h5',
          '.card-body p',
          '.d-none.d-sm-block',
          'div:not([class])',
        ];

        document.querySelectorAll(selectors.join(',')).forEach(el => {
          // Ne pas toucher aux éléments qui ont des enfants éléments (sauf span/i)
          const hasElementChildren = Array.from(el.childNodes).some(n => n.nodeType === 1 && !['I','SPAN','SMALL'].includes(n.tagName));
          if (hasElementChildren) return;

          const text = el.textContent.trim();
          if (dict[text]) el.textContent = dict[text];
        });

        // Traduire les placeholders
        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
          if (dict[el.placeholder]) el.placeholder = dict[el.placeholder];
        });

        // Sauvegarder la langue choisie
        localStorage.setItem('app-lang', lang);
        document.documentElement.lang = lang;
      }

      // Écouter les clics sur les items de langue
      document.querySelectorAll('[data-app-lang]').forEach(el => {
        el.addEventListener('click', function () {
          translatePage(this.dataset.appLang);
        });
      });

      // Appliquer la langue sauvegardée au chargement
      const savedLang = localStorage.getItem('app-lang');
      if (savedLang && savedLang !== 'fr') {
        translatePage(savedLang);
      }
    </script>

    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  
<script>
// Avatar upload AJAX
document.getElementById('upload').addEventListener('change', function() {
    if (!this.files[0]) return;
    const formData = new FormData();
    formData.append('avatar', this.files[0]);
    fetch('../../../../../controller/upload_avatar.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const src = '../../../../../view/frontoffice/' + data.avatar + '?t=' + Date.now();
            document.getElementById('uploadedAvatar').src = src;
            document.querySelectorAll('.avatar-online img, .avatar img').forEach(img => img.src = src);
        } else { alert(data.error || 'Erreur upload'); }
    }).catch(() => alert('Erreur réseau'));
});
</script>

<!-- Custom Dropdown Behavior -->
<script src="../assets/js/custom-dropdown.js"></script>

</body>
</html>









