<?php
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Etudiant.php';
require_once __DIR__ . '/../classes/Professeur.php';

class AdminController {
    private $admin;
    private $etudiant;
    private $professeur;
    
    public function __construct() {
        $this->admin = new Admin();
        $this->etudiant = new Etudiant();
        $this->professeur = new Professeur();
        
        // Vérifier les autorisations
        $this->checkAdminAccess();
    }
    
    /**
     * Vérifie que l'utilisateur est un administrateur
     */
    private function checkAdminAccess() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
    }
    
    /**
     * Gère le tableau de bord administrateur
     */
    public function dashboard() {
        $stats = $this->admin->getStatistiques();
        $rapport = $this->admin->genererRapport('mensuel');
        
        return [
            'stats' => $stats,
            'rapport' => $rapport
        ];
    }
    
    /**
     * Gère la création d'une année universitaire
     */
    public function creerAnneeUniversitaire($data) {
        return $this->admin->creerAnneeUniversitaire($data);
    }
    
    /**
     * Gère l'ouverture/fermeture d'un semestre
     */
    public function gererSemestre($semestreId, $estOuvert) {
        return $this->admin->gererSemestre($semestreId, $estOuvert);
    }
    
    /**
     * Gère la création d'une classe
     */
    public function creerClasse($data) {
        return $this->admin->creerClasse($data);
    }
    
    /**
     * Gère la désignation d'un chef de classe
     */
    public function designerChefClasse($etudiantId, $classeId) {
        return $this->admin->designerChefClasse($etudiantId, $classeId);
    }
    
    /**
     * Gère la validation des listes d'étudiants et de professeurs
     */
    public function validerListesClasse($classeId) {
        return $this->admin->validerListesClasse($classeId);
    }
    
    /**
     * Gère la publication des résultats d'un semestre
     */
    public function publierResultats($semestreId) {
        return $this->admin->publierResultats($semestreId);
    }
    
    /**
     * Gère la modification d'une note après publication
     */
    public function modifierNoteApresPublication($noteId, $nouvelleNote, $motif) {
        return $this->admin->modifierNoteApresPublication($noteId, $nouvelleNote, $motif);
    }
    
    /**
     * Récupère la liste des étudiants avec pagination
     */
    public function listerEtudiants($page = 1, $perPage = 20) {
        return $this->etudiant->getEtudiantsPagines($page, $perPage);
    }
    
    /**
     * Récupère la liste des professeurs avec pagination
     */
    public function listerProfesseurs($page = 1, $perPage = 20) {
        return $this->professeur->getProfesseursPagines($page, $perPage);
    }
    
    /**
     * Active ou désactive un utilisateur
     */
    public function toggleUserStatus($userId, $isActive) {
        $user = new User();
        return $user->update($userId, ['is_active' => $isActive ? 1 : 0]);
    }
    
    /**
     * Récupère les statistiques générales
     */
    public function getStatistiques() {
        return $this->admin->getStatistiques();
    }
    
    /**
     * Génère un rapport
     */
    public function genererRapport($type, $options = []) {
        return $this->admin->genererRapport($type, $options);
    }
}
