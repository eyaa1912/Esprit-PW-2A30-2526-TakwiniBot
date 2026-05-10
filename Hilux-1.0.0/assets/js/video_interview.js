/**
 * TAKWINI VIDEO INTERVIEW - Complete Interview System
 * Conducts job interviews via video call with human-like avatar
 */

class VideoInterview {
    constructor() {
        this.config = this.loadConfig();
        this.currentQuestionIndex = 0;
        this.answers = {};
        this.isRecording = false;
        this.isMicOn = true;
        this.isSubtitlesOn = true;
        this.interviewStartTime = null;
        this.questionStartTime = null;
        this.mediaStream = null;
        this.recognition = this.initSpeechRecognition();
        this.synthesis = window.speechSynthesis;
        this.currentUtterance = null;
        this.avatar = null;
        
        this.init();
    }

    loadConfig() {
        const configEl = document.getElementById('interviewConfig');
        if (configEl) {
            return JSON.parse(configEl.textContent);
        }
        return {};
    }

    async init() {
        this.cacheElements();
        this.setupDisabilityMode();
        await this.initializeWebcam();
        this.attachEventListeners();
        this.startInterview();
    }

    cacheElements() {
        this.candidateVideo = document.getElementById('candidateVideo');
        this.subtitleBar = document.getElementById('subtitleBar');
        this.subtitleText = document.getElementById('subtitleText');
        this.questionDisplay = document.getElementById('questionDisplay');
        this.questionText = document.getElementById('questionText');
        this.timerText = document.getElementById('timerText');
        this.micBtn = document.getElementById('micBtn');
        this.subtitleBtn = document.getElementById('subtitleBtn');
        this.contrastBtn = document.getElementById('contrastBtn');
        this.endCallBtn = document.getElementById('endCallBtn');
        this.answerInputGroup = document.getElementById('answerInputGroup');
        this.answerInput = document.getElementById('answerInput');
        this.submitAnswerBtn = document.getElementById('submitAnswerBtn');
        this.motorButtons = document.getElementById('motorButtons');
        this.emojiButtons = document.getElementById('emojiButtons');
        this.avatarContainer = document.querySelector('.avatar-container');
        this.avatar = document.querySelector('.takwini-avatar-video');
    }

    setupDisabilityMode() {
        const typeHandicap = this.config.typeHandicap;

        switch (typeHandicap) {
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
        this.subtitleBar.style.display = 'flex';
        this.answerInputGroup.style.display = 'flex';
        this.isSubtitlesOn = true;
    }

    setupMoteurMode() {
        // Show large buttons
        this.motorButtons.style.display = 'flex';
        this.isSubtitlesOn = true;
    }

    setupVisuelMode() {
        // Show high contrast button
        this.contrastBtn.style.display = 'flex';
        this.isSubtitlesOn = false;
    }

    setupCognitifMode() {
        // Show emoji buttons
        this.emojiButtons.style.display = 'flex';
        this.isSubtitlesOn = true;
    }

    setupDefaultMode() {
        this.isSubtitlesOn = true;
    }

    async initializeWebcam() {
        try {
            this.mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: true
            });
            this.candidateVideo.srcObject = this.mediaStream;
        } catch (error) {
            console.error('Webcam access denied:', error);
            alert('Please allow webcam access to continue with the interview.');
        }
    }

    attachEventListeners() {
        this.micBtn.addEventListener('click', () => this.toggleMicrophone());
        this.subtitleBtn.addEventListener('click', () => this.toggleSubtitles());
        this.contrastBtn.addEventListener('click', () => this.toggleHighContrast());
        this.endCallBtn.addEventListener('click', () => this.endInterview());
        this.submitAnswerBtn.addEventListener('click', () => this.submitTextAnswer());

        // Motor mode buttons
        const motorBtns = this.motorButtons.querySelectorAll('.motor-btn');
        motorBtns[0].addEventListener('click', () => this.handleMotorResponse('yes'));
        motorBtns[1].addEventListener('click', () => this.handleMotorResponse('no'));
        motorBtns[2].addEventListener('click', () => this.nextQuestion());

        // Cognitive mode buttons
        const emojiBtns = this.emojiButtons.querySelectorAll('.emoji-btn');
        emojiBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleEmotionResponse(e.target.dataset.emotion));
        });

        // Answer input
        this.answerInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.submitTextAnswer();
            }
        });
    }

    startInterview() {
        this.interviewStartTime = new Date();
        this.greetCandidate();
    }

    greetCandidate() {
        const greeting = `Bonjour ${this.config.candidateName}, je suis Takwini, votre interviewer. Merci de participer à cet entretien pour le poste de ${this.config.position}. Êtes-vous prêt à commencer?`;
        
        this.animateAvatar('smile');
        this.speak(greeting);
        this.showSubtitle(greeting);

        setTimeout(() => {
            this.nextQuestion();
        }, 5000);
    }

    nextQuestion() {
        if (this.currentQuestionIndex >= this.config.questions.length) {
            this.endInterview();
            return;
        }

        const question = this.config.questions[this.currentQuestionIndex];
        this.questionStartTime = new Date();

        this.questionText.textContent = question.text;
        this.speak(question.text);
        this.showSubtitle(question.text);
        this.startQuestionTimer(question.duration);

        if (this.config.typeHandicap === 'auditif') {
            this.animateHandSign('attention');
        } else if (this.config.typeHandicap === 'moteur') {
            this.animateAvatar('listening');
        } else if (this.config.typeHandicap === 'cognitif') {
            this.animateAvatar('thinking');
        }

        this.currentQuestionIndex++;
    }

    startQuestionTimer(duration) {
        let remaining = duration;
        const timerInterval = setInterval(() => {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            this.timerText.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            remaining--;

            if (remaining < 0) {
                clearInterval(timerInterval);
                if (this.config.typeHandicap !== 'auditif' && this.config.typeHandicap !== 'moteur') {
                    this.nextQuestion();
                }
            }
        }, 1000);
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
            this.animateMouth('speaking');
            this.showSoundWaves();
        };

        this.currentUtterance.onend = () => {
            this.animateMouth('idle');
            this.hideSoundWaves();
        };

        this.synthesis.speak(this.currentUtterance);
    }

    stopSpeaking() {
        if (this.synthesis.speaking) {
            this.synthesis.cancel();
            this.animateMouth('idle');
            this.hideSoundWaves();
        }
    }

    showSubtitle(text) {
        if (this.isSubtitlesOn) {
            this.subtitleText.textContent = text;
            this.subtitleBar.style.display = 'flex';
        }
    }

    hideSubtitle() {
        this.subtitleBar.style.display = 'none';
    }

    toggleMicrophone() {
        this.isMicOn = !this.isMicOn;
        this.micBtn.classList.toggle('active', this.isMicOn);
        this.micBtn.classList.toggle('inactive', !this.isMicOn);

        if (this.mediaStream) {
            this.mediaStream.getAudioTracks().forEach(track => {
                track.enabled = this.isMicOn;
            });
        }
    }

    toggleSubtitles() {
        this.isSubtitlesOn = !this.isSubtitlesOn;
        this.subtitleBtn.classList.toggle('active', this.isSubtitlesOn);
        this.subtitleBtn.classList.toggle('inactive', !this.isSubtitlesOn);

        if (!this.isSubtitlesOn) {
            this.hideSubtitle();
        }
    }

    toggleHighContrast() {
        document.body.classList.toggle('high-contrast');
        this.contrastBtn.classList.toggle('active');
    }

    submitTextAnswer() {
        const answer = this.answerInput.value.trim();
        if (answer) {
            this.answers[this.currentQuestionIndex - 1] = answer;
            this.answerInput.value = '';
            this.animateAvatar('happy');
            this.speak('Merci pour votre réponse. Passons à la question suivante.');
            setTimeout(() => this.nextQuestion(), 2000);
        }
    }

    handleMotorResponse(response) {
        const responses = {
            yes: 'Excellent!',
            no: 'D\'accord'
        };

        this.answers[this.currentQuestionIndex - 1] = response;
        this.speak(responses[response]);
        this.animateAvatar('happy');
        this.animateHandSign(response === 'yes' ? 'thumbsup' : 'nod');

        setTimeout(() => this.nextQuestion(), 1500);
    }

    handleEmotionResponse(emotion) {
        const responses = {
            happy: 'Super!',
            thinking: 'Prenez votre temps',
            confused: 'Pas de problème',
            excited: 'Bravo!'
        };

        this.answers[this.currentQuestionIndex - 1] = emotion;
        this.speak(responses[emotion]);
        this.animateAvatar('happy');

        setTimeout(() => this.nextQuestion(), 2000);
    }

    initSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) return null;

        const recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR';
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onstart = () => {
            this.animateAvatar('listening');
        };

        recognition.onresult = (event) => {
            const transcript = Array.from(event.results)
                .map(result => result[0].transcript)
                .join('');

            this.answers[this.currentQuestionIndex - 1] = transcript;
            this.animateAvatar('happy');
            this.speak('Merci pour votre réponse. Passons à la question suivante.');

            setTimeout(() => this.nextQuestion(), 2000);
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
        };

        return recognition;
    }

    startListening() {
        if (this.recognition && !this.isRecording) {
            this.isRecording = true;
            this.recognition.start();
        }
    }

    // Avatar Animations
    animateAvatar(expression) {
        if (!this.avatar) return;

        this.avatar.classList.remove('celebrating', 'nodding');

        switch (expression) {
            case 'happy':
                this.avatar.classList.add('celebrating');
                this.animateMouth('smiling');
                break;
            case 'listening':
                this.animateEyebrows('listening');
                break;
            case 'thinking':
                this.animateEyebrows('thinking');
                break;
            case 'smile':
                this.animateMouth('smiling');
                break;
        }
    }

    animateMouth(state) {
        const mouth = this.avatar?.querySelector('.takwini-mouth');
        if (!mouth) return;

        if (state === 'speaking') {
            mouth.style.animation = 'talk-video 0.5s infinite';
        } else if (state === 'smiling') {
            mouth.classList.add('smiling');
        } else {
            mouth.classList.remove('smiling');
            mouth.style.animation = 'talk-video 2s infinite';
        }
    }

    animateEyebrows(expression) {
        const eyebrows = this.avatar?.querySelectorAll('.takwini-eyebrow');
        if (!eyebrows) return;

        eyebrows.forEach(brow => {
            if (expression === 'listening') {
                brow.style.animation = 'expression-neutral-video 1s infinite';
            } else if (expression === 'thinking') {
                brow.style.animation = 'expression-neutral-video 2s infinite';
            }
        });
    }

    animateHandSign(sign) {
        const arms = this.avatar?.querySelectorAll('.takwini-arm');
        if (!arms) return;

        arms.forEach(arm => {
            arm.classList.remove('sign-hello', 'sign-yes', 'sign-no', 'sign-celebrate', 'sign-thumbsup');
            
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
                case 'thumbsup':
                    arm.classList.add('sign-thumbsup');
                    break;
                case 'attention':
                    arm.classList.add('sign-hello');
                    break;
            }
        });
    }

    showSoundWaves() {
        const soundWaves = this.avatar?.querySelector('.takwini-sound-waves');
        if (soundWaves) {
            soundWaves.style.display = 'block';
        }
    }

    hideSoundWaves() {
        const soundWaves = this.avatar?.querySelector('.takwini-sound-waves');
        if (soundWaves) {
            soundWaves.style.display = 'none';
        }
    }

    async endInterview() {
        this.stopSpeaking();

        const closingMessage = `Merci beaucoup pour cet entretien! Vous avez été formidable. Nous vous recontacterons bientôt avec notre décision.`;
        this.speak(closingMessage);
        this.showSubtitle(closingMessage);
        this.animateAvatar('happy');
        this.animateHandSign('celebrate');

        // Save answers to database
        await this.saveAnswers();

        // Stop webcam
        if (this.mediaStream) {
            this.mediaStream.getTracks().forEach(track => track.stop());
        }

        // Redirect after 5 seconds
        setTimeout(() => {
            window.location.href = '/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/Hilux-1.0.0/View/entretien/interview_complete.php?id=' + this.config.entretienId;
        }, 5000);
    }

    async saveAnswers() {
        try {
            const response = await fetch('/Esprit-PW-2A30-2627-TakwiniBot-gestion_entretien/Hilux-1.0.0/Controller/save_interview_answers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    entretienId: this.config.entretienId,
                    answers: this.answers,
                    duration: Math.floor((new Date() - this.interviewStartTime) / 1000)
                })
            });

            const result = await response.json();
            console.log('Interview answers saved:', result);
        } catch (error) {
            console.error('Error saving interview answers:', error);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.videoInterview = new VideoInterview();
});
