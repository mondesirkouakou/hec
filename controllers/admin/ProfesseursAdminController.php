<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Professeur.php';

class ProfesseursAdminController {
    private $db;
    private $professeurModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->professeurModel = new Professeur();
    }

    private function checkAccess() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }

    public function index() {
        $this->checkAccess();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }
        $perPage = 20;

        $result = $this->professeurModel->getProfesseursPagines($page, $perPage);
        $professeurs = $result['professeurs'] ?? [];
        $currentPage = $result['page'] ?? $page;
        $totalPages = $result['totalPages'] ?? 1;

        include __DIR__ . '/../../views/admin/professeurs/index.php';
    }

    public function create() {
        $this->checkAccess();
        $professeur = null;
        include __DIR__ . '/../../views/admin/professeurs/form.php';
    }

    public function store() {
        $this->checkAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/professeurs');
            exit();
        }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $specialite = trim($_POST['specialite'] ?? '');

        if ($nom === '' || $prenom === '' || $email === '' || $telephone === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            header('Location: ' . BASE_URL . 'admin/professeurs/ajouter');
            exit();
        }

        if ($password === '') {
            $password = $telephone;
        }

        $professeurData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'specialite' => $specialite !== '' ? $specialite : null,
            'password' => $password,
        ];

        try {
            $professeurId = $this->professeurModel->creerProfesseur($professeurData);
            if (!$professeurId) {
                throw new Exception("Échec de la création du professeur.");
            }
            $_SESSION['success'] = "Professeur ajouté avec succès.";
        } catch (Exception $e) {
            error_log('Erreur ajout professeur admin: ' . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de l'ajout du professeur.";
        }

        header('Location: ' . BASE_URL . 'admin/professeurs');
        exit();
    }
}
