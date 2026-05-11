<?php
include __DIR__ . '/../../Controller/FormationController.php';
include __DIR__ . '/../../Model/Formation.php';

$fc = new FormationController();

if (isset($_POST['save'])) {
    $formation = new Formation(
        $_POST['titre'],
        $_POST['duree'],
        (float)$_POST['prix'],
        $_POST['niveau'],
        $_POST['description']
    );
    $fc->updateFormation((int)$_POST['id'], $formation);
    header('Location: listFormations.php');
    exit;
}

$f = null;
if (isset($_GET['id'])) {
    $f = $fc->getFormation((int)$_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Formation</title>
</head>
<body>

<h1>Modifier une Formation</h1>

<?php if ($f): ?>

<form method="POST" action="updateFormation.php">

    <input type="hidden" name="id" value="<?= $f['id']; ?>">

    <label>Titre :</label><br>
    <input type="text" name="titre" value="<?= htmlspecialchars($f['titre']); ?>" required><br><br>

    <label>Durée :</label><br>
    <input type="text" name="duree" value="<?= htmlspecialchars($f['duree']); ?>" required><br><br>

    <label>Prix (TND) :</label><br>
    <input type="number" step="0.01" name="prix" value="<?= htmlspecialchars($f['prix']); ?>" required><br><br>

    <label>Niveau :</label><br>
    <select name="niveau" required>
        <option value="Débutant"      <?= $f['niveau'] === 'Débutant'      ? 'selected' : ''; ?>>Débutant</option>
        <option value="Intermédiaire" <?= $f['niveau'] === 'Intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
        <option value="Avancé"        <?= $f['niveau'] === 'Avancé'        ? 'selected' : ''; ?>>Avancé</option>
    </select><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="40"><?= htmlspecialchars($f['description']); ?></textarea><br><br>

    <input type="submit" name="save" value="Enregistrer">
    <a href="listFormations.php">Annuler</a>

</form>

<?php else: ?>
    <p>Formation introuvable.</p>
    <a href="listFormations.php">Retour à la liste</a>
<?php endif; ?>

</body>
</html>
