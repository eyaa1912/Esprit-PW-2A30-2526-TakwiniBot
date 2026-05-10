<!-- 3D Avatar Assistant Component -->
<div id="avatar-assistant" class="avatar-assistant">
    <div class="avatar-container">
        <!-- Animated SVG Avatar -->
        <svg class="avatar-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle class="avatar-head" cx="100" cy="80" r="40" fill="#6f42c1"/>
            
            <!-- Eyes -->
            <g class="avatar-eyes">
                <ellipse class="avatar-eye-left" cx="90" cy="75" rx="5" ry="8" fill="#fff"/>
                <ellipse class="avatar-eye-right" cx="110" cy="75" rx="5" ry="8" fill="#fff"/>
                <circle class="avatar-pupil-left" cx="90" cy="77" r="3" fill="#333"/>
                <circle class="avatar-pupil-right" cx="110" cy="77" r="3" fill="#333"/>
            </g>
            
            <!-- Mouth -->
            <path class="avatar-mouth" d="M 85 90 Q 100 95 115 90" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/>
            
            <!-- Body -->
            <ellipse class="avatar-body" cx="100" cy="150" rx="45" ry="50" fill="#30b5e1"/>
            
            <!-- Arms -->
            <g class="avatar-arms">
                <ellipse class="avatar-arm-left" cx="60" cy="140" rx="12" ry="35" fill="#30b5e1" transform="rotate(-20 60 140)"/>
                <ellipse class="avatar-arm-right" cx="140" cy="140" rx="12" ry="35" fill="#30b5e1" transform="rotate(20 140 140)"/>
            </g>
            
            <!-- Sound waves (when speaking) -->
            <g class="avatar-sound-waves" opacity="0">
                <path class="wave wave-1" d="M 140 80 Q 150 80 155 75" stroke="#6f42c1" stroke-width="2" fill="none"/>
                <path class="wave wave-2" d="M 145 80 Q 160 80 170 75" stroke="#6f42c1" stroke-width="2" fill="none"/>
                <path class="wave wave-3" d="M 150 80 Q 170 80 185 75" stroke="#6f42c1" stroke-width="2" fill="none"/>
            </g>
        </svg>
        
        <!-- Avatar Status -->
        <div class="avatar-status">
            <span class="avatar-status-text">Assistant prêt</span>
            <div class="avatar-status-indicator"></div>
        </div>
    </div>
    
    <!-- Control Panel -->
    <div class="avatar-controls">
        <button type="button" id="avatar-toggle" class="avatar-btn avatar-btn-primary" title="Activer/Désactiver l'assistant vocal">
            <i class="fa fa-microphone"></i>
            <span>Activer l'assistant</span>
        </button>
        
        <button type="button" id="avatar-help" class="avatar-btn avatar-btn-secondary" title="Aide">
            <i class="fa fa-question-circle"></i>
        </button>
        
        <button type="button" id="avatar-settings" class="avatar-btn avatar-btn-secondary" title="Paramètres">
            <i class="fa fa-cog"></i>
        </button>
    </div>
    
    <!-- Voice Recognition Feedback -->
    <div id="avatar-feedback" class="avatar-feedback" style="display: none;">
        <div class="avatar-feedback-content">
            <div class="avatar-listening-animation">
                <span class="listening-dot"></span>
                <span class="listening-dot"></span>
                <span class="listening-dot"></span>
            </div>
            <p class="avatar-feedback-text">Je vous écoute...</p>
            <p class="avatar-transcript"></p>
        </div>
    </div>
</div>

<style>
/* Avatar Assistant Styles */
.avatar-assistant {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 20px;
    max-width: 300px;
    transition: all 0.3s ease;
}

.avatar-assistant.minimized {
    max-width: 80px;
    padding: 10px;
}

.avatar-container {
    text-align: center;
    margin-bottom: 15px;
}

.avatar-svg {
    width: 150px;
    height: 150px;
    transition: transform 0.3s ease;
}

.avatar-assistant.minimized .avatar-svg {
    width: 60px;
    height: 60px;
}

.avatar-svg:hover {
    transform: scale(1.05);
}

/* Avatar Animations */
@keyframes blink {
    0%, 90%, 100% { transform: scaleY(1); }
    95% { transform: scaleY(0.1); }
}

.avatar-eyes {
    animation: blink 4s infinite;
}

@keyframes wave {
    0%, 100% { opacity: 0; transform: translateX(0); }
    50% { opacity: 1; transform: translateX(5px); }
}

.avatar-sound-waves.active {
    opacity: 1 !important;
}

.wave-1 {
    animation: wave 1s infinite;
}

.wave-2 {
    animation: wave 1s infinite 0.2s;
}

.wave-3 {
    animation: wave 1s infinite 0.4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.avatar-svg {
    animation: float 3s ease-in-out infinite;
}

/* Avatar Status */
.avatar-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    font-size: 12px;
    color: #666;
}

.avatar-status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #28a745;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.avatar-status-indicator.listening {
    background: #ff6b6b;
}

.avatar-status-indicator.speaking {
    background: #6f42c1;
}

/* Controls */
.avatar-controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.avatar-btn {
    flex: 1;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.avatar-btn-primary {
    background: linear-gradient(135deg, #6f42c1 0%, #30b5e1 100%);
    color: white;
    flex-basis: 100%;
}

.avatar-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(111, 66, 193, 0.4);
}

.avatar-btn-primary.active {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
}

.avatar-btn-secondary {
    background: #f0f0f0;
    color: #666;
    flex-basis: calc(50% - 5px);
}

.avatar-btn-secondary:hover {
    background: #e0e0e0;
}

.avatar-assistant.minimized .avatar-controls {
    display: none;
}

/* Feedback */
.avatar-feedback {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    min-width: 300px;
    text-align: center;
}

.avatar-listening-animation {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 15px;
}

.listening-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6f42c1;
    animation: bounce 1.4s infinite ease-in-out both;
}

.listening-dot:nth-child(1) {
    animation-delay: -0.32s;
}

.listening-dot:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

.avatar-feedback-text {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.avatar-transcript {
    font-size: 14px;
    color: #666;
    font-style: italic;
    min-height: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .avatar-assistant {
        bottom: 10px;
        right: 10px;
        max-width: 250px;
        padding: 15px;
    }
    
    .avatar-svg {
        width: 100px;
        height: 100px;
    }
}

/* Accessibility Enhancements */
.avatar-assistant.high-contrast .avatar-head {
    fill: #000;
}

.avatar-assistant.high-contrast .avatar-body,
.avatar-assistant.high-contrast .avatar-arms ellipse {
    fill: #000;
}

.avatar-assistant.large-text .avatar-status-text,
.avatar-assistant.large-text .avatar-btn {
    font-size: 16px;
}

/* Simplified Mode (Cognitive Impairment) */
.avatar-assistant.simplified-mode {
    transform: scale(1.2);
}

.avatar-assistant.simplified-mode .avatar-btn {
    font-size: 20px !important;
    padding: 18px 35px !important;
    font-weight: bold;
}

.avatar-assistant.simplified-mode .avatar-status-text {
    font-size: 18px !important;
    font-weight: bold;
}

/* Visual Mode (Hearing Impairment) */
.avatar-assistant.visual-mode .avatar-svg {
    animation: bounce 1s infinite, glow 2s infinite;
}

@keyframes glow {
    0%, 100% {
        filter: drop-shadow(0 0 5px #30b5e1);
    }
    50% {
        filter: drop-shadow(0 0 20px #6f42c1);
    }
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-15px);
    }
}

/* High Contrast Mode (Visual Impairment) */
.avatar-assistant.high-contrast {
    border: 3px solid #000;
    background: #fff;
}

.avatar-assistant.high-contrast .avatar-btn {
    border: 2px solid #000 !important;
    font-weight: bold;
}

.avatar-assistant.high-contrast .avatar-status-text {
    color: #000;
    font-weight: bold;
    font-size: 16px;
}

/* Custom Notification System */
.avatar-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10001;
    max-width: 500px;
    min-width: 300px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
}

.avatar-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.avatar-notification-content {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    gap: 15px;
}

.avatar-notification-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.avatar-notification-message {
    flex: 1;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

.avatar-notification-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s;
    flex-shrink: 0;
}

.avatar-notification-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #333;
}

.avatar-notification-info {
    border-left: 4px solid #30b5e1;
}

.avatar-notification-info .avatar-notification-icon {
    color: #30b5e1;
}

.avatar-notification-success {
    border-left: 4px solid #28a745;
}

.avatar-notification-success .avatar-notification-icon {
    color: #28a745;
}

.avatar-notification-error {
    border-left: 4px solid #dc3545;
}

.avatar-notification-error .avatar-notification-icon {
    color: #dc3545;
}

@media (max-width: 768px) {
    .avatar-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
        min-width: 0;
    }
}
</style>
