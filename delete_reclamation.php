<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ── Method check ──────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Methode non autorisee.',
    ]);
}

// ── Input parsing ─────────────────────────────────────────────────
$data = getRequestData();
$id   = parsePositiveInt($data['id'] ?? null);

if ($id === null) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'ID invalide ou manquant.',
    ]);
}

// ── Delete with transaction ───────────────────────────────────────
try {
    $pdo = getPDO();

    // Verify the reclamation exists before attempting deletion
    $checkStmt = $pdo->prepare('SELECT id, sujet, statut FROM reclamation WHERE id = :id LIMIT 1');
    $checkStmt->execute([':id' => $id]);
    $reclamation = $checkStmt->fetch();

    if (!$reclamation) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'Reclamation introuvable.',
        ]);
    }

    $pdo->beginTransaction();

    // 1) Delete related responses first (foreign key dependency)
    $deleteReponses = $pdo->prepare('DELETE FROM reponse WHERE reclamation_id = :reclamation_id');
    $deleteReponses->execute([':reclamation_id' => $id]);
    $reponsesDeleted = $deleteReponses->rowCount();

    // 2) Delete the reclamation itself
    $deleteReclamation = $pdo->prepare('DELETE FROM reclamation WHERE id = :id');
    $deleteReclamation->execute([':id' => $id]);

    $pdo->commit();

    jsonResponse(200, [
        'success' => true,
        'message' => 'Reclamation supprimee avec succes.',
        'data'    => [
            'id'                => $id,
            'reponses_deleted'  => $reponsesDeleted,
        ],
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erreur serveur lors de la suppression.',
        'error'   => $e->getMessage(),
    ]);
}
