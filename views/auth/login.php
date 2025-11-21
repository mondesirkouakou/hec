<?php
$pageTitle = 'Connexion - Portail HEC Abidjan';
ob_start();
?>

<div class="login-container animated-container">
    <div class="login-box rotate-3d">
        <h2>Connexion au portail</h2>
        
        <form action="<?= BASE_URL ?>login" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required 
                       class="form-control form-control-animated" placeholder="Entrez votre nom d'utilisateur">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" required 
                           class="form-control form-control-animated" placeholder="Entrez votre mot de passe">
                    <button type="button" class="toggle-password ripple-effect" onclick="togglePassword()">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group form-actions">
                <button type="submit" class="btn btn-primary btn-block ripple-effect explosive-zoom">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
                <a href="<?= BASE_URL ?>forgot-password" class="forgot-password">Mot de passe oublié ?</a>
            </div>
        </form>
        
        <div class="login-footer">
            <p>Première connexion ? Votre identifiant et mot de passe vous ont été communiqués par email.</p>
        </div>
    </div>
</div>

<script>
// Animation spectaculaire au chargement
document.addEventListener('DOMContentLoaded', function() {
    const loginBox = document.querySelector('.login-box');
    const title = document.querySelector('.neon-effect');
    const formGroups = document.querySelectorAll('.form-group-animated');
    
    // Animation d'entrée de la boîte de login
    loginBox.style.opacity = '0';
    loginBox.style.transform = 'translateY(-100px) rotateX(90deg)';
    loginBox.style.transformStyle = 'preserve-3d';
    
    setTimeout(() => {
        loginBox.style.transition = 'all 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        loginBox.style.opacity = '1';
        loginBox.style.transform = 'translateY(0) rotateX(0deg)';
    }, 300);
    
    // Animation du titre néon
    setTimeout(() => {
        title.style.animation = 'neon-pulse 2s ease-in-out infinite alternate';
    }, 1000);
    
    // Animation des champs de formulaire
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateX(-50px)';
        setTimeout(() => {
            group.style.transition = 'all 0.6s ease-out';
            group.style.opacity = '1';
            group.style.transform = 'translateX(0)';
        }, 800 + (index * 200));
    });
    
    // Effet de particules flottantes
    createFloatingParticles();
});

function createFloatingParticles() {
    const container = document.querySelector('.login-container');
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.style.position = 'absolute';
        particle.style.width = '4px';
        particle.style.height = '4px';
        particle.style.background = `hsl(${Math.random() * 60 + 200}, 100%, 70%)`;
        particle.style.borderRadius = '50%';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animation = `float ${Math.random() * 4 + 3}s ease-in-out infinite`;
        particle.style.animationDelay = Math.random() * 2 + 's';
        particle.style.pointerEvents = 'none';
        particle.style.zIndex = '1';
        container.appendChild(particle);
    }
}

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.querySelector('.toggle-password i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        
        // Effet de lueur quand le mot de passe est visible
        passwordInput.style.boxShadow = '0 0 20px rgba(7, 82, 221, 0.6)';
        passwordInput.style.background = 'linear-gradient(45deg, #f8f9fa, #e3f2fd)';
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        passwordInput.style.boxShadow = 'none';
        passwordInput.style.background = '#fff';
    }
}

// Effet de vibration en cas d'erreur
function shakeForm() {
    const loginBox = document.querySelector('.login-box');
    loginBox.classList.add('vibrate');
    setTimeout(() => {
        loginBox.classList.remove('vibrate');
    }, 500);
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
