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
    $fc->addFormation($formation);
    header('Location: listFormations.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Formation</title>
</head>
<body>

<h1>Ajouter une Formation</h1>

<form method="POST" action="addFormation.php">

    <label>Titre :</label><br>
    <input type="text" name="titre" placeholder="Titre de la formation" required><br><br>

    <label>Durée :</label><br>
    <input type="text" name="duree" placeholder="Ex: 3 mois" required><br><br>

    <label>Prix (TND) :</label><br>
    <input type="number" step="0.01" name="prix" placeholder="Ex: 299.99" required><br><br>

    <label>Niveau :</label><br>
    <select name="niveau" required>
        <option value="">-- Choisir --</option>
        <option value="Débutant">Débutant</option>
        <option value="Intermédiaire">Intermédiaire</option>
        <option value="Avancé">Avancé</option>
    </select><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="4" cols="40" placeholder="Description de la formation"></textarea><br><br>

    <input type="submit" name="save" value="Ajouter">
    <a href="listFormations.php">Annuler</a>

</form>

</body>
</html>
