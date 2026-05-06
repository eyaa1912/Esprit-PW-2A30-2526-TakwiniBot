/**
 * Commandes Vocales — Navigation uniquement
 * Commandes : "about" | "formation" | "reclamation" | "login"
 */

class VoiceCommands {
    constructor() {
        this.SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!this.SR) { console.warn('VoiceCommands : utilisez Chrome ou Edge.'); return; }

        this.rec         = new this.SR();
        this.synth       = window.speechSynthesis;
        this.isListening = false;
        this.base        = this._resolveBase();

        this.rec.lang            = 'fr-FR';
        this.rec.continuous      = true;
        this.rec.interimResults  = true;
        this.rec.maxAlternatives = 5;

        this._init();
    }

    /* ── Chemin de base ─────────────────────────────────────────────────── */
    _resolveBase() {
        const p   = window.location.pathname;
        const idx = p.indexOf('/formations/');
        return idx !== -1
            ? window.location.origin + p.slice(0, idx + '/formations/'.length)
            : window.location.href.slice(0, window.location.href.lastIndexOf('/') + 1);
    }

    /* ── Initialisation ─────────────────────────────────────────────────── */
    _init() {
        this._injectStyles();
        this._createButton();
        this._createIndicator();
        this._setupEvents();
    }

    _injectStyles() {
        if (document.getElementById('vc-css')) return;
        const s = document.createElement('style');
        s.id = 'vc-css';
        s.textContent = `
            @keyframes vcPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.35);opacity:.6} }
            @keyframes vcIn    { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
        `;
        document.head.appendChild(s);
    }

    _createButton() {
        const b = document.createElement('button');
        b.id = 'vc-btn';
        b.innerHTML = '<i class="fa fa-microphone"></i>';
        b.title = 'Commandes vocales — cliquez pour parler';
        b.style.cssText = `
            position:fixed;bottom:160px;right:20px;width:60px;height:60px;
            border-radius:50%;background:linear-gradient(135deg,#11998e,#38ef7d);
            color:#fff;border:none;box-shadow:0 4px 15px rgba(0,0,0,.25);
            cursor:pointer;z-index:9999;font-size:22px;
            display:flex;align-items:center;justify-content:center;transition:all .3s;
        `;
        b.onclick = () => this.isListening ? this._stop() : this._start();
        b.onmouseenter = () => { b.style.transform = 'scale(1.1)'; };
        b.onmouseleave = () => { b.style.transform = 'scale(1)'; };
        document.body.appendChild(b);
        this.btn = b;
    }

    _createIndicator() {
        const ind = document.createElement('div');
        ind.id = 'vc-ind';
        ind.innerHTML = `
            <span style="width:11px;height:11px;background:#f44;border-radius:50%;
                animation:vcPulse 1s infinite;display:inline-block;"></span>
            <span style="font-size:13px;font-weight:600;">🎤 J'écoute…</span>`;
        ind.style.cssText = `
            position:fixed;bottom:230px;right:20px;
            background:rgba(17,153,142,.95);color:#fff;
            padding:11px 17px;border-radius:28px;
            box-shadow:0 4px 14px rgba(0,0,0,.28);
            z-index:9998;display:none;align-items:center;gap:9px;
            animation:vcIn .3s ease;
        `;
        document.body.appendChild(ind);
        this.ind = ind;
    }

    /* ── Reconnaissance ─────────────────────────────────────────────────── */
    _setupEvents() {
        this.rec.onstart = () => {
            this.isListening = true;
            this.btn.style.background = 'linear-gradient(135deg,#f44,#f77)';
            this.ind.style.display = 'flex';
        };

        this.rec.onresult = (e) => {
            let final = '';
            for (let i = e.resultIndex; i < e.results.length; i++) {
                if (e.results[i].isFinal) final += e.results[i][0].transcript;
            }
            if (final) this._handle(final.toLowerCase().trim());
        };

        this.rec.onerror = (e) => {
            if (e.error === 'no-speech') return;
            if (e.error === 'not-allowed') {
                alert('Autorisez le microphone dans les paramètres du navigateur.');
                this._stop();
            }
        };

        this.rec.onend = () => {
            if (this.isListening) try { this.rec.start(); } catch (_) {}
        };
    }

    _start() {
        try { this.rec.start(); this._say('Je vous écoute'); }
        catch (e) {
            if (e.name === 'InvalidStateError') {
                this.rec.stop();
                setTimeout(() => this._start(), 200);
            }
        }
    }

    _stop() {
        this.isListening = false;
        this.btn.style.background = 'linear-gradient(135deg,#11998e,#38ef7d)';
        this.ind.style.display = 'none';
        try { this.rec.stop(); } catch (_) {}
    }

    /* ── Traitement des commandes ───────────────────────────────────────── */
    _handle(t) {

        // accueil → page d'accueil
        if (/\baccueil\b/.test(t)) {
            this._say('Navigation vers accueil');
            setTimeout(() => { window.location.href = this.base + 'index.html'; }, 900);
            return;
        }

        // about → à propos
        if (/\babout\b/.test(t)) {
            this._say('Navigation vers à propos');
            setTimeout(() => { window.location.href = this.base + 'about.html'; }, 900);
            return;
        }

        // formation → page formations
        if (/\bformation\b/.test(t)) {
            this._say('Navigation vers formation');
            setTimeout(() => { window.location.href = this.base + 'formation.php'; }, 900);
            return;
        }

        // produits → galerie produits
        if (/\bproduits?\b/.test(t)) {
            this._say('Navigation vers produits');
            setTimeout(() => { window.location.href = this.base + 'gallery.html'; }, 900);
            return;
        }

        // entretien → blog
        if (/\bentretien\b/.test(t)) {
            this._say('Navigation vers entretien');
            setTimeout(() => { window.location.href = this.base + 'blog.html'; }, 900);
            return;
        }

        // reclamation → formulaire réclamation
        if (/\br[eé]clamation\b/.test(t)) {
            this._say('Navigation vers réclamation');
            setTimeout(() => { window.location.href = this.base + 'front_formulaire_reclamation.html'; }, 900);
            return;
        }

        // login → page connexion
        if (/\blogin\b/.test(t)) {
            this._say('Navigation vers login');
            setTimeout(() => { window.location.href = this.base + 'Modern-Login-master/login.html'; }, 900);
            return;
        }

        this._say('Commande non reconnue. Dites accueil, about, formation, produits, entretien, reclamation ou login.');
    }

    /* ── Synthèse vocale ────────────────────────────────────────────────── */
    _say(text) {
        this.synth.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'fr-FR'; u.rate = 1.0; u.volume = 1.0;
        const voices = this.synth.getVoices();
        const fr = voices.find(v => v.lang === 'fr-FR') || voices.find(v => v.lang.startsWith('fr'));
        if (fr) u.voice = fr;
        this.synth.speak(u);
    }
}

/* ── Démarrage ──────────────────────────────────────────────────────────── */
(function () {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.warn('VoiceCommands : utilisez Chrome ou Edge.');
        return;
    }
    const go = () => { if (!window.voiceCommands) window.voiceCommands = new VoiceCommands(); };
    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', go)
        : go();
})();
