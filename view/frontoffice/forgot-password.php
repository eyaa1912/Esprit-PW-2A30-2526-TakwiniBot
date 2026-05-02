<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/PasswordResetController.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $controller = new PasswordResetController();

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez saisir une adresse email valide.';
    } else {
        $result  = $controller->sendResetLink($email);
        $message = $result['message'];
        $success = $result['success'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Takwini - Mot de passe oublié</title>
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
label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
input[type=email]{width:100%;padding:13px 16px;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:12px;font-size:14px;outline:none;transition:all .25s;color:#1a1a2e;}
input[type=email]:focus{background:#d4edda;border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
.btn{width:100%;padding:14px;margin-top:20px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(76,175,80,.35);}
.btn:hover{transform:translateY(-2px);}
.alert{width:100%;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:16px;display:flex;align-items:center;gap:10px;}
.alert-danger{background:#fde8e8;color:#c0392b;border-left:3px solid #e53935;}
.alert-success{background:#e8f5e9;color:#2e7d32;border-left:3px solid #4caf50;}
.back-link{display:block;text-align:center;margin-top:20px;color:#4caf50;font-size:13px;font-weight:600;text-decoration:none;}
.back-link:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="card">
  <div class="logo">
    <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="26" cy="26" r="26" fill="#e8f5e9"/>
      <path d="M16 36c0-5.523 4.477-10 10-10s10 4.477 10 10" stroke="#4caf50" stroke-width="2.5" stroke-linecap="round"/>
      <circle cx="26" cy="20" r="5" stroke="#2e7d32" stroke-width="2.5"/>
      <path d="M20 30l-4 4M32 30l4 4" stroke="#4caf50" stroke-width="2" stroke-linecap="round"/>
    </svg>
  </div>

  <h1>Mot de passe oublié ?</h1>
  <p class="subtitle">Saisissez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

  <?php if ($message && !$success): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($message) ?></div>
  <?php elseif ($message && $success): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <form method="POST" action="forgot-password.php">
    <label for="email">Adresse email</label>
    <input type="email" name="email" id="email" placeholder="exemple@email.com"
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
    <button type="submit" class="btn">Envoyer le lien de réinitialisation</button>
  </form>
  <?php endif; ?>

  <a href="login.php" class="back-link">← Retour à la connexion</a>
</div>

</body>
</html>
