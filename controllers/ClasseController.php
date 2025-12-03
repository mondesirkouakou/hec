<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Classe.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../classes/Matiere.php';
require_once __DIR__ . '/../classes/Admin.php';

class ClasseController {
    private $db;
    private $classeModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->classeModel = new Classe($this->db);
    }
    
    /**
     * Affiche la liste des classes
     */
    public function index() {
        $filtreStatutListes = $_GET['statut_listes'] ?? null;
        $anneeId = isset($_GET['annee_id']) ? (int)$_GET['annee_id'] : null;

        if ($filtreStatutListes === 'en_attente') {
            // Pour les listes en attente, on reste limité à l'année active
            $classes = $this->classeModel->getClassesEnAttenteValidation();
        } else {
            if ($anneeId) {
                // Lister les classes de l'année demandée (même si l'année est fermée)
                $classes = $this->classeModel->getClassesByAnneeUniversitaire($anneeId);
            } else {
                // Comportement par défaut : classes de l'année active
                $classes = $this->classeModel->getAllClasses();
            }
        }
        include __DIR__ . '/../views/admin/classes/index.php';
    }
    
    /**
     * Affiche le formulaire de création d'une classe
     */
    public function create() {
        // Récupérer la liste des années universitaires pour le formulaire
        $anneeModel = new AnneeUniversitaire($this->db);
        $annees = $anneeModel->getAll();
        
        include __DIR__ . '/../views/admin/classes/create.php';
    }
    
    /**
     * Enregistre une nouvelle classe
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'code' => $_POST['code'] ?? '',
                'intitule' => $_POST['intitule'] ?? '',
                'niveau' => $_POST['niveau'] ?? '',
                'annee_universitaire_id' => $_POST['annee_universitaire_id'] ?? null
            ];
            
            // Validation des données
            $errors = $this->validateClassData($data);
            
            if (empty($errors)) {
                $success = $this->classeModel->create($data);
                
                if ($success) {
                    $_SESSION['success'] = "La classe a été créée avec succès.";
                    header('Location: ' . BASE_URL . 'admin/classes');
                    exit();
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de la classe.";
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/classes/nouvelle');
            exit();
        }
    }
    
    /**
     * Affiche les détails d'une classe
     */
    public function show($id) {
        $classe = $this->classeModel->getById($id);
        
        if (!$classe) {
            $_SESSION['error'] = "Classe introuvable.";
            header('Location: ' . BASE_URL . 'admin/classes');
            exit();
        }
        
        // Récupérer les étudiants de la classe
        $etudiants = $this->classeModel->getEtudiants($id);
        
        // Récupérer les professeurs de la classe
        $professeurs = $this->classeModel->getProfesseurs($id);
        
        include __DIR__ . '/../views/admin/classes/show.php';
    }
    
    /**
     * Affiche le formulaire de modification d'une classe
     */
    public function edit($id) {
        $classe = $this->classeModel->getById($id);
        
        if (!$classe) {
            $_SESSION['error'] = "Classe introuvable.";
            header('Location: ' . BASE_URL . 'admin/classes');
            exit();
        }
        
        // Récupérer la liste des années universitaires pour le formulaire
        $anneeModel = new AnneeUniversitaire($this->db);
        $annees = $anneeModel->getAll();

        // Récupérer les étudiants de la classe
        $etudiants = $this->classeModel->getEtudiants($id);

        // Récupérer toutes les matières disponibles
        $matiereModel = new Matiere($this->db);
        $allMatieres = $matiereModel->getAll();

        // Récupérer les matières attribuées à cette classe avec leurs coefficients et crédits
        $assignedMatieres = $this->classeModel->getAssignedMatieresWithDetails($id);
        
        include __DIR__ . '/../views/admin/classes/edit_full.php';
    }
    
    /**
     * Met à jour une classe existante
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'code' => $_POST['code'] ?? '',
                'intitule' => $_POST['intitule'] ?? '',
                'niveau' => $_POST['niveau'] ?? '',
                'annee_universitaire_id' => $_POST['annee_universitaire_id'] ?? null
            ];
            
            // Validation des données
            $errors = $this->validateClassData($data);
            
            if (empty($errors)) {
                $success = $this->classeModel->update($data);
                
                if ($success) {
                    $_SESSION['success'] = "La classe a été mise à jour avec succès.";
                    header('Location: ' . BASE_URL . 'admin/classes/' . $id);
                    exit();
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de la classe.";
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/classes/' . $id . '/edit');
            exit();
        }
    }
    
    /**
     * Supprime une classe
     */
    public function delete($id) {
        $success = $this->classeModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "La classe a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression de la classe.";
        }
        
        header('Location: ' . BASE_URL . 'admin/classes');
        exit();
    }

    public function validerListes($classeId) {
        $admin = new Admin();
        $ok = $admin->validerListesClasse($classeId);
        if ($ok) {
            $_SESSION['success'] = "Les listes de cette classe ont été validées.";
        } else {
            if (empty($_SESSION['error'])) {
                $_SESSION['error'] = "Erreur lors de la validation des listes.";
            }
        }
        header('Location: ' . BASE_URL . 'admin/classes/' . (int)$classeId);
        exit();
    }
    
    public function validerToutesLesListes() {
        $admin = new Admin();
        $ok = $admin->validerToutesLesListesEnAttente();
        if ($ok) {
            $_SESSION['success'] = "Toutes les listes en attente ont été validées.";
        } else {
            if (empty($_SESSION['error'])) {
                $_SESSION['error'] = "Erreur lors de la validation des listes en attente.";
            }
        }
        header('Location: ' . BASE_URL . 'admin/classes?statut_listes=en_attente');
        exit();
    }
    
    /**
     * Valide les données d'une classe
     */
    private function validateClassData($data) {
        $errors = [];
        
        if (empty($data['code'])) {
            $errors[] = "Le code de la classe est obligatoire.";
        }
        
        if (empty($data['intitule'])) {
            $errors[] = "L'intitulé de la classe est obligatoire.";
        }
        
        if (empty($data['annee_universitaire_id'])) {
            $errors[] = "L'année universitaire est obligatoire.";
        }
        
        return $errors;
    }
    /**
     * Affiche l'écran de désignation du chef de classe
     */
    public function designerChef($classeId) {
        $classe = $this->classeModel->getById($classeId);
        if (!$classe) {
            $_SESSION['error'] = "Classe introuvable.";
            header('Location: ' . BASE_URL . 'admin/classes');
            exit();
        }
        $etudiants = $this->classeModel->getEtudiants($classeId);
        include __DIR__ . '/../views/admin/classes/designer_chef.php';
    }

    /**
     * Nommer un chef de classe (POST)
     */
    public function nommerChef($classeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/classes/designer-chef/' . $classeId);
            exit();
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez renseigner l'email et le mot de passe.";
            header('Location: ' . BASE_URL . 'admin/classes/designer-chef/' . $classeId);
            exit();
        }
        $admin = new Admin();
        $ok = $admin->creerChefClassePourClasse($classeId, $email, $password);
        if ($ok) {
            $_SESSION['success'] = "Chef de classe désigné avec succès.";
            header('Location: ' . BASE_URL . 'admin/classes');
        } else {
            if (empty($_SESSION['error'])) {
                $_SESSION['error'] = "Échec de la désignation du chef de classe.";
            }
            header('Location: ' . BASE_URL . 'admin/classes/designer-chef/' . $classeId);
        }
        exit();
    }

    /**
     * Attribue une matière à une classe avec coefficient et crédits (POST)
     */
    public function assignMatiere($classeId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matiereIntitule = trim($_POST['matiere_intitule'] ?? '');
            $coefficient = $_POST['coefficient'] ?? null;
            $credits = $_POST['credits'] ?? null;

            $errors = [];
            if (empty($matiereIntitule)) {
                $errors[] = "Veuillez renseigner le nom de la matière.";
            }
            if (empty($coefficient) || !is_numeric($coefficient) || $coefficient <= 0) {
                $errors[] = "Le coefficient doit être un nombre positif.";
            }
            if (empty($credits) || !is_numeric($credits) || $credits <= 0) {
                $errors[] = "Les crédits doivent être un nombre positif.";
            }

            if (empty($errors)) {
                $matiereModel = new Matiere($this->db);
                $existing = $matiereModel->getByIntitule($matiereIntitule);
                $matiereId = $existing['id'] ?? null;
                if (!$matiereId) {
                    $matiereId = $matiereModel->create($matiereIntitule);
                }
                if (!$matiereId) {
                    $_SESSION['error'] = "Impossible de créer la matière.";
                } else {
                    $data = [
                        'classe_id' => $classeId,
                        'matiere_id' => $matiereId,
                        'coefficient' => $coefficient,
                        'credits' => $credits
                    ];
                    $success = $this->classeModel->assignMatiereToClass($data);

                    if ($success) {
                        $_SESSION['success'] = "Matière attribuée avec succès à la classe.";
                    } else {
                        $_SESSION['error'] = "Une erreur est survenue lors de l'attribution de la matière.";
                    }
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        }
        error_log("Redirection vers: " . BASE_URL . "admin/classes/assign-matiere/" . $classeId);
        header('Location: ' . BASE_URL . 'admin/classes/assign-matiere/' . $classeId);
        exit();
    }

    public function assignMatiereForm($classeId) {
        $classe = $this->classeModel->getById($classeId);
        if (!$classe) {
            $_SESSION['error'] = "Classe introuvable.";
            header('Location: ' . BASE_URL . 'admin/classes');
            exit();
        }

        $matiereModel = new Matiere($this->db);
        $allMatieres = $matiereModel->getAll();
        $assignedMatieres = $this->classeModel->getAssignedMatieresWithDetails($classeId);

        include __DIR__ . '/../views/admin/classes/assign_matiere.php';
    }
}
?>
