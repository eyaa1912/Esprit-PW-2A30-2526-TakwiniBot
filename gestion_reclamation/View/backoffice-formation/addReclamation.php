<?php
declare(strict_types=1);
include __DIR__ . '/../../Controller/ReclamationController.php';

$controller = new ReclamationController();
$error = '';

if (isset($_POST['save'])) {
    try {
        $controller->addReclamation([
            'type'    => $_POST['type']    ?? '',
            'sujet'   => $_POST['sujet']   ?? '',
            'message' => $_POST['message'] ?? '',
            'user_id' => $_POST['user_id'] ?? null,
        ]);
        header('Location: listReclamations.php?success=added');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'Erreur serveur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Réclamation | Takwinibot</title>
    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="assets/css/dark-mode.css" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background: #f5f5f9; padding: 2rem; }
        .container { max-width: 640px; margin: 0 auto; }
        h1 { color: #566a7f; margin-bottom: 1.5rem; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 2px 6px rgba(67,89,113,.12); padding: 2rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-weight: 600; color: #566a7f; margin-bottom: 0.4rem; font-size: 0.875rem; }
        input[type=text], textarea, select {
            width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d9dee3;
            border-radius: 0.375rem; font-size: 0.9375rem; box-sizing: border-box;
            font-family: inherit; color: #566a7f;
        }
        input[type=text]:focus, textarea:focus, select:focus { outline: none; border-color: #696cff; box-shadow: 0 0 0 3px rgba(105,108,255,.15); }
        textarea { resize: vertical; min-height: 120px; }
        .btn { display: inline-block; padding: 0.5rem 1.25rem; border-radius: 0.375rem; text-decoration: none; font-weight: 600; cursor: pointer; border: none; font-size: 0.9375rem; }
        .btn-primary { background: #696cff; color: #fff; }
        .btn-primary:hover { background: #5f61e6; }
        .btn-secondary { background: #8592a3; color: #fff; }
        .btn-secondary:hover { background: #7a8799; }
        .actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .alert-danger { background: #ffd5cc; color: #7e1a0a; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; }
        .form-text { font-size: 0.8rem; color: #a1acb8; margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>➕ Ajouter une Réclamation</h1>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="addReclamation.php">

            <div class="form-group">
                <label for="type">Type de réclamation *</label>
                <select name="type" id="type" required>
                    <option value="">-- Choisir un type --</option>
                    <option value="Compte"      <?= ($_POST['type'] ?? '') === 'Compte'      ? 'selected' : ''; ?>>Compte</option>
                    <option value="Technique"   <?= ($_POST['type'] ?? '') === 'Technique'   ? 'selected' : ''; ?>>Technique</option>
                    <option value="Facturation" <?= ($_POST['type'] ?? '') === 'Facturation' ? 'selected' : ''; ?>>Facturation</option>
                    <option value="Formation"   <?= ($_POST['type'] ?? '') === 'Formation'   ? 'selected' : ''; ?>>Formation</option>
                    <option value="Autre"       <?= ($_POST['type'] ?? '') === 'Autre'       ? 'selected' : ''; ?>>Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="sujet">Sujet *</label>
                <input type="text" name="sujet" id="sujet"
                       value="<?= htmlspecialchars($_POST['sujet'] ?? ''); ?>"
                       placeholder="Objet de la réclamation" required>
                <span class="form-text">Entre 3 et 200 caractères.</span>
            </div>

            <div class="form-group">
                <label for="message">Message *</label>
                <textarea name="message" id="message"
                          placeholder="Décrivez votre réclamation en détail..."
                          required><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <span class="form-text">Minimum 5 caractères.</span>
            </div>

            <div class="form-group">
                <label for="user_id">ID Utilisateur (optionnel)</label>
                <input type="text" name="user_id" id="user_id"
                       value="<?= htmlspecialchars($_POST['user_id'] ?? ''); ?>"
                       placeholder="Laisser vide pour utilisateur anonyme">
            </div>

            <div class="actions">
                <button type="submit" name="save" class="btn btn-primary">💾 Enregistrer</button>
                <a href="listReclamations.php" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</div>
</body>
</html>
