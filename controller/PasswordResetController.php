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
     * Envoie un email de réinitialisation si l'email existe dans la BDD.
     * Retourne toujours un message générique (sécurité).
     */
    public function sendResetLink(string $email): array
    {
        // Vérifier si l'email existe
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            // On retourne quand même un succès (évite l'énumération d'emails)
            return ['success' => true, 'message' => 'Si cet email existe, un lien vous a été envoyé.'];
        }

        // Générer le token
        $token      = bin2hex(random_bytes(16));
        $token_hash = hash('sha256', $token);
        $expiry     = date('Y-m-d H:i:s', time() + 60 * 30); // 30 minutes

        // Sauvegarder le token en BDD
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token_hash = :hash, reset_token_expires_at = :exp WHERE email = :email'
        );
        $stmt->execute([
            'hash'  => $token_hash,
            'exp'   => $expiry,
            'email' => $email,
        ]);

        // Construire le lien (adapter le chemin selon ton hébergement)
        $resetLink = 'http://localhost/gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/reset-password.php?token=' . $token;

        // Envoyer l'email
        $mail = new PHPMailer(true);
        try {
            // ── Configurer ton SMTP ici ────────────────────────────────
            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->Host       = 'smtp.gmail.com';       // <-- ton serveur SMTP
            $mail->Port       = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username   = 'teya5466@gmail.com';  // <-- ton email
            $mail->Password   = 'zgcd nupj csbt ugdg'; // <-- mot de passe app Gmail
            // ────────────────────────────────────────────────────────────

            $mail->isHTML(true);
            $mail->setFrom('noreply@takwini.tn', 'Takwini');
            $mail->addAddress($email);
            $mail->Subject = 'Réinitialisation de votre mot de passe - Takwini';
            $mail->Body    = "
                <div style='font-family:Inter,sans-serif;max-width:500px;margin:auto;padding:30px;'>
                    <h2 style='color:#2e7d32;'>Mot de passe oublié ?</h2>
                    <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe.</p>
                    <p><strong>Ce lien expire dans 30 minutes.</strong></p>
                    <a href='{$resetLink}' style='display:inline-block;padding:12px 28px;background:#4caf50;color:#fff;text-decoration:none;border-radius:10px;font-weight:700;margin:16px 0;'>
                        Réinitialiser mon mot de passe
                    </a>
                    <p style='color:#999;font-size:12px;'>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </div>
            ";

            $mail->send();
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur envoi email : ' . $mail->ErrorInfo];
        }

        return ['success' => true, 'message' => 'Si cet email existe, un lien vous a été envoyé.'];
    }

    /**
     * Vérifie la validité du token et retourne l'utilisateur correspondant.
     */
    public function validateToken(string $token): array
    {
        $token_hash = hash('sha256', $token);

        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE reset_token_hash = :hash LIMIT 1'
        );
        $stmt->execute(['hash' => $token_hash]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['valid' => false, 'message' => 'Lien invalide ou déjà utilisé.'];
        }

        if (strtotime($user['reset_token_expires_at']) <= time()) {
            return ['valid' => false, 'message' => 'Ce lien a expiré. Veuillez en demander un nouveau.'];
        }

        return ['valid' => true, 'user' => $user];
    }

    /**
     * Réinitialise le mot de passe après validation du token.
     */
    public function resetPassword(string $token, string $password, string $confirm): array
    {
        // Valider le token
        $check = $this->validateToken($token);
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

        // Mettre à jour le mot de passe et effacer le token
        $stmt = $this->db->prepare(
            'UPDATE users SET mot_de_passe = :mdp, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = :id'
        );
        $stmt->execute(['mdp' => $passwordHash, 'id' => $user['id']]);

        return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès. Vous pouvez vous connecter.'];
    }
}
