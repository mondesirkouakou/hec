<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Etudiant.php';
require_once __DIR__ . '/../classes/Professeur.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/ChefClasse.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Traite la tentative de connexion
     */
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm() {
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord approprié
        if (isset($_SESSION['user_id'])) {
            $this->redirectByRole($_SESSION['role_id']);
            return;
        }
        
        // Afficher le formulaire de connexion
        $pageTitle = 'Connexion';
        $error = $_SESSION['error'] ?? null;
        
        // Inclure la vue de connexion
        include __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Traite la tentative de connexion
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            
            if ($this->user->login($username, $password)) {
                if (!empty($_SESSION['force_password_change'])) {
                    header('Location: ' . BASE_URL . 'change-password');
                    exit();
                }
                // Redirection en fonction du rôle
                $this->redirectByRole($_SESSION['role_id']);
            } else {
                $_SESSION['error'] = "Identifiants incorrects";
                $this->showLoginForm();
            }
        } else {
            // Si la méthode n'est pas POST, afficher le formulaire
            $this->showLoginForm();
        }
    }
    
    /**
     * Redirige l'utilisateur en fonction de son rôle
     */
    private function redirectByRole($roleId) {
        switch ($roleId) {
            case 1: // Admin
                header('Location: ' . BASE_URL . 'admin/dashboard');
                break;
            case 2: // Chef de classe
                header('Location: ' . BASE_URL . 'chef-classe/dashboard');
                break;
            case 3: // Professeur
                header('Location: ' . BASE_URL . 'professeur/dashboard');
                break;
            case 4: // Étudiant
                header('Location: ' . BASE_URL . 'etudiant/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . 'login');
        }
        exit();
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        // Détruire la session
        session_unset();
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: ' . BASE_URL . 'login');
        exit();
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }
    
    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    public static function checkRole($requiredRole) {
        self::checkAuth();
        
        if ($_SESSION['role_id'] != $requiredRole) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
    }
    
    /**
     * Change le mot de passe de l'utilisateur
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                return false;
            }
            
            // Vérifier l'ancien mot de passe (sauf si c'est une première connexion forcée)
            $user = $this->user->getById($_SESSION['user_id']);
            $isFirstLogin = $_SESSION['first_login'] ?? false;
            
            // Pour la première connexion, on ne vérifie pas le mot de passe actuel
            if (!$isFirstLogin && !password_verify($currentPassword, $user['password'])) {
                $_SESSION['error'] = "Mot de passe actuel incorrect";
                return false;
            }
            
            // Mettre à jour le mot de passe
            $success = $this->user->updatePassword($_SESSION['user_id'], $newPassword);
            
            if ($success) {
                $_SESSION['success'] = "Mot de passe mis à jour avec succès";
                $_SESSION['first_login'] = 0; // Désactiver le flag de première connexion
                unset($_SESSION['force_password_change']); // Retirer l'obligation de changement
                
                // Rediriger vers le tableau de bord approprié
                $this->redirectByRole($_SESSION['role_id']);
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du mot de passe";
                return false;
            }
        } else {
            // Afficher le formulaire
            include __DIR__ . '/../views/auth/change_password.php';
        }
    }
}
