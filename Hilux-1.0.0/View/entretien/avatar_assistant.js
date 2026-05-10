/**
 * TakwiniBot Avatar Assistant
 * 3D Avatar with Text-to-Speech and Speech-to-Text capabilities
 * Adapts to different disability types
 */

class AvatarAssistant {
    constructor() {
        this.isActive = false;
        this.isListening = false;
        this.isSpeaking = false;
        this.handicapType = null;
        this.audioDisabled = false;
        this.visualSpeak = null;
        
        // Web Speech API
        this.synthesis = window.speechSynthesis;
        this.recognition = null;
        
        // DOM Elements
        this.avatar = document.getElementById('avatar-assistant');
        this.toggleBtn = document.getElementById('avatar-toggle');
        this.helpBtn = document.getElementById('avatar-help');
        this.settingsBtn = document.getElementById('avatar-settings');
        this.feedback = document.getElementById('avatar-feedback');
        this.statusText = document.querySelector('.avatar-status-text');
        this.statusIndicator = document.querySelector('.avatar-status-indicator');
        this.transcript = document.querySelector('.avatar-transcript');
        this.soundWaves = document.querySelector('.avatar-sound-waves');
        
        // Form fields
        this.fields = {
            nom_candidat: document.getElementById('nom_candidat'),
            email_candidat: document.getElementById('email_candidat'),
            genre: document.getElementById('genre'),
            type_handicap: document.getElementById('type_handicap'),
            amenagements: document.getElementById('amenagements'),
            type_entretien_id: document.getElementById('type_entretien_id'),
            date_entretien: document.getElementById('date_entretien'),
            heure_entretien: document.getElementById('heure_entretien'),
            poste_cible: document.getElementById('poste_cible'),
            metier_suggere: document.getElementById('metier_suggere'),
            score_rse: document.getElementById('score_rse'),
            remarques: document.getElementById('remarques'),
            statut: document.getElementById('statut'),
            has_handicap: document.getElementById('has_handicap')
        };
        
        // Questions for voice guidance - ALL FIELDS
        this.questions = [
            { field: 'nom_candidat', text: 'Quel est votre nom complet ?' },
            { field: 'email_candidat', text: 'Quel est votre adresse email ?' },
            { field: 'genre', text: 'Quel est votre genre ? Dites homme ou femme.' },
            { field: 'type_handicap', text: 'Quel est votre type de handicap ? Par exemple : moteur, visuel, auditif, ou cognitif.' },
            { field: 'amenagements', text: 'Quels aménagements souhaitez-vous pour votre poste de travail ?' },
            { field: 'type_entretien_id', text: 'Quel type d\'entretien souhaitez-vous ? Par exemple : entretien technique, entretien RH, ou entretien général.' },
            { field: 'date_entretien', text: 'Quelle est la date de votre entretien ? Dites la date au format jour mois année. Par exemple : 15 mai 2026.' },
            { field: 'heure_entretien', text: 'À quelle heure souhaitez-vous votre entretien ? Dites l\'heure au format 14 heures 30.' },
            { field: 'poste_cible', text: 'Quel poste souhaitez-vous occuper ?' },
            { field: 'score_rse', text: 'Quel score RSE donnez-vous ? Dites un chiffre entre 1 et 5.' },
            { field: 'remarques', text: 'Avez-vous des remarques ou informations supplémentaires à ajouter ?' }
        ];
        
        this.currentQuestionIndex = 0;
        
        this.init();
    }
    
    init() {
        // Initialize Speech Recognition
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.lang = 'fr-FR';
            this.recognition.continuous = false;
            this.recognition.interimResults = true;
            
            this.recognition.onresult = (event) => this.handleSpeechResult(event);
            this.recognition.onerror = (event) => this.handleSpeechError(event);
            this.recognition.onend = () => this.handleSpeechEnd();
        } else {
            console.warn('Speech Recognition not supported');
        }
        
        // Event listeners
        this.toggleBtn.addEventListener('click', () => this.toggle());
        this.helpBtn.addEventListener('click', () => this.showHelp());
        this.settingsBtn.addEventListener('click', () => this.showSettings());
        
        // Monitor handicap type changes
        this.fields.type_handicap.addEventListener('change', () => this.adaptToHandicap());
        this.fields.has_handicap.addEventListener('change', () => this.adaptToHandicap());
        
        // Detect handicap type on load
        this.adaptToHandicap();
        
        // Welcome message
        setTimeout(() => {
            if (this.fields.has_handicap.checked) {
                this.speak('Bonjour ! Je suis votre assistant virtuel. Je peux vous aider à remplir ce formulaire par la voix. Cliquez sur le bouton pour commencer.');
            }
        }, 1000);
    }
    
    toggle() {
        if (this.isActive) {
            this.deactivate();
        } else {
            this.activate();
        }
    }
    
    activate() {
        if (!this.recognition) {
            this.showNotification('La reconnaissance vocale n\'est pas supportée par votre navigateur. Veuillez utiliser Chrome ou Edge.', 'error');
            return;
        }
        
        this.isActive = true;
        this.toggleBtn.classList.add('active');
        this.toggleBtn.innerHTML = '<i class="fa fa-microphone-slash"></i><span>Désactiver</span>';
        this.updateStatus('Assistant activé', 'listening');
        
        this.speak('Assistant vocal activé. Je vais vous poser quelques questions pour remplir le formulaire. Répondez après le signal sonore.');
        
        setTimeout(() => {
            this.askNextQuestion();
        }, 3000);
    }
    
    deactivate() {
        this.isActive = false;
        this.isListening = false;
        this.toggleBtn.classList.remove('active');
        this.toggleBtn.innerHTML = '<i class="fa fa-microphone"></i><span>Activer l\'assistant</span>';
        this.updateStatus('Assistant désactivé');
        this.hideFeedback();
        
        if (this.recognition) {
            this.recognition.stop();
        }
        
        this.synthesis.cancel();
    }
    
    askNextQuestion() {
        if (!this.isActive || this.currentQuestionIndex >= this.questions.length) {
            this.complete();
            return;
        }
        
        const question = this.questions[this.currentQuestionIndex];
        
        // Skip if field already filled
        if (this.fields[question.field] && this.fields[question.field].value.trim() !== '') {
            this.currentQuestionIndex++;
            this.askNextQuestion();
            return;
        }
        
        this.speak(question.text, () => {
            this.startListening(question.field);
        });
    }
    
    startListening(fieldName) {
        if (!this.recognition || !this.isActive) return;
        
        this.isListening = true;
        this.showFeedback();
        this.updateStatus('En écoute...', 'listening');
        
        this.currentField = fieldName;
        
        try {
            this.recognition.start();
        } catch (e) {
            console.error('Recognition start error:', e);
        }
    }
    
    handleSpeechResult(event) {
        const transcript = Array.from(event.results)
            .map(result => result[0].transcript)
            .join('');
        
        this.transcript.textContent = transcript;
        
        // Final result
        if (event.results[event.results.length - 1].isFinal) {
            this.fillField(this.currentField, transcript);
            this.hideFeedback();
            
            // Move to next question
            this.currentQuestionIndex++;
            setTimeout(() => {
                this.askNextQuestion();
            }, 1000);
        }
    }
    
    handleSpeechError(event) {
        console.error('Speech recognition error:', event.error);
        this.hideFeedback();
        
        if (event.error === 'no-speech') {
            this.speak('Je n\'ai rien entendu. Réessayons.');
            setTimeout(() => {
                if (this.isActive) {
                    this.startListening(this.currentField);
                }
            }, 2000);
        } else if (event.error === 'not-allowed') {
            this.showNotification('Veuillez autoriser l\'accès au microphone dans les paramètres de votre navigateur.', 'error');
            this.deactivate();
        }
    }
    
    handleSpeechEnd() {
        this.isListening = false;
    }
    
    fillField(fieldName, value) {
        const field = this.fields[fieldName];
        if (!field) return;
        
        let processedValue = value.trim();
        
        // Special handling for different field types
        if (fieldName === 'genre') {
            // Convert voice input to select value
            if (processedValue.toLowerCase().includes('femme')) {
                processedValue = 'femme';
            } else if (processedValue.toLowerCase().includes('homme')) {
                processedValue = 'homme';
            }
        }
        
        if (fieldName === 'date_entretien') {
            // Convert voice date to YYYY-MM-DD format
            // Example: "15 mai 2026" → "2026-05-15"
            processedValue = this.convertVoiceDateToISO(processedValue);
        }
        
        if (fieldName === 'heure_entretien') {
            // Convert voice time to HH:MM format
            // Example: "14 heures 30" → "14:30"
            processedValue = this.convertVoiceTimeToISO(processedValue);
        }
        
        if (fieldName === 'score_rse') {
            // Extract number from voice input
            const match = processedValue.match(/\d+/);
            if (match) {
                const score = parseInt(match[0]);
                if (score >= 1 && score <= 5) {
                    processedValue = score.toString();
                }
            }
        }
        
        if (fieldName === 'type_entretien_id') {
            // Try to match voice input to available options
            processedValue = this.matchEntretienType(processedValue);
        }
        
        // Set the field value
        field.value = processedValue;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Visual feedback
        field.style.backgroundColor = '#d4edda';
        setTimeout(() => {
            field.style.backgroundColor = '';
        }, 1000);
        
        this.speak('Enregistré : ' + processedValue);
    }
    
    // Helper: Convert voice date to ISO format
    convertVoiceDateToISO(voiceDate) {
        // Example: "15 mai 2026" → "2026-05-15"
        const months = {
            'janvier': '01', 'février': '02', 'mars': '03', 'avril': '04',
            'mai': '05', 'juin': '06', 'juillet': '07', 'août': '08',
            'septembre': '09', 'octobre': '10', 'novembre': '11', 'décembre': '12'
        };
        
        const parts = voiceDate.toLowerCase().split(/\s+/);
        if (parts.length >= 3) {
            const day = parts[0].padStart(2, '0');
            const month = months[parts[1]] || '01';
            const year = parts[2];
            return `${year}-${month}-${day}`;
        }
        return voiceDate;
    }
    
    // Helper: Convert voice time to ISO format
    convertVoiceTimeToISO(voiceTime) {
        // Example: "14 heures 30" → "14:30"
        const parts = voiceTime.toLowerCase().split(/\s+/);
        let hours = '00';
        let minutes = '00';
        
        for (let i = 0; i < parts.length; i++) {
            if (parts[i].match(/^\d+$/)) {
                if (i === 0 || parts[i - 1].includes('heure')) {
                    hours = parts[i].padStart(2, '0');
                } else if (parts[i - 1].includes('minute') || parts[i - 1].includes('et')) {
                    minutes = parts[i].padStart(2, '0');
                }
            }
        }
        
        return `${hours}:${minutes}`;
    }
    
    // Helper: Match voice input to entretien type
    matchEntretienType(voiceInput) {
        const input = voiceInput.toLowerCase();
        
        // Try to match keywords
        if (input.includes('technique')) return 'technique';
        if (input.includes('rh') || input.includes('ressources')) return 'rh';
        if (input.includes('général') || input.includes('general')) return 'général';
        if (input.includes('téléphone') || input.includes('telephone')) return 'téléphone';
        if (input.includes('visio') || input.includes('vidéo') || input.includes('video')) return 'visio';
        
        return voiceInput;
    }
    
    speak(text, callback) {
        // If hearing impaired mode, show text visually instead
        if (this.audioDisabled && this.visualSpeak) {
            this.visualSpeak(text);
            if (callback) {
                setTimeout(callback, 2000);
            }
            return;
        }
        
        if (!this.synthesis) return;
        
        // Cancel any ongoing speech
        this.synthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        
        // Adapt voice based on handicap type
        if (this.handicapType === 'visuel') {
            utterance.rate = 0.8; // Slower for visually impaired
            utterance.volume = 1.0;
        } else if (this.handicapType === 'cognitif') {
            utterance.rate = 0.7; // Much slower for cognitive disabilities
        }
        
        utterance.onstart = () => {
            this.isSpeaking = true;
            this.updateStatus('En train de parler...', 'speaking');
            this.soundWaves.classList.add('active');
        };
        
        utterance.onend = () => {
            this.isSpeaking = false;
            this.soundWaves.classList.remove('active');
            if (this.isActive) {
                this.updateStatus('En écoute...', 'listening');
            }
            if (callback) callback();
        };
        
        this.synthesis.speak(utterance);
    }
    
    complete() {
        this.speak('Formulaire complété ! Vous pouvez maintenant vérifier les informations et enregistrer.');
        this.deactivate();
    }
    
    adaptToHandicap() {
        const handicapType = this.fields.type_handicap.value.toLowerCase();
        this.handicapType = handicapType;
        
        // Remove all previous adaptations
        this.avatar.classList.remove('high-contrast', 'large-text', 'visual-mode', 'simplified-mode');
        
        // Apply specific adaptations
        if (handicapType.includes('visuel')) {
            this.enableVisualImpairmentMode();
        } else if (handicapType.includes('moteur')) {
            this.enableMotorImpairmentMode();
        } else if (handicapType.includes('cognitif')) {
            this.enableCognitiveImpairmentMode();
        } else if (handicapType.includes('auditif')) {
            this.enableHearingImpairmentMode();
        }
    }
    
    // ===== HANDICAP VISUEL =====
    enableVisualImpairmentMode() {
        this.avatar.classList.add('high-contrast');
        this.updateStatus('Mode malvoyant activé - Audio renforcé');
        
        // Add ARIA labels to ALL elements
        Object.keys(this.fields).forEach(key => {
            const field = this.fields[key];
            if (field && !field.getAttribute('aria-label')) {
                const label = document.querySelector(`label[for="${field.id}"]`);
                if (label) {
                    field.setAttribute('aria-label', label.textContent);
                }
            }
        });
        
        // Read all labels on focus
        Object.keys(this.fields).forEach(key => {
            const field = this.fields[key];
            if (field) {
                field.addEventListener('focus', () => {
                    const label = document.querySelector(`label[for="${field.id}"]`);
                    if (label) {
                        this.speak(label.textContent + '. Champ de saisie.');
                    }
                });
            }
        });
        
        // Announce when typing
        Object.keys(this.fields).forEach(key => {
            const field = this.fields[key];
            if (field && field.tagName === 'INPUT') {
                let typingTimeout;
                field.addEventListener('input', () => {
                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        if (field.value) {
                            this.speak('Vous avez saisi : ' + field.value);
                        }
                    }, 1500);
                });
            }
        });
        
        this.showNotification('Mode malvoyant : Toutes les questions seront lues à voix haute. Utilisez la touche Tab pour naviguer.', 'info', 6000);
    }
    
    // ===== HANDICAP MOTEUR =====
    enableMotorImpairmentMode() {
        this.updateStatus('Mode handicap moteur - Contrôle vocal 100%');
        
        // Enable voice commands for form submission
        this.enableVoiceCommands();
        
        // Make all buttons larger and easier to click
        const buttons = document.querySelectorAll('button, .btn');
        buttons.forEach(btn => {
            btn.style.minHeight = '50px';
            btn.style.fontSize = '18px';
            btn.style.padding = '15px 30px';
        });
        
        this.showNotification('Mode handicap moteur : Utilisez uniquement votre voix. Dites "Enregistrer" pour soumettre le formulaire.', 'info', 6000);
    }
    
    // ===== HANDICAP COGNITIF =====
    enableCognitiveImpairmentMode() {
        this.avatar.classList.add('large-text', 'simplified-mode');
        this.updateStatus('Mode simplifié activé');
        
        // Simplify questions
        this.questions = [
            { field: 'nom_candidat', text: 'Votre nom ?' },
            { field: 'email_candidat', text: 'Votre email ?' },
            { field: 'genre', text: 'Vous êtes homme ou femme ?' },
            { field: 'type_handicap', text: 'Votre handicap ?' },
            { field: 'amenagements', text: 'Ce dont vous avez besoin au travail ?' },
            { field: 'type_entretien_id', text: 'Quel type d\'entretien ?' },
            { field: 'date_entretien', text: 'Date de l\'entretien ?' },
            { field: 'heure_entretien', text: 'Heure de l\'entretien ?' },
            { field: 'poste_cible', text: 'Quel travail voulez-vous ?' },
            { field: 'score_rse', text: 'Note de 1 à 5 ?' },
            { field: 'remarques', text: 'Autre chose à dire ?' }
        ];
        
        // Make buttons MUCH larger
        const buttons = document.querySelectorAll('button, .btn');
        buttons.forEach(btn => {
            btn.style.minHeight = '60px';
            btn.style.fontSize = '22px';
            btn.style.padding = '20px 40px';
            btn.style.fontWeight = 'bold';
        });
        
        // Increase all text size
        const formGroups = document.querySelectorAll('.form-group');
        formGroups.forEach(group => {
            group.style.fontSize = '20px';
        });
        
        const labels = document.querySelectorAll('label');
        labels.forEach(label => {
            label.style.fontSize = '22px';
            label.style.fontWeight = 'bold';
            label.style.marginBottom = '10px';
        });
        
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.style.fontSize = '20px';
            input.style.padding = '15px';
            input.style.minHeight = '50px';
        });
        
        // Add visual progress indicator
        this.addProgressIndicator();
        
        this.showNotification('Mode simplifié : Questions courtes, gros boutons, guidage étape par étape.', 'info', 6000);
    }
    
    // ===== HANDICAP AUDITIF =====
    enableHearingImpairmentMode() {
        this.avatar.classList.add('visual-mode');
        this.updateStatus('Mode sourd/malentendant - Affichage visuel');
        
        // Disable all audio
        this.audioDisabled = true;
        
        // Create visual text display panel
        this.createVisualTextPanel();
        
        // Show text instead of speaking
        this.visualSpeak = (text) => {
            this.displayVisualText(text);
        };
        
        // Enhanced visual animations
        this.enhanceVisualAnimations();
        
        this.showNotification('Mode sourd/malentendant : Toutes les informations sont affichées visuellement. Pas de son.', 'info', 6000);
    }
    
    // Helper: Voice commands for motor impairment
    enableVoiceCommands() {
        if (!this.recognition) return;
        
        // Listen for special commands
        const originalHandler = this.handleSpeechResult.bind(this);
        this.handleSpeechResult = (event) => {
            const transcript = Array.from(event.results)
                .map(result => result[0].transcript)
                .join('').toLowerCase();
            
            // Check for commands
            if (transcript.includes('enregistrer') || transcript.includes('soumettre')) {
                const form = document.querySelector('form');
                if (form) {
                    this.speak('Enregistrement du formulaire en cours...');
                    setTimeout(() => form.submit(), 1500);
                }
                return;
            }
            
            if (transcript.includes('répéter')) {
                this.currentQuestionIndex--;
                this.askNextQuestion();
                return;
            }
            
            if (transcript.includes('passer')) {
                this.currentQuestionIndex++;
                this.askNextQuestion();
                return;
            }
            
            if (transcript.includes('arrêter') || transcript.includes('stop')) {
                this.deactivate();
                return;
            }
            
            // Normal handling
            originalHandler(event);
        };
    }
    
    // Helper: Visual text panel for hearing impaired
    createVisualTextPanel() {
        let panel = document.getElementById('visual-text-panel');
        if (panel) return;
        
        panel = document.createElement('div');
        panel.id = 'visual-text-panel';
        panel.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 10000;
            max-width: 600px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            display: none;
            animation: pulse 2s infinite;
        `;
        
        document.body.appendChild(panel);
    }
    
    displayVisualText(text) {
        const panel = document.getElementById('visual-text-panel');
        if (!panel) return;
        
        panel.textContent = text;
        panel.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            panel.style.display = 'none';
        }, 5000);
    }
    
    enhanceVisualAnimations() {
        // Make avatar bounce more visibly
        this.avatar.style.animation = 'bounce 1s infinite';
        
        // Add CSS for bounce animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }
            @keyframes pulse {
                0%, 100% { transform: translate(-50%, -50%) scale(1); }
                50% { transform: translate(-50%, -50%) scale(1.05); }
            }
        `;
        document.head.appendChild(style);
    }
    
    addProgressIndicator() {
        let indicator = document.getElementById('progress-indicator');
        if (indicator) return;
        
        indicator = document.createElement('div');
        indicator.id = 'progress-indicator';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            font-size: 20px;
            font-weight: bold;
            color: #6f42c1;
        `;
        indicator.textContent = `Étape 1 sur ${this.questions.length}`;
        
        document.body.appendChild(indicator);
        
        // Update progress as questions are answered
        const originalAskNext = this.askNextQuestion.bind(this);
        this.askNextQuestion = function() {
            indicator.textContent = `Étape ${this.currentQuestionIndex + 1} sur ${this.questions.length}`;
            originalAskNext();
        };
    }
    
    showHelp() {
        const helpContent = `
            <div style="text-align: left; line-height: 1.8;">
                <h4 style="color: #6f42c1; margin-bottom: 15px;">🎙️ Assistant vocal TakwiniBot</h4>
                
                <p><strong>Comment utiliser l'assistant :</strong></p>
                <ul style="margin-left: 20px;">
                    <li>Cliquez sur "Activer l'assistant" pour commencer</li>
                    <li>Répondez aux questions après le signal sonore</li>
                    <li>L'assistant remplit automatiquement le formulaire</li>
                    <li>Vous pouvez modifier les réponses manuellement</li>
                    <li>Compatible avec tous types de handicap</li>
                </ul>
                
                <p style="margin-top: 15px;"><strong>Commandes vocales :</strong></p>
                <ul style="margin-left: 20px;">
                    <li><strong>Répéter</strong> : Répète la dernière question</li>
                    <li><strong>Passer</strong> : Passe à la question suivante</li>
                    <li><strong>Arrêter</strong> : Désactive l'assistant</li>
                </ul>
            </div>
        `;
        
        this.showNotification(helpContent, 'info', 8000);
    }
    
    showSettings() {
        const settingsContent = `
            <div style="text-align: left; line-height: 1.8;">
                <h4 style="color: #6f42c1; margin-bottom: 15px;">⚙️ Paramètres de l'assistant</h4>
                
                <p><strong>Fonctionnalités disponibles :</strong></p>
                <ul style="margin-left: 20px;">
                    <li>✓ Vitesse de parole adaptative</li>
                    <li>✓ Volume automatique</li>
                    <li>✓ Langue française</li>
                    <li>✓ Mode d'accessibilité selon le handicap</li>
                </ul>
                
                <p style="margin-top: 15px; color: #666;">
                    <em>Les paramètres s'adaptent automatiquement selon le type de handicap sélectionné.</em>
                </p>
            </div>
        `;
        
        this.showNotification(settingsContent, 'info', 6000);
    }
    
    
    showNotification(message, type = 'info', duration = 5000) {
        // Remove any existing notification
        const existingNotification = document.querySelector('.avatar-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `avatar-notification avatar-notification-${type}`;
        notification.innerHTML = `
            <div class="avatar-notification-content">
                <div class="avatar-notification-icon">
                    ${type === 'error' ? '<i class="fa fa-exclamation-circle"></i>' : 
                      type === 'success' ? '<i class="fa fa-check-circle"></i>' : 
                      '<i class="fa fa-info-circle"></i>'}
                </div>
                <div class="avatar-notification-message">${message}</div>
                <button class="avatar-notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto-remove after duration
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, duration);
    }
    
    updateStatus(text, type = 'ready') {
        this.statusText.textContent = text;
        this.statusIndicator.className = 'avatar-status-indicator ' + type;
    }
    
    showFeedback() {
        this.feedback.style.display = 'block';
        this.transcript.textContent = '';
    }
    
    hideFeedback() {
        this.feedback.style.display = 'none';
    }
}

// Initialize avatar assistant when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if handicap checkbox is checked or on form load
    const hasHandicap = document.getElementById('has_handicap');
    
    if (hasHandicap) {
        // Initialize avatar
        window.avatarAssistant = new AvatarAssistant();
        
        // Show/hide avatar based on handicap checkbox
        hasHandicap.addEventListener('change', function() {
            const avatar = document.getElementById('avatar-assistant');
            if (this.checked) {
                avatar.style.display = 'block';
            } else {
                avatar.style.display = 'none';
                if (window.avatarAssistant) {
                    window.avatarAssistant.deactivate();
                }
            }
        });
        
        // Initial state
        const avatar = document.getElementById('avatar-assistant');
        avatar.style.display = hasHandicap.checked ? 'block' : 'none';
    }
});
