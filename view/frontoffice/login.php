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
                    header('Location: /gestion_utilisateur_v5/gestion_utilisateur1/view/backoffice/sneat-plateforme-finale/sneat-final/html/index.php');
                } else {
                    header('Location: /gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/formations/index.php');
                }
                exit;
            } else {
                $error_login = $result['message'];
                if ($result['action'] === 'not_found') $active_panel = 'register';
            }
        }
    } elseif ($action === 'register_unified') {
        $active_panel = 'register';
        $role         = $_POST['role']       ?? 'candidat';
        $nom          = trim($_POST['nom']   ?? '');
        $prenom       = trim($_POST['prenom']?? '');
        $email        = trim($_POST['email'] ?? '');
        $password     = $_POST['password']   ?? '';
        $telephone    = trim($_POST['telephone'] ?? '');
        $face_descriptor = $_POST['face_descriptor'] ?? '';
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error_register = 'Les champs nom, prenom, email et mot de passe sont obligatoires.';
        } elseif (strlen($password) < 6) {
            $error_register = 'Le mot de passe doit contenir au moins 6 caracteres.';
        } else {
            if ($role === 'candidat') {
                $sexe           = $_POST['sexe']           ?? '';
                $date_naissance = $_POST['date_naissance'] ?? '';
                $adresse        = trim($_POST['adresse']   ?? '');
                $handicap       = !empty($_POST['handicap']) ? 1 : 0;
                $type_handicap  = $handicap ? trim($_POST['type_handicap'] ?? '') : null;
                $result = $controller->register($nom, $prenom, $email, $password, $telephone, $sexe, $date_naissance, $adresse, $handicap, $type_handicap, $face_descriptor);
                if ($result['success']) {
                    $_SESSION['user'] = $result['user'];
                    header('Location: /gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/formations/index.php');
                    exit;
                } else {
                    $error_register = $result['message'];
                    if ($result['action'] === 'already_exists') {
                        $active_panel = 'login';
                        $error_login  = $result['message'];
                        $error_register = '';
                    }
                }
            } else {
                $entreprise = trim($_POST['entreprise']       ?? '');
                $matricule  = trim($_POST['matricule_fiscal'] ?? '');
                $secteur    = trim($_POST['secteur']          ?? '');
                if (empty($entreprise) || empty($matricule)) {
                    $error_register = 'Tous les champs obligatoires doivent etre remplis.';
                } else {
                    try {
                        $db = config::getConnexion();
                        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
                        $check->execute(['email' => $email]);
                        if ($check->fetch()) {
                            $error_register = 'Cet email est deja utilise.';
                        } else {
                            $hashed  = password_hash($password, PASSWORD_BCRYPT);
                            $docPath = null;
                            if (isset($_FILES['document_entreprise']) && $_FILES['document_entreprise']['error'] === 0) {
                                $ext = strtolower(pathinfo($_FILES['document_entreprise']['name'], PATHINFO_EXTENSION));
                                if (in_array($ext, ['pdf','jpg','jpeg','png']) && $_FILES['document_entreprise']['size'] <= 5*1024*1024) {
                                    $dir = __DIR__ . '/uploads/documents/';
                                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                                    $filename = 'doc_' . time() . '_' . uniqid() . '.' . $ext;
                                    if (move_uploaded_file($_FILES['document_entreprise']['tmp_name'], $dir . $filename))
                                        $docPath = 'uploads/documents/' . $filename;
                                }
                            }
                            $db->prepare('INSERT INTO users (nom,prenom,email,mot_de_passe,telephone,role,statut,entreprise,matricule_fiscal,secteur,document_entreprise) VALUES (:nom,:prenom,:email,:mdp,:tel,:role,:statut,:entreprise,:matricule,:secteur,:doc)')
                               ->execute(['nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'mdp'=>$hashed,'tel'=>$telephone?:null,'role'=>'recruteur','statut'=>'en_attente','entreprise'=>$entreprise,'matricule'=>$matricule,'secteur'=>$secteur?:null,'doc'=>$docPath]);
                            $newId = $db->lastInsertId();
                            if (!empty($face_descriptor)) {
                                $db->prepare('INSERT INTO face_descriptors (user_id, descriptor) VALUES (:uid, :desc)')
                                   ->execute(['uid' => $newId, 'desc' => $face_descriptor]);
                            }
                            $db->prepare('INSERT INTO notifications (titre,message,type,lien) VALUES (:t,:m,:ty,:l)')
                               ->execute(['t'=>'Nouveau recruteur en attente','m'=>$nom.' '.$prenom.' ('.$entreprise.') - a valider','ty'=>'recruteur','l'=>'gestion-recruteurs.php']);
                            $success      = 'Demande envoyee ! Un administrateur validera votre compte sous 48h.';
                            $active_panel = 'login';
                        }
                    } catch (Exception $e) {
                        $error_register = 'Erreur : ' . $e->getMessage();
                    }
                }
            }
        }
    }
}
$isRegister = ($active_panel === 'register');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Takwini — Connexion</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ── Reset & variables ── */
*{margin:0;padding:0;box-sizing:border-box;}
:root{--green:#22c55e;--green-dark:#16a34a;--green-deep:#052e16;}
html,body{height:100%;font-family:'DM Sans',sans-serif;}
body{background:#000000;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;position:relative;}

/* ── 3D Canvas background ── */
#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none;display:block;}

/* ── Ambient glows ── */
.glow-orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:1;}
.glow-1{width:600px;height:600px;background:radial-gradient(circle,rgba(34,197,94,.18) 0%,transparent 70%);top:-100px;left:-100px;animation:drift1 12s ease-in-out infinite;}
.glow-2{width:500px;height:500px;background:radial-gradient(circle,rgba(16,163,74,.14) 0%,transparent 70%);bottom:-80px;right:-80px;animation:drift2 15s ease-in-out infinite;}
@keyframes drift1{0%,100%{transform:translate(0,0);}50%{transform:translate(40px,30px);}}
@keyframes drift2{0%,100%{transform:translate(0,0);}50%{transform:translate(-30px,-40px);}}
.container{background:#fff;border-radius:30px;box-shadow:0 8px 32px rgba(0,0,0,.12);position:relative;overflow:hidden;width:1100px;max-width:100%;min-height:700px;display:flex;z-index:2;}
.form-container{position:absolute;top:0;left:0;width:50%;height:100%;overflow-y:auto;transition:transform .6s ease,opacity .6s ease;}
.sign-in{z-index:2;transform:translateX(0);opacity:1;}
.sign-up{z-index:1;transform:translateX(-100%);opacity:0;pointer-events:none;}
.container.active .sign-in{transform:translateX(100%);opacity:0;pointer-events:none;z-index:1;}
.container.active .sign-up{transform:translateX(100%);opacity:1;pointer-events:all;z-index:2;}
.form-container form{display:flex;align-items:center;justify-content:center;flex-direction:column;padding:20px 40px 30px;min-height:100%;}
.form-container h1{font-size:24px;font-weight:800;color:#1a1a2e;margin-bottom:2px;}
.subtitle{font-size:12px;color:#888;margin-bottom:14px;text-align:center;}
.input-wrap{width:100%;position:relative;margin:4px 0;}
.input-wrap input{width:100%;padding:11px 16px;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:10px;font-size:13px;outline:none;transition:all .25s;color:#1a1a2e;}
.input-wrap input:focus{background:#d4edda;border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
.container input[type=file]{width:100%;padding:10px 14px;background:#e8f5e9;border:1px dashed #c8e6c9;border-radius:10px;font-size:13px;cursor:pointer;margin:5px 0;}
.btn-main{width:100%;padding:14px;margin-top:14px;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(76,175,80,.35);}
.btn-main:hover{transform:translateY(-2px);}
.container a{color:#4caf50;font-size:13px;text-decoration:none;margin:10px 0 4px;font-weight:500;}
.form-row{display:flex;gap:10px;width:100%;}
.form-row>div{flex:1;}
.role-selector{display:flex;gap:0;width:100%;margin:6px 0 14px;border-radius:12px;overflow:hidden;border:2px solid #c8e6c9;}
.role-selector label{display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;font-weight:600;color:#888;background:#f5f5f5;padding:11px 18px;flex:1;transition:all .2s;}
.role-selector label:has(input:checked){background:#4caf50;color:#fff;}
.role-selector input[type=radio]{display:none;}
.radio-group{display:flex;gap:10px;width:100%;margin:4px 0;}
.radio-group label{display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:500;color:#555;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:10px;padding:9px 14px;flex:1;transition:all .2s;}
.radio-group label:has(input:checked){border-color:#4caf50;background:#d4edda;color:#2e7d32;}
.radio-group input[type=radio]{accent-color:#4caf50;}
.check-wrap{width:100%;display:flex;align-items:center;gap:10px;background:#e8f5e9;border:1px solid #c8e6c9;border-radius:10px;padding:10px 14px;margin:5px 0;cursor:pointer;}
.check-wrap input[type=checkbox]{width:16px;height:16px;accent-color:#4caf50;}
.check-wrap span{font-size:13px;font-weight:500;color:#555;}
.field-error{color:#e53935;font-size:11px;margin:0 0 2px 4px;min-height:12px;display:block;}
.input-error input{border-color:#e53935!important;background:#fff5f5!important;}
.alert{width:100%;padding:10px 14px;border-radius:10px;font-size:13px;margin:6px 0;display:flex;align-items:center;gap:8px;}
.alert-danger{background:#fde8e8;color:#c0392b;border-left:3px solid #e53935;}
.alert-success{background:#e8f5e9;color:#2e7d32;border-left:3px solid #4caf50;}
.section-label{width:100%;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#aaa;margin:10px 0 4px;}
.recruteur-note{width:100%;padding:10px 14px;border-radius:10px;font-size:12px;background:#fff8e1;color:#f57f17;border-left:3px solid #ffc107;margin:6px 0;line-height:1.5;}
.fields-candidat,.fields-recruteur{width:100%;overflow:hidden;transition:opacity .35s ease,max-height .4s ease,transform .35s ease;max-height:600px;opacity:1;transform:translateY(0);}
.fields-hidden{max-height:0!important;opacity:0!important;transform:translateY(-8px)!important;pointer-events:none;}
.toggle-container{position:absolute;top:0;left:50%;width:50%;height:100%;overflow:hidden;transition:all .6s cubic-bezier(.68,-.55,.27,1.55);border-radius:120px 0 0 120px;z-index:10;}
.container.active .toggle-container{transform:translateX(-100%);border-radius:0 120px 120px 0;}
.toggle{background:linear-gradient(160deg,#1b5e20 0%,#2e7d32 35%,#43a047 70%,#66bb6a 100%);color:#fff;position:relative;left:-100%;height:100%;width:200%;transform:translateX(0);transition:all .6s cubic-bezier(.68,-.55,.27,1.55);}
.container.active .toggle{transform:translateX(50%);}
.toggle-panel{position:absolute;width:50%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:40px 36px;text-align:center;top:0;transition:all .6s ease-in-out;}
.toggle-panel h1{font-size:28px;font-weight:800;margin-bottom:12px;}
.toggle-panel p{font-size:14px;opacity:.85;line-height:1.6;margin-bottom:28px;}
.toggle-left{transform:translateX(-200%);}
.container.active .toggle-left{transform:translateX(0);}
.toggle-right{right:0;transform:translateX(0);}
.container.active .toggle-right{transform:translateX(200%);}
.btn-toggle{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.7);border-radius:50px;padding:11px 36px;font-size:14px;font-weight:700;cursor:pointer;transition:all .25s;width:200px;margin:6px 0;}
.btn-toggle:hover{background:rgba(255,255,255,.15);border-color:#fff;transform:translateY(-2px);}
.toggle-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border-radius:50px;padding:6px 16px;font-size:12px;font-weight:600;margin-bottom:20px;}

/* Soulignement animé mot de passe oublié */
.forgot-link{
  position:relative;
  display:inline-block;
}
.forgot-link::after{
  content:'';
  position:absolute;
  left:0;
  bottom:-2px;
  width:0%;
  height:1.5px;
  background:#4caf50;
  border-radius:2px;
  transition:width .3s ease;
}
.forgot-link:hover::after{
  width:100%;
}
</style>
</head>
<body>
<!-- Canvas 3D background -->
<canvas id="bg-canvas"></canvas>
<div class="glow-orb glow-1"></div>
<div class="glow-orb glow-2"></div>
<!-- Skip link invisible sauf au focus clavier -->
<a href="#main-form"
   style="position:fixed;top:-60px;left:16px;background:#2e7d32;color:#fff;padding:10px 18px;border-radius:0 0 8px 8px;font-weight:700;font-size:14px;z-index:99999;transition:top .2s;text-decoration:none;"
   onfocus="this.style.top='0'" onblur="this.style.top='-60px'">
  Aller au formulaire
</a>
<?php $cls = 'container' . ($isRegister ? ' active' : ''); ?>
<div class="<?= $cls ?>" id="container">

  <!-- CONNEXION -->
  <div class="form-container sign-in">
    <form method="POST" action="login.php">
      <input type="hidden" name="action" value="login">
      <h1>Bon retour !</h1>
      <p class="subtitle">Connectez-vous a votre espace</p>
      <?php if($error_login):?><div class="alert alert-danger"><?=htmlspecialchars($error_login)?></div><?php endif;?>
      <?php if($success):?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif;?>
      <div class="input-wrap">
        <input type="text" name="email" id="login-email" placeholder="Adresse email" value="<?=htmlspecialchars(($_POST['action']??'')==='login'?($_POST['email']??''):'')?>">
      </div>
      <span class="field-error" id="err-login-email"></span>
      <div class="input-wrap" style="position:relative;">
        <input type="password" name="password" id="login-password" placeholder="Mot de passe" style="padding-right:40px;">
        <img src="../../nonn.png" id="eye-login" onclick="togglePwd('login-password',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;width:20px;height:20px;object-fit:contain;" data-show="../../ouii.png" data-hide="../../nonn.png">
      </div>
      <span class="field-error" id="err-login-password"></span>
      <div style="width:100%;text-align:right;margin-top:4px;">
        <a href="forgot-password.php" class="forgot-link"
           style="font-size:12px;color:#4caf50;text-decoration:none;font-weight:500;">
          Mot de passe oublié ?
        </a>
      </div>
      <button type="submit" class="btn-main" id="btn-login" style="margin-top:20px;">Se connecter</button>

      <!-- Bouton assistance vocale -->
      <button type="button" onclick="activerAssistanceBouton('login')"
              style="width:100%;margin-top:10px;padding:13px;background:#1b5e20;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.3px;">
        Je ne vois pas — Assistance vocale
      </button>
      <div style="display:flex;align-items:center;gap:10px;width:100%;margin:10px 0 4px;">
        <div style="flex:1;height:1px;background:#e0e0e0;"></div>
        <span style="font-size:11px;color:#aaa;font-weight:600;">OU</span>
        <div style="flex:1;height:1px;background:#e0e0e0;"></div>
      </div>

      <!-- Bouton Face ID -->
      <button type="button" id="btn-faceid"
              style="width:100%;padding:12px;background:#fff;border:2px solid #e0e0e0;border-radius:12px;
                     font-size:14px;font-weight:600;color:#555;cursor:pointer;display:flex;align-items:center;
                     justify-content:center;gap:10px;transition:all .25s;margin-top:4px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2">
              <circle cx="12" cy="8" r="3"/>
              <path d="M6 20v-1a6 6 0 0 1 12 0v1"/>
              <path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
          </svg>
          Se connecter avec le visage
      </button>
      <div id="faceid-msg" style="font-size:12px;text-align:center;margin-top:6px;min-height:16px;"></div>

      <!-- Modal caméra Face ID -->
      <div id="face-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;">
          <div style="background:#fff;border-radius:20px;padding:28px;width:420px;max-width:95vw;text-align:center;">
              <h3 style="margin-bottom:8px;font-size:18px;font-weight:800;">Reconnaissance faciale</h3>
              <p style="font-size:13px;color:#888;margin-bottom:16px;">Placez votre visage dans le cercle</p>
              <div style="position:relative;border-radius:16px;overflow:hidden;background:#000;margin-bottom:16px;">
                  <video id="face-video" autoplay muted playsinline style="width:100%;display:block;border-radius:16px;"></video>
                  <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:180px;height:180px;border:3px solid #4caf50;border-radius:50%;box-shadow:0 0 0 9999px rgba(0,0,0,.4);pointer-events:none;" id="face-circle"></div>
              </div>
              <div id="face-modal-msg" style="font-size:13px;color:#888;margin-bottom:12px;min-height:18px;font-weight:600;"></div>
              <button onclick="fermerFaceModal()" style="background:#f5f5f5;border:none;padding:10px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">Annuler</button>
          </div>
      </div>
    </form>
  </div>

  <!-- INSCRIPTION -->
  <div class="form-container sign-up">
    <form method="POST" action="login.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="register_unified">
      <input type="hidden" name="face_descriptor" id="reg-face-descriptor">
      <h1>Creer un compte</h1>
      <p class="subtitle">Rejoignez la communaute Takwini</p>
      <?php if($error_register && $isRegister):?><div class="alert alert-danger"><?=htmlspecialchars($error_register)?></div><?php endif;?>
      <div class="section-label">Je suis</div>
      <div class="role-selector">
        <label><input type="radio" name="role" value="candidat" <?=(($_POST['role']??'candidat')==='candidat')?'checked':''?> onchange="switchRole('candidat')"> Candidat</label>
        <label><input type="radio" name="role" value="recruteur" <?=(($_POST['role']??'')==='recruteur')?'checked':''?> onchange="switchRole('recruteur')"> Recruteur</label>
      </div>
      <div class="form-row">
        <div><div class="input-wrap"><input type="text" name="nom" id="reg-nom" placeholder="Nom *" value="<?=htmlspecialchars($_POST['nom']??'')?>"></div><span class="field-error" id="err-reg-nom"></span></div>
        <div><div class="input-wrap"><input type="text" name="prenom" id="reg-prenom" placeholder="Prenom *" value="<?=htmlspecialchars($_POST['prenom']??'')?>"></div><span class="field-error" id="err-reg-prenom"></span></div>
      </div>
      <div class="input-wrap"><input type="text" name="email" id="reg-email" placeholder="Email *" value="<?=htmlspecialchars(($_POST['action']??'')==='register_unified'?($_POST['email']??''):'')?>"></div>
      <span class="field-error" id="err-reg-email"></span>
      <div class="input-wrap" style="position:relative;">
        <input type="password" name="password" id="reg-password" placeholder="Mot de passe (min. 6 car.) *" style="padding-right:40px;">
        <img src="../../nonn.png" onclick="togglePwd('reg-password',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;width:20px;height:20px;object-fit:contain;" data-show="../../ouii.png" data-hide="../../nonn.png">
      </div>
      <span class="field-error" id="err-reg-password"></span>
      <div class="input-wrap"><input type="text" name="telephone" placeholder="Telephone" value="<?=htmlspecialchars($_POST['telephone']??'')?>"></div>

      <!-- Champs Candidat -->
      <div id="fields-candidat" class="fields-candidat <?=(($_POST['role']??'candidat')==='recruteur')?'fields-hidden':''?>">
        <div class="form-row">
          <div><div class="input-wrap"><input type="date" name="date_naissance" value="<?=htmlspecialchars($_POST['date_naissance']??'')?>"></div></div>
          <div>
            <div class="radio-group">
              <label><input type="radio" name="sexe" value="homme" <?=(($_POST['sexe']??'')==='homme')?'checked':''?>> Homme</label>
              <label><input type="radio" name="sexe" value="femme" <?=(($_POST['sexe']??'')==='femme')?'checked':''?>> Femme</label>
            </div>
            <span class="field-error" id="err-reg-sexe"></span>
          </div>
        </div>
        <div class="input-wrap"><input type="text" name="adresse" placeholder="Adresse" value="<?=htmlspecialchars($_POST['adresse']??'')?>"></div>
        <label class="check-wrap"><input type="checkbox" name="handicap" id="reg-handicap" value="1" <?=!empty($_POST['handicap'])?'checked':''?> onchange="toggleHandicap(this)"><span>Je suis en situation de handicap</span></label>
        <div id="handicap-desc-wrap" style="width:100%;display:<?=!empty($_POST['handicap'])?'block':'none'?>;"><div class="input-wrap"><input type="text" name="type_handicap" placeholder="Type de handicap" value="<?=htmlspecialchars($_POST['type_handicap']??'')?>"></div></div>
      </div>

      <!-- Champs Recruteur -->
      <div id="fields-recruteur" class="fields-recruteur <?=(($_POST['role']??'')==='recruteur')?'':'fields-hidden'?>">
        <div class="section-label">Informations entreprise</div>
        <div class="input-wrap"><input type="text" name="entreprise" id="rec-entreprise" placeholder="Nom de l'entreprise *" value="<?=htmlspecialchars($_POST['entreprise']??'')?>"></div>
        <span class="field-error" id="err-rec-entreprise"></span>
        <div class="form-row">
          <div>
            <div class="input-wrap" style="position:relative;"><input type="text" name="matricule_fiscal" id="rec-matricule" placeholder="Ex: 1182431M/A/M/000" value="<?=htmlspecialchars($_POST['matricule_fiscal']??'')?>" oninput="validerMatricule(this)" maxlength="20" style="padding-right:38px;text-transform:uppercase;letter-spacing:1px;"><span id="matricule-icon" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:16px;"></span></div>
            <span class="field-error" id="err-rec-matricule"></span>
            <div id="matricule-badge" style="display:none;margin-top:5px;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:600;"></div>
          </div>
          <div><div class="input-wrap"><input type="text" name="secteur" placeholder="Secteur d'activite" value="<?=htmlspecialchars($_POST['secteur']??'')?>"></div></div>
        </div>
        <div style="width:100%;margin:5px 0;"><label style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px;">Document officiel</label><input type="file" name="document_entreprise" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="recruteur-note">Votre compte sera active apres validation par un administrateur (sous 48h).</div>
      </div>

      <button type="button" id="btn-face-register"
              style="width:100%;padding:12px;background:#fff;border:2px dashed #4caf50;border-radius:12px;
                     font-size:14px;font-weight:600;color:#2e7d32;cursor:pointer;display:flex;align-items:center;
                     justify-content:center;gap:10px;transition:all .25s;margin-bottom:10px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2">
              <circle cx="12" cy="8" r="3"/>
              <path d="M6 20v-1a6 6 0 0 1 12 0v1"/>
              <path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
          </svg>
          Scanner mon visage (Optionnel)
      </button>

      <button type="submit" class="btn-main">S'inscrire</button>

      <!-- Bouton assistance vocale inscription -->
      <button type="button" id="btn-aveugle-register" onclick="activerAssistanceBouton('register')"
              style="width:100%;margin-top:10px;padding:13px;background:#1b5e20;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.3px;">
        Je ne vois pas — Assistance vocale
      </button>
    </form>
  </div>

  <!-- TOGGLE -->
  <div class="toggle-container">
    <div class="toggle">
      <div class="toggle-panel toggle-left">
        <div class="toggle-badge">Deja membre ?</div>
        <h1>Bon retour !</h1>
        <p>Connectez-vous pour acceder a toutes les fonctionnalites</p>
        <button class="btn-toggle" id="loginBtn">Se connecter</button>
      </div>
      <div class="toggle-panel toggle-right">
        <div class="toggle-badge">Nouveau ici ?</div>
        <h1>Rejoignez-nous !</h1>
        <p>Creez votre compte et accedez aux formations et offres d'emploi</p>
        <button class="btn-toggle" id="registerBtn">S'inscrire</button>
      </div>
    </div>
  </div>

</div>

<script>
// Toggle panels
const container = document.getElementById('container');
document.getElementById('registerBtn').addEventListener('click', function(){ container.classList.add('active'); });
document.getElementById('loginBtn').addEventListener('click', function(){ container.classList.remove('active'); });

// Role switch
function switchRole(role){
    document.getElementById('fields-candidat').classList.toggle('fields-hidden', role !== 'candidat');
    document.getElementById('fields-recruteur').classList.toggle('fields-hidden', role !== 'recruteur');
}

// Handicap
function toggleHandicap(cb){ document.getElementById('handicap-desc-wrap').style.display = cb.checked ? 'block' : 'none'; }

// Eye toggle
function togglePwd(id, icon){
    const inp = document.getElementById(id);
    icon.style.transition='transform .15s ease,opacity .15s ease';
    icon.style.transform='translateY(-50%) scale(0.7)'; icon.style.opacity='0.4';
    setTimeout(()=>{ icon.style.transform='translateY(-50%) scale(1.15)'; icon.style.opacity='1'; setTimeout(()=>{ icon.style.transform='translateY(-50%) scale(1)'; },100); },120);
    if(inp.type==='password'){ inp.type='text'; icon.src=icon.dataset.show; } else { inp.type='password'; icon.src=icon.dataset.hide; }
}

// Validation
function setError(el,span,msg){ el.classList.add('input-error'); span.textContent=msg; }
function clearErr(el,span){ el.classList.remove('input-error'); span.textContent=''; }
function isEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

document.getElementById('btn-login').addEventListener('click',function(e){
    const em=document.getElementById('login-email'), pw=document.getElementById('login-password');
    const eE=document.getElementById('err-login-email'), eP=document.getElementById('err-login-password');
    let ok=true;
    if(!em.value.trim()){setError(em,eE,"Email obligatoire.");ok=false;} else if(!isEmail(em.value)){setError(em,eE,"Format invalide.");ok=false;} else clearErr(em,eE);
    if(!pw.value){setError(pw,eP,"Mot de passe obligatoire.");ok=false;} else if(pw.value.length<6){setError(pw,eP,"Minimum 6 caracteres.");ok=false;} else clearErr(pw,eP);
    if(!ok) e.preventDefault();
});

// Matricule validation
function validerMatricule(input){
    const val=input.value.toUpperCase().trim(); input.value=val;
    const badge=document.getElementById('matricule-badge'), icon=document.getElementById('matricule-icon');
    const regex=/^\d{6,8}[A-Z]?\s*(\/[A-Z]+){1,3}\/\d{3}$|^\d{6,8}\s+([A-Z]\s+){1,3}\d{3}$/;
    if(!val){ badge.style.display='none'; icon.textContent=''; return; }
    if(regex.test(val)){ badge.style.display='block'; badge.style.background='#e8f5e9'; badge.style.color='#2e7d32'; badge.style.border='1px solid #c8e6c9'; badge.innerHTML='Format valide'; icon.textContent='OK'; }
    else { badge.style.display='block'; badge.style.background='#fde8e8'; badge.style.color='#c0392b'; badge.style.border='1px solid #f5c6c6'; badge.innerHTML='Format invalide'; icon.textContent='X'; }
}

// Biometric
function b64ToBuffer(b){ const base64=b.replace(/-/g,'+').replace(/_/g,'/'); const bin=atob(base64); return Uint8Array.from(bin,c=>c.charCodeAt(0)).buffer; }
function bufToB64(buf){ const bytes=new Uint8Array(buf); let s=''; bytes.forEach(b=>s+=String.fromCharCode(b)); return btoa(s).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,''); }

// ── Face ID avec caméra ─────────────────────────────────────────────────────
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
let faceModelsLoaded = false;
let faceStream = null;

async function chargerModelesFace() {
    if (faceModelsLoaded) return true;
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        faceModelsLoaded = true;
        return true;
    } catch(e) { return false; }
}

let faceScanMode = 'login'; // 'login' or 'register'

document.getElementById('btn-faceid').addEventListener('click', async function() {
    const emailInput = document.getElementById('login-email').value.trim();
    const msg = document.getElementById('faceid-msg');
    
    if (!emailInput) {
        msg.style.color = '#e53935';
        msg.textContent = "Veuillez saisir votre email d'abord.";
        return;
    }

    faceScanMode = 'login';
    msg.style.color = '#888';
    msg.textContent = 'Chargement des modèles IA...';
    await openCameraModal(msg);
});

const btnFaceReg = document.getElementById('btn-face-register');
if (btnFaceReg) {
    btnFaceReg.addEventListener('click', async function() {
        faceScanMode = 'register';
        const msg = document.createElement('div'); // Dummy msg element for register
        await openCameraModal(msg);
    });
}

async function openCameraModal(msgElement) {
    const ok = await chargerModelesFace();
    if (!ok) { msgElement.style.color='#e53935'; msgElement.textContent='Erreur chargement modèles.'; return; }
    const modal = document.getElementById('face-modal');
    modal.style.display = 'flex';
    const faceMsg = document.getElementById('face-modal-msg');
    faceMsg.textContent = 'Démarrage de la caméra...';
    try {
        faceStream = await navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 300, facingMode: 'user' } });
        const faceVideo = document.getElementById('face-video');
        faceVideo.srcObject = faceStream;
        faceVideo.addEventListener('loadeddata', async () => {
            faceMsg.textContent = 'Placez votre visage dans le cercle...';
            setTimeout(async () => {
                faceMsg.textContent = 'Analyse en cours...';
                try {
                    const det = await faceapi.detectSingleFace(faceVideo, new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.6 })).withFaceLandmarks().withFaceDescriptor();
                    if (!det) { faceMsg.style.color='#e53935'; faceMsg.textContent='Aucun visage détecté.'; setTimeout(()=>fermerFaceModal(),2000); return; }
                    faceMsg.style.color='#4caf50'; faceMsg.textContent='Visage détecté ! Traitement...';
                    const descriptor = Array.from(det.descriptor);
                    
                    if (faceScanMode === 'register') {
                        document.getElementById('reg-face-descriptor').value = JSON.stringify(descriptor);
                        const btn = document.getElementById('btn-face-register');
                        btn.innerHTML = '✅ Visage enregistré';
                        btn.style.borderColor = '#4caf50';
                        btn.style.background = '#e8f5e9';
                        fermerFaceModal();
                    } else {
                        const email = document.getElementById('login-email').value.trim();
                        const res  = await fetch('face-login.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({descriptor, email}) });
                        const data = await res.json();
                        fermerFaceModal();
                        if (data.success) { msgElement.style.color='#4caf50'; msgElement.textContent='✅ Connecté ! Redirection...'; setTimeout(()=>window.location.href=data.redirect,800); }
                        else { msgElement.style.color='#e53935'; msgElement.textContent=data.error||'Visage non reconnu.'; }
                    }
                } catch(e) { fermerFaceModal(); if(msgElement) { msgElement.style.color='#e53935'; msgElement.textContent='Erreur : '+e.message; } }
            }, 2000);
        });
    } catch(e) { fermerFaceModal(); if(msgElement) { msgElement.style.color='#e53935'; msgElement.textContent='Caméra non accessible.'; } }
}

function fermerFaceModal() {
    if (faceStream) { faceStream.getTracks().forEach(t=>t.stop()); faceStream=null; }
    document.getElementById('face-modal').style.display='none';
    document.getElementById('face-modal-msg').textContent='';
    document.getElementById('face-modal-msg').style.color='#888';
}

// ══════════════════════════════════════════════════════════════════════════════
// ASSISTANCE VOCALE POUR NON-VOYANTS
// ══════════════════════════════════════════════════════════════════════════════
let modeAveugle = false;
let etapeActuelle = 0;
let etapesCourantes = [];

// ── Arrêt global : clic souris = utilisateur voyant → tout stopper ───────────
document.addEventListener('mousedown', function() {
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        modeAveugle = false;
    }
});

function parler(texte, callback) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    const msg = new SpeechSynthesisUtterance(texte);
    msg.lang = 'fr-FR'; msg.rate = 0.9; msg.pitch = 1.1; msg.volume = 1;
    if (callback) msg.onend = callback;
    window.speechSynthesis.speak(msg);
}

const etapesLogin = [
    { id: 'login-email',    message: 'Écrivez votre adresse email, puis appuyez sur Entrée.' },
    { id: 'login-password', message: 'Écrivez votre mot de passe, puis appuyez sur Entrée.' },
    { id: 'btn-login',      message: 'Appuyez sur Entrée pour vous connecter.' }
];

const etapesRegister = [
    { id: 'reg-nom',       message: 'Écrivez votre nom de famille, puis appuyez sur Entrée.' },
    { id: 'reg-prenom',    message: 'Écrivez votre prénom, puis appuyez sur Entrée.' },
    { id: 'reg-email',     message: 'Écrivez votre adresse email, puis appuyez sur Entrée.' },
    { id: 'reg-password',  message: 'Choisissez un mot de passe d\'au moins 6 caractères, puis appuyez sur Entrée.' },
    { id: 'reg-telephone', message: 'Écrivez votre numéro de téléphone. Optionnel. Appuyez sur Entrée pour continuer.' },
    { id: 'reg-dob',       message: 'Écrivez votre date de naissance au format jour slash mois slash année, puis appuyez sur Entrée.' },
    { id: 'sexe-homme',    message: 'Choisissez votre sexe. Appuyez sur H pour Homme, ou F pour Femme.' },
    { id: 'reg-adresse',   message: 'Écrivez votre adresse. Optionnel. Appuyez sur Entrée pour continuer.' },
    { id: 'btn-aveugle-register', message: 'Formulaire complet. Appuyez sur Entrée pour soumettre votre inscription.' }
];

function activerModeAveugle(mode) {
    modeAveugle = true;
    etapeActuelle = 0;
    document.addEventListener('keydown', gererEntreeAveugle);

    if (mode === 'register') {
        // Basculer vers le panneau inscription
        container.classList.add('active');
        // Cocher automatiquement handicap visuel
        setTimeout(function() {
            const cbHandicap = document.getElementById('reg-handicap');
            if (cbHandicap) { cbHandicap.checked = true; toggleHandicap(cbHandicap); }
            const typeHandicap = document.getElementById('reg-type-handicap');
            if (typeHandicap) typeHandicap.value = 'Visuel';
            etapesCourantes = etapesRegister;
            parler('Mode assistance activé. La case handicap visuel a été cochée automatiquement. ' + etapesRegister[0].message, function() {
                const el = document.getElementById(etapesRegister[0].id);
                if (el) el.focus();
            });
        }, 500);
    } else {
        etapesCourantes = etapesLogin;
        parler('Mode assistance activé. ' + etapesLogin[0].message, function() {
            const el = document.getElementById(etapesLogin[0].id);
            if (el) el.focus();
        });
    }
}

function gererEntreeAveugle(e) {
    if (!modeAveugle) return;

    // Bloquer + pour qu'il ne s'écrive pas
    if (e.key === '+') { e.preventDefault(); return; }

    // Touche - → revenir au champ précédent
    if (e.key === '-') {
        e.preventDefault();
        if (etapeActuelle > 0) {
            etapeActuelle--;
            const etape = etapesCourantes[etapeActuelle];
            parler('Retour. ' + etape.message, function() {
                const el = document.getElementById(etape.id);
                if (el) { el.focus(); if(el.select) el.select(); }
            });
        } else {
            parler('Vous êtes déjà au premier champ.');
        }
        return;
    }

    // Étape sexe : H ou F
    const etapeCourante = etapesCourantes[etapeActuelle];
    if (etapeCourante && etapeCourante.id === 'sexe-homme') {
        if (e.key === 'h' || e.key === 'H') {
            e.preventDefault();
            const radioH = document.querySelector('input[name="sexe"][value="homme"]');
            if (radioH) radioH.checked = true;
            etapeActuelle++;
            const prochaine = etapesCourantes[etapeActuelle];
            parler('Homme sélectionné. ' + (prochaine ? prochaine.message : ''), function() {
                if (prochaine) { const el = document.getElementById(prochaine.id); if (el) el.focus(); }
            });
            return;
        }
        if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            const radioF = document.querySelector('input[name="sexe"][value="femme"]');
            if (radioF) radioF.checked = true;
            etapeActuelle++;
            const prochaine = etapesCourantes[etapeActuelle];
            parler('Femme sélectionnée. ' + (prochaine ? prochaine.message : ''), function() {
                if (prochaine) { const el = document.getElementById(prochaine.id); if (el) el.focus(); }
            });
            return;
        }
        if (e.key !== 'Enter') return;
    }

    if (e.key !== 'Enter') return;

    const etape = etapesCourantes[etapeActuelle];
    if (!etape) return;
    const el = document.getElementById(etape.id);

    // Bouton final → soumettre
    if (etape.id === 'btn-aveugle-register' || etape.id === 'btn-login') {
        e.preventDefault();
        parler('Envoi en cours.', function() { el.closest('form').submit(); });
        return;
    }

    if (el && el.tagName === 'INPUT') {
        const val = el.value.trim();
        const optionnel = ['reg-telephone', 'reg-adresse'].includes(etape.id);
        if (!val && !optionnel) { parler('Ce champ est obligatoire. ' + etape.message); return; }
        e.preventDefault();

        const confirmations = {
            'reg-nom':       'Nom enregistré. ',
            'reg-prenom':    'Prénom enregistré. ',
            'reg-email':     'Email enregistré. ',
            'reg-password':  'Mot de passe enregistré. ',
            'reg-telephone': val ? 'Téléphone enregistré. ' : 'Téléphone ignoré. ',
            'reg-dob':       'Date de naissance enregistrée. ',
            'reg-adresse':   val ? 'Adresse enregistrée. ' : 'Adresse ignorée. ',
            'login-email':   'Email enregistré. ',
            'login-password':'Mot de passe enregistré. '
        };
        const confirmation = confirmations[etape.id] || 'Enregistré. ';
        etapeActuelle++;

        if (etapeActuelle < etapesCourantes.length) {
            const prochaine = etapesCourantes[etapeActuelle];
            parler(confirmation + prochaine.message, function() {
                const elSuivant = document.getElementById(prochaine.id);
                if (elSuivant) { elSuivant.focus(); if(elSuivant.select) elSuivant.select(); }
            });
        } else {
            parler(confirmation + 'Formulaire complet. Appuyez sur Entrée pour valider.');
        }
    }
}

// ── Annonce au chargement ────────────────────────────────────────────────────
window.addEventListener('load', function() {
    setTimeout(function() {
        const erreurLogin    = <?= json_encode($error_login) ?>;
        const erreurRegister = <?= json_encode($error_register) ?>;
        const msgSucces      = <?= json_encode($success) ?>;

        if (erreurLogin) {
            parler('Erreur : ' + erreurLogin + '. Veuillez réessayer. Écrivez votre adresse email, puis appuyez sur Entrée. Ou appuyez sur le signe plus pour vous inscrire.', function() {
                modeAveugle = true; etapeActuelle = 0; etapesCourantes = etapesLogin;
                const el = document.getElementById('login-email');
                if (el) el.focus();
                document.addEventListener('keydown', gererEntreeAveugle);
                // Permettre + pour basculer vers inscription même en mode erreur
                document.addEventListener('keydown', function gererPlusErreur(e) {
                    if (e.key === '+') {
                        e.preventDefault();
                        modeAveugle = false;
                        window.speechSynthesis.cancel();
                        document.removeEventListener('keydown', gererEntreeAveugle);
                        document.removeEventListener('keydown', gererPlusErreur);
                        container.classList.add('active');
                        setTimeout(() => activerModeAveugle('register'), 400);
                    }
                });
            });
            // Annuler la voix si l'utilisateur tape ou clique
            function stopErreurLogin() {
                window.speechSynthesis.cancel();
                document.removeEventListener('mousedown', stopErreurLogin);
                document.removeEventListener('keydown', stopErreurLoginKey);
            }
            function stopErreurLoginKey(e) {
                if (e.key !== '+' && e.key !== '-' && e.key !== 'Enter') {
                    window.speechSynthesis.cancel();
                    document.removeEventListener('mousedown', stopErreurLogin);
                    document.removeEventListener('keydown', stopErreurLoginKey);
                }
            }
            document.addEventListener('mousedown', stopErreurLogin);
            document.addEventListener('keydown', stopErreurLoginKey);
            return;
        }
        if (erreurRegister) {
            parler('Erreur : ' + erreurRegister + '. Veuillez corriger et réessayer.', function() {
                modeAveugle = true; etapeActuelle = 0; etapesCourantes = etapesRegister;
                const el = document.getElementById('reg-nom');
                if (el) el.focus();
                document.addEventListener('keydown', gererEntreeAveugle);
            });
            return;
        }
        if (msgSucces) { parler(msgSucces); return; }

        // Message de bienvenue automatique
        parler(
            'Bienvenue sur Takwini. ' +
            'Appuyez sur Entrée pour vous connecter. ' +
            'Si vous voulez vous inscrire, appuyez sur le signe plus. ' +
            'Si vous avez oublié votre mot de passe, appuyez sur le signe moins.',
            null
        );

        // Si l'utilisateur clique avec la souris ou tape directement
        // dans un champ → annuler la voix (utilisateur voyant)
        function annulerVoix() {
            window.speechSynthesis.cancel();
            document.removeEventListener('mousedown', annulerVoix);
            document.removeEventListener('keydown', gererTouches);
        }

        function gererTouches(e) {
            // + → inscription
            if (e.key === '+') {
                e.preventDefault();
                document.removeEventListener('mousedown', annulerVoix);
                document.removeEventListener('keydown', gererTouches);
                container.classList.add('active');
                setTimeout(() => activerModeAveugle('register'), 400);
                return;
            }
            // - → mot de passe oublié
            if (e.key === '-') {
                e.preventDefault();
                document.removeEventListener('mousedown', annulerVoix);
                document.removeEventListener('keydown', gererTouches);
                parler('Redirection vers mot de passe oublié.', function() {
                    window.location.href = 'forgot-password.php?reset=1';
                });
                return;
            }
            // Entrée → connexion guidée
            if (e.key === 'Enter') {
                document.removeEventListener('mousedown', annulerVoix);
                document.removeEventListener('keydown', gererTouches);
                activerModeAveugle('login');
                return;
            }
            // Lettre ou chiffre tapé → utilisateur voyant → annuler la voix
            if (e.key.length === 1 && !e.ctrlKey && !e.altKey) {
                window.speechSynthesis.cancel();
                document.removeEventListener('mousedown', annulerVoix);
                document.removeEventListener('keydown', gererTouches);
                return;
            }
        }

        document.addEventListener('mousedown', annulerVoix);
        document.addEventListener('keydown', gererTouches);
    }, 800);
});

// Boutons "Je ne vois pas"
function activerAssistanceBouton(mode) { activerModeAveugle(mode); }

const btnFace=document.getElementById('btn-faceid');
if(btnFace){
    btnFace.addEventListener('mouseenter',()=>{btnFace.style.borderColor='#4caf50';btnFace.style.color='#2e7d32';btnFace.style.background='#f1f8f2';});
    btnFace.addEventListener('mouseleave',()=>{btnFace.style.borderColor='#e0e0e0';btnFace.style.color='#555';btnFace.style.background='#fff';});
}
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<!-- Three.js 3D background -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function(){
    const canvas = document.getElementById('bg-canvas');
    if(!canvas || !window.THREE) return;
    const renderer = new THREE.WebGLRenderer({canvas, antialias:true, alpha:true});
    renderer.setPixelRatio(Math.min(window.devicePixelRatio,2));
    renderer.setSize(window.innerWidth, window.innerHeight);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);
    camera.position.z = 30;

    // Particules vertes
    const count = 1800;
    const geo = new THREE.BufferGeometry();
    const pos = new Float32Array(count * 3);
    const col = new Float32Array(count * 3);
    for(let i=0;i<count;i++){
        pos[i*3]   = (Math.random()-0.5)*120;
        pos[i*3+1] = (Math.random()-0.5)*120;
        pos[i*3+2] = (Math.random()-0.5)*80;
        const g = 0.5 + Math.random()*0.5;
        col[i*3]=0; col[i*3+1]=g; col[i*3+2]=g*0.2;
    }
    geo.setAttribute('position', new THREE.BufferAttribute(pos,3));
    geo.setAttribute('color',    new THREE.BufferAttribute(col,3));
    const mat = new THREE.PointsMaterial({size:0.22, vertexColors:true, transparent:true, opacity:0.7});
    const points = new THREE.Points(geo, mat);
    scene.add(points);

    // Anneaux flottants
    const rings = [];
    for(let i=0;i<4;i++){
        const r = new THREE.Mesh(
            new THREE.TorusGeometry(6+i*3, 0.04, 8, 60),
            new THREE.MeshBasicMaterial({color:0x22c55e, transparent:true, opacity:0.05+i*0.01})
        );
        r.rotation.x = Math.random()*Math.PI;
        r.rotation.y = Math.random()*Math.PI;
        r.position.set((Math.random()-0.5)*20,(Math.random()-0.5)*20,-10);
        scene.add(r); rings.push(r);
    }

    // Tilt 3D de la carte au mouvement souris
    const card = document.getElementById('container');
    let mx=0, my=0;
    document.addEventListener('mousemove', e=>{
        mx = (e.clientX/window.innerWidth - 0.5)*2;
        my = (e.clientY/window.innerHeight - 0.5)*2;
        if(card){
            const rx = -(e.clientY/window.innerHeight - 0.5)*6;
            const ry =  (e.clientX/window.innerWidth  - 0.5)*6;
            card.style.transform = `perspective(1200px) rotateX(${rx}deg) rotateY(${ry}deg)`;
            card.style.transition = 'transform .1s ease';
        }
    });
    document.addEventListener('mouseleave', ()=>{
        if(card) card.style.transform = 'perspective(1200px) rotateX(0) rotateY(0)';
    });

    function animate(){
        requestAnimationFrame(animate);
        points.rotation.y += 0.0005;
        points.rotation.x += 0.0002;
        camera.position.x += (mx*3 - camera.position.x)*0.02;
        camera.position.y += (-my*3 - camera.position.y)*0.02;
        rings.forEach((r,i)=>{ r.rotation.x += 0.002*(i%2===0?1:-1); r.rotation.z += 0.001; });
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize',()=>{
        camera.aspect = window.innerWidth/window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();
</script>
</body>
</html>
