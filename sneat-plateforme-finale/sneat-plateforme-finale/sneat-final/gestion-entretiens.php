<?php
require_once __DIR__ . '/controllers/EntretienController.php';

// Routage simple
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$controller = new EntretienController();

switch ($action) {
    case 'show':
        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;
    case 'create':
        $controller->create();
        break;
    case 'edit':
        if ($id) {
            $controller->update($id);
        } else {
            $controller->index();
        }
        break;
    case 'delete':
        if ($id) {
            $controller->delete($id);
        } else {
            $controller->index();
        }
        break;
    case 'api':
        $controller->api();
        break;
    default:
        $controller->index();
        break;
}
?>