<?php
require_once __DIR__ . '/../../Controller/Inscriptioncontroller .php';

$ic = new InscriptionController();
$inscriptions = $ic->listInscriptions()->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Inscriptions</title>
</head>
<body>

<h1>Liste des Inscriptions</h1>

<a href="addInscription.php">➕ Ajouter une inscription</a>
<br><br>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Formation ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Niveau</th>
            <th>Mode Formation</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($inscriptions as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['id']); ?></td>
            <td><?= htmlspecialchars($i['user_id']); ?></td>
            <td><?= htmlspecialchars($i['formation_id']); ?></td>
            <td><?= htmlspecialchars($i['nom']); ?></td>
            <td><?= htmlspecialchars($i['prenom']); ?></td>
            <td><?= htmlspecialchars($i['email']); ?></td>
            <td><?= htmlspecialchars($i['niveau'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($i['mode_formation'] ?? '-'); ?></td>
            <td>
                <a href="updateInscription.php?id=<?= $i['id']; ?>">✏️ Modifier</a>
                &nbsp;
                <a href="deleteInscription.php?id=<?= $i['id']; ?>" onclick="return confirm('Supprimer cette inscription ?')">🗑 Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
