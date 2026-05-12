<?php
declare(strict_types=1);
include __DIR__ . '/../../Controller/ReclamationController.php';

$controller = new ReclamationController();
$liste = $controller->listReclamation();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Réclamations | Takwinibot</title>
    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="assets/css/dark-mode.css" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background: #f5f5f9; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #566a7f; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; font-weight: 500; cursor: pointer; border: none; font-size: 0.875rem; }
        .btn-primary { background: #696cff; color: #fff; }
        .btn-primary:hover { background: #5f61e6; }
        .btn-danger { background: #ff3e1d; color: #fff; }
        .btn-danger:hover { background: #e8381a; }
        .btn-warning { background: #ffab00; color: #fff; }
        .btn-warning:hover { background: #e69c00; }
        .btn-secondary { background: #8592a3; color: #fff; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 6px rgba(67,89,113,.12); }
        thead { background: #696cff; color: #fff; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e7e7ff; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f5f5ff; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-attente { background: #ffe0b2; color: #e65100; }
        .badge-cours   { background: #fff3cd; color: #856404; }
        .badge-traite  { background: #d1f7c4; color: #1b5e20; }
        .actions { display: flex; gap: 0.5rem; align-items: center; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; }
        .alert-success { background: #d1f7c4; color: #1b5e20; }
        .alert-danger  { background: #ffd5cc; color: #7e1a0a; }
    </style>
</head>
<body>
<div class="container">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            $msg = [
                'added'   => 'Réclamation ajoutée avec succès.',
                'updated' => 'Réclamation mise à jour avec succès.',
                'deleted' => 'Réclamation supprimée avec succès.',
            ];
            echo htmlspecialchars($msg[$_GET['success']] ?? 'Opération réussie.');
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <h1>📋 Liste des Réclamations</h1>
        <a href="addReclamation.php" class="btn btn-primary">➕ Ajouter une réclamation</a>
    </div>

    <?php if (empty($liste)): ?>
        <p style="color:#8592a3;">Aucune réclamation enregistrée.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sujet</th>
                <th>Message</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($liste as $r): ?>
            <?php
            $statut = strtolower(str_replace(' ', '_', $r['statut'] ?? 'en_attente'));
            $badgeClass = match($statut) {
                'traite'   => 'badge-traite',
                'en_cours' => 'badge-cours',
                default    => 'badge-attente',
            };
            $badgeLabel = match($statut) {
                'traite'   => 'Traitée',
                'en_cours' => 'En cours',
                default    => 'En attente',
            };
            ?>
            <tr>
                <td><strong>R-<?= htmlspecialchars((string)$r['id']); ?></strong></td>
                <td><?= htmlspecialchars($r['sujet'] ?? '-'); ?></td>
                <td><?= htmlspecialchars(mb_strimwidth($r['message'] ?? '', 0, 60, '…')); ?></td>
                <td><?= htmlspecialchars($r['type'] ?? '-'); ?></td>
                <td><span class="badge <?= $badgeClass; ?>"><?= $badgeLabel; ?></span></td>
                <td><?= htmlspecialchars($r['date_creation'] ?? '-'); ?></td>
                <td>
                    <div class="actions">
                        <a href="updateReclamation.php?id=<?= (int)$r['id']; ?>" class="btn btn-warning">✏️ Modifier</a>
                        <a href="deleteReclamation.php?id=<?= (int)$r['id']; ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Supprimer cette réclamation ?')">🗑️ Supprimer</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div style="margin-top:1.5rem;">
        <a href="html/gestion-reclamations.html" class="btn btn-secondary">← Retour au backoffice</a>
    </div>
</div>
</body>
</html>
