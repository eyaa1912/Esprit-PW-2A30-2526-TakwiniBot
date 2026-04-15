<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

$error_login    = '';
$error_register = '';
$success        = '';
$active_panel   = 'login'; // quel panneau afficher par défaut

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    $controller = new UtilisateurController();

    // ── CONNEXION ────────────────────────────────────────────────────────────
    if ($action === 'login') {
        $active_panel = 'login';

        if (empty($email) || empty($password)) {
            $error_login = 'Email et mot de passe sont obligatoires.';
        } else {
            $result = $controller->login($email, $password);

            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                $success = $result['message'];
                // Redirection selon le rôle
                if ($result['user']['role'] === 'admin') {
                    header('refresh:1;url=../../view/backoffice/sneat-plateforme-finale/sneat-final/html/gestion-utilisateurs.php');
                } else {
                    header('refresh:1;url=formations/index.html');
                }
            } else {
                $error_login = $result['message'];
                // Si email introuvable → basculer vers le panneau inscription
                if ($result['action'] === 'not_found') {
                    $active_panel = 'register';
                }
            }
        }
    }

    // ── INSCRIPTION ──────────────────────────────────────────────────────────
    elseif ($action === 'register') {
        $active_panel = 'register';

        if (empty($nom) || empty($email) || empty($password)) {
            $error_register = 'Tous les champs sont obligatoires.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            $result = $controller->register($nom, $email, $password);

            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                $success = $result['message'];
                header('refresh:1;url=formations/index.html');
            } else {
                $error_register = $result['message'];
                // Si email déjà utilisé → basculer vers connexion
                if ($result['action'] === 'already_exists') {
                    $active_panel = 'login';
                    $error_login  = $result['message'];
                    $error_register = '';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takwini – Connexion / Inscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif; }

        body {
            background: linear-gradient(to right, #e2e2e2, #d7f0d8);
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,.35);
            position: relative; overflow: hidden;
            width: 768px; max-width: 100%; min-height: 480px;
        }

        .container p  { font-size:14px; line-height:20px; letter-spacing:.3px; margin:20px 0; }
        .container span { font-size:12px; }
        .container a  { color:#333; font-size:13px; text-decoration:none; margin:15px 0 10px; }

        .container button {
            background-color: #2e7d32; color:#fff; font-size:12px;
            padding:10px 45px; border:1px solid transparent; border-radius:8px;
            font-weight:600; letter-spacing:.5px; text-transform:uppercase;
            margin-top:10px; cursor:pointer;
        }
        .container button.hidden { background-color:transparent; border-color:#fff; }

        .container form {
            background-color:#fff; display:flex; align-items:center;
            justify-content:center; flex-direction:column;
            padding:0 40px; height:100%;
        }

        .container input {
            background-color:#eee; border:none; margin:6px 0;
            padding:10px 15px; font-size:13px; border-radius:8px;
            width:100%; outline:none;
        }

        .form-container { position:absolute; top:0; height:100%; transition:all .6s ease-in-out; }

        .sign-in { left:0; width:50%; z-index:2; }
        .container.active .sign-in { transform:translateX(100%); }

        .sign-up { left:0; width:50%; opacity:0; z-index:1; }
        .container.active .sign-up { transform:translateX(100%); opacity:1; z-index:5; animation:move .6s; }

        @keyframes move {
            0%,49.99% { opacity:0; z-index:1; }
            50%,100%  { opacity:1; z-index:5; }
        }

        .social-icons { margin:20px 0; }
        .social-icons a {
            border:1px solid #ccc; border-radius:20%;
            display:inline-flex; justify-content:center; align-items:center;
            margin:0 3px; width:40px; height:40px;
        }

        .toggle-container {
            position:absolute; top:0; left:50%; width:50%; height:100%;
            overflow:hidden; transition:all .6s ease-in-out;
            border-radius:150px 0 0 100px; z-index:1000;
        }
        .container.active .toggle-container { transform:translateX(-100%); border-radius:0 150px 100px 0; }

        .toggle {
            background: linear-gradient(to right, #43a047, #2e7d32);
            color:#fff; position:relative; left:-100%;
            height:100%; width:200%;
            transform:translateX(0); transition:all .6s ease-in-out;
        }
        .container.active .toggle { transform:translateX(50%); }

        .toggle-panel {
            position:absolute; width:50%; height:100%;
            display:flex; align-items:center; justify-content:center;
            flex-direction:column; padding:0 30px; text-align:center;
            top:0; transition:all .6s ease-in-out;
        }
        .toggle-left  { transform:translateX(-200%); }
        .container.active .toggle-left  { transform:translateX(0); }
        .toggle-right { right:0; transform:translateX(0); }
        .container.active .toggle-right { transform:translateX(200%); }

        /* Alertes */
        .alert { width:100%; padding:8px 12px; border-radius:8px; font-size:12px; margin:6px 0; text-align:center; }
        .alert-danger  { background:#fde8e8; color:#c0392b; }
        .alert-success { background:#e8f5e9; color:#2e7d32; }
    </style>
</head>
<body>

<div class="container" id="container">

    <!-- ── INSCRIPTION ─────────────────────────────── -->
    <div class="form-container sign-up">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="register">
            <h1>Créer un compte</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <span>ou utilisez votre email pour vous inscrire</span>

            <?php if ($error_register): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_register) ?></div>
            <?php endif; ?>
            <?php if ($success && $active_panel === 'register'): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <input type="text"     name="nom"      placeholder="Nom complet"
                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
            <input type="email"    name="email"     placeholder="Email"
                   value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'register' ? ($_POST['email'] ?? '') : '') ?>" required>
            <input type="password" name="password"  placeholder="Mot de passe (min. 6 car.)" required>
            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <!-- ── CONNEXION ──────────────────────────────── -->
    <div class="form-container sign-in">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">
            <h1>Se connecter</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <span>ou utilisez votre email et mot de passe</span>

            <?php if ($error_login): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_login) ?></div>
            <?php endif; ?>
            <?php if ($success && $active_panel === 'login'): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <input type="email"    name="email"    placeholder="Email"
                   value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'login' ? ($_POST['email'] ?? '') : '') ?>" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <a href="#">Mot de passe oublié ?</a>
            <button type="submit">Se connecter</button>
        </form>
    </div>

    <!-- ── TOGGLE ─────────────────────────────────── -->
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Bon retour !</h1>
                <p>Entrez vos identifiants pour accéder à toutes les fonctionnalités</p>
                <button class="hidden" id="loginBtn">Se connecter</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Bonjour !</h1>
                <p>Inscrivez-vous avec vos informations personnelles pour commencer</p>
                <button class="hidden" id="registerBtn">S'inscrire</button>
            </div>
        </div>
    </div>
</div>

<script>
    const container   = document.getElementById('container');
    const registerBtn = document.getElementById('registerBtn');
    const loginBtn    = document.getElementById('loginBtn');

    registerBtn.addEventListener('click', () => container.classList.add('active'));
    loginBtn.addEventListener('click',    () => container.classList.remove('active'));

    // Ouvrir automatiquement le bon panneau selon la réponse PHP
    <?php if ($active_panel === 'register'): ?>
    container.classList.add('active');
    <?php endif; ?>
</script>
</body>
</html>
