<?php
// Test direct de l'API Mistral sans CV
require_once __DIR__ . '/../api_mistral.php';

$data = [
    'model'    => MISTRAL_MODEL,
    'messages' => [
        ['role' => 'user', 'content' => 'Dis bonjour en français.']
    ],
    'max_tokens' => 100,
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
echo "<b>JSON envoyé :</b><pre>" . htmlspecialchars($json) . "</pre>";

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
$error    = curl_error($ch);
curl_close($ch);

echo "<b>HTTP Code :</b> $httpCode<br>";
echo "<b>cURL Error :</b> " . ($error ?: 'aucune') . "<br>";
echo "<b>Réponse :</b><pre>" . htmlspecialchars($response) . "</pre>";
?>
