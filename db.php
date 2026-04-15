<?php
declare(strict_types=1);

function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestData(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    return $_POST;
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = '127.0.0.1';
    $dbName = 'projet_takwini';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        jsonResponse(500, [
            'success' => false,
            'message' => 'Connexion a la base de donnees impossible.',
            'error' => $e->getMessage(),
        ]);
    }

    return $pdo;
}

function parsePositiveInt(mixed $value): ?int
{
    $parsed = filter_var($value, FILTER_VALIDATE_INT);
    if ($parsed === false || $parsed <= 0) {
        return null;
    }

    return (int)$parsed;
}

function resolveUserId(
    PDO $pdo,
    ?int $requestedUserId = null,
    ?string $requiredRole = null,
    bool $allowAnyUserFallback = true
): ?int
{
    if ($requestedUserId !== null) {
        if ($requiredRole !== null) {
            $stmt = $pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE id = :id AND role = :role
                 LIMIT 1'
            );
            $stmt->execute([
                ':id' => $requestedUserId,
                ':role' => $requiredRole,
            ]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $requestedUserId]);
        }

        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }
    }

    if ($requiredRole !== null) {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE role = :role
             ORDER BY id ASC
             LIMIT 1'
        );
        $stmt->execute([':role' => $requiredRole]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }
    }

    if ($allowAnyUserFallback) {
        $stmt = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }
    }

    return null;
}

function getOrCreateFormulaireId(PDO $pdo, string $type): int
{
    $selectStmt = $pdo->prepare(
        'SELECT id
         FROM formulaire_reclamation
         WHERE type = :type
         LIMIT 1'
    );
    $selectStmt->execute([':type' => $type]);
    $existing = $selectStmt->fetch();

    if ($existing) {
        return (int)$existing['id'];
    }

    $insertStmt = $pdo->prepare(
        'INSERT INTO formulaire_reclamation (type, champs, est_actif)
         VALUES (:type, :champs, :est_actif)'
    );
    $insertStmt->execute([
        ':type' => $type,
        ':champs' => null,
        ':est_actif' => 1,
    ]);

    return (int)$pdo->lastInsertId();
}
