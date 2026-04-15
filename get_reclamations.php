<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Methode non autorisee.',
    ]);
}

try {
    $pdo = getPDO();
    $baseSelect = "
        SELECT
            r.id,
            r.user_id,
            COALESCE(fr.type, '-') AS type,
            r.sujet,
            r.message,
            r.statut,
            r.date_creation,
            r.date_modification,
            u.nom AS user_nom,
            u.prenom AS user_prenom,
            u.email AS user_email,
            rp.contenu AS reponse,
            rp.date_reponse
        FROM reclamation r
        LEFT JOIN formulaire_reclamation fr ON fr.id = r.formulaire_id
        LEFT JOIN users u ON u.id = r.user_id
        LEFT JOIN reponse rp ON rp.id = (
            SELECT rp2.id
            FROM reponse rp2
            WHERE rp2.reclamation_id = r.id
            ORDER BY rp2.date_reponse DESC, rp2.id DESC
            LIMIT 1
        )
    ";

    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            jsonResponse(422, [
                'success' => false,
                'message' => 'ID invalide.',
            ]);
        }

        $stmt = $pdo->prepare($baseSelect . ' WHERE r.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            jsonResponse(404, [
                'success' => false,
                'message' => 'Reclamation introuvable.',
            ]);
        }

        jsonResponse(200, [
            'success' => true,
            'data' => $row,
        ]);
    }

    $stmt = $pdo->query($baseSelect . ' ORDER BY r.date_creation DESC');

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
