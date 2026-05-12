/**
 * Eye Tracking — MediaPipe Face Mesh (Google)
 * Détecte les yeux via webcam avec précision
 * 👁️ Regard = déplacement curseur | 😑 Clignement = clic
 */
class EyeTracker {
    constructor() {
        this.active      = false;
        this.video       = null;
        this.canvas      = null;
        this.ctx         = null;
        this.faceMesh    = null;
        this.camera      = null;

        // Position du regard lissée
        this.smoothX     = window.innerWidth  / 2;
        this.smoothY     = window.innerHeight / 2;
        this.SMOOTH      = 0.15; // 0=très lisse, 1=direct

        // Clignement
        this.EAR_THRESH  = 0.21;  // seuil fermeture yeux (Eye Aspect Ratio)
        this.blinkStart  = 0;
        this.eyesClosed  = false;
        this.blinkCount  = 0;
        this.blinkTimer  = null;

        // Calibration
        this.calibrated  = false;
        this.calPoints   = [];    // [{screenX, screenY, gazeX, gazeY}]
        this.calStep     = 0;

        // Régression linéaire pour mapper regard → écran
        this.mapX = null; // fonction gaze → screen X
        this.mapY = null; // fonction gaze → screen Y

        this._injectStyles();
        this._createUI();
    }

    /* ── Styles ─────────────────────────────────────────────────────── */
    _injectStyles() {
        if (document.getElementById('et-css')) return;
        const s = document.createElement('style');
        s.id = 'et-css';
        s.textContent = `
        #et-btn {
            position:fixed; bottom:24px; right:90px;
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#0ea5e9,#6366f1);
            border:none; cursor:pointer; z-index:9995;
            box-shadow:0 4px 18px rgba(14,165,233,.4);
            display:flex; align-items:center; justify-content:center;
            font-size:24px; transition:all .25s;
        }
        #et-btn:hover   { transform:scale(1.1); }
        #et-btn.on      { background:linear-gradient(135deg,#ef4444,#f97316); }
        #et-lbl {
            position:fixed; bottom:6px; right:90px; width:56px;
            text-align:center; font-size:10px; font-weight:700;
            color:#0ea5e9; pointer-events:none; z-index:9995;
        }
        /* Curseur virtuel */
        #et-cur {
            position:fixed; pointer-events:none; z-index:99999;
            width:26px; height:26px; border-radius:50%;
            border:3px solid #6366f1;
            background:rgba(99,102,241,.15);
            transform:translate(-50%,-50%);
            display:none;
        }
        #et-cur::after {
            content:''; position:absolute; top:50%; left:50%;
            transform:translate(-50%,-50%);
            width:6px; height:6px; border-radius:50%;
            background:#6366f1;
        }
        #et-cur.closed { border-color:#ef4444; background:rgba(239,68,68,.25); }
        #et-cur.closed::after { background:#ef4444; }
        /* Vidéo webcam (petite, coin) */
        #et-video-wrap {
            position:fixed; bottom:90px; right:16px;
            width:160px; border-radius:12px; overflow:hidden;
            box-shadow:0 4px 16px rgba(0,0,0,.3);
            display:none; z-index:9994;
            border:2px solid #6366f1;
        }
        #et-video-wrap video { width:100%; display:block; transform:scaleX(-1); }
        #et-video-wrap canvas { display:none; }
        /* Overlay calibration */
        #et-cal-overlay {
            position:fixed; inset:0;
            background:rgba(10,10,30,.93);
            z-index:99990; display:none;
            font-family:'Segoe UI',sans-serif; color:#fff;
        }
        #et-cal-title {
            position:fixed; top:40px; left:50%; transform:translateX(-50%);
            text-align:center;
        }
        #et-cal-title h2 { font-size:22px; font-weight:700; margin:0 0 6px; }
        #et-cal-title p  { font-size:13px; color:#94a3b8; margin:0; }
        .et-pt {
            position:fixed; width:24px; height:24px; border-radius:50%;
            background:#6366f1; border:3px solid #fff;
            transform:translate(-50%,-50%); cursor:pointer;
            z-index:99991; transition:transform .2s;
            box-shadow:0 0 0 0 rgba(99,102,241,.7);
            animation:etPulse 1.4s infinite;
        }
        .et-pt:hover { transform:translate(-50%,-50%) scale(1.5); }
        .et-pt.done  { background:#22c55e; animation:none; }
        @keyframes etPulse {
            0%  { box-shadow:0 0 0 0 rgba(99,102,241,.7); }
            70% { box-shadow:0 0 0 14px rgba(99,102,241,0); }
            100%{ box-shadow:0 0 0 0 rgba(99,102,241,0); }
        }
        #et-prog-wrap {
            position:fixed; bottom:40px; left:50%; transform:translateX(-50%);
            width:260px; text-align:center;
        }
        #et-prog-bar-bg {
            background:rgba(255,255,255,.1); border-radius:20px;
            height:8px; overflow:hidden; margin-bottom:8px;
        }
        #et-prog-bar { height:100%; background:linear-gradient(90deg,#6366f1,#8b5cf6); border-radius:20px; width:0%; transition:width .4s; }
        #et-prog-txt { font-size:12px; color:#94a3b8; }
        /* Toast */
        #et-toast {
            position:fixed; top:20px; left:50%; transform:translateX(-50%);
            background:rgba(10,10,30,.92); color:#fff;
            padding:11px 22px; border-radius:30px;
            font-size:13px; font-weight:600; z-index:99993;
            display:none; align-items:center; gap:10px;
            box-shadow:0 4px 20px rgba(0,0,0,.3);
        }
        /* Slider vitesse */
        #et-speed {
            position:fixed; bottom:260px; right:16px;
            background:rgba(10,10,30,.88); color:#fff;
            padding:10px 14px; border-radius:14px;
            font-size:12px; z-index:9994; display:none;
            flex-direction:column; gap:6px; width:160px;
        }
        #et-speed input { accent-color:#6366f1; width:100%; }
        /* Effet clic */
        @keyframes etClick {
            0%  { transform:translate(-50%,-50%) scale(0); opacity:1; }
            100%{ transform:translate(-50%,-50%) scale(2.5); opacity:0; }
        }
        `;
        document.head.appendChild(s);
    }

    /* ── UI ──────────────────────────────────────────────────────────── */
    _createUI() {
        // Bouton
        const btn = document.createElement('button');
        btn.id = 'et-btn'; btn.innerHTML = '👁️';
        btn.title = 'Activer Eye Tracking';
        btn.onclick = () => this.active ? this._stop() : this._init();
        document.body.appendChild(btn);
        this.btn = btn;

        document.body.insertAdjacentHTML('beforeend', '<div id="et-lbl">Yeux</div>');

        // Curseur
        const cur = document.createElement('div');
        cur.id = 'et-cur';
        document.body.appendChild(cur);
        this.cur = cur;

        // Toast
        const toast = document.createElement('div');
        toast.id = 'et-toast';
        document.body.appendChild(toast);
        this.toast = toast;

        // Slider vitesse
        const spd = document.createElement('div');
        spd.id = 'et-speed';
        spd.innerHTML = `
            <div style="font-weight:700;font-size:11px;color:#94a3b8;text-transform:uppercase;">⚡ Vitesse</div>
            <input type="range" id="et-sl" min="5" max="100" value="15">
            <div style="display:flex;justify-content:space-between;font-size:10px;color:#64748b;">
                <span>Lent</span><span id="et-sl-v">15%</span><span>Rapide</span>
            </div>
        `;
        document.body.appendChild(spd);
        this.spdPanel = spd;
        setTimeout(() => {
            const sl = document.getElementById('et-sl');
            if (sl) sl.oninput = e => {
                this.SMOOTH = parseInt(e.target.value) / 100;
                document.getElementById('et-sl-v').textContent = e.target.value + '%';
            };
        }, 200);

        // Vidéo + canvas (cachés)
        const wrap = document.createElement('div');
        wrap.id = 'et-video-wrap';
        wrap.innerHTML = '<video id="et-video" autoplay playsinline muted></video><canvas id="et-canvas"></canvas>';
        document.body.appendChild(wrap);
        this.video  = document.getElementById('et-video');
        this.canvas = document.getElementById('et-canvas');
        this.ctx    = this.canvas.getContext('2d');
        this.videoWrap = wrap;
    }

    _toast(icon, msg, ms = 3000) {
        this.toast.innerHTML = `<span style="font-size:18px">${icon}</span>${msg}`;
        this.toast.style.display = 'flex';
        clearTimeout(this._tt);
        this._tt = setTimeout(() => { this.toast.style.display = 'none'; }, ms);
    }

    /* ── Initialisation MediaPipe ────────────────────────────────────── */
    _init() {
        this._toast('📷', 'Chargement de la détection faciale...', 4000);

        // Charger MediaPipe Face Mesh
        this._loadScript('https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js', () => {
            this._loadScript('https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js', () => {
                this._startCamera();
            });
        });
    }

    _loadScript(src, cb) {
        if (document.querySelector(`script[src="${src}"]`)) { cb(); return; }
        const s = document.createElement('script');
        s.src = src; s.crossOrigin = 'anonymous';
        s.onload = cb;
        s.onerror = () => this._toast('❌', 'Erreur chargement. Vérifiez votre connexion.', 5000);
        document.head.appendChild(s);
    }

    _startCamera() {
        navigator.mediaDevices.getUserMedia({ video: { width:640, height:480, facingMode:'user' } })
        .then(stream => {
            this.video.srcObject = stream;
            this.stream = stream;
            this.videoWrap.style.display = 'block';
            this._setupFaceMesh();
        })
        .catch(() => this._toast('❌', 'Caméra refusée. Autorisez la caméra dans Chrome.', 5000));
    }

    _setupFaceMesh() {
        const fm = new FaceMesh({
            locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${f}`
        });
        fm.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,   // landmarks précis pour les yeux
            minDetectionConfidence: 0.5,
            minTrackingConfidence:  0.5
        });
        fm.onResults(r => this._onResults(r));
        this.faceMesh = fm;

        const cam = new Camera(this.video, {
            onFrame: async () => { await fm.send({ image: this.video }); },
            width: 640, height: 480
        });
        cam.start();
        this.camera = cam;

        this._toast('✅', 'Caméra prête ! Démarrage de la calibration...', 3000);
        setTimeout(() => this._startCalibration(), 2500);
    }

    /* ── Landmarks MediaPipe ─────────────────────────────────────────── */
    // Indices des landmarks pour les yeux (MediaPipe Face Mesh 468 points)
    // Iris gauche : 468-472 | Iris droit : 473-477
    // Contour œil gauche : 33,7,163,144,145,153,154,155,133,173,157,158,159,160,161,246
    // Contour œil droit  : 362,382,381,380,374,373,390,249,263,466,388,387,386,385,384,398

    _getIrisCenter(lm, indices) {
        let x = 0, y = 0;
        indices.forEach(i => { x += lm[i].x; y += lm[i].y; });
        return { x: x / indices.length, y: y / indices.length };
    }

    _eyeAspectRatio(lm, top, bottom, left, right) {
        // EAR = hauteur / largeur de l'œil
        const h = Math.abs(lm[top].y - lm[bottom].y);
        const w = Math.abs(lm[left].x - lm[right].x);
        return w > 0 ? h / w : 0.3;
    }

    _onResults(results) {
        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) return;
        const lm = results.multiFaceLandmarks[0];

        // ── Position iris (regard) ──
        // Iris gauche : 468,469,470,471,472 | Iris droit : 473,474,475,476,477
        const leftIris  = this._getIrisCenter(lm, [468,469,470,471,472]);
        const rightIris = this._getIrisCenter(lm, [473,474,475,476,477]);
        const gazeNX = (leftIris.x + rightIris.x) / 2; // normalisé 0-1
        const gazeNY = (leftIris.y + rightIris.y) / 2;

        // ── Déplacer le curseur ──
        if (this.active && this.calibrated) {
            const screenX = this.mapX ? this.mapX(gazeNX) : gazeNX * window.innerWidth;
            const screenY = this.mapY ? this.mapY(gazeNY) : gazeNY * window.innerHeight;

            this.smoothX += (screenX - this.smoothX) * this.SMOOTH;
            this.smoothY += (screenY - this.smoothY) * this.SMOOTH;

            const cx = Math.max(0, Math.min(window.innerWidth,  this.smoothX));
            const cy = Math.max(0, Math.min(window.innerHeight, this.smoothY));
            this.cur.style.left = cx + 'px';
            this.cur.style.top  = cy + 'px';
        }

        // ── Calibration : enregistrer position iris ──
        if (this.waitingGaze) {
            this.currentGaze = { x: gazeNX, y: gazeNY };
        }

        // ── Détection clignement (EAR) ──
        if (this.active && this.calibrated) {
            // EAR œil gauche : points 159(top) 145(bottom) 33(left) 133(right)
            const earL = this._eyeAspectRatio(lm, 159, 145, 33, 133);
            // EAR œil droit  : points 386(top) 374(bottom) 362(left) 263(right)
            const earR = this._eyeAspectRatio(lm, 386, 374, 362, 263);
            const ear  = (earL + earR) / 2;

            if (ear < this.EAR_THRESH && !this.eyesClosed) {
                this.eyesClosed = true;
                this.blinkStart = Date.now();
                this.cur.classList.add('closed');
            } else if (ear >= this.EAR_THRESH && this.eyesClosed) {
                this.eyesClosed = false;
                this.cur.classList.remove('closed');
                const dur = Date.now() - this.blinkStart;
                this._handleBlink(dur);
            }
        }
    }

    /* ── Calibration ─────────────────────────────────────────────────── */
    _startCalibration() {
        const W = window.innerWidth, H = window.innerHeight;
        // 9 points grille 3x3
        this.calPts = [
            {sx: W*.1, sy: H*.1}, {sx: W*.5, sy: H*.1}, {sx: W*.9, sy: H*.1},
            {sx: W*.1, sy: H*.5}, {sx: W*.5, sy: H*.5}, {sx: W*.9, sy: H*.5},
            {sx: W*.1, sy: H*.9}, {sx: W*.5, sy: H*.9}, {sx: W*.9, sy: H*.9},
        ];
        this.calData  = [];
        this.calStep  = 0;

        // Overlay
        const ov = document.createElement('div');
        ov.id = 'et-cal-overlay';
        ov.style.display = 'block';
        ov.innerHTML = `
            <div id="et-cal-title">
                <h2>👁️ Calibration</h2>
                <p>Regardez chaque point violet et cliquez dessus.<br>Gardez la tête immobile.</p>
            </div>
            <div id="et-prog-wrap">
                <div id="et-prog-bar-bg"><div id="et-prog-bar"></div></div>
                <div id="et-prog-txt">0 / 9 points</div>
            </div>
        `;
        document.body.appendChild(ov);
        this.calOverlay = ov;

        this._showCalPoint(0);
    }

    _showCalPoint(idx) {
        // Supprimer l'ancien point
        document.querySelectorAll('.et-pt').forEach(p => p.remove());

        if (idx >= this.calPts.length) {
            this._finishCalibration();
            return;
        }

        const pt = this.calPts[idx];
        const el = document.createElement('div');
        el.className = 'et-pt';
        el.style.left = pt.sx + 'px';
        el.style.top  = pt.sy + 'px';
        document.body.appendChild(el);

        // Attendre que l'utilisateur regarde et clique
        this.waitingGaze = true;
        el.onclick = () => {
            if (!this.currentGaze) return;
            el.classList.add('done');
            this.calData.push({
                sx: pt.sx / window.innerWidth,
                sy: pt.sy / window.innerHeight,
                gx: this.currentGaze.x,
                gy: this.currentGaze.y
            });
            this.waitingGaze = false;

            // Progression
            const pct = ((idx + 1) / this.calPts.length * 100).toFixed(0);
            document.getElementById('et-prog-bar').style.width = pct + '%';
            document.getElementById('et-prog-txt').textContent = (idx+1) + ' / ' + this.calPts.length + ' points';

            setTimeout(() => this._showCalPoint(idx + 1), 400);
        };
    }

    _finishCalibration() {
        // Supprimer overlay
        document.querySelectorAll('.et-pt').forEach(p => p.remove());
        if (this.calOverlay) this.calOverlay.remove();

        // Calculer la régression linéaire gaze → screen
        this.mapX = this._linearRegression(this.calData.map(d => d.gx), this.calData.map(d => d.sx));
        this.mapY = this._linearRegression(this.calData.map(d => d.gy), this.calData.map(d => d.sy));

        this.calibrated = true;
        this.active     = true;
        this.btn.classList.add('on');
        this.btn.innerHTML = '🔴';
        this.cur.style.display = 'block';
        this.spdPanel.style.display = 'flex';

        this._toast('✅', 'Calibration terminée ! Bougez les yeux pour déplacer le curseur.', 4000);
    }

    // Régression linéaire simple : y = a*x + b
    _linearRegression(xs, ys) {
        const n  = xs.length;
        const sx = xs.reduce((a,b) => a+b, 0);
        const sy = ys.reduce((a,b) => a+b, 0);
        const sxy = xs.reduce((s,x,i) => s + x*ys[i], 0);
        const sxx = xs.reduce((s,x) => s + x*x, 0);
        const a = (n*sxy - sx*sy) / (n*sxx - sx*sx);
        const b = (sy - a*sx) / n;
        // Retourner une fonction qui mappe gaze normalisé → coordonnée écran normalisée
        return gx => Math.max(0, Math.min(1, a*gx + b)) * (xs === this.calData.map(d=>d.gx) ? window.innerWidth : window.innerHeight);
    }

    /* ── Clignement ──────────────────────────────────────────────────── */
    _handleBlink(dur) {
        if (dur < 60) return; // ignorer micro-clignements

        if (dur > 800) {
            // Long = double clic
            this._click('dblclick');
            this._toast('👁️', 'Double clic', 800);
        } else {
            this.blinkCount++;
            clearTimeout(this.blinkTimer);
            this.blinkTimer = setTimeout(() => {
                if (this.blinkCount >= 2) {
                    this._click('contextmenu');
                    this._toast('👁️', 'Clic droit', 800);
                } else {
                    this._click('click');
                    this._toast('👁️', 'Clic', 600);
                }
                this.blinkCount = 0;
            }, 350);
        }
    }

    _click(type) {
        const x = this.smoothX, y = this.smoothY;
        const el = document.elementFromPoint(x, y);
        if (!el) return;
        el.dispatchEvent(new MouseEvent(type, { bubbles:true, cancelable:true, clientX:x, clientY:y, view:window }));
        // Effet visuel
        const ring = document.createElement('div');
        ring.style.cssText = `position:fixed;left:${x}px;top:${y}px;width:28px;height:28px;border-radius:50%;border:3px solid #6366f1;pointer-events:none;transform:translate(-50%,-50%) scale(0);z-index:99999;animation:etClick .4s ease forwards;`;
        document.body.appendChild(ring);
        setTimeout(() => ring.remove(), 420);
    }

    /* ── Arrêt ───────────────────────────────────────────────────────── */
    _stop() {
        this.active = this.calibrated = false;
        this.btn.classList.remove('on');
        this.btn.innerHTML = '👁️';
        this.cur.style.display = 'none';
        this.spdPanel.style.display = 'none';
        this.videoWrap.style.display = 'none';
        if (this.stream) this.stream.getTracks().forEach(t => t.stop());
        if (this.camera) try { this.camera.stop(); } catch(_) {}
        this._toast('⏹️', 'Eye tracking désactivé', 2000);
    }
}

/* ── Démarrage ───────────────────────────────────────────────────────── */
(function() {
    const go = () => { if (!window.eyeTracker) window.eyeTracker = new EyeTracker(); };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', go) : go();
})();
