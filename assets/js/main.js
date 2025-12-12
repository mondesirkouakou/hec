// Fichier JavaScript principal

document.addEventListener('DOMContentLoaded', function() {
    console.log('Le DOM est entièrement chargé et analysé');

    // Exemple: Ajouter un écouteur d'événements à un bouton
    const myButton = document.getElementById('myButton');
    if (myButton) {
        myButton.addEventListener('click', function() {
            alert('Bouton cliqué !');
        });
    }

    // Exemple: Gérer les messages flash (s'ils existent)
    const flashMessages = document.querySelectorAll('.message');
    flashMessages.forEach(function(message) {
        // Vous pouvez ajouter une logique pour masquer les messages après un certain temps
        // setTimeout(() => {
        //     message.style.display = 'none';
        // }, 5000);
    });

    const themeToggle = document.getElementById('themeToggle');
    const themeLabel = document.getElementById('themeLabel');
    if (themeToggle && themeLabel) {
        const themeIcon = themeToggle.querySelector('i');

        const applyTheme = function(mode) {
            if (mode === 'dark') {
                document.body.classList.add('dark-mode');
                themeLabel.textContent = 'Mode Clair';
                if (themeIcon) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
            } else {
                document.body.classList.remove('dark-mode');
                themeLabel.textContent = 'Mode Sombre';
                if (themeIcon) {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
            }
        };

        const storedTheme = window.localStorage ? localStorage.getItem('theme') : null;
        if (storedTheme === 'dark' || storedTheme === 'light') {
            applyTheme(storedTheme);
        } else {
            applyTheme('light');
        }

        themeToggle.addEventListener('click', function(event) {
            event.preventDefault();
            const isDark = document.body.classList.contains('dark-mode');
            const newTheme = isDark ? 'light' : 'dark';

            if (window.localStorage) {
                localStorage.setItem('theme', newTheme);
            }

            // Recharger la page pour que tous les composants (tables, modals, etc.)
            // se réinitialisent correctement avec le nouveau thème
            window.location.reload();
        });
    }

    // Gestion manuelle du menu utilisateur (icône bonhomme)
    const userDropdownBtn = document.getElementById('userDropdown');
    const userMenu = document.getElementById('userMenu');
    if (userDropdownBtn && userMenu) {
        userDropdownBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            userMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (event) {
            if (!userMenu.contains(event.target) && !userDropdownBtn.contains(event.target)) {
                userMenu.classList.remove('show');
            }
        });
    }
});