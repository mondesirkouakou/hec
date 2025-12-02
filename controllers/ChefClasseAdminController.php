<?php
require_once __DIR__ . '/../classes/Admin.php';

class ChefClasseAdminController {
    private $admin;

    public function __construct() {
        $this->admin = new Admin();
    }

    /**
     * Liste les chefs de classe avec leurs comptes
     */
    public function index() {
        $chefs = $this->admin->getChefsClasseAvecComptes();
        include __DIR__ . '/../views/admin/chef_classe/index.php';
    }

    /**
     * Traite les actions groupées (activer, désactiver, supprimer)
     */
    public function actionsGroupees() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/chefs-classe');
            exit();
        }

        $action = $_POST['action'] ?? '';
        $userIds = isset($_POST['chefs']) && is_array($_POST['chefs']) ? $_POST['chefs'] : [];

        if (empty($userIds)) {
            $_SESSION['error'] = "Veuillez sélectionner au moins un chef de classe.";
            header('Location: ' . BASE_URL . 'admin/chefs-classe');
            exit();
        }

        // Normaliser les IDs
        $userIds = array_map('intval', $userIds);

        $ok = false;
        $successMessage = '';
        $errorMessage = '';

        switch ($action) {
            case 'activer':
                $ok = $this->admin->changerStatutChefsClasse($userIds, true);
                $successMessage = 'Les comptes sélectionnés ont été activés.';
                $errorMessage = "Erreur lors de l'activation des comptes sélectionnés.";
                break;

            case 'desactiver':
                $ok = $this->admin->changerStatutChefsClasse($userIds, false);
                $successMessage = 'Les comptes sélectionnés ont été désactivés.';
                $errorMessage = "Erreur lors de la désactivation des comptes sélectionnés.";
                break;

            case 'supprimer':
                $ok = $this->admin->supprimerChefsClasse($userIds);
                $successMessage = 'Les comptes chef de classe sélectionnés ont été supprimés (désactivés et retirés de la liste des chefs de classe).';
                $errorMessage = "Erreur lors de la suppression des comptes sélectionnés.";
                break;

            default:
                $_SESSION['error'] = 'Action invalide.';
                header('Location: ' . BASE_URL . 'admin/chefs-classe');
                exit();
        }

        if ($ok) {
            $_SESSION['success'] = $successMessage;
        } else {
            if (empty($_SESSION['error'])) {
                $_SESSION['error'] = $errorMessage;
            }
        }

        header('Location: ' . BASE_URL . 'admin/chefs-classe');
        exit();
    }
}
