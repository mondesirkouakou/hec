<?php
/**
 * Gestionnaire de sessions sécurisé
 * Gère le timeout, la régénération d'ID, et la sécurité des sessions
 */

class SessionManager {
    
    // Durée d'inactivité avant expiration (30 minutes)
    const TIMEOUT_DURATION = 1800;
    
    // Durée avant régénération automatique de l'ID (5 minutes)
    const REGENERATE_INTERVAL = 300;
    
    /**
     * Initialise une session sécurisée
     */
    public static function init() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configuration sécurisée déjà faite dans config.php
        // On démarre juste la session
        session_start();
        
        // Vérifier le timeout
        self::checkTimeout();
        
        // Régénérer l'ID périodiquement
        self::regenerateIdPeriodically();
        
        // Valider la session
        self::validateSession();
    }
    
    /**
     * Vérifie le timeout de la session
     */
    private static function checkTimeout() {
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $elapsed = time() - $_SESSION['LAST_ACTIVITY'];
            
            if ($elapsed > self::TIMEOUT_DURATION) {
                self::destroy();
                $_SESSION['error_message'] = 'Votre session a expiré. Veuillez vous reconnecter.';
                header('Location: ' . BASE_URL . 'login.php');
                exit();
            }
        }
        
        // Mettre à jour le timestamp
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    
    /**
     * Régénère l'ID de session périodiquement
     */
    private static function regenerateIdPeriodically() {
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else {
            $elapsed = time() - $_SESSION['CREATED'];
            
            if ($elapsed > self::REGENERATE_INTERVAL) {
                session_regenerate_id(true);
                $_SESSION['CREATED'] = time();
            }
        }
    }
    
    /**
     * Valide la session contre le hijacking
     */
    private static function validateSession() {
        // Vérifier l'IP (optionnel, peut causer des problèmes avec certains FAI)
        if (!isset($_SESSION['USER_IP'])) {
            $_SESSION['USER_IP'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        // Vérifier le User-Agent
        if (!isset($_SESSION['USER_AGENT'])) {
            $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        } else {
            $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            if ($_SESSION['USER_AGENT'] !== $currentAgent) {
                // Possible session hijacking
                logSecurityEvent('session_hijacking_attempt', 'User-Agent mismatch');
                self::destroy();
                header('Location: ' . BASE_URL . 'login.php');
                exit();
            }
        }
    }
    
    /**
     * Régénère l'ID de session (à appeler après login)
     */
    public static function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    
    /**
     * Détruit complètement la session
     */
    public static function destroy() {
        $_SESSION = [];
        
        // Détruire le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Définit une variable de session
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Récupère une variable de session
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Vérifie si une variable de session existe
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Supprime une variable de session
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Définit un message flash (disponible une seule fois)
     */
    public static function flash($key, $value) {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Récupère et supprime un message flash
     */
    public static function getFlash($key, $default = null) {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
?>
