<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function statusBadgeClass(string $statut): string
{
    return match ($statut) {
        'planifié' => 'status-planifie',
        'en cours' => 'status-encours',
        'terminé' => 'status-termine',
        'annulé' => 'status-annule',
        default => 'status-default',
    };
}

function renderStars(?int $score): string
{
    if ($score === null) {
        return '<span class="text-muted">Non évalué</span>';
    }

    $html = '<span class="stars" aria-label="Score RSE ' . $score . ' sur 5">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star ' . ($i <= $score ? 'filled' : 'empty') . '">★</span>';
    }
    $html .= '</span>';

    return $html;
}

$genres = ['homme', 'femme'];
$statuts = ['planifié', 'en cours', 'terminé', 'annulé'];

$genre = $_GET['genre'] ?? '';
$statut = $_GET['statut'] ?? '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$parPage = 10;

if ($genre !== '' && !in_array($genre, $genres, true)) {
    $genre = '';
}
if ($statut !== '' && !in_array($statut, $statuts, true)) {
    $statut = '';
}

$conditions = [];
$params = [];

if ($genre !== '') {
    $conditions[] = 'genre = :genre';
    $params[':genre'] = $genre;
}

if ($statut !== '') {
    $conditions[] = 'statut = :statut';
    $params[':statut'] = $statut;
}

$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM entretien {$whereSql}");
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$total = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $parPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $parPage;

$listStmt = $pdo->prepare("
    SELECT
        id_entretien,
        nom_candidat,
        email_candidat,
        genre,
        type_entretien,
        date_entretien,
        heure_entretien,
        poste_cible,
        score_rse,
        statut
    FROM entretien
    {$whereSql}
    ORDER BY date_entretien DESC, heure_entretien DESC, id_entretien DESC
    LIMIT :limite OFFSET :decalage
");
foreach ($params as $key => $value) {
    $listStmt->bindValue($key, $value);
}
$listStmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
$listStmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
$listStmt->execute();
$entretiens = $listStmt->fetchAll();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

function pageUrl(int $targetPage, string $genre, string $statut): string
{
    $query = ['page' => $targetPage];
    if ($genre !== '') {
        $query['genre'] = $genre;
    }
    if ($statut !== '') {
        $query['statut'] = $statut;
    }

    return 'index.php?' . http_build_query($query);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TakwiniBot - Entretiens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="index">
<main class="container py-4">
    <section class="top-bar mb-4">
        <div>
            <h1 class="mb-1">Module Entretiens</h1>
            <p class="text-muted mb-0">Gestion inclusive des entretiens TakwiniBot</p>
        </div>
        <a href="create.php" class="btn btn-takwini">+ Nouvel entretien</a>
    </section>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
    <?php endif; ?>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="genre" class="form-label">Filtrer par genre</label>
                    <select id="genre" name="genre" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($genres as $item): ?>
                            <option value="<?= e($item) ?>" <?= $genre === $item ? 'selected' : '' ?>>
                                <?= e(ucfirst($item)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="statut" class="form-label">Filtrer par statut</label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($statuts as $item): ?>
                            <option value="<?= e($item) ?>" <?= $statut === $item ? 'selected' : '' ?>>
                                <?= e(ucfirst($item)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-takwini w-100">Appliquer</button>
                    <a href="index.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                </div>
            </form>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Liste des entretiens</h2>
                <span class="text-muted"><?= $total ?> résultat(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Candidat</th>
                        <th>Genre</th>
                        <th>Type</th>
                        <th>Date &amp; heure</th>
                        <th>Poste cible</th>
                        <th>Score RSE</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$entretiens): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucun entretien trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entretiens as $entretien): ?>
                            <tr>
                                <td>
                                    <strong><?= e($entretien['nom_candidat']) ?></strong><br>
                                    <small class="text-muted"><?= e($entretien['email_candidat']) ?></small>
                                </td>
                                <td><?= e(ucfirst($entretien['genre'])) ?></td>
                                <td><?= e($entretien['type_entretien']) ?></td>
                                <td>
                                    <?= e(date('d/m/Y', strtotime($entretien['date_entretien']))) ?><br>
                                    <small class="text-muted"><?= e(substr($entretien['heure_entretien'], 0, 5)) ?></small>
                                </td>
                                <td><?= e($entretien['poste_cible']) ?></td>
                                <td><?= renderStars($entretien['score_rse'] !== null ? (int) $entretien['score_rse'] : null) ?></td>
                                <td>
                                    <span class="status-badge <?= statusBadgeClass($entretien['statut']) ?>">
                                        <?= e(ucfirst($entretien['statut'])) ?>
                                    </span>
                                </td>
                                <td class="text-end table-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="show.php?id=<?= (int) $entretien['id_entretien'] ?>">Voir</a>
                                    <a class="btn btn-sm btn-outline-success" href="edit.php?id=<?= (int) $entretien['id_entretien'] ?>">Modifier</a>
                                    <form method="post"
                                          action="delete.php?id=<?= (int) $entretien['id_entretien'] ?>"
                                          class="d-inline js-delete"
                                          data-confirm="Supprimer l'entretien de <?= e($entretien['nom_candidat']) ?> ?">
                                        <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                        <input type="hidden" name="return_genre" value="<?= e($genre) ?>">
                                        <input type="hidden" name="return_statut" value="<?= e($statut) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination entretiens">
                    <ul class="pagination justify-content-center mt-3 mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $page > 1 ? e(pageUrl($page - 1, $genre, $statut)) : '#' ?>">Précédent</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e(pageUrl($i, $genre, $statut)) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $page < $totalPages ? e(pageUrl($page + 1, $genre, $statut)) : '#' ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
