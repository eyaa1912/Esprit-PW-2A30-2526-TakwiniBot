<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['entretienId']) || !isset($input['answers'])) {
        throw new Exception('Missing required fields');
    }

    $entretienId = (int) $input['entretienId'];
    $answers = $input['answers'];
    $duration = (int) ($input['duration'] ?? 0);

    $pdo = config::getConnexion();

    // Prepare answers data
    $answersData = [
        'answers' => $answers,
        'duration' => $duration,
        'completed_at' => date('Y-m-d H:i:s')
    ];

    $remarques = json_encode($answersData, JSON_UNESCAPED_UNICODE);

    // Update entretien with answers
    $sql = "UPDATE entretien SET remarques = :remarques, statut = :statut WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':remarques' => $remarques,
        ':statut' => 'terminé',
        ':id' => $entretienId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Interview answers saved successfully',
        'entretienId' => $entretienId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
