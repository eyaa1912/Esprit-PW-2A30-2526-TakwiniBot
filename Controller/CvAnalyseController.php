<?php

require_once __DIR__ . '/../api_mistral.php';

class CvAnalyseController
{
    public function extractText(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'pdf')                    return $this->extractFromPdf($filePath);
        if (in_array($ext, ['doc', 'docx']))   return $this->extractFromDoc($filePath);
        return '';
    }

    private function extractFromPdf(string $filePath): string
    {
        // Essayer pdftotext (Poppler) — chemins possibles
        $pdftotextPaths = [
            'C:\\poppler\\bin\\pdftotext.exe',
            'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
            'C:\\xampp\\poppler\\bin\\pdftotext.exe',
            'pdftotext', // Si dans le PATH système
        ];

        foreach ($pdftotextPaths as $path) {
            $cmd    = '"' . $path . '" -enc UTF-8 "' . $filePath . '" -';
            $output = @shell_exec($cmd);
            if ($output && strlen(trim($output)) > 20) {
                return $this->cleanText($output);
            }
        }

        // Fallback PHP : extraction basique
        $content = file_get_contents($filePath);
        if (!$content) return '';

        $text = '';
        preg_match_all('/BT(.*?)ET/s', $content, $btMatches);
        foreach ($btMatches[1] as $bt) {
            preg_match_all('/\((.*?)\)/', $bt, $strMatches);
            foreach ($strMatches[1] as $s) {
                $clean = preg_replace('/[^\x20-\x7E]/', ' ', $s);
                if (strlen(trim($clean)) > 1) $text .= $clean . ' ';
            }
        }

        if (strlen(trim($text)) < 30) {
            preg_match_all('/\(([^\)]{2,100})\)/', $content, $matches);
            foreach ($matches[1] as $m) {
                $clean = preg_replace('/[^\x20-\x7E]/', ' ', $m);
                if (strlen(trim($clean)) > 2) $text .= $clean . ' ';
            }
        }

        return $this->cleanText($text) ?: 'CV PDF recu.';
    }

    private function extractFromDoc(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'docx') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xml) {
                    $text = strip_tags(str_replace(['</w:p>', '</w:r>'], ["\n", ' '], $xml));
                    return $this->cleanText($text);
                }
            }
        }
        return $this->cleanText(file_get_contents($filePath) ?: '');
    }

    private function cleanText(string $text): string
    {
        // Supprimer caractères de contrôle
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $text);
        // Normaliser espaces
        $text = preg_replace('/\s+/', ' ', $text);
        // Forcer UTF-8 valide
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Supprimer caractères non-UTF8
        $text = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', ' ', $text);
        return trim($text);
    }

    public function analyserCv(string $cvText, string $offreTitre = '', string $offreType = ''): array
    {
        // Nettoyer et limiter
        $cvText = $this->cleanText($cvText);
        if (strlen($cvText) < 20) {
            $cvText = 'CV recu mais contenu non lisible automatiquement.';
        }
        $cvText = mb_substr($cvText, 0, 2000);

        // Construire le prompt sans caractères spéciaux
        $contexte = $offreTitre ? "pour le poste : $offreTitre" : '';
        $prompt   = "Tu es consultant RH. Analyse ce CV $contexte. Sois bref et direct.\n\n"
                  . "Reponds avec ce format exact, sans emojis :\n\n"
                  . "POINTS FORTS\n"
                  . "- [point 1]\n"
                  . "- [point 2]\n"
                  . "- [point 3]\n\n"
                  . "AXES D'AMELIORATION\n"
                  . "1. [titre] : [une phrase]\n"
                  . "2. [titre] : [une phrase]\n"
                  . "3. [titre] : [une phrase]\n\n"
                  . "SCORE : [X]/10 — [justification en une phrase]\n\n"
                  . "CV :\n" . $cvText;

        $data = [
            'model'       => MISTRAL_MODEL,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'max_tokens'  => 1500,
            'temperature' => 0.7,
        ];

        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonData === false) {
            return ['success' => false, 'message' => 'Erreur encodage JSON : ' . json_last_error_msg()];
        }

        $ch = curl_init(MISTRAL_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
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

        if ($error) {
            return ['success' => false, 'message' => 'Erreur reseau : ' . $error];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200 || empty($result['choices'][0]['message']['content'])) {
            $errMsg = isset($result['error']['message'])
                ? $result['error']['message']
                : 'Erreur API (code ' . $httpCode . ') : ' . $response;
            return ['success' => false, 'message' => $errMsg];
        }

        return [
            'success' => true,
            'analyse' => $result['choices'][0]['message']['content'],
        ];
    }
}
?>
