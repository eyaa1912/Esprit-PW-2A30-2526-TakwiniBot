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

<<<<<<< Updated upstream
    // ── CONNEXION ────────────────────────────────────────────────────────────
=======
    // ── CONNEXION ──────────────────────────────────────────────────────────
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
                // Si email introuvable → basculer vers le panneau inscription
                if ($result['action'] === 'not_found') {
                    $active_panel = 'register';
                }
=======
                if ($result['action'] === 'not_found') $active_panel = 'register';
>>>>>>> Stashed changes
            }
        }
    }

<<<<<<< Updated upstream
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
=======
    // ── INSCRIPTION UNIFIÉE ────────────────────────────────────────────────
    } elseif ($action === 'register_unified') {
        $active_panel   = 'register';
        $role           = $_POST['role']            ?? 'candidat';
        $nom            = trim($_POST['nom']            ?? '');
        $prenom         = trim($_POST['prenom']         ?? '');
        $email          = trim($_POST['email']          ?? '');
        $password       = $_POST['password']            ?? '';
        $telephone      = trim($_POST['telephone']      ?? '');

        // Validation commune
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error_register = 'Les champs nom, prénom, email et mot de passe sont obligatoires.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            if ($role === 'candidat') {
                // ── Candidat ──
                $sexe           = $_POST['sexe']            ?? '';
                $date_naissance = $_POST['date_naissance']  ?? '';
                $adresse        = trim($_POST['adresse']    ?? '');
                $handicap       = !empty($_POST['handicap']) ? 1 : 0;
                $type_handicap  = $handicap ? trim($_POST['type_handicap'] ?? '') : null;

                $result = $controller->register(
                    $nom, $prenom, $email, $password,
                    $telephone, $sexe, $date_naissance, $adresse,
                    $handicap, $type_handicap
                );
                if ($result['success']) {
                    $_SESSION['user'] = $result['user'];
                    header('Location: formations/index.php');
                    exit;
                } else {
                    $error_register = $result['message'];
                    if ($result['action'] === 'already_exists') {
                        $active_panel   = 'login';
                        $error_login    = $result['message'];
                        $error_register = '';
                    }
                }

            } else {
                // ── Recruteur ──
                $entreprise = trim($_POST['entreprise']       ?? '');
                $matricule  = trim($_POST['matricule_fiscal'] ?? '');
                $secteur    = trim($_POST['secteur']          ?? '');

                if (empty($entreprise) || empty($matricule)) {
                    $error_register = 'Tous les champs obligatoires (*) doivent être remplis.';
                } else {
                    try {
                        $db    = config::getConnexion();
                        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
                        $check->execute(['email' => $email]);
                        if ($check->fetch()) {
                            $error_register = 'Cet email est déjà utilisé.';
                        } else {
                            $hashed  = password_hash($password, PASSWORD_BCRYPT);
                            $docPath = null;
                            if (isset($_FILES['document_entreprise']) && $_FILES['document_entreprise']['error'] === 0) {
                                $ext     = strtolower(pathinfo($_FILES['document_entreprise']['name'], PATHINFO_EXTENSION));
                                $allowed = ['pdf','jpg','jpeg','png'];
                                if (in_array($ext, $allowed) && $_FILES['document_entreprise']['size'] <= 5 * 1024 * 1024) {
                                    $dir = __DIR__ . '/uploads/documents/';
                                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                                    $filename = 'doc_' . time() . '_' . uniqid() . '.' . $ext;
                                    if (move_uploaded_file($_FILES['document_entreprise']['tmp_name'], $dir . $filename))
                                        $docPath = 'uploads/documents/' . $filename;
                                }
                            }
                            $stmt = $db->prepare(
                                'INSERT INTO users
                                    (nom, prenom, email, mot_de_passe, telephone, role, statut,
                                     entreprise, matricule_fiscal, secteur, document_entreprise)
                                 VALUES
                                    (:nom, :prenom, :email, :mdp, :tel, :role, :statut,
                                     :entreprise, :matricule, :secteur, :doc)'
                            );
                            $stmt->execute([
                                'nom'        => $nom,
                                'prenom'     => $prenom,
                                'email'      => $email,
                                'mdp'        => $hashed,
                                'tel'        => $telephone ?: null,
                                'role'       => 'recruteur',
                                'statut'     => 'en_attente',
                                'entreprise' => $entreprise,
                                'matricule'  => $matricule,
                                'secteur'    => $secteur ?: null,
                                'doc'        => $docPath,
                            ]);
                            // Notification admin
                            $db->prepare(
                                'INSERT INTO notifications (titre, message, type, lien)
                                 VALUES (:titre, :message, :type, :lien)'
                            )->execute([
                                'titre'   => 'Nouveau recruteur en attente',
                                'message' => $nom . ' ' . $prenom . ' (' . $entreprise . ') — à valider',
                                'type'    => 'recruteur',
                                'lien'    => 'gestion-recruteurs.php',
                            ]);
                            $success      = 'Demande envoyée ! Un administrateur validera votre compte sous 48h.';
                            $active_panel = 'login';
                        }
                    } catch (Exception $e) {
                        $error_register = 'Erreur : ' . $e->getMessage();
                    }
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
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
=======
/* ── Formulaires ── */
.form-container {
    position:absolute; top:0; left:0; width:50%; height:100%;
    overflow-y:auto; transition:transform .6s ease, opacity .6s ease;
}
.sign-in  { z-index:2; transform:translateX(0);    opacity:1; }
.sign-up  { z-index:1; transform:translateX(-100%); opacity:0; pointer-events:none; }

.container.active .sign-in { transform:translateX(100%); opacity:0; pointer-events:none; z-index:1; }
.container.active .sign-up { transform:translateX(100%); opacity:1; pointer-events:all;  z-index:5; }
>>>>>>> Stashed changes

        .container input {
            background-color:#eee; border:none; margin:6px 0;
            padding:10px 15px; font-size:13px; border-radius:8px;
            width:100%; outline:none;
        }

<<<<<<< Updated upstream
        .form-container { position:absolute; top:0; height:100%; transition:all .6s ease-in-out; }

        .sign-in { left:0; width:50%; z-index:2; }
        .container.active .sign-in { transform:translateX(100%); }
=======
/* ── Inputs ── */
.input-wrap { width:100%; position:relative; margin:4px 0; }
.input-wrap input {
    width:100%; padding:11px 16px;
    background:#e8f5e9; border:1px solid #c8e6c9;
    border-radius:10px; font-size:13px; outline:none;
    transition:all .25s; color:#1a1a2e; font-family:inherit;
}
.input-wrap input:focus {
    background:#d4edda; border-color:#4caf50;
    box-shadow:0 0 0 3px rgba(76,175,80,.15);
}
.input-wrap select {
    width:100%; padding:11px 16px;
    background:#e8f5e9; border:1px solid #c8e6c9;
    border-radius:10px; font-size:13px; outline:none;
    transition:all .25s; color:#1a1a2e; font-family:inherit;
    cursor:pointer; appearance:none;
}
.input-wrap select:focus {
    background:#d4edda; border-color:#4caf50;
    box-shadow:0 0 0 3px rgba(76,175,80,.15);
}

.container input[type=file] {
    width:100%; padding:10px 14px; background:#e8f5e9;
    border:1px dashed #c8e6c9; border-radius:10px;
    font-size:13px; outline:none; cursor:pointer; margin:5px 0;
}
.container input[type=file]:hover { border-color:#4caf50; background:#d4edda; }
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
        .toggle-container {
            position:absolute; top:0; left:50%; width:50%; height:100%;
            overflow:hidden; transition:all .6s ease-in-out;
            border-radius:150px 0 0 100px; z-index:1000;
        }
        .container.active .toggle-container { transform:translateX(-100%); border-radius:0 150px 100px 0; }
=======
/* ── Role selector ── */
.role-selector { display:flex; gap:10px; width:100%; margin:6px 0 10px; }
.role-selector label {
    display:flex; align-items:center; gap:8px;
    font-size:14px; cursor:pointer; font-weight:600; color:#555;
    background:#e8f5e9; border:2px solid #c8e6c9; border-radius:12px;
    padding:11px 18px; flex:1; transition:all .2s;
}
.role-selector label:has(input:checked) {
    border-color:#4caf50; background:#d4edda; color:#2e7d32;
    box-shadow:0 0 0 3px rgba(76,175,80,.15);
}
.role-selector input[type=radio] { accent-color:#4caf50; width:16px; height:16px; }

/* ── Radio sexe ── */
.radio-group { display:flex; gap:10px; width:100%; margin:4px 0; }
.radio-group label {
    display:flex; align-items:center; gap:8px;
    font-size:13px; cursor:pointer; font-weight:500; color:#555;
    background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px;
    padding:9px 14px; flex:1; transition:all .2s;
}
.radio-group label:has(input:checked) { border-color:#4caf50; background:#d4edda; color:#2e7d32; }
.radio-group input[type=radio] { accent-color:#4caf50; }
>>>>>>> Stashed changes

        .toggle {
            background: linear-gradient(to right, #43a047, #2e7d32);
            color:#fff; position:relative; left:-100%;
            height:100%; width:200%;
            transform:translateX(0); transition:all .6s ease-in-out;
        }
        .container.active .toggle { transform:translateX(50%); }

<<<<<<< Updated upstream
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
=======
/* ── Erreurs ── */
.field-error { color:#e53935; font-size:11px; margin:0 0 2px 4px; min-height:12px; display:block; line-height:1.2; }
.input-error input, .input-error select { border-color:#e53935 !important; background:#fff5f5 !important; }

/* ── Alertes ── */
.alert { width:100%; padding:10px 14px; border-radius:10px; font-size:13px; margin:6px 0; display:flex; align-items:center; gap:8px; }
.alert-danger  { background:#fde8e8; color:#c0392b; border-left:3px solid #e53935; }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left:3px solid #4caf50; }

/* ── Section label ── */
.section-label {
    width:100%; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:1px; color:#aaa; margin:10px 0 4px;
}

/* ── Note recruteur ── */
.recruteur-note {
    width:100%; padding:10px 14px; border-radius:10px; font-size:12px;
    background:#fff8e1; color:#f57f17; border-left:3px solid #ffc107;
    margin:6px 0; display:flex; align-items:flex-start; gap:8px; line-height:1.5;
}

/* ── Toggle ── */
.toggle-container {
    position:absolute; top:0; left:50%; width:50%; height:100%;
    overflow:hidden; transition:all .6s cubic-bezier(.68,-.55,.27,1.55);
    border-radius:120px 0 0 120px; z-index:1000;
}
.container.active .toggle-container { transform:translateX(-100%); border-radius:0 120px 120px 0; }

.toggle {
    background:linear-gradient(160deg,#1b5e20 0%,#2e7d32 35%,#43a047 70%,#66bb6a 100%);
    color:#fff; position:relative; left:-100%;
    height:100%; width:200%; transform:translateX(0);
    transition:all .6s cubic-bezier(.68,-.55,.27,1.55);
}
.toggle::before {
    content:''; position:absolute; width:300px; height:300px;
    background:radial-gradient(circle,rgba(255,255,255,.08),transparent 70%);
    top:-50px; right:100px; border-radius:50%;
}
.container.active .toggle { transform:translateX(50%); }

.toggle-panel {
    position:absolute; width:50%; height:100%;
    display:flex; align-items:center; justify-content:center;
    flex-direction:column; padding:40px 36px; text-align:center;
    top:0; transition:all .6s ease-in-out;
}
.toggle-panel h1 { font-size:28px; font-weight:800; margin-bottom:12px; letter-spacing:-.5px; }
.toggle-panel p  { font-size:14px; opacity:.85; line-height:1.6; margin-bottom:28px; }

.toggle-left  { transform:translateX(-200%); }
.container.active .toggle-left { transform:translateX(0); }
.toggle-right { right:0; transform:translateX(0); }
.container.active .toggle-right { transform:translateX(200%); }

.btn-toggle {
    background:transparent; color:#fff;
    border:2px solid rgba(255,255,255,.7); border-radius:50px;
    padding:11px 36px; font-size:14px; font-weight:700;
    cursor:pointer; letter-spacing:.5px; transition:all .25s;
    width:200px; margin:6px 0;
}
.btn-toggle:hover { background:rgba(255,255,255,.15); border-color:#fff; transform:translateY(-2px); }

.toggle-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.15); border-radius:50px;
    padding:6px 16px; font-size:12px; font-weight:600;
    margin-bottom:20px; backdrop-filter:blur(10px);
}

/* ── Champs conditionnels ── */
.fields-candidat, .fields-recruteur { width:100%; }
</style>
</head>
<body>

<?php
$containerClass = 'container';
if ($active_panel === 'register') $containerClass .= ' active';
?>
<div class="<?= $containerClass ?>" id="container">

    <!-- ── CONNEXION ─────────────────────────────── -->
    <div class="form-container sign-in">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">
            <h1>Bon retour !</h1>
            <p class="subtitle">Connectez-vous à votre espace</p>
            <?php if ($error_login): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error_login) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <div class="input-wrap">
                <input type="text" name="email" id="login-email" placeholder="Adresse email"
                       value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'login' ? ($_POST['email'] ?? '') : '') ?>">
            </div>
            <span class="field-error" id="err-login-email"></span>
            <div class="input-wrap" style="position:relative;">
                <input type="password" name="password" id="login-password" placeholder="Mot de passe" style="padding-right:40px;">
                <img src="../../nonn.png" onclick="togglePwd('login-password', this)"
                     style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;width:20px;height:20px;object-fit:contain;"
                     data-show="../../ouii.png" data-hide="../../nonn.png">
            </div>
            <span class="field-error" id="err-login-password"></span>
            <a href="#">Mot de passe oublié ?</a>
            <button type="submit" class="btn-main" id="btn-login">Se connecter</button>
        </form>
    </div>

    <!-- ── INSCRIPTION UNIFIÉE ───────────────────── -->
>>>>>>> Stashed changes
    <div class="form-container sign-up">
        <form method="POST" action="login.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register_unified">
            <h1>Créer un compte</h1>
<<<<<<< Updated upstream
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
=======
            <p class="subtitle">Rejoignez la communauté Takwini</p>

            <?php if ($error_register && $active_panel === 'register'): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error_register) ?></div>
            <?php endif; ?>

            <!-- Sélecteur de rôle -->
            <div class="section-label">Je suis…</div>
            <div class="role-selector">
                <label>
                    <input type="radio" name="role" value="candidat" id="role-candidat"
                           <?= (($_POST['role'] ?? 'candidat') === 'candidat') ? 'checked' : '' ?>
                           onchange="switchRole('candidat')">
                    👤 Candidat
                </label>
                <label>
                    <input type="radio" name="role" value="recruteur" id="role-recruteur"
                           <?= (($_POST['role'] ?? '') === 'recruteur') ? 'checked' : '' ?>
                           onchange="switchRole('recruteur')">
                    🏢 Recruteur
                </label>
            </div>

            <!-- Champs communs -->
            <div class="form-row">
                <div>
                    <div class="input-wrap"><input type="text" name="nom" id="reg-nom" placeholder="Nom *"
                        value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-reg-nom"></span>
                </div>
                <div>
                    <div class="input-wrap"><input type="text" name="prenom" id="reg-prenom" placeholder="Prénom *"
                        value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-reg-prenom"></span>
                </div>
            </div>
            <div class="input-wrap">
                <input type="text" name="email" id="reg-email" placeholder="Email *"
                       value="<?= htmlspecialchars(in_array($_POST['action'] ?? '', ['register_unified']) ? ($_POST['email'] ?? '') : '') ?>">
            </div>
            <span class="field-error" id="err-reg-email"></span>
            <div class="input-wrap" style="position:relative;">
                <input type="password" name="password" id="reg-password" placeholder="Mot de passe (min. 6 car.) *" style="padding-right:40px;">
                <img src="../../nonn.png" onclick="togglePwd('reg-password', this)"
                     style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;width:20px;height:20px;object-fit:contain;"
                     data-show="../../ouii.png" data-hide="../../nonn.png">
            </div>
            <span class="field-error" id="err-reg-password"></span>
            <div class="input-wrap">
                <input type="text" name="telephone" id="reg-telephone" placeholder="Téléphone"
                       value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>
            <span class="field-error" id="err-reg-telephone"></span>

            <!-- ── Champs Candidat ── -->
            <div class="fields-candidat" id="fields-candidat"
                 style="display:<?= (($_POST['role'] ?? 'candidat') === 'candidat') ? 'block' : 'none' ?>;">
                <div class="form-row">
                    <div>
                        <div class="input-wrap">
                            <input type="date" name="date_naissance" id="reg-date"
                                   value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                        </div>
                    </div>
                    <div>
                        <div class="radio-group">
                            <label><input type="radio" name="sexe" value="homme"
                                <?= (($_POST['sexe'] ?? '') === 'homme') ? 'checked' : '' ?>> Homme</label>
                            <label><input type="radio" name="sexe" value="femme"
                                <?= (($_POST['sexe'] ?? '') === 'femme') ? 'checked' : '' ?>> Femme</label>
                        </div>
                        <span class="field-error" id="err-reg-sexe"></span>
                    </div>
                </div>
                <div class="input-wrap">
                    <input type="text" name="adresse" id="reg-adresse" placeholder="Adresse"
                           value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                </div>
                <label class="check-wrap">
                    <input type="checkbox" name="handicap" id="reg-handicap" value="1"
                           <?= !empty($_POST['handicap']) ? 'checked' : '' ?>
                           onchange="toggleHandicap(this)">
                    <span>Je suis en situation de handicap</span>
                </label>
                <div id="handicap-desc-wrap" style="width:100%;display:<?= !empty($_POST['handicap']) ? 'block' : 'none' ?>;">
                    <div class="input-wrap">
                        <input type="text" name="type_handicap" id="reg-type-handicap"
                               placeholder="Type de handicap (moteur, visuel...)"
                               value="<?= htmlspecialchars($_POST['type_handicap'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- ── Champs Recruteur ── -->
            <div class="fields-recruteur" id="fields-recruteur"
                 style="display:<?= (($_POST['role'] ?? '') === 'recruteur') ? 'block' : 'none' ?>;">
                <div class="section-label">Informations entreprise</div>
                <div class="input-wrap">
                    <input type="text" name="entreprise" id="rec-entreprise" placeholder="Nom de l'entreprise *"
                           value="<?= htmlspecialchars($_POST['entreprise'] ?? '') ?>">
                </div>
                <span class="field-error" id="err-rec-entreprise"></span>
                <div class="form-row">
                    <div>
                        <div class="input-wrap" style="position:relative;">
                            <input type="text" name="matricule_fiscal" id="rec-matricule"
                                   placeholder="Ex: 1182431M/A/M/000"
                                   value="<?= htmlspecialchars($_POST['matricule_fiscal'] ?? '') ?>"
                                   oninput="validerMatricule(this)"
                                   maxlength="14"
                                   style="padding-right:38px;text-transform:uppercase;letter-spacing:1px;">
                            <span id="matricule-icon" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:16px;"></span>
                        </div>
                        <span class="field-error" id="err-rec-matricule"></span>
                        <div id="matricule-badge" style="display:none;margin-top:5px;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:600;"></div>
                        <div style="font-size:10px;color:#aaa;margin-top:3px;">Ex: <em>1182431M/A/M/000</em> &nbsp;·&nbsp; <em>868024/D/A/M/000</em></div>
                    </div>
                    <div>
                        <div class="input-wrap">
                            <input type="text" name="secteur" placeholder="Secteur d'activité"
                                   value="<?= htmlspecialchars($_POST['secteur'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div style="width:100%;margin:5px 0;">
                    <label style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px;">Document officiel (PDF/Image)</label>
                    <input type="file" name="document_entreprise" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="recruteur-note">
                    ⏳ Votre compte sera activé après validation par un administrateur (sous 48h).
                </div>
            </div>

            <button type="submit" class="btn-main" id="btn-register">S'inscrire</button>
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
                <h1>Bonjour !</h1>
                <p>Inscrivez-vous avec vos informations personnelles pour commencer</p>
                <button class="hidden" id="registerBtn">S'inscrire</button>
=======
                <div class="toggle-badge">✨ Nouveau ici ?</div>
                <h1>Rejoignez-nous !</h1>
                <p>Créez votre compte et accédez aux formations, offres d'emploi et bien plus</p>
                <button class="btn-toggle" id="registerBtn">S'inscrire</button>
>>>>>>> Stashed changes
            </div>
        </div>
    </div>
</div>

<script>
<<<<<<< Updated upstream
    const container   = document.getElementById('container');
    const registerBtn = document.getElementById('registerBtn');
    const loginBtn    = document.getElementById('loginBtn');

    registerBtn.addEventListener('click', () => container.classList.add('active'));
    loginBtn.addEventListener('click',    () => container.classList.remove('active'));

    // Ouvrir automatiquement le bon panneau selon la réponse PHP
    <?php if ($active_panel === 'register'): ?>
    container.classList.add('active');
    <?php endif; ?>
=======
const container   = document.getElementById('container');
const registerBtn = document.getElementById('registerBtn');
const loginBtn    = document.getElementById('loginBtn');

registerBtn.addEventListener('click', function() {
    container.classList.add('active');
});
loginBtn.addEventListener('click', function() {
    container.classList.remove('active');
});

// Basculer les champs selon le rôle sélectionné
function switchRole(role) {
    const candidatFields  = document.getElementById('fields-candidat');
    const recruteurFields = document.getElementById('fields-recruteur');
    if (role === 'candidat') {
        candidatFields.style.display  = 'block';
        recruteurFields.style.display = 'none';
    } else {
        candidatFields.style.display  = 'none';
        recruteurFields.style.display = 'block';
    }
}

// Validation JS
function setError(el, span, msg) { el.classList.add('input-error'); span.textContent = msg; }
function clearErr(el, span) { el.classList.remove('input-error'); span.textContent = ''; }
function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

function toggleHandicap(cb) {
    document.getElementById('handicap-desc-wrap').style.display = cb.checked ? 'block' : 'none';
    if (!cb.checked) { var i = document.getElementById('reg-type-handicap'); if(i) i.value=''; }
}

// Login validation
document.getElementById('btn-login').addEventListener('click', function(e) {
    const email = document.getElementById('login-email');
    const pwd   = document.getElementById('login-password');
    const eE    = document.getElementById('err-login-email');
    const eP    = document.getElementById('err-login-password');
    let ok = true;
    if (!email.value.trim())        { setError(email, eE, "L'email est obligatoire."); ok=false; }
    else if (!isEmail(email.value)) { setError(email, eE, "Format invalide.");         ok=false; }
    else clearErr(email, eE);
    if (!pwd.value)              { setError(pwd, eP, 'Le mot de passe est obligatoire.'); ok=false; }
    else if (pwd.value.length<6) { setError(pwd, eP, 'Minimum 6 caractères.');           ok=false; }
    else clearErr(pwd, eP);
    if (!ok) e.preventDefault();
});

// Inscription unifiée validation
document.getElementById('btn-register').addEventListener('click', function(e) {
    const role   = document.querySelector('input[name="role"]:checked')?.value || 'candidat';
    const nom    = document.getElementById('reg-nom');
    const prenom = document.getElementById('reg-prenom');
    const email  = document.getElementById('reg-email');
    const pwd    = document.getElementById('reg-password');
    const tel    = document.getElementById('reg-telephone');
    let ok = true;

    // Champs communs
    if (!nom.value.trim() || nom.value.trim().length < 2) {
        setError(nom, document.getElementById('err-reg-nom'), nom.value.trim() ? 'Minimum 2 caractères.' : 'Nom obligatoire.');
        ok = false;
    } else clearErr(nom, document.getElementById('err-reg-nom'));

    if (!prenom.value.trim()) {
        setError(prenom, document.getElementById('err-reg-prenom'), 'Prénom obligatoire.');
        ok = false;
    } else clearErr(prenom, document.getElementById('err-reg-prenom'));

    if (!email.value.trim()) {
        setError(email, document.getElementById('err-reg-email'), 'Email obligatoire.');
        ok = false;
    } else if (!isEmail(email.value)) {
        setError(email, document.getElementById('err-reg-email'), 'Format invalide.');
        ok = false;
    } else clearErr(email, document.getElementById('err-reg-email'));

    if (!pwd.value) {
        setError(pwd, document.getElementById('err-reg-password'), 'Mot de passe obligatoire.');
        ok = false;
    } else if (pwd.value.length < 6) {
        setError(pwd, document.getElementById('err-reg-password'), 'Minimum 6 caractères.');
        ok = false;
    } else clearErr(pwd, document.getElementById('err-reg-password'));

    if (tel.value.trim() && !/^[0-9\s\+\-]{6,15}$/.test(tel.value.trim())) {
        setError(tel, document.getElementById('err-reg-telephone'), 'Numéro invalide.');
        ok = false;
    } else clearErr(tel, document.getElementById('err-reg-telephone'));

    // Champs spécifiques candidat
    if (role === 'candidat') {
        if (!document.querySelector('input[name="sexe"]:checked')) {
            document.getElementById('err-reg-sexe').textContent = 'Choisissez un genre.';
            ok = false;
        } else {
            document.getElementById('err-reg-sexe').textContent = '';
        }
    }

    // Champs spécifiques recruteur
    if (role === 'recruteur') {
        const entreprise = document.getElementById('rec-entreprise');
        const matricule  = document.getElementById('rec-matricule');

        if (!entreprise.value.trim()) {
            setError(entreprise, document.getElementById('err-rec-entreprise'), "Nom d'entreprise obligatoire.");
            ok = false;
        } else clearErr(entreprise, document.getElementById('err-rec-entreprise'));

        if (!matricule.value.trim()) {
            setError(matricule, document.getElementById('err-rec-matricule'), 'Matricule fiscal obligatoire.');
            ok = false;
        } else if (!/^\d{6,8}[A-Z]?\s*(\/[A-Z]+){1,3}\/\d{3}$|^\d{6,8}\s+([A-Z]\s+){1,3}\d{3}$/.test(matricule.value.trim().toUpperCase())) {
            setError(matricule, document.getElementById('err-rec-matricule'), 'Format invalide — ex: 1234567A/P/000');
            ok = false;
        } else clearErr(matricule, document.getElementById('err-rec-matricule'));
    }

    if (!ok) e.preventDefault();
});

// Clear on input
document.querySelectorAll('.form-container input').forEach(function(el) {
    el.addEventListener('input', function() {
        el.classList.remove('input-error');
        const errId = 'err-' + el.id;
        const span  = document.getElementById(errId);
        if (span) span.textContent = '';
    });
});

// Validation matricule fiscal tunisien
function validerMatricule(input) {
    const val    = input.value.toUpperCase().trim();
    input.value  = val;
    const badge   = document.getElementById('matricule-badge');
    const icon    = document.getElementById('matricule-icon');
    const errSpan = document.getElementById('err-rec-matricule');
    const normalized = val.replace(/\s+/g, '');
    const regex = /^\d{6,8}[A-Z]?\s*(\/[A-Z]+){1,3}\/\d{3}$|^\d{6,8}\s+([A-Z]\s+){1,3}\d{3}$/;

    if (val.length === 0) {
        badge.style.display = 'none';
        icon.textContent = '';
        input.classList.remove('input-error');
        errSpan.textContent = '';
        return;
    }

    if (regex.test(val) || regex.test(normalized)) {
        badge.style.display    = 'block';
        badge.style.background = '#e8f5e9';
        badge.style.color      = '#2e7d32';
        badge.style.border     = '1px solid #c8e6c9';
        badge.innerHTML = '✅ Format valide — en attente de vérification admin';
        icon.textContent = '✅';
        input.classList.remove('input-error');
        errSpan.textContent = '';
    } else {
        badge.style.display    = 'block';
        badge.style.background = '#fde8e8';
        badge.style.color      = '#c0392b';
        badge.style.border     = '1px solid #f5c6c6';
        badge.innerHTML = '❌ Format invalide — ex: <strong>1234567A/P/000</strong> ou <strong>868024/D/A/M/000</strong>';
        icon.textContent = '❌';
        input.classList.add('input-error');
    }
}

function togglePwd(inputId, icon) {
    const input = document.getElementById(inputId);
    icon.style.transition = 'transform 0.15s ease, opacity 0.15s ease';
    icon.style.transform  = 'translateY(-50%) scale(0.7)';
    icon.style.opacity    = '0.4';
    setTimeout(() => {
        icon.style.transform = 'translateY(-50%) scale(1.15)';
        icon.style.opacity   = '1';
        setTimeout(() => { icon.style.transform = 'translateY(-50%) scale(1)'; }, 100);
    }, 120);
    if (input.type === 'password') {
        input.type = 'text';
        icon.src   = icon.dataset.show;
    } else {
        input.type = 'password';
        icon.src   = icon.dataset.hide;
    }
}
>>>>>>> Stashed changes
</script>
</body>
</html>
