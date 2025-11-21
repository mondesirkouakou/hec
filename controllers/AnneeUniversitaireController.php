<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';

class AnneeUniversitaireController {
    private $db;
    private $anneeModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->anneeModel = new AnneeUniversitaire($this->db);
    }
    
    /**
     * Affiche la liste des années universitaires
     */
    public function index() {
        $annees = $this->anneeModel->getAll();
        $anneeActive = $this->anneeModel->getActiveYear();
        
        include __DIR__ . '/../views/admin/annees/index.php';
    }
    
    /**
     * Affiche le formulaire de création d'une année universitaire
     */
    public function create() {
        include __DIR__ . '/../views/admin/annees/create.php';
    }
    
    /**
     * Enregistre une nouvelle année universitaire
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'annee_debut' => $_POST['annee_debut'] ?? '',
                'annee_fin' => $_POST['annee_fin'] ?? '',
                'est_active' => isset($_POST['est_active']) ? 1 : 0
            ];
            
            // Validation des données
            $errors = $this->validateYearData($data);
            
            if (empty($errors)) {
                // Vérifier si l'année existe déjà
                if ($this->anneeModel->yearExists($data['annee_debut'], $data['annee_fin'])) {
                    $errors[] = "Cette année universitaire existe déjà.";
                } else {
                    // Si c'est la première année, on l'active par défaut
                    $existingYears = $this->anneeModel->getAll();
                    if (empty($existingYears)) {
                        $data['est_active'] = 1;
                    }
                    
                    $success = $this->anneeModel->create($data);
                    
                    if ($success && $data['est_active']) {
                        $this->anneeModel->setActiveYear($success);
                    }
                    
                    if ($success) {
                        $_SESSION['success'] = "L'année universitaire a été créée avec succès.";
                        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
                        exit();
                    } else {
                        $errors[] = "Une erreur est survenue lors de la création de l'année universitaire.";
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/annees-universitaires/nouvelle');
            exit();
        }
    }
    
    /**
     * Active une année universitaire
     */
    public function activate($id) {
        if ($this->anneeModel->setActiveYear($id)) {
            $_SESSION['success'] = "L'année universitaire a été activée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'activation de l'année universitaire.";
        }
        
        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
        exit();
    }
    
    /**
     * Affiche le formulaire de modification d'une année universitaire
     */
    public function edit($id) {
        $annee = $this->anneeModel->getById($id);
        
        if (!$annee) {
            $_SESSION['error'] = "Année universitaire introuvable.";
            header('Location: ' . BASE_URL . 'admin/annees-universitaires');
            exit();
        }
        
        include __DIR__ . '/../views/admin/annees/edit.php';
    }
    
    /**
     * Met à jour une année universitaire existante
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'annee_debut' => $_POST['annee_debut'] ?? '',
                'annee_fin' => $_POST['annee_fin'] ?? '',
                'est_active' => isset($_POST['est_active']) ? 1 : 0
            ];
            
            // Validation des données
            $errors = $this->validateYearData($data);
            
            if (empty($errors)) {
                // Vérifier si l'année existe déjà (hors l'année courante)
                if ($this->anneeModel->yearExists($data['annee_debut'], $data['annee_fin'], $id)) {
                    $errors[] = "Cette année universitaire existe déjà.";
                } else {
                    $success = $this->anneeModel->update($data);
                    
                    if ($success && $data['est_active']) {
                        $this->anneeModel->setActiveYear($id);
                    }
                    
                    if ($success) {
                        $_SESSION['success'] = "L'année universitaire a été mise à jour avec succès.";
                        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
                        exit();
                    } else {
                        $errors[] = "Une erreur est survenue lors de la mise à jour de l'année universitaire.";
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/annees-universitaires/' . $id . '/modifier');
            exit();
        }
    }
    
    /**
     * Supprime une année universitaire
     */
    public function delete($id) {
        $success = $this->anneeModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "L'année universitaire a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer cette année universitaire car elle est liée à des classes existantes.";
        }
        
        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
        exit();
    }
    
    /**
     * Valide les données d'une année universitaire
     */
    private function validateYearData($data) {
        $errors = [];
        
        if (empty($data['annee_debut'])) {
            $errors[] = "L'année de début est obligatoire.";
        } elseif (!is_numeric($data['annee_debut']) || strlen($data['annee_debut']) != 4) {
            $errors[] = "L'année de début doit être une année valide (4 chiffres).";
        }
        
        if (empty($data['annee_fin'])) {
            $errors[] = "L'année de fin est obligatoire.";
        } elseif (!is_numeric($data['annee_fin']) || strlen($data['annee_fin']) != 4) {
            $errors[] = "L'année de fin doit être une année valide (4 chiffres).";
        }
        
        if (!empty($data['annee_debut']) && !empty($data['annee_fin'])) {
            if ($data['annee_fin'] != $data['annee_debut'] + 1) {
                $errors[] = "L'année de fin doit être égale à l'année de début + 1 (ex: 2023-2024).";
            }
        }
        
        return $errors;
    }
}
?>
