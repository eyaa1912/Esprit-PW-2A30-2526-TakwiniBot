<?php
require_once __DIR__ . '/../../models/Entretien.php';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Export entretiens</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
    h1 { margin: 0 0 8px 0; font-size: 24px; }
    .muted { color: #6b7280; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
    th { background: #f3f4f6; }
    .actions { margin-top: 14px; }
    .btn { display: inline-block; padding: 8px 12px; border: 1px solid #9ca3af; border-radius: 6px; text-decoration: none; color: #111827; margin-right: 8px; }
    @media print {
      .actions { display: none; }
      body { margin: 0; }
    }
  </style>
</head>
<body>
  <h1>Export des entretiens</h1>
  <div class="muted">Généré le <?= date('d/m/Y H:i') ?></div>

  <table>
    <thead>
      <tr>
        <th>Candidat</th>
        <th>Email</th>
        <th>Poste</th>
        <th>Type</th>
        <th>Date</th>
        <th>Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($entretiens)): ?>
        <tr>
          <td colspan="6">Aucun entretien trouvé.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($entretiens as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['nom_candidat']) ?></td>
            <td><?= htmlspecialchars($e['email_candidat']) ?></td>
            <td><?= htmlspecialchars($e['poste_cible'] ?? '—') ?></td>
            <td><?= htmlspecialchars($e['type_libelle'] ?? '—') ?></td>
            <td><?= htmlspecialchars(\Entretien::formatDateHeure($e['date_entretien'], $e['heure_entretien'])) ?></td>
            <td><?= htmlspecialchars($e['statut']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="actions">
    <a href="javascript:window.print()" class="btn">Imprimer / Sauvegarder en PDF</a>
    <a href="./gestion-entretiens.php" class="btn">Retour</a>
  </div>
</body>
</html>
