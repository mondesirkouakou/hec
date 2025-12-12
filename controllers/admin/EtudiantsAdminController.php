<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Etudiant.php';
require_once __DIR__ . '/../../classes/Classe.php';

class EtudiantsAdminController {
    private $db;
    private $etudiantModel;
    private $classeModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->etudiantModel = new Etudiant();
        $this->classeModel = new Classe($this->db);
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

        $result = $this->etudiantModel->getEtudiantsPagines($page, $perPage);
        $etudiants = $result['etudiants'] ?? [];
        $currentPage = $result['page'] ?? $page;
        $totalPages = $result['totalPages'] ?? 1;

        // Classes pour le filtre
        $classes = $this->classeModel->getAllClasses();

        include __DIR__ . '/../../views/admin/etudiants/index.php';
    }

    public function create() {
        $this->checkAccess();
        $classes = $this->classeModel->getAllClasses();
        $etudiant = null;
        include __DIR__ . '/../../views/admin/etudiants/form.php';
    }

    public function store() {
        $this->checkAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/etudiants');
            exit();
        }

        $matricule = trim($_POST['matricule'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $classeId = isset($_POST['classe_id']) && $_POST['classe_id'] !== '' ? (int)$_POST['classe_id'] : null;

        if ($matricule === '' || $nom === '' || $prenom === '' || $email === '' || strlen($password) < 8) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires (mot de passe ≥ 8 caractères).";
            header('Location: ' . BASE_URL . 'admin/etudiants/ajouter');
            exit();
        }

        $etudiantData = [
            'matricule' => $matricule,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $_POST['date_naissance'] ?? null,
            'lieu_naissance' => $_POST['lieu_naissance'] ?? null,
            'telephone' => $_POST['telephone'] ?? null,
            'email' => $email,
            'password' => $password,
        ];

        try {
            $this->db->beginTransaction();

            $etudiantId = $this->etudiantModel->creerEtudiant($etudiantData);
            if (!$etudiantId) {
                throw new Exception("Échec de la création de l'étudiant.");
            }

            if ($classeId) {
                $anneeActive = $this->db->fetch("SELECT id FROM annees_universitaires WHERE est_active = 1 LIMIT 1");
                if (!$anneeActive) {
                    throw new Exception("Aucune année universitaire active trouvée pour l'inscription.");
                }

                $this->db->insert(
                    "INSERT INTO inscriptions (etudiant_id, classe_id, annee_universitaire_id, date_inscription)
                     VALUES (:etudiant_id, :classe_id, :annee_id, NOW())",
                    [
                        'etudiant_id' => $etudiantId,
                        'classe_id' => $classeId,
                        'annee_id' => (int)$anneeActive['id'],
                    ]
                );
            }

            $this->db->commit();
            $_SESSION['success'] = "Étudiant ajouté avec succès.";
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur ajout étudiant admin: ' . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de l'ajout de l'étudiant.";
        }

        header('Location: ' . BASE_URL . 'admin/etudiants');
        exit();
    }
}
