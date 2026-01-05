<?php
/**
 * Fonctions de sécurité supplémentaires
 */

/**
 * Nettoie et valide une entrée utilisateur
 * @param mixed $data Les données à nettoyer
 * @param string $type Le type de validation (string, email, int, float, url)
 * @return mixed Les données nettoyées ou false si invalide
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) ? filter_var($data, FILTER_SANITIZE_EMAIL) : false;
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT) !== false ? (int)$data : false;
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT) !== false ? (float)$data : false;
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL) ? filter_var($data, FILTER_SANITIZE_URL) : false;
        case 'string':
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Génère un mot de passe aléatoire sécurisé
 * @param int $length Longueur du mot de passe (minimum 8)
 * @return string Le mot de passe généré
 */
function generateSecurePassword($length = 12) {
    $length = max(8, $length);
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
    $password = '';
    $charsLength = strlen($chars);
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charsLength - 1)];
    }
    
    // S'assurer qu'il contient au moins une majuscule, une minuscule, un chiffre et un caractère spécial
    if (!preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        return generateSecurePassword($length);
    }
    
    return $password;
}

/**
 * Limite le taux de requêtes pour prévenir les attaques par force brute
 * @param string $identifier Identifiant unique (IP, user_id, etc.)
 * @param int $maxAttempts Nombre maximum de tentatives
 * @param int $timeWindow Fenêtre de temps en secondes
 * @return bool True si la limite n'est pas atteinte, false sinon
 */
function rateLimitCheck($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['first_attempt'];
    
    // Réinitialiser si la fenêtre de temps est dépassée
    if ($elapsed > $timeWindow) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    // Incrémenter les tentatives
    $_SESSION[$key]['attempts']++;
    
    // Vérifier si la limite est atteinte
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

/**
 * Réinitialise le compteur de limitation de taux
 * @param string $identifier Identifiant unique
 */
function rateLimitReset($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    unset($_SESSION[$key]);
}

/**
 * Enregistre une tentative de sécurité suspecte
 * @param string $type Type d'événement (login_failed, csrf_failed, etc.)
 * @param string $details Détails supplémentaires
 */
function logSecurityEvent($type, $details = '') {
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);
    
    // Créer le dossier logs s'il n'existe pas
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $userId = $_SESSION['user_id'] ?? 'guest';
    
    $logEntry = sprintf(
        "[%s] Type: %s | User: %s | IP: %s | Details: %s | User-Agent: %s\n",
        $timestamp,
        $type,
        $userId,
        $ip,
        $details,
        $userAgent
    );
    
    error_log($logEntry, 3, $logFile);
}
?>
