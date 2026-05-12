/**
 * Hand Tracking pour TakwiniBot
 * ✋ Main ouverte = déplacer le curseur
 * ✊ Main fermée  = clic
 * Utilise MediaPipe Hands (Google AI)
 */
class HandTracker {
    constructor() {
        this.active = false;
        this.stream = null;
        this.camera = null;

        // Lissage curseur
        this.smoothX = window.innerWidth / 2;
        this.smoothY = window.innerHeight / 2;
        this.SMOOTH = 0.18;

        // État main ouverte/fermée
        this.handWasOpen = true;
        this.isClicking = false;
        this.clickCooldown = false;
        this.COOLDOWN_MS = 600;

        // Buffer pour confirmer le geste (éviter faux positifs)
        this.closedFrames = 0;
        this.CLOSED_THRESHOLD = 4; // frames consécutives main fermée pour cliquer

        this._injectStyles();
        this._buildUI();
    }

    /* ── Styles ───────────────────────────────────── */
    _injectStyles() {
        if (document.getElementById('ht-css')) return;
        const s = document.createElement('style');
        s.id = 'ht-css';
        s.textContent = `
        #ht-btn {
            position:fixed; bottom:24px; right:90px;
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#059669,#10b981);
            border:none; cursor:pointer; z-index:9995;
            box-shadow:0 4px 18px rgba(5,150,105,.45);
            font-size:24px; transition:all .25s;
            display:flex; align-items:center; justify-content:center;
        }
        #ht-btn:hover { transform:scale(1.1); }
        #ht-btn.on { background:linear-gradient(135deg,#ef4444,#f97316); }
        #ht-lbl {
            position:fixed; bottom:6px; right:90px; width:56px;
            text-align:center; font-size:10px; font-weight:700;
            color:#059669; pointer-events:none; z-index:9995;
        }

        /* Curseur virtuel */
        #ht-cur {
            position:fixed; pointer-events:none; z-index:99999;
            width:44px; height:44px; border-radius:50%;
            border:3px solid #10b981;
            background:rgba(16,185,129,.12);
            transform:translate(-50%,-50%);
            display:none;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        #ht-cur::after {
            content:''; position:absolute; top:50%; left:50%;
            transform:translate(-50%,-50%);
            width:10px; height:10px; border-radius:50%;
            background:#10b981; transition: background .15s;
        }
        #ht-cur.clicking {
            border-color:#ef4444;
            background:rgba(239,68,68,.2);
            box-shadow:0 0 20px rgba(239,68,68,.4);
        }
        #ht-cur.clicking::after { background:#ef4444; }

        /* Webcam miniature */
        #ht-vwrap {
            position:fixed; bottom:90px; right:16px;
            width:220px; border-radius:12px; overflow:hidden;
            box-shadow:0 4px 16px rgba(0,0,0,.3);
            display:none; z-index:9994;
            border:2px solid #10b981;
        }
        #ht-vwrap video { width:100%; display:block; transform:scaleX(-1); }
        #ht-vwrap canvas {
            position:absolute; top:0; left:0;
            width:100%; height:100%; transform:scaleX(-1);
        }

        /* Badge geste */
        #ht-gest {
            position:fixed; top:16px; left:50%; transform:translateX(-50%);
            padding:10px 24px; border-radius:28px;
            font-size:15px; font-weight:700; z-index:9994;
            display:none; align-items:center; gap:8px;
            box-shadow:0 4px 14px rgba(0,0,0,.25); color:#fff;
        }
        #ht-gest.open { background:rgba(5,150,105,.92); }
        #ht-gest.closed { background:rgba(239,68,68,.92); }

        /* Toast */
        #ht-toast {
            position:fixed; top:70px; left:50%; transform:translateX(-50%);
            background:rgba(10,10,30,.9); color:#fff;
            padding:10px 20px; border-radius:26px;
            font-size:13px; font-weight:600; z-index:9993;
            display:none; align-items:center; gap:8px;
        }

        /* Aide */
        #ht-help {
            position:fixed; bottom:90px; left:16px;
            background:rgba(10,10,30,.92); color:#fff;
            padding:14px 16px; border-radius:14px;
            font-size:13px; z-index:9994; display:none;
            flex-direction:column; gap:10px; min-width:220px;
            border:1px solid rgba(16,185,129,.3);
        }
        #ht-help .r { display:flex; align-items:center; gap:10px; }
        #ht-help .i { font-size:22px; width:30px; text-align:center; }

        /* État main */
        #ht-state {
            position:fixed; bottom:170px; right:16px;
            width:160px; background:rgba(10,10,30,.85);
            border-radius:12px; padding:10px 14px;
            display:none; z-index:9994; flex-direction:column; gap:6px;
            text-align:center;
        }
        #ht-state-icon { font-size:32px; }
        #ht-state-text {
            font-size:12px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.5px;
        }
        #ht-state.open #ht-state-icon::after { content:'✋'; }
        #ht-state.open #ht-state-text { color:#6ee7b7; }
        #ht-state.closed #ht-state-icon::after { content:'✊'; }
        #ht-state.closed #ht-state-text { color:#fca5a5; }

        /* Slider vitesse */
        #ht-spd {
            position:fixed; bottom:260px; right:16px;
            background:rgba(10,10,30,.88); color:#fff;
            padding:10px 13px; border-radius:13px;
            font-size:12px; z-index:9994; display:none;
            flex-direction:column; gap:5px; width:160px;
        }
        #ht-spd input { accent-color:#10b981; width:100%; }

        /* Effet clic */
        @keyframes htRing {
            0%   { transform:translate(-50%,-50%) scale(0); opacity:1; }
            100% { transform:translate(-50%,-50%) scale(3); opacity:0; }
        }
        `;
        document.head.appendChild(s);
    }

    /* ── UI ───────────────────────────────────────── */
    _buildUI() {
        // Bouton ON/OFF
        const btn = document.createElement('button');
        btn.id = 'ht-btn'; btn.innerHTML = '🤚';
        btn.title = 'Activer le contrôle par la main';
        btn.onclick = () => this.active ? this._stop() : this._start();
        document.body.appendChild(btn);
        this.btn = btn;

        document.body.insertAdjacentHTML('beforeend', '<div id="ht-lbl">Mains</div>');

        // Curseur
        const cur = document.createElement('div');
        cur.id = 'ht-cur';
        document.body.appendChild(cur);
        this.cur = cur;

        // Badge geste
        const gest = document.createElement('div');
        gest.id = 'ht-gest';
        document.body.appendChild(gest);
        this.gestEl = gest;

        // Toast
        const toast = document.createElement('div');
        toast.id = 'ht-toast';
        document.body.appendChild(toast);
        this.toastEl = toast;

        // État main
        document.body.insertAdjacentHTML('beforeend', `
            <div id="ht-state" class="open">
                <div id="ht-state-icon"></div>
                <div id="ht-state-text">En attente...</div>
            </div>
        `);
        this.stateEl = document.getElementById('ht-state');
        this.stateText = document.getElementById('ht-state-text');

        // Aide
        const help = document.createElement('div');
        help.id = 'ht-help';
        help.innerHTML = `
            <div style="font-weight:700;font-size:11px;color:#6ee7b7;text-transform:uppercase;margin-bottom:2px;">CONTRÔLE MAIN</div>
            <div class="r"><span class="i">✋</span><span>Main ouverte → Déplacer</span></div>
            <div class="r"><span class="i">✊</span><span>Main fermée → Clic</span></div>
            <div style="margin-top:4px;font-size:10px;color:#94a3b8;">La caméra détecte uniquement votre main</div>
        `;
        document.body.appendChild(help);
        this.helpEl = help;

        // Slider vitesse
        const spd = document.createElement('div');
        spd.id = 'ht-spd';
        spd.innerHTML = `
            <div style="font-weight:700;font-size:11px;color:#6ee7b7;text-transform:uppercase;">⚡ Sensibilité</div>
            <input type="range" id="ht-sl" min="8" max="45" value="18">
            <div style="display:flex;justify-content:space-between;font-size:10px;color:#64748b;">
                <span>Lisse</span><span id="ht-slv">18%</span><span>Rapide</span>
            </div>
        `;
        document.body.appendChild(spd);
        this.spdEl = spd;
        setTimeout(() => {
            const sl = document.getElementById('ht-sl');
            if (sl) sl.oninput = e => {
                this.SMOOTH = parseInt(e.target.value) / 100;
                document.getElementById('ht-slv').textContent = e.target.value + '%';
            };
        }, 300);

        // Vidéo + canvas
        const vw = document.createElement('div');
        vw.id = 'ht-vwrap';
        vw.innerHTML = `
            <video id="ht-vid" autoplay playsinline muted></video>
            <canvas id="ht-canvas"></canvas>
        `;
        document.body.appendChild(vw);
        this.video = document.getElementById('ht-vid');
        this.canvas = document.getElementById('ht-canvas');
        this.ctx = this.canvas.getContext('2d');
        this.vwrapEl = vw;
    }

    /* ── Helpers ──────────────────────────────────── */
    _toast(icon, msg, ms = 3500) {
        this.toastEl.innerHTML = `<span style="font-size:16px">${icon}</span>${msg}`;
        this.toastEl.style.display = 'flex';
        clearTimeout(this._tt);
        this._tt = setTimeout(() => { this.toastEl.style.display = 'none'; }, ms);
    }

    _showGest(msg, type) {
        this.gestEl.textContent = msg;
        this.gestEl.className = type; // 'open' ou 'closed'
        this.gestEl.style.display = 'flex';
        clearTimeout(this._gt);
        this._gt = setTimeout(() => { this.gestEl.style.display = 'none'; }, 800);
    }

    /* ── Démarrage ────────────────────────────────── */
    _start() {
        this._toast('📷', 'Chargement détection main...', 5000);
        this._load('https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js', () => {
            this._load('https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js', () => {
                this._openCamera();
            });
        });
    }

    _load(src, cb) {
        if (document.querySelector(`script[src="${src}"]`)) { cb(); return; }
        const s = document.createElement('script');
        s.src = src; s.crossOrigin = 'anonymous';
        s.onload = cb;
        s.onerror = () => this._toast('❌', 'Erreur réseau.', 5000);
        document.head.appendChild(s);
    }

    _openCamera() {
        navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        })
        .then(stream => {
            this.stream = stream;
            this.video.srcObject = stream;
            this.vwrapEl.style.display = 'block';
            this.video.onloadedmetadata = () => {
                this.canvas.width = this.video.videoWidth;
                this.canvas.height = this.video.videoHeight;
            };
            this._setupMediaPipe();
        })
        .catch(() => this._toast('❌', 'Caméra refusée. Autorisez dans le navigateur.', 5000));
    }

    _setupMediaPipe() {
        const hands = new Hands({
            locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${f}`
        });
        hands.setOptions({
            maxNumHands: 1,
            modelComplexity: 1,
            minDetectionConfidence: 0.7,
            minTrackingConfidence: 0.6
        });
        hands.onResults(r => this._process(r));

        const cam = new Camera(this.video, {
            onFrame: async () => { await hands.send({ image: this.video }); },
            width: 640, height: 480
        });
        cam.start();
        this.camera = cam;

        this.active = true;
        this.btn.classList.add('on');
        this.btn.innerHTML = '🔴';
        this.cur.style.display = 'block';
        this.helpEl.style.display = 'flex';
        this.spdEl.style.display = 'flex';
        this.stateEl.style.display = 'flex';

        this._toast('✅', '✋ Main ouverte = Déplacer | ✊ Main fermée = Clic', 5000);
    }

    /* ── Détection main ouverte / fermée ──────────── */
    _isHandOpen(lm) {
        // Vérifier si chaque doigt est étendu
        // Un doigt est étendu si le bout (tip) est plus haut que le PIP (2e articulation)
        const fingers = [
            { tip: 8,  pip: 6  }, // Index
            { tip: 12, pip: 10 }, // Majeur
            { tip: 16, pip: 14 }, // Annulaire
            { tip: 20, pip: 18 }, // Auriculaire
        ];

        let extended = 0;
        for (const f of fingers) {
            if (lm[f.tip].y < lm[f.pip].y) extended++;
        }

        // Pouce : vérifier distance horizontale
        const thumbTip = lm[4];
        const thumbIp = lm[3];
        const thumbMcp = lm[2];
        const thumbDist = Math.hypot(thumbTip.x - thumbMcp.x, thumbTip.y - thumbMcp.y);
        const thumbRef = Math.hypot(thumbIp.x - thumbMcp.x, thumbIp.y - thumbMcp.y);
        if (thumbDist > thumbRef * 1.15) extended++;

        // Main ouverte si >= 3 doigts étendus
        return extended >= 3;
    }

    /* ── Traitement frame ─────────────────────────── */
    _process(results) {
        if (!this.active) return;

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        if (!results.multiHandLandmarks || !results.multiHandLandmarks.length) {
            this.closedFrames = 0;
            this.cur.classList.remove('clicking');
            this.stateText.textContent = 'Aucune main';
            this.stateEl.className = 'open';
            return;
        }

        const lm = results.multiHandLandmarks[0];
        const W = window.innerWidth;
        const H = window.innerHeight;

        // Dessiner le skeleton
        this._drawSkeleton(lm);

        // Position curseur = bout de l'index (landmark 8)
        const ix = lm[8].x;
        const iy = lm[8].y;

        // Miroir X + mapping à l'écran
        const tx = (1 - ix) * W;
        const ty = iy * H;

        // Lissage
        this.smoothX += (tx - this.smoothX) * this.SMOOTH;
        this.smoothY += (ty - this.smoothY) * this.SMOOTH;

        const cx = Math.max(0, Math.min(W, this.smoothX));
        const cy = Math.max(0, Math.min(H, this.smoothY));
        this.cur.style.left = cx + 'px';
        this.cur.style.top = cy + 'px';

        // Détection ouverte/fermée
        const isOpen = this._isHandOpen(lm);

        if (isOpen) {
            // Main ouverte → déplacer
            this.closedFrames = 0;
            this.isClicking = false;
            this.handWasOpen = true;
            this.cur.classList.remove('clicking');
            this.stateEl.className = 'open';
            this.stateText.textContent = '✋ Déplacer';
        } else {
            // Main fermée → potentiel clic
            this.closedFrames++;
            this.cur.classList.add('clicking');
            this.stateEl.className = 'closed';
            this.stateText.textContent = '✊ Clic !';

            // Clic uniquement sur transition ouverte → fermée (confirmée)
            if (this.handWasOpen && this.closedFrames >= this.CLOSED_THRESHOLD && !this.clickCooldown) {
                this._doClick(cx, cy);
                this._showGest('✊ CLIC !', 'closed');
                this._playSound();
                this.handWasOpen = false;
                this.clickCooldown = true;
                setTimeout(() => { this.clickCooldown = false; }, this.COOLDOWN_MS);
            }
        }
    }

    /* ── Dessiner skeleton ────────────────────────── */
    _drawSkeleton(lm) {
        const W = this.canvas.width;
        const H = this.canvas.height;
        const ctx = this.ctx;

        const connections = [
            [0,1],[1,2],[2,3],[3,4],
            [0,5],[5,6],[6,7],[7,8],
            [0,9],[9,10],[10,11],[11,12],
            [0,13],[13,14],[14,15],[15,16],
            [0,17],[17,18],[18,19],[19,20],
            [5,9],[9,13],[13,17]
        ];

        ctx.strokeStyle = '#10b981';
        ctx.lineWidth = 2;
        connections.forEach(([a, b]) => {
            ctx.beginPath();
            ctx.moveTo((1 - lm[a].x) * W, lm[a].y * H);
            ctx.lineTo((1 - lm[b].x) * W, lm[b].y * H);
            ctx.stroke();
        });

        lm.forEach((pt, i) => {
            ctx.beginPath();
            const isTip = [4, 8, 12, 16, 20].includes(i);
            ctx.arc((1 - pt.x) * W, pt.y * H, isTip ? 5 : 3, 0, Math.PI * 2);
            ctx.fillStyle = isTip ? '#6c5ce7' : (i === 0 ? '#f59e0b' : '#10b981');
            ctx.fill();
        });
    }

    /* ── Clic réel sur l'élément sous le curseur ── */
    _doClick(x, y) {
        // Masquer le curseur pour trouver l'élément en dessous
        this.cur.style.display = 'none';
        const el = document.elementFromPoint(x, y);
        this.cur.style.display = 'block';

        if (!el) return;

        // Effet visuel de clic
        const ring = document.createElement('div');
        ring.style.cssText = `position:fixed;left:${x}px;top:${y}px;width:44px;height:44px;
            border-radius:50%;border:3px solid #ef4444;pointer-events:none;
            transform:translate(-50%,-50%) scale(0);z-index:99999;
            animation:htRing .5s ease forwards;`;
        document.body.appendChild(ring);
        setTimeout(() => ring.remove(), 520);

        // Chercher un lien ou bouton parent
        let target = el;
        for (let i = 0; i < 8; i++) {
            if (!target) break;
            if (target.tagName === 'A' && target.href) {
                setTimeout(() => { window.location.href = target.href; }, 250);
                return;
            }
            if (target.tagName === 'BUTTON' || target.type === 'submit') {
                target.click();
                return;
            }
            target = target.parentElement;
        }

        // Sinon, clic générique
        el.dispatchEvent(new MouseEvent('click', {
            bubbles: true, cancelable: true, clientX: x, clientY: y, view: window
        }));
    }

    /* ── Son de clic ──────────────────────────────── */
    _playSound() {
        try {
            const ac = new (window.AudioContext || window.webkitAudioContext)();
            const o = ac.createOscillator();
            const g = ac.createGain();
            o.connect(g); g.connect(ac.destination);
            o.frequency.value = 900;
            o.type = 'sine';
            g.gain.setValueAtTime(0.08, ac.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, ac.currentTime + 0.1);
            o.start(ac.currentTime);
            o.stop(ac.currentTime + 0.1);
        } catch (_) {}
    }

    /* ── Arrêt ────────────────────────────────────── */
    _stop() {
        this.active = false;
        this.btn.classList.remove('on');
        this.btn.innerHTML = '🤚';
        this.cur.style.display = 'none';
        this.helpEl.style.display = 'none';
        this.spdEl.style.display = 'none';
        this.vwrapEl.style.display = 'none';
        this.gestEl.style.display = 'none';
        this.stateEl.style.display = 'none';
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        if (this.stream) this.stream.getTracks().forEach(t => t.stop());
        if (this.camera) try { this.camera.stop(); } catch (_) {}
        this._toast('⏹️', 'Contrôle par la main désactivé', 2000);
    }
}

/* ── Init ─────────────────────────────────────────── */
(function () {
    const go = () => { if (!window.handTracker) window.handTracker = new HandTracker(); };
    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', go)
        : go();
})();
