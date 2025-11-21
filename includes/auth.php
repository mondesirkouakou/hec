<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

// Cette page est incluse dans toutes les pages nécessitant une authentification
// Elle vérifie si l'utilisateur est connecté et a les permissions nécessaires

// Démarrer la session si ce n'est pas déjà fait (normalement géré par config.php)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour exiger une connexion
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'Veuillez vous connecter pour accéder à cette page.';
        redirect(BASE_URL . 'login.php');
    }
}

// Fonction pour exiger un rôle spécifique
function requireRole($requiredRole) {
    requireLogin(); // S'assurer que l'utilisateur est connecté d'abord

    if (!hasPermission($requiredRole)) {
        $_SESSION['error_message'] = 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.';
        redirect(BASE_URL . 'index.php'); // Rediriger vers une page d'accueil ou d'erreur
    }
}

// Exemple d'utilisation dans une page:
// requireRole(ROLE_ADMIN);

?>