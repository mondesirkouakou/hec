/**
 * Thème HEC - JavaScript pour effets spectaculaires
 * Animations, transitions et interactions époustouflantes
 */

class HECTheme {
    constructor() {
        this.init();
    }

    init() {
        this.initAnimations();
        this.initParticleEffects();
        this.initScrollEffects();
        this.initButtonEffects();
        this.initLoadingEffects();
        this.initFormEffects();
        this.initNavigationEffects();
    }

    /**
     * Initialise les animations d'entrée
     */
    initAnimations() {
        // Animation d'entrée pour les éléments
        const animatedElements = document.querySelectorAll('.card, .table, .alert, .btn');
        
        animatedElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease-out';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animation spéciale pour les dashboard cards
        const dashboardCards = document.querySelectorAll('.dashboard-card');
        dashboardCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8) rotateY(15deg)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'scale(1) rotateY(0)';
            }, 300 + index * 150);
        });
    }

    /**
     * Effet de particules flottantes
     */
    initParticleEffects() {
        const particleContainer = document.createElement('div');
        particleContainer.className = 'particles';
        particleContainer.style.position = 'fixed';
        particleContainer.style.top = '0';
        particleContainer.style.left = '0';
        particleContainer.style.width = '100%';
        particleContainer.style.height = '100%';
        particleContainer.style.pointerEvents = 'none';
        particleContainer.style.zIndex = '1';
        document.body.appendChild(particleContainer);

        // Créer des particules
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 6 + 's';
            particle.style.animationDuration = (6 + Math.random() * 4) + 's';
            particleContainer.appendChild(particle);
        }
    }

    /**
     * Effets de scroll époustouflants
     */
    initScrollEffects() {
        let ticking = false;
        
        const updateScrollEffects = () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax');
            
            parallaxElements.forEach(element => {
                const speed = element.dataset.speed || 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });

            // Effet de fondu pour les éléments au scroll
            const fadeElements = document.querySelectorAll('.fade-on-scroll');
            fadeElements.forEach(element => {
                const elementTop = element.offsetTop;
                const elementHeight = element.offsetHeight;
                const windowHeight = window.innerHeight;
                
                if (scrolled > elementTop - windowHeight + elementHeight / 4) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });

            ticking = false;
        };

        const requestScrollUpdate = () => {
            if (!ticking) {
                requestAnimationFrame(updateScrollEffects);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestScrollUpdate);
    }

    /**
     * Effets sur les boutons
     */
    initButtonEffects() {
        // Effet de ripple sur les boutons
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                // Style du ripple
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.pointerEvents = 'none';
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Ajouter l'animation du ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Effets de chargement
     */
    initLoadingEffects() {
        // Spinner de chargement personnalisé
        const loadingHTML = `
            <div class="loading-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                backdrop-filter: blur(5px);
            ">
                <div class="loading-content" style="text-align: center;">
                    <div class="loading-spinner" style="
                        width: 60px;
                        height: 60px;
                        border: 4px solid rgba(7, 82, 221, 0.2);
                        border-top: 4px solid #0752dd;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 20px;
                    "></div>
                    <p style="
                        color: #0752dd;
                        font-weight: 600;
                        font-size: 1.1rem;
                        animation: pulse 1.5s ease-in-out infinite;
                    ">Chargement...</p>
                </div>
            </div>
        `;

        // Fonction pour afficher le chargement
        window.showLoading = function() {
            const loadingDiv = document.createElement('div');
            loadingDiv.innerHTML = loadingHTML;
            loadingDiv.id = 'global-loading';
            document.body.appendChild(loadingDiv);
        };

        // Fonction pour cacher le chargement
        window.hideLoading = function() {
            const loadingDiv = document.getElementById('global-loading');
            if (loadingDiv) {
                loadingDiv.style.opacity = '0';
                setTimeout(() => {
                    loadingDiv.remove();
                }, 300);
            }
        };

        // Intercepter les soumissions de formulaire pour afficher le chargement
        document.addEventListener('submit', function(e) {
            setTimeout(() => {
                window.showLoading();
            }, 100);
        });
    }

    /**
     * Effets sur les formulaires
     */
    initFormEffects() {
        // Animation des labels flottants (désactivée par défaut; n'activer que si .form-group-floating est présent)
        const formGroups = document.querySelectorAll('.form-group.form-group-floating');
        
        formGroups.forEach(group => {
            const input = group.querySelector('.form-control');
            const label = group.querySelector('label');
            
            if (input && label) {
                // Pas d'animation des labels par défaut
            }
        });

        // Effet de validation en temps réel
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#28a745';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                } else {
                    this.style.borderColor = '#dc3545';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                }
            });
        });
    }

    /**
     * Effets de navigation
     */
    initNavigationEffects() {
        // Effet de transition entre les pages
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (link && !link.target && link.href.includes(window.location.hostname)) {
                e.preventDefault();
                const href = link.getAttribute('href');
                
                // Animation de sortie
                document.body.style.opacity = '0';
                document.body.style.transform = 'translateX(-20px)';
                document.body.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        });

        // Animation d'entrée de la page
        window.addEventListener('load', function() {
            document.body.style.opacity = '1';
            document.body.style.transform = 'translateX(0)';
            document.body.style.transition = 'all 0.5s ease';
        });

        // Effet de scroll smooth
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Bouton de retour en haut avec animation
        const backToTopButton = document.createElement('div');
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopButton.className = 'back-to-top';
        backToTopButton.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0752dd, #0a6df0);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(7, 82, 221, 0.3);
            z-index: 1000;
            font-size: 1.2rem;
        `;
        
        document.body.appendChild(backToTopButton);
        
        // Afficher/masquer le bouton selon le scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.opacity = '1';
                backToTopButton.style.visibility = 'visible';
            } else {
                backToTopButton.style.opacity = '0';
                backToTopButton.style.visibility = 'hidden';
            }
        });
        
        // Retour en haut avec animation
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /**
     * Effets spéciaux pour les tableaux
     */
    initTableEffects() {
        const tables = document.querySelectorAll('.table');
        
        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-30px)';
                
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease-out';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);
            });
        });
    }

    /**
     * Effets pour les modales
     */
    initModalEffects() {
        // Animation d'entrée pour les modales
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                const modalContent = this.querySelector('.modal-content');
                modalContent.style.opacity = '0';
                modalContent.style.transform = 'scale(0.8) rotateY(15deg)';
                
                setTimeout(() => {
                    modalContent.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    modalContent.style.opacity = '1';
                    modalContent.style.transform = 'scale(1) rotateY(0)';
                }, 50);
            });
        });
    }
}

// Initialiser le thème quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    new HECTheme();
});

// Effets supplémentaires pour les pages spécifiques
window.addEventListener('load', function() {
    // Ajouter une classe au body pour les animations de page
    document.body.classList.add('page-loaded');
    
    // Effet de typing pour les titres
    const mainTitle = document.querySelector('h1, .main-title');
    if (mainTitle && mainTitle.textContent) {
        const text = mainTitle.textContent;
        mainTitle.textContent = '';
        let i = 0;
        
        const typeWriter = () => {
            if (i < text.length) {
                mainTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        
        setTimeout(typeWriter, 500);
    }
});

// Effet de survol pour les cartes de statistiques
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
            this.style.boxShadow = '0 20px 40px rgba(7, 82, 221, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 15px rgba(7, 82, 221, 0.1)';
        });
    });
});