<?php
require_once __DIR__ . '/../../Controller/Inscriptioncontroller .php';
require_once __DIR__ . '/../../Model/Inscription.php';

$ic = new InscriptionController();

if (isset($_POST['save'])) {
    $inscription = new Inscription(
        (int)$_POST['user_id'],
        (int)$_POST['formation_id'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['niveau'] ?? null,
        $_POST['mode_formation'] ?? null
    );
    $ic->addInscription($inscription);
    header('Location: gestion-inscriptions.php?success=add');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Inscription</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <style>
        body { background-color: #f5f5f5; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px; margin-top: 30px; }
        h1 { color: #333; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .form-group label { font-weight: 600; color: #333; }
        .form-control { border: 1px solid #ddd; border-radius: 4px; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .btn-submit { background-color: #667eea; color: white; width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: 600; }
        .btn-submit:hover { background-color: #5568d3; color: white; }
        .btn-cancel { background-color: #6c757d; color: white; width: 100%; padding: 10px; border: none; border-radius: 4px; font-weight: 600; margin-top: 10px; }
        .btn-cancel:hover { background-color: #5a6268; color: white; }
        .required { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fa fa-plus"></i> Ajouter une Inscription</h1>
    
    <form method="POST" action="addInscription.php">
        
        <div class="form-group">
            <label>ID Utilisateur <span class="required">*</span></label>
            <input type="number" name="user_id" class="form-control" placeholder="ID de l'utilisateur" required min="1">
        </div>

        <div class="form-group">
            <label>ID Formation <span class="required">*</span></label>
            <input type="number" name="formation_id" class="form-control" placeholder="ID de la formation" required min="1">
        </div>

        <div class="form-group">
            <label>Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control" placeholder="Nom du stagiaire" required maxlength="100">
        </div>

        <div class="form-group">
            <label>Prénom <span class="required">*</span></label>
            <input type="text" name="prenom" class="form-control" placeholder="Prénom du stagiaire" required maxlength="100">
        </div>

        <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="Email du stagiaire" required maxlength="150">
        </div>

        <div class="form-group">
            <label>Niveau</label>
            <input type="text" name="niveau" class="form-control" placeholder="Ex: Débutant, Intermédiaire, Avancé" maxlength="100">
        </div>

        <div class="form-group">
            <label>Mode de formation</label>
            <select name="mode_formation" class="form-control">
                <option value="">-- Sélectionner --</option>
                <option value="En ligne">En ligne</option>
                <option value="Présentiel">Présentiel</option>
                <option value="Hybride">Hybride</option>
            </select>
        </div>

        <button type="submit" name="save" class="btn-submit"><i class="fa fa-save"></i> Ajouter</button>
        <a href="gestion-inscriptions.php" class="btn-cancel" style="text-decoration: none; display: block; text-align: center;"><i class="fa fa-times"></i> Annuler</a>
    </form>
</div>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="validationinscription.js"></script>

</body>
</html>
