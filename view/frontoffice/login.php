<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

$error_login    = '';
$error_register = '';
$success        = '';
$active_panel   = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $controller = new UtilisateurController();

    if ($action === 'login') {
        $active_panel = 'login';
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) {
            $error_login = 'Email et mot de passe sont obligatoires.';
        } else {
            $result = $controller->login($email, $password);
            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                $success = $result['message'];
                if ($result['user']['role'] === 'admin') {
                    header('Location: ../../view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php');
                } else {
                    header('Location: formations/index.php');
                }
                exit;
            } else {
                $error_login = $result['message'];
                if ($result['action'] === 'not_found') {
                    $active_panel = 'register';
                }
            }
        }
    }

    elseif ($action === 'register') {
        $active_panel   = 'register';
        $nom            = trim($_POST['nom']            ?? '');
        $prenom         = trim($_POST['prenom']         ?? '');
        $email          = trim($_POST['email']          ?? '');
        $password       = $_POST['password']            ?? '';
        $telephone      = trim($_POST['telephone']      ?? '');
        $sexe           = $_POST['sexe']                ?? '';
        $date_naissance = $_POST['date_naissance']      ?? '';
        $adresse        = trim($_POST['adresse']        ?? '');

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error_register = 'Les champs nom, prénom, email et mot de passe sont obligatoires.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            $result = $controller->register($nom, $prenom, $email, $password, $telephone, $sexe, $date_naissance, $adresse);
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
<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

* { margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif; }

body {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh; padding: 20px;
}

.container {
    background-color: #fff;
    border-radius: 30px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    position: relative; overflow: hidden;
    width: 900px; max-width: 100%; min-height: 560px;
}
.container p  { font-size:13px; line-height:20px; letter-spacing:.3px; margin:15px 0; }
.container span { font-size:12px; }
.container a  { color:#333; font-size:13px; text-decoration:none; margin:10px 0 8px; }

.container button {
    background-color: #4caf50;
    color:#fff; font-size:12px;
    padding:10px 45px; border:1px solid transparent; border-radius:8px;
    font-weight:600; letter-spacing:.5px; text-transform:uppercase;
    margin-top:12px; cursor:pointer; transition: background .2s;
}
.container button:hover { background-color: #388e3c; }
.container button.hidden { background-color:transparent; border-color:#fff; }
.container button.hidden:hover { background-color:rgba(255,255,255,0.15); }

.container form {
    background-color:#fff; display:flex; align-items:center;
    justify-content:center; flex-direction:column;
    padding: 20px 30px; height:100%; overflow-y: auto;
}
.container h1 { font-size: 22px; color: #333; margin-bottom: 5px; }

.container input, .container select {
    background-color:#e8f5e9; border: 1px solid #c8e6c9; margin:4px 0;
    padding:9px 14px; font-size:13px; border-radius:8px;
    width:100%; outline:none; transition: background .2s;
}
.container input:focus, .container select:focus {
    background-color:#c8e6c9;
    box-shadow: 0 0 0 2px rgba(76,175,80,0.4);
}

.form-row { display: flex; gap: 10px; width: 100%; }
.form-row input, .form-row select { flex: 1; }

.form-container { position:absolute; top:0; height:100%; transition:all .6s ease-in-out; overflow-y: auto; }
.sign-in { left:0; width:50%; z-index:2; }
.container.active .sign-in { transform:translateX(100%); }
.sign-up { left:0; width:50%; opacity:0; z-index:1; }
.container.active .sign-up { transform:translateX(100%); opacity:1; z-index:5; animation:move .6s; }

@keyframes move {
    0%,49.99% { opacity:0; z-index:1; }
    50%,100%  { opacity:1; z-index:5; }
}

.social-icons { margin:12px 0 8px; }
.social-icons a {
    border:1px solid #ccc; border-radius:20%;
    display:inline-flex; justify-content:center; align-items:center;
    margin:0 3px; width:36px; height:36px; transition: border-color .2s;
}
.social-icons a:hover { border-color: #4caf50; color: #4caf50; }

/* ── Toggle panel ── */
.toggle-container {
    position:absolute; top:0; left:50%; width:50%; height:100%;
    overflow:hidden; transition:all .6s ease-in-out;
    border-radius:150px 0 0 100px; z-index:1000;
}
.container.active .toggle-container { transform:translateX(-100%); border-radius:0 150px 100px 0; }

.toggle {
    background: linear-gradient(135deg, #66bb6a 0%, #2e7d32 100%);
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
.toggle-panel .logo-icon { font-size:48px; margin-bottom:15px; }
.toggle-left  { transform:translateX(-200%); }
.container.active .toggle-left  { transform:translateX(0); }
.toggle-right { right:0; transform:translateX(0); }
.container.active .toggle-right { transform:translateX(200%); }

/* Alertes */
.alert { width:100%; padding:8px 12px; border-radius:8px; font-size:12px; margin:6px 0; text-align:center; }
.alert-danger  { background:#fde8e8; color:#c0392b; border-left: 3px solid #c0392b; }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left: 3px solid #4caf50; }

/* Erreurs champs JS */
.field-error { color:#c0392b; font-size:11px; margin:-3px 0 3px 2px; min-height:13px; display:block; }
.container input.input-error, .container select.input-error { border:1px solid #c0392b; background:#fff5f5; }

/* Accessibility badge */
.access-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #888; margin: 8px 0 4px; }
.access-badge i { color: #4caf50; }

/* Label genre */
.genre-label { font-size: 12px; color: #666; align-self: flex-start; margin: 4px 0 2px 2px; }
.radio-group { display:flex; gap:20px; width:100%; margin: 2px 0 6px; }
.radio-group label { display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer; }
.radio-group input[type=radio] { width:auto; background:none; margin:0; padding:0; accent-color: #4caf50; }
</style>
</head>
<body>

<div class="container <?= ($active_panel === 'register') ? 'active' : '' ?>" id="container">

    <!-- ── INSCRIPTION ─────────────────────────────── -->
    <div class="form-container sign-up">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="register">
            <h1>Créer un compte</h1>

            <?php if ($error_register): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_register) ?></div>
            <?php endif; ?>
            <?php if ($success && $active_panel === 'register'): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-row">
                <div style="flex:1">
                    <input type="text" name="nom" id="reg-nom" placeholder="Nom *"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    <span class="field-error" id="err-reg-nom"></span>
                </div>
                <div style="flex:1">
                    <input type="text" name="prenom" id="reg-prenom" placeholder="Prénom *"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    <span class="field-error" id="err-reg-prenom"></span>
                </div>
            </div>

            <input type="email" name="email" id="reg-email" placeholder="Email *"
                   value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'register' ? ($_POST['email'] ?? '') : '') ?>">
            <span class="field-error" id="err-reg-email"></span>

            <input type="password" name="password" id="reg-password" placeholder="Mot de passe (min. 6 car.) *">
            <span class="field-error" id="err-reg-password"></span>

            <input type="tel" name="telephone" id="reg-telephone" placeholder="Téléphone"
                   value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            <span class="field-error" id="err-reg-telephone"></span>

            <div class="form-row">
                <div style="flex:1">
                    <input type="date" name="date_naissance" id="reg-date"
                           value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>" title="Date de naissance">
                    <span class="field-error" id="err-reg-date"></span>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="sexe" value="homme" <?= (($_POST['sexe'] ?? '') === 'homme') ? 'checked' : '' ?>>
                            Homme
                        </label>
                        <label>
                            <input type="radio" name="sexe" value="femme" <?= (($_POST['sexe'] ?? '') === 'femme') ? 'checked' : '' ?>>
                            Femme
                        </label>
                    </div>
                    <span class="field-error" id="err-reg-sexe"></span>
                </div>
            </div>

            <input type="text" name="adresse" id="reg-adresse" placeholder="Adresse"
                   value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
            <span class="field-error" id="err-reg-adresse"></span>

            <button type="submit" id="btn-register">S'inscrire</button>
        </form>
    </div>

    <!-- ── CONNEXION ──────────────────────────────── -->
    <div class="form-container sign-in">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">
            <h1>Se connecter</h1>
            <span>ou utilisez votre email et mot de passe</span>

            <?php if ($error_login): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_login) ?></div>
            <?php endif; ?>
            <?php if ($success && $active_panel === 'login'): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <input type="email" name="email" id="login-email" placeholder="Email"
                   value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'login' ? ($_POST['email'] ?? '') : '') ?>">
            <span class="field-error" id="err-login-email"></span>

            <input type="password" name="password" id="login-password" placeholder="Mot de passe">
            <span class="field-error" id="err-login-password"></span>

            <a href="#">Mot de passe oublié ?</a>
            <button type="submit" id="btn-login">Se connecter</button>
        </form>
    </div>

    <!-- ── TOGGLE ─────────────────────────────────── -->
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Bon retour !</h1>
                <p>Entrez vos identifiants pour accéder à toutes les fonctionnalités du site</p>
                <button class="hidden" id="loginBtn">Se connecter</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Rejoignez-nous !</h1>
                <p>Inscrivez-vous pour accéder aux formations, offres d'emploi et bien plus encore</p>
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
</script>

<script>
function setError(inputEl, spanEl, msg) { inputEl.classList.add('input-error'); spanEl.textContent = msg; }
function clearError(inputEl, spanEl) { inputEl.classList.remove('input-error'); spanEl.textContent = ''; }
function isValidEmail(val) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val); }

document.getElementById('btn-register').addEventListener('click', function(e) {
    const nom      = document.getElementById('reg-nom');
    const prenom   = document.getElementById('reg-prenom');
    const email    = document.getElementById('reg-email');
    const password = document.getElementById('reg-password');
    const tel      = document.getElementById('reg-telephone');
    const errNom    = document.getElementById('err-reg-nom');
    const errPrenom = document.getElementById('err-reg-prenom');
    const errEmail  = document.getElementById('err-reg-email');
    const errPwd    = document.getElementById('err-reg-password');
    const errTel    = document.getElementById('err-reg-telephone');
    const errSexe   = document.getElementById('err-reg-sexe');
    let valid = true;

    if (nom.value.trim() === '') { setError(nom, errNom, 'Le nom est obligatoire.'); valid = false; }
    else if (nom.value.trim().length < 2) { setError(nom, errNom, 'Minimum 2 caractères.'); valid = false; }
    else { clearError(nom, errNom); }

    if (prenom.value.trim() === '') { setError(prenom, errPrenom, 'Le prénom est obligatoire.'); valid = false; }
    else { clearError(prenom, errPrenom); }

    if (email.value.trim() === '') { setError(email, errEmail, "L'email est obligatoire."); valid = false; }
    else if (!isValidEmail(email.value.trim())) { setError(email, errEmail, "Format invalide."); valid = false; }
    else { clearError(email, errEmail); }

    if (password.value === '') { setError(password, errPwd, 'Le mot de passe est obligatoire.'); valid = false; }
    else if (password.value.length < 6) { setError(password, errPwd, 'Minimum 6 caractères.'); valid = false; }
    else { clearError(password, errPwd); }

    if (tel.value.trim() !== '' && !/^[0-9\s\+\-]{6,15}$/.test(tel.value.trim())) { setError(tel, errTel, 'Numéro invalide.'); valid = false; }
    else { clearError(tel, errTel); }

    if (!document.querySelector('input[name="sexe"]:checked')) { errSexe.textContent = 'Veuillez choisir un genre.'; valid = false; }
    else { errSexe.textContent = ''; }

    if (!valid) e.preventDefault();
});

document.getElementById('btn-login').addEventListener('click', function(e) {
    const email    = document.getElementById('login-email');
    const password = document.getElementById('login-password');
    const errEmail = document.getElementById('err-login-email');
    const errPwd   = document.getElementById('err-login-password');
    let valid = true;

    if (email.value.trim() === '') { setError(email, errEmail, "L'email est obligatoire."); valid = false; }
    else if (!isValidEmail(email.value.trim())) { setError(email, errEmail, "Format invalide."); valid = false; }
    else { clearError(email, errEmail); }

    if (password.value === '') { setError(password, errPwd, 'Le mot de passe est obligatoire.'); valid = false; }
    else if (password.value.length < 6) { setError(password, errPwd, 'Minimum 6 caractères.'); valid = false; }
    else { clearError(password, errPwd); }

    if (!valid) e.preventDefault();
});

['reg-nom','reg-prenom','reg-email','reg-password','reg-telephone','reg-date','login-email','login-password'].forEach(function(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function() {
        const errEl = document.getElementById('err-' + id);
        if (errEl) clearError(el, errEl);
    });
});
</script>
</body>
</html>
