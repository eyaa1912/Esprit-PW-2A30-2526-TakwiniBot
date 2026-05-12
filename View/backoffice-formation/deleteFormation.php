<?php
include __DIR__ . '/../../Controller/FormationController.php';

$fc = new FormationController();
$fc->deleteFormation((int)$_GET['id']);

header('Location: listFormations.php');
exit;
?>
