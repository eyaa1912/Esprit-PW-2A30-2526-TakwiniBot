<?php
session_start();
require_once __DIR__ . '/../../../../../config.php';
require_once __DIR__ . '/../../../../../controller/UtilisateurController.php';

// Déjà connecté en tant qu'admin → redirection directe
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location: gestion-utilisateurs.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email et mot de passe sont obligatoires.';
    } else {
        $controller = new UtilisateurController();
        $result     = $controller->login($email, $password);

        if (!$result['success']) {
            $error = $result['message'];
        } elseif ($result['user']['role'] !== 'admin') {
            $error = 'Accès refusé. Vous n\'êtes pas administrateur.';
        } else {
            $_SESSION['user'] = $result['user'];
            header('Location: gestion-utilisateurs.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
  <title>Connexion Admin | Takwini</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/tak.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css"/>
  <link rel="stylesheet" href="../assets/vendor/css/core.css"/>
  <link rel="stylesheet" href="../assets/css/demo.css"/>
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
  <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css"/>
  <script src="../assets/vendor/js/helpers.js"></script>
  <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">

      <div class="card">
        <div class="card-body">

          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="#" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="../assets/img/favicon/tak.png" alt="Takwini" style="width:40px;height:40px;object-fit:contain;">
              </span>
              <span class="app-brand-text demo fw-bold ms-2" style="font-size:1.3rem;">Takwini</span>
            </a>
          </div>

          <h4 class="mb-1 text-center">Espace Administrateur</h4>
          <p class="mb-6 text-center text-muted">Connectez-vous avec votre compte admin</p>

          <?php if ($error): ?>
          <div class="alert alert-danger mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i><?= htmlspecialchars($error) ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="login-admin.php">
            <div class="mb-4">
              <label class="form-label" for="email">Email</label>
              <input type="email" id="email" name="email" class="form-control"
                     placeholder="admin@exemple.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     autofocus required/>
            </div>

            <div class="mb-6">
              <label class="form-label" for="password">Mot de passe</label>
              <div class="input-group">
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Votre mot de passe" required/>
                <span class="input-group-text cursor-pointer" id="togglePassword">
                  <i class="bx bx-hide" id="toggleIcon"></i>
                </span>
              </div>
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100">
              <i class="bx bx-log-in me-2"></i>Se connecter
            </button>
          </form>

          <div class="text-center mt-4">
            <a href="../../../../../view/frontoffice/login.php" class="text-muted small">
              <i class="bx bx-arrow-back me-1"></i>Retour au site
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/js/main.js"></script>
<script>
  // Afficher/masquer mot de passe
  document.getElementById('togglePassword').addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('bx-hide', 'bx-show');
    } else {
      input.type = 'password';
      icon.classList.replace('bx-show', 'bx-hide');
    }
  });
</script>
</body>
</html>
