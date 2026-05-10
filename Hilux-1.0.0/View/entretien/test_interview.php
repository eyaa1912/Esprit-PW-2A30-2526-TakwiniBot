<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../Controller/VideoInterviewController.php';
require_once __DIR__ . '/../../Controller/EmailController.php';
require_once __DIR__ . '/../../config/database.php';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$entretienId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

echo "<h1>Interview Test Page</h1>";
echo "<p>Testing interview ID: $entretienId</p>";

try {
    // Test database connection
    echo "<h2>1. Database Connection</h2>";
    $pdo = config::getConnexion();
    echo "<p style='color: green;'>✓ Database connected</p>";

    // Test VideoInterviewController
    echo "<h2>2. VideoInterviewController</h2>";
    $controller = new VideoInterviewController();
    echo "<p style='color: green;'>✓ VideoInterviewController instantiated</p>";

    // Test getInterviewSession
    echo "<h2>3. Get Interview Session</h2>";
    $sessionData = $controller->getInterviewSession($entretienId);
    if (isset($sessionData['error'])) {
        echo "<p style='color: red;'>✗ Error: " . e($sessionData['error']) . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Session data retrieved</p>";
        echo "<pre>" . json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    }

    // Test getInterviewQuestions
    echo "<h2>4. Get Interview Questions</h2>";
    $questionsData = $controller->getInterviewQuestions($entretienId);
    if (isset($questionsData['error'])) {
        echo "<p style='color: red;'>✗ Error: " . e($questionsData['error']) . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Questions retrieved</p>";
        echo "<p>Total questions: " . count($questionsData['questions']) . "</p>";
        echo "<p>Candidate: " . e($questionsData['candidate']['nom']) . "</p>";
        echo "<p>Position: " . e($questionsData['candidate']['poste']) . "</p>";
    }

    // Test EmailController
    echo "<h2>5. EmailController</h2>";
    $emailController = new EmailController();
    echo "<p style='color: green;'>✓ EmailController instantiated</p>";

    // Test token verification
    echo "<h2>6. Token Verification</h2>";
    $sql = "SELECT interview_token FROM entretien WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $entretienId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['interview_token']) {
        echo "<p style='color: green;'>✓ Token found in database</p>";
        echo "<p>Token: " . e($result['interview_token']) . "</p>";
        
        // Test token verification
        $isValid = $emailController->verifyInterviewToken($entretienId, $result['interview_token']);
        if ($isValid) {
            echo "<p style='color: green;'>✓ Token verification successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Token verification failed</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ No token found in database</p>";
    }

    // Test interview_token column
    echo "<h2>7. Database Schema</h2>";
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'entretien' AND COLUMN_NAME = 'interview_token'";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'>✓ interview_token column exists</p>";
    } else {
        echo "<p style='color: red;'>✗ interview_token column NOT found</p>";
    }

    echo "<h2>8. Ready to Test</h2>";
    echo "<p><a href='video_interview.php?id=$entretienId' style='padding: 10px 20px; background: #30b5e1; color: white; text-decoration: none; border-radius: 5px;'>Start Interview (Direct)</a></p>";
    
    if ($result && isset($result['interview_token'])) {
        $token = $result['interview_token'];
        echo "<p><a href='video_interview.php?id=$entretienId&token=$token' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Start Interview (With Token)</a></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . e($e->getMessage()) . "</p>";
    echo "<pre>" . e($e->getTraceAsString()) . "</pre>";
}

?>
