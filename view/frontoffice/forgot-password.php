<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = '';
$error   = '';
$sent    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir une adresse email valide.';
    } else {
        $db   = config::getConnexion();
        $stmt = $db->prepare('SELECT id, nom FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Générer token unique
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Supprimer anciens tokens pour cet email
            $db->prepare('DELETE FROM password_resets WHERE email = :email')->execute(['email' => $email]);

            // Insérer nouveau token
            $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)')
               ->execute(['email' => $email, 'token' => $token, 'expires' => $expiresAt]);

            // Construire le lien
            $resetLink = 'http://localhost/gestion_utilisateur_v5/gestion_utilisateur1/view/frontoffice/reset-password.php?token=' . $token;

            // Envoyer email avec PHPMailer
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'teya5466@gmail.com';
                $mail->Password   = 'ykywiozmwfjqofck';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('teya5466@gmail.com', 'Takwinibot');
                $mail->addAddress($email, $user['nom']);
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe - Takwinibot';
                $mail->Body    = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f8e9;font-family:Inter,Arial,sans-serif;">
  <div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
    <div style="background:linear-gradient(135deg,#2e7d32,#43a047);padding:32px;text-align:center;">
      <h1 style="color:#fff;margin:0;font-size:24px;font-weight:800;">Takwinibot</h1>
    </div>
    <div style="padding:36px 40px;">
      <p style="font-size:16px;color:#333;">Bonjour <strong>' . htmlspecialchars($user['nom']) . '</strong>,</p>
      <p style="font-size:14px;color:#555;line-height:1.7;">
        Nous avons reçu une demande de réinitialisation du mot de passe pour votre compte Takwinibot.<br>
        Si vous êtes à l\'origine de cette demande, cliquez sur le bouton ci-dessous :
      </p>
      <div style="text-align:center;margin:32px 0;">
        <a href="' . $resetLink . '" style="background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;padding:14px 36px;border-radius:50px;text-decoration:none;font-size:15px;font-weight:700;display:inline-block;">
          Réinitialiser mon mot de passe
        </a>
      </div>
      <p style="font-size:12px;color:#999;text-align:center;">Ce lien est unique et expire dans <strong>1 heure</strong>.</p>
      <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
      <p style="font-size:12px;color:#aaa;">
        Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email. Votre mot de passe ne sera pas modifié.<br>
        Takwinibot ne vous demandera jamais votre mot de passe par email.
      </p>
    </div>
    <div style="background:#f9f9f9;padding:16px;text-align:center;">
      <p style="font-size:11px;color:#bbb;margin:0;">© ' . date('Y') . ' Takwinibot. Tous droits réservés.</p>
    </div>
  </div>
</body>
</html>';

                $mail->send();
                $sent = true;
            } catch (Exception $e) {
                $error = 'Erreur envoi email : ' . $mail->ErrorInfo;
            }
        } else {
            // Sécurité : ne pas révéler si l'email existe
            $sent = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mot de passe oublié - Takwinibot</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:linear-gradient(135deg,#e8f5e9,#f1f8e9); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
.card { background:#fff; border-radius:24px; box-shadow:0 8px 32px rgba(0,0,0,.1); padding:48px 40px; width:100%; max-width:440px; }
.back-btn { display:inline-flex; align-items:center; gap:8px; color:#666; text-decoration:none; font-size:13px; font-weight:600; background:#f5f5f5; padding:8px 14px; border-radius:10px; margin-bottom:28px; transition:all .2s; }
.back-btn:hover { background:#e8f5e9; color:#2e7d32; }
h1 { font-size:26px; font-weight:800; color:#1a1a2e; margin-bottom:8px; }
p.desc { font-size:14px; color:#888; line-height:1.6; margin-bottom:28px; }
label { display:block; font-size:12px; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
input[type=email] { width:100%; padding:12px 16px; border:1.5px solid #e0e0e0; border-radius:12px; font-size:14px; outline:none; transition:all .25s; background:#fafafa; }
input[type=email]:focus { border-color:#4caf50; background:#fff; box-shadow:0 0 0 3px rgba(76,175,80,.12); }
.btn { width:100%; padding:14px; background:linear-gradient(135deg,#4caf50,#2e7d32); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; margin-top:20px; transition:all .25s; }
.btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(76,175,80,.35); }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #4caf50; padding:14px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
.alert-error { background:#fde8e8; color:#c0392b; border-left:4px solid #e53935; padding:14px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; }
</style>
</head>
<body>
<div class="card">
    <a href="login.php" class="back-btn">← Retour</a>

    <?php if ($sent): ?>
        <h1>Email envoyé !</h1>
        <p class="desc">Vérifiez votre boîte mail.</p>
        <div class="alert-success">
            ✅ Un email avec les instructions a été envoyé. Le lien expire dans 1 heure.
        </div>
        <a href="login.php" class="btn" style="display:block;text-align:center;text-decoration:none;">Retour à la connexion</a>
    <?php else: ?>
        <h1>Mot de passe oublié ?</h1>
        <p class="desc">Saisissez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="votre@email.com" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <button type="submit" class="btn">Envoyer le lien</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
