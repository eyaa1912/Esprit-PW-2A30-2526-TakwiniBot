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

$type = trim((string)($data['type'] ?? ''));
$sujet = trim((string)($data['sujet'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$userIdInput = parsePositiveInt($data['user_id'] ?? null);

$errors = [];
if ($type === '' || mb_strlen($type) > 100) {
    $errors[] = 'Type invalide.';
}
if ($sujet === '' || mb_strlen($sujet) < 3 || mb_strlen($sujet) > 255) {
    $errors[] = 'Sujet invalide (3 a 255 caracteres).';
}
if ($message === '' || mb_strlen($message) < 5) {
    $errors[] = 'Message invalide (minimum 5 caracteres).';
}

if (!empty($errors)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Validation echouee.',
        'errors' => $errors,
    ]);
}

try {
    $pdo = getPDO();
    $resolvedUserId = resolveUserId($pdo, $userIdInput, null);
    if ($resolvedUserId === null) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Aucun utilisateur disponible. Creez au moins un user dans la table users.',
        ]);
    }

    $formulaireId = getOrCreateFormulaireId($pdo, $type);

    $stmt = $pdo->prepare(
        'INSERT INTO reclamation (user_id, formulaire_id, sujet, message, statut, date_creation, date_modification)
         VALUES (:user_id, :formulaire_id, :sujet, :message, :statut, NOW(), NOW())'
    );

    $stmt->execute([
        ':user_id' => $resolvedUserId,
        ':formulaire_id' => $formulaireId,
        ':sujet' => $sujet,
        ':message' => $message,
        ':statut' => 'en_attente',
    ]);

    jsonResponse(201, [
        'success' => true,
        'message' => 'Reclamation enregistree avec succes.',
        'data' => [
            'id' => (int)$pdo->lastInsertId(),
            'user_id' => $resolvedUserId,
            'formulaire_id' => $formulaireId,
            'type' => $type,
            'sujet' => $sujet,
            'statut' => 'en_attente',
        ],
    ]);
} catch (Throwable $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erreur serveur lors de lenregistrement.',
        'error' => $e->getMessage(),
    ]);
}
