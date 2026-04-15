<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Methode non autorisee.',
    ]);
}

$idsRaw = trim((string)($_GET['ids'] ?? ''));
$userId = parsePositiveInt($_GET['user_id'] ?? null);

$ids = [];
if ($idsRaw !== '') {
    $ids = array_filter(array_map('trim', explode(',', $idsRaw)), static function (string $value): bool {
        return $value !== '';
    });
    $ids = array_unique(array_map(static function (string $value): int {
        return (int)$value;
    }, $ids));
    $ids = array_values(array_filter($ids, static function (int $value): bool {
        return $value > 0;
    }));
}

if (empty($ids) && $userId === null) {
    jsonResponse(200, [
        'success' => true,
        'data' => [],
    ]);
}

try {
    $pdo = getPDO();

    $conditions = [];
    $params = [];

    if (!empty($ids)) {
        $idPlaceholders = [];
        foreach ($ids as $index => $id) {
            $paramName = ':id_' . $index;
            $idPlaceholders[] = $paramName;
            $params[$paramName] = $id;
        }
        $conditions[] = 'r.id IN (' . implode(', ', $idPlaceholders) . ')';
    }

    if ($userId !== null) {
        $conditions[] = 'r.user_id = :user_id';
        $params[':user_id'] = $userId;
    }

    $whereClause = implode(' AND ', $conditions);

    $sql = "
        SELECT
            r.id,
            r.user_id,
            COALESCE(fr.type, '-') AS type,
            r.sujet,
            r.message,
            r.statut,
            r.date_creation,
            r.date_modification,
            rp.contenu AS reponse,
            rp.date_reponse
        FROM reclamation r
        LEFT JOIN formulaire_reclamation fr ON fr.id = r.formulaire_id
        LEFT JOIN reponse rp ON rp.id = (
            SELECT rp2.id
            FROM reponse rp2
            WHERE rp2.reclamation_id = r.id
            ORDER BY rp2.date_reponse DESC, rp2.id DESC
            LIMIT 1
        )
        WHERE {$whereClause}
        ORDER BY r.date_creation DESC
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $paramName => $value) {
        $stmt->bindValue($paramName, $value, PDO::PARAM_INT);
    }
    $stmt->execute();

    $rows = $stmt->fetchAll();

    jsonResponse(200, [
        'success' => true,
        'data' => $rows,
    ]);
} catch (Throwable $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erreur serveur lors de la recuperation.',
        'error' => $e->getMessage(),
    ]);
}
