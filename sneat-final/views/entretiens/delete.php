<?php
// Vue : confirmer la suppression d'un entretien
$entretien = $entretien ?? [];
?>
<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="./assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Supprimer entretien | Takwini</title>
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
                        <h4 class="fw-bold py-3 mb-4">Supprimer un entretien</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-body">
                                        <p class="text-danger fw-bold">Êtes-vous sûr de vouloir supprimer cet entretien ?</p>
                                        <p><strong>Candidat :</strong> <?= htmlspecialchars($entretien['nom_candidat'] ?? '') ?></p>
                                        <p><strong>Date :</strong> <?= htmlspecialchars($entretien['date_entretien'] ?? '') ?></p>
                                        <form method="POST" action="gestion-entretiens.php?action=delete&id=<?= htmlspecialchars($entretien['id'] ?? '') ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                                        </form>
                                        <a href="gestion-entretiens.php" class="btn btn-secondary">Annuler</a>
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
