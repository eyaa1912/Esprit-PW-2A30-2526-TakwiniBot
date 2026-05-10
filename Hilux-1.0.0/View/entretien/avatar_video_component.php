<?php
/**
 * Takwini Avatar Video Component
 * Human-like animated avatar for video call interviews
 */
?>

<svg class="takwini-avatar-video" viewBox="0 0 400 500" xmlns="http://www.w3.org/2000/svg">
    <!-- Background -->
    <rect width="400" height="500" fill="#f5f7fa" rx="20"/>

    <!-- Head -->
    <ellipse cx="200" cy="120" rx="80" ry="90" fill="#f4a460" stroke="#d4845a" stroke-width="2"/>

    <!-- Hair -->
    <path d="M 120 80 Q 120 30 200 20 Q 280 30 280 80" fill="#8b6f47" stroke="#6b5437" stroke-width="2"/>

    <!-- Left Eye -->
    <g class="takwini-eye takwini-eye-left">
        <ellipse cx="160" cy="110" rx="15" ry="20" fill="white" stroke="#333" stroke-width="1.5"/>
        <circle class="takwini-pupil" cx="160" cy="120" r="8" fill="#333"/>
        <circle class="takwini-shine" cx="163" cy="117" r="3" fill="white"/>
    </g>

    <!-- Right Eye -->
    <g class="takwini-eye takwini-eye-right">
        <ellipse cx="240" cy="110" rx="15" ry="20" fill="white" stroke="#333" stroke-width="1.5"/>
        <circle class="takwini-pupil" cx="240" cy="120" r="8" fill="#333"/>
        <circle class="takwini-shine" cx="243" cy="117" r="3" fill="white"/>
    </g>

    <!-- Eyebrows -->
    <path class="takwini-eyebrow takwini-eyebrow-left" d="M 145 90 Q 160 80 175 90" stroke="#6b5437" stroke-width="3" fill="none" stroke-linecap="round"/>
    <path class="takwini-eyebrow takwini-eyebrow-right" d="M 225 90 Q 240 80 255 90" stroke="#6b5437" stroke-width="3" fill="none" stroke-linecap="round"/>

    <!-- Nose -->
    <path d="M 200 120 L 200 160" stroke="#d4845a" stroke-width="3" fill="none" stroke-linecap="round"/>

    <!-- Mouth -->
    <g class="takwini-mouth">
        <path class="takwini-mouth-line" d="M 170 180 Q 200 210 230 180" stroke="#c85a54" stroke-width="4" fill="none" stroke-linecap="round"/>
        <ellipse class="takwini-mouth-fill" cx="200" cy="190" rx="35" ry="15" fill="#f0a0a0" opacity="0.5"/>
    </g>

    <!-- Blush -->
    <ellipse class="takwini-blush takwini-blush-left" cx="120" cy="140" rx="15" ry="12" fill="#ffb6c1" opacity="0.6"/>
    <ellipse class="takwini-blush takwini-blush-right" cx="280" cy="140" rx="15" ry="12" fill="#ffb6c1" opacity="0.6"/>

    <!-- Neck -->
    <rect x="170" y="200" width="60" height="30" fill="#f4a460" stroke="#d4845a" stroke-width="1.5"/>

    <!-- Shoulders -->
    <ellipse cx="200" cy="260" rx="100" ry="50" fill="#4a90e2" stroke="#2e5c8a" stroke-width="2"/>

    <!-- Arms -->
    <g class="takwini-arm takwini-arm-left">
        <line x1="110" y1="260" x2="50" y2="350" stroke="#f4a460" stroke-width="12" stroke-linecap="round"/>
        <circle cx="50" cy="350" r="10" fill="#f4a460"/>
    </g>
    <g class="takwini-arm takwini-arm-right">
        <line x1="290" y1="260" x2="350" y2="350" stroke="#f4a460" stroke-width="12" stroke-linecap="round"/>
        <circle cx="350" cy="350" r="10" fill="#f4a460"/>
    </g>

    <!-- Shirt -->
    <path d="M 120 250 L 100 400 L 300 400 L 280 250 Z" fill="#4a90e2" stroke="#2e5c8a" stroke-width="2"/>

    <!-- Sound Waves (for speaking) -->
    <g class="takwini-sound-waves" style="display: none;">
        <circle cx="200" cy="120" r="120" fill="none" stroke="#667eea" stroke-width="2" opacity="0.3"/>
        <circle cx="200" cy="120" r="150" fill="none" stroke="#667eea" stroke-width="2" opacity="0.2"/>
        <circle cx="200" cy="120" r="180" fill="none" stroke="#667eea" stroke-width="2" opacity="0.1"/>
    </g>
</svg>

<style>
.takwini-avatar-video {
    width: 100%;
    height: 100%;
    max-width: 400px;
    max-height: 500px;
    filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.2));
}

/* Eye Animations */
.takwini-eye {
    animation: blink-video 3s infinite;
}

@keyframes blink-video {
    0%, 49%, 100% { transform: scaleY(1); }
    50%, 51% { transform: scaleY(0.1); }
}

.takwini-eye-left { transform-origin: 160px 110px; }
.takwini-eye-right { transform-origin: 240px 110px; }

.takwini-pupil {
    animation: look-around-video 4s infinite;
}

@keyframes look-around-video {
    0%, 100% { cx: 160; cy: 120; }
    25% { cx: 168; cy: 115; }
    50% { cx: 160; cy: 128; }
    75% { cx: 152; cy: 120; }
}

.takwini-eye-right .takwini-pupil {
    animation: look-around-right-video 4s infinite;
}

@keyframes look-around-right-video {
    0%, 100% { cx: 240; cy: 120; }
    25% { cx: 248; cy: 115; }
    50% { cx: 240; cy: 128; }
    75% { cx: 232; cy: 120; }
}

/* Eyebrow Animations */
.takwini-eyebrow {
    animation: expression-neutral-video 3s infinite;
}

@keyframes expression-neutral-video {
    0%, 100% { d: path('M 145 90 Q 160 80 175 90'); }
    50% { d: path('M 145 85 Q 160 75 175 85'); }
}

.takwini-eyebrow-right {
    animation: expression-neutral-right-video 3s infinite;
}

@keyframes expression-neutral-right-video {
    0%, 100% { d: path('M 225 90 Q 240 80 255 90'); }
    50% { d: path('M 225 85 Q 240 75 255 85'); }
}

/* Mouth Animations */
.takwini-mouth {
    animation: talk-video 2s infinite;
    transform-origin: 200px 190px;
}

@keyframes talk-video {
    0%, 100% { transform: scaleY(1); }
    25% { transform: scaleY(1.3); }
    50% { transform: scaleY(0.8); }
    75% { transform: scaleY(1.2); }
}

/* Blush Animation */
.takwini-blush {
    animation: blush-pulse-video 2.5s infinite;
}

@keyframes blush-pulse-video {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 0.8; }
}

/* Arm Animations */
.takwini-arm {
    animation: arm-rest-video 3s infinite;
    transform-origin: 200px 260px;
}

.takwini-arm-left {
    animation: arm-rest-left-video 3s infinite;
}

.takwini-arm-right {
    animation: arm-rest-right-video 3s infinite;
}

@keyframes arm-rest-left-video {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(-20deg); }
}

@keyframes arm-rest-right-video {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(20deg); }
}

/* Hand Sign Animations */
.takwini-arm.sign-hello {
    animation: sign-hello-video 1.5s ease-in-out;
}

@keyframes sign-hello-video {
    0% { transform: rotate(0deg); }
    50% { transform: rotate(45deg); }
    100% { transform: rotate(0deg); }
}

.takwini-arm.sign-yes {
    animation: sign-yes-video 1s ease-in-out;
}

@keyframes sign-yes-video {
    0%, 100% { transform: rotateX(0deg); }
    50% { transform: rotateX(30deg); }
}

.takwini-arm.sign-no {
    animation: sign-no-video 1s ease-in-out;
}

@keyframes sign-no-video {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-30deg); }
    75% { transform: rotate(30deg); }
}

.takwini-arm.sign-celebrate {
    animation: sign-celebrate-video 1.5s ease-in-out;
}

@keyframes sign-celebrate-video {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-45deg); }
    50% { transform: rotate(45deg); }
    75% { transform: rotate(-45deg); }
}

.takwini-arm.sign-thumbsup {
    animation: sign-thumbsup-video 1s ease-in-out;
}

@keyframes sign-thumbsup-video {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(-90deg); }
}

/* Sound Waves Animation */
.takwini-sound-waves {
    animation: sound-waves-video 1.5s infinite;
}

@keyframes sound-waves-video {
    0% {
        opacity: 1;
    }
    100% {
        opacity: 0;
        r: 220px;
    }
}

/* Celebration Animation */
.takwini-avatar-video.celebrating {
    animation: celebrate-video 0.8s ease-in-out;
}

@keyframes celebrate-video {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    25% { transform: translateY(-15px) rotate(-5deg); }
    50% { transform: translateY(-30px) rotate(5deg); }
    75% { transform: translateY(-15px) rotate(-5deg); }
}

/* Nod Animation */
.takwini-avatar-video.nodding {
    animation: nod-video 0.6s ease-in-out;
}

@keyframes nod-video {
    0%, 100% { transform: rotateX(0deg); }
    25% { transform: rotateX(15deg); }
    75% { transform: rotateX(-15deg); }
}

/* Smile Animation */
.takwini-mouth.smiling {
    animation: smile-video 0.5s ease-in-out forwards;
}

@keyframes smile-video {
    0% { transform: scaleY(1); }
    100% { transform: scaleY(1.4); }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    .takwini-eye,
    .takwini-pupil,
    .takwini-eyebrow,
    .takwini-mouth,
    .takwini-blush,
    .takwini-arm,
    .takwini-avatar-video.celebrating,
    .takwini-avatar-video.nodding,
    .takwini-mouth.smiling,
    .takwini-sound-waves {
        animation: none !important;
    }
}
</style>
