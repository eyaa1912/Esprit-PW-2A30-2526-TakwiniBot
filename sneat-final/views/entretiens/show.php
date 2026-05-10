<?php
// Vue : afficher les détails d'un entretien
$entretien = $entretien ?? [];
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="./assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Détails entretien | Takwini</title>
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
                        <h4 class="fw-bold py-3 mb-4">Détails de l'entretien</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nom candidat</label>
                                            <p><?= htmlspecialchars($entretien['nom_candidat'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email candidat</label>
                                            <p><?= htmlspecialchars($entretien['email_candidat'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Genre</label>
                                            <p><?= htmlspecialchars($entretien['genre'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Type d'entretien</label>
                                            <p><?= htmlspecialchars($entretien['type_entretien_id'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Date entretien</label>
                                            <p><?= htmlspecialchars($entretien['date_entretien'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Heure entretien</label>
                                            <p><?= htmlspecialchars(substr($entretien['heure_entretien'] ?? '', 0, 5)) ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Poste cible</label>
                                            <p><?= htmlspecialchars($entretien['poste_cible'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Type de handicap</label>
                                            <p><?= htmlspecialchars($entretien['type_handicap'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Aménagements</label>
                                            <p><?= htmlspecialchars($entretien['amenagements'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Métier suggéré</label>
                                            <p><?= htmlspecialchars($entretien['metier_suggere'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Score RSE</label>
                                            <p><?= htmlspecialchars($entretien['score_rse'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Remarques</label>
                                            <p><?= htmlspecialchars($entretien['remarques'] ?? '') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Statut</label>
                                            <p><?= htmlspecialchars($entretien['statut'] ?? '') ?></p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="gestion-entretiens.php?action=edit&id=<?= htmlspecialchars($entretien['id'] ?? '') ?>" class="btn btn-primary">Éditer</a>
                                            <a href="gestion-entretiens.php" class="btn btn-secondary">Retour</a>
                                        </div>
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
