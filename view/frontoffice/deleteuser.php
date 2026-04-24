<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

// Seul un admin peut supprimer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$controller = new UtilisateurController();
$message    = '';
$error      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $result  = $controller->deleteUser($id);
        $message = $result['success'] ? $result['message'] : '';
        $error   = $result['success'] ? '' : $result['message'];
    } else {
        $error = 'ID invalide.';
    }
}

$users = $controller->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takwini – Supprimer un utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Montserrat',sans-serif; }
        body { background: linear-gradient(to right,#e2e2e2,#d7f0d8); min-height:100vh; padding:40px 20px; }
        .card { background:#fff; border-radius:16px; box-shadow:0 5px 15px rgba(0,0,0,.15); max-width:900px; margin:0 auto; padding:30px; }
        h2 { color:#2e7d32; margin-bottom:20px; }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:12px 14px; text-align:left; border-bottom:1px solid #eee; font-size:14px; }
        th { background:#2e7d32; color:#fff; }
        tr:hover { background:#f9f9f9; }
        .btn-delete { background:#e53935; color:#fff; border:none; padding:7px 14px; border-radius:6px; cursor:pointer; font-size:12px; }
        .btn-delete:hover { background:#c62828; }
        .alert { padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; }
        .alert-success { background:#e8f5e9; color:#2e7d32; }
        .alert-danger  { background:#fde8e8; color:#c0392b; }
        .back-link { display:inline-block; margin-bottom:20px; color:#2e7d32; text-decoration:none; font-size:14px; }
        .back-link:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="card">
    <a href="login.php" class="back-link"><i class="fa fa-arrow-left"></i> Retour</a>
    <h2><i class="fa fa-users"></i> Gestion des utilisateurs – Suppression</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['statut']) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn-delete"><i class="fa fa-trash"></i> Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
