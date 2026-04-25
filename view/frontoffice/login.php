<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

$error_login    = '';
$error_register = '';
$success        = '';
$active_panel   = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $controller = new UtilisateurController();

    if ($action === 'login') {
        $active_panel = 'login';
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        if (empty($email) || empty($password)) {
            $error_login = 'Email et mot de passe sont obligatoires.';
        } else {
            $result = $controller->login($email, $password);
            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                if ($result['user']['role'] === 'admin') {
                    header('Location: ../../view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php');
                } else {
                    header('Location: formations/index.php');
                }
                exit;
            } else {
                $error_login  = $result['message'];
                if ($result['action'] === 'not_found') $active_panel = 'register';
            }
        }

    } elseif ($action === 'register') {
        $active_panel   = 'register';
        $nom            = trim($_POST['nom']            ?? '');
        $prenom         = trim($_POST['prenom']         ?? '');
        $email          = trim($_POST['email']          ?? '');
        $password       = $_POST['password']            ?? '';
        $telephone      = trim($_POST['telephone']      ?? '');
        $sexe           = $_POST['sexe']                ?? '';
        $date_naissance = $_POST['date_naissance']      ?? '';
        $adresse        = trim($_POST['adresse']        ?? '');
        $handicap       = !empty($_POST['handicap']) ? 1 : 0;
        $type_handicap  = $handicap ? trim($_POST['type_handicap'] ?? '') : null;

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error_register = 'Les champs nom, prénom, email et mot de passe sont obligatoires.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            $result = $controller->register($nom, $prenom, $email, $password, $telephone, $sexe, $date_naissance, $adresse, $handicap, $type_handicap);
            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                header('Location: formations/index.php');
                exit;
            } else {
                $error_register = $result['message'];
                if ($result['action'] === 'already_exists') {
                    $active_panel = 'login';
                    $error_login  = $result['message'];
                    $error_register = '';
                }
            }
        }

    } elseif ($action === 'register_recruteur') {
        $active_panel = 'recruteur';
        $nom          = trim($_POST['nom']              ?? '');
        $prenom       = trim($_POST['prenom']           ?? '');
        $email        = trim($_POST['email']            ?? '');
        $password     = $_POST['password']              ?? '';
        $telephone    = trim($_POST['telephone']        ?? '');
        $entreprise   = trim($_POST['entreprise']       ?? '');
        $matricule    = trim($_POST['matricule_fiscal'] ?? '');
        $secteur      = trim($_POST['secteur']          ?? '');

        if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($entreprise) || empty($matricule)) {
            $error_register = 'Tous les champs obligatoires (*) doivent être remplis.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caractères.';
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
                    $stmt = $db->prepare('INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, role, statut, entreprise, matricule_fiscal, secteur, document_entreprise) VALUES (:nom, :prenom, :email, :mdp, :tel, :role, :statut, :entreprise, :matricule, :secteur, :doc)');
                    $stmt->execute([
                        'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                        'mdp' => $hashed, 'tel' => $telephone ?: null,
                        'role' => 'recruteur', 'statut' => 'en_attente',
                        'entreprise' => $entreprise, 'matricule' => $matricule,
                        'secteur' => $secteur ?: null, 'doc' => $docPath,
                    ]);
                    // Notification admin
                    $db->prepare('INSERT INTO notifications (titre, message, type, lien) VALUES (:titre, :message, :type, :lien)')
                       ->execute([
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }

body {
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
    display:flex; align-items:center; justify-content:center;
    min-height:100vh; padding:20px;
    position:relative; overflow:hidden;
}
body::before { display:none; }
body::after  { display:none; }

.container {
    background:#fff;
    border-radius:30px;
    box-shadow:0 8px 32px rgba(0,0,0,.12);
    position:relative; overflow:hidden;
    width:1100px; max-width:100%; min-height:700px;
    display:flex;
}

/* ── Formulaires ── */
.form-container {
    position:absolute; top:0; left:0; width:50%; height:100%;
    overflow-y:auto; transition:transform .6s ease, opacity .6s ease;
}
.sign-in       { z-index:2; transform:translateX(0);    opacity:1; }
.sign-up       { z-index:1; transform:translateX(-100%); opacity:0; pointer-events:none; }
.recruteur-panel { z-index:1; transform:translateX(-100%); opacity:0; pointer-events:none; }

/* Quand candidat actif */
.container.active .sign-in   { transform:translateX(100%); opacity:0; pointer-events:none; z-index:1; }
.container.active .sign-up   { transform:translateX(100%); opacity:1; pointer-events:all;  z-index:5; }

/* Quand recruteur actif */
.container.recruteur .sign-in         { transform:translateX(100%); opacity:0; pointer-events:none; z-index:1; }
.container.recruteur .recruteur-panel { transform:translateX(100%); opacity:1; pointer-events:all;  z-index:5; }

@keyframes move { 0%,49.99%{opacity:0;z-index:1;} 50%,100%{opacity:1;z-index:5;} }

.form-container form {
    background:transparent; display:flex; align-items:center;
    justify-content:center; flex-direction:column;
    padding:20px 40px 30px; min-height:100%;
}
.form-container h1 {
    font-size:24px; font-weight:800; color:#1a1a2e;
    margin-bottom:2px; letter-spacing:-.5px;
}
.form-container .subtitle {
    font-size:12px; color:#888; margin-bottom:14px; text-align:center;
}

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

/* Inputs sans icône (file, checkbox, radio) */
.container input[type=file] {
    width:100%; padding:10px 14px; background:#e8f5e9;
    border:1px dashed #c8e6c9; border-radius:10px;
    font-size:13px; outline:none; cursor:pointer; margin:5px 0;
}
.container input[type=file]:hover { border-color:#4caf50; background:#d4edda; }

/* ── Bouton principal ── */
.btn-main {
    width:100%; padding:14px; margin-top:14px;
    background:linear-gradient(135deg,#4caf50,#2e7d32);
    color:#fff; border:none; border-radius:12px;
    font-size:15px; font-weight:700; cursor:pointer;
    letter-spacing:.3px; transition:all .25s;
    box-shadow:0 4px 20px rgba(76,175,80,.35);
}
.btn-main:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(76,175,80,.45); }
.btn-main:active { transform:translateY(0); }

.container a { color:#4caf50; font-size:13px; text-decoration:none; margin:10px 0 4px; font-weight:500; }
.container a:hover { text-decoration:underline; }

.form-row { display:flex; gap:10px; width:100%; }
.form-row > div { flex:1; }

/* ── Radio ── */
.radio-group { display:flex; gap:10px; width:100%; margin:4px 0; }
.radio-group label {
    display:flex; align-items:center; gap:8px;
    font-size:13px; cursor:pointer; font-weight:500; color:#555;
    background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px;
    padding:9px 14px; flex:1; transition:all .2s;
}
.radio-group label:has(input:checked) { border-color:#4caf50; background:#d4edda; color:#2e7d32; }
.radio-group input[type=radio] { accent-color:#4caf50; }

/* ── Checkbox handicap ── */
.check-wrap {
    width:100%; display:flex; align-items:center; gap:10px;
    background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px;
    padding:10px 14px; margin:5px 0; cursor:pointer; transition:all .2s;
}
.check-wrap:has(input:checked) { border-color:#4caf50; background:#d4edda; }
.check-wrap input[type=checkbox] { width:16px; height:16px; accent-color:#4caf50; cursor:pointer; }
.check-wrap span { font-size:13px; font-weight:500; color:#555; }

/* ── Erreurs ── */
.field-error { color:#e53935; font-size:11px; margin:0 0 2px 4px; min-height:12px; display:block; line-height:1.2; }
.input-error input { border-color:#e53935 !important; background:#fff5f5 !important; }

/* ── Alertes ── */
.alert { width:100%; padding:10px 14px; border-radius:10px; font-size:13px; margin:6px 0; display:flex; align-items:center; gap:8px; }
.alert-danger  { background:#fde8e8; color:#c0392b; border-left:3px solid #e53935; }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left:3px solid #4caf50; }

/* ── Section label ── */
.section-label {
    width:100%; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:1px; color:#aaa; margin:10px 0 4px;
}

/* ── Toggle ── */
.toggle-container {
    position:absolute; top:0; left:50%; width:50%; height:100%;
    overflow:hidden; transition:all .6s cubic-bezier(.68,-.55,.27,1.55);
    border-radius:120px 0 0 120px; z-index:1000;
}
.container.active .toggle-container,
.container.recruteur .toggle-container { transform:translateX(-100%); border-radius:0 120px 120px 0; }

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
.container.active .toggle,
.container.recruteur .toggle { transform:translateX(50%); }

.toggle-panel {
    position:absolute; width:50%; height:100%;
    display:flex; align-items:center; justify-content:center;
    flex-direction:column; padding:40px 36px; text-align:center;
    top:0; transition:all .6s ease-in-out;
}
.toggle-panel h1 { font-size:28px; font-weight:800; margin-bottom:12px; letter-spacing:-.5px; }
.toggle-panel p  { font-size:14px; opacity:.85; line-height:1.6; margin-bottom:28px; }

.toggle-left  { transform:translateX(-200%); }
.container.active .toggle-left,
.container.recruteur .toggle-left { transform:translateX(0); }
.toggle-right { right:0; transform:translateX(0); }
.container.active .toggle-right,
.container.recruteur .toggle-right { transform:translateX(200%); }

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
</style>
</head>
<body>

<?php
$containerClass = 'container';
if ($active_panel === 'register')  $containerClass .= ' active';
if ($active_panel === 'recruteur') $containerClass .= ' recruteur';
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
                <input type="text" name="email" id="login-email" placeholder="Adresse email" value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'login' ? ($_POST['email'] ?? '') : '') ?>">
                
            </div>
            <span class="field-error" id="err-login-email"></span>
            <div class="input-wrap">
                <input type="password" name="password" id="login-password" placeholder="Mot de passe">
                
            </div>
            <span class="field-error" id="err-login-password"></span>
            <a href="#">Mot de passe oublié ?</a>
            <button type="submit" class="btn-main" id="btn-login">Se connecter</button>
        </form>
    </div>

    <!-- ── CANDIDAT ─────────────────────────────── -->
    <div class="form-container sign-up">
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="register">
            <h1>Créer un compte</h1>
            <p class="subtitle">Rejoignez la communauté Takwini</p>
            <?php if ($error_register && $active_panel === 'register'): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error_register) ?></div>
            <?php endif; ?>
            <div class="form-row">
                <div>
                    <div class="input-wrap"><input type="text" name="nom" id="reg-nom" placeholder="Nom *" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-reg-nom"></span>
                </div>
                <div>
                    <div class="input-wrap"><input type="text" name="prenom" id="reg-prenom" placeholder="Prénom *" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-reg-prenom"></span>
                </div>
            </div>
            <div class="input-wrap"><input type="text" name="email" id="reg-email" placeholder="Email *" value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'register' ? ($_POST['email'] ?? '') : '') ?>"></div>
            <span class="field-error" id="err-reg-email"></span>
            <div class="input-wrap"><input type="password" name="password" id="reg-password" placeholder="Mot de passe (min. 6 car.) *"></div>
            <span class="field-error" id="err-reg-password"></span>
            <div class="input-wrap"><input type="text" name="telephone" id="reg-telephone" placeholder="Téléphone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"></div>
            <span class="field-error" id="err-reg-telephone"></span>
            <div class="form-row">
                <div>
                    <div class="input-wrap"><input type="date" name="date_naissance" id="reg-date" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"></div>
                </div>
                <div>
                    <div class="radio-group">
                        <label><input type="radio" name="sexe" value="homme" <?= (($_POST['sexe'] ?? '') === 'homme') ? 'checked' : '' ?>> Homme</label>
                        <label><input type="radio" name="sexe" value="femme" <?= (($_POST['sexe'] ?? '') === 'femme') ? 'checked' : '' ?>> Femme</label>
                    </div>
                    <span class="field-error" id="err-reg-sexe"></span>
                </div>
            </div>
            <div class="input-wrap"><input type="text" name="adresse" id="reg-adresse" placeholder="Adresse" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"></div>
            <label class="check-wrap">
                <input type="checkbox" name="handicap" id="reg-handicap" value="1" <?= !empty($_POST['handicap']) ? 'checked' : '' ?> onchange="toggleHandicap(this)">
                <span>Je suis en situation de handicap</span>
            </label>
            <div id="handicap-desc-wrap" style="width:100%;display:<?= !empty($_POST['handicap']) ? 'block' : 'none' ?>;">
                <div class="input-wrap"><input type="text" name="type_handicap" id="reg-type-handicap" placeholder="Type de handicap (moteur, visuel...)" value="<?= htmlspecialchars($_POST['type_handicap'] ?? '') ?>"></div>
            </div>
            <button type="submit" class="btn-main" id="btn-register">Créer mon compte</button>
        </form>
    </div>

    <!-- ── RECRUTEUR ─────────────────────────────── -->
    <div class="form-container recruteur-panel">
        <form method="POST" action="login.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register_recruteur">
            <h1>Espace Recruteur</h1>
            <p class="subtitle">Publiez vos offres et trouvez vos talents</p>
            <?php if ($error_register && $active_panel === 'recruteur'): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error_register) ?></div>
            <?php endif; ?>
            <div class="form-row">
                <div>
                    <div class="input-wrap"><input type="text" name="nom" id="rec-nom" placeholder="Nom *" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-rec-nom"></span>
                </div>
                <div>
                    <div class="input-wrap"><input type="text" name="prenom" id="rec-prenom" placeholder="Prénom *" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"></div>
                    <span class="field-error" id="err-rec-prenom"></span>
                </div>
            </div>
            <div class="input-wrap"><input type="text" name="email" id="rec-email" placeholder="Email professionnel *" value="<?= htmlspecialchars(($_POST['action'] ?? '') === 'register_recruteur' ? ($_POST['email'] ?? '') : '') ?>"></div>
            <span class="field-error" id="err-rec-email"></span>
            <div class="input-wrap"><input type="password" name="password" id="rec-password" placeholder="Mot de passe (min. 6 car.) *"></div>
            <span class="field-error" id="err-rec-password"></span>
            <div class="input-wrap"><input type="text" name="telephone" placeholder="Téléphone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"></div>
            <div class="section-label">Informations entreprise</div>
            <div class="input-wrap"><input type="text" name="entreprise" id="rec-entreprise" placeholder="Nom de l'entreprise *" value="<?= htmlspecialchars($_POST['entreprise'] ?? '') ?>"></div>
            <span class="field-error" id="err-rec-entreprise"></span>
            <div class="form-row">
                <div>
                    <div class="input-wrap"><input type="text" name="matricule_fiscal" id="rec-matricule" placeholder="Matricule fiscal *" value="<?= htmlspecialchars($_POST['matricule_fiscal'] ?? '') ?>"></div>
                    <span class="field-error" id="err-rec-matricule"></span>
                </div>
                <div>
                    <div class="input-wrap"><input type="text" name="secteur" placeholder="Secteur d'activité" value="<?= htmlspecialchars($_POST['secteur'] ?? '') ?>"></div>
                </div>
            </div>
            <div style="width:100%;margin:5px 0;">
                <label style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px;">Document officiel (PDF/Image)</label>
                <input type="file" name="document_entreprise" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <button type="submit" class="btn-main" id="btn-recruteur">Envoyer la demande</button>
        </form>
    </div>

    <!-- ── TOGGLE ─────────────────────────────────── -->
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <div class="toggle-badge">👋 Déjà membre ?</div>
                <h1>Bon retour !</h1>
                <p>Connectez-vous pour accéder à toutes les fonctionnalités de la plateforme</p>
                <button class="btn-toggle" id="loginBtn">Se connecter</button>
            </div>
            <div class="toggle-panel toggle-right">
                <div class="toggle-badge">✨ Nouveau ici ?</div>
                <h1>Rejoignez-nous !</h1>
                <p>Créez votre compte et accédez aux formations, offres d'emploi et bien plus</p>
                <button class="btn-toggle" id="registerBtn" style="margin-bottom:8px;">Je suis candidat</button>
                <button class="btn-toggle" id="recruteurBtn">Je suis recruteur</button>
            </div>
        </div>
    </div>

</div>

<script>
const container    = document.getElementById('container');
const registerBtn  = document.getElementById('registerBtn');
const recruteurBtn = document.getElementById('recruteurBtn');
const loginBtn     = document.getElementById('loginBtn');

registerBtn.addEventListener('click', function() {
    container.classList.remove('recruteur');
    container.classList.add('active');
});
recruteurBtn.addEventListener('click', function() {
    container.classList.remove('active');
    container.classList.add('recruteur');
});
loginBtn.addEventListener('click', function() {
    container.classList.remove('active');
    container.classList.remove('recruteur');
});

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

// Candidat validation
document.getElementById('btn-register').addEventListener('click', function(e) {
    const nom    = document.getElementById('reg-nom');
    const prenom = document.getElementById('reg-prenom');
    const email  = document.getElementById('reg-email');
    const pwd    = document.getElementById('reg-password');
    const tel    = document.getElementById('reg-telephone');
    let ok = true;
    if (!nom.value.trim() || nom.value.trim().length<2) { setError(nom, document.getElementById('err-reg-nom'), nom.value.trim()?'Minimum 2 caractères.':'Nom obligatoire.'); ok=false; } else clearErr(nom, document.getElementById('err-reg-nom'));
    if (!prenom.value.trim()) { setError(prenom, document.getElementById('err-reg-prenom'), 'Prénom obligatoire.'); ok=false; } else clearErr(prenom, document.getElementById('err-reg-prenom'));
    if (!email.value.trim())        { setError(email, document.getElementById('err-reg-email'), "Email obligatoire."); ok=false; }
    else if (!isEmail(email.value)) { setError(email, document.getElementById('err-reg-email'), "Format invalide.");   ok=false; }
    else clearErr(email, document.getElementById('err-reg-email'));
    if (!pwd.value)              { setError(pwd, document.getElementById('err-reg-password'), 'Mot de passe obligatoire.'); ok=false; }
    else if (pwd.value.length<6) { setError(pwd, document.getElementById('err-reg-password'), 'Minimum 6 caractères.');    ok=false; }
    else clearErr(pwd, document.getElementById('err-reg-password'));
    if (tel.value.trim() && !/^[0-9\s\+\-]{6,15}$/.test(tel.value.trim())) { setError(tel, document.getElementById('err-reg-telephone'), 'Numéro invalide.'); ok=false; } else clearErr(tel, document.getElementById('err-reg-telephone'));
    if (!document.querySelector('input[name="sexe"]:checked')) { document.getElementById('err-reg-sexe').textContent='Choisissez un genre.'; ok=false; } else document.getElementById('err-reg-sexe').textContent='';
    if (!ok) e.preventDefault();
});

// Recruteur validation
document.getElementById('btn-recruteur').addEventListener('click', function(e) {
    const fields = [
        ['rec-nom',        'err-rec-nom',        'Nom obligatoire.'],
        ['rec-prenom',     'err-rec-prenom',      'Prénom obligatoire.'],
        ['rec-email',      'err-rec-email',       'Email obligatoire.'],
        ['rec-password',   'err-rec-password',    'Mot de passe obligatoire.'],
        ['rec-entreprise', 'err-rec-entreprise',  "Nom d'entreprise obligatoire."],
        ['rec-matricule',  'err-rec-matricule',   'Matricule fiscal obligatoire.'],
    ];
    let ok = true;
    fields.forEach(function([id, errId, msg]) {
        const el   = document.getElementById(id);
        const span = document.getElementById(errId);
        if (!el || !span) return;
        if (!el.value.trim()) { setError(el, span, msg); ok=false; }
        else if (id==='rec-email' && !isEmail(el.value)) { setError(el, span, 'Format invalide.'); ok=false; }
        else if (id==='rec-password' && el.value.length<6) { setError(el, span, 'Minimum 6 caractères.'); ok=false; }
        else clearErr(el, span);
    });
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
</script>
</body>
</html>

