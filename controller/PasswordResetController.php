<?php
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

class PasswordResetController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    /**
     * Génère un code à 6 chiffres et l'envoie par email.
     * Retourne toujours un message générique (sécurité).
     */
    public function sendResetCode(string $email): array
    {
        // Vérifier si l'email existe
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            // On retourne quand même un succès (évite l'énumération d'emails)
            return ['success' => true, 'message' => 'Si cet email existe, un code vous a été envoyé.'];
        }

        // Générer un code à 6 chiffres
        $code       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $code_hash  = hash('sha256', $code);
        $expiry     = date('Y-m-d H:i:s', time() + 60 * 15); // 15 minutes

        // Sauvegarder le code hashé en BDD
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token_hash = :hash, reset_token_expires_at = :exp WHERE email = :email'
        );
        $stmt->execute([
            'hash'  => $code_hash,
            'exp'   => $expiry,
            'email' => $email,
        ]);

        // Envoyer l'email avec le code
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug  = 0; // Mettre à 2 pour voir les logs SMTP en cas de problème
            $mail->SMTPAuth   = true;
            $mail->Host       = 'smtp.gmail.com';
            $mail->Port       = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username   = 'teya5466@gmail.com';
            $mail->Password   = 'zgcd nupj csbt ugdg';
            $mail->CharSet    = 'UTF-8';

            $mail->isHTML(true);
            $mail->setFrom('teya5466@gmail.com', 'Takwini'); // From doit correspondre au compte SMTP
            $mail->addAddress($email);
            $mail->Subject = 'Votre code de réinitialisation - Takwini';
            $mail->Body    = "
                <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:30px;background:#f9f9f9;border-radius:16px;'>
                    <h2 style='color:#2e7d32;text-align:center;'>Réinitialisation du mot de passe</h2>
                    <p style='color:#555;text-align:center;'>Utilisez le code ci-dessous pour réinitialiser votre mot de passe.</p>
                    <div style='text-align:center;margin:28px 0;'>
                        <span style='display:inline-block;font-size:40px;font-weight:900;letter-spacing:12px;color:#2e7d32;background:#e8f5e9;padding:18px 32px;border-radius:14px;border:2px dashed #4caf50;'>
                            {$code}
                        </span>
                    </div>
                    <p style='color:#888;font-size:13px;text-align:center;'><strong>Ce code expire dans 15 minutes.</strong></p>
                    <p style='color:#bbb;font-size:12px;text-align:center;margin-top:20px;'>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </div>
            ";
            $mail->AltBody = "Votre code de réinitialisation Takwini : {$code} (expire dans 15 minutes)";

            $mail->send();
        } catch (\Exception $e) {
            // Retourner l'erreur réelle pour faciliter le débogage
            return ['success' => false, 'message' => 'Erreur envoi email : ' . $mail->ErrorInfo];
        }

        return ['success' => true, 'message' => 'Code envoyé ! Vérifiez votre boîte email (et les spams).'];
    }

    /**
     * Vérifie la validité du code à 6 chiffres.
     */
    public function validateCode(string $email, string $code): array
    {
        $code_hash = hash('sha256', $code);

        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email AND reset_token_hash = :hash LIMIT 1'
        );
        $stmt->execute(['email' => $email, 'hash' => $code_hash]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['valid' => false, 'message' => 'Code incorrect.'];
        }

        if (strtotime($user['reset_token_expires_at']) <= time()) {
            return ['valid' => false, 'message' => 'Ce code a expiré. Veuillez en demander un nouveau.'];
        }

        return ['valid' => true, 'user' => $user];
    }

    /**
     * Réinitialise le mot de passe après validation du code.
     */
    public function resetPasswordWithCode(string $email, string $code, string $password, string $confirm): array
    {
        // Valider le code
        $check = $this->validateCode($email, $code);
        if (!$check['valid']) {
            return ['success' => false, 'message' => $check['message']];
        }

        // Valider le mot de passe
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.'];
        }
        if ($password !== $confirm) {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        $user         = $check['user'];
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Mettre à jour le mot de passe et effacer le code
        $stmt = $this->db->prepare(
            'UPDATE users SET mot_de_passe = :mdp, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = :id'
        );
        $stmt->execute(['mdp' => $passwordHash, 'id' => $user['id']]);

        return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès. Vous pouvez vous connecter.'];
    }

    // ── Méthodes conservées pour compatibilité ──────────────────────────────

    /** @deprecated Utiliser sendResetCode() */
    public function sendResetLink(string $email): array
    {
        return $this->sendResetCode($email);
    }

    /** @deprecated Utiliser validateCode() */
    public function validateToken(string $token): array
    {
        return ['valid' => false, 'message' => 'Méthode obsolète, utilisez un code.'];
    }

    /** @deprecated Utiliser resetPasswordWithCode() */
    public function resetPassword(string $token, string $password, string $confirm): array
    {
        return ['success' => false, 'message' => 'Méthode obsolète, utilisez un code.'];
    }
}
