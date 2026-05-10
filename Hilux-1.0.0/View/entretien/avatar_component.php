<?php
/**
 * Takwini Avatar Component
 * Human-like animated avatar that adapts to disability types
 * Embedded in interview forms (add.php, edit.php)
 */
// Get values from form data array (passed from add.php or edit.php)
$typeHandicap = $data['type_handicap'] ?? 'aucun';
$candidatName = $data['nom_candidat'] ?? 'Candidat';
?>

<div class="takwini-avatar-container" data-disability="<?= htmlspecialchars($typeHandicap) ?>">
    <!-- Avatar Toggle Button -->
    <button class="takwini-toggle-btn" title="Afficher/Masquer l'assistant Takwini">
        <i class="bx bx-user-circle"></i>
    </button>

    <!-- Avatar Panel -->
    <div class="takwini-panel">
        <!-- Header -->
        <div class="takwini-header">
            <h3>Takwini</h3>
            <button class="takwini-close-btn" title="Fermer">×</button>
        </div>

        <!-- Avatar Face (SVG) -->
        <div class="takwini-face-container">
            <svg class="takwini-face" viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
                <!-- Head -->
                <ellipse cx="100" cy="80" rx="50" ry="55" fill="#f4a460" stroke="#d4845a" stroke-width="2"/>

                <!-- Hair -->
                <path d="M 50 50 Q 50 20 100 15 Q 150 20 150 50" fill="#8b6f47" stroke="#6b5437" stroke-width="1.5"/>

                <!-- Left Eye -->
                <g class="takwini-eye takwini-eye-left">
                    <ellipse cx="75" cy="70" rx="8" ry="12" fill="white" stroke="#333" stroke-width="1"/>
                    <circle class="takwini-pupil" cx="75" cy="75" r="5" fill="#333"/>
                    <circle class="takwini-shine" cx="77" cy="73" r="2" fill="white"/>
                </g>

                <!-- Right Eye -->
                <g class="takwini-eye takwini-eye-right">
                    <ellipse cx="125" cy="70" rx="8" ry="12" fill="white" stroke="#333" stroke-width="1"/>
                    <circle class="takwini-pupil" cx="125" cy="75" r="5" fill="#333"/>
                    <circle class="takwini-shine" cx="127" cy="73" r="2" fill="white"/>
                </g>

                <!-- Eyebrows -->
                <path class="takwini-eyebrow takwini-eyebrow-left" d="M 65 55 Q 75 50 85 55" stroke="#6b5437" stroke-width="2" fill="none" stroke-linecap="round"/>
                <path class="takwini-eyebrow takwini-eyebrow-right" d="M 115 55 Q 125 50 135 55" stroke="#6b5437" stroke-width="2" fill="none" stroke-linecap="round"/>

                <!-- Nose -->
                <path d="M 100 75 L 100 95" stroke="#d4845a" stroke-width="2" fill="none" stroke-linecap="round"/>

                <!-- Mouth -->
                <g class="takwini-mouth">
                    <path class="takwini-mouth-line" d="M 80 110 Q 100 125 120 110" stroke="#c85a54" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <ellipse class="takwini-mouth-fill" cx="100" cy="115" rx="20" ry="8" fill="#f0a0a0" opacity="0.6"/>
                </g>

                <!-- Blush -->
                <ellipse class="takwini-blush takwini-blush-left" cx="55" cy="90" rx="8" ry="6" fill="#ffb6c1" opacity="0.5"/>
                <ellipse class="takwini-blush takwini-blush-right" cx="145" cy="90" rx="8" ry="6" fill="#ffb6c1" opacity="0.5"/>

                <!-- Neck -->
                <rect x="85" y="130" width="30" height="20" fill="#f4a460" stroke="#d4845a" stroke-width="1"/>

                <!-- Shoulders -->
                <ellipse cx="100" cy="160" rx="60" ry="35" fill="#4a90e2" stroke="#2e5c8a" stroke-width="2"/>

                <!-- Arms (for hand signs) -->
                <g class="takwini-arm takwini-arm-left">
                    <line x1="50" y1="160" x2="30" y2="200" stroke="#f4a460" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="30" cy="200" r="6" fill="#f4a460"/>
                </g>
                <g class="takwini-arm takwini-arm-right">
                    <line x1="150" y1="160" x2="170" y2="200" stroke="#f4a460" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="170" cy="200" r="6" fill="#f4a460"/>
                </g>
            </svg>
        </div>

        <!-- Subtitle Display (for AUDITIF & MOTEUR) -->
        <div class="takwini-subtitle" style="display: none;">
            <p class="takwini-subtitle-text"></p>
        </div>

        <!-- Status Message -->
        <div class="takwini-message">
            <p class="takwini-greeting">Bonjour <?= htmlspecialchars($candidatName) ?>, je suis Takwini, votre assistant d'entretien!</p>
        </div>

        <!-- Disability-Specific Controls -->
        <div class="takwini-controls">
            <!-- MOTEUR: Large Buttons -->
            <div class="takwini-button-group takwini-moteur-buttons" style="display: none;">
                <button class="takwini-btn takwini-btn-yes">✓ Oui</button>
                <button class="takwini-btn takwini-btn-maybe">? Peut-être</button>
                <button class="takwini-btn takwini-btn-no">✗ Non</button>
                <button class="takwini-btn takwini-btn-repeat">🔄 Répéter</button>
            </div>

            <!-- COGNITIF: Emoji Buttons -->
            <div class="takwini-emoji-group takwini-cognitif-buttons" style="display: none;">
                <button class="takwini-emoji-btn" data-emotion="happy">😊</button>
                <button class="takwini-emoji-btn" data-emotion="thinking">🤔</button>
                <button class="takwini-emoji-btn" data-emotion="confused">😕</button>
                <button class="takwini-emoji-btn" data-emotion="excited">🎉</button>
            </div>

            <!-- VISUEL: Audio Controls -->
            <div class="takwini-audio-group takwini-visuel-buttons" style="display: none;">
                <button class="takwini-btn takwini-btn-speak">🔊 Lire à haute voix</button>
                <button class="takwini-btn takwini-btn-stop">⏹ Arrêter</button>
            </div>

            <!-- Progress Bar (MOTEUR) -->
            <div class="takwini-progress takwini-moteur-progress" style="display: none;">
                <div class="takwini-progress-bar">
                    <div class="takwini-progress-fill"></div>
                </div>
                <p class="takwini-progress-text">Étape <span class="takwini-current-step">1</span> sur <span class="takwini-total-steps">11</span></p>
            </div>
        </div>

        <!-- High Contrast Toggle (VISUEL) -->
        <div class="takwini-accessibility" style="display: none;">
            <label class="takwini-checkbox">
                <input type="checkbox" class="takwini-high-contrast-toggle">
                <span>Mode contraste élevé</span>
            </label>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<script type="application/json" id="takwini-config">
{
    "typeHandicap": "<?= htmlspecialchars($typeHandicap) ?>",
    "candidatName": "<?= htmlspecialchars($candidatName) ?>",
    "formFields": [
        "nom_candidat", "email_candidat", "genre", "type_handicap", 
        "amenagements", "type_entretien_id", "date_entretien", 
        "heure_entretien", "poste_cible", "score_rse", "remarques"
    ]
}
</script>
