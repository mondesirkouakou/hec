<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../config/config.php';

class ProfileController {
    private $user;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->user = new User();
        $this->checkAuth();
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }

    public function index() {
        $user = $this->user->getById((int)$_SESSION['user_id']);
        $displayName = $_SESSION['display_name'] ?? ($user['username'] ?? '');
        include __DIR__ . '/../views/profile/index.php';
    }

    public function update() {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $displayName = trim($_POST['display_name'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Mise à jour du nom affiché
        if ($displayName !== '') {
            $okDn = $this->user->updateDisplayName($userId, $displayName);
            if ($okDn) {
                $_SESSION['display_name'] = $displayName;
            }
        }

        // Mise à jour du mot de passe (si un nouveau mot de passe est fourni)
        $wantsPasswordChange = ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '');
        if ($wantsPasswordChange) {
            if ($newPassword === '' || $confirmPassword === '') {
                $_SESSION['error'] = 'Veuillez renseigner le nouveau mot de passe et sa confirmation.';
                header('Location: ' . BASE_URL . 'profile');
                exit();
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
                header('Location: ' . BASE_URL . 'profile');
                exit();
            }

            $user = $this->user->getById($userId);
            if (!$user) {
                $_SESSION['error'] = 'Utilisateur introuvable';
                header('Location: ' . BASE_URL . 'profile');
                exit();
            }

            $isFirstLogin = !empty($_SESSION['first_login']);
            if (!$isFirstLogin) {
                if (!password_verify($currentPassword, $user['password'])) {
                    $_SESSION['error'] = 'Mot de passe actuel incorrect';
                    header('Location: ' . BASE_URL . 'profile');
                    exit();
                }
            }

            $success = $this->user->updatePassword($userId, $newPassword);
            if ($success) {
                $_SESSION['success'] = 'Mot de passe mis à jour avec succès';
                $_SESSION['first_login'] = 0;
                unset($_SESSION['force_password_change']);
                header('Location: ' . BASE_URL . 'profile');
                exit();
            }

            $_SESSION['error'] = 'Erreur lors de la mise à jour du mot de passe';
            header('Location: ' . BASE_URL . 'profile');
            exit();
        }

        $_SESSION['success'] = 'Profil mis à jour';
        header('Location: ' . BASE_URL . 'profile');
        exit();
    }
}
