<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Model/Entretien.php';
require_once __DIR__ . '/../Model/TypeEntretien.php';

class EntretienController {
    public function listEntretiens(): array {
        $db = config::getConnexion();

        try {
            $sql = 'SELECT e.*, te.libelle AS type_entretien_libelle,
                           te.duree_prevue AS type_entretien_duree_prevue,
                           te.description AS type_entretien_description
                    FROM entretien e
                    LEFT JOIN type_entretien te ON te.id = e.type_entretien_id
                    ORDER BY e.date_entretien DESC, e.heure_entretien DESC, e.id DESC';

            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addEntretien(Entretien $e): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('INSERT INTO entretien (
                nom_candidat,
                email_candidat,
                genre,
                type_handicap,
                amenagements,
                type_entretien_id,
                date_entretien,
                heure_entretien,
                poste_cible,
                metier_suggere,
                score_rse,
                remarques,
                statut,
                created_at
            ) VALUES (
                :nom_candidat,
                :email_candidat,
                :genre,
                :type_handicap,
                :amenagements,
                :type_entretien_id,
                :date_entretien,
                :heure_entretien,
                :poste_cible,
                :metier_suggere,
                :score_rse,
                :remarques,
                :statut,
                NOW()
            )');

            $stmt->execute([
                'nom_candidat'      => $e->getNomCandidat(),
                'email_candidat'    => $e->getEmailCandidat(),
                'genre'             => $e->getGenre(),
                'type_handicap'     => $e->getTypeHandicap(),
                'amenagements'      => $e->getAmenagements(),
                'type_entretien_id' => $e->getTypeEntretienId(),
                'date_entretien'    => $e->getDateEntretien(),
                'heure_entretien'   => $e->getHeureEntretien(),
                'poste_cible'       => $e->getPosteCible(),
                'metier_suggere'    => $e->getMetierSuggere(),
                'score_rse'         => $e->getScoreRse(),
                'remarques'         => $e->getRemarques(),
                'statut'            => $e->getStatut(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteEntretien(int $id): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('DELETE FROM entretien WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getEntretien(int $id): array|false {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare(
                'SELECT e.*,
                        te.libelle AS type_entretien_libelle,
                        te.duree_prevue AS type_entretien_duree_prevue,
                        te.description AS type_entretien_description
                 FROM entretien e
                 LEFT JOIN type_entretien te ON te.id = e.type_entretien_id
                 WHERE e.id = :id'
            );
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateEntretien(int $id, Entretien $e): void {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare(
                'UPDATE entretien SET
                    nom_candidat       = :nom_candidat,
                    email_candidat     = :email_candidat,
                    genre              = :genre,
                    type_handicap      = :type_handicap,
                    amenagements       = :amenagements,
                    type_entretien_id  = :type_entretien_id,
                    date_entretien     = :date_entretien,
                    heure_entretien    = :heure_entretien,
                    poste_cible        = :poste_cible,
                    metier_suggere     = :metier_suggere,
                    score_rse          = :score_rse,
                    remarques          = :remarques,
                    statut             = :statut
                 WHERE id = :id'
            );

            $stmt->execute([
                'id'                => $id,
                'nom_candidat'      => $e->getNomCandidat(),
                'email_candidat'    => $e->getEmailCandidat(),
                'genre'             => $e->getGenre(),
                'type_handicap'     => $e->getTypeHandicap(),
                'amenagements'      => $e->getAmenagements(),
                'type_entretien_id' => $e->getTypeEntretienId(),
                'date_entretien'    => $e->getDateEntretien(),
                'heure_entretien'   => $e->getHeureEntretien(),
                'poste_cible'       => $e->getPosteCible(),
                'metier_suggere'    => $e->getMetierSuggere(),
                'score_rse'         => $e->getScoreRse(),
                'remarques'         => $e->getRemarques(),
                'statut'            => $e->getStatut(),
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function listTypeEntretiens(): array {
        $db = config::getConnexion();

        try {
            $stmt = $db->prepare('SELECT id as id_type_entretien, libelle, duree_prevue, description FROM type_entretien ORDER BY libelle ASC');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint for AI-powered job suggestions.
     */
    public function suggestMetier(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest')) {
            http_response_code(405);
            echo json_encode(['error' => 'Requête non autorisée.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput ?: '', true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données JSON invalides.']);
            exit;
        }

        $payload = $this->normalizeSuggestionPayload($input);
        $validationErrors = $this->validateSuggestionPayload($payload);

        if ($validationErrors !== []) {
            http_response_code(422);
            echo json_encode(['error' => implode(' ', $validationErrors)]);
            exit;
        }

        $scoreRse = $this->calculateScoreRse($payload['amenagements']);
        $apiKey = getenv('ANTHROPIC_API_KEY') ?: getenv('CLAUDE_API_KEY') ?: '';

        if ($apiKey !== '') {
            $apiResponse = $this->callClaudeApi(
                $apiKey,
                $payload['type_handicap'],
                $payload['amenagements'],
                $payload['poste_cible'],
                $payload['genre'],
                $payload['type_entretien_id'],
                $scoreRse
            );

            if (!isset($apiResponse['error'])) {
                // Use Claude's score if provided, otherwise use calculated score
                $finalScore = $apiResponse['score_rse'] ?? $scoreRse;
                echo json_encode([
                    'suggestions' => $apiResponse['suggestions'],
                    'score_rse' => $finalScore,
                    'source' => 'claude',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }

        $fallbackSuggestions = $this->buildFallbackSuggestions($payload['type_handicap'], $payload['poste_cible'], $payload['amenagements']);

        echo json_encode([
            'suggestions' => $fallbackSuggestions,
            'score_rse' => $scoreRse,
            'source' => 'local_fallback',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function normalizeSuggestionPayload(array $input): array
    {
        return [
            'type_handicap' => trim((string) ($input['type_handicap'] ?? '')),
            'amenagements' => trim((string) ($input['amenagements'] ?? '')),
            'poste_cible' => trim((string) ($input['poste_cible'] ?? '')),
            'genre' => trim((string) ($input['genre'] ?? '')),
            'type_entretien_id' => (int) ($input['type_entretien_id'] ?? 0),
        ];
    }

    private function validateSuggestionPayload(array $payload): array
    {
        $errors = [];

        if ($payload['type_handicap'] === '') {
            $errors[] = 'Le type de handicap est requis.';
        }

        if ($payload['poste_cible'] === '') {
            $errors[] = 'Le poste cible est requis.';
        }

        if (!in_array($payload['genre'], ['homme', 'femme'], true)) {
            $errors[] = 'Le genre est invalide.';
        }

        if ($payload['type_entretien_id'] <= 0) {
            $errors[] = 'Le type d\'entretien est requis.';
        }

        return $errors;
    }

    /**
     * Calculates a score on a 0-100 scale from the requested aménagements.
     */
    private function calculateScoreRse(string $amenagements): int
    {
        $amenagements = trim($amenagements);
        if ($amenagements === '') {
            return 0;
        }

        $score = 10;
        $normalized = mb_strtolower($amenagements, 'UTF-8');

        $weights = [
            'accessibilité' => 18,
            'accessibilite' => 18,
            'rampe' => 12,
            'ascenseur' => 12,
            'ergonom' => 10,
            'bureau réglable' => 10,
            'bureau reglable' => 10,
            'lecteur d écran' => 15,
            'lecteur d ecran' => 15,
            'sous-titr' => 15,
            'interprète' => 15,
            'interprete' => 15,
            'braille' => 15,
            'horaires flex' => 10,
            'télétravail' => 12,
            'teletravail' => 12,
            'environnement calme' => 10,
            'pauses courtes' => 8,
            'temps supplémentaire' => 8,
            'temps supplementaire' => 8,
            'transport' => 10,
            'logiciel' => 10,
            'matériel' => 10,
            'materiel' => 10,
        ];

        foreach ($weights as $needle => $points) {
            if (str_contains($normalized, $needle)) {
                $score += $points;
            }
        }

        if (str_contains($normalized, ',') || str_contains($normalized, ';')) {
            $score += 5;
        }

        return min(100, $score);
    }

    private function buildFallbackSuggestions(string $typeHandicap, string $posteCible, string $amenagements): array
    {
        $normalizedHandicap = mb_strtolower($typeHandicap, 'UTF-8');
        $normalizedPoste = trim($posteCible);

        $catalog = [
            'moteur' => [
                ['metier' => 'Développeur web', 'justification' => 'Le travail est principalement sédentaire et compatible avec le télétravail. Les aménagements ergonomiques renforcent l’autonomie.'],
                ['metier' => 'Analyste de données', 'justification' => 'L’environnement numérique réduit les contraintes de mobilité. Le poste valorise l’analyse et la concentration.'],
                ['metier' => 'Gestionnaire de projet digital', 'justification' => 'La coordination peut se faire à distance avec des outils collaboratifs. Les déplacements physiques restent limités.'],
            ],
            'visuel' => [
                ['metier' => 'Développeur logiciel', 'justification' => 'Les lecteurs d’écran et outils d’accessibilité rendent ce métier pleinement praticable. Les livrables sont majoritairement numériques.'],
                ['metier' => 'Rédacteur web', 'justification' => 'Le poste s’appuie sur la structuration de contenu et le travail rédactionnel. Il s’adapte bien aux outils d’assistance vocale.'],
                ['metier' => 'Support client par téléphone', 'justification' => 'L’activité repose surtout sur l’échange oral et la résolution de problèmes. Les interfaces peuvent être adaptées pour la navigation clavier.'],
            ],
            'auditif' => [
                ['metier' => 'Développeur front-end', 'justification' => 'La collaboration écrite et la logique de code sont centrales dans ce métier. Les consignes peuvent être transmises visuellement.'],
                ['metier' => 'Designer graphique', 'justification' => 'Le travail créatif se fait de manière visuelle et documentaire. Les échanges peuvent être facilités par messagerie ou sous-titrage.'],
                ['metier' => 'Comptable', 'justification' => 'Les tâches sont structurées, écrites et facilement documentées. Les réunions peuvent être adaptées avec interprétation ou transcription.'],
            ],
            'psychique' => [
                ['metier' => 'Rédacteur contenu', 'justification' => 'Le rythme de travail peut être ajusté selon les besoins. Le cadre de travail reste calme et prévisible.'],
                ['metier' => 'Archiviste numérique', 'justification' => 'Les tâches sont organisées, répétitives et peu exposées à la pression sociale. Cela favorise la stabilité professionnelle.'],
                ['metier' => 'Technicien support applicatif', 'justification' => 'Le poste est structuré et peut être exercé avec des consignes claires. Les aménagements d’équipe facilitent l’intégration.'],
            ],
        ];

        $suggestions = null;
        foreach ($catalog as $key => $items) {
            if (str_contains($normalizedHandicap, $key)) {
                $suggestions = $items;
                break;
            }
        }

        if ($suggestions === null) {
            $suggestions = [
                ['metier' => 'Assistant administratif', 'justification' => 'Le poste est facilement adaptable avec des outils numériques et des aménagements simples.'],
                ['metier' => 'Chargé de support client', 'justification' => 'Les missions peuvent être organisées autour d’outils écrits et de procédures claires.'],
                ['metier' => 'Coordinateur digital', 'justification' => 'Les tâches sont compatibles avec le télétravail et une organisation flexible.'],
            ];
        }

        if ($normalizedPoste !== '') {
            $suggestions[0]['metier'] = $normalizedPoste;
            $suggestions[0]['justification'] = 'Le poste de ' . $posteCible . ' peut être adapté avec les aménagements suivants : ' . ($amenagements !== '' ? $amenagements : 'ceux indiqués par le candidat') . '. La compatibilité avec ce profil reste bonne sous réserve d\'adaptations ciblées.';
        }

        return array_slice($this->sanitizeSuggestionItems($suggestions), 0, 3);
    }

    private function callClaudeApi(
        string $apiKey,
        string $typeHandicap,
        string $amenagements,
        string $posteCible,
        string $genre,
        int $typeEntretienId,
        int $scoreRse
    ): array {
        $prompt = <<<PROMPT
Tu es un conseiller RH spécialisé dans l'emploi inclusif en Tunisie.
Analyse le profil suivant et propose exactement 3 suggestions de métiers.

Contexte candidat:
- Genre: {$genre}
- Type de handicap: {$typeHandicap}
- Aménagements demandés: {$amenagements}
- Poste cible: {$posteCible}
- Type d'entretien: {$typeEntretienId}

Règles:
- Réponds uniquement en JSON valide.
- Chaque suggestion doit avoir les clés "metier" et "justification".
- La justification doit expliquer pourquoi ce métier convient au profil et inclure une logique RSE.
- Donne 2 à 3 lignes courtes par justification, sans liste à puces.
- Génère aussi un "score_rse" (entier entre 1 et 5) basé sur l'alignement du profil avec les valeurs RSE (inclusion, diversité, accessibilité).

Format exact attendu:
{
  "suggestions": [
    {"metier": "...", "justification": "..."},
    {"metier": "...", "justification": "..."},
    {"metier": "...", "justification": "..."}
  ],
  "score_rse": 3
}
PROMPT;

        $payload = json_encode([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 900,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            return ['error' => 'Impossible de préparer la requête Claude.'];
        }

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return ['error' => 'Erreur réseau: ' . ($curlError ?: 'réponse vide')];
        }

        if ($httpCode !== 200) {
            return ['error' => 'Erreur Claude HTTP ' . $httpCode . '.'];
        }

        $decoded = json_decode($response, true);
        $text = (string) ($decoded['content'][0]['text'] ?? '');
        $json = $this->extractJsonBlock($text);

        if ($json === null) {
            return ['error' => 'Réponse Claude invalide.'];
        }

        $parsed = json_decode($json, true);
        if (!is_array($parsed) || !isset($parsed['suggestions']) || !is_array($parsed['suggestions'])) {
            return ['error' => 'Format de réponse Claude inattendu.'];
        }

        $suggestions = $this->sanitizeSuggestionItems($parsed['suggestions']);
        if (count($suggestions) < 2) {
            return ['error' => 'Nombre de suggestions insuffisant.'];
        }

        // Extract score_rse from Claude response if provided
        $claudeScore = null;
        if (isset($parsed['score_rse']) && is_int($parsed['score_rse'])) {
            $claudeScore = max(1, min(5, $parsed['score_rse'])); // Ensure it's between 1-5
        }

        return [
            'suggestions' => array_slice($suggestions, 0, 3),
            'score_rse' => $claudeScore,
        ];
    }

    private function extractJsonBlock(string $text): ?string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }

    private function sanitizeSuggestionItems(array $items): array
    {
        $suggestions = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $metier = trim((string) ($item['metier'] ?? ''));
            $justification = trim((string) ($item['justification'] ?? ''));

            if ($metier === '' || $justification === '') {
                continue;
            }

            $suggestions[] = [
                'metier' => $metier,
                'justification' => $justification,
            ];
        }

        return $suggestions;
    }
}
