<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Controller/CvAnalyseController.php';

$ctrl       = new CvAnalyseController();
$offreTitre = trim($_POST['offre_titre'] ?? '');
$offreType  = trim($_POST['offre_type']  ?? '');

// Priorité 1 : texte collé directement
$cvText = trim($_POST['cv_text'] ?? '');

$uploadExt = '';

// Priorité 2 : fichier uploadé
if ($cvText === '' && !empty($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cv'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['pdf', 'doc', 'docx'], true)) {
        $uploadExt = $ext;
        $tmpPath   = sys_get_temp_dir() . '/cv_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $tmpPath)) {
            $cvText = $ctrl->extractText($tmpPath);
            @unlink($tmpPath);
        }
    }
}

if ($cvText === '') {
    $msg = 'Aucun contenu CV reçu.';
    if ($uploadExt === 'doc') {
        $msg = 'Les fichiers .doc (Word 97–2003) ne sont pas lus correctement ici. Enregistrez votre document au format .docx ou PDF puis réessayez.';
    }
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'not_cv'  => $uploadExt !== '',
    ]);
    exit;
}

$cvCheck = $ctrl->validateEstCv($cvText);
if (!$cvCheck['is_cv']) {
    echo json_encode([
        'success' => false,
        'message' => $cvCheck['message'] ?? 'Document non reconnu comme CV.',
        'not_cv'  => true,
    ]);
    exit;
}

$result = $ctrl->analyserCv($cvText, $offreTitre, $offreType);
echo json_encode($result);
exit;
?>
