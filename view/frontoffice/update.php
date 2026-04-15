<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

// Doit être connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$controller = new UtilisateurController();
$message    = '';
$error      = '';

// L'admin peut modifier n'importe quel user via ?id=X, sinon on modifie son propre compte
$targetId = isset($_GET['id']) && $_SESSION['user']['role'] === 'admin'
    ? (int) $_GET['id']
    : (int) $_SESSION['user']['id'];

$user = $controller->getById($targetId);
if (!$user) {
    die('Utilisateur introuvable.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nom) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $result = $controller->updateUser($targetId, $nom, $email, $password);
        if ($result['success']) {
            $message = $result['message'];
            // Rafraîchir les données
            $user = $controller->getById($targetId);
            // Mettre à jour la session si c'est son propre compte
            if ($targetId === (int) $_SESSION['user']['id']) {
                $_SESSION['user']['nom']   = $user['nom'];
                $_SESSION['user']['email'] = $user['email'];
            }
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takwini – Modifier le profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif; }
        body { background:linear-gradient(to right,#e2e2e2,#d7f0d8); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:20px; box-shadow:0 5px 15px rgba(0,0,0,.2); padding:40px; width:420px; max-width:95%; }
        h2 { color:#2e7d32; margin-bottom:24px; text-align:center; }
        label { font-size:13px; color:#555; display:block; margin-bottom:4px; }
        input { background:#eee; border:none; padding:10px 15px; border-radius:8px; width:100%; font-size:13px; outline:none; margin-bottom:14px; }
        button[type=submit] { background:#2e7d32; color:#fff; border:none; padding:12px; border-radius:8px; width:100%; font-size:14px; font-weight:600; cursor:pointer; letter-spacing:.5px; text-transform:uppercase; margin-top:6px; }
        button[type=submit]:hover { background:#1b5e20; }
        .alert { padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; text-align:center; }
        .alert-success { background:#e8f5e9; color:#2e7d32; }
        .alert-danger  { background:#fde8e8; color:#c0392b; }
        .back-link { display:block; text-align:center; margin-top:16px; color:#2e7d32; font-size:13px; text-decoration:none; }
        .back-link:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="card">
    <h2><i class="fa fa-user-pen"></i> Modifier le profil</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nom complet</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Nouveau mot de passe</label>
        <input type="password" name="password" placeholder="Entrez un nouveau mot de passe" required>

        <button type="submit"><i class="fa fa-save"></i> Enregistrer</button>
    </form>

    <a href="formations/index.html" class="back-link"><i class="fa fa-arrow-left"></i> Retour à l'accueil</a>
</div>
</body>
</html>
