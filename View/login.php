<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $db  = config::getConnexion();
        $req = $db->prepare('SELECT * FROM user WHERE email = :email LIMIT 1');
        $req->execute(['email' => $email]);
        $user = $req->fetch();

        if ($user && md5($password) === $user['motDePasse']) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nom']  = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            // Redirection selon le rôle
            if ($user['role'] === 'admin' || $user['role'] === 'recruteur') {
                header('Location: Admin/pages/index.php');
            } else {
                header('Location: offres.php');
            }
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Takwinibot - Connexion</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Exo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body data-spy="scroll" data-offset="80">

<!-- PRELOADER -->
<div class="preloader"><div class="status"><div class="status-mes"></div></div></div>

<!-- NAVBAR -->
<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3"><span class="icon-close2 js-menu-toggle"></span></div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>
<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
                <h1 class="mb-0 site-logo"><a href="index.php"><img src="assets/img/logo.png" alt=""></a></h1>
            </div>
            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        <li><a href="index.php" class="nav-link">Home</a></li>
                        <li><a href="about.html" class="nav-link">About</a></li>
                        <li><a href="formation.html" class="nav-link">Formations</a></li>
                        <li><a href="gallery.html" class="nav-link">Produits</a></li>
                        <li><a href="blog.html" class="nav-link">Entretien</a></li>
                        <li><a href="offres.php" class="nav-link">Offres</a></li>
                        <li class="active"><a href="login.php" class="nav-link">Se connecter</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position:relative;top:3px;">
                <a href="#" class="site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
            </div>
        </div>
    </div>
</header>

<!-- SECTION TOP -->
<section class="section-top">
    <div class="container">
        <div class="col-lg-10 offset-lg-1 text-center">
            <div class="section-top-title"><h1>Connexion</h1></div>
        </div>
    </div>
</section>

<!-- LOGIN FORM -->
<section class="login_register section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3 col-sm-12">
                <div class="login">
                    <h4 class="login_register_title">Connectez-vous à votre compte</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control input-label"
                                placeholder="Adresse email" required
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control input-label"
                                placeholder="Mot de passe" required>
                        </div>
                        <div class="form-group col-md-12 mbnone">
                            <button class="btn btn-contact-bg" type="submit">Se connecter</button>
                        </div>
                        <p class="mt-3 text-center">
                            Pas encore de compte ? <a href="register.html">S'inscrire</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer-area">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-12">
                <p class="footer_copyright">Takwinibot &copy; 2026</p>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/jquery-1.12.4.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/menu.js"></script>
<script src="assets/js/jquery.sticky.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>
