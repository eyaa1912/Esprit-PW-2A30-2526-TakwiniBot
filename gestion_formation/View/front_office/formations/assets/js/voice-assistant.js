/**
 * Assistant Vocal - Text to Speech
 * Lit le texte lorsque le curseur passe sur un mot
 */

class VoiceAssistant {
    constructor() {
        this.synth = window.speechSynthesis;
        this.isEnabled = true;
        this.currentUtterance = null;
        this.voices = [];
        this.selectedVoice = null;
        
        // Charger les voix disponibles
        this.loadVoices();
        
        // Initialiser l'assistant
        this.init();
    }
    
    loadVoices() {
        this.voices = this.synth.getVoices();
        
        // Chercher une voix française
        this.selectedVoice = this.voices.find(voice => voice.lang.startsWith('fr')) || this.voices[0];
        
        // Les voix peuvent se charger de manière asynchrone
        if (this.synth.onvoiceschanged !== undefined) {
            this.synth.onvoiceschanged = () => {
                this.voices = this.synth.getVoices();
                this.selectedVoice = this.voices.find(voice => voice.lang.startsWith('fr')) || this.voices[0];
            };
        }
    }
    
    init() {
        // Ajouter un bouton de contrôle pour activer/désactiver
        this.createControlButton();
        
        // Ajouter les écouteurs d'événements sur tous les éléments textuels
        this.attachListeners();
    }
    
    createControlButton() {
        const button = document.createElement('button');
        button.id = 'voice-assistant-toggle';
        button.innerHTML = '<i class="fa fa-volume-up"></i>';
        button.title = 'Activer/Désactiver l\'assistant vocal';
        button.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 9999;
            font-size: 24px;
            transition: all 0.3s ease;
        `;
        
        button.addEventListener('click', () => {
            this.isEnabled = !this.isEnabled;
            if (this.isEnabled) {
                button.innerHTML = '<i class="fa fa-volume-up"></i>';
                button.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                this.speak('Assistant vocal activé');
            } else {
                button.innerHTML = '<i class="fa fa-volume-off"></i>';
                button.style.background = '#6c757d';
                this.stop();
            }
        });
        
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.1)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });
        
        document.body.appendChild(button);
    }
    
    attachListeners() {
        // Sélectionner tous les éléments textuels
        const textElements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, a, li, td, th, label, button, div');
        
        textElements.forEach(element => {
            // Ignorer les éléments vides ou qui contiennent d'autres éléments
            if (element.childNodes.length === 1 && element.childNodes[0].nodeType === Node.TEXT_NODE) {
                element.style.cursor = 'pointer';
                
                element.addEventListener('mouseenter', (e) => {
                    if (this.isEnabled) {
                        const text = element.textContent.trim();
                        if (text.length > 0) {
                            // Ajouter un effet visuel
                            element.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
                            element.style.transition = 'background-color 0.2s';
                            
                            this.speak(text);
                        }
                    }
                });
                
                element.addEventListener('mouseleave', (e) => {
                    element.style.backgroundColor = '';
                });
            } else {
                // Pour les éléments avec plusieurs enfants, écouter les mots individuels
                this.wrapWordsInSpans(element);
            }
        });
    }
    
    wrapWordsInSpans(element) {
        // Ne traiter que les nœuds texte directs
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        const textNodes = [];
        let node;
        
        while (node = walker.nextNode()) {
            if (node.textContent.trim().length > 0) {
                textNodes.push(node);
            }
        }
        
        textNodes.forEach(textNode => {
            const words = textNode.textContent.split(/(\s+)/);
            const fragment = document.createDocumentFragment();
            
            words.forEach(word => {
                if (word.trim().length > 0) {
                    const span = document.createElement('span');
                    span.textContent = word;
                    span.style.cursor = 'pointer';
                    span.className = 'voice-word';
                    
                    span.addEventListener('mouseenter', () => {
                        if (this.isEnabled) {
                            span.style.backgroundColor = 'rgba(102, 126, 234, 0.2)';
                            span.style.transition = 'background-color 0.2s';
                            this.speak(word);
                        }
                    });
                    
                    span.addEventListener('mouseleave', () => {
                        span.style.backgroundColor = '';
                    });
                    
                    fragment.appendChild(span);
                } else {
                    fragment.appendChild(document.createTextNode(word));
                }
            });
            
            textNode.parentNode.replaceChild(fragment, textNode);
        });
    }
    
    speak(text) {
        // Arrêter la lecture en cours
        this.stop();
        
        if (!this.isEnabled || !text) return;
        
        // Créer une nouvelle utterance
        this.currentUtterance = new SpeechSynthesisUtterance(text);
        this.currentUtterance.voice = this.selectedVoice;
        this.currentUtterance.rate = 1.0; // Vitesse normale
        this.currentUtterance.pitch = 1.0; // Ton normal
        this.currentUtterance.volume = 1.0; // Volume maximum
        
        // Lire le texte
        this.synth.speak(this.currentUtterance);
    }
    
    stop() {
        if (this.synth.speaking) {
            this.synth.cancel();
        }
    }
}

// Initialiser l'assistant vocal quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si le navigateur supporte la synthèse vocale
    if ('speechSynthesis' in window) {
        window.voiceAssistant = new VoiceAssistant();
    } else {
        console.warn('La synthèse vocale n\'est pas supportée par ce navigateur.');
    }
});
