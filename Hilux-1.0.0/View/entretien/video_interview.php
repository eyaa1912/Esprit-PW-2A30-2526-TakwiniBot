<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../Controller/VideoInterviewController.php';
require_once __DIR__ . '/../../Controller/EmailController.php';
require_once __DIR__ . '/../../config/database.php';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$controller = new VideoInterviewController();
$emailController = new EmailController();
$entretienId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$token = isset($_GET['token']) ? (string) $_GET['token'] : '';

if ($entretienId <= 0) {
    die('Invalid interview ID');
}

// Verify token if provided (for email invitation links)
if ($token !== '') {
    if (!$emailController->verifyInterviewToken($entretienId, $token)) {
        die('Invalid or expired interview link. Please check your email for the correct link.');
    }
}

$sessionData = $controller->getInterviewSession($entretienId);
if (isset($sessionData['error'])) {
    die('Error: ' . e($sessionData['error']));
}

$questionsData = $controller->getInterviewQuestions($entretienId);
if (isset($questionsData['error'])) {
    die('Error: ' . e($questionsData['error']));
}

$session = $sessionData['session'];
$candidate = $questionsData['candidate'];
$questions = $questionsData['questions'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video Interview - TakwiniBot</title>
    <link rel="stylesheet" href="/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/Hilux-1.0.0/css/video_interview.css">
</head>
<body>
    <div class="video-interview-container">
        <!-- Video Call Layout -->
        <div class="video-call-wrapper">
            <!-- Avatar (Interviewer) - Left Side -->
            <div class="video-section avatar-section">
                <div class="avatar-container">
                    <?php include __DIR__ . '/avatar_video_component.php'; ?>
                </div>
                <div class="avatar-info">
                    <h3>Takwini</h3>
                    <p>Interviewer</p>
                </div>
            </div>

            <!-- Candidate Webcam - Right Side -->
            <div class="video-section candidate-section">
                <video id="candidateVideo" autoplay muted playsinline></video>
                <div class="candidate-info">
                    <h3><?= e($candidate['nom']) ?></h3>
                    <p><?= e($candidate['poste']) ?></p>
                </div>
            </div>
        </div>

        <!-- Subtitle Display (for AUDITIF mode) -->
        <div class="subtitle-bar" id="subtitleBar" style="display: none;">
            <p id="subtitleText"></p>
        </div>

        <!-- Question Display -->
        <div class="question-display" id="questionDisplay">
            <p id="questionText"></p>
            <div class="question-timer">
                <span id="timerText">00:00</span>
            </div>
        </div>

        <!-- Control Bar -->
        <div class="control-bar">
            <!-- Microphone Toggle -->
            <button class="control-btn" id="micBtn" title="Toggle Microphone">
                <i class="fa fa-microphone"></i>
            </button>

            <!-- Subtitle Toggle -->
            <button class="control-btn" id="subtitleBtn" title="Toggle Subtitles">
                <i class="fa fa-closed-captioning"></i>
            </button>

            <!-- High Contrast Toggle (VISUEL mode) -->
            <button class="control-btn" id="contrastBtn" title="High Contrast Mode" style="display: none;">
                <i class="fa fa-adjust"></i>
            </button>

            <!-- Answer Input (AUDITIF mode) -->
            <div class="answer-input-group" id="answerInputGroup" style="display: none;">
                <input type="text" id="answerInput" placeholder="Type your answer..." class="form-control">
                <button class="btn btn-primary" id="submitAnswerBtn">Submit</button>
            </div>

            <!-- Large Buttons (MOTEUR mode) -->
            <div class="motor-buttons" id="motorButtons" style="display: none;">
                <button class="motor-btn motor-btn-yes">✓ Yes</button>
                <button class="motor-btn motor-btn-no">✗ No</button>
                <button class="motor-btn motor-btn-next">→ Next</button>
            </div>

            <!-- Emoji Buttons (COGNITIF mode) -->
            <div class="emoji-buttons" id="emojiButtons" style="display: none;">
                <button class="emoji-btn" data-emotion="happy">😊</button>
                <button class="emoji-btn" data-emotion="thinking">🤔</button>
                <button class="emoji-btn" data-emotion="confused">😕</button>
                <button class="emoji-btn" data-emotion="excited">🎉</button>
            </div>

            <!-- End Call Button -->
            <button class="control-btn end-call-btn" id="endCallBtn" title="End Interview">
                <i class="fa fa-phone"></i>
            </button>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script type="application/json" id="interviewConfig">
    {
        "entretienId": <?= (int) $entretienId ?>,
        "candidateName": "<?= e($candidate['nom']) ?>",
        "position": "<?= e($candidate['poste']) ?>",
        "typeHandicap": "<?= e($candidate['type_handicap']) ?>",
        "amenagements": "<?= e($candidate['amenagements'] ?? '') ?>",
        "questions": <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
    }
    </script>

    <script src="/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/Hilux-1.0.0/assets/js/video_interview.js"></script>
</body>
</html>
