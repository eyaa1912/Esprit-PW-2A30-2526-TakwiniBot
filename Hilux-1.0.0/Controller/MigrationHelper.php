<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class MigrationHelper {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * Add interview_token column to entretien table if it doesn't exist
     */
    public function addInterviewTokenColumn(): bool {
        try {
            // Check if column exists
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'entretien' AND COLUMN_NAME = 'interview_token'";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Column already exists
                return true;
            }

            // Add the column
            $sql = "ALTER TABLE entretien ADD COLUMN interview_token VARCHAR(255) NULL UNIQUE";
            $this->pdo->exec($sql);
            
            return true;
        } catch (Exception $e) {
            error_log('Migration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Run all migrations
     */
    public function runMigrations(): bool {
        return $this->addInterviewTokenColumn();
    }
}
