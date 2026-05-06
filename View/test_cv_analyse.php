<?php
require_once __DIR__ . '/../api_mistral.php';

// Simuler une analyse CV avec texte fixe
$cvText = "Ahmed Benali, développeur PHP, 3 ans d'expérience, Laravel, MySQL.";
$prompt = "Analyse ce CV et donne 3 améliorations : " . $cvText;

$data = [
    'model'    => MISTRAL_MODEL,
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens'  => 500,
    'temperature' => 0.7,
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

echo "<b>JSON valide :</b> " . ($json !== false ? 'OUI' : 'NON - ' . json_last_error_msg()) . "<br>";
echo "<b>Longueur :</b> " . strlen($json) . " octets<br>";
echo "<b>JSON :</b><pre>" . htmlspecialchars(substr($json, 0, 500)) . "</pre>";

$ch = curl_init(MISTRAL_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $json,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json),
        'Authorization: Bearer ' . MISTRAL_API_KEY,
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<b>HTTP Code :</b> $httpCode<br>";
$result = json_decode($response, true);
if ($httpCode === 200) {
    echo "<b style='color:green'>✅ Succès !</b><br>";
    echo "<pre>" . htmlspecialchars($result['choices'][0]['message']['content']) . "</pre>";
} else {
    echo "<b style='color:red'>❌ Erreur</b><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>
