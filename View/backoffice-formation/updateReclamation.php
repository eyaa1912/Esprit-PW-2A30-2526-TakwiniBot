<?php
declare(strict_types=1);
include __DIR__ . '/../../Controller/ReclamationController.php';

$controller = new ReclamationController();
$error = '';
$reclamation = null;

// Charger la réclamation existante
if (isset($_GET['id'])) {
    $reclamation = $controller->getReclamation((int)$_GET['id']);
    if (!$reclamation) {
        header('Location: listReclamations.php?error=' . urlencode('Réclamation introuvable.'));
        exit;
    }
}

// Traitement du formulaire
if (isset($_POST['save'])) {
    try {
        $controller->updateReclamation((int)$_POST['id'], [
            'sujet'   => $_POST['sujet']   ?? '',
            'message' => $_POST['message'] ?? '',
            'statut'  => $_POST['statut']  ?? 'en_attente',
        ]);
        header('Location: listReclamations.php?success=updated');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'Erreur serveur : ' . $e->getMessage();
    }
}

// Données à afficher dans le formulaire (POST en cas d'erreur, sinon BD)
$data = [
    'id'      => $reclamation ? $reclamation->getId()      : (int)($_POST['id'] ?? 0),
    'sujet'   => $_POST['sujet']   ?? ($reclamation ? $reclamation->getSujet()   : ''),
    'message' => $_POST['message'] ?? ($reclamation ? $reclamation->getMessage() : ''),
    'statut'  => $_POST['statut']  ?? ($reclamation ? $reclamation->getStatut()  : 'en_attente'),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Réclamation R-<?= (int)$data['id']; ?> | Takwinibot</title>
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
        .ref-badge { display: inline-block; background: #e7e7ff; color: #696cff; padding: 0.25rem 0.75rem; border-radius: 999px; font-weight: 700; font-size: 0.875rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>✏️ Modifier la Réclamation</h1>
    <span class="ref-badge">R-<?= (int)$data['id']; ?></span>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($data['id']): ?>
    <div class="card">
        <form method="POST" action="updateReclamation.php">

            <input type="hidden" name="id" value="<?= (int)$data['id']; ?>">

            <div class="form-group">
                <label for="sujet">Sujet *</label>
                <input type="text" name="sujet" id="sujet"
                       value="<?= htmlspecialchars($data['sujet']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="message">Message *</label>
                <textarea name="message" id="message" required><?= htmlspecialchars($data['message']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="statut">Statut *</label>
                <select name="statut" id="statut" required>
                    <option value="en_attente" <?= $data['statut'] === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="en_cours"   <?= $data['statut'] === 'en_cours'   ? 'selected' : ''; ?>>En cours</option>
                    <option value="traite"     <?= $data['statut'] === 'traite'     ? 'selected' : ''; ?>>Traitée</option>
                </select>
            </div>

            <div class="actions">
                <button type="submit" name="save" class="btn btn-primary">💾 Enregistrer</button>
                <a href="listReclamations.php" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
    <?php else: ?>
        <p style="color:#8592a3;">Réclamation introuvable.</p>
        <a href="listReclamations.php" class="btn btn-secondary">← Retour à la liste</a>
    <?php endif; ?>
</div>
</body>
</html>
