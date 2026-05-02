<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$db     = config::getConnexion();
$stmt   = $db->prepare('SELECT id, created_at FROM face_descriptors WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $userId]);
$existing = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enregistrer mon visage - Takwinibot</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:linear-gradient(135deg,#e8f5e9,#f1f8e9);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.card{background:#fff;border-radius:24px;box-shadow:0 8px 32px rgba(0,0,0,.1);padding:40px;width:100%;max-width:520px;}
.back-btn{display:inline-flex;align-items:center;gap:8px;color:#666;text-decoration:none;font-size:13px;font-weight:600;background:#f5f5f5;padding:8px 14px;border-radius:10px;margin-bottom:24px;transition:all .2s;}
.back-btn:hover{background:#e8f5e9;color:#2e7d32;}
h1{font-size:22px;font-weight:800;color:#1a1a2e;margin-bottom:6px;}
p.desc{font-size:14px;color:#888;margin-bottom:24px;line-height:1.6;}
.status-box{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.status-ok{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;}
.status-none{background:#fff8e1;color:#f57f17;border:1px solid #ffe082;}
.video-wrap{position:relative;width:100%;border-radius:16px;overflow:hidden;background:#000;margin-bottom:20px;}
video{width:100%;display:block;border-radius:16px;}
canvas{display:none;}
.overlay{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;}
.face-circle{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:200px;height:200px;border:3px solid rgba(76,175,80,.8);border-radius:50%;box-shadow:0 0 0 9999px rgba(0,0,0,.35);}
.btn{width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-primary{background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;box-shadow:0 4px 20px rgba(76,175,80,.3);}
.btn-primary:hover{transform:translateY(-2px);}
.btn-primary:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.btn-danger{background:#fff;color:#e53935;border:2px solid #ffcdd2;margin-top:10px;}
.btn-danger:hover{background:#fde8e8;}
.msg{font-size:13px;text-align:center;margin-top:12px;min-height:18px;font-weight:600;}
.steps{background:#f8f9fa;border-radius:12px;padding:14px 18px;margin-bottom:20px;}
.steps p{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa;margin-bottom:8px;}
.steps ol{padding-left:18px;}
.steps li{font-size:13px;color:#555;line-height:1.8;}
#loading-models{text-align:center;padding:20px;color:#888;font-size:14px;}
</style>
</head>
<body>
<div class="card">
    <a href="formations/index.php" class="back-btn">← Retour</a>
    <h1>Connexion par visage</h1>
    <p class="desc">Enregistrez votre visage pour vous connecter sans mot de passe.</p>

    <?php if ($existing): ?>
    <div class="status-box status-ok">✅ Visage enregistré le <?= date('d/m/Y', strtotime($existing['created_at'])) ?></div>
    <?php else: ?>
    <div class="status-box status-none">⚠️ Aucun visage enregistré</div>
    <?php endif; ?>

    <div class="steps">
        <p>Comment ça marche</p>
        <ol>
            <li>Autorisez l'accès à la caméra</li>
            <li>Placez votre visage dans le cercle vert</li>
            <li>Cliquez "Enregistrer mon visage"</li>
            <li>Attendez la confirmation</li>
        </ol>
    </div>

    <div id="loading-models">⏳ Chargement des modèles IA...</div>

    <div id="camera-section" style="display:none;">
        <div class="video-wrap">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="canvas"></canvas>
            <div class="overlay">
                <div class="face-circle"></div>
            </div>
        </div>

        <button class="btn btn-primary" id="btn-register" disabled>
            📸 Enregistrer mon visage
        </button>
        <?php if ($existing): ?>
        <button class="btn btn-danger" id="btn-delete">Supprimer mon visage</button>
        <?php endif; ?>
        <div class="msg" id="msg"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video   = document.getElementById('video');
const canvas  = document.getElementById('canvas');
const btnReg  = document.getElementById('btn-register');
const msgEl   = document.getElementById('msg');
const loading = document.getElementById('loading-models');
const section = document.getElementById('camera-section');

const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

async function loadModels() {
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        loading.style.display = 'none';
        section.style.display = 'block';
        startCamera();
    } catch(e) {
        loading.textContent = '❌ Erreur chargement modèles : ' + e.message;
    }
}

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 300, facingMode: 'user' } });
        video.srcObject = stream;
        video.addEventListener('loadeddata', () => {
            btnReg.disabled = false;
            detectLoop();
        });
    } catch(e) {
        msgEl.style.color = '#e53935';
        msgEl.textContent = 'Caméra non accessible : ' + e.message;
    }
}

// Détection en temps réel pour feedback visuel
async function detectLoop() {
    const circle = document.querySelector('.face-circle');
    setInterval(async () => {
        const det = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.6 }));
        if (det) {
            circle.style.borderColor = 'rgba(76,175,80,1)';
            circle.style.boxShadow   = '0 0 0 9999px rgba(0,0,0,.35), 0 0 20px rgba(76,175,80,.6)';
        } else {
            circle.style.borderColor = 'rgba(255,255,255,.5)';
            circle.style.boxShadow   = '0 0 0 9999px rgba(0,0,0,.35)';
        }
    }, 500);
}

btnReg.addEventListener('click', async () => {
    btnReg.disabled = true;
    msgEl.style.color = '#888';
    msgEl.textContent = 'Analyse du visage...';

    try {
        const detection = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.6 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            msgEl.style.color = '#e53935';
            msgEl.textContent = 'Aucun visage détecté. Placez-vous face à la caméra.';
            btnReg.disabled = false;
            return;
        }

        const descriptor = Array.from(detection.descriptor);
        msgEl.textContent = 'Sauvegarde en cours...';

        const res  = await fetch('face-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descriptor })
        });
        const data = await res.json();

        if (data.success) {
            msgEl.style.color = '#4caf50';
            msgEl.textContent = '✅ Visage enregistré avec succès !';
            setTimeout(() => location.reload(), 1500);
        } else {
            msgEl.style.color = '#e53935';
            msgEl.textContent = data.error || 'Erreur lors de la sauvegarde.';
            btnReg.disabled = false;
        }
    } catch(e) {
        msgEl.style.color = '#e53935';
        msgEl.textContent = 'Erreur : ' + e.message;
        btnReg.disabled = false;
    }
});

const btnDel = document.getElementById('btn-delete');
if (btnDel) {
    btnDel.addEventListener('click', async () => {
        if (!confirm('Supprimer votre visage enregistré ?')) return;
        const res  = await fetch('face-delete.php', { method: 'POST' });
        const data = await res.json();
        if (data.success) location.reload();
    });
}

loadModels();
</script>
</body>
</html>
