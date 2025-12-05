<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Semestre.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';

class SemestreController {
    private $db;
    private $semestreModel;
    private $anneeModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->semestreModel = new Semestre($this->db);
        $this->anneeModel = new AnneeUniversitaire($this->db);
    }
    
    /**
     * Affiche la liste des semestres
     */
    public function index() {
        // Vérifier si une année est spécifiée dans la requête
        $anneeId = $_GET['annee'] ?? null;
        
        if ($anneeId) {
            $semestres = $this->semestreModel->getByAnneeUniversitaire($anneeId);
            $annee = $this->anneeModel->getById($anneeId);
            $anneeLibelle = $annee ? "{$annee['annee_debut']}-{$annee['annee_fin']}" : '';
        } else {
            $semestres = $this->semestreModel->getAll();
            $anneeLibelle = '';
        }
        
        include __DIR__ . '/../views/admin/semestres/index.php';
    }
    
    /**
     * Affiche le formulaire de création d'un semestre
     */
    public function create() {
        // Récupérer l'ID de l'année si spécifié
        $anneeId = $_GET['annee'] ?? null;
        $annees = $this->anneeModel->getAll();
        
        if (empty($annees)) {
            $_SESSION['error'] = "Vous devez d'abord créer une année universitaire avant d'ajouter des semestres.";
            header('Location: ' . BASE_URL . 'admin/annees-universitaires/nouvelle');
            exit();
        }
        
        include __DIR__ . '/../views/admin/semestres/create.php';
    }
    
    /**
     * Enregistre un nouveau semestre
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero' => $_POST['numero'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? '',
                'annee_universitaire_id' => $_POST['annee_universitaire_id'] ?? null,
                'est_ouvert' => isset($_POST['est_ouvert']),
                'est_cloture' => isset($_POST['est_cloture'])
            ];
            
            // Validation des données
            $errors = $this->validateSemestreData($data);
            
            if (empty($errors)) {
                // Vérifier si le semestre existe déjà pour cette année
                if ($this->semestreModel->semestreExists($data['numero'], $data['annee_universitaire_id'])) {
                    $errors[] = "Un semestre avec ce numéro existe déjà pour cette année universitaire.";
                } else {
                    // Vérifier les conflits de dates
                    $dateDebut = new DateTime($data['date_debut']);
                    $dateFin = new DateTime($data['date_fin']);
                    
                    if ($dateFin <= $dateDebut) {
                        $errors[] = "La date de fin doit être postérieure à la date de début.";
                    } else {
                        // Si c'est le premier semestre, on l'active par défaut
                        $existingSemestres = $this->semestreModel->getByAnneeUniversitaire($data['annee_universitaire_id']);
                        if (empty($existingSemestres)) {
                            $data['est_ouvert'] = 1;
                        }
                        
                        $success = $this->semestreModel->create($data);
                        
                        if ($success && $data['est_ouvert']) {
                            $this->semestreModel->setActiveSemestre($success);
                        }
                        
                        if ($success) {
                            $_SESSION['success'] = "Le semestre a été créé avec succès.";
                            header('Location: ' . BASE_URL . 'admin/semestres?annee=' . $data['annee_universitaire_id']);
                            exit();
                        } else {
                            $errors[] = "Une erreur est survenue lors de la création du semestre.";
                        }
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/semestres/nouveau' . ($data['annee_universitaire_id'] ? '?annee=' . $data['annee_universitaire_id'] : ''));
            exit();
        }
    }
    
    /**
     * Affiche les détails d'un semestre
     */
    public function show($id) {
        $semestre = $this->semestreModel->getById($id);
        
        if (!$semestre) {
            $_SESSION['error'] = "Semestre introuvable.";
            header('Location: ' . BASE_URL . 'admin/semestres');
            exit();
        }
        
        // Récupérer l'année universitaire associée
        $annee = $this->anneeModel->getById($semestre['annee_universitaire_id']);
        
        include __DIR__ . '/../views/admin/semestres/show.php';
    }
    
    /**
     * Affiche le formulaire de modification d'un semestre
     */
    public function edit($id) {
        $semestre = $this->semestreModel->getById($id);
        
        if (!$semestre) {
            $_SESSION['error'] = "Semestre introuvable.";
            header('Location: ' . BASE_URL . 'admin/semestres');
            exit();
        }
        
        $annees = $this->anneeModel->getAll();
        
        include __DIR__ . '/../views/admin/semestres/edit.php';
    }
    
    /**
     * Met à jour un semestre existant
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $semestre = $this->semestreModel->getById($id);
            
            if (!$semestre) {
                $_SESSION['error'] = "Semestre introuvable.";
                header('Location: ' . BASE_URL . 'admin/semestres');
                exit();
            }
            
            $data = [
                'id' => $id,
                'numero' => $_POST['numero'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? '',
                'annee_universitaire_id' => $_POST['annee_universitaire_id'] ?? null,
                'est_ouvert' => isset($_POST['est_ouvert']),
                'est_cloture' => isset($_POST['est_cloture'])
            ];
            
            // Si le semestre est clôturé, on ne peut pas le modifier
            if ($semestre['est_cloture']) {
                $_SESSION['error'] = "Impossible de modifier un semestre clôturé.";
                header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
                exit();
            }
            
            // Validation des données
            $errors = $this->validateSemestreData($data);
            
            if (empty($errors)) {
                // Vérifier si le semestre existe déjà pour cette année (hors le semestre courant)
                if ($this->semestreModel->semestreExists($data['numero'], $data['annee_universitaire_id'], $id)) {
                    $errors[] = "Un semestre avec ce numéro existe déjà pour cette année universitaire.";
                } else {
                    // Vérifier les conflits de dates
                    $dateDebut = new DateTime($data['date_debut']);
                    $dateFin = new DateTime($data['date_fin']);
                    
                    if ($dateFin <= $dateDebut) {
                        $errors[] = "La date de fin doit être postérieure à la date de début.";
                    } else {
                        $success = $this->semestreModel->update($data);
                        
                        if ($success && $data['est_ouvert']) {
                            $this->semestreModel->setActiveSemestre($id);
                        }
                        
                        if ($success) {
                            $_SESSION['success'] = "Le semestre a été mis à jour avec succès.";
                            header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
                            exit();
                        } else {
                            $errors[] = "Une erreur est survenue lors de la mise à jour du semestre.";
                        }
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/semestres/' . $id . '/modifier');
            exit();
        }
    }
    
    /**
     * Active un semestre
     */
    public function activate($id) {
        $semestre = $this->semestreModel->getById($id);
        
        if (!$semestre) {
            $_SESSION['error'] = "Semestre introuvable.";
            header('Location: ' . BASE_URL . 'admin/semestres');
            exit();
        }
        
        // Si le semestre est clôturé, on ne peut pas l'activer
        if ($semestre['est_cloture']) {
            $_SESSION['error'] = "Impossible d'activer un semestre clôturé.";
            header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
            exit();
        }
        
        if ($this->semestreModel->setActiveSemestre($id)) {
            $_SESSION['success'] = "Le semestre a été activé avec succès.";
            try {
                if (!empty($semestre['annee_universitaire_id'])) {
                    $anneeId = (int)$semestre['annee_universitaire_id'];
                    $this->db->execute(
                        "UPDATE classes SET statut_listes = NULL WHERE annee_universitaire_id = :annee_id",
                        ['annee_id' => $anneeId]
                    );
                    $this->db->execute(
                        "DELETE FROM affectation_professeur WHERE annee_universitaire_id = :annee_id",
                        ['annee_id' => $anneeId]
                    );
                }
            } catch (Exception $e) {
                error_log('Erreur reinitialisation listes lors activation semestre: ' . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'activation du semestre.";
        }
        
        header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
        exit();
    }
    
    /**
     * Clôture un semestre
     */
    public function close($id) {
        $semestre = $this->semestreModel->getById($id);
        
        if (!$semestre) {
            $_SESSION['error'] = "Semestre introuvable.";
            header('Location: ' . BASE_URL . 'admin/semestres');
            exit();
        }
        
        // Vérifier si toutes les notes sont saisies et validées
        // (à implémenter selon les besoins spécifiques)
        
        if ($this->semestreModel->closeSemestre($id)) {
            $_SESSION['success'] = "Le semestre a été clôturé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la clôture du semestre.";
        }

        // Après clôture, retourner au tableau de bord pour éventuellement déclencher la clôture d'année
        $anneeId = isset($semestre['annee_universitaire_id']) ? (int)$semestre['annee_universitaire_id'] : null;
        if ($anneeId) {
            header('Location: ' . BASE_URL . 'admin/dashboard?annee_id=' . $anneeId);
        } else {
            header('Location: ' . BASE_URL . 'admin/dashboard');
        }
        exit();
    }
    
    /**
     * Supprime un semestre
     */
    public function delete($id) {
        $semestre = $this->semestreModel->getById($id);
        
        if (!$semestre) {
            $_SESSION['error'] = "Semestre introuvable.";
            header('Location: ' . BASE_URL . 'admin/semestres');
            exit();
        }
        
        // Si le semestre est clôturé, on ne peut pas le supprimer
        if ($semestre['est_cloture']) {
            $_SESSION['error'] = "Impossible de supprimer un semestre clôturé.";
            header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
            exit();
        }
        
        $success = $this->semestreModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "Le semestre a été supprimé avec succès.";
            header('Location: ' . BASE_URL . 'admin/semestres');
        } else {
            $_SESSION['error'] = "Impossible de supprimer ce semestre car il est lié à des notes existantes.";
            header('Location: ' . BASE_URL . 'admin/semestres/' . $id);
        }
        
        exit();
    }
    
    /**
     * Valide les données d'un semestre
     */
    private function validateSemestreData($data) {
        $errors = [];
        
        if (empty($data['numero'])) {
            $errors[] = "Le numéro du semestre est obligatoire.";
        } elseif (!is_numeric($data['numero']) || $data['numero'] < 1 || $data['numero'] > 2) {
            $errors[] = "Le numéro du semestre doit être 1 ou 2.";
        }
        
        if (empty($data['date_debut'])) {
            $errors[] = "La date de début est obligatoire.";
        } elseif (!strtotime($data['date_debut'])) {
            $errors[] = "La date de début n'est pas valide.";
        }
        
        if (empty($data['date_fin'])) {
            $errors[] = "La date de fin est obligatoire.";
        } elseif (!strtotime($data['date_fin'])) {
            $errors[] = "La date de fin n'est pas valide.";
        }
        
        if (empty($data['annee_universitaire_id'])) {
            $errors[] = "L'année universitaire est obligatoire.";
        }
        
        return $errors;
    }
}
