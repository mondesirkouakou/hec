<?php
/**
 * Système de protection CSRF (Cross-Site Request Forgery)
 * Génère et valide des tokens pour protéger les formulaires
 */

/**
 * Génère un token CSRF et le stocke en session
 * @return string Le token généré
 */
function generateCsrfToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Récupère le token CSRF actuel
 * @return string|null Le token ou null s'il n'existe pas
 */
function getCsrfToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['csrf_token'] ?? null;
}

/**
 * Valide un token CSRF
 * @param string $token Le token à valider
 * @return bool True si le token est valide, false sinon
 */
function validateCsrfToken($token) {
    $sessionToken = getCsrfToken();
    
    if (!$sessionToken || !$token) {
        return false;
    }
    
    return hash_equals($sessionToken, $token);
}

/**
 * Génère un champ input hidden avec le token CSRF
 * @return string Le HTML du champ input
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Vérifie le token CSRF dans une requête POST
 * Termine le script avec une erreur 403 si le token est invalide
 */
function verifyCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validateCsrfToken($token)) {
            http_response_code(403);
            die('Erreur de sécurité: Token CSRF invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }
}

/**
 * Régénère le token CSRF (à utiliser après une action sensible)
 */
function regenerateCsrfToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
?>
