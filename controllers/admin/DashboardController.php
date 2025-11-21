<?php
class DashboardController {
    private $db;
    private $anneeModel;
    private $classeModel;
    private $etudiantModel;
    private $professeurModel;
    private $matiereModel;
    
    public function __construct() {
        // Initialiser la connexion à la base de données
        $this->db = Database::getInstance();
        
        // Charger les modèles nécessaires
        require_once __DIR__ . '/../../classes/AnneeUniversitaire.php';
        require_once __DIR__ . '/../../classes/Classe.php';
        require_once __DIR__ . '/../../classes/Etudiant.php';
        require_once __DIR__ . '/../../classes/Professeur.php';
        require_once __DIR__ . '/../../classes/Matiere.php';
        
        $this->anneeModel = new AnneeUniversitaire($this->db);
        $this->classeModel = new Classe($this->db);
        $this->etudiantModel = new Etudiant($this->db);
        $this->professeurModel = new Professeur($this->db);
        $this->matiereModel = new Matiere($this->db);
    }
    
    /**
     * Affiche le tableau de bord de l'administrateur
     */
    public function index() {
        // Vérifier les autorisations
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Récupérer les statistiques
        $stats = [
            'annees' => $this->anneeModel->count(),
            'classes' => $this->classeModel->count(),
            'etudiants' => $this->etudiantModel->count(),
            'professeurs' => $this->professeurModel->count(),
            'matieres' => $this->matiereModel->count()
        ];
        
        // Récupérer l'année universitaire active
        $anneeActive = $this->anneeModel->getActiveYear();
        
        // Récupérer les dernières classes créées
        $dernieresClasses = $this->classeModel->getLatest(5);
        
        // Inclure la vue du tableau de bord
        include __DIR__ . '/../../views/admin/dashboard.php';
    }
}
