<?php
$pageTitle = 'Connexion - Portail HEC Abidjan';
$isFullWidth = true;
ob_start();
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #0752dd 0%, #2980b9 100%);
    --glass-bg: rgba(255, 255, 255, 0.85);
    --glass-border: rgba(255, 255, 255, 0.6);
    --input-bg: rgba(243, 244, 246, 0.6);
    --primary-color: #0752dd;
}

.login-wrapper {
    min-height: 100vh;
    width: 100vw;
    max-width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: transparent;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    perspective: 1000px;
}

#bgCanvas {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
}

/* Carte Glassmorphism avec Tilt */
.glass-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    background: var(--glass-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 
        0 4px 6px -1px rgba(0, 0, 0, 0.05),
        0 20px 40px -8px rgba(0, 0, 0, 0.1),
        inset 0 1px 1px rgba(255, 255, 255, 0.8);
    opacity: 0;
    transform-style: preserve-3d;
    transform: translateY(20px);
    animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    overflow: hidden;
}

/* Effet de brillance dynamique */
.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -150%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transform: skewX(-25deg);
    transition: left 0.7s ease;
    pointer-events: none;
    z-index: 11;
}

.glass-card:hover::before {
    left: 150%;
    transition: left 1.5s ease;
}

/* Header */
.login-header {
    text-align: center;
    margin-bottom: 32px;
    transform: translateZ(20px);
}

.icon-wrapper {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, rgba(7, 82, 221, 0.1), rgba(224, 37, 36, 0.1));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 28px;
    color: var(--primary-color);
    box-shadow: 0 10px 20px rgba(7, 82, 221, 0.1);
    transform: rotate(-5deg);
    transition: transform 0.3s ease;
}

.glass-card:hover .icon-wrapper {
    transform: rotate(0deg) scale(1.05) translateZ(10px);
}

.login-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
}

.login-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-top: 8px;
}

/* Floating Label Inputs */
.input-group-float {
    position: relative;
    margin-bottom: 24px;
    opacity: 0;
    transform: translateZ(10px);
    animation: fadeInSlide 0.5s ease forwards;
}

.input-group-float:nth-child(1) { animation-delay: 0.2s; }
.input-group-float:nth-child(2) { animation-delay: 0.3s; }

.custom-input {
    width: 100%;
    padding: 16px 16px 16px 48px;
    background: var(--input-bg);
    border: 2px solid transparent;
    border-radius: 16px;
    font-size: 15px;
    color: #1e293b;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}

.custom-input:focus {
    background: #ffffff;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(7, 82, 221, 0.1);
    transform: translateY(-2px);
}

.custom-input:placeholder-shown + .input-label {
    transform: translateY(0);
    color: #94a3b8;
}

.custom-input:focus + .input-label,
.custom-input:not(:placeholder-shown) + .input-label {
    transform: translateY(-26px) scale(0.85);
    color: var(--primary-color);
    font-weight: 600;
    background: transparent;
}

.input-label {
    position: absolute;
    left: 48px;
    top: 18px;
    color: #64748b;
    font-size: 15px;
    pointer-events: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: left top;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 18px;
    color: #94a3b8;
    font-size: 18px;
    transition: color 0.3s;
}

.custom-input:focus ~ .input-icon {
    color: var(--primary-color);
}

.toggle-password-btn {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    transition: color 0.3s;
}

.toggle-password-btn:hover {
    color: #1e293b;
}

/* Submit Button */
.btn-submit {
    width: 100%;
    padding: 16px;
    background: var(--primary-gradient);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 20px -5px rgba(7, 82, 221, 0.4);
    position: relative;
    overflow: hidden;
    opacity: 0;
    transform: translateZ(15px);
    animation: fadeInSlide 0.5s ease 0.4s forwards;
}

.btn-submit:hover {
    transform: translateY(-2px) scale(1.01) translateZ(20px);
    box-shadow: 0 15px 30px -5px rgba(7, 82, 221, 0.5);
}

.btn-submit:active {
    transform: translateY(0) scale(0.98);
}

/* Animations */
@keyframes fadeInUp {
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInSlide {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

/* Shake Animation (Error) */
.shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>

<div class="login-wrapper" id="loginWrapper">
    <!-- Canvas Animation -->
    <canvas id="bgCanvas"></canvas>

    <div class="glass-card" id="loginCard">
        <div class="login-header">
            <div class="icon-wrapper">
                <i class="fas fa-university"></i>
            </div>
            <h1 class="login-title">Bienvenue</h1>
            <p class="login-subtitle">Connectez-vous au portail HEC Abidjan</p>
        </div>

        <form action="<?= BASE_URL ?>login" method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <!-- Username Input -->
            <div class="input-group-float">
                <input type="text" id="username" name="username" class="custom-input" placeholder=" " required autocomplete="off">
                <label for="username" class="input-label">Identifiant</label>
                <i class="fas fa-user input-icon"></i>
            </div>

            <!-- Password Input -->
            <div class="input-group-float">
                <input type="password" id="password" name="password" class="custom-input" placeholder=" " required>
                <label for="password" class="input-label">Mot de passe</label>
                <i class="fas fa-lock input-icon"></i>
                <button type="button" class="toggle-password-btn" onclick="togglePassword()">
                    <i class="far fa-eye" id="toggleIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-submit">
                Se connecter <i class="fas fa-arrow-right" style="margin-left:8px; font-size:14px;"></i>
            </button>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Focus initial
    setTimeout(() => {
        const usernameInput = document.getElementById('username');
        if(usernameInput && !usernameInput.value) {
            usernameInput.focus();
        }
    }, 800);

    // Particle Animation
    const canvas = document.getElementById('bgCanvas');
    const ctx = canvas.getContext('2d');
    let particles = [];
    let glyphs = [];
    let width = 0;
    let height = 0;
    let dpr = Math.max(1, Math.floor(window.devicePixelRatio || 1));
    let animationFrameId;

    function resizeCanvas() {
        dpr = Math.max(1, Math.floor(window.devicePixelRatio || 1));
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.floor(width * dpr);
        canvas.height = Math.floor(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 1;
            this.vy = (Math.random() - 0.5) * 1;
            this.size = Math.random() * 2 + 1;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(7, 82, 221, 0.5)';
            ctx.fill();
        }
    }

    class Glyph {
        constructor() {
            const glyphChars = ['🎓', '📚', '📖', '✏️', '∑', 'π', 'λ', 'Δ'];
            this.char = glyphChars[Math.floor(Math.random() * glyphChars.length)];
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.25;
            this.vy = (Math.random() - 0.5) * 0.25;
            this.size = Math.random() * 18 + 16;
            this.alpha = Math.random() * 0.22 + 0.14;
            this.phase = Math.random() * Math.PI * 2;
        }

        update() {
            this.phase += 0.01;
            this.x += this.vx;
            this.y += this.vy + Math.sin(this.phase) * 0.08;

            if (this.x < -40) this.x = width + 40;
            if (this.x > width + 40) this.x = -40;
            if (this.y < -40) this.y = height + 40;
            if (this.y > height + 40) this.y = -40;
        }

        draw() {
            ctx.save();
            ctx.globalAlpha = this.alpha;
            ctx.font = `${this.size}px "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", "Segoe UI", sans-serif`;
            ctx.shadowColor = 'rgba(0, 0, 0, 0.28)';
            ctx.shadowBlur = 10;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 2;
            ctx.lineWidth = 3;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.65)';
            ctx.strokeText(this.char, this.x, this.y);
            ctx.fillStyle = 'rgba(4, 40, 120, 0.95)';
            ctx.fillText(this.char, this.x, this.y);
            ctx.restore();
        }
    }

    function initParticles() {
        particles = [];
        const numberOfParticles = Math.min(110, Math.max(35, Math.floor((width * height) / 22000)));
        for (let i = 0; i < numberOfParticles; i++) {
            particles.push(new Particle());
        }

        glyphs = [];
        const numberOfGlyphs = Math.min(22, Math.max(10, Math.floor((width * height) / 90000)));
        for (let i = 0; i < numberOfGlyphs; i++) {
            glyphs.push(new Glyph());
        }
    }

    function animateParticles() {
        ctx.clearRect(0, 0, width, height);

        ctx.fillStyle = '#f0f2f5';
        ctx.fillRect(0, 0, width, height);

        for (let g = 0; g < glyphs.length; g++) {
            glyphs[g].update();
            glyphs[g].draw();
        }
        
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            for (let j = i; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 150) {
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(7, 82, 221, ${0.15 - distance/1000})`;
                    ctx.lineWidth = 1;
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }
        animationFrameId = requestAnimationFrame(animateParticles);
    }

    initParticles();
    animateParticles();

    // Tilt 3D Logic
    const card = document.getElementById('loginCard');
    const wrapper = document.getElementById('loginWrapper');

    if(wrapper && card) {
        wrapper.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });

        wrapper.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateY(0deg) rotateX(0deg)';
            card.style.transition = 'transform 0.5s ease';
        });

        wrapper.addEventListener('mouseenter', () => {
            card.style.transition = 'none';
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
