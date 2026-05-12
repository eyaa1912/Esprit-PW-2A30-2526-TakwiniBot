<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/ContratController.php';

try {
    $ctrl = new ContratController();
    echo json_encode($ctrl->getPublicStats(), JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'count'         => 0,
        'latest_id'     => null,
        'highlight_new' => false,
        'error'         => 'stats_unavailable',
    ]);
}
