<?php
session_start();
require_once __DIR__ . '/../../config.php';

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = false;
$valid   = false;
$email   = '';

if (empty($token)) {
    $error = 'Lien invalide.';
} else {
    $db   = config::getConnexion();
    $stmt = $db->prepare('SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1');
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $valid = true;
        $email = $reset['email'];
    } else {
        $error = 'Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $password2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare('UPDATE users SET mot_de_passe = :mdp WHERE email = :email')
           ->execute(['mdp' => $hashed, 'email' => $email]);
        $db->prepare('DELETE FROM password_resets WHERE email = :email')
           ->execute(['email' => $email]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nouveau mot de passe - Takwinibot</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:linear-gradient(135deg,#e8f5e9,#f1f8e9); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
.card { background:#fff; border-radius:24px; box-shadow:0 8px 32px rgba(0,0,0,.1); padding:48px 40px; width:100%; max-width:440px; }
.back-btn { display:inline-flex; align-items:center; gap:8px; color:#666; text-decoration:none; font-size:13px; font-weight:600; background:#f5f5f5; padding:8px 14px; border-radius:10px; margin-bottom:28px; transition:all .2s; }
.back-btn:hover { background:#e8f5e9; color:#2e7d32; }
.icon { font-size:48px; margin-bottom:16px; display:block; }
h1 { font-size:26px; font-weight:800; color:#1a1a2e; margin-bottom:8px; }
.divider { height:3px; background:linear-gradient(90deg,#4caf50,#2e7d32); border-radius:2px; margin-bottom:20px; }
p.desc { font-size:14px; color:#888; line-height:1.6; margin-bottom:28px; }
label { display:block; font-size:12px; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; margin-top:16px; }
.input-wrap { position:relative; }
input[type=password], input[type=text] { width:100%; padding:12px 40px 12px 16px; border:1.5px solid #e0e0e0; border-radius:12px; font-size:14px; outline:none; transition:all .25s; background:#fafafa; }
input:focus { border-color:#4caf50; background:#fff; box-shadow:0 0 0 3px rgba(76,175,80,.12); }
.req { font-size:11px; color:#aaa; margin-top:6px; }
.btn { width:100%; padding:14px; background:linear-gradient(135deg,#4caf50,#2e7d32); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; margin-top:24px; transition:all .25s; }
.btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(76,175,80,.35); }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #4caf50; padding:14px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; }
.alert-error { background:#fde8e8; color:#c0392b; border-left:4px solid #e53935; padding:14px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; }
.strength { height:4px; border-radius:2px; margin-top:6px; transition:all .3s; background:#eee; }
</style>
</head>
<body>
<div class="card">
    <a href="login.php" class="back-btn">← Retour</a>

    <?php if ($success): ?>
        <span class="icon">✅</span>
        <h1>Mot de passe mis à jour !</h1>
        <div class="divider"></div>
        <p class="desc">Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.</p>
        <a href="login.php" class="btn" style="display:block;text-align:center;text-decoration:none;">Se connecter</a>

    <?php elseif (!$valid): ?>
        <h1>Lien invalide</h1>
        <div class="divider"></div>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <a href="forgot-password.php" class="btn" style="display:block;text-align:center;text-decoration:none;">Nouvelle demande</a>

    <?php else: ?>
        <span class="icon">🔐</span>
        <h1>Nouveau mot de passe</h1>
        <div class="divider"></div>
        <p class="desc">Choisissez un mot de passe fort que vous n'avez pas utilisé auparavant.</p>

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="reset-password.php?token=<?= htmlspecialchars($token) ?>">
            <label>Nouveau mot de passe</label>
            <div class="input-wrap">
                <input type="password" name="password" id="pwd" placeholder="Minimum 6 caractères" required oninput="checkStrength(this.value)">
            </div>
            <div class="strength" id="strength-bar"></div>
            <p class="req">Minimum 6 caractères — utilisez lettres, chiffres et symboles.</p>

            <label>Confirmer le mot de passe</label>
            <div class="input-wrap">
                <input type="password" name="password2" placeholder="Répétez le mot de passe" required>
            </div>

            <button type="submit" class="btn">Enregistrer le mot de passe</button>
        </form>
    <?php endif; ?>
</div>

<script>
function checkStrength(val) {
    const bar = document.getElementById('strength-bar');
    if (val.length === 0) { bar.style.width='0'; bar.style.background='#eee'; return; }
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#e53935','#ff9800','#ffc107','#8bc34a','#4caf50'];
    const widths = ['20%','40%','60%','80%','100%'];
    bar.style.width   = widths[score-1] || '20%';
    bar.style.background = colors[score-1] || '#e53935';
}
</script>
</body>
</html>
