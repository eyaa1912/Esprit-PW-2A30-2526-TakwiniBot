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
<title>Takwini - Connexion / Inscription</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{background:url('formations/assets/img/bg/home-bg.jpg') center/cover fixed;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;}
body::before{content:'';position:fixed;inset:0;background:rgba(27,94,32,.55);z-index:0;pointer-events:none;}
.container{background:#fff;border-radius:30px;box-shadow:0 8px 32px rgba(0,0,0,.12);position:relative;overflow:hidden;width:1100px;max-width:100%;min-height:700px;display:flex;z-index:1;}
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
</style>
</head>
<body>
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
       <a href="forgot-password.php" style="font-size:12px;color:#4caf50;text-decoration:none;font-weight:500;">
        Mot de passe oublié ?
       </a>
       </div>
      <button type="submit" class="btn-main" id="btn-login" style="margin-top:20px;">Se connecter</button>
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

const btnFace=document.getElementById('btn-faceid');
if(btnFace){
    btnFace.addEventListener('mouseenter',()=>{btnFace.style.borderColor='#4caf50';btnFace.style.color='#2e7d32';btnFace.style.background='#f1f8f2';});
    btnFace.addEventListener('mouseleave',()=>{btnFace.style.borderColor='#e0e0e0';btnFace.style.color='#555';btnFace.style.background='#fff';});
}
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</body>
</html>
