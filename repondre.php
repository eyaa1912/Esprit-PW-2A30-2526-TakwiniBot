<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Methode non autorisee.',
    ]);
}

$data = getRequestData();
$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$reponse = trim((string)($data['reponse'] ?? ''));
$adminIdInput = parsePositiveInt($data['admin_id'] ?? null);

if ($id === false || $id === null || $id <= 0) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'ID invalide.',
    ]);
}

if ($reponse === '' || mb_strlen($reponse) < 3) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Reponse invalide (minimum 3 caracteres).',
    ]);
}

try {
    $pdo = getPDO();
    $adminId = resolveUserId($pdo, $adminIdInput, 'admin', false);
    if ($adminId === null) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Aucun administrateur disponible. Ajoutez un user role=admin.',
        ]);
    }

    $checkStmt = $pdo->prepare('SELECT id FROM reclamation WHERE id = :id LIMIT 1');
    $checkStmt->execute([':id' => $id]);
    $exists = $checkStmt->fetch();

    if (!$exists) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'Reclamation introuvable.',
        ]);
    }

    $pdo->beginTransaction();

    // ── Upsert: update existing response or insert new one ────────
    $existingStmt = $pdo->prepare(
        'SELECT id FROM reponse WHERE reclamation_id = :reclamation_id ORDER BY date_reponse DESC, id DESC LIMIT 1'
    );
    $existingStmt->execute([':reclamation_id' => $id]);
    $existingReponse = $existingStmt->fetch();

    if ($existingReponse) {
        // Update existing response instead of creating a duplicate
        $upsertStmt = $pdo->prepare(
            'UPDATE reponse
             SET admin_id = :admin_id, contenu = :contenu, date_reponse = NOW()
             WHERE id = :reponse_id'
        );
        $upsertStmt->execute([
            ':admin_id'   => $adminId,
            ':contenu'    => $reponse,
            ':reponse_id' => $existingReponse['id'],
        ]);
        $actionLabel = 'mise_a_jour';
    } else {
        // Insert new response
        $insertStmt = $pdo->prepare(
            'INSERT INTO reponse (reclamation_id, admin_id, contenu, date_reponse)
             VALUES (:reclamation_id, :admin_id, :contenu, NOW())'
        );
        $insertStmt->execute([
            ':reclamation_id' => $id,
            ':admin_id'       => $adminId,
            ':contenu'        => $reponse,
        ]);
        $actionLabel = 'creation';
    }

    $updateStmt = $pdo->prepare(
        'UPDATE reclamation
         SET statut = :statut, date_modification = NOW()
         WHERE id = :id'
    );
    $updateStmt->execute([
        ':statut' => 'traite',
        ':id'     => $id,
    ]);

    $pdo->commit();

    // ── Return full updated data ──────────────────────────────────
    $fetchStmt = $pdo->prepare(
        'SELECT r.id, r.sujet, r.statut, r.date_modification,
                rp.contenu AS reponse, rp.date_reponse
         FROM reclamation r
         LEFT JOIN reponse rp ON rp.reclamation_id = r.id
         WHERE r.id = :id
         ORDER BY rp.date_reponse DESC, rp.id DESC
         LIMIT 1'
    );
    $fetchStmt->execute([':id' => $id]);
    $updatedRow = $fetchStmt->fetch();

    jsonResponse(200, [
        'success' => true,
        'message' => $actionLabel === 'mise_a_jour'
            ? 'Reponse mise a jour avec succes.'
            : 'Reponse enregistree avec succes.',
        'data' => [
            'id'        => (int)$id,
            'admin_id'  => $adminId,
            'statut'    => 'traite',
            'action'    => $actionLabel,
            'reponse'   => $updatedRow['reponse'] ?? $reponse,
            'date_reponse' => $updatedRow['date_reponse'] ?? null,
        ],
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erreur serveur lors de la mise a jour.',
        'error'   => $e->getMessage(),
    ]);
}
