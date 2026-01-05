<?php
/**
 * Middleware RBAC (Role-Based Access Control)
 * Contrôle d'accès basé sur les rôles utilisateur
 */

// Définition des rôles (constantes)
define('ROLE_ADMIN', 1);
define('ROLE_CHEF_CLASSE', 2);
define('ROLE_PROFESSEUR', 3);
define('ROLE_ETUDIANT', 4);

class RBACMiddleware {
    
    /**
     * Mapping des routes vers les rôles autorisés
     */
    private static $routePermissions = [
        '/admin/' => [ROLE_ADMIN],
        '/chef_classe/' => [ROLE_CHEF_CLASSE],
        '/professeur/' => [ROLE_PROFESSEUR],
        '/etudiant/' => [ROLE_ETUDIANT],
        '/profile/' => [ROLE_ADMIN, ROLE_CHEF_CLASSE, ROLE_PROFESSEUR, ROLE_ETUDIANT],
    ];
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function requireAuth() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Veuillez vous connecter pour accéder à cette page.';
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            
            logSecurityEvent('unauthorized_access_attempt', 'Not logged in - URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            
            header('Location: ' . BASE_URL . 'login.php');
            exit();
        }
    }
    
    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    public static function requireRole($allowedRoles) {
        self::requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        $userRole = $_SESSION['role_id'] ?? null;
        
        if (!in_array($userRole, $allowedRoles)) {
            logSecurityEvent('forbidden_access_attempt', 
                'User role: ' . $userRole . ' - Required: ' . implode(',', $allowedRoles) . 
                ' - URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown')
            );
            
            http_response_code(403);
            $_SESSION['error_message'] = 'Accès interdit. Vous n\'avez pas les permissions nécessaires.';
            
            // Rediriger vers le dashboard approprié
            self::redirectToDashboard();
            exit();
        }
    }
    
    /**
     * Vérifie automatiquement les permissions basées sur l'URL
     */
    public static function checkRoutePermissions() {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        
        // Ignorer les pages publiques
        $publicPages = ['/login.php', '/index.php', '/logout.php', '/'];
        foreach ($publicPages as $page) {
            if (strpos($currentPath, $page) !== false) {
                return;
            }
        }
        
        // Vérifier si l'utilisateur est connecté
        self::requireAuth();
        
        // Vérifier les permissions par route
        foreach (self::$routePermissions as $route => $allowedRoles) {
            if (strpos($currentPath, $route) !== false) {
                self::requireRole($allowedRoles);
                return;
            }
        }
    }
    
    /**
     * Redirige vers le dashboard approprié selon le rôle
     */
    private static function redirectToDashboard() {
        $userRole = $_SESSION['role_id'] ?? null;
        
        switch ($userRole) {
            case ROLE_ADMIN:
                header('Location: ' . BASE_URL . 'admin/dashboard');
                break;
            case ROLE_CHEF_CLASSE:
                header('Location: ' . BASE_URL . 'chef_classe/dashboard');
                break;
            case ROLE_PROFESSEUR:
                header('Location: ' . BASE_URL . 'professeur/dashboard');
                break;
            case ROLE_ETUDIANT:
                header('Location: ' . BASE_URL . 'etudiant/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . 'login.php');
        }
    }
    
    /**
     * Vérifie si l'utilisateur a accès à une ressource spécifique
     * Exemple: vérifier qu'un étudiant accède uniquement à ses propres notes
     */
    public static function checkResourceOwnership($resourceUserId) {
        $currentUserId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role_id'] ?? null;
        
        // Les admins ont accès à tout
        if ($userRole === ROLE_ADMIN) {
            return true;
        }
        
        // Vérifier que l'utilisateur accède à ses propres ressources
        if ($currentUserId != $resourceUserId) {
            logSecurityEvent('resource_ownership_violation', 
                'User ' . $currentUserId . ' tried to access resource of user ' . $resourceUserId
            );
            
            http_response_code(403);
            die('Accès interdit. Vous ne pouvez accéder qu\'à vos propres données.');
        }
        
        return true;
    }
    
    /**
     * Vérifie si l'utilisateur peut effectuer une action
     */
    public static function can($action, $resource = null) {
        $userRole = $_SESSION['role_id'] ?? null;
        
        // Matrice de permissions
        $permissions = [
            ROLE_ADMIN => ['*'], // Admins peuvent tout faire
            ROLE_CHEF_CLASSE => ['manage_students', 'manage_professors', 'view_notes', 'submit_lists'],
            ROLE_PROFESSEUR => ['enter_notes', 'view_students', 'view_classes'],
            ROLE_ETUDIANT => ['view_own_notes', 'view_bulletin', 'view_schedule'],
        ];
        
        $userPermissions = $permissions[$userRole] ?? [];
        
        // Admin a tous les droits
        if (in_array('*', $userPermissions)) {
            return true;
        }
        
        return in_array($action, $userPermissions);
    }
}

/**
 * Fonction helper pour vérifier rapidement une permission
 */
function can($action, $resource = null) {
    return RBACMiddleware::can($action, $resource);
}

/**
 * Fonction helper pour exiger une authentification
 */
function requireAuth() {
    RBACMiddleware::requireAuth();
}

/**
 * Fonction helper pour exiger un rôle
 */
function requireRole($roles) {
    RBACMiddleware::requireRole($roles);
}
?>
