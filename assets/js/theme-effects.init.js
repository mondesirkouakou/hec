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
