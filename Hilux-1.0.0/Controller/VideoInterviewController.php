<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Entretien.php';
require_once __DIR__ . '/../config/database.php';

class VideoInterviewController {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * Get interview questions based on candidate profile
     */
    public function getInterviewQuestions(int $entretienId): array {
        try {
            $entretien = $this->getEntretienData($entretienId);
            if (!$entretien) {
                return ['error' => 'Entretien not found'];
            }

            $questions = $this->generateQuestions($entretien);
            return [
                'success' => true,
                'questions' => $questions,
                'candidate' => [
                    'nom' => $entretien['nom_candidat'],
                    'poste' => $entretien['poste_cible'],
                    'type_handicap' => $entretien['type_handicap'],
                    'amenagements' => $entretien['amenagements']
                ]
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get entretien data from database
     */
    private function getEntretienData(int $entretienId): array|false {
        $db = config::getConnexion();
        $sql = "SELECT * FROM entretien WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $entretienId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate interview questions based on candidate profile
     */
    private function generateQuestions(array $entretien): array {
        $typeHandicap = $entretien['type_handicap'];
        $posteCible = $entretien['poste_cible'];
        $amenagements = $entretien['amenagements'] ?? '';

        $questions = [
            // Introduction
            [
                'id' => 1,
                'type' => 'introduction',
                'text' => "Bonjour {$entretien['nom_candidat']}, je suis Takwini, votre interviewer. Merci de participer à cet entretien pour le poste de {$posteCible}. Êtes-vous prêt à commencer?",
                'duration' => 5,
                'allowSkip' => false
            ],
            // Experience
            [
                'id' => 2,
                'type' => 'experience',
                'text' => "Parlez-moi de votre expérience professionnelle pertinente pour le poste de {$posteCible}.",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Skills
            [
                'id' => 3,
                'type' => 'skills',
                'text' => "Quelles sont vos compétences principales pour ce rôle?",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Motivation
            [
                'id' => 4,
                'type' => 'motivation',
                'text' => "Pourquoi êtes-vous intéressé par ce poste?",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Accommodations
            [
                'id' => 5,
                'type' => 'accommodations',
                'text' => "Avez-vous besoin d'aménagements spécifiques pour travailler efficacement?",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Strengths
            [
                'id' => 6,
                'type' => 'strengths',
                'text' => "Quels sont vos points forts?",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Challenges
            [
                'id' => 7,
                'type' => 'challenges',
                'text' => "Comment gérez-vous les défis au travail?",
                'duration' => 30,
                'allowSkip' => false
            ],
            // Questions
            [
                'id' => 8,
                'type' => 'questions',
                'text' => "Avez-vous des questions pour moi ou pour l'entreprise?",
                'duration' => 30,
                'allowSkip' => true
            ],
            // Closing
            [
                'id' => 9,
                'type' => 'closing',
                'text' => "Merci beaucoup pour cet entretien! Vous avez été formidable. Nous vous recontacterons bientôt avec notre décision.",
                'duration' => 5,
                'allowSkip' => false
            ]
        ];

        return $questions;
    }

    /**
     * Save interview answers to database
     */
    public function saveInterviewAnswers(int $entretienId, array $answers): array {
        try {
            $db = config::getConnexion();
            $sql = "UPDATE entretien SET remarques = :remarques, statut = :statut WHERE id = :id";
            $stmt = $db->prepare($sql);

            $remarques = json_encode($answers, JSON_UNESCAPED_UNICODE);
            $stmt->execute([
                ':remarques' => $remarques,
                ':statut' => 'terminé',
                ':id' => $entretienId
            ]);

            return ['success' => true, 'message' => 'Interview answers saved successfully'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get interview session data
     */
    public function getInterviewSession(int $entretienId): array {
        try {
            $entretien = $this->getEntretienData($entretienId);
            if (!$entretien) {
                return ['error' => 'Entretien not found'];
            }

            return [
                'success' => true,
                'session' => [
                    'id' => $entretienId,
                    'candidate_name' => $entretien['nom_candidat'],
                    'position' => $entretien['poste_cible'],
                    'disability_type' => $entretien['type_handicap'],
                    'accommodations' => $entretien['amenagements'],
                    'start_time' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
