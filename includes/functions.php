<?php
/**
 * Fonctions utilitaires globales
 */

// Inclure le système CSRF et les fonctions de sécurité
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/security.php';

/**
 * Fonction d'échappement pour l'affichage sécurisé (Protection XSS)
 * À utiliser SYSTÉMATIQUEMENT pour toute donnée utilisateur affichée
 * 
 * @param mixed $value La valeur à échapper
 * @param bool $doubleEncode Si true, encode les entités HTML existantes
 * @return string La valeur échappée et sécurisée
 * 
 * Exemples d'utilisation :
 * - <?= e($user['nom']) ?>
 * - <input value="<?= e($data['email']) ?>">
 * - <textarea><?= e($comment) ?></textarea>
 */
function e($value, $doubleEncode = true) {
    if ($value === null) {
        return '';
    }
    
    if (is_array($value)) {
        return array_map('e', $value);
    }
    
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8', $doubleEncode);
}

/**
 * Alias de e() pour compatibilité
 */
function esc($value) {
    return e($value);
}

/**
 * Échappement pour attributs JavaScript
 * Utiliser dans : onclick="alert('<?= js($message) ?>')"
 */
function js($value) {
    return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Échappement pour URLs
 * Utiliser dans : <a href="<?= url($link) ?>">
 */
function url($value) {
    return htmlspecialchars(urlencode((string)$value), ENT_QUOTES, 'UTF-8');
}

// Fonction pour hacher les mots de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fonction pour vérifier les mots de passe
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Fonction de redirection
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fonction pour vérifier si un utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir le rôle de l'utilisateur connecté
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Fonction pour vérifier les permissions (exemple simple)
function hasPermission($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    $userRole = getUserRole();
    // Logique de permission plus complexe ici si nécessaire
    return $userRole === $requiredRole;
}

// Fonction pour charger automatiquement les classes
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Fonction pour se connecter à la base de données
function getDbConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Enregistrer l'erreur et afficher un message générique
            error_log('Erreur de connexion DB: ' . $e->getMessage());
            die('Impossible de se connecter à la base de données.');
        }
    }
    return $pdo;
}

?>