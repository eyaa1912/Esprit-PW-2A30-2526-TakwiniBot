<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/PasswordResetController.php';

$controller = new PasswordResetController();
$token      = $_GET['token'] ?? $_POST['token'] ?? '';
$message    = '';
$success    = false;
$tokenValid = false;

// Vérification initiale du token (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($token)) {
        $message = 'Lien invalide.';
    } else {
        $check      = $controller->validateToken($token);
        $tokenValid = $check['valid'];
        if (!$tokenValid) $message = $check['message'];
    }
}

// Traitement du formulaire (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $result   = $controller->resetPassword($token, $password, $confirm);
    $success  = $result['success'];
    $message  = $result['message'];

    if (!$success) {
        // Re-valider le token pour réafficher le formulaire
        $check      = $controller->validateToken($token);
        $tokenValid = $check['valid'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Takwini - Réinitialisation du mot de passe</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:url('formations/assets/img/bg/home-bg.jpg') center/cover fixed;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;}
body::before{content:'';position:fixed;inset:0;background:rgba(27,94,32,.55);z-index:0;pointer-events:none;}
.card{background:#fff;border-radius:24px;box-shadow:0 8px 32px rgba(0,0,0,.15);padding:48px 40px;width:440px;max-width:100%;z-index:1;position:relative;}
.logo{text-align:center;margin-bottom:8px;}
.logo svg{width:52px;height:52px;}
h1{font-size:24px;font-weight:800;color:#1a1a2e;text-align:center;margin-bottom:6px;}
.subtitle{font-size:13px;color:#888;text-align:center;margin-bottom:28px;line-height:1.5;}
label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;margin-top:14px;}
.input-wrap{position:relative;}
.input-wrap input{width:100%;padding:13px 44px 13px 16px;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:12px;font-size:14px;outline:none;transition:all .25s;color:#1a1a2e;}
.input-wrap input:focus{background:#d4edda;border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
.eye-icon{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;font-size:18px;user-select:none;}
.eye-icon:hover{color:#4caf50;}
.btn{width:100%;padding:14px;margin-top:24px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(76,175,80,.35);}
.btn:hover{transform:translateY(-2px);}
.alert{width:100%;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:16px;display:flex;align-items:center;gap:10px;}
.alert-danger{background:#fde8e8;color:#c0392b;border-left:3px solid #e53935;}
.alert-success{background:#e8f5e9;color:#2e7d32;border-left:3px solid #4caf50;}
.back-link{display:block;text-align:center;margin-top:20px;color:#4caf50;font-size:13px;font-weight:600;text-decoration:none;}
.back-link:hover{text-decoration:underline;}
.strength-bar{height:4px;border-radius:2px;margin-top:6px;background:#eee;overflow:hidden;}
.strength-bar-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;width:0%;}
.strength-label{font-size:11px;color:#aaa;margin-top:3px;}
</style>
</head>
<body>

<div class="card">
  <div class="logo">
    <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="26" cy="26" r="26" fill="#e8f5e9"/>
      <rect x="16" y="24" width="20" height="14" rx="3" stroke="#4caf50" stroke-width="2.5"/>
      <path d="M20 24v-5a6 6 0 0 1 12 0v5" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round"/>
      <circle cx="26" cy="31" r="2" fill="#4caf50"/>
    </svg>
  </div>

  <h1>Nouveau mot de passe</h1>
  <p class="subtitle">Choisissez un mot de passe sécurisé pour votre compte Takwini.</p>

  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
      <?= $success ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <a href="login.php" class="btn" style="display:block;text-align:center;text-decoration:none;margin-top:20px;">
      Se connecter maintenant
    </a>
  <?php elseif ($tokenValid || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
  <form method="POST" action="reset-password.php">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <label for="password">Nouveau mot de passe</label>
    <div class="input-wrap">
      <input type="password" name="password" id="password" placeholder="Minimum 6 caractères"
             required oninput="checkStrength(this.value)">
      <span class="eye-icon" onclick="togglePwd('password', this)">👁</span>
    </div>
    <div class="strength-bar"><div class="strength-bar-fill" id="strength-fill"></div></div>
    <div class="strength-label" id="strength-label"></div>

    <label for="password_confirm">Confirmer le mot de passe</label>
    <div class="input-wrap">
      <input type="password" name="password_confirm" id="password_confirm" placeholder="Répéter le mot de passe" required>
      <span class="eye-icon" onclick="togglePwd('password_confirm', this)">👁</span>
    </div>

    <button type="submit" class="btn">Réinitialiser le mot de passe</button>
  </form>
  <?php else: ?>
    <a href="forgot-password.php" class="back-link">→ Demander un nouveau lien</a>
  <?php endif; ?>

  <a href="login.php" class="back-link">← Retour à la connexion</a>
</div>

<script>
function togglePwd(id, icon) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; icon.textContent = '🙈'; }
    else { inp.type = 'password'; icon.textContent = '👁'; }
}

function checkStrength(val) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#e53935','#ff7043','#ffc107','#66bb6a','#2e7d32'];
    const labels = ['Très faible','Faible','Moyen','Fort','Très fort'];
    fill.style.width  = (score * 20) + '%';
    fill.style.background = colors[score - 1] || '#eee';
    label.textContent = val.length ? labels[score - 1] || '' : '';
    label.style.color = colors[score - 1] || '#aaa';
}
</script>

</body>
</html>
