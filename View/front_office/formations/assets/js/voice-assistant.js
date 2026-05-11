/**
 * Assistant Vocal - Text to Speech
 * Lit le texte lorsque le curseur passe sur un mot
 * Corrigé : activation manuelle obligatoire + gestion async des voix
 */

class VoiceAssistant {
    constructor() {
        this.synth = window.speechSynthesis;
        this.isEnabled = false;   // Désactivé par défaut — attend un clic utilisateur
        this.currentUtterance = null;
        this.voices = [];
        this.selectedVoice = null;
        this.userInteracted = false;

        this.loadVoices();
        this.createControlButton();
        // Attacher les listeners APRÈS que le DOM soit prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachListeners());
        } else {
            this.attachListeners();
        }
    }

    loadVoices() {
        const setVoice = () => {
            this.voices = this.synth.getVoices();
            // Préférer une voix française, sinon la première disponible
            this.selectedVoice =
                this.voices.find(v => v.lang === 'fr-FR') ||
                this.voices.find(v => v.lang.startsWith('fr')) ||
                this.voices[0] ||
                null;
        };

        setVoice();
        // Les voix se chargent de façon asynchrone dans Chrome
        if (typeof this.synth.onvoiceschanged !== 'undefined') {
            this.synth.onvoiceschanged = setVoice;
        }
    }

    createControlButton() {
        const button = document.createElement('button');
        button.id = 'voice-assistant-toggle';
        button.innerHTML = '<i class="fa fa-volume-off"></i>';
        button.title = 'Activer l\'assistant vocal (lecture au survol)';
        button.setAttribute('aria-label', 'Activer/Désactiver l\'assistant vocal');
        button.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #6c757d;
            color: white;
            border: 3px solid transparent;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            cursor: pointer;
            z-index: 9999;
            font-size: 22px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        button.addEventListener('click', () => {
            this.userInteracted = true;
            this.isEnabled = !this.isEnabled;

            if (this.isEnabled) {
                button.innerHTML = '<i class="fa fa-volume-up"></i>';
                button.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                button.style.border = '3px solid rgba(255,255,255,0.4)';
                button.title = 'Désactiver l\'assistant vocal';
                // Confirmation vocale après le clic (interaction utilisateur garantie)
                this.speakNow('Assistant vocal activé');
            } else {
                button.innerHTML = '<i class="fa fa-volume-off"></i>';
                button.style.background = '#6c757d';
                button.style.border = '3px solid transparent';
                button.title = 'Activer l\'assistant vocal';
                this.stop();
            }
        });

        button.addEventListener('mouseenter', () => { button.style.transform = 'scale(1.1)'; });
        button.addEventListener('mouseleave', () => { button.style.transform = 'scale(1)'; });

        document.body.appendChild(button);
        this.button = button;
    }

    attachListeners() {
        // Sélectionner les éléments textuels pertinents (pas les scripts/styles)
        const selector = 'p, h1, h2, h3, h4, h5, h6, li, td, th, label, .card-title, .card-text, .section-title, [data-speak]';
        const elements = document.querySelectorAll(selector);

        elements.forEach(el => {
            // Éviter les éléments déjà traités
            if (el.dataset.voiceAttached) return;
            el.dataset.voiceAttached = '1';

            el.addEventListener('mouseenter', () => {
                if (!this.isEnabled || !this.userInteracted) return;
                const text = el.innerText?.trim() || el.textContent?.trim() || '';
                if (text.length > 1) {
                    el.style.outline = '2px solid rgba(102,126,234,0.35)';
                    el.style.borderRadius = '3px';
                    this.speak(text);
                }
            });

            el.addEventListener('mouseleave', () => {
                el.style.outline = '';
                el.style.borderRadius = '';
                // Ne pas couper la lecture au mouseleave — laisser finir le mot
            });
        });

        // Lecture mot par mot sur les spans .voice-word déjà présents
        document.querySelectorAll('.voice-word').forEach(span => {
            if (span.dataset.voiceAttached) return;
            span.dataset.voiceAttached = '1';
            span.addEventListener('mouseenter', () => {
                if (!this.isEnabled || !this.userInteracted) return;
                const word = span.textContent.trim();
                if (word.length > 0) {
                    span.style.backgroundColor = 'rgba(102,126,234,0.2)';
                    this.speak(word);
                }
            });
            span.addEventListener('mouseleave', () => {
                span.style.backgroundColor = '';
            });
        });
    }

    speak(text) {
        if (!this.isEnabled || !this.userInteracted || !text) return;
        this.speakNow(text);
    }

    speakNow(text) {
        // Annuler la lecture en cours
        this.stop();

        if (!text) return;

        // Recharger les voix si nécessaire
        if (!this.selectedVoice) this.loadVoices();

        this.currentUtterance = new SpeechSynthesisUtterance(text);

        if (this.selectedVoice) {
            this.currentUtterance.voice = this.selectedVoice;
        }

        this.currentUtterance.lang   = 'fr-FR';
        this.currentUtterance.rate   = 0.95;   // Légèrement plus lent pour la clarté
        this.currentUtterance.pitch  = 1.0;
        this.currentUtterance.volume = 1.0;

        // Workaround Chrome : bug où speechSynthesis se bloque après ~15s
        this.currentUtterance.onstart = () => {
            clearInterval(this._resumeTimer);
            this._resumeTimer = setInterval(() => {
                if (this.synth.speaking && this.synth.paused) {
                    this.synth.resume();
                }
            }, 5000);
        };

        this.currentUtterance.onend = () => {
            clearInterval(this._resumeTimer);
        };

        this.currentUtterance.onerror = (e) => {
            clearInterval(this._resumeTimer);
            // Ignorer l'erreur "interrupted" (normale quand on change de mot)
            if (e.error !== 'interrupted') {
                console.warn('VoiceAssistant error:', e.error);
            }
        };

        this.synth.speak(this.currentUtterance);
    }

    stop() {
        clearInterval(this._resumeTimer);
        if (this.synth.speaking || this.synth.pending) {
            this.synth.cancel();
        }
    }
}

// Initialiser quand le DOM est prêt
(function () {
    if (!('speechSynthesis' in window)) {
        console.warn('VoiceAssistant : Web Speech API non supportée par ce navigateur.');
        return;
    }

    const start = () => {
        if (!window.voiceAssistant) {
            window.voiceAssistant = new VoiceAssistant();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
