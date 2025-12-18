/**
 * HEC Premium UX - JavaScript pour interactions premium
 * Micro-interactions, animations fluides et feedback visuel
 */

class PremiumUX {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initRippleEffect();
            this.initSmoothScrolling();
            this.initButtonFeedback();
            this.initCardInteractions();
            this.initFormEnhancements();
            this.initPageTransitions();
            this.initTooltips();
            this.initLoadingStates();
            this.initScrollAnimations();
            this.initTableEnhancements();
        });
    }

    /**
     * Effet ripple premium sur les boutons
     */
    initRippleEffect() {
        document.addEventListener('click', (e) => {
            const target = e.target.closest('.btn, .nav-link, .dropdown-item, .card-clickable');
            if (!target) return;

            const rect = target.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height) * 2;
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, transparent 70%);
                border-radius: 50%;
                transform: scale(0);
                animation: premiumRipple 0.6s ease-out forwards;
                pointer-events: none;
                z-index: 1000;
            `;

            target.style.position = 'relative';
            target.style.overflow = 'hidden';
            target.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });

        // Ajouter l'animation CSS
        if (!document.getElementById('premium-ripple-style')) {
            const style = document.createElement('style');
            style.id = 'premium-ripple-style';
            style.textContent = `
                @keyframes premiumRipple {
                    0% { transform: scale(0); opacity: 1; }
                    100% { transform: scale(1); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Scroll fluide premium
     */
    initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                const targetId = anchor.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Feedback visuel sur les boutons
     */
    initButtonFeedback() {
        document.querySelectorAll('.btn').forEach(btn => {
            // Effet de pression
            btn.addEventListener('mousedown', () => {
                btn.style.transform = 'scale(0.98)';
            });

            btn.addEventListener('mouseup', () => {
                btn.style.transform = '';
            });

            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
            });

            // Effet de focus amélioré
            btn.addEventListener('focus', () => {
                btn.style.boxShadow = '0 0 0 4px rgba(7, 82, 221, 0.2)';
            });

            btn.addEventListener('blur', () => {
                btn.style.boxShadow = '';
            });
        });
    }

    /**
     * Interactions sur les cards
     */
    initCardInteractions() {
        document.querySelectorAll('.card').forEach(card => {
            // Effet de tilt 3D subtil
            card.addEventListener('mousemove', (e) => {
                if (window.innerWidth < 768) return; // Désactiver sur mobile
                
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    /**
     * Améliorations des formulaires
     */
    initFormEnhancements() {
        // Labels flottants et animations
        document.querySelectorAll('.form-control').forEach(input => {
            // Animation de focus
            input.addEventListener('focus', () => {
                const parent = input.closest('.form-group, .mb-3');
                if (parent) {
                    parent.classList.add('focused');
                }
                input.style.borderColor = '#0752dd';
                input.style.boxShadow = '0 0 0 4px rgba(7, 82, 221, 0.1)';
            });

            input.addEventListener('blur', () => {
                const parent = input.closest('.form-group, .mb-3');
                if (parent) {
                    parent.classList.remove('focused');
                }
                input.style.borderColor = '';
                input.style.boxShadow = '';
            });

            // Validation visuelle en temps réel
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else if (input.value) {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });
        });

        // Animation de soumission de formulaire
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Chargement...
                    `;
                    submitBtn.disabled = true;
                }
            });
        });
    }

    /**
     * Transitions de page fluides
     */
    initPageTransitions() {
        // Animation d'entrée des éléments
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observer les éléments pour animation au scroll
        document.querySelectorAll('.card, .alert, .table, .form-group').forEach(el => {
            el.classList.add('animate-ready');
            observer.observe(el);
        });

        // Ajouter les styles d'animation
        if (!document.getElementById('premium-animate-style')) {
            const style = document.createElement('style');
            style.id = 'premium-animate-style';
            style.textContent = `
                .animate-ready {
                    opacity: 0;
                    transform: translateY(20px);
                    transition: opacity 0.5s ease, transform 0.5s ease;
                }
                .animate-in {
                    opacity: 1;
                    transform: translateY(0);
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Tooltips améliorés
     */
    initTooltips() {
        // Initialiser les tooltips Bootstrap si disponible
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"], [title]').forEach(el => {
                if (el.title && !el.dataset.bsToggle) {
                    el.dataset.bsToggle = 'tooltip';
                    el.dataset.bsPlacement = 'top';
                }
                new bootstrap.Tooltip(el, {
                    animation: true,
                    delay: { show: 200, hide: 100 }
                });
            });
        }
    }

    /**
     * États de chargement premium
     */
    initLoadingStates() {
        // Créer l'overlay de chargement
        if (!document.getElementById('premium-loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'premium-loading-overlay';
            overlay.innerHTML = `
                <div class="premium-loader">
                    <div class="loader-ring"></div>
                    <div class="loader-ring"></div>
                    <div class="loader-ring"></div>
                    <p class="loader-text">Chargement...</p>
                </div>
            `;
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(8px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            `;
            document.body.appendChild(overlay);

            // Styles du loader
            const style = document.createElement('style');
            style.textContent = `
                .premium-loader {
                    text-align: center;
                }
                .loader-ring {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    margin: 0 4px;
                    background: #0752dd;
                    border-radius: 50%;
                    animation: loaderBounce 1.4s ease-in-out infinite both;
                }
                .loader-ring:nth-child(1) { animation-delay: -0.32s; }
                .loader-ring:nth-child(2) { animation-delay: -0.16s; }
                .loader-ring:nth-child(3) { animation-delay: 0s; }
                .loader-text {
                    margin-top: 16px;
                    color: #64748b;
                    font-weight: 500;
                }
                @keyframes loaderBounce {
                    0%, 80%, 100% { transform: scale(0); }
                    40% { transform: scale(1); }
                }
            `;
            document.head.appendChild(style);
        }

        // Exposer les méthodes globalement
        window.PremiumLoader = {
            show: () => {
                const overlay = document.getElementById('premium-loading-overlay');
                if (overlay) {
                    overlay.style.opacity = '1';
                    overlay.style.visibility = 'visible';
                }
            },
            hide: () => {
                const overlay = document.getElementById('premium-loading-overlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    overlay.style.visibility = 'hidden';
                }
            }
        };
    }

    /**
     * Animations au scroll
     */
    initScrollAnimations() {
        let lastScrollY = window.scrollY;
        let ticking = false;

        const updateScroll = () => {
            const scrollY = window.scrollY;
            
            // Effet parallaxe subtil sur le header
            const header = document.querySelector('.animated-header');
            if (header) {
                const opacity = Math.max(0.9, 1 - scrollY / 500);
                header.style.opacity = opacity;
            }

            // Bouton retour en haut
            const backToTop = document.getElementById('back-to-top');
            if (backToTop) {
                if (scrollY > 300) {
                    backToTop.style.opacity = '1';
                    backToTop.style.visibility = 'visible';
                    backToTop.style.transform = 'translateY(0)';
                } else {
                    backToTop.style.opacity = '0';
                    backToTop.style.visibility = 'hidden';
                    backToTop.style.transform = 'translateY(20px)';
                }
            }

            lastScrollY = scrollY;
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateScroll);
                ticking = true;
            }
        }, { passive: true });

        // Bouton retour en haut - action
        const backToTop = document.getElementById('back-to-top');
        if (backToTop) {
            backToTop.style.transition = 'all 0.3s ease';
            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    /**
     * Améliorations des tableaux
     */
    initTableEnhancements() {
        document.querySelectorAll('.table tbody tr').forEach(row => {
            // Effet de survol amélioré
            row.addEventListener('mouseenter', () => {
                row.style.transition = 'all 0.2s ease';
                row.style.background = 'linear-gradient(135deg, rgba(7, 82, 221, 0.03) 0%, rgba(7, 82, 221, 0.06) 100%)';
                row.style.transform = 'scale(1.005)';
            });

            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                row.style.transform = '';
            });
        });

        // Animation des lignes au chargement
        document.querySelectorAll('.table tbody tr').forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, 50 + index * 30);
        });
    }
}

// Initialiser Premium UX
const premiumUX = new PremiumUX();

// Utilitaires globaux
window.PremiumUX = {
    // Notification toast premium
    toast: (message, type = 'info', duration = 3000) => {
        const toast = document.createElement('div');
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#0752dd'
        };
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 16px 24px;
            background: ${colors[type] || colors.info};
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 99999;
            font-weight: 500;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        `;
        
        document.body.appendChild(toast);
        
        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });
        
        // Animation de sortie
        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, duration);
    },

    // Confirmation premium
    confirm: (message, onConfirm, onCancel) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmation',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0752dd',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler',
                customClass: {
                    popup: 'premium-swal-popup'
                }
            }).then((result) => {
                if (result.isConfirmed && onConfirm) {
                    onConfirm();
                } else if (onCancel) {
                    onCancel();
                }
            });
        } else if (confirm(message)) {
            onConfirm && onConfirm();
        } else {
            onCancel && onCancel();
        }
    }
};
