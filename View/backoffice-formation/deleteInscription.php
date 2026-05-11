<?php
require_once __DIR__ . '/../../Controller/Inscriptioncontroller .php';

$ic = new InscriptionController();
$ic->deleteInscription((int)$_GET['id']);

header('Location: listInscriptions.php');
exit;
?>
