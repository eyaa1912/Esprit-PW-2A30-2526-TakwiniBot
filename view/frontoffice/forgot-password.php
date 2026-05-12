<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/PasswordResetController.php';

$message    = '';
$success    = false;
$step       = $_SESSION['reset_step'] ?? 'email';
$resetEmail = $_SESSION['reset_email'] ?? '';

// Si on arrive depuis login avec ?reset=1 → repartir de zéro
if (isset($_GET['reset'])) {
    unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['reset_code']);
    $step       = 'email';
    $resetEmail = '';
}

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
body{background:#000;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;}
#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none;}
.glow-orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:1;}
.glow-1{width:500px;height:500px;background:radial-gradient(circle,rgba(34,197,94,.15) 0%,transparent 70%);top:-80px;left:-80px;animation:drift1 12s ease-in-out infinite;}
.glow-2{width:400px;height:400px;background:radial-gradient(circle,rgba(16,163,74,.12) 0%,transparent 70%);bottom:-60px;right:-60px;animation:drift2 15s ease-in-out infinite;}
@keyframes drift1{0%,100%{transform:translate(0,0);}50%{transform:translate(30px,20px);}}
@keyframes drift2{0%,100%{transform:translate(0,0);}50%{transform:translate(-20px,-30px);}}
.card{background:#fff;border-radius:24px;box-shadow:0 8px 32px rgba(0,0,0,.15);padding:48px 40px;width:460px;max-width:100%;z-index:1;position:relative;overflow:visible;}
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

<canvas id="bg-canvas"></canvas>
<div class="glow-orb glow-1"></div>
<div class="glow-orb glow-2"></div>

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

  <!-- Bouton assistance vocale -->
  <button type="button" onclick="activerVoix()"
          style="width:100%;margin-top:16px;padding:14px;background:#1b5e20;color:#fff;
                 border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;
                 box-shadow:0 4px 16px rgba(27,94,32,.4);">
    Je ne vois pas — Assistance vocale
  </button>
</div>

<script>
// ── Assistance vocale ────────────────────────────────────────────────────────
function parler(texte, callback) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    const msg = new SpeechSynthesisUtterance(texte);
    msg.lang = 'fr-FR'; msg.rate = 0.9; msg.pitch = 1.1; msg.volume = 1;
    if (callback) msg.onend = callback;
    window.speechSynthesis.speak(msg);
}

// Clic souris ou frappe clavier → utilisateur voyant → arrêter la voix
document.addEventListener('mousedown', function() {
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
    }
});

document.addEventListener('keydown', function(e) {
    // Si une lettre ou chiffre est tapé → arrêter la voix
    if (e.key.length === 1 && !e.ctrlKey && !e.altKey && window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
    }
});

// Texte à lire selon l'étape courante
const step    = <?= json_encode($step) ?>;
const message = <?= json_encode($message) ?>;
const email   = <?= json_encode($resetEmail) ?>;

function lireEtape() {
    if (step === 'email') {
        let texte = 'Page mot de passe oublié. ';
        if (message) texte += 'Erreur : ' + message + '. ';
        texte += 'Écrivez votre adresse email, puis appuyez sur Entrée.';
        parler(texte, function() {
            const el = document.getElementById('email');
            if (el) el.focus();
            activerAssistanceEmail();
        });
    }
    else if (step === 'code') {
        let texte = '';
        if (message) texte = message + '. ';
        texte += 'Un code à 6 chiffres a été envoyé à ' + email + '. Tapez les 6 chiffres un par un, puis appuyez sur Entrée.';
        parler(texte, function() {
            if (digits.length) digits[0].focus();
        });
    }
    else if (step === 'password') {
        let texte = '';
        if (message) texte = message + '. ';
        texte += 'Choisissez un nouveau mot de passe d\'au moins 6 caractères, puis appuyez sur Entrée.';
        parler(texte, function() {
            const el = document.getElementById('password');
            if (el) el.focus();
            activerAssistanceMdp();
        });
    }
    else if (step === 'done') {
        parler('Mot de passe mis à jour avec succès. Appuyez sur Entrée pour vous connecter.', function() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') window.location.href = 'login.php';
            }, { once: true });
        });
    }
}

// Bouton "Je ne vois pas" → déclenche la voix (contourne le blocage navigateur)
function activerVoix() { lireEtape(); }

// Tentative automatique au chargement (fonctionne si l'utilisateur vient de login.php)
window.addEventListener('load', function() {
    setTimeout(lireEtape, 800);
});

// Assistance étape email — Entrée soumet le formulaire directement
function activerAssistanceEmail() {
    const emailInput = document.getElementById('email');
    if (emailInput) emailInput.focus();

    document.addEventListener('keydown', function handler(e) {
        if (e.key !== 'Enter') return;
        const el  = document.getElementById('email');
        const val = el ? el.value.trim() : '';
        if (!val) {
            e.preventDefault();
            parler('Ce champ est obligatoire. Écrivez votre adresse email.');
            return;
        }
        // Email rempli → laisser le formulaire se soumettre normalement
        document.removeEventListener('keydown', handler);
        // Ne pas appeler e.preventDefault() → le formulaire se soumet
    });
}

// Assistance étape nouveau mot de passe
function activerAssistanceMdp() {
    let etapeMdp = 0;
    document.addEventListener('keydown', function handler(e) {
        if (e.key !== 'Enter') return;
        if (etapeMdp === 0) {
            const pw = document.getElementById('password');
            if (!pw || pw.value.length < 6) {
                e.preventDefault();
                parler('Le mot de passe doit contenir au moins 6 caractères. Réessayez.');
                return;
            }
            e.preventDefault();
            etapeMdp = 1;
            parler('Mot de passe enregistré. Confirmez votre mot de passe, puis appuyez sur Entrée.', function() {
                const conf = document.getElementById('password_confirm');
                if (conf) conf.focus();
            });
        } else if (etapeMdp === 1) {
            const pw   = document.getElementById('password');
            const conf = document.getElementById('password_confirm');
            if (!conf || conf.value !== pw.value) {
                e.preventDefault();
                parler('Les mots de passe ne correspondent pas. Réécrivez la confirmation.');
                return;
            }
            document.removeEventListener('keydown', handler);
            parler('Confirmation correcte. Envoi en cours.', function() {
                conf.closest('form').submit();
            });
        }
    });
}

// ── Navigation entre les cases du code ──────────────────────────────────────
const digits = document.querySelectorAll('.digit');
if (digits.length) {
    digits.forEach((input, i) => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && i < digits.length - 1) digits[i + 1].focus();
            updateHiddenCode();
            this.classList.toggle('filled', this.value !== '');
            // Quand les 6 cases sont remplies, annoncer et soumettre
            const code = Array.from(digits).map(d => d.value).join('');
            if (code.length === 6) {
                parler('Code saisi : ' + code.split('').join(', ') + '. Appuyez sur Entrée pour vérifier.');
            }
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && i > 0) digits[i - 1].focus();
            if (e.key === 'Enter') {
                const code = Array.from(digits).map(d => d.value).join('');
                if (code.length < 6) {
                    e.preventDefault();
                    parler('Veuillez saisir les 6 chiffres du code.');
                }
                // sinon laisser le formulaire se soumettre
            }
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function(){
    const canvas=document.getElementById('bg-canvas');
    if(!canvas||!window.THREE) return;
    const renderer=new THREE.WebGLRenderer({canvas,antialias:true,alpha:true});
    renderer.setPixelRatio(Math.min(window.devicePixelRatio,2));
    renderer.setSize(window.innerWidth,window.innerHeight);
    const scene=new THREE.Scene();
    const camera=new THREE.PerspectiveCamera(75,window.innerWidth/window.innerHeight,0.1,1000);
    camera.position.z=30;
    const count=1500;
    const geo=new THREE.BufferGeometry();
    const pos=new Float32Array(count*3);
    const col=new Float32Array(count*3);
    for(let i=0;i<count;i++){
        pos[i*3]=(Math.random()-.5)*120;
        pos[i*3+1]=(Math.random()-.5)*120;
        pos[i*3+2]=(Math.random()-.5)*80;
        const g=.5+Math.random()*.5;
        col[i*3]=0;col[i*3+1]=g;col[i*3+2]=g*.2;
    }
    geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
    geo.setAttribute('color',new THREE.BufferAttribute(col,3));
    const mat=new THREE.PointsMaterial({size:.22,vertexColors:true,transparent:true,opacity:.7});
    scene.add(new THREE.Points(geo,mat));
    const points=scene.children[0];
    function animate(){requestAnimationFrame(animate);points.rotation.y+=.0005;points.rotation.x+=.0002;renderer.render(scene,camera);}
    animate();
    window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight);});
})();
</script>
</body>
</html>
