<?php
require_once __DIR__ . '/../classes/Professeur.php';

class ProfesseurController {
    private $professeur;
    
    public function __construct() {
        $this->professeur = new Professeur();
        $this->checkAccess();
    }
    
    /**
     * Vérifie que l'utilisateur est un professeur
     */
    private function checkAccess() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 3) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
        
        // Charger les informations du professeur
        $this->professeur = $this->professeur->getByUserId($_SESSION['user_id']);
        
        if (!$this->professeur) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Profil professeur non trouvé';
            exit();
        }
    }
    
    /**
     * Affiche le tableau de bord du professeur
     */
    public function dashboard() {
        $matieres = $this->professeur->getMatieresEnseignees($this->professeur['id']);
        
        return [
            'professeur' => $this->professeur,
            'matieres' => $matieres
        ];
    }
    
    /**
     * Récupère les classes pour une matière donnée
     */
    public function getClassesPourMatiere($matiereId) {
        return $this->professeur->getClassesPourMatiere($this->professeur['id'], $matiereId);
    }
    
    /**
     * Récupère les étudiants d'une classe pour une matière donnée
     */
    public function getEtudiantsPourNote($classeId, $matiereId) {
        return $this->professeur->getEtudiantsPourNote($classeId, $matiereId);
    }
    
    /**
     * Enregistre les notes des étudiants
     */
    public function enregistrerNotes($notesData) {
        return $this->professeur->enregistrerNotes($this->professeur['id'], $notesData);
    }
    
    /**
     * Récupère l'historique des notes saisies
     */
    public function getHistoriqueNotes($matiereId = null, $classeId = null) {
        $params = ['professeur_id' => $this->professeur['id']];
        $where = [];
        
        if ($matiereId) {
            $where[] = 'n.matiere_id = :matiere_id';
            $params['matiere_id'] = $matiereId;
        }
        
        if ($classeId) {
            $where[] = 'e.classe_id = :classe_id';
            $params['classe_id'] = $classeId;
        }
        
        $whereClause = !empty($where) ? 'AND ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT n.*, m.nom as matiere_nom, c.intitule as classe_nom,
                       CONCAT(e.nom, ' ', e.prenom) as etudiant_nom
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                JOIN etudiants etu ON n.etudiant_id = etu.id
                JOIN inscriptions i ON etu.id = i.etudiant_id
                JOIN classes c ON i.classe_id = c.id
                WHERE n.professeur_id = :professeur_id
                $whereClause
                ORDER BY n.date_modification DESC, n.date_creation DESC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Récupère les statistiques des notes pour une matière
     */
    public function getStatistiquesNotes($matiereId, $classeId = null) {
        $params = [
            'professeur_id' => $this->professeur['id'],
            'matiere_id' => $matiereId
        ];
        
        $whereClause = '';
        if ($classeId) {
            $whereClause = 'AND i.classe_id = :classe_id';
            $params['classe_id'] = $classeId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_etudiants,
                    AVG(n.note) as moyenne,
                    MIN(n.note) as note_min,
                    MAX(n.note) as note_max,
                    SUM(CASE WHEN n.note >= 10 THEN 1 ELSE 0 END) as admis,
                    SUM(CASE WHEN n.note < 10 THEN 1 ELSE 0 END) as ajournes
                FROM notes n
                JOIN etudiants e ON n.etudiant_id = e.id
                JOIN inscriptions i ON e.id = i.etudiant_id
                WHERE n.matiere_id = :matiere_id
                AND n.professeur_id = :professeur_id
                $whereClause";
        
        $db = Database::getInstance();
        return $db->fetch($sql, $params);
    }
    
    /**
     * Exporte les notes au format CSV
     */
    public function exporterNotesCSV($matiereId, $classeId) {
        $etudiants = $this->getEtudiantsPourNote($classeId, $matiereId);
        $matiere = $this->professeur->getMatiereById($matiereId);
        $classe = $this->professeur->getClasseById($classeId);
        
        // En-têtes HTTP pour forcer le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=notes_' . $matiere['code'] . '_' . $classe['code'] . '.csv');
        
        // Création du fichier de sortie
        $output = fopen('php://output', 'w');
        
        // En-têtes du CSV
        fputcsv($output, [
            'Matricule', 'Nom', 'Prénom', 'Note', 'Appréciation', 'Statut'
        ]);
        
        // Données
        foreach ($etudiants as $etudiant) {
            fputcsv($output, [
                $etudiant['matricule'],
                $etudiant['nom'],
                $etudiant['prenom'],
                $etudiant['note'] ?? '',
                $etudiant['appreciation'] ?? '',
                $etudiant['statut'] ?? 'Non noté'
            ]);
        }
        
        fclose($output);
        exit();
    }
}
