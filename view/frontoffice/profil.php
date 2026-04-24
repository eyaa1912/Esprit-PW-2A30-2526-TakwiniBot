<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

// Vérifie la connexion
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId     = (int) $_SESSION['user']['id'];
$db         = config::getConnexion();
$message    = '';
$error      = '';

// ── Charger user depuis BDD ────────────────────────────────────────────────
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── TRAITEMENT UPDATE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $nom            = trim($_POST['nom']            ?? '');
    $prenom         = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $telephone      = trim($_POST['telephone']      ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $date_naissance = $_POST['date_naissance']      ?? '';
    $sexe           = $_POST['sexe']                ?? '';

    if (empty($nom) || empty($email)) {
        $error = 'Nom et email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email invalide.';
    } else {
        // Vérifier email dupliqué
        $checkEmail = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $checkEmail->execute(['email' => $email, 'id' => $userId]);
        if ($checkEmail->fetch()) {
            $error = 'Cet email est déjà utilisé par un autre compte.';
        } else {
            $update = $db->prepare('
                UPDATE users SET
                    nom = :nom, prenom = :prenom, email = :email,
                    telephone = :tel, adresse = :adresse,
                    date_naissance = :dob, sexe = :sexe
                WHERE id = :id
            ');
            $update->execute([
                'nom'    => $nom,
                'prenom' => $prenom ?: null,
                'email'  => $email,
                'tel'    => $telephone ?: null,
                'adresse'=> $adresse ?: null,
                'dob'    => $date_naissance ?: null,
                'sexe'   => $sexe ?: null,
                'id'     => $userId,
            ]);
            // Refresh user + session
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            $_SESSION['user']['nom']   = $user['nom'];
            $_SESSION['user']['email'] = $user['email'];
            $message = 'Profil mis à jour avec succès !';
        }
    }
}

// ── UPLOAD AVATAR ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext     = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format non autorisé.';
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
                $message = 'Avatar mis à jour !';
            } else {
                $error = 'Impossible de sauvegarder le fichier.';
            }
        }
    } else {
        $error = 'Aucun fichier sélectionné ou erreur d\'upload.';
    }
}

// Avatar display
$avatarSrc = (!empty($user['avatar']))
    ? htmlspecialchars($user['avatar'])
    : null;
$initiales = strtoupper(substr($user['nom'], 0, 1) . substr($user['prenom'] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil – Takwini</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --vert:       #2e7d32;
            --vert-dark:  #1b5e20;
            --vert-mid:   #43a047;
            --vert-light: #e8f5e9;
            --vert-pale:  #f1f8f2;
            --white:      #ffffff;
            --gris:       #e0e0e0;
            --text:       #1a2e1c;
            --muted:      #616161;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: url('formations/assets/img/bg/home-bg.jpg') center center / cover fixed;
            min-height: 100vh;
            padding: 32px 16px;
            color: var(--text);
        }

        .profil-wrap {
            max-width: 680px;
            margin: 0 auto;
        }

        /* Retour */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--vert);
            text-decoration: none;
            font-size: .9rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .back-link:hover { color: var(--vert-dark); }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(46,125,50,.12);
            overflow: hidden;
        }

        /* Header vert */
        .card-header {
            background: linear-gradient(135deg, var(--vert-mid) 0%, var(--vert-dark) 100%);
            padding: 32px 36px 72px;
            text-align: center;
            position: relative;
        }
        .card-header h1 {
            color: white;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: .5px;
        }

        /* Avatar zone */
        .avatar-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -52px;
            padding-bottom: 8px;
        }
        .avatar-ring {
            position: relative;
            width: 104px; height: 104px;
        }
        .avatar-ring img,
        .avatar-ring .avatar-initials {
            width: 104px; height: 104px;
            border-radius: 50%;
            border: 4px solid var(--white);
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
            object-fit: cover;
        }
        .avatar-ring .avatar-initials {
            background: var(--vert);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
        }
        .avatar-edit {
            position: absolute;
            bottom: 4px; right: 4px;
            width: 30px; height: 30px;
            background: var(--vert);
            color: white;
            border: 2px solid white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 13px;
            box-shadow: 0 2px 6px rgba(0,0,0,.2);
            transition: background .2s;
        }
        .avatar-edit:hover { background: var(--vert-dark); }
        .avatar-name {
            margin-top: 10px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text);
        }
        .avatar-role {
            font-size: .82rem;
            color: var(--muted);
            margin-top: 3px;
            text-transform: capitalize;
        }
        .upload-feedback {
            font-size: .8rem;
            margin-top: 6px;
            min-height: 14px;
        }
        .ok  { color: var(--vert); }
        .err { color: #c62828; }

        /* Body */
        .card-body {
            padding: 24px 36px 36px;
        }

        .section-title {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--vert);
            padding-bottom: 8px;
            border-bottom: 2px solid var(--vert-light);
            margin: 22px 0 16px;
        }

        .field-row { display: flex; gap: 14px; }
        .field-row .field { flex: 1; }
        .field { margin-bottom: 14px; }
        .field label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 5px;
        }
        .field input, .field select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--gris);
            border-radius: 10px;
            font-size: .93rem;
            font-family: inherit;
            background: var(--vert-pale);
            outline: none;
            transition: border-color .2s;
        }
        .field input:focus, .field select:focus {
            border-color: var(--vert-mid);
            box-shadow: 0 0 0 3px rgba(67,160,71,.12);
            background: #fff;
        }
        .radio-row {
            display: flex;
            gap: 20px;
            padding: 8px 0;
        }
        .radio-row label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .9rem;
            cursor: pointer;
            font-weight: 500;
        }
        .radio-row input[type=radio] {
            accent-color: var(--vert);
            width: 15px; height: 15px;
        }

        .btn-save {
            width: 100%;
            padding: 13px;
            background: var(--vert);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            letter-spacing: .5px;
            transition: background .2s, transform .1s;
        }
        .btn-save:hover { background: var(--vert-dark); transform: translateY(-1px); }

        .alert {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .88rem;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .alert-ok  { background: var(--vert-light); color: var(--vert-dark); border-left: 3px solid var(--vert); }
        .alert-err { background: #fff8e1; color: #e65100; border-left: 3px solid #ff9800; }

        @media(max-width:520px) {
            .field-row { flex-direction: column; }
            .card-body { padding: 18px 20px 28px; }
        }
    </style>
</head>
<body>
<div class="profil-wrap">

    <a href="formations/index.php" class="back-link">&#8592; Retour à l'accueil</a>

    <div class="card">

        <!-- Header -->
        <div class="card-header">
            <h1>Mon Profil</h1>
        </div>

        <!-- Avatar + 2 boutons -->
        <div class="avatar-zone">
            <div class="avatar-ring">
                <?php if ($avatarSrc): ?>
                    <img src="<?= $avatarSrc ?>" alt="Avatar" id="avatar-preview">
                <?php else: ?>
                    <div class="avatar-initials" id="avatar-initials"><?= $initiales ?: '?' ?></div>
                <?php endif; ?>
            </div>
            <div class="avatar-name"><?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom'] ?? '') ?></div>
            <div class="avatar-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>

        <!-- Zone 2 boutons avatar -->
        <form method="POST" enctype="multipart/form-data" id="avatar-form" style="padding:10px 36px 0;">
            <input type="hidden" name="action" value="upload_avatar">
            <input type="file" id="avatar-file" name="avatar"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   style="display:none;"
                   onchange="previewAvatar(this)">

            <div style="display:flex;gap:14px;margin-bottom:6px;">
                <!-- BTN 1: Changer l'avatar -->
                <label for="avatar-file" style="
                    flex:1; display:flex; flex-direction:column;
                    align-items:center; justify-content:center;
                    background:linear-gradient(135deg,#43a047,#1b5e20);
                    color:#fff; padding:14px 20px; border-radius:16px;
                    cursor:pointer; font-size:15px; font-weight:700;
                    min-height:64px; text-align:center; transition:opacity .2s;">
                    Changer l'avatar
                    <span id="avatar-msg" style="font-size:13px;font-weight:600;margin-top:4px;min-height:18px;"></span>
                </label>

                <!-- BTN 2: Enregistrer -->
                <button type="submit" id="btn-avatar-save" style="
                    flex:1; background:linear-gradient(135deg,#66bb6a,#2e7d32);
                    color:#fff; border:none; padding:14px 20px;
                    border-radius:16px; font-size:15px; font-weight:700;
                    cursor:pointer; min-height:64px; transition:opacity .2s;">
                    Enregistrer
                </button>
            </div>
        </form>

        <!-- Body -->
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-ok"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div class="section-title">Informations personnelles</div>

                <div class="field-row">
                    <div class="field">
                        <label>Nom *</label>
                        <input type="text" name="nom" required value="<?= htmlspecialchars($user['nom']) ?>">
                    </div>
                    <div class="field">
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Email *</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="field">
                        <label>Téléphone</label>
                        <input type="tel" name="telephone" placeholder="+216 XX XXX XXX" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Date de naissance</label>
                        <input type="date" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Genre</label>
                        <div class="radio-row">
                            <label><input type="radio" name="sexe" value="homme" <?= (($user['sexe'] ?? '') === 'homme') ? 'checked' : '' ?>> Homme</label>
                            <label><input type="radio" name="sexe" value="femme" <?= (($user['sexe'] ?? '') === 'femme') ? 'checked' : '' ?>> Femme</label>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Adresse</label>
                    <input type="text" name="adresse" placeholder="Votre adresse" value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
                </div>

                <button type="submit" class="btn-save">Enregistrer les modifications</button>
            </form>

        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    const msg = document.getElementById('avatar-msg');
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const allowed = ['image/jpeg','image/png','image/gif','image/webp'];

    if (!allowed.includes(file.type)) {
        msg.textContent = 'Format non autorisé !';
        msg.style.color = '#ff3e1d';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        msg.textContent = 'Fichier trop grand (max 2MB) !';
        msg.style.color = '#ff3e1d';
        return;
    }

    // Prévisualisation
    const reader = new FileReader();
    reader.onload = function(e) {
        // Remplacer l'avatar affiché
        const ring = document.querySelector('.avatar-ring');
        let img = ring.querySelector('img');
        if (!img) {
            ring.innerHTML = '<img id="avatar-preview" style="width:104px;height:104px;border-radius:50%;border:4px solid #fff;object-fit:cover;">';
            img = ring.querySelector('img');
        }
        img.src = e.target.result;
        msg.textContent = 'Photo sélectionnée ✓';
        msg.style.color = '#4caf50';
    };
    reader.readAsDataURL(file);
}

// Afficher message après upload
<?php if ($message && str_contains($message, 'Avatar')): ?>
document.addEventListener('DOMContentLoaded', function() {
    const msg = document.getElementById('avatar-msg');
    if (msg) { msg.textContent = '<?= htmlspecialchars($message) ?>'; msg.style.color = '#4caf50'; }
});
<?php endif; ?>
<?php if ($error): ?>
document.addEventListener('DOMContentLoaded', function() {
    const msg = document.getElementById('avatar-msg');
    if (msg) { msg.textContent = '<?= htmlspecialchars($error) ?>'; msg.style.color = '#ff3e1d'; }
});
<?php endif; ?>
</script>
</body>
</html>
