<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$db     = config::getConnexion();

// Charger user
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) { session_destroy(); header('Location: login.php'); exit; }

$role    = $user['role'];
$message = '';
$error   = '';

// ── UPLOAD AVATAR ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format non autorise.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $error = 'Fichier trop grand (max 2MB).';
        } else {
            $dir = __DIR__ . '/uploads/avatars/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                $avatarPath = 'uploads/avatars/' . $filename;
                $db->prepare('UPDATE users SET avatar = :a WHERE id = :id')
                   ->execute(['a' => $avatarPath, 'id' => $userId]);
                $user['avatar'] = $avatarPath;
                $_SESSION['user']['avatar'] = $avatarPath;
                $message = 'Avatar mis a jour !';
            } else {
                $error = 'Impossible de sauvegarder le fichier.';
            }
        }
    } else {
        $error = 'Aucun fichier selectionne.';
    }
}

// ── UPDATE PROFIL ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $tel    = trim($_POST['telephone'] ?? '');

    if (empty($nom) || empty($email)) {
        $error = 'Nom et email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email invalide.';
    } else {
        $checkEmail = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $checkEmail->execute(['email' => $email, 'id' => $userId]);
        if ($checkEmail->fetch()) {
            $error = 'Cet email est deja utilise.';
        } else {
            if ($role === 'candidat') {
                $sexe           = $_POST['sexe']           ?? '';
                $date_naissance = $_POST['date_naissance'] ?? '';
                $adresse        = trim($_POST['adresse']   ?? '');
                $handicap       = !empty($_POST['handicap']) ? 1 : 0;
                $type_handicap  = $handicap ? trim($_POST['type_handicap'] ?? '') : null;
                $db->prepare('UPDATE users SET nom=:nom,prenom=:prenom,email=:email,telephone=:tel,sexe=:sexe,date_naissance=:dob,adresse=:adresse,handicap=:h,type_handicap=:th WHERE id=:id')
                   ->execute(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'tel'=>$tel?:null,'sexe'=>$sexe?:null,'dob'=>$date_naissance?:null,'adresse'=>$adresse?:null,'h'=>$handicap,'th'=>$type_handicap,'id'=>$userId]);
            } elseif ($role === 'recruteur') {
                $entreprise = trim($_POST['entreprise']       ?? '');
                $matricule  = trim($_POST['matricule_fiscal'] ?? '');
                $secteur    = trim($_POST['secteur']          ?? '');
                $db->prepare('UPDATE users SET nom=:nom,prenom=:prenom,email=:email,telephone=:tel,entreprise=:ent,matricule_fiscal=:mat,secteur=:sec WHERE id=:id')
                   ->execute(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'tel'=>$tel?:null,'ent'=>$entreprise?:null,'mat'=>$matricule?:null,'sec'=>$secteur?:null,'id'=>$userId]);
            } else {
                $db->prepare('UPDATE users SET nom=:nom,prenom=:prenom,email=:email,telephone=:tel WHERE id=:id')
                   ->execute(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'tel'=>$tel?:null,'id'=>$userId]);
            }
            // Refresh
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            $_SESSION['user']['nom']   = $user['nom'];
            $_SESSION['user']['email'] = $user['email'];
            $message = 'Profil mis a jour avec succes !';
        }
    }
}

$avatarSrc = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : null;
$initiales = strtoupper(substr($user['nom'],0,1) . substr($user['prenom']??'',0,1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Profil - Takwinibot</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:#000;min-height:100vh;padding:32px 16px;color:#1a2e1c;position:relative;}
#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none;}
.glow-orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:1;}
.glow-1{width:500px;height:500px;background:radial-gradient(circle,rgba(34,197,94,.15) 0%,transparent 70%);top:-80px;left:-80px;animation:drift1 12s ease-in-out infinite;}
.glow-2{width:400px;height:400px;background:radial-gradient(circle,rgba(16,163,74,.12) 0%,transparent 70%);bottom:-60px;right:-60px;animation:drift2 15s ease-in-out infinite;}
@keyframes drift1{0%,100%{transform:translate(0,0);}50%{transform:translate(30px,20px);}}
@keyframes drift2{0%,100%{transform:translate(0,0);}50%{transform:translate(-20px,-30px);}}
.back-link{display:inline-flex;align-items:center;gap:6px;color:#4caf50;text-decoration:none;font-size:.9rem;font-weight:600;margin-bottom:20px;}
.back-link:hover{color:#2e7d32;}
.profil-wrap{max-width:700px;margin:0 auto;}
.card{background:#fff;border-radius:24px;box-shadow:0 8px 32px rgba(46,125,50,.12);overflow:hidden;}
.card-header{background:linear-gradient(135deg,#43a047,#1b5e20);padding:28px 36px 68px;text-align:center;}
.card-header h1{color:#fff;font-size:1.4rem;font-weight:800;}
.avatar-zone{display:flex;flex-direction:column;align-items:center;margin-top:-52px;padding-bottom:8px;}
.avatar-ring{position:relative;width:104px;height:104px;}
.avatar-ring img,.avatar-ring .av-init{width:104px;height:104px;border-radius:50%;border:4px solid #fff;box-shadow:0 4px 16px rgba(0,0,0,.15);object-fit:cover;}
.avatar-ring .av-init{background:#4caf50;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;}
.avatar-name{margin-top:10px;font-size:1.1rem;font-weight:700;color:#1a2e1c;}
.avatar-role{font-size:.82rem;color:#888;margin-top:3px;text-transform:capitalize;}
.avatar-btns{display:flex;gap:14px;width:100%;padding:10px 36px 0;}
.btn-av-label{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#43a047,#1b5e20);color:#fff;padding:14px 20px;border-radius:16px;cursor:pointer;font-size:15px;font-weight:700;min-height:60px;text-align:center;transition:opacity .2s;}
.btn-av-save{flex:1;background:linear-gradient(135deg,#66bb6a,#2e7d32);color:#fff;border:none;padding:14px 20px;border-radius:16px;font-size:15px;font-weight:700;cursor:pointer;min-height:60px;transition:opacity .2s;}
.card-body{padding:24px 36px 36px;}
.section-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#4caf50;padding-bottom:8px;border-bottom:2px solid #e8f5e9;margin:22px 0 16px;}
.field-row{display:flex;gap:14px;}
.field-row>.field{flex:1;}
.field{margin-bottom:14px;}
.field label{display:block;font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;}
.field input,.field select{width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.93rem;font-family:inherit;background:#f9fdf9;outline:none;transition:border-color .2s;}
.field input:focus,.field select:focus{border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.12);background:#fff;}
.radio-row{display:flex;gap:20px;padding:8px 0;}
.radio-row label{display:flex;align-items:center;gap:7px;font-size:.9rem;cursor:pointer;font-weight:500;}
.radio-row input[type=radio]{accent-color:#4caf50;width:15px;height:15px;}
.check-wrap{display:flex;align-items:center;gap:10px;background:#f9fdf9;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;margin:5px 0;cursor:pointer;}
.check-wrap input[type=checkbox]{width:16px;height:16px;accent-color:#4caf50;}
.btn-save{width:100%;padding:13px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:8px;transition:all .2s;}
.btn-save:hover{transform:translateY(-1px);}
.alert{padding:11px 14px;border-radius:10px;font-size:.88rem;margin-bottom:16px;font-weight:600;}
.alert-ok{background:#e8f5e9;color:#2e7d32;border-left:3px solid #4caf50;}
.alert-err{background:#fff8e1;color:#e65100;border-left:3px solid #ff9800;}
@media(max-width:520px){.field-row{flex-direction:column;}.card-body{padding:18px 20px 28px;}}
</style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="glow-orb glow-1"></div>
<div class="glow-orb glow-2"></div>
<div class="profil-wrap" style="position:relative;z-index:2;">
    <a href="/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/Esprit-PW-2A30-2627-TakwiniBot-gestion_formation/gestion_formation/View/front_office/formations/index.html" class="back-link">← Retour a l'accueil</a>
    <div class="card">
        <div class="card-header"><h1>Mon Profil</h1></div>

        <!-- Avatar -->
        <div class="avatar-zone">
            <div class="avatar-ring">
                <?php if($avatarSrc): ?>
                    <img src="<?=$avatarSrc?>" alt="Avatar" id="avatar-preview">
                <?php else: ?>
                    <div class="av-init" id="avatar-initials"><?=$initiales?:''?></div>
                <?php endif; ?>
            </div>
            <div class="avatar-name"><?=htmlspecialchars($user['nom'])?> <?=htmlspecialchars($user['prenom']??'')?></div>
            <div class="avatar-role"><?=htmlspecialchars($role)?></div>
        </div>

        <!-- Boutons avatar -->
        <form method="POST" enctype="multipart/form-data" id="avatar-form">
            <input type="hidden" name="action" value="upload_avatar">
            <input type="file" id="avatar-file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="previewAvatar(this)">
            <div class="avatar-btns">
                <label for="avatar-file" class="btn-av-label">
                    Changer l'avatar
                    <span id="avatar-msg" style="font-size:13px;font-weight:600;margin-top:4px;min-height:18px;"></span>
                </label>
                <button type="submit" class="btn-av-save">Enregistrer</button>
            </div>
        </form>

        <!-- Formulaire profil -->
        <div class="card-body">
            <?php if($message): ?><div class="alert alert-ok"><?=htmlspecialchars($message)?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-err"><?=htmlspecialchars($error)?></div><?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div class="section-title">Informations personnelles</div>
                <div class="field-row">
                    <div class="field"><label>Nom *</label><input type="text" name="nom" required value="<?=htmlspecialchars($user['nom'])?>"></div>
                    <div class="field"><label>Prenom</label><input type="text" name="prenom" value="<?=htmlspecialchars($user['prenom']??'')?>"></div>
                </div>
                <div class="field-row">
                    <div class="field"><label>Email *</label><input type="email" name="email" required value="<?=htmlspecialchars($user['email'])?>"></div>
                    <div class="field"><label>Telephone</label><input type="tel" name="telephone" placeholder="+216 XX XXX XXX" value="<?=htmlspecialchars($user['telephone']??'')?>"></div>
                </div>

                <?php if($role === 'candidat'): ?>
                <!-- Champs candidat -->
                <div class="section-title">Informations candidat</div>
                <div class="field-row">
                    <div class="field"><label>Date de naissance</label><input type="date" name="date_naissance" value="<?=htmlspecialchars($user['date_naissance']??'')?>"></div>
                    <div class="field">
                        <label>Genre</label>
                        <div class="radio-row">
                            <label><input type="radio" name="sexe" value="homme" <?=(($user['sexe']??'')==='homme')?'checked':''?>> Homme</label>
                            <label><input type="radio" name="sexe" value="femme" <?=(($user['sexe']??'')==='femme')?'checked':''?>> Femme</label>
                        </div>
                    </div>
                </div>
                <div class="field"><label>Adresse</label><input type="text" name="adresse" placeholder="Votre adresse" value="<?=htmlspecialchars($user['adresse']??'')?>"></div>
                <label class="check-wrap">
                    <input type="checkbox" name="handicap" id="handicap-cb" value="1" <?=!empty($user['handicap'])?'checked':''?> onchange="toggleHandicap(this)">
                    <span>Je suis en situation de handicap</span>
                </label>
                <div id="handicap-wrap" style="display:<?=!empty($user['handicap'])?'block':'none'?>;">
                    <div class="field"><label>Type de handicap</label><input type="text" name="type_handicap" placeholder="Moteur, visuel..." value="<?=htmlspecialchars($user['type_handicap']??'')?>"></div>
                </div>

                <?php elseif($role === 'recruteur'): ?>
                <!-- Champs recruteur -->
                <div class="section-title">Informations entreprise</div>
                <div class="field"><label>Nom de l'entreprise</label><input type="text" name="entreprise" value="<?=htmlspecialchars($user['entreprise']??'')?>"></div>
                <div class="field-row">
                    <div class="field"><label>Matricule fiscal</label><input type="text" name="matricule_fiscal" value="<?=htmlspecialchars($user['matricule_fiscal']??'')?>"></div>
                    <div class="field"><label>Secteur d'activite</label><input type="text" name="secteur" value="<?=htmlspecialchars($user['secteur']??'')?>"></div>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-save">Enregistrer les modifications</button>
            </form>

            <!-- ── SECTION FACE ID ─────────────────────────────────────── -->
            <div class="section-title" style="margin-top:28px;">Connexion par reconnaissance faciale</div>

            <?php
            // Vérifier si un descripteur facial existe déjà
            $faceStmt = $db->prepare('SELECT id FROM face_descriptors WHERE user_id = :uid LIMIT 1');
            $faceStmt->execute(['uid' => $userId]);
            $hasFace = (bool) $faceStmt->fetch();
            ?>

            <div style="background:#f9fdf9;border:1.5px solid #e0e0e0;border-radius:14px;padding:18px 20px;margin-bottom:8px;">
                <?php if ($hasFace): ?>
                <p style="font-size:14px;color:#2e7d32;font-weight:600;margin-bottom:14px;">
                    ✓ Visage enregistré — vous pouvez vous connecter avec votre visage.
                </p>
                <?php else: ?>
                <p style="font-size:14px;color:#888;margin-bottom:14px;">
                    Aucun visage enregistré. Ajoutez votre visage pour vous connecter sans mot de passe.
                </p>
                <?php endif; ?>

                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <button type="button" onclick="ouvrirCameraFace()"
                            style="flex:1;padding:12px 20px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;min-width:160px;">
                        <?= $hasFace ? 'Mettre à jour mon visage' : 'Ajouter mon visage' ?>
                    </button>
                    <?php if ($hasFace): ?>
                    <button type="button" onclick="supprimerFace()"
                            style="flex:1;padding:12px 20px;background:#fff;color:#e53935;border:1.5px solid #e53935;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;min-width:160px;">
                        Supprimer mon visage
                    </button>
                    <?php endif; ?>
                </div>
                <div id="face-status" style="font-size:13px;margin-top:10px;min-height:18px;font-weight:600;"></div>
            </div>

            <!-- Modal caméra Face ID -->
            <div id="face-modal-profil" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;">
                <div style="background:#fff;border-radius:20px;padding:28px;width:420px;max-width:95vw;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.3);">
                    <h3 style="margin-bottom:8px;font-size:18px;font-weight:800;color:#1a1a2e;">Scanner votre visage</h3>
                    <p style="font-size:13px;color:#888;margin-bottom:16px;">Placez votre visage dans le cercle</p>
                    <div style="position:relative;border-radius:16px;overflow:hidden;background:#000;margin-bottom:16px;">
                        <video id="face-video-profil" autoplay muted playsinline style="width:100%;display:block;border-radius:16px;"></video>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:180px;height:180px;border:3px solid #4caf50;border-radius:50%;box-shadow:0 0 0 9999px rgba(0,0,0,.4);pointer-events:none;"></div>
                    </div>
                    <div id="face-modal-msg-profil" style="font-size:13px;color:#888;margin-bottom:14px;min-height:18px;font-weight:600;"></div>
                    <button onclick="fermerCameraFace()" style="background:#f5f5f5;border:none;padding:10px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">Annuler</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Face ID profil ───────────────────────────────────────────────────────────
const MODEL_URL_P = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
let faceModelsLoadedP = false;
let faceStreamP = null;

async function chargerModelesFaceP() {
    if (faceModelsLoadedP) return true;
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL_P);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL_P);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL_P);
        faceModelsLoadedP = true;
        return true;
    } catch(e) { return false; }
}

async function ouvrirCameraFace() {
    const status = document.getElementById('face-status');
    status.style.color = '#888';
    status.textContent = 'Chargement des modèles IA...';

    const ok = await chargerModelesFaceP();
    if (!ok) { status.style.color = '#e53935'; status.textContent = 'Erreur chargement modèles.'; return; }

    const modal = document.getElementById('face-modal-profil');
    modal.style.display = 'flex';
    const msg = document.getElementById('face-modal-msg-profil');
    msg.textContent = 'Démarrage de la caméra...';

    try {
        faceStreamP = await navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 300, facingMode: 'user' } });
        const video = document.getElementById('face-video-profil');
        video.srcObject = faceStreamP;
        video.addEventListener('loadeddata', async () => {
            msg.textContent = 'Placez votre visage dans le cercle...';
            setTimeout(async () => {
                msg.textContent = 'Analyse en cours...';
                try {
                    const det = await faceapi
                        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.6 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!det) {
                        msg.style.color = '#e53935';
                        msg.textContent = 'Aucun visage détecté. Réessayez.';
                        setTimeout(() => fermerCameraFace(), 2000);
                        return;
                    }

                    msg.style.color = '#4caf50';
                    msg.textContent = 'Visage détecté ! Enregistrement...';
                    const descriptor = Array.from(det.descriptor);

                    // Envoyer au serveur
                    const res = await fetch('face-save.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ descriptor })
                    });
                    const data = await res.json();
                    fermerCameraFace();

                    const st = document.getElementById('face-status');
                    if (data.success) {
                        st.style.color = '#2e7d32';
                        st.textContent = '✓ Visage enregistré avec succès !';
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        st.style.color = '#e53935';
                        st.textContent = data.error || 'Erreur enregistrement.';
                    }
                } catch(e) {
                    fermerCameraFace();
                    document.getElementById('face-status').style.color = '#e53935';
                    document.getElementById('face-status').textContent = 'Erreur : ' + e.message;
                }
            }, 2000);
        });
    } catch(e) {
        fermerCameraFace();
        status.style.color = '#e53935';
        status.textContent = 'Caméra non accessible.';
    }
}

function fermerCameraFace() {
    if (faceStreamP) { faceStreamP.getTracks().forEach(t => t.stop()); faceStreamP = null; }
    document.getElementById('face-modal-profil').style.display = 'none';
    document.getElementById('face-modal-msg-profil').textContent = '';
    document.getElementById('face-modal-msg-profil').style.color = '#888';
}

async function supprimerFace() {
    if (!confirm('Supprimer votre visage enregistré ?')) return;
    const res = await fetch('face-delete.php', { method: 'POST' });
    const data = await res.json();
    const st = document.getElementById('face-status');
    if (data.success) {
        st.style.color = '#2e7d32';
        st.textContent = 'Visage supprimé.';
        setTimeout(() => location.reload(), 1000);
    } else {
        st.style.color = '#e53935';
        st.textContent = data.error || 'Erreur suppression.';
    }
}

function previewAvatar(input) {
    const msg = document.getElementById('avatar-msg');
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) { msg.textContent='Format non autorise!'; msg.style.color='#ff3e1d'; return; }
    if (file.size > 2*1024*1024) { msg.textContent='Fichier trop grand!'; msg.style.color='#ff3e1d'; return; }
    const reader = new FileReader();
    reader.onload = function(e) {
        const ring = document.querySelector('.avatar-ring');
        let img = ring.querySelector('img');
        if (!img) { ring.innerHTML='<img id="avatar-preview" style="width:104px;height:104px;border-radius:50%;border:4px solid #fff;object-fit:cover;">'; img=ring.querySelector('img'); }
        img.src = e.target.result;
        msg.textContent = 'Photo selectionnee ✓'; msg.style.color='#4caf50';
    };
    reader.readAsDataURL(file);
}
function toggleHandicap(cb) { document.getElementById('handicap-wrap').style.display = cb.checked ? 'block' : 'none'; }
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
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
    const points=new THREE.Points(geo,mat);
    scene.add(points);
    function animate(){requestAnimationFrame(animate);points.rotation.y+=.0005;points.rotation.x+=.0002;renderer.render(scene,camera);}
    animate();
    window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight);});
})();
</script>
</body>
</html>
