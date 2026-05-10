<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->getConnection();

if ($pdo) {
    echo "✅ Database connected successfully!<br>";
    
    // Test query
    $sql = "SELECT COUNT(*) as count FROM entretien";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    echo "✅ Entretien table has " . $result['count'] . " records<br>";
} else {
    echo "❌ Database connection failed!<br>";
}

echo "<br>Testing controller...<br>";

require_once __DIR__ . '/controllers/EntretienController.php';

try {
    $controller = new EntretienController();
    echo "✅ Controller loaded successfully!<br>";
} catch (Exception $e) {
    echo "❌ Controller error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='gestion-entretiens.php'>Go to Dashboard</a>";
?>
