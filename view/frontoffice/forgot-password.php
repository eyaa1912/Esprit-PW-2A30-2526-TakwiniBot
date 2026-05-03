<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/PasswordResetController.php';

$message    = '';
$success    = false;
$step       = $_SESSION['reset_step'] ?? 'email'; // 'email' | 'code' | 'password'
$resetEmail = $_SESSION['reset_email'] ?? '';

$controller = new PasswordResetController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Étape 1 : envoi du code ──────────────────────────────────────────
    if ($action === 'send_code') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Veuillez saisir une adresse email valide.';
        } else {
            $result  = $controller->sendResetCode($email);
            $success = $result['success'];
            $message = $result['message'];
            if ($success) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_step']  = 'code';
                $step       = 'code';
                $resetEmail = $email;
            }
            // Si échec, on reste sur l'étape email et on affiche l'erreur réelle
        }
    }

    // ── Étape 2 : vérification du code ──────────────────────────────────
    elseif ($action === 'verify_code') {
        $code  = trim($_POST['code'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';
        if (empty($code) || strlen($code) !== 6 || !ctype_digit($code)) {
            $message = 'Veuillez saisir le code à 6 chiffres reçu par email.';
            $step    = 'code';
        } else {
            $check = $controller->validateCode($email, $code);
            if ($check['valid']) {
                $_SESSION['reset_step'] = 'password';
                $_SESSION['reset_code'] = $code;
                $step    = 'password';
                $success = true;
                $message = 'Code vérifié ! Choisissez votre nouveau mot de passe.';
            } else {
                $message = $check['message'];
                $step    = 'code';
            }
        }
    }

    // ── Étape 3 : nouveau mot de passe ──────────────────────────────────
    elseif ($action === 'reset_password') {
        $email    = $_SESSION['reset_email'] ?? '';
        $code     = $_SESSION['reset_code']  ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        $result = $controller->resetPasswordWithCode($email, $code, $password, $confirm);
        $success = $result['success'];
        $message = $result['message'];

        if ($success) {
            unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['reset_code']);
            $step = 'done';
        } else {
            $step = 'password';
        }
    }

    // ── Renvoyer le code ────────────────────────────────────────────────
    elseif ($action === 'resend_code') {
        $email = $_SESSION['reset_email'] ?? '';
        if ($email) {
            $result  = $controller->sendResetCode($email);
            $message = $result['success'] ? 'Un nouveau code a été envoyé.' : $result['message'];
            $success = $result['success'];
        }
        $step = 'code';
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
.card{background:#fff;border-radius:24px;box-shadow:0 8px 32px rgba(0,0,0,.15);padding:48px 40px;width:460px;max-width:100%;z-index:1;position:relative;}
.logo{text-align:center;margin-bottom:8px;}
.logo svg{width:52px;height:52px;}
h1{font-size:24px;font-weight:800;color:#1a1a2e;text-align:center;margin-bottom:6px;}
.subtitle{font-size:13px;color:#888;text-align:center;margin-bottom:28px;line-height:1.5;}
label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
input[type=email],input[type=password]{width:100%;padding:13px 16px;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:12px;font-size:14px;outline:none;transition:all .25s;color:#1a1a2e;}
input[type=email]:focus,input[type=password]:focus{background:#d4edda;border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
.btn{width:100%;padding:14px;margin-top:20px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(76,175,80,.35);}
.btn:hover{transform:translateY(-2px);}
.alert{width:100%;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:16px;display:flex;align-items:center;gap:10px;}
.alert-danger{background:#fde8e8;color:#c0392b;border-left:3px solid #e53935;}
.alert-success{background:#e8f5e9;color:#2e7d32;border-left:3px solid #4caf50;}
.back-link{display:block;text-align:center;margin-top:20px;color:#4caf50;font-size:13px;font-weight:600;text-decoration:none;}
.back-link:hover{text-decoration:underline;}

/* Code input */
.code-inputs{display:flex;gap:10px;justify-content:center;margin:20px 0;}
.code-inputs input{width:52px;height:60px;text-align:center;font-size:26px;font-weight:800;color:#2e7d32;background:#e8f5e9;border:2px solid #c8e6c9;border-radius:12px;outline:none;transition:all .2s;caret-color:#4caf50;}
.code-inputs input:focus{border-color:#4caf50;background:#d4edda;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
.code-inputs input.filled{border-color:#4caf50;background:#d4edda;}
.resend-link{display:block;text-align:center;margin-top:10px;color:#4caf50;font-size:12px;font-weight:600;cursor:pointer;background:none;border:none;text-decoration:underline;}
.resend-link:hover{color:#2e7d32;}

/* Steps indicator */
.steps{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px;}
.step{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#ccc;}
.step.active{color:#4caf50;}
.step.done{color:#2e7d32;}
.step-dot{width:28px;height:28px;border-radius:50%;border:2px solid #e0e0e0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;background:#fff;color:#ccc;transition:all .3s;}
.step.active .step-dot{border-color:#4caf50;color:#4caf50;background:#e8f5e9;}
.step.done .step-dot{border-color:#2e7d32;background:#2e7d32;color:#fff;}
.step-line{width:32px;height:2px;background:#e0e0e0;margin:0 4px;}
.step-line.done{background:#4caf50;}

/* Password wrap */
.input-wrap{position:relative;}
.input-wrap input{padding-right:44px;}
.eye-icon{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;font-size:18px;user-select:none;}
.eye-icon:hover{color:#4caf50;}
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
      <path d="M16 36c0-5.523 4.477-10 10-10s10 4.477 10 10" stroke="#4caf50" stroke-width="2.5" stroke-linecap="round"/>
      <circle cx="26" cy="20" r="5" stroke="#2e7d32" stroke-width="2.5"/>
    </svg>
  </div>

  <!-- Indicateur d'étapes -->
  <?php if ($step !== 'done'): ?>
  <div class="steps">
    <div class="step <?= $step === 'email' ? 'active' : 'done' ?>">
      <div class="step-dot"><?= $step === 'email' ? '1' : '✓' ?></div>
    </div>
    <div class="step-line <?= in_array($step, ['code','password']) ? 'done' : '' ?>"></div>
    <div class="step <?= $step === 'code' ? 'active' : ($step === 'password' ? 'done' : '') ?>">
      <div class="step-dot"><?= $step === 'password' ? '✓' : '2' ?></div>
    </div>
    <div class="step-line <?= $step === 'password' ? 'done' : '' ?>"></div>
    <div class="step <?= $step === 'password' ? 'active' : '' ?>">
      <div class="step-dot">3</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($step === 'email'): ?>
    <h1>Mot de passe oublié ?</h1>
    <p class="subtitle">Saisissez votre email et nous vous enverrons un code à 6 chiffres.</p>

    <?php if ($message && !$success): ?>
      <div class="alert alert-danger">⚠️ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php">
      <input type="hidden" name="action" value="send_code">
      <label for="email">Adresse email</label>
      <input type="email" name="email" id="email" placeholder="exemple@email.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      <button type="submit" class="btn">Envoyer le code</button>
    </form>

  <?php elseif ($step === 'code'): ?>
    <h1>Vérification</h1>
    <p class="subtitle">Un code à 6 chiffres a été envoyé à<br><strong><?= htmlspecialchars($resetEmail) ?></strong></p>

    <?php if ($message && !$success): ?>
      <div class="alert alert-danger">⚠️ <?= htmlspecialchars($message) ?></div>
    <?php elseif ($message && $success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php" id="code-form">
      <input type="hidden" name="action" value="verify_code">
      <input type="hidden" name="code" id="hidden-code">

      <div class="code-inputs" id="code-inputs">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit" autofocus>
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="digit">
      </div>

      <button type="submit" class="btn" id="btn-verify">Vérifier le code</button>
    </form>

    <form method="POST" action="forgot-password.php" style="margin-top:0;">
      <input type="hidden" name="action" value="resend_code">
      <button type="submit" class="resend-link">Renvoyer le code</button>
    </form>

  <?php elseif ($step === 'password'): ?>
    <h1>Nouveau mot de passe</h1>
    <p class="subtitle">Choisissez un mot de passe sécurisé pour votre compte.</p>

    <?php if ($message && !$success): ?>
      <div class="alert alert-danger">⚠️ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php">
      <input type="hidden" name="action" value="reset_password">

      <label for="password" style="margin-top:0;">Nouveau mot de passe</label>
      <div class="input-wrap">
        <input type="password" name="password" id="password" placeholder="Minimum 6 caractères"
               required oninput="checkStrength(this.value)">
        <span class="eye-icon" onclick="togglePwd('password', this)">👁</span>
      </div>
      <div class="strength-bar"><div class="strength-bar-fill" id="strength-fill"></div></div>
      <div class="strength-label" id="strength-label"></div>

      <label for="password_confirm">Confirmer le mot de passe</label>
      <div class="input-wrap">
        <input type="password" name="password_confirm" id="password_confirm"
               placeholder="Répéter le mot de passe" required>
        <span class="eye-icon" onclick="togglePwd('password_confirm', this)">👁</span>
      </div>

      <button type="submit" class="btn">Réinitialiser le mot de passe</button>
    </form>

  <?php elseif ($step === 'done'): ?>
    <h1>Mot de passe mis à jour !</h1>
    <p class="subtitle">Votre mot de passe a été réinitialisé avec succès.</p>
    <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
    <a href="login.php" class="btn" style="display:block;text-align:center;text-decoration:none;margin-top:20px;">
      Se connecter maintenant
    </a>
  <?php endif; ?>

  <a href="login.php" class="back-link">← Retour à la connexion</a>
</div>

<script>
// ── Navigation entre les cases du code ──────────────────────────────────────
const digits = document.querySelectorAll('.digit');
if (digits.length) {
    digits.forEach((input, i) => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && i < digits.length - 1) digits[i + 1].focus();
            updateHiddenCode();
            this.classList.toggle('filled', this.value !== '');
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && i > 0) digits[i - 1].focus();
        });
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            pasted.split('').forEach((ch, j) => {
                if (digits[j]) { digits[j].value = ch; digits[j].classList.add('filled'); }
            });
            const next = Math.min(pasted.length, digits.length - 1);
            digits[next].focus();
            updateHiddenCode();
        });
    });
}

function updateHiddenCode() {
    const hidden = document.getElementById('hidden-code');
    if (hidden) hidden.value = Array.from(digits).map(d => d.value).join('');
}

// ── Mot de passe ─────────────────────────────────────────────────────────────
function togglePwd(id, icon) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; icon.textContent = '🙈'; }
    else { inp.type = 'password'; icon.textContent = '👁'; }
}

function checkStrength(val) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    if (!fill) return;
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#e53935','#ff7043','#ffc107','#66bb6a','#2e7d32'];
    const labels = ['Très faible','Faible','Moyen','Fort','Très fort'];
    fill.style.width      = (score * 20) + '%';
    fill.style.background = colors[score - 1] || '#eee';
    label.textContent     = val.length ? labels[score - 1] || '' : '';
    label.style.color     = colors[score - 1] || '#aaa';
}
</script>

</body>
</html>
