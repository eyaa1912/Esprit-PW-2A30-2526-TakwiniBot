<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$typesEntretien = ['présentiel', 'visioconférence', 'téléphonique', 'LST', 'hybride'];
$genres = ['homme', 'femme'];
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];

$data = [
    'nom_candidat' => '',
    'email_candidat' => '',
    'genre' => '',
    'type_handicap' => '',
    'amenagements' => '',
    'type_entretien' => '',
    'date_entretien' => '',
    'heure_entretien' => '',
    'poste_cible' => '',
    'metier_suggere' => '',
    'score_rse' => '',
    'remarques' => '',
    'statut' => 'planifié',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) {
        $data[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if ($data['nom_candidat'] === '') {
        $errors['nom_candidat'] = 'Le nom du candidat est requis.';
    }
    if ($data['email_candidat'] === '') {
        $errors['email_candidat'] = 'L’email du candidat est requis.';
    } elseif (!filter_var($data['email_candidat'], FILTER_VALIDATE_EMAIL)) {
        $errors['email_candidat'] = 'Veuillez saisir un email valide.';
    }
    if (!in_array($data['genre'], $genres, true)) {
        $errors['genre'] = 'Veuillez sélectionner un genre valide.';
    }
    if ($data['type_handicap'] === '') {
        $errors['type_handicap'] = 'Le type de handicap est requis.';
    }
    if (!in_array($data['type_entretien'], $typesEntretien, true)) {
        $errors['type_entretien'] = 'Veuillez sélectionner un type d’entretien valide.';
    }
    if ($data['date_entretien'] === '') {
        $errors['date_entretien'] = 'La date de l’entretien est requise.';
    }
    if ($data['heure_entretien'] === '') {
        $errors['heure_entretien'] = 'L’heure de l’entretien est requise.';
    }
    if ($data['poste_cible'] === '') {
        $errors['poste_cible'] = 'Le poste cible est requis.';
    }
    if (!in_array($data['statut'], $statuts, true)) {
        $errors['statut'] = 'Veuillez sélectionner un statut valide.';
    }

    if ($data['score_rse'] !== '') {
        $score = filter_var(
            $data['score_rse'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 5]]
        );
        if ($score === false) {
            $errors['score_rse'] = 'Le score RSE doit être un entier entre 1 et 5.';
        } else {
            $data['score_rse'] = (string) $score;
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO entretien (
                    nom_candidat,
                    email_candidat,
                    genre,
                    type_handicap,
                    amenagements,
                    type_entretien,
                    date_entretien,
                    heure_entretien,
                    poste_cible,
                    metier_suggere,
                    score_rse,
                    remarques,
                    statut
                ) VALUES (
                    :nom_candidat,
                    :email_candidat,
                    :genre,
                    :type_handicap,
                    :amenagements,
                    :type_entretien,
                    :date_entretien,
                    :heure_entretien,
                    :poste_cible,
                    :metier_suggere,
                    :score_rse,
                    :remarques,
                    :statut
                )'
            );

            $stmt->execute([
                ':nom_candidat' => $data['nom_candidat'],
                ':email_candidat' => $data['email_candidat'],
                ':genre' => $data['genre'],
                ':type_handicap' => $data['type_handicap'],
                ':amenagements' => $data['amenagements'] !== '' ? $data['amenagements'] : null,
                ':type_entretien' => $data['type_entretien'],
                ':date_entretien' => $data['date_entretien'],
                ':heure_entretien' => $data['heure_entretien'],
                ':poste_cible' => $data['poste_cible'],
                ':metier_suggere' => $data['metier_suggere'] !== '' ? $data['metier_suggere'] : null,
                ':score_rse' => $data['score_rse'] !== '' ? (int) $data['score_rse'] : null,
                ':remarques' => $data['remarques'] !== '' ? $data['remarques'] : null,
                ':statut' => $data['statut'],
            ]);

            $_SESSION['flash_success'] = 'Entretien créé avec succès.';
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $errors['global'] = 'Impossible de créer l’entretien. Vérifiez les données puis réessayez.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un entretien - TakwiniBot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="create">
<main class="container py-4">
    <section class="top-bar mb-4">
        <div>
            <h1 class="mb-1">Créer un entretien</h1>
            <p class="text-muted mb-0">Ajout d’un entretien inclusif pour TakwiniBot</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">Retour à la liste</a>
    </section>

    <?php if (isset($errors['global'])): ?>
        <div class="alert alert-danger"><?= e($errors['global']) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="avatar-card">
                <svg class="takwini-avatar" viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Avatar TakwiniBot">
                    <circle cx="90" cy="90" r="86" fill="#F1EFE8" />
                    <circle cx="90" cy="72" r="36" fill="#534AB7" />
                    <rect x="42" y="112" width="96" height="42" rx="20" fill="#1D9E75" />
                    <circle cx="76" cy="72" r="5" fill="#FFFFFF" />
                    <circle cx="104" cy="72" r="5" fill="#FFFFFF" />
                    <path d="M75 89 Q90 101 105 89" stroke="#FFFFFF" stroke-width="5" fill="none" stroke-linecap="round" />
                </svg>
                <p id="avatar-help" class="avatar-bubble">
                    Je t’aide à planifier un entretien accessible et bien structuré.
                </p>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate data-avatar-help="true" data-avatar-help-target="avatar-help">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="nom_candidat">Nom candidat *</label>
                                <input type="text" id="nom_candidat" name="nom_candidat" class="form-control <?= isset($errors['nom_candidat']) ? 'is-invalid' : '' ?>" value="<?= e($data['nom_candidat']) ?>">
                                <div class="invalid-feedback"><?= e($errors['nom_candidat'] ?? 'Champ requis.') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email_candidat">Email candidat *</label>
                                <input type="text" id="email_candidat" name="email_candidat" class="form-control <?= isset($errors['email_candidat']) ? 'is-invalid' : '' ?>" value="<?= e($data['email_candidat']) ?>">
                                <div class="invalid-feedback"><?= e($errors['email_candidat'] ?? 'Email invalide.') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="genre">Genre *</label>
                                <select id="genre" name="genre" class="form-select <?= isset($errors['genre']) ? 'is-invalid' : '' ?>">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?= e($genre) ?>" <?= $data['genre'] === $genre ? 'selected' : '' ?>>
                                            <?= e(ucfirst($genre)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['genre'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="type_handicap">Type de handicap *</label>
                                <input type="text" id="type_handicap" name="type_handicap" class="form-control <?= isset($errors['type_handicap']) ? 'is-invalid' : '' ?>" value="<?= e($data['type_handicap']) ?>">
                                <div class="invalid-feedback"><?= e($errors['type_handicap'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="amenagements">Aménagements</label>
                                <textarea id="amenagements" name="amenagements" rows="2" class="form-control"><?= e($data['amenagements']) ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="type_entretien">Type d’entretien *</label>
                                <select id="type_entretien" name="type_entretien" class="form-select <?= isset($errors['type_entretien']) ? 'is-invalid' : '' ?>">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($typesEntretien as $type): ?>
                                        <option value="<?= e($type) ?>" <?= $data['type_entretien'] === $type ? 'selected' : '' ?>>
                                            <?= e(ucfirst($type)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['type_entretien'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="date_entretien">Date *</label>
                                <input type="date" id="date_entretien" name="date_entretien" class="form-control <?= isset($errors['date_entretien']) ? 'is-invalid' : '' ?>" value="<?= e($data['date_entretien']) ?>">
                                <div class="invalid-feedback"><?= e($errors['date_entretien'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="heure_entretien">Heure *</label>
                                <input type="time" id="heure_entretien" name="heure_entretien" class="form-control <?= isset($errors['heure_entretien']) ? 'is-invalid' : '' ?>" value="<?= e($data['heure_entretien']) ?>">
                                <div class="invalid-feedback"><?= e($errors['heure_entretien'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="poste_cible">Poste cible *</label>
                                <input type="text" id="poste_cible" name="poste_cible" class="form-control <?= isset($errors['poste_cible']) ? 'is-invalid' : '' ?>" value="<?= e($data['poste_cible']) ?>">
                                <div class="invalid-feedback"><?= e($errors['poste_cible'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="metier_suggere">Métier suggéré</label>
                                <input type="text" id="metier_suggere" name="metier_suggere" class="form-control" value="<?= e($data['metier_suggere']) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="score_rse">Score RSE (1-5)</label>
                                <input type="text" id="score_rse" name="score_rse" class="form-control <?= isset($errors['score_rse']) ? 'is-invalid' : '' ?>" value="<?= e($data['score_rse']) ?>">
                                <div class="invalid-feedback"><?= e($errors['score_rse'] ?? 'Valeur entre 1 et 5.') ?></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="statut">Statut *</label>
                                <select id="statut" name="statut" class="form-select <?= isset($errors['statut']) ? 'is-invalid' : '' ?>">
                                    <?php foreach ($statuts as $item): ?>
                                        <option value="<?= e($item) ?>" <?= $data['statut'] === $item ? 'selected' : '' ?>>
                                            <?= e(ucfirst($item)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= e($errors['statut'] ?? 'Champ requis.') ?></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="remarques">Remarques</label>
                                <textarea id="remarques" name="remarques" rows="3" class="form-control"><?= e($data['remarques']) ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-takwini">Enregistrer</button>
                            <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
