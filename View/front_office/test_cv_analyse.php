<?php
/**
 * Page de test : analyse CV via Mistral.
 * Choisissez un fichier (PDF, DOC, DOCX) sur votre PC ou collez le texte du CV.
 */
require_once __DIR__ . '/../../Controller/CvAnalyseController.php';

$resultHtml = '';
$errorHtml   = '';
$debugHtml   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl = new CvAnalyseController();
    $cvText = trim($_POST['cv_text'] ?? '');

    if ($cvText === '' && !empty($_FILES['cv']['name']) && (int) ($_FILES['cv']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $file = $_FILES['cv'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            $errorHtml = 'Format non supporté. Utilisez PDF, DOC ou DOCX.';
        } else {
            $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cv_test_' . uniqid('', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
                $errorHtml = 'Impossible d’enregistrer le fichier uploadé.';
            } else {
                $cvText = $ctrl->extractText($tmpPath);
                @unlink($tmpPath);
                if (trim($cvText) === '') {
                    $errorHtml = $ext === 'doc'
                        ? 'Les fichiers .doc (Word 97–2003) ne sont pas lus ici. Enregistrez en .docx ou PDF, ou collez le texte du CV.'
                        : 'Impossible d’extraire le texte de ce fichier. Essayez un autre PDF / .docx ou collez le texte à la main.';
                }
            }
        }
    }

    if ($errorHtml === '' && trim($cvText) === '') {
        $errorHtml = 'Sélectionnez un fichier CV ou collez le contenu texte du CV.';
    }

    if ($errorHtml === '') {
        $cvCheck = $ctrl->validateEstCv($cvText);
        if (!$cvCheck['is_cv']) {
            $errorHtml = htmlspecialchars($cvCheck['message'] ?? 'Document non reconnu comme CV.');
        }
    }

    if ($errorHtml === '') {
        $analysis = $ctrl->analyserCv($cvText, '', '');
        if (!empty($analysis['success'])) {
            $resultHtml = '<div class="ok"><h2>Résultat de l’analyse</h2><pre>' . htmlspecialchars($analysis['analyse'] ?? '') . '</pre></div>';
        } else {
            $errorHtml = htmlspecialchars($analysis['message'] ?? 'Erreur inconnue.');
        }
    }

    if (!empty($_POST['debug']) && $errorHtml === '') {
        $preview = mb_substr($cvText ?? '', 0, 800);
        $debugHtml = '<div class="debug"><strong>Extrait du texte envoyé à l’API (800 car. max) :</strong><pre>'
            . htmlspecialchars($preview) . (mb_strlen($cvText ?? '') > 800 ? "\n…" : '')
            . '</pre></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test analyse CV</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 32px auto; padding: 0 16px; color: #222; }
        h1 { font-size: 1.25rem; color: #2c3e50; }
        label { display: block; margin-top: 14px; font-weight: 600; font-size: 14px; }
        input[type="file"] { margin-top: 6px; }
        textarea { width: 100%; min-height: 120px; margin-top: 6px; padding: 10px; box-sizing: border-box; font-size: 14px; }
        button { margin-top: 16px; padding: 10px 20px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; }
        button:hover { background: #6d28d9; }
        .hint { font-size: 13px; color: #666; margin-top: 4px; }
        .ok pre { white-space: pre-wrap; word-break: break-word; background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; font-size: 14px; line-height: 1.6; }
        .err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .debug pre { font-size: 12px; background: #1e293b; color: #e2e8f0; padding: 12px; border-radius: 8px; overflow: auto; max-height: 240px; }
    </style>
</head>
<body>
    <h1>Test analyse CV (fichier ou texte)</h1>
    <p class="hint">Choisissez un CV sur votre ordinateur (PDF, DOC, DOCX) ou collez le texte. Le fichier uploadé est traité en mémoire puis supprimé.</p>

    <?php if ($errorHtml !== ''): ?>
        <div class="err"><?php echo $errorHtml; ?></div>
    <?php endif; ?>
    <?php echo $resultHtml; ?>
    <?php echo $debugHtml; ?>

    <form method="post" enctype="multipart/form-data" action="">
        <label for="cv">Fichier CV</label>
        <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
        <p class="hint">Optionnel si vous collez le texte ci-dessous.</p>

        <label for="cv_text">Ou texte du CV (copier-coller)</label>
        <textarea id="cv_text" name="cv_text" placeholder="Collez ici le contenu de votre CV…"></textarea>

        <label style="font-weight: normal;">
            <input type="checkbox" name="debug" value="1"> Afficher un extrait du texte extrait (debug)
        </label>

        <button type="submit">Lancer l’analyse</button>
    </form>
</body>
</html>
