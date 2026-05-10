/**
 * TAKWINI AVATAR - JavaScript Logic
 * Human-like animated avatar with TTS, STT, and disability-specific behaviors
 */

class TakwiniAvatar {
    constructor() {
        this.config = this.loadConfig();
        this.typeHandicap = this.config.typeHandicap || 'aucun';
        this.candidatName = this.config.candidatName || 'Candidat';
        this.formFields = this.config.formFields || [];
        this.currentFieldIndex = 0;
        this.isOpen = false;
        this.isSpeaking = false;
        this.isListening = false;
        this.speechSynthesis = window.speechSynthesis;
        this.recognition = this.initSpeechRecognition();
        this.currentUtterance = null;
        
        this.init();
    }

    loadConfig() {
        const configEl = document.getElementById('takwini-config');
        if (configEl) {
            return JSON.parse(configEl.textContent);
        }
        return { typeHandicap: 'aucun', candidatName: 'Candidat', formFields: [] };
    }

    init() {
        this.cacheElements();
        this.attachEventListeners();
        this.setupDisabilityMode();
        this.greetCandidat();
    }

    cacheElements() {
        this.container = document.querySelector('.takwini-avatar-container');
        this.toggleBtn = document.querySelector('.takwini-toggle-btn');
        this.panel = document.querySelector('.takwini-panel');
        this.closeBtn = document.querySelector('.takwini-close-btn');
        this.face = document.querySelector('.takwini-face');
        this.message = document.querySelector('.takwini-greeting');
        this.subtitle = document.querySelector('.takwini-subtitle');
        this.subtitleText = document.querySelector('.takwini-subtitle-text');
        this.motorButtons = document.querySelector('.takwini-moteur-buttons');
        this.cognitifButtons = document.querySelector('.takwini-cognitif-buttons');
        this.visuelButtons = document.querySelector('.takwini-visuel-buttons');
        this.progressBar = document.querySelector('.takwini-progress-fill');
        this.progressText = document.querySelector('.takwini-progress-text');
        this.highContrastToggle = document.querySelector('.takwini-high-contrast-toggle');
        this.accessibilitySection = document.querySelector('.takwini-accessibility');
        this.arms = document.querySelectorAll('.takwini-arm');
    }

    attachEventListeners() {
        this.toggleBtn.addEventListener('click', () => this.togglePanel());
        this.closeBtn.addEventListener('click', () => this.closePanel());

        // MOTEUR buttons
        if (this.motorButtons) {
            this.motorButtons.querySelector('.takwini-btn-yes')?.addEventListener('click', () => this.handleMotorResponse('yes'));
            this.motorButtons.querySelector('.takwini-btn-no')?.addEventListener('click', () => this.handleMotorResponse('no'));
            this.motorButtons.querySelector('.takwini-btn-maybe')?.addEventListener('click', () => this.handleMotorResponse('maybe'));
            this.motorButtons.querySelector('.takwini-btn-repeat')?.addEventListener('click', () => this.repeatInstruction());
        }

        // COGNITIF emoji buttons
        if (this.cognitifButtons) {
            this.cognitifButtons.querySelectorAll('.takwini-emoji-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleEmotionResponse(e.target.dataset.emotion));
            });
        }

        // VISUEL audio buttons
        if (this.visuelButtons) {
            this.visuelButtons.querySelector('.takwini-btn-speak')?.addEventListener('click', () => this.speakCurrentField());
            this.visuelButtons.querySelector('.takwini-btn-stop')?.addEventListener('click', () => this.stopSpeaking());
        }

        // High contrast toggle
        if (this.highContrastToggle) {
            this.highContrastToggle.addEventListener('change', (e) => this.toggleHighContrast(e.target.checked));
        }

        // Form field listeners
        this.attachFormFieldListeners();
    }

    attachFormFieldListeners() {
        this.formFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('focus', () => this.onFieldFocus(fieldName, field));
                field.addEventListener('change', () => this.onFieldChange(fieldName));
            }
        });
    }

    setupDisabilityMode() {
        switch (this.typeHandicap) {
            case 'auditif':
                this.setupAuditifMode();
                break;
            case 'moteur':
                this.setupMoteurMode();
                break;
            case 'visuel':
                this.setupVisuelMode();
                break;
            case 'cognitif':
                this.setupCognitifMode();
                break;
            default:
                this.setupDefaultMode();
        }
    }

    setupAuditifMode() {
        // Show subtitles, hide audio
        this.subtitle.style.display = 'flex';
        if (this.visuelButtons) this.visuelButtons.style.display = 'none';
        this.message.textContent = '👋 Bonjour! Je suis Takwini. Je vais vous guider avec des sous-titres et des gestes.';
        this.animateHandSign('hello');
    }

    setupMoteurMode() {
        // Show large buttons and progress bar
        this.motorButtons.style.display = 'grid';
        this.progressBar.parentElement.parentElement.style.display = 'flex';
        this.message.textContent = '✓ Utilisez les boutons pour répondre. Je vais vous guider étape par étape.';
        this.updateProgress();
    }

    setupVisuelMode() {
        // Show audio controls, enable TTS
        this.visuelButtons.style.display = 'grid';
        this.accessibilitySection.style.display = 'block';
        this.message.textContent = '🔊 Bonjour! Je vais vous lire les instructions. Vous pouvez aussi utiliser votre voix.';
        this.speak('Bonjour ' + this.candidatName + '. Je suis Takwini, votre assistant d\'entretien. Je vais vous lire les instructions.');
    }

    setupCognitifMode() {
        // Show emoji buttons, step-by-step wizard
        this.cognitifButtons.style.display = 'grid';
        this.message.textContent = '😊 Répondez avec vos émojis préférés. Pas de stress, on y va doucement.';
        this.showOneFieldAtATime();
    }

    setupDefaultMode() {
        this.message.textContent = '👋 Bonjour ' + this.candidatName + '! Je suis Takwini, votre assistant.';
    }

    // ============ GREETING ============
    greetCandidat() {
        setTimeout(() => {
            this.animateFaceExpression('smile');
            if (this.typeHandicap === 'visuel') {
                this.speak('Bienvenue ' + this.candidatName);
            }
        }, 500);
    }

    // ============ PANEL MANAGEMENT ============
    togglePanel() {
        if (this.isOpen) {
            this.closePanel();
        } else {
            this.openPanel();
        }
    }

    openPanel() {
        this.panel.classList.add('is-open');
        this.isOpen = true;
        this.animateFaceExpression('happy');
    }

    closePanel() {
        this.panel.classList.remove('is-open');
        this.isOpen = false;
        this.stopSpeaking();
    }

    // ============ FIELD INTERACTIONS ============
    onFieldFocus(fieldName, field) {
        if (!this.isOpen) this.openPanel();

        const label = document.querySelector(`label[for="${fieldName}"]`)?.textContent || fieldName;
        const hint = field.getAttribute('data-hint') || label;

        switch (this.typeHandicap) {
            case 'auditif':
                this.showSubtitle(hint);
                this.animateHandSign('attention');
                break;
            case 'moteur':
                this.showSubtitle(hint);
                this.updateProgress();
                break;
            case 'visuel':
                this.speak(hint);
                break;
            case 'cognitif':
                this.showSubtitle(hint);
                this.animateFaceExpression('thinking');
                break;
        }
    }

    onFieldChange(fieldName) {
        this.animateFaceExpression('happy');
        if (this.typeHandicap === 'moteur') {
            this.animateHandSign('celebrate');
            this.updateProgress();
        }
    }

    // ============ MOTOR MODE (MOTEUR) ============
    handleMotorResponse(response) {
        const responses = {
            yes: '✓ Excellent!',
            no: '✗ D\'accord',
            maybe: '? Peut-être'
        };

        this.message.textContent = responses[response];
        this.animateHandSign(response === 'yes' ? 'yes' : response === 'no' ? 'no' : 'thinking');
        this.animateFaceExpression('happy');

        setTimeout(() => this.moveToNextField(), 1000);
    }

    repeatInstruction() {
        const field = this.getCurrentField();
        if (field) {
            const hint = field.getAttribute('data-hint') || field.name;
            this.showSubtitle(hint);
            this.animateHandSign('attention');
        }
    }

    moveToNextField() {
        this.currentFieldIndex++;
        if (this.currentFieldIndex < this.formFields.length) {
            const nextField = document.querySelector(`[name="${this.formFields[this.currentFieldIndex]}"]`);
            if (nextField) {
                nextField.focus();
                nextField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            this.celebrateCompletion();
        }
    }

    updateProgress() {
        const total = this.formFields.length;
        const current = this.currentFieldIndex + 1;
        const percentage = (current / total) * 100;

        if (this.progressBar) {
            this.progressBar.style.width = percentage + '%';
        }
        if (this.progressText) {
            this.progressText.innerHTML = `Étape <span class="takwini-current-step">${current}</span> sur <span class="takwini-total-steps">${total}</span>`;
        }
    }

    // ============ COGNITIVE MODE (COGNITIF) ============
    handleEmotionResponse(emotion) {
        const responses = {
            happy: '😊 Super!',
            thinking: '🤔 Prenez votre temps',
            confused: '😕 Pas de problème, on recommence',
            excited: '🎉 Bravo!'
        };

        this.message.textContent = responses[emotion];
        this.animateFaceExpression('happy');

        const btn = document.querySelector(`[data-emotion="${emotion}"]`);
        if (btn) {
            btn.classList.add('selected');
            setTimeout(() => btn.classList.remove('selected'), 500);
        }
    }

    showOneFieldAtATime() {
        // Hide all form sections except current
        const form = document.querySelector('form');
        if (form) {
            const groups = form.querySelectorAll('.form-group, .row');
            groups.forEach((group, idx) => {
                group.style.display = idx === this.currentFieldIndex ? 'block' : 'none';
            });
        }
    }

    // ============ VISUAL MODE (VISUEL) ============
    speakCurrentField() {
        const field = this.getCurrentField();
        if (field) {
            const label = document.querySelector(`label[for="${field.name}"]`)?.textContent || field.name;
            const hint = field.getAttribute('data-hint') || label;
            this.speak(hint);
        }
    }

    getCurrentField() {
        return document.querySelector(`[name="${this.formFields[this.currentFieldIndex]}"]`);
    }

    speak(text) {
        if (!('speechSynthesis' in window)) {
            console.warn('Speech Synthesis not supported');
            return;
        }

        this.stopSpeaking();

        this.currentUtterance = new SpeechSynthesisUtterance(text);
        this.currentUtterance.lang = 'fr-FR';
        this.currentUtterance.rate = 0.9;
        this.currentUtterance.pitch = 1;
        this.currentUtterance.volume = 1;

        this.currentUtterance.onstart = () => {
            this.isSpeaking = true;
            this.animateMouth('speaking');
        };

        this.currentUtterance.onend = () => {
            this.isSpeaking = false;
            this.animateMouth('idle');
        };

        this.speechSynthesis.speak(this.currentUtterance);
    }

    stopSpeaking() {
        if (this.speechSynthesis.speaking) {
            this.speechSynthesis.cancel();
            this.isSpeaking = false;
            this.animateMouth('idle');
        }
    }

    // ============ SPEECH RECOGNITION ============
    initSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) return null;

        const recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR';
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onstart = () => {
            this.isListening = true;
            this.animateFaceExpression('listening');
        };

        recognition.onresult = (event) => {
            const transcript = Array.from(event.results)
                .map(result => result[0].transcript)
                .join('');

            this.fillCurrentField(transcript);
            this.animateFaceExpression('happy');
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            this.message.textContent = '❌ Erreur de reconnaissance vocale';
        };

        recognition.onend = () => {
            this.isListening = false;
        };

        return recognition;
    }

    startListening() {
        if (this.recognition && !this.isListening) {
            this.recognition.start();
        }
    }

    fillCurrentField(value) {
        const field = this.getCurrentField();
        if (field) {
            field.value = value;
            field.dispatchEvent(new Event('change', { bubbles: true }));
            this.speak('Champ rempli: ' + value);
        }
    }

    // ============ ANIMATIONS ============
    animateFaceExpression(expression) {
        this.face.classList.remove('celebrating', 'listening');

        switch (expression) {
            case 'happy':
                this.animateEyebrows('happy');
                this.animateMouth('smile');
                break;
            case 'thinking':
                this.animateEyebrows('thinking');
                this.animateMouth('neutral');
                break;
            case 'listening':
                this.face.classList.add('listening');
                break;
            case 'smile':
                this.animateMouth('smile');
                break;
        }
    }

    animateEyebrows(expression) {
        const eyebrows = document.querySelectorAll('.takwini-eyebrow');
        eyebrows.forEach(brow => {
            brow.style.animation = expression === 'happy' ? 'expressionHappy 0.5s ease' : 'expressionNeutral 3s infinite';
        });
    }

    animateMouth(state) {
        const mouth = document.querySelector('.takwini-mouth');
        if (state === 'speaking') {
            mouth.style.animation = 'talk 0.5s infinite';
        } else if (state === 'smile') {
            mouth.style.animation = 'none';
            mouth.style.transform = 'scaleY(1.2)';
        } else {
            mouth.style.animation = 'talk 2s infinite';
            mouth.style.transform = 'scaleY(1)';
        }
    }

    animateHandSign(sign) {
        this.arms.forEach(arm => {
            arm.classList.remove('sign-hello', 'sign-yes', 'sign-no', 'sign-celebrate', 'sign-attention', 'sign-thinking');
            
            switch (sign) {
                case 'hello':
                    arm.classList.add('sign-hello');
                    break;
                case 'yes':
                    arm.classList.add('sign-yes');
                    break;
                case 'no':
                    arm.classList.add('sign-no');
                    break;
                case 'celebrate':
                    arm.classList.add('sign-celebrate');
                    break;
                case 'attention':
                    arm.classList.add('sign-hello');
                    break;
                case 'thinking':
                    arm.classList.add('sign-thinking');
                    break;
            }
        });
    }

    showSubtitle(text) {
        this.subtitle.style.display = 'flex';
        this.subtitleText.textContent = text;
    }

    hideSubtitle() {
        this.subtitle.style.display = 'none';
    }

    celebrateCompletion() {
        this.face.classList.add('celebrating');
        this.animateHandSign('celebrate');
        this.message.textContent = '🎉 Bravo! Vous avez terminé!';
        
        if (this.typeHandicap === 'visuel') {
            this.speak('Félicitations! Vous avez complété le formulaire.');
        }

        setTimeout(() => {
            this.face.classList.remove('celebrating');
        }, 2000);
    }

    // ============ ACCESSIBILITY ============
    toggleHighContrast(enabled) {
        if (enabled) {
            this.container.classList.add('takwini-high-contrast');
        } else {
            this.container.classList.remove('takwini-high-contrast');
        }
    }
}

// Initialize avatar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.takwini = new TakwiniAvatar();
});
