<?php
// Définir les constantes de base
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

// Inclure l'autoloader
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Gestion des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir le fuseau horaire
date_default_timezone_set('Africa/Abidjan');

// Obtenir l'URL demandée
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Supprimer le dossier de base de l'URL si nécessaire
$base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($base_path === '/') {
    $base_path = '';
}

// Supprimer le dossier de base de l'URI
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Nettoyer l'URI
$request_uri = '/' . trim($request_uri, '/');

// Routeur simple
switch ($request_uri) {
    case '/':
    case '':
    case '/index':
    case '/index.php':
        // Page d'accueil
        if (isLoggedIn()) {
            // Rediriger vers le tableau de bord approprié en fonction du rôle
            $userRole = $_SESSION['user_role'] ?? 'guest';
            switch ($userRole) {
                case 'admin':
                    header('Location: ' . BASE_URL . 'admin/dashboard');
                    break;
                case 'professeur':
                    echo '<a href="' . BASE_URL . 'forgot-password" class="forgot-password">Mot de passe oublié ?</a>';
                    break;
                case 'etudiant':
                    header('Location: ' . BASE_URL . 'etudiant/dashboard');
                    break;
                default:
                    header('Location: ' . BASE_URL . 'login');
            }
            exit();
        } else {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        break;
        
    case '/login':
        // Page de connexion
        require_once __DIR__ . '/controllers/AuthController.php';
        $authController = new AuthController();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->login();
        } else {
            $authController->showLoginForm();
        }
        break;

    case '/change-password':
        header('Location: ' . BASE_URL . 'profile');
        exit();
        break;

    case '/profile':
        require_once __DIR__ . '/controllers/ProfileController.php';
        $profileController = new ProfileController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileController->update();
        } else {
            $profileController->index();
        }
        break;
        
    case '/logout':
        // Déconnexion
        require_once __DIR__ . '/controllers/AuthController.php';
        $authController = new AuthController();
        $authController->logout();
        break;
        
    // Routes d'administration
    case (preg_match('/^\/admin\//', $request_uri) ? true : false):
        // Vérifier si l'utilisateur est connecté et a le rôle admin
        if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé. Veuillez vous connecter en tant qu'administrateur.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Inclure le routeur d'administration
        require_once __DIR__ . '/routes/admin.php';
        break;
        
    // Routes chef de classe
    case (preg_match('/^\/chef-classe\//', $request_uri) ? true : false):
        // Vérifier si l'utilisateur est connecté et a le rôle chef-classe
        if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'chef-classe') {
            $_SESSION['error'] = "Accès non autorisé. Veuillez vous connecter en tant que chef de classe.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Inclure le routeur chef de classe
        require_once __DIR__ . '/routes/chef_classe.php';
        break;

    // Routes professeur
    case (preg_match('/^\/professeur\//', $request_uri) ? true : false):
        if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'professeur') {
            $_SESSION['error'] = "Accès non autorisé. Veuillez vous connecter en tant que professeur.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        require_once __DIR__ . '/routes/professeur.php';
        break;

    // Routes étudiant
    case (preg_match('/^\/etudiant\//', $request_uri) ? true : false):
        if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'etudiant') {
            $_SESSION['error'] = "Accès non autorisé. Veuillez vous connecter en tant qu'étudiant.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        require_once __DIR__ . '/routes/etudiant.php';
        break;
        
    // Autres routes (à étendre selon les besoins)
    default:
        // Vérifier si le fichier existe dans le dossier public
        if (file_exists(__DIR__ . '/public' . $request_uri)) {
            return false; // Laissez le serveur gérer la requête
        }
        
        // Page non trouvée
        http_response_code(404);
        include __DIR__ . '/views/errors/404.php';
        break;
}
