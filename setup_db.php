<?php
require_once __DIR__ . '/config.php';
$db = config::getConnexion();
$sql = file_get_contents(__DIR__ . '/database.sql');
$db->exec($sql);
echo "Database updated successfully.";
