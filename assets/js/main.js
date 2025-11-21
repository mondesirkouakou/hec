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
});