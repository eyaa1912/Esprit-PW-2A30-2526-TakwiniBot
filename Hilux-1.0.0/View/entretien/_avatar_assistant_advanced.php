<?php
declare(strict_types=1);

// Initialize avatar controller
require_once __DIR__ . '/../../Controller/AvatarLangController.php';
$avatarCtrl = new AvatarLangController();
$currentLang = $avatarCtrl->getCurrentLanguage();
$translations = $avatarCtrl->getTranslations();
$signCards = $avatarCtrl->getSignCards();
$accessibility = $avatarCtrl->getAccessibilitySettings();
?>

<!-- Avatar Panel -->
<div class="avatar-panel" id="avatarPanel">
    <!-- Avatar Face -->
    <div class="avatar-face">
        <svg class="avatar-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle cx="50" cy="50" r="45" fill="#667eea" opacity="0.1"/>
            
            <!-- Eyes -->
            <g class="avatar-eyes">
                <ellipse class="avatar-eye" cx="35" cy="40" rx="6" ry="8"/>
                <ellipse class="avatar-eye" cx="65" cy="40" rx="6" ry="8"/>
                <circle cx="35" cy="42" r="3" fill="#667eea"/>
                <circle cx="65" cy="42" r="3" fill="#667eea"/>
            </g>
            
            <!-- Mouth -->
            <path class="avatar-mouth" d="M 40 60 Q 50 65 60 60" stroke="#667eea" stroke-width="2" fill="none" stroke-linecap="round"/>
        </svg>
    </div>

    <!-- Language Switcher -->
    <div class="avatar-lang-switcher">
        <?php foreach ($avatarCtrl->getSupportedLanguages() as $lang): ?>
            <button class="lang-btn <?= $currentLang === $lang ? 'active' : '' ?>" 
                    onclick="switchLanguage('<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>')">
                <?= htmlspecialchars(strtoupper($lang), ENT_QUOTES, 'UTF-8') ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Sign Language Cards -->
    <div class="sign-cards-container" id="signCardsContainer">
        <?php foreach ($signCards as $card): ?>
            <div class="sign-card" onclick="speakSign('<?= htmlspecialchars($card['id'], ENT_QUOTES, 'UTF-8') ?>')">
                <span><?= htmlspecialchars($card['emoji'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="sign-card-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Accessibility Toggles -->
    <div class="accessibility-toggles">
        <div class="toggle-item">
            <span><?= htmlspecialchars($translations['tts'], ENT_QUOTES, 'UTF-8') ?></span>
            <div class="toggle-switch <?= $accessibility['tts_enabled'] ? 'active' : '' ?>" 
                 onclick="toggleAccessibility('tts')"></div>
        </div>
        <div class="toggle-item">
            <span><?= htmlspecialchars($translations['subtitles'], ENT_QUOTES, 'UTF-8') ?></span>
            <div class="toggle-switch <?= $accessibility['subtitles_enabled'] ? 'active' : '' ?>" 
                 onclick="toggleAccessibility('subtitles')"></div>
        </div>
        <div class="toggle-item">
            <span><?= htmlspecialchars($translations['sign_panel'], ENT_QUOTES, 'UTF-8') ?></span>
            <div class="toggle-switch <?= $accessibility['sign_panel_enabled'] ? 'active' : '' ?>" 
                 onclick="toggleAccessibility('sign_panel')"></div>
        </div>
    </div>
</div>

<!-- Subtitle Bar -->
<div class="subtitle-bar" id="subtitleBar">
    <div class="subtitle-text" id="subtitleText"></div>
</div>

<!-- Avatar JavaScript -->
<script>
(function() {
    'use strict';

    // Configuration
    const config = {
        lang: '<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>',
        ttsEnabled: <?= $accessibility['tts_enabled'] ? 'true' : 'false' ?>,
        subtitlesEnabled: <?= $accessibility['subtitles_enabled'] ? 'true' : 'false' ?>,
        signPanelEnabled: <?= $accessibility['sign_panel_enabled'] ? 'true' : 'false' ?>,
    };

    // Web Speech API
    const synthesis = window.speechSynthesis;
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    /**
     * Switch language
     */
    window.switchLanguage = function(lang) {
        window.location.href = '?lang=' + encodeURIComponent(lang);
    };

    /**
     * Toggle accessibility setting
     */
    window.toggleAccessibility = function(setting) {
        const toggle = event.target;
        toggle.classList.toggle('active');
        
        // Send to server
        fetch('<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'toggle_accessibility=' + encodeURIComponent(setting)
        });
    };

    /**
     * Speak sign language
     */
    window.speakSign = function(signId) {
        const signs = {
            'hello': { fr: 'Bonjour', en: 'Hello', ar: 'مرحبا' },
            'yes': { fr: 'Oui', en: 'Yes', ar: 'نعم' },
            'question': { fr: 'Question', en: 'Question', ar: 'سؤال' },
            'courage': { fr: 'Courage', en: 'Courage', ar: 'شجاعة' },
            'thank_you': { fr: 'Merci', en: 'Thank you', ar: 'شكرا' },
            'pause': { fr: 'Pause', en: 'Pause', ar: 'توقف' },
        };

        const text = signs[signId][config.lang] || signs[signId]['en'];
        
        if (config.ttsEnabled) {
            speak(text);
        }
        
        if (config.subtitlesEnabled) {
            showSubtitle(text);
        }
    };

    /**
     * Speak text using Web Speech API
     */
    function speak(text) {
        synthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = config.lang === 'ar' ? 'ar-SA' : (config.lang === 'en' ? 'en-US' : 'fr-FR');
        utterance.rate = 0.9;
        utterance.pitch = 1;
        
        synthesis.speak(utterance);
    }

    /**
     * Show subtitle
     */
    function showSubtitle(text) {
        const subtitleBar = document.getElementById('subtitleBar');
        const subtitleText = document.getElementById('subtitleText');
        
        subtitleText.textContent = text;
        subtitleBar.classList.add('active');
        
        setTimeout(() => {
            subtitleBar.classList.remove('active');
        }, 3000);
    }

    /**
     * Add field hints with Web Speech API
     */
    function initializeFieldHints() {
        const fields = document.querySelectorAll('[data-hint]');
        
        fields.forEach(field => {
            const hint = field.getAttribute('data-hint');
            
            // Add hint icon
            const hintIcon = document.createElement('span');
            hintIcon.className = 'hint-icon';
            hintIcon.textContent = '🔊';
            hintIcon.title = hint;
            hintIcon.onclick = (e) => {
                e.preventDefault();
                if (config.ttsEnabled) speak(hint);
                if (config.subtitlesEnabled) showSubtitle(hint);
            };
            
            // Wrap field if needed
            if (field.parentElement.classList.contains('form-group')) {
                field.parentElement.classList.add('field-with-hint');
                field.parentElement.appendChild(hintIcon);
            }
            
            // Read hint on focus
            field.addEventListener('focus', () => {
                if (config.ttsEnabled) speak(hint);
                if (config.subtitlesEnabled) showSubtitle(hint);
            });
        });
    }

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initializeFieldHints();
        
        // Show sign panel if enabled
        if (config.signPanelEnabled) {
            document.getElementById('signCardsContainer').classList.add('active');
        }
    });
})();
</script>
