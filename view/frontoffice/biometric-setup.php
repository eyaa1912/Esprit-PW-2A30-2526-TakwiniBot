<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$db     = config::getConnexion();
$stmt   = $db->prepare('SELECT id, created_at FROM webauthn_credentials WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $userId]);
$existing = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion biométrique - Takwinibot</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:linear-gradient(135deg,#e8f5e9,#f1f8e9); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
.card { background:#fff; border-radius:24px; box-shadow:0 8px 32px rgba(0,0,0,.1); padding:48px 40px; width:100%; max-width:460px; }
.back-btn { display:inline-flex; align-items:center; gap:8px; color:#666; text-decoration:none; font-size:13px; font-weight:600; background:#f5f5f5; padding:8px 14px; border-radius:10px; margin-bottom:28px; transition:all .2s; }
.back-btn:hover { background:#e8f5e9; color:#2e7d32; }
.icon-wrap { width:72px; height:72px; background:linear-gradient(135deg,#e8f5e9,#c8e6c9); border-radius:20px; display:flex; align-items:center; justify-content:center; margin-bottom:20px; }
h1 { font-size:24px; font-weight:800; color:#1a1a2e; margin-bottom:8px; }
p.desc { font-size:14px; color:#888; line-height:1.7; margin-bottom:28px; }
.status-box { padding:14px 16px; border-radius:12px; font-size:13px; margin-bottom:24px; display:flex; align-items:center; gap:10px; }
.status-active { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
.status-none   { background:#fff8e1; color:#f57f17; border:1px solid #ffe082; }
.btn { width:100%; padding:14px; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; transition:all .25s; display:flex; align-items:center; justify-content:center; gap:10px; }
.btn-primary { background:linear-gradient(135deg,#4caf50,#2e7d32); color:#fff; box-shadow:0 4px 20px rgba(76,175,80,.3); }
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(76,175,80,.4); }
.btn-danger { background:#fff; color:#e53935; border:2px solid #ffcdd2; margin-top:10px; }
.btn-danger:hover { background:#fde8e8; }
.msg { font-size:13px; text-align:center; margin-top:12px; min-height:18px; font-weight:600; }
.steps { background:#f8f9fa; border-radius:12px; padding:16px 20px; margin-bottom:24px; }
.steps p { font-size:12px; color:#666; margin-bottom:6px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
.steps ol { padding-left:18px; }
.steps li { font-size:13px; color:#555; line-height:1.8; }
</style>
</head>
<body>
<div class="card">
    <a href="formations/index.php" class="back-btn">← Retour</a>

    <div class="icon-wrap">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="1.5">
            <path d="M12 1C8.5 1 5 3.5 5 7.5c0 6 7 14 7 14s7-8 7-14C19 3.5 15.5 1 12 1z"/>
            <circle cx="12" cy="7" r="2.5"/>
            <path d="M9 12c0 1.7 1.3 3 3 3s3-1.3 3-3"/>
        </svg>
    </div>

    <h1>Connexion biométrique</h1>
    <p class="desc">Activez la connexion par empreinte digitale ou Face ID (Windows Hello) pour vous connecter sans mot de passe.</p>

    <?php if ($existing): ?>
    <div class="status-box status-active">
        ✅ Empreinte enregistrée le <?= date('d/m/Y', strtotime($existing['created_at'])) ?>
    </div>
    <?php else: ?>
    <div class="status-box status-none">
        ⚠️ Aucune empreinte enregistrée pour ce compte
    </div>
    <?php endif; ?>

    <div class="steps">
        <p>Comment ça marche</p>
        <ol>
            <li>Cliquez "Enregistrer mon empreinte"</li>
            <li>Windows Hello / Face ID s'ouvre</li>
            <li>Scannez votre visage ou empreinte</li>
            <li>Votre clé est sauvegardée — prête pour la connexion</li>
        </ol>
    </div>

    <button class="btn btn-primary" id="btn-register-bio">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <path d="M12 1C8.5 1 5 3.5 5 7.5c0 6 7 14 7 14s7-8 7-14C19 3.5 15.5 1 12 1z"/>
            <circle cx="12" cy="7" r="2.5"/>
        </svg>
        <?= $existing ? 'Mettre à jour mon empreinte' : 'Enregistrer mon empreinte' ?>
    </button>

    <?php if ($existing): ?>
    <button class="btn btn-danger" id="btn-delete-bio">Supprimer l'empreinte</button>
    <?php endif; ?>

    <div class="msg" id="bio-msg"></div>
</div>

<script>
function base64urlToBuffer(b) {
    const base64 = b.replace(/-/g,'+').replace(/_/g,'/');
    const bin = atob(base64);
    return Uint8Array.from(bin, c => c.charCodeAt(0)).buffer;
}
function bufferToBase64url(buf) {
    const bytes = new Uint8Array(buf);
    let str = '';
    bytes.forEach(b => str += String.fromCharCode(b));
    return btoa(str).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
}

document.getElementById('btn-register-bio').addEventListener('click', async function() {
    const msg = document.getElementById('bio-msg');
    msg.style.color = '#888';
    msg.textContent = 'Préparation...';

    if (!window.PublicKeyCredential) {
        msg.style.color = '#e53935';
        msg.textContent = 'Votre navigateur ne supporte pas la biométrie.';
        return;
    }

    try {
        // 1. Obtenir challenge
        const res  = await fetch('webauthn-register-challenge.php');
        const data = await res.json();

        if (data.error) { msg.style.color='#e53935'; msg.textContent=data.error; return; }

        msg.textContent = 'Scannez votre empreinte ou visage...';

        // 2. Créer credential
        const userId = new TextEncoder().encode(String(data.user_id));
        const cred   = await navigator.credentials.create({
            publicKey: {
                challenge:              base64urlToBuffer(data.challenge),
                rp:                     { name: 'Takwinibot', id: 'localhost' },
                user:                   { id: userId, name: data.user_name, displayName: data.user_display },
                pubKeyCredParams:       [{ type: 'public-key', alg: -7 }, { type: 'public-key', alg: -257 }],
                authenticatorSelection: { userVerification: 'required', residentKey: 'preferred' },
                timeout:                60000,
                attestation:            'none',
            }
        });

        // 3. Sauvegarder
        const saveRes  = await fetch('webauthn-register-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                credentialId: bufferToBase64url(cred.rawId),
                publicKey:    bufferToBase64url(cred.response.getPublicKey ? cred.response.getPublicKey() : cred.response.attestationObject),
            })
        });
        const saveData = await saveRes.json();

        if (saveData.success) {
            msg.style.color = '#4caf50';
            msg.textContent = '✅ Empreinte enregistrée avec succès !';
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.style.color = '#e53935';
            msg.textContent = saveData.error || 'Erreur lors de la sauvegarde.';
        }
    } catch (err) {
        msg.style.color = '#e53935';
        msg.textContent = err.name === 'NotAllowedError' ? 'Annulé.' : 'Erreur : ' + err.message;
    }
});

<?php if ($existing): ?>
document.getElementById('btn-delete-bio').addEventListener('click', async function() {
    if (!confirm('Supprimer votre empreinte ?')) return;
    const res  = await fetch('webauthn-delete.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) location.reload();
});
<?php endif; ?>
</script>
</body>
</html>
