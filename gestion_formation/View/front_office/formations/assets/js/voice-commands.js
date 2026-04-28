/**
 * Commandes Vocales - Speech Recognition
 * Contrôle la navigation par la voix
 */

class VoiceCommands {
    constructor() {
        // Vérifier le support de la reconnaissance vocale
        this.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        
        if (!this.SpeechRecognition) {
            console.warn('La reconnaissance vocale n\'est pas supportée par ce navigateur.');
            return;
        }
        
        this.recognition = new this.SpeechRecognition();
        this.isListening = false;
        this.synth = window.speechSynthesis;
        
        // Configuration
        this.recognition.lang = 'fr-FR'; // Langue française
        this.recognition.continuous = false; // Arrêt après une commande
        this.recognition.interimResults = false; // Pas de résultats intermédiaires
        this.recognition.maxAlternatives = 1;
        
        // Commandes disponibles
        this.commands = {
            // Navigation
            'accueil': () => this.navigate('index.html'),
            'home': () => this.navigate('index.html'),
            'maison': () => this.navigate('index.html'),
            
            'about': () => this.navigate('about.html'),
            'à propos': () => this.navigate('about.html'),
            'apropos': () => this.navigate('about.html'),
            
            'formation': () => this.navigate('formation.php'),
            'formations': () => this.navigate('formation.php'),
            
            'réclamation': () => this.navigate('front_formulaire_reclamation.html'),
            'réclamations': () => this.navigate('front_mes_reclamations.html'),
            'reclamation': () => this.navigate('front_formulaire_reclamation.html'),
            
            // Actions
            'inscription': () => this.openInscriptionModal(),
            'inscrire': () => this.openInscriptionModal(),
            "s'inscrire": () => this.openInscriptionModal(),
            
            'connecter': () => this.clickConnectButton(),
            'se connecter': () => this.clickConnectButton(),
            'connexion': () => this.clickConnectButton(),
            'login': () => this.clickConnectButton(),
            
            // Aide
            'aide': () => this.showHelp(),
            'help': () => this.showHelp(),
            'commandes': () => this.showHelp(),
            
            // Contrôle
            'arrêter': () => this.stopListening(),
            'stop': () => this.stopListening(),
            'fermer': () => this.stopListening()
        };
        
        this.init();
    }
    
    init() {
        // Créer le bouton de contrôle
        this.createMicButton();
        
        // Configurer les événements de reconnaissance
        this.setupRecognitionEvents();
    }
    
    createMicButton() {
        const button = document.createElement('button');
        button.id = 'voice-command-toggle';
        button.innerHTML = '<i class="fa fa-microphone"></i>';
        button.title = 'Commandes vocales - Cliquez pour parler';
        button.style.cssText = `
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 9999;
            font-size: 24px;
            transition: all 0.3s ease;
        `;
        
        button.addEventListener('click', () => {
            if (this.isListening) {
                this.stopListening();
            } else {
                this.startListening();
            }
        });
        
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.1)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });
        
        document.body.appendChild(button);
        this.micButton = button;
        
        // Créer l'indicateur d'écoute
        this.createListeningIndicator();
    }
    
    createListeningIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'listening-indicator';
        indicator.innerHTML = `
            <div class="pulse"></div>
            <div class="text">🎤 J'écoute...</div>
        `;
        indicator.style.cssText = `
            position: fixed;
            bottom: 170px;
            right: 20px;
            background: rgba(17, 153, 142, 0.95);
            color: white;
            padding: 15px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 9998;
            display: none;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            animation: fadeIn 0.3s ease;
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.2); opacity: 0.7; }
            }
            #listening-indicator .pulse {
                width: 12px;
                height: 12px;
                background: #ff4444;
                border-radius: 50%;
                animation: pulse 1s infinite;
            }
            #listening-indicator .text {
                font-size: 14px;
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(indicator);
        this.listeningIndicator = indicator;
    }
    
    setupRecognitionEvents() {
        this.recognition.onstart = () => {
            console.log('Reconnaissance vocale démarrée');
            this.isListening = true;
            this.micButton.style.background = 'linear-gradient(135deg, #ff4444 0%, #ff6b6b 100%)';
            this.micButton.innerHTML = '<i class="fa fa-microphone"></i>';
            this.listeningIndicator.style.display = 'flex';
        };
        
        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript.toLowerCase().trim();
            console.log('Commande détectée:', transcript);
            this.processCommand(transcript);
        };
        
        this.recognition.onerror = (event) => {
            console.error('Erreur de reconnaissance:', event.error);
            this.stopListening();
            
            if (event.error === 'no-speech') {
                this.speak('Je n\'ai rien entendu. Réessayez.');
            } else if (event.error === 'not-allowed') {
                this.speak('Veuillez autoriser l\'accès au microphone.');
                alert('Veuillez autoriser l\'accès au microphone dans les paramètres de votre navigateur.');
            }
        };
        
        this.recognition.onend = () => {
            this.stopListening();
        };
    }
    
    startListening() {
        try {
            this.recognition.start();
            this.speak('Je vous écoute');
        } catch (error) {
            console.error('Erreur lors du démarrage:', error);
            if (error.name === 'InvalidStateError') {
                // La reconnaissance est déjà en cours
                this.recognition.stop();
                setTimeout(() => this.startListening(), 100);
            }
        }
    }
    
    stopListening() {
        this.isListening = false;
        this.micButton.style.background = 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)';
        this.micButton.innerHTML = '<i class="fa fa-microphone"></i>';
        this.listeningIndicator.style.display = 'none';
        
        try {
            this.recognition.stop();
        } catch (error) {
            console.error('Erreur lors de l\'arrêt:', error);
        }
    }
    
    processCommand(transcript) {
        console.log('Traitement de la commande:', transcript);
        
        // Chercher une correspondance exacte
        if (this.commands[transcript]) {
            this.commands[transcript]();
            return;
        }
        
        // Chercher une correspondance partielle
        for (const [command, action] of Object.entries(this.commands)) {
            if (transcript.includes(command)) {
                action();
                return;
            }
        }
        
        // Aucune commande trouvée
        this.speak(`Commande non reconnue: ${transcript}. Dites "aide" pour voir les commandes disponibles.`);
    }
    
    navigate(url) {
        this.speak(`Navigation vers ${url.replace('.html', '').replace('.php', '')}`);
        setTimeout(() => {
            window.location.href = url;
        }, 1000);
    }
    
    openInscriptionModal() {
        this.speak('Ouverture du formulaire d\'inscription');
        
        // Chercher tous les modals d'inscription possibles
        const modalSelectors = [
            '#inscriptionModal',           // Modal simple
            '[id^="inscriptionModal"]',    // Modals avec ID dynamique (inscriptionModal1, inscriptionModal2...)
            '.modal[id*="inscription"]',   // Tout modal contenant "inscription"
        ];
        
        let modalFound = false;
        
        for (const selector of modalSelectors) {
            const modals = document.querySelectorAll(selector);
            
            if (modals.length > 0) {
                // Prendre le premier modal trouvé
                const modal = modals[0];
                modalFound = true;
                
                console.log('Modal trouvé:', modal.id);
                
                // Essayer différentes méthodes pour ouvrir le modal
                
                // Méthode 1 : Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                        return;
                    } catch (e) {
                        console.log('Bootstrap 5 non disponible');
                    }
                }
                
                // Méthode 2 : Bootstrap 4/3 avec jQuery
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    try {
                        $(modal).modal('show');
                        return;
                    } catch (e) {
                        console.log('jQuery modal non disponible');
                    }
                }
                
                // Méthode 3 : Cliquer sur le bouton d'inscription
                const inscriptionButton = document.querySelector('[data-target="#' + modal.id + '"]');
                if (inscriptionButton) {
                    inscriptionButton.click();
                    return;
                }
                
                // Méthode 4 : Affichage manuel
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.classList.add('in'); // Pour Bootstrap 3
                modal.setAttribute('aria-hidden', 'false');
                
                // Ajouter le backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;';
                document.body.appendChild(backdrop);
                document.body.classList.add('modal-open');
                
                // Fermer au clic sur backdrop
                backdrop.addEventListener('click', () => {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    modal.classList.remove('in');
                    backdrop.remove();
                    document.body.classList.remove('modal-open');
                });
                
                // Fermer avec le bouton close
                const closeButtons = modal.querySelectorAll('[data-dismiss="modal"], .close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        modal.classList.remove('in');
                        backdrop.remove();
                        document.body.classList.remove('modal-open');
                    });
                });
                
                return;
            }
        }
        
        if (!modalFound) {
            this.speak('Formulaire d\'inscription non trouvé sur cette page');
            console.error('Aucun modal d\'inscription trouvé');
        }
    }
    
    clickConnectButton() {
        this.speak('Recherche du bouton de connexion');
        
        // Chercher le bouton de connexion (plusieurs possibilités)
        const selectors = [
            'a[href*="login"]',
            'a[href*="connexion"]',
            'a[href*="auth"]',
            'a[href*="signin"]',
            'button[class*="login"]',
            'button[id*="login"]',
            '.login-btn',
            '#login-btn',
            '.btn-login',
            '[data-target*="login"]',
            '[data-target*="connexion"]'
        ];
        
        // Essayer les sélecteurs
        for (const selector of selectors) {
            const button = document.querySelector(selector);
            if (button) {
                console.log('Bouton trouvé:', selector);
                this.speak('Clic sur le bouton de connexion');
                button.click();
                return;
            }
        }
        
        // Chercher dans le texte des liens
        const links = document.querySelectorAll('a, button');
        for (const link of links) {
            const text = link.textContent.toLowerCase().trim();
            if (text.includes('connexion') || 
                text.includes('connecter') ||
                text.includes('login') ||
                text.includes('se connecter') ||
                text === 'connexion' ||
                text === 'login') {
                console.log('Bouton trouvé par texte:', text);
                this.speak('Clic sur le bouton de connexion');
                link.click();
                return;
            }
        }
        
        // Si toujours pas trouvé, chercher dans la navigation
        const navLinks = document.querySelectorAll('nav a, .navbar a, .menu a, header a');
        for (const link of navLinks) {
            const text = link.textContent.toLowerCase().trim();
            if (text.includes('connexion') || text.includes('login') || text.includes('connecter')) {
                console.log('Bouton trouvé dans navigation:', text);
                this.speak('Clic sur le bouton de connexion');
                link.click();
                return;
            }
        }
        
        this.speak('Bouton de connexion non trouvé sur cette page');
        console.error('Aucun bouton de connexion trouvé');
    }
    
    showHelp() {
        const helpText = `
            Commandes disponibles:
            - Dites "accueil" ou "home" pour aller à l'accueil
            - Dites "about" ou "à propos" pour la page à propos
            - Dites "formation" pour voir les formations
            - Dites "inscription" pour ouvrir le formulaire
            - Dites "se connecter" pour vous connecter
            - Dites "réclamation" pour les réclamations
            - Dites "aide" pour cette aide
        `;
        
        this.speak('Voici les commandes disponibles');
        
        // Afficher une alerte avec les commandes
        const helpModal = document.createElement('div');
        helpModal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 10000;
            max-width: 500px;
            width: 90%;
        `;
        
        helpModal.innerHTML = `
            <h3 style="color: #11998e; margin-bottom: 20px;">🎤 Commandes Vocales</h3>
            <div style="line-height: 2; color: #333;">
                <p><strong>Navigation:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>🏠 "accueil" ou "home"</li>
                    <li>ℹ️ "about" ou "à propos"</li>
                    <li>📚 "formation" ou "formations"</li>
                    <li>📝 "réclamation"</li>
                </ul>
                <p><strong>Actions:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>✍️ "inscription" ou "s'inscrire"</li>
                    <li>🔐 "se connecter" ou "connexion"</li>
                    <li>❓ "aide" ou "help"</li>
                </ul>
            </div>
            <button onclick="this.parentElement.remove()" style="
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                color: white;
                border: none;
                padding: 10px 30px;
                border-radius: 25px;
                cursor: pointer;
                margin-top: 20px;
                font-weight: bold;
            ">Compris !</button>
        `;
        
        document.body.appendChild(helpModal);
        
        // Fermer après 10 secondes
        setTimeout(() => {
            if (helpModal.parentElement) {
                helpModal.remove();
            }
        }, 10000);
    }
    
    speak(text) {
        // Arrêter toute lecture en cours
        this.synth.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        // Chercher une voix française
        const voices = this.synth.getVoices();
        const frenchVoice = voices.find(voice => voice.lang.startsWith('fr'));
        if (frenchVoice) {
            utterance.voice = frenchVoice;
        }
        
        this.synth.speak(utterance);
    }
}

// Initialiser les commandes vocales quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si le navigateur supporte la reconnaissance vocale
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        window.voiceCommands = new VoiceCommands();
        console.log('✅ Commandes vocales activées');
    } else {
        console.warn('❌ La reconnaissance vocale n\'est pas supportée par ce navigateur.');
        console.warn('Utilisez Chrome, Edge ou Safari pour cette fonctionnalité.');
    }
});
