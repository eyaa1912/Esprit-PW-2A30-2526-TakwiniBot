<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController
{
    private const PUBLICATION_TABLE = 'publication';
    private const SATISFACTION_TABLE = 'reclamation_satisfaction';
    private const VOICE_TABLE = 'reclamation_vocale';

    private function getPDO(): PDO
    {
        return config::getConnexion();
    }

    private function parsePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $intValue === false ? null : (int)$intValue;
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table'
        );
        $stmt->execute([':table' => $table]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function columnExists(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $stmt->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function getOrCreateFormulaireId(PDO $pdo, string $type): int
    {
        $stmt = $pdo->prepare('SELECT id FROM formulaire_reclamation WHERE type = :type LIMIT 1');
        $stmt->execute([':type' => $type]);
        $existingId = $stmt->fetchColumn();
        if ($existingId !== false) {
            return (int)$existingId;
        }

        if ($this->columnExists($pdo, 'formulaire_reclamation', 'date_creation')) {
            $insertStmt = $pdo->prepare(
                'INSERT INTO formulaire_reclamation (type, date_creation) VALUES (:type, NOW())'
            );
        } else {
            $insertStmt = $pdo->prepare('INSERT INTO formulaire_reclamation (type) VALUES (:type)');
        }
        $insertStmt->execute([':type' => $type]);

        return (int)$pdo->lastInsertId();
    }

    private function resolveUserId(PDO $pdo, ?int $userId = null, ?string $role = null, bool $allowAny = true): ?int
    {
        if (!$this->tableExists($pdo, 'users') || !$this->columnExists($pdo, 'users', 'id')) {
            return null;
        }

        if ($userId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $existingId = $stmt->fetchColumn();
            if ($existingId !== false) {
                return (int)$existingId;
            }
        }

        if ($role !== null && $this->columnExists($pdo, 'users', 'role')) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE role = :role ORDER BY id ASC LIMIT 1');
            $stmt->execute([':role' => $role]);
            $roleId = $stmt->fetchColumn();
            if ($roleId !== false) {
                return (int)$roleId;
            }
        }

        if (!$allowAny) {
            return null;
        }

        $stmt = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1');
        $fallbackId = $stmt->fetchColumn();

        return $fallbackId === false ? null : (int)$fallbackId;
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json; charset=utf-8');
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
            $flags |= JSON_PARTIAL_OUTPUT_ON_ERROR;
        }
        $json = json_encode($payload, $flags);
        if ($json === false) {
            $json = json_encode(
                ['success' => false, 'message' => 'Erreur interne (serialisation).'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) ?: '{"success":false,"message":"Erreur interne."}';
        }
        echo $json;
    }

    private function ensurePublicationTable(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS publication (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                titre VARCHAR(200) NOT NULL,
                contenu TEXT NOT NULL,
                image_path VARCHAR(255) NULL,
                statut ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_publication_statut (statut),
                INDEX idx_publication_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
    }

    private function ensureSatisfactionTable(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS reclamation_satisfaction (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reclamation_id INT NOT NULL,
                user_id INT NULL,
                emotion VARCHAR(30) NOT NULL,
                commentaire TEXT NULL,
                date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_reclamation_satisfaction (reclamation_id),
                INDEX idx_reclamation_satisfaction_emotion (emotion),
                INDEX idx_reclamation_satisfaction_reclamation (reclamation_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
        $this->ensureSatisfactionForeignKey($pdo);
    }

    private function ensureVoiceTable(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS reclamation_vocale (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                sujet VARCHAR(200) NOT NULL,
                audio_path VARCHAR(255) NOT NULL,
                duree_secondes INT NULL,
                date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_reclamation_vocale_user (user_id),
                INDEX idx_reclamation_vocale_date (date_creation)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
    }

    private function getTableEngine(PDO $pdo, string $table): ?string
    {
        $stmt = $pdo->prepare(
            'SELECT ENGINE
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table
             LIMIT 1'
        );
        $stmt->execute([':table' => $table]);
        $engine = $stmt->fetchColumn();
        return $engine === false ? null : strtolower((string)$engine);
    }

    private function getColumnType(PDO $pdo, string $table, string $column): ?string
    {
        $stmt = $pdo->prepare(
            'SELECT COLUMN_TYPE
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column
             LIMIT 1'
        );
        $stmt->execute([
            ':table' => $table,
            ':column' => $column,
        ]);
        $columnType = $stmt->fetchColumn();
        return $columnType === false ? null : strtolower((string)$columnType);
    }

    private function hasForeignKey(PDO $pdo, string $table, string $constraintName): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.table_constraints
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND constraint_name = :constraint_name
               AND constraint_type = :constraint_type'
        );
        $stmt->execute([
            ':table' => $table,
            ':constraint_name' => $constraintName,
            ':constraint_type' => 'FOREIGN KEY',
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function ensureSatisfactionForeignKey(PDO $pdo): void
    {
        $sourceTable = self::SATISFACTION_TABLE;
        $targetTable = 'reclamation';
        $constraintName = 'fk_reclamation_satisfaction_reclamation';

        if (
            !$this->tableExists($pdo, $sourceTable) ||
            !$this->tableExists($pdo, $targetTable) ||
            !$this->columnExists($pdo, $sourceTable, 'reclamation_id') ||
            !$this->columnExists($pdo, $targetTable, 'id')
        ) {
            return;
        }

        if ($this->hasForeignKey($pdo, $sourceTable, $constraintName)) {
            return;
        }

        // MySQL exige un type identique et un moteur compatible pour la FK.
        $sourceType = $this->getColumnType($pdo, $sourceTable, 'reclamation_id');
        $targetType = $this->getColumnType($pdo, $targetTable, 'id');
        $sourceEngine = $this->getTableEngine($pdo, $sourceTable);
        $targetEngine = $this->getTableEngine($pdo, $targetTable);

        if ($sourceType === null || $targetType === null) {
            return;
        }
        if ($sourceType !== $targetType) {
            return;
        }
        if ($sourceEngine !== 'innodb' || $targetEngine !== 'innodb') {
            return;
        }

        try {
            $pdo->exec(
                'ALTER TABLE reclamation_satisfaction
                 ADD CONSTRAINT fk_reclamation_satisfaction_reclamation
                 FOREIGN KEY (reclamation_id) REFERENCES reclamation(id) ON DELETE CASCADE'
            );
        } catch (Throwable $e) {
            // Ne pas bloquer l\'application si la FK ne peut pas être ajoutée.
        }
    }

    private function normalizeEmotion(string $emotion): string
    {
        $value = strtolower(trim($emotion));
        $allowed = ['tres_satisfait', 'satisfait', 'neutre', 'decu', 'frustre'];
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException('Emotion invalide.');
        }
        return $value;
    }

    private function getUploadsDirectory(): string
    {
        return dirname(__DIR__) . '/uploads/publications';
    }

    private function getVoiceUploadsDirectory(): string
    {
        return dirname(__DIR__) . '/uploads/reclamations-vocales';
    }

    private function storePublicationImage(array $file): ?string
    {
        if (!isset($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int)($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Erreur lors du televersement de l\'image.');
        }

        $maxSize = 5 * 1024 * 1024;
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxSize) {
            throw new InvalidArgumentException('Image invalide (taille maximale 5 MB).');
        }

        $tmpName = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Fichier image non valide.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmpName);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            throw new InvalidArgumentException('Format image non supporte (jpg, png, gif, webp).');
        }

        $uploadsDir = $this->getUploadsDirectory();
        if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0777, true) && !is_dir($uploadsDir)) {
            throw new RuntimeException('Impossible de creer le dossier des images.');
        }

        $filename = 'publication_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $targetAbsolute = $uploadsDir . '/' . $filename;
        if (!move_uploaded_file($tmpName, $targetAbsolute)) {
            throw new RuntimeException('Impossible d\'enregistrer l\'image.');
        }

        return 'uploads/publications/' . $filename;
    }

    private function storeVoiceMessage(array $file): string
    {
        if (!isset($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('Message vocal requis.');
        }
        if ((int)($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Erreur lors du televersement du message vocal.');
        }

        $maxSize = 15 * 1024 * 1024;
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxSize) {
            throw new InvalidArgumentException('Audio invalide (taille maximale 15 MB).');
        }

        $tmpName = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Fichier audio non valide.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string)$finfo->file($tmpName));
        $clientMime = strtolower((string)($file['type'] ?? ''));
        $clientName = (string)($file['name'] ?? '');
        $clientExt = strtolower((string)pathinfo($clientName, PATHINFO_EXTENSION));

        $allowedByMime = [
            'audio/webm' => 'webm',
            'video/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/vnd.wave' => 'wav',
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'video/mp4' => 'm4a',
            'audio/aac' => 'aac',
            'audio/x-aac' => 'aac',
        ];
        $allowedExt = ['webm', 'ogg', 'wav', 'mp3', 'm4a', 'aac'];

        $extension = $allowedByMime[$mime] ?? null;
        if ($extension === null && $clientMime !== '') {
            $extension = $allowedByMime[$clientMime] ?? null;
        }
        if ($extension === null && in_array($clientExt, $allowedExt, true)) {
            $extension = $clientExt;
        }
        if ($extension === null) {
            throw new InvalidArgumentException('Format audio non supporte (webm, ogg, wav, mp3, m4a, aac).');
        }

        $uploadsDir = $this->getVoiceUploadsDirectory();
        if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0777, true) && !is_dir($uploadsDir)) {
            throw new RuntimeException('Impossible de creer le dossier des messages vocaux.');
        }

        $filename = 'reclamation_vocale_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetAbsolute = $uploadsDir . '/' . $filename;
        if (!move_uploaded_file($tmpName, $targetAbsolute)) {
            throw new RuntimeException('Impossible d\'enregistrer le message vocal.');
        }

        return 'uploads/reclamations-vocales/' . $filename;
    }

    public function addPublication(array $data, array $files): int
    {
        $pdo = $this->getPDO();
        $this->ensurePublicationTable($pdo);

        $userId = $this->parsePositiveInt($data['user_id'] ?? null);
        $titre = trim((string)($data['titre'] ?? ''));
        $contenu = trim((string)($data['contenu'] ?? ''));
        $statut = 'pending';

        if ($titre === '' || mb_strlen($titre) < 3 || mb_strlen($titre) > 200) {
            throw new InvalidArgumentException('Titre invalide (3 a 200 caracteres).');
        }
        if ($contenu === '' || mb_strlen($contenu) < 10) {
            throw new InvalidArgumentException('Contenu invalide (minimum 10 caracteres).');
        }

        $resolvedUserId = $this->resolveUserId($pdo, $userId, null, true);
        $imagePath = $this->storePublicationImage($files['image'] ?? []);

        $stmt = $pdo->prepare(
            'INSERT INTO publication (user_id, titre, contenu, image_path, statut, date_creation, date_modification)
             VALUES (:user_id, :titre, :contenu, :image_path, :statut, NOW(), NOW())'
        );
        $stmt->bindValue(':user_id', $resolvedUserId, $resolvedUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindValue(':contenu', $contenu, PDO::PARAM_STR);
        $stmt->bindValue(':image_path', $imagePath, $imagePath === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$pdo->lastInsertId();
    }

    public function addVoiceReclamation(array $data, array $files): int
    {
        $pdo = $this->getPDO();
        $this->ensureVoiceTable($pdo);

        $userId = $this->parsePositiveInt($data['user_id'] ?? null);
        $sujet = trim((string)($data['sujet'] ?? ''));
        $dureeSecondes = $this->parsePositiveInt($data['duree_secondes'] ?? null);

        if ($sujet === '' || mb_strlen($sujet) < 3 || mb_strlen($sujet) > 200) {
            throw new InvalidArgumentException('Sujet invalide (3 a 200 caracteres).');
        }

        $resolvedUserId = $this->resolveUserId($pdo, $userId, null, true);
        $audioPath = $this->storeVoiceMessage($files['audio'] ?? []);

        $stmt = $pdo->prepare(
            'INSERT INTO reclamation_vocale (user_id, sujet, audio_path, duree_secondes, date_creation)
             VALUES (:user_id, :sujet, :audio_path, :duree_secondes, NOW())'
        );
        $stmt->bindValue(':user_id', $resolvedUserId, $resolvedUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':sujet', $sujet, PDO::PARAM_STR);
        $stmt->bindValue(':audio_path', $audioPath, PDO::PARAM_STR);
        $stmt->bindValue(':duree_secondes', $dureeSecondes, $dureeSecondes === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int)$pdo->lastInsertId();
    }

    public function listVoiceReclamations(?int $userId, string $idsRaw = '', bool $includeAllWhenEmpty = false): array
    {
        $ids = [];
        if ($idsRaw !== '') {
            $ids = array_filter(array_map('trim', explode(',', $idsRaw)), static function (string $value): bool {
                return $value !== '';
            });
            $ids = array_unique(array_map(static function (string $value): int {
                return (int)$value;
            }, $ids));
            $ids = array_values(array_filter($ids, static function (int $value): bool {
                return $value > 0;
            }));
        }

        if (empty($ids) && $userId === null && !$includeAllWhenEmpty) {
            return [];
        }

        $pdo = $this->getPDO();
        $this->ensureVoiceTable($pdo);

        $conditions = [];
        $params = [];
        if (!empty($ids)) {
            $idPlaceholders = [];
            foreach ($ids as $index => $id) {
                $paramName = ':id_' . $index;
                $idPlaceholders[] = $paramName;
                $params[$paramName] = $id;
            }
            $conditions[] = 'rv.id IN (' . implode(', ', $idPlaceholders) . ')';
        }
        if ($userId !== null) {
            $conditions[] = 'rv.user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $hasUsers = $this->tableExists($pdo, 'users') && $this->columnExists($pdo, 'users', 'id');
        $joins = [];
        $userNomSelect = 'NULL AS user_nom';
        $userPrenomSelect = 'NULL AS user_prenom';
        $userEmailSelect = 'NULL AS user_email';
        if ($hasUsers) {
            $joins[] = 'LEFT JOIN users u ON u.id = rv.user_id';
            if ($this->columnExists($pdo, 'users', 'nom')) {
                $userNomSelect = 'u.nom AS user_nom';
            }
            if ($this->columnExists($pdo, 'users', 'prenom')) {
                $userPrenomSelect = 'u.prenom AS user_prenom';
            }
            if ($this->columnExists($pdo, 'users', 'email')) {
                $userEmailSelect = 'u.email AS user_email';
            }
        }

        $whereSql = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $sql = "
            SELECT
                rv.id,
                rv.user_id,
                rv.sujet,
                rv.audio_path,
                rv.duree_secondes,
                rv.date_creation,
                {$userNomSelect},
                {$userPrenomSelect},
                {$userEmailSelect}
            FROM reclamation_vocale rv
            " . implode("\n            ", $joins) . "
            {$whereSql}
            ORDER BY rv.date_creation DESC
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $paramName => $value) {
            $type = str_starts_with($paramName, ':id_') || $paramName === ':user_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($paramName, $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listPublications(?int $userId, string $idsRaw = '', bool $includeAllWhenEmpty = false, ?string $statusFilter = null): array
    {
        $ids = [];
        if ($idsRaw !== '') {
            $ids = array_filter(array_map('trim', explode(',', $idsRaw)), static function (string $value): bool {
                return $value !== '';
            });
            $ids = array_unique(array_map(static function (string $value): int {
                return (int)$value;
            }, $ids));
            $ids = array_values(array_filter($ids, static function (int $value): bool {
                return $value > 0;
            }));
        }

        if (empty($ids) && $userId === null && !$includeAllWhenEmpty) {
            return [];
        }

        $pdo = $this->getPDO();
        $this->ensurePublicationTable($pdo);

        $conditions = [];
        $params = [];
        if (!empty($ids)) {
            $idPlaceholders = [];
            foreach ($ids as $index => $id) {
                $paramName = ':id_' . $index;
                $idPlaceholders[] = $paramName;
                $params[$paramName] = $id;
            }
            $conditions[] = 'p.id IN (' . implode(', ', $idPlaceholders) . ')';
        }
        if ($userId !== null) {
            $conditions[] = 'p.user_id = :user_id';
            $params[':user_id'] = $userId;
        }
        if ($statusFilter !== null && $statusFilter !== '') {
            $normalizedStatus = strtolower(trim($statusFilter));
            $allowedStatus = ['pending', 'approved', 'rejected'];
            if (!in_array($normalizedStatus, $allowedStatus, true)) {
                throw new InvalidArgumentException('Filtre statut invalide.');
            }
            $conditions[] = 'p.statut = :status_filter';
            $params[':status_filter'] = $normalizedStatus;
        }

        $hasUsers = $this->tableExists($pdo, 'users') && $this->columnExists($pdo, 'users', 'id');
        $joins = [];
        $userNomSelect = 'NULL AS user_nom';
        $userPrenomSelect = 'NULL AS user_prenom';
        $userEmailSelect = 'NULL AS user_email';
        if ($hasUsers) {
            $joins[] = 'LEFT JOIN users u ON u.id = p.user_id';
            if ($this->columnExists($pdo, 'users', 'nom')) {
                $userNomSelect = 'u.nom AS user_nom';
            }
            if ($this->columnExists($pdo, 'users', 'prenom')) {
                $userPrenomSelect = 'u.prenom AS user_prenom';
            }
            if ($this->columnExists($pdo, 'users', 'email')) {
                $userEmailSelect = 'u.email AS user_email';
            }
        }

        $whereSql = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $sql = "
            SELECT
                p.id,
                p.user_id,
                p.titre,
                p.contenu,
                p.image_path,
                p.statut,
                p.date_creation,
                p.date_modification,
                {$userNomSelect},
                {$userPrenomSelect},
                {$userEmailSelect}
            FROM publication p
            " . implode("\n            ", $joins) . "
            {$whereSql}
            ORDER BY p.date_creation DESC
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $paramName => $value) {
            $type = str_starts_with($paramName, ':id_') || $paramName === ':user_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($paramName, $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updatePublicationStatus(int $id, string $status): bool
    {
        $normalized = strtolower(trim($status));
        $allowed = ['pending', 'approved', 'rejected'];
        if (!in_array($normalized, $allowed, true)) {
            throw new InvalidArgumentException('Statut de publication invalide.');
        }

        $pdo = $this->getPDO();
        $this->ensurePublicationTable($pdo);
        $stmt = $pdo->prepare(
            'UPDATE publication
             SET statut = :statut, date_modification = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':statut' => $normalized,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function showReclamation(): void
    {
        $reclamations = $this->listReclamation();
        require __DIR__ . '/../views/Backend/backoffice/listreclamtion.php';
    }

    public function listReclamation(): array
    {
        return $this->getUserReclamations(null, '', true);
    }

    public function addReclamation(array $data): int
    {
        $pdo = $this->getPDO();

        $type = trim((string)($data['type'] ?? ''));
        $userId = $this->parsePositiveInt($data['user_id'] ?? null);
        $sujet = trim((string)($data['sujet'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));
        $statut = 'en_attente';

        if ($type === '' || mb_strlen($type) > 100) {
            throw new InvalidArgumentException('Type invalide.');
        }
        if ($sujet === '' || mb_strlen($sujet) < 3 || mb_strlen($sujet) > 200) {
            throw new InvalidArgumentException('Sujet invalide (3 a 200 caracteres).');
        }
        if ($message === '' || mb_strlen($message) < 5) {
            throw new InvalidArgumentException('Message invalide (minimum 5 caracteres).');
        }

        $resolvedUserId = $this->resolveUserId($pdo, $userId, null);
        $formulaireId = $this->getOrCreateFormulaireId($pdo, $type);

        $stmt = $pdo->prepare(
            'INSERT INTO reclamation (user_id, formulaire_id, sujet, message, statut, date_creation, date_modification)
             VALUES (:user_id, :formulaire_id, :sujet, :message, :statut, NOW(), NOW())'
        );

        $stmt->execute([
            ':user_id' => $resolvedUserId,
            ':formulaire_id' => $formulaireId,
            ':sujet' => $sujet,
            ':message' => $message,
            ':statut' => $statut,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function deleteReclamation(int $id, ?int $adminIdInput = null): bool
    {
        $pdo = $this->getPDO();

        $adminId = $this->resolveUserId($pdo, $adminIdInput, 'admin', true);
        if ($adminId === null) {
            throw new RuntimeException('Aucun administrateur disponible.');
        }

        $stmt = $pdo->prepare('DELETE FROM reclamation WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function getReclamation(int $id): ?Reclamation
    {
        $pdo = $this->getPDO();
        $stmt = $pdo->prepare(
            'SELECT id, user_id, formulaire_id, sujet, message, statut, date_creation, date_modification
             FROM reclamation
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return new Reclamation(
            (int)$row['id'],
            isset($row['user_id']) ? (int)$row['user_id'] : null,
            isset($row['formulaire_id']) ? (int)$row['formulaire_id'] : null,
            (string)$row['sujet'],
            (string)$row['message'],
            (string)$row['statut'],
            isset($row['date_creation']) ? (string)$row['date_creation'] : null,
            isset($row['date_modification']) ? (string)$row['date_modification'] : null
        );
    }

    public function updateReclamation(int $id, array $data): bool
    {
        $pdo = $this->getPDO();

        $sujet = trim((string)($data['sujet'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));
        $statut = trim((string)($data['statut'] ?? 'en_attente'));

        if ($sujet === '' || $message === '') {
            throw new InvalidArgumentException('Sujet et message obligatoires.');
        }

        $stmt = $pdo->prepare(
            'UPDATE reclamation
             SET sujet = :sujet,
                 message = :message,
                 statut = :statut,
                 date_modification = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':sujet' => $sujet,
            ':message' => $message,
            ':statut' => $statut,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function getUserReclamations(?int $userId, string $idsRaw = '', bool $includeAllWhenEmpty = false): array
    {
        $ids = [];
        if ($idsRaw !== '') {
            $ids = array_filter(array_map('trim', explode(',', $idsRaw)), static function (string $value): bool {
                return $value !== '';
            });
            $ids = array_unique(array_map(static function (string $value): int {
                return (int)$value;
            }, $ids));
            $ids = array_values(array_filter($ids, static function (int $value): bool {
                return $value > 0;
            }));
        }

        if (empty($ids) && $userId === null && !$includeAllWhenEmpty) {
            return [];
        }

        $pdo = $this->getPDO();
        $this->ensureSatisfactionTable($pdo);
        $conditions = [];
        $params = [];

        if (!empty($ids)) {
            $idPlaceholders = [];
            foreach ($ids as $index => $id) {
                $paramName = ':id_' . $index;
                $idPlaceholders[] = $paramName;
                $params[$paramName] = $id;
            }
            $conditions[] = 'r.id IN (' . implode(', ', $idPlaceholders) . ')';
        }

        if ($userId !== null) {
            $conditions[] = 'r.user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $hasFormulaire = $this->tableExists($pdo, 'formulaire_reclamation');
        $hasUsers = $this->tableExists($pdo, 'users') && $this->columnExists($pdo, 'users', 'id');
        $hasReponse = $this->tableExists($pdo, 'reponse');
        $hasSatisfaction = $this->tableExists($pdo, self::SATISFACTION_TABLE);

        $typeSelect = $hasFormulaire ? "COALESCE(fr.type, '-') AS type" : "'-' AS type";
        $userNomSelect = $hasUsers && $this->columnExists($pdo, 'users', 'nom') ? 'u.nom AS user_nom' : 'NULL AS user_nom';
        $userPrenomSelect = $hasUsers && $this->columnExists($pdo, 'users', 'prenom') ? 'u.prenom AS user_prenom' : 'NULL AS user_prenom';
        $userEmailSelect = $hasUsers && $this->columnExists($pdo, 'users', 'email') ? 'u.email AS user_email' : 'NULL AS user_email';
        $reponseSelect = $hasReponse ? 'rp.contenu AS reponse' : 'NULL AS reponse';
        $dateReponseSelect = $hasReponse ? 'rp.date_reponse' : 'NULL AS date_reponse';
        $satisfactionEmotionSelect = $hasSatisfaction ? 'rs.emotion AS satisfaction_emotion' : 'NULL AS satisfaction_emotion';
        $satisfactionCommentaireSelect = $hasSatisfaction ? 'rs.commentaire AS satisfaction_commentaire' : 'NULL AS satisfaction_commentaire';
        $satisfactionDateSelect = $hasSatisfaction ? 'rs.date_creation AS satisfaction_date' : 'NULL AS satisfaction_date';

        $joins = [];
        if ($hasFormulaire) {
            $joins[] = 'LEFT JOIN formulaire_reclamation fr ON fr.id = r.formulaire_id';
        }
        if ($hasUsers) {
            $joins[] = 'LEFT JOIN users u ON u.id = r.user_id';
        }
        if ($hasReponse) {
            $joins[] = "LEFT JOIN reponse rp ON rp.id = (
                SELECT rp2.id
                FROM reponse rp2
                WHERE rp2.reclamation_id = r.id
                ORDER BY rp2.date_reponse DESC, rp2.id DESC
                LIMIT 1
            )";
        }
        if ($hasSatisfaction) {
            $joins[] = 'LEFT JOIN reclamation_satisfaction rs ON rs.reclamation_id = r.id';
        }

        $whereSql = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT
                r.id,
                r.user_id,
                {$typeSelect},
                r.sujet,
                r.message,
                r.statut,
                r.date_creation,
                r.date_modification,
                {$userNomSelect},
                {$userPrenomSelect},
                {$userEmailSelect},
                {$reponseSelect},
                {$dateReponseSelect},
                {$satisfactionEmotionSelect},
                {$satisfactionCommentaireSelect},
                {$satisfactionDateSelect}
            FROM reclamation r
            " . implode("\n            ", $joins) . "
            {$whereSql}
            ORDER BY r.date_creation DESC
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $paramName => $value) {
            $stmt->bindValue($paramName, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function saveReclamationSatisfaction(int $reclamationId, string $emotion, string $commentaire = '', ?int $userIdInput = null): bool
    {
        $pdo = $this->getPDO();
        $this->ensureSatisfactionTable($pdo);

        $normalizedEmotion = $this->normalizeEmotion($emotion);
        $comment = trim($commentaire);
        if (mb_strlen($comment) > 800) {
            throw new InvalidArgumentException('Commentaire trop long (max 800 caracteres).');
        }

        $checkStmt = $pdo->prepare(
            'SELECT r.id, r.user_id
             FROM reclamation r
             INNER JOIN reponse rp ON rp.reclamation_id = r.id
             WHERE r.id = :id
             LIMIT 1'
        );
        $checkStmt->execute([':id' => $reclamationId]);
        $reclamation = $checkStmt->fetch();
        if (!$reclamation) {
            return false;
        }

        $resolvedUserId = $this->resolveUserId($pdo, $userIdInput, null, true);
        if ($resolvedUserId === null && isset($reclamation['user_id'])) {
            $resolvedUserId = (int)$reclamation['user_id'];
        }

        $upsertStmt = $pdo->prepare(
            'INSERT INTO reclamation_satisfaction (reclamation_id, user_id, emotion, commentaire, date_creation, date_modification)
             VALUES (:reclamation_id, :user_id, :emotion, :commentaire, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                emotion = VALUES(emotion),
                commentaire = VALUES(commentaire),
                date_modification = NOW()'
        );
        $upsertStmt->bindValue(':reclamation_id', $reclamationId, PDO::PARAM_INT);
        $upsertStmt->bindValue(':user_id', $resolvedUserId, $resolvedUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $upsertStmt->bindValue(':emotion', $normalizedEmotion, PDO::PARAM_STR);
        $upsertStmt->bindValue(':commentaire', $comment, $comment === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $upsertStmt->execute();

        return true;
    }

    public function replyReclamation(int $id, string $reponse, ?int $adminIdInput = null): bool
    {
        if ($reponse === '' || mb_strlen($reponse) < 3) {
            throw new InvalidArgumentException('Reponse invalide.');
        }

        $pdo = $this->getPDO();
        $adminId = $this->resolveUserId($pdo, $adminIdInput, 'admin', true);
        if ($adminId === null) {
            throw new RuntimeException('Aucun administrateur disponible.');
        }

        $checkStmt = $pdo->prepare('SELECT id FROM reclamation WHERE id = :id LIMIT 1');
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            $insertStmt = $pdo->prepare(
                'INSERT INTO reponse (reclamation_id, admin_id, contenu, date_reponse)
                 VALUES (:reclamation_id, :admin_id, :contenu, NOW())'
            );
            $insertStmt->execute([
                ':reclamation_id' => $id,
                ':admin_id' => $adminId,
                ':contenu' => $reponse,
            ]);

            $updateStmt = $pdo->prepare(
                'UPDATE reclamation
                 SET statut = :statut, date_modification = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                ':statut' => 'traite',
                ':id' => $id,
            ]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->jsonResponse(['success' => true]);
            return;
        }

        $action = (string)($_GET['action'] ?? '');

        try {
            if ($action === 'list') {
                $userId = $this->parsePositiveInt($_GET['user_id'] ?? null);
                $idsRaw = trim((string)($_GET['ids'] ?? ''));
                $includeAll = $userId === null && $idsRaw === '';

                $this->jsonResponse([
                    'success' => true,
                    'data' => $this->getUserReclamations($userId, $idsRaw, $includeAll),
                ]);
                return;
            }

            if ($action === 'add') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'M�thode non autoris�e.'], 405);
                    return;
                }

                $payload = $_POST;
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (stripos($contentType, 'application/json') !== false) {
                    $decoded = json_decode(file_get_contents('php://input') ?: '', true);
                    if (is_array($decoded)) {
                        $payload = $decoded;
                    }
                }

                $id = $this->addReclamation($payload);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'R�clamation enregistr�e.',
                    'id' => $id,
                    'data' => $this->getUserReclamations(null, (string)$id)[0] ?? null,
                ], 201);
                return;
            }

            if ($action === 'publication_add') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'Methode non autorisee.'], 405);
                    return;
                }

                $id = $this->addPublication($_POST, $_FILES);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Publication enregistree en attente de validation.',
                    'id' => $id,
                    'data' => $this->listPublications(null, (string)$id)[0] ?? null,
                ], 201);
                return;
            }

            if ($action === 'voice_add') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'Methode non autorisee.'], 405);
                    return;
                }

                $id = $this->addVoiceReclamation($_POST, $_FILES);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Reclamation vocale enregistree.',
                    'id' => $id,
                    'data' => $this->listVoiceReclamations(null, (string)$id)[0] ?? null,
                ], 201);
                return;
            }

            if ($action === 'voice_list') {
                $userId = $this->parsePositiveInt($_GET['user_id'] ?? null);
                $idsRaw = trim((string)($_GET['ids'] ?? ''));
                $includeAll = $userId === null && $idsRaw === '';
                $this->jsonResponse([
                    'success' => true,
                    'data' => $this->listVoiceReclamations($userId, $idsRaw, $includeAll),
                ]);
                return;
            }

            if ($action === 'publication_list') {
                $userId = $this->parsePositiveInt($_GET['user_id'] ?? null);
                $idsRaw = trim((string)($_GET['ids'] ?? ''));
                $status = trim((string)($_GET['status'] ?? ''));
                $includeAll = $userId === null && $idsRaw === '';
                $this->jsonResponse([
                    'success' => true,
                    'data' => $this->listPublications($userId, $idsRaw, $includeAll, $status === '' ? null : $status),
                ]);
                return;
            }

            if ($action === 'publication_status') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'Methode non autorisee.'], 405);
                    return;
                }

                $id = $this->parsePositiveInt($_POST['id'] ?? null);
                $status = trim((string)($_POST['status'] ?? ''));
                if ($id === null) {
                    $this->jsonResponse(['success' => false, 'message' => 'ID invalide.'], 400);
                    return;
                }
                $updated = $this->updatePublicationStatus($id, $status);
                if (!$updated) {
                    $this->jsonResponse(['success' => false, 'message' => 'Publication non trouvee.'], 404);
                    return;
                }
                $this->jsonResponse(['success' => true, 'message' => 'Statut de publication mis a jour.']);
                return;
            }

            if ($action === 'reply') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'M�thode non autoris�e.'], 405);
                    return;
                }

                $id = $this->parsePositiveInt($_POST['id'] ?? null);
                $reponse = trim((string)($_POST['reponse'] ?? ''));
                if ($id === null) {
                    $this->jsonResponse(['success' => false, 'message' => 'ID invalide.'], 400);
                    return;
                }

                $replied = $this->replyReclamation($id, $reponse);
                if (!$replied) {
                    $this->jsonResponse(['success' => false, 'message' => 'R�clamation non trouv�e.'], 404);
                    return;
                }

                $this->jsonResponse(['success' => true, 'message' => 'R�ponse enregistr�e.']);
                return;
            }
            if ($action === 'satisfaction_submit') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->jsonResponse(['success' => false, 'message' => 'Methode non autorisee.'], 405);
                    return;
                }

                $id = $this->parsePositiveInt($_POST['id'] ?? null);
                $emotion = trim((string)($_POST['emotion'] ?? ''));
                $commentaire = trim((string)($_POST['commentaire'] ?? ''));
                $userId = $this->parsePositiveInt($_POST['user_id'] ?? null);
                if ($id === null) {
                    $this->jsonResponse(['success' => false, 'message' => 'ID invalide.'], 400);
                    return;
                }

                $saved = $this->saveReclamationSatisfaction($id, $emotion, $commentaire, $userId);
                if (!$saved) {
                    $this->jsonResponse(['success' => false, 'message' => 'Reclamation non trouvee.'], 404);
                    return;
                }

                $this->jsonResponse(['success' => true, 'message' => 'Merci, votre retour a ete enregistre.']);
                return;
            }
            if ($action === 'delete') {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                    $this->jsonResponse(['success' => false, 'message' => 'M�thode non autoris�e.'], 405);
                    return;
                }

                $id = $this->parsePositiveInt($_POST['id'] ?? $_GET['id'] ?? null);
                if ($id === null) {
                    $this->jsonResponse(['success' => false, 'message' => 'ID invalide.'], 400);
                    return;
                }

                $deleted = $this->deleteReclamation($id);
                if (!$deleted) {
                    $this->jsonResponse(['success' => false, 'message' => 'R�clamation non trouv�e.'], 404);
                    return;
                }

                $this->jsonResponse(['success' => true, 'message' => 'R�clamation supprim�e.']);
                return;
            }

            $this->jsonResponse(['success' => false, 'message' => 'Action inconnue.'], 404);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}

(new ReclamationController())->handleRequest();
