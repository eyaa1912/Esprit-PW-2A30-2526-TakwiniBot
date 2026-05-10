/**
 * Realistic 3D Avatar System
 * Uses Three.js to create a 3D human avatar with realistic animations
 */

class Avatar3D {
    constructor(containerId = 'avatar-3d-container') {
        this.container = document.getElementById(containerId);
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.avatar = null;
        this.mixer = null;
        this.clock = new THREE.Clock();
        this.animations = {};
        this.isAnimating = false;
        
        this.init();
    }

    init() {
        // Setup scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xf5f7fa);
        
        // Setup camera
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        this.camera.position.z = 2.5;
        
        // Setup renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.container.appendChild(this.renderer.domElement);
        
        // Add lighting
        this.setupLighting();
        
        // Create avatar
        this.createAvatar();
        
        // Start animation loop
        this.animate();
        
        // Handle window resize
        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupLighting() {
        // Key light (main)
        const keyLight = new THREE.DirectionalLight(0xffffff, 1);
        keyLight.position.set(5, 5, 5);
        this.scene.add(keyLight);
        
        // Fill light
        const fillLight = new THREE.DirectionalLight(0xffffff, 0.5);
        fillLight.position.set(-5, 3, 5);
        this.scene.add(fillLight);
        
        // Back light
        const backLight = new THREE.DirectionalLight(0xffffff, 0.3);
        backLight.position.set(0, 5, -5);
        this.scene.add(backLight);
        
        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
    }

    createAvatar() {
        // Create head group
        const headGroup = new THREE.Group();
        
        // Head (sphere with texture)
        const headGeometry = new THREE.SphereGeometry(0.8, 64, 64);
        const headMaterial = new THREE.MeshStandardMaterial({
            color: 0xd4a574,
            roughness: 0.4,
            metalness: 0.1
        });
        const head = new THREE.Mesh(headGeometry, headMaterial);
        head.castShadow = true;
        head.receiveShadow = true;
        headGroup.add(head);
        
        // Load and apply photo texture
        this.loadAvatarTexture(head);
        
        // Eyes
        this.createEyes(headGroup);
        
        // Mouth
        this.createMouth(headGroup);
        
        // Hair
        this.createHair(headGroup);
        
        // Neck
        this.createNeck(headGroup);
        
        // Shoulders
        this.createShoulders(headGroup);
        
        // Add head to scene
        this.avatar = headGroup;
        this.scene.add(this.avatar);
        
        // Setup mixer for animations
        this.mixer = new THREE.AnimationMixer(this.avatar);
    }

    loadAvatarTexture(mesh) {
        // Load the avatar photo
        const textureLoader = new THREE.TextureLoader();
        textureLoader.load(
            '/Esprit-PW-2A30-2526-TakwiniBot-gestion_entretien/Esprit-PW-2A30-2526-TakwiniBot-gestion_entretien/Hilux-1.0.0/assets/img/avatar-photo.jpg',
            (texture) => {
                // Apply texture to head
                mesh.material.map = texture;
                mesh.material.needsUpdate = true;
            },
            undefined,
            (error) => {
                console.log('Could not load avatar texture, using solid color');
            }
        );
    }

    createEyes(parent) {
        // Left eye
        const leftEyeGeometry = new THREE.SphereGeometry(0.15, 32, 32);
        const eyeMaterial = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            roughness: 0.1,
            metalness: 0.2
        });
        const leftEye = new THREE.Mesh(leftEyeGeometry, eyeMaterial);
        leftEye.position.set(-0.25, 0.3, 0.7);
        parent.add(leftEye);
        
        // Left pupil
        const pupilGeometry = new THREE.SphereGeometry(0.08, 32, 32);
        const pupilMaterial = new THREE.MeshStandardMaterial({
            color: 0x333333,
            roughness: 0.3
        });
        const leftPupil = new THREE.Mesh(pupilGeometry, pupilMaterial);
        leftPupil.position.set(-0.25, 0.3, 0.82);
        parent.add(leftPupil);
        
        // Right eye
        const rightEye = leftEye.clone();
        rightEye.position.set(0.25, 0.3, 0.7);
        parent.add(rightEye);
        
        // Right pupil
        const rightPupil = leftPupil.clone();
        rightPupil.position.set(0.25, 0.3, 0.82);
        parent.add(rightPupil);
        
        // Store for animation
        this.leftPupil = leftPupil;
        this.rightPupil = rightPupil;
    }

    createMouth(parent) {
        // Mouth shape
        const mouthGeometry = new THREE.TorusGeometry(0.2, 0.05, 16, 32, Math.PI);
        const mouthMaterial = new THREE.MeshStandardMaterial({
            color: 0xc97a7a,
            roughness: 0.5
        });
        const mouth = new THREE.Mesh(mouthGeometry, mouthMaterial);
        mouth.position.set(0, -0.2, 0.75);
        mouth.rotation.x = Math.PI;
        parent.add(mouth);
        
        this.mouth = mouth;
    }

    createHair(parent) {
        // Hair (simple geometry)
        const hairGeometry = new THREE.SphereGeometry(0.85, 32, 32, 0, Math.PI * 2, 0, Math.PI * 0.5);
        const hairMaterial = new THREE.MeshStandardMaterial({
            color: 0x2d2d2d,
            roughness: 0.6
        });
        const hair = new THREE.Mesh(hairGeometry, hairMaterial);
        hair.position.y = 0.3;
        parent.add(hair);
    }

    createNeck(parent) {
        // Neck
        const neckGeometry = new THREE.CylinderGeometry(0.3, 0.35, 0.5, 32);
        const neckMaterial = new THREE.MeshStandardMaterial({
            color: 0xd4a574,
            roughness: 0.4
        });
        const neck = new THREE.Mesh(neckGeometry, neckMaterial);
        neck.position.y = -0.7;
        parent.add(neck);
    }

    createShoulders(parent) {
        // Left shoulder
        const shoulderGeometry = new THREE.SphereGeometry(0.4, 32, 32);
        const shoulderMaterial = new THREE.MeshStandardMaterial({
            color: 0x1a3a52,
            roughness: 0.5
        });
        const leftShoulder = new THREE.Mesh(shoulderGeometry, shoulderMaterial);
        leftShoulder.position.set(-0.6, -1.2, 0);
        leftShoulder.scale.set(1, 0.6, 0.8);
        parent.add(leftShoulder);
        
        // Right shoulder
        const rightShoulder = leftShoulder.clone();
        rightShoulder.position.set(0.6, -1.2, 0);
        parent.add(rightShoulder);
        
        // Chest
        const chestGeometry = new THREE.BoxGeometry(1.2, 0.8, 0.6);
        const chestMaterial = new THREE.MeshStandardMaterial({
            color: 0x1a3a52,
            roughness: 0.5
        });
        const chest = new THREE.Mesh(chestGeometry, chestMaterial);
        chest.position.y = -1;
        parent.add(chest);
    }

    // Animation methods
    animateTalking() {
        if (this.isAnimating) return;
        this.isAnimating = true;
        
        const startTime = Date.now();
        const duration = 3000; // 3 seconds
        
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Mouth animation (talking)
            const mouthScale = 1 + Math.sin(progress * Math.PI * 4) * 0.3;
            this.mouth.scale.y = mouthScale;
            
            // Head slight movement
            this.avatar.rotation.y = Math.sin(progress * Math.PI * 2) * 0.1;
            this.avatar.rotation.x = Math.cos(progress * Math.PI * 2) * 0.05;
            
            // Eye blink
            if (Math.sin(progress * Math.PI * 8) > 0.9) {
                this.leftPupil.scale.y = 0.1;
                this.rightPupil.scale.y = 0.1;
            } else {
                this.leftPupil.scale.y = 1;
                this.rightPupil.scale.y = 1;
            }
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.isAnimating = false;
                this.resetAvatar();
            }
        };
        
        animate();
    }

    animateNod() {
        if (this.isAnimating) return;
        this.isAnimating = true;
        
        const startTime = Date.now();
        const duration = 1000;
        
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Head nod
            this.avatar.rotation.x = Math.sin(progress * Math.PI * 2) * 0.3;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.isAnimating = false;
                this.resetAvatar();
            }
        };
        
        animate();
    }

    animateSmile() {
        if (this.isAnimating) return;
        this.isAnimating = true;
        
        const startTime = Date.now();
        const duration = 1500;
        
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Smile (mouth curve)
            this.mouth.scale.y = 1 + Math.sin(progress * Math.PI) * 0.5;
            
            // Eye squint
            this.leftPupil.scale.y = 1 - Math.sin(progress * Math.PI) * 0.3;
            this.rightPupil.scale.y = 1 - Math.sin(progress * Math.PI) * 0.3;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.isAnimating = false;
                this.resetAvatar();
            }
        };
        
        animate();
    }

    animateBlink() {
        const duration = 300;
        const startTime = Date.now();
        
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Blink animation
            const blink = Math.sin(progress * Math.PI);
            this.leftPupil.scale.y = 1 - blink * 0.9;
            this.rightPupil.scale.y = 1 - blink * 0.9;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        animate();
    }

    resetAvatar() {
        this.avatar.rotation.set(0, 0, 0);
        this.mouth.scale.set(1, 1, 1);
        this.leftPupil.scale.set(1, 1, 1);
        this.rightPupil.scale.set(1, 1, 1);
    }

    animate() {
        requestAnimationFrame(() => this.animate());
        
        // Random blink every 5 seconds
        if (Math.random() < 0.01) {
            this.animateBlink();
        }
        
        this.renderer.render(this.scene, this.camera);
    }

    onWindowResize() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    // Public animation methods
    speak() {
        this.animateTalking();
    }

    nod() {
        this.animateNod();
    }

    smile() {
        this.animateSmile();
    }

    blink() {
        this.animateBlink();
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.avatar3D = new Avatar3D('avatar-3d-container');
});
