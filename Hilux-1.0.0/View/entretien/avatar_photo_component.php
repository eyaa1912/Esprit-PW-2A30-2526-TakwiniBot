<?php
/**
 * Photo Avatar Component
 * Displays a real photo as the avatar with continuous animations
 */
?>

<style>
    .avatar-photo-container {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        overflow: hidden;
        position: relative;
        perspective: 1000px;
    }

    .avatar-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        animation: avatarPulse 0.3s ease-in-out;
        transition: all 0.3s ease;
    }

    /* Continuous breathing animation */
    .avatar-photo.breathing {
        animation: avatarBreathing 3s ease-in-out infinite;
    }

    /* Speaking animation - continuous */
    .avatar-photo.speaking {
        animation: avatarSpeaking 0.4s ease-in-out infinite;
    }

    /* Nodding animation */
    .avatar-photo.nodding {
        animation: avatarNodding 0.8s ease-in-out infinite;
    }

    /* Smiling animation */
    .avatar-photo.smiling {
        filter: brightness(1.1);
        animation: avatarSmile 1s ease-in-out infinite;
    }

    /* Blinking animation */
    .avatar-photo.blinking {
        animation: avatarBlink 0.3s ease-in-out;
    }

    /* Head tilt animation */
    .avatar-photo.tilting {
        animation: avatarTilt 2s ease-in-out infinite;
    }

    /* Continuous movement */
    .avatar-photo.moving {
        animation: avatarMoving 4s ease-in-out infinite;
    }

    @keyframes avatarPulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes avatarBreathing {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    @keyframes avatarSpeaking {
        0%, 100% { transform: scaleY(1) translateY(0); }
        25% { transform: scaleY(1.05) translateY(-5px); }
        50% { transform: scaleY(1.08) translateY(-8px); }
        75% { transform: scaleY(1.05) translateY(-5px); }
    }

    @keyframes avatarNodding {
        0%, 100% { transform: rotateX(0deg) rotateZ(0deg); }
        25% { transform: rotateX(-20deg) rotateZ(0deg); }
        50% { transform: rotateX(0deg) rotateZ(0deg); }
        75% { transform: rotateX(20deg) rotateZ(0deg); }
    }

    @keyframes avatarSmile {
        0%, 100% { filter: brightness(1); }
        50% { filter: brightness(1.15) saturate(1.1); }
    }

    @keyframes avatarBlink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    @keyframes avatarTilt {
        0%, 100% { transform: rotateZ(0deg); }
        25% { transform: rotateZ(-5deg); }
        75% { transform: rotateZ(5deg); }
    }

    @keyframes avatarMoving {
        0%, 100% { transform: translateX(0) translateY(0); }
        25% { transform: translateX(10px) translateY(-5px); }
        50% { transform: translateX(0) translateY(0); }
        75% { transform: translateX(-10px) translateY(5px); }
    }

    /* Combined animations */
    .avatar-photo.active {
        animation: 
            avatarBreathing 3s ease-in-out infinite,
            avatarMoving 4s ease-in-out infinite,
            avatarTilt 2s ease-in-out infinite;
    }

    .avatar-photo.active.speaking {
        animation: 
            avatarSpeaking 0.4s ease-in-out infinite,
            avatarMoving 4s ease-in-out infinite;
    }
</style>

<div class="avatar-photo-container">
    <img id="avatar-photo" class="avatar-photo active" src="/Esprit-PW-2A30-2526-TakwiniBot-gestion_entretien/Esprit-PW-2A30-2526-TakwiniBot-gestion_entretien/Hilux-1.0.0/assets/img/takwini-avatar.png" alt="Takwini Avatar">
</div>

<script>
    // Photo avatar animations with continuous movement
    window.avatarPhoto = {
        element: document.getElementById('avatar-photo'),
        blinkInterval: null,
        
        init: function() {
            // Start with active state
            this.element.classList.add('active');
            // Random blinking every 3-5 seconds
            this.startRandomBlink();
        },
        
        speak: function() {
            this.element.classList.add('speaking');
            this.element.classList.add('active');
        },
        
        stopSpeaking: function() {
            this.element.classList.remove('speaking');
            this.element.classList.add('active');
        },
        
        nod: function() {
            this.element.classList.remove('nodding');
            setTimeout(() => {
                this.element.classList.add('nodding');
            }, 10);
            setTimeout(() => {
                this.element.classList.remove('nodding');
                this.element.classList.add('active');
            }, 1600);
        },
        
        smile: function() {
            this.element.classList.add('smiling');
            this.element.classList.add('active');
        },
        
        stopSmiling: function() {
            this.element.classList.remove('smiling');
        },
        
        blink: function() {
            this.element.classList.add('blinking');
            setTimeout(() => {
                this.element.classList.remove('blinking');
            }, 300);
        },
        
        startRandomBlink: function() {
            const randomBlink = () => {
                const delay = Math.random() * 3000 + 2000; // 2-5 seconds
                setTimeout(() => {
                    this.blink();
                    randomBlink();
                }, delay);
            };
            randomBlink();
        },
        
        reset: function() {
            this.element.classList.remove('speaking', 'nodding', 'smiling', 'blinking');
            this.element.classList.add('active');
        }
    };
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
        window.avatarPhoto.init();
    });
</script>
