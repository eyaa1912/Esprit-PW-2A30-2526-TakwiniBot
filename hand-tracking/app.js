/* ============================================
   Hand Tracking App - MediaPipe Hands
   Main ouverte = déplacer curseur
   Main fermée = clic
   ============================================ */

// ---- DOM Elements ----
const videoEl = document.getElementById('webcam');
const canvasEl = document.getElementById('handCanvas');
const ctx = canvasEl.getContext('2d');
const startBtn = document.getElementById('startBtn');
const statusDot = document.getElementById('statusDot');
const statusText = document.getElementById('statusText');
const loadingOverlay = document.getElementById('loadingOverlay');
const loadingBar = document.getElementById('loadingBar');
const virtualCursor = document.getElementById('virtualCursor');
const interactiveZone = document.getElementById('interactiveZone');
const clickRipple = document.getElementById('clickRipple');
const cameraLabel = document.querySelector('.camera-label');

// Stats
const handStateEl = document.getElementById('handState');
const clickCountEl = document.getElementById('clickCount');
const posXEl = document.getElementById('posX');
const posYEl = document.getElementById('posY');

// Indicators
const openIndicator = document.getElementById('openIndicator');
const closedIndicator = document.getElementById('closedIndicator');

// ---- State ----
let isRunning = false;
let clickCount = 0;
let lastClickTime = 0;
let isCurrentlyClicking = false;
let handWasOpen = true;

// Smoothing for cursor position
let smoothX = 0.5;
let smoothY = 0.5;
const SMOOTHING = 0.35; // Lower = smoother but more laggy

// ---- MediaPipe Hands Setup ----
const hands = new Hands({
    locateFile: (file) => {
        return `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`;
    }
});

hands.setOptions({
    maxNumHands: 1,            // Only detect one hand
    modelComplexity: 1,        // 0=lite, 1=full
    minDetectionConfidence: 0.7,
    minTrackingConfidence: 0.6
});

// Loading progress simulation
let loadProgress = 0;
const loadInterval = setInterval(() => {
    loadProgress = Math.min(loadProgress + Math.random() * 15, 90);
    loadingBar.style.width = loadProgress + '%';
}, 300);

hands.onResults(onResults);

// Mark loading complete
hands.initialize().then(() => {
    clearInterval(loadInterval);
    loadingBar.style.width = '100%';
    setTimeout(() => {
        loadingOverlay.classList.add('hidden');
    }, 500);
    statusText.textContent = 'Modèle prêt';
    statusDot.classList.add('active');
});

// ---- Camera Start ----
startBtn.addEventListener('click', async () => {
    if (isRunning) return;

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        });

        videoEl.srcObject = stream;
        await videoEl.play();

        // Set canvas size to match video
        canvasEl.width = videoEl.videoWidth;
        canvasEl.height = videoEl.videoHeight;

        isRunning = true;
        startBtn.classList.add('running');
        startBtn.innerHTML = '<span class="btn-icon">✅</span> Caméra Active';
        cameraLabel.classList.add('visible');
        statusText.textContent = 'Détection active';
        statusDot.className = 'status-indicator detecting';

        // Start detection loop
        detectFrame();

    } catch (err) {
        console.error('Erreur caméra:', err);
        statusText.textContent = 'Erreur caméra!';
        alert('Impossible d\'accéder à la caméra. Vérifiez les permissions.');
    }
});

// ---- Frame Detection Loop ----
async function detectFrame() {
    if (!isRunning) return;

    await hands.send({ image: videoEl });
    requestAnimationFrame(detectFrame);
}

// ---- Process Results ----
function onResults(results) {
    // Clear canvas
    ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);

    if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
        const landmarks = results.multiHandLandmarks[0];

        // Draw hand skeleton
        drawHand(landmarks);

        // Get index finger tip position (landmark 8) for cursor
        const indexTip = landmarks[8];

        // Smooth the cursor position
        smoothX = smoothX + (indexTip.x - smoothX) * SMOOTHING;
        smoothY = smoothY + (indexTip.y - smoothY) * SMOOTHING;

        // Move virtual cursor in the interactive zone
        moveCursor(smoothX, smoothY);

        // Detect open/closed hand
        const handOpen = isHandOpen(landmarks);
        updateHandState(handOpen);

        // Show interactive zone active state
        interactiveZone.classList.add('hand-detected');
        virtualCursor.classList.add('visible');

        // Update stats
        posXEl.textContent = Math.round(smoothX * 100);
        posYEl.textContent = Math.round(smoothY * 100);

    } else {
        // No hand detected
        interactiveZone.classList.remove('hand-detected');
        virtualCursor.classList.remove('visible');
        handStateEl.textContent = '—';
        posXEl.textContent = '—';
        posYEl.textContent = '—';
        openIndicator.classList.remove('active');
        closedIndicator.classList.remove('active');
    }
}

// ---- Draw Hand Landmarks ----
function drawHand(landmarks) {
    // Draw connections
    const connections = [
        [0, 1], [1, 2], [2, 3], [3, 4],       // Thumb
        [0, 5], [5, 6], [6, 7], [7, 8],       // Index
        [0, 9], [9, 10], [10, 11], [11, 12],  // Middle
        [0, 13], [13, 14], [14, 15], [15, 16],// Ring
        [0, 17], [17, 18], [18, 19], [19, 20],// Pinky
        [5, 9], [9, 13], [13, 17]             // Palm
    ];

    ctx.strokeStyle = 'rgba(108, 92, 231, 0.7)';
    ctx.lineWidth = 2;

    connections.forEach(([a, b]) => {
        const pA = landmarks[a];
        const pB = landmarks[b];
        ctx.beginPath();
        ctx.moveTo(pA.x * canvasEl.width, pA.y * canvasEl.height);
        ctx.lineTo(pB.x * canvasEl.width, pB.y * canvasEl.height);
        ctx.stroke();
    });

    // Draw landmarks
    landmarks.forEach((lm, i) => {
        const x = lm.x * canvasEl.width;
        const y = lm.y * canvasEl.height;

        // Fingertips get special treatment
        const isFingertip = [4, 8, 12, 16, 20].includes(i);
        const radius = isFingertip ? 6 : 4;

        ctx.beginPath();
        ctx.arc(x, y, radius, 0, 2 * Math.PI);

        if (isFingertip) {
            ctx.fillStyle = '#6c5ce7';
            ctx.shadowColor = 'rgba(108, 92, 231, 0.6)';
            ctx.shadowBlur = 10;
        } else if (i === 0) {
            ctx.fillStyle = '#00b894';
            ctx.shadowColor = 'rgba(0, 184, 148, 0.6)';
            ctx.shadowBlur = 10;
        } else {
            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.shadowBlur = 0;
        }

        ctx.fill();
        ctx.shadowBlur = 0;
    });
}

// ---- Detect Open/Closed Hand ----
function isHandOpen(landmarks) {
    // Strategy: Check if each finger is extended
    // A finger is extended if its tip is further from the wrist than its MCP joint

    const wrist = landmarks[0];

    // For each finger, compare tip distance to MCP distance from wrist
    // Index: tip=8, mcp=5
    // Middle: tip=12, mcp=9
    // Ring: tip=16, mcp=13
    // Pinky: tip=20, mcp=17

    const fingers = [
        { tip: 8, pip: 6 },   // Index
        { tip: 12, pip: 10 }, // Middle
        { tip: 16, pip: 14 }, // Ring
        { tip: 20, pip: 18 }, // Pinky
    ];

    let extendedCount = 0;

    fingers.forEach(({ tip, pip }) => {
        const tipLm = landmarks[tip];
        const pipLm = landmarks[pip];

        // A finger is extended if the tip is further from the palm center
        // than the PIP joint (2nd knuckle)
        // Using Y coordinate: tip.y < pip.y means finger is pointing up (extended)
        if (tipLm.y < pipLm.y) {
            extendedCount++;
        }
    });

    // Also check thumb: tip=4, ip=3
    const thumbTip = landmarks[4];
    const thumbIp = landmarks[3];
    const thumbMcp = landmarks[2];

    // Thumb is extended if tip is further from palm than IP joint
    const thumbExtended = distance2D(thumbTip, thumbMcp) > distance2D(thumbIp, thumbMcp) * 1.2;
    if (thumbExtended) extendedCount++;

    // Hand is open if at least 3 fingers are extended
    return extendedCount >= 3;
}

function distance2D(a, b) {
    return Math.sqrt((a.x - b.x) ** 2 + (a.y - b.y) ** 2);
}

// ---- Update Hand State ----
function updateHandState(isOpen) {
    if (isOpen) {
        // Hand is open - move cursor
        handStateEl.textContent = '✋ Ouverte';
        handStateEl.style.color = '#00b894';
        virtualCursor.classList.remove('clicking');
        openIndicator.classList.add('active');
        closedIndicator.classList.remove('active');

        // Reset click state when hand opens
        if (!handWasOpen) {
            isCurrentlyClicking = false;
        }
        handWasOpen = true;

    } else {
        // Hand is closed - CLICK
        handStateEl.textContent = '✊ Fermée';
        handStateEl.style.color = '#e17055';
        virtualCursor.classList.add('clicking');
        openIndicator.classList.remove('active');
        closedIndicator.classList.add('active');

        // Trigger click only on transition from open to closed
        if (handWasOpen && !isCurrentlyClicking) {
            triggerClick();
            isCurrentlyClicking = true;
        }
        handWasOpen = false;
    }
}

// ---- Move Cursor ----
function moveCursor(normX, normY) {
    const zone = interactiveZone.getBoundingClientRect();

    // Map normalized coordinates to zone
    // Note: normX is mirrored because camera is flipped
    const x = (1 - normX) * 100;
    const y = normY * 100;

    virtualCursor.style.left = x + '%';
    virtualCursor.style.top = y + '%';

    // Check hover on targets
    checkTargetHover(x, y);
}

// ---- Check Target Hover ----
function checkTargetHover(cursorXPercent, cursorYPercent) {
    const targets = document.querySelectorAll('.target');

    targets.forEach(target => {
        const tLeft = parseFloat(target.style.left);
        const tTop = parseFloat(target.style.top);

        // Calculate distance in percentage space
        const dist = Math.sqrt((cursorXPercent - tLeft) ** 2 + (cursorYPercent - tTop) ** 2);

        if (dist < 12) {
            target.classList.add('hovered');
        } else {
            target.classList.remove('hovered');
        }
    });
}

// ---- Trigger Click ----
function triggerClick() {
    const now = Date.now();
    if (now - lastClickTime < 500) return; // Debounce
    lastClickTime = now;

    clickCount++;
    clickCountEl.textContent = clickCount;

    // Show ripple at cursor position
    const cursorLeft = virtualCursor.style.left;
    const cursorTop = virtualCursor.style.top;

    clickRipple.style.left = cursorLeft;
    clickRipple.style.top = cursorTop;
    clickRipple.classList.remove('active');
    void clickRipple.offsetWidth; // Force reflow
    clickRipple.classList.add('active');

    // Check if clicking on a target
    const targets = document.querySelectorAll('.target');
    targets.forEach(target => {
        if (target.classList.contains('hovered')) {
            target.classList.add('clicked');
            setTimeout(() => target.classList.remove('clicked'), 500);

            // Visual feedback
            const color = target.dataset.color;
            interactiveZone.style.boxShadow = `0 0 40px ${color}44, inset 0 0 40px ${color}11`;
            setTimeout(() => {
                interactiveZone.style.boxShadow = '';
            }, 400);
        }
    });

    // Sound feedback (short beep)
    playClickSound();
}

// ---- Click Sound ----
function playClickSound() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.1);

        oscillator.start(audioCtx.currentTime);
        oscillator.stop(audioCtx.currentTime + 0.1);
    } catch (e) {
        // Audio not supported, skip
    }
}
