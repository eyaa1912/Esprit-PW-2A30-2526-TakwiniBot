<?php
include __DIR__ . '/../../Controller/FormationController.php';

$fc = new FormationController();
$liste = $fc->listFormations();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Formations</title>
</head>
<body>

<h1>Liste des Formations</h1>

<a href="addFormation.php">➕ Ajouter une formation</a>
<br><br>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Durée</th>
            <th>Prix (TND)</th>
            <th>Niveau</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($liste as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['id']); ?></td>
            <td><?= htmlspecialchars($f['titre']); ?></td>
            <td><?= htmlspecialchars($f['duree']); ?></td>
            <td><?= htmlspecialchars($f['prix']); ?></td>
            <td><?= htmlspecialchars($f['niveau']); ?></td>
            <td><?= htmlspecialchars($f['description']); ?></td>
            <td>
                <a href="deleteFormation.php?id=<?= $f['id']; ?>"
                   onclick="return confirm('Supprimer cette formation ?')">🗑 Supprimer</a>
                &nbsp;
                <form action="updateFormation.php" method="GET" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $f['id']; ?>">
                    <input type="submit" value="✏️ Modifier">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
