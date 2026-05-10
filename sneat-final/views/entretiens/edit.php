<?php
// Vue : éditer un entretien
$entretien = $entretien ?? [];
$typesEntretien = $typesEntretien ?? [];
$genres = ['homme', 'femme'];
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="./assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Éditer un entretien | Takwini</title>
    <base href="/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/sneat-final/">
    <link rel="icon" type="image/x-icon" href="./assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="./assets/vendor/css/core.css" />
    <link rel="stylesheet" href="./assets/css/demo.css" />
    <link rel="stylesheet" href="./assets/css/dark-mode.css" />
    <link rel="stylesheet" href="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="./assets/vendor/js/helpers.js"></script>
    <script src="./assets/js/config.js"></script>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4">Éditer un entretien</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST" action="gestion-entretiens.php?action=edit&id=<?= htmlspecialchars($entretien['id'] ?? '') ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nom candidat *</label>
                                                <input type="text" name="nom_candidat" class="form-control" value="<?= htmlspecialchars($entretien['nom_candidat'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email candidat *</label>
                                                <input type="email" name="email_candidat" class="form-control" value="<?= htmlspecialchars($entretien['email_candidat'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Genre *</label>
                                                <select name="genre" class="form-select" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php foreach ($genres as $g): ?>
                                                        <option value="<?= htmlspecialchars($g) ?>" <?= ($entretien['genre'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Type d'entretien *</label>
                                                <select name="type_entretien_id" class="form-select" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php foreach ($typesEntretien as $type): ?>
                                                        <option value="<?= htmlspecialchars($type['id']) ?>" <?= ($entretien['type_entretien_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['libelle']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date entretien *</label>
                                                <input type="date" name="date_entretien" class="form-control" value="<?= htmlspecialchars($entretien['date_entretien'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Heure entretien *</label>
                                                <input type="time" name="heure_entretien" class="form-control" value="<?= htmlspecialchars(substr($entretien['heure_entretien'] ?? '', 0, 5)) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Poste cible *</label>
                                                <input type="text" name="poste_cible" class="form-control" value="<?= htmlspecialchars($entretien['poste_cible'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Type de handicap</label>
                                                <input type="text" name="type_handicap" class="form-control" value="<?= htmlspecialchars($entretien['type_handicap'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Aménagements</label>
                                                <input type="text" name="amenagements" class="form-control" value="<?= htmlspecialchars($entretien['amenagements'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Métier suggéré</label>
                                                <input type="text" name="metier_suggere" class="form-control" value="<?= htmlspecialchars($entretien['metier_suggere'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Score RSE (1-5)</label>
                                                <input type="number" name="score_rse" class="form-control" min="1" max="5" value="<?= htmlspecialchars($entretien['score_rse'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Remarques</label>
                                                <textarea name="remarques" class="form-control" rows="3"><?= htmlspecialchars($entretien['remarques'] ?? '') ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Statut *</label>
                                                <select name="statut" class="form-select" required>
                                                    <?php foreach ($statuts as $s): ?>
                                                        <option value="<?= htmlspecialchars($s) ?>" <?= ($entretien['statut'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                                <a href="gestion-entretiens.php" class="btn btn-secondary">Annuler</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="./assets/vendor/js/bootstrap.js"></script>
    <script src="./js/bootstrap.js"></script>
</body>
</html>
