<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Controller/CvAnalyseController.php';

$ctrl       = new CvAnalyseController();
$offreTitre = trim($_POST['offre_titre'] ?? '');
$offreType  = trim($_POST['offre_type']  ?? '');

// Priorité 1 : texte collé directement
$cvText = trim($_POST['cv_text'] ?? '');

// Priorité 2 : fichier uploadé
if (empty($cvText) && !empty($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cv'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['pdf', 'doc', 'docx'])) {
        $tmpPath = sys_get_temp_dir() . '/cv_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $tmpPath)) {
            $cvText = $ctrl->extractText($tmpPath);
            @unlink($tmpPath);
        }
    }
}

if (empty($cvText)) {
    echo json_encode(['success' => false, 'message' => 'Aucun contenu CV reçu.']);
    exit;
}

$result = $ctrl->analyserCv($cvText, $offreTitre, $offreType);
echo json_encode($result);
exit;
?>
