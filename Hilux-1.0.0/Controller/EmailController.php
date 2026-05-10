<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/MigrationHelper.php';

class EmailController {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
        
        // Run migrations to ensure interview_token column exists
        $migration = new MigrationHelper();
        $migration->runMigrations();
    }

    /**
     * Send interview invitation email to candidate
     */
    public function sendInterviewInvitation(int $entretienId): array {
        try {
            $entretien = $this->getEntretienData($entretienId);
            if (!$entretien) {
                return ['error' => 'Interview not found'];
            }

            // Generate unique token for this interview
            $token = bin2hex(random_bytes(32));
            
            // Save token to database
            $this->saveInterviewToken($entretienId, $token);

            // Build interview link
            $interviewLink = $this->buildInterviewLink($entretienId, $token);

            // Update status to "invitation_sent" (before sending email)
            $this->updateInterviewStatus($entretienId, 'invitation_sent');

            // Try to send email
            $emailSent = $this->sendEmail(
                $entretien['email_candidat'],
                $entretien['nom_candidat'],
                $entretien['poste_cible'],
                $interviewLink
            );

            // Return success even if email fails (token is saved)
            return [
                'success' => true,
                'message' => $emailSent ? 'Email sent successfully' : 'Token generated (email sending not configured)',
                'email' => $entretien['email_candidat'],
                'link' => $interviewLink,
                'token' => $token,
                'emailSent' => $emailSent
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get entretien data
     */
    private function getEntretienData(int $entretienId): array|false {
        $sql = "SELECT * FROM entretien WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $entretienId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save interview token
     */
    private function saveInterviewToken(int $entretienId, string $token): void {
        $sql = "UPDATE entretien SET interview_token = :token WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':token' => $token,
            ':id' => $entretienId
        ]);
    }

    /**
     * Build interview link
     */
    private function buildInterviewLink(int $entretienId, string $token): string {
        $baseUrl = 'http://localhost/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/Hilux-1.0.0';
        return "{$baseUrl}/View/entretien/video_interview.php?id={$entretienId}&token={$token}";
    }

    /**
     * Update interview status
     */
    private function updateInterviewStatus(int $entretienId, string $status): void {
        $sql = "UPDATE entretien SET statut = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':id' => $entretienId
        ]);
    }

    /**
     * Send email to candidate using SMTP
     */
    private function sendEmail(string $email, string $candidateName, string $position, string $interviewLink): bool {
        $smtpHost = config::getSmtpHost();
        $smtpPort = config::getSmtpPort();
        $smtpUser = config::getSmtpUser();
        $smtpPassword = config::getSmtpPassword();
        $fromEmail = config::getSmtpFromEmail();
        $fromName = config::getSmtpFromName();

        // Check if SMTP is configured
        if (empty($smtpUser) || empty($smtpPassword) || empty($fromEmail)) {
            error_log('SMTP not configured. Email not sent to: ' . $email);
            return false;
        }

        try {
            // Create SMTP connection
            $smtp = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
            if (!$smtp) {
                error_log("SMTP Connection failed: $errstr ($errno)");
                return false;
            }

            // Read SMTP response
            $response = fgets($smtp, 1024);
            if (strpos($response, '220') === false) {
                fclose($smtp);
                return false;
            }

            // Send EHLO
            fputs($smtp, "EHLO localhost\r\n");
            $response = fgets($smtp, 1024);

            // Start TLS
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 1024);
            if (strpos($response, '220') === false) {
                fclose($smtp);
                return false;
            }

            // Enable crypto
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            // Send EHLO again after TLS
            fputs($smtp, "EHLO localhost\r\n");
            $response = fgets($smtp, 1024);

            // Authenticate
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, base64_encode($smtpUser) . "\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($smtp, 1024);

            if (strpos($response, '235') === false) {
                error_log("SMTP Authentication failed");
                fclose($smtp);
                return false;
            }

            // Send email
            $subject = "Invitation à un entretien d'embauche - TakwiniBot";
            $htmlBody = $this->getEmailTemplate($candidateName, $position, $interviewLink);

            fputs($smtp, "MAIL FROM: <" . $fromEmail . ">\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, "RCPT TO: <" . $email . ">\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, "DATA\r\n");
            $response = fgets($smtp, 1024);

            $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
            $headers .= "To: " . $email . "\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "Reply-To: " . $fromEmail . "\r\n";

            $message = $headers . "\r\n" . $htmlBody;

            fputs($smtp, $message . "\r\n.\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, "QUIT\r\n");
            fclose($smtp);

            return strpos($response, '250') !== false;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email template
     */
    private function getEmailTemplate(string $candidateName, string $position, string $interviewLink): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .message {
            margin-bottom: 20px;
            line-height: 1.8;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            opacity: 0.9;
        }
        .link-text {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            word-break: break-all;
            font-size: 12px;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #999;
        }
        .info-box {
            background: #e8f5e9;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎥 Entretien avec Takwini</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Bonjour <strong>{$candidateName}</strong>,
            </div>

            <div class="message">
                Nous sommes heureux de vous inviter à participer à un entretien d'embauche pour le poste de <strong>{$position}</strong>.
            </div>

            <div class="info-box">
                <strong>✨ Entretien innovant:</strong> Vous allez participer à un entretien vidéo avec Takwini, notre assistant d'entretien intelligent et accessible. 
                Takwini s'adaptera à vos besoins spécifiques pour vous offrir la meilleure expérience possible.
            </div>

            <div class="message">
                <strong>Comment ça marche:</strong>
                <ul>
                    <li>Cliquez sur le bouton ci-dessous pour commencer l'entretien</li>
                    <li>Autorisez l'accès à votre webcam et microphone</li>
                    <li>Répondez aux questions de Takwini</li>
                    <li>L'entretien dure environ 10-15 minutes</li>
                </ul>
            </div>

            <div class="button-container">
                <a href="{$interviewLink}" class="button">Commencer l'entretien</a>
            </div>

            <div class="link-text">
                Ou copiez ce lien dans votre navigateur:<br>
                {$interviewLink}
            </div>

            <div class="message">
                <strong>Important:</strong>
                <ul>
                    <li>Assurez-vous d'avoir une bonne connexion Internet</li>
                    <li>Trouvez un endroit calme pour l'entretien</li>
                    <li>Utilisez un navigateur moderne (Chrome, Firefox, Safari, Edge)</li>
                    <li>Le lien est personnel et unique pour vous</li>
                </ul>
            </div>

            <div class="message">
                Si vous avez des questions ou des problèmes techniques, n'hésitez pas à nous contacter à contact@takwinibot.com.
            </div>

            <div class="message">
                Bonne chance! 🍀
            </div>

            <div class="footer">
                <p>© 2026 TakwiniBot - Entretiens accessibles pour tous</p>
                <p>Cet email a été envoyé à {$candidateName} pour participer à un entretien d'embauche.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Verify interview token
     */
    public function verifyInterviewToken(int $entretienId, string $token): bool {
        $sql = "SELECT interview_token FROM entretien WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $entretienId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        return hash_equals($result['interview_token'] ?? '', $token);
    }
}
