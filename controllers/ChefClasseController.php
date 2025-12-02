<?php
require_once __DIR__ . '/../classes/ChefClasse.php';

class ChefClasseController {
    private $chefClasse;
    
    public function __construct() {
        $this->chefClasse = new ChefClasse($_SESSION['user_id']);
        $this->checkAccess();
    }
    
    /**
     * Vérifie que l'utilisateur est un chef de classe
     */
    private function checkAccess() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
        
        if (!$this->chefClasse->estChefClasse($_SESSION['user_id'])) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Vous n\'êtes pas chef de classe';
            exit();
        }
    }
    
    /**
     * Affiche le tableau de bord du chef de classe
     */
    public function dashboard() {
        $classe = $this->chefClasse->getClasse();
        $etudiants = $this->chefClasse->getListeEtudiants();
        $professeurs = $this->chefClasse->getListeProfesseurs();
        
        // Inclure la vue
        include __DIR__ . '/../views/chef_classe/dashboard.php';
    }

    public function listeEtudiants() {
        $classe = $this->chefClasse->getClasse();
        $etudiants = $this->chefClasse->getListeEtudiants();
        include __DIR__ . '/../views/chef_classe/etudiants.php';
    }

    public function listeProfesseurs() {
        $classe = $this->chefClasse->getClasse();
        $professeurs = $this->chefClasse->getListeProfesseurs();
        include __DIR__ . '/../views/chef_classe/professeurs.php';
    }

    public function formAjouterEtudiant() {
        $classe = $this->chefClasse->getClasse();
        include __DIR__ . '/../views/chef_classe/etudiants_ajouter.php';
    }

    public function formAjouterProfesseur() {
        $classe = $this->chefClasse->getClasse();
        include __DIR__ . '/../views/chef_classe/professeurs_ajouter.php';
    }
    
    /**
     * Gère l'ajout d'un étudiant à la classe
     */
    public function ajouterEtudiant() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $etudiantData = [
                'matricule' => $_POST['matricule'] ?? '',
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'date_naissance' => $_POST['date_naissance'] ?? '',
                'lieu_naissance' => $_POST['lieu_naissance'] ?? ''
            ];
            
            $resultat = $this->chefClasse->ajouterEtudiant($etudiantData);
            
            if ($resultat) {
                $_SESSION['message'] = 'Étudiant ajouté avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout de l\'étudiant';
            }
            
            header('Location: ' . BASE_URL . 'chef-classe/dashboard');
            exit();
        }
    }
    
    /**
     * Gère la suppression d'un étudiant de la classe
     */
    public function supprimerEtudiant() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $etudiantId = $_POST['etudiant_id'] ?? '';
            
            $resultat = $this->chefClasse->supprimerEtudiant($etudiantId);
            
            if ($resultat) {
                $_SESSION['message'] = 'Étudiant supprimé avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de la suppression de l\'étudiant';
            }
            
            header('Location: ' . BASE_URL . 'chef-classe/dashboard');
            exit();
        }
    }
    
    /**
     * Gère l'ajout d'un professeur à la classe
     */
    public function ajouterProfesseur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $professeurData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'matiere_id' => $_POST['matiere_id'] ?? ''
            ];
            
            $resultat = $this->chefClasse->ajouterNouveauProfesseur($professeurData);
            
            if ($resultat) {
                $_SESSION['message'] = 'Professeur ajouté avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout du professeur';
            }
            
            header('Location: ' . BASE_URL . 'chef-classe/dashboard');
            exit();
        }
    }
    
    /**
     * Gère la suppression d'un professeur de la classe
     */
    public function supprimerProfesseur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $professeurId = $_POST['professeur_id'] ?? '';
            $matiereId = $_POST['matiere_id'] ?? '';
            
            $resultat = $this->chefClasse->supprimerProfesseur($professeurId, $matiereId);
            
            if ($resultat) {
                $_SESSION['message'] = 'Professeur supprimé avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de la suppression du professeur';
            }
            
            header('Location: ' . BASE_URL . 'chef-classe/dashboard');
            exit();
        }
    }
    
    /**
     * Soumet les listes pour validation
     */
    public function soumettreListes() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultat = $this->chefClasse->soumettreListes();
            
            if ($resultat) {
                $_SESSION['message'] = 'Listes soumises avec succès pour validation';
            } else {
                if (empty($_SESSION['error'])) {
                    $_SESSION['error'] = 'Erreur lors de la soumission des listes';
                }
            }
            
            header('Location: ' . BASE_URL . 'chef-classe/dashboard');
            exit();
        }
    }
    
    /**
     * Récupère la liste des étudiants avec des informations supplémentaires
     */
    public function getListeEtudiantsComplet() {
        $classe = $this->chefClasse->getClasse();
        
        $sql = "SELECT e.*, u.email, u.is_active,
                       (SELECT COUNT(*) FROM absences a WHERE a.etudiant_id = e.id) as nb_absences,
                       (SELECT AVG(n.note) FROM notes n 
                        JOIN inscriptions i ON n.etudiant_id = i.etudiant_id 
                        WHERE i.etudiant_id = e.id AND i.classe_id = :classe_id) as moyenne_generale
                FROM etudiants e
                JOIN users u ON e.user_id = u.id
                JOIN inscriptions i ON e.id = i.etudiant_id
                WHERE i.classe_id = :classe_id
                ORDER BY e.nom, e.prenom";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, ['classe_id' => $classe['id']]);
    }
    
    /**
     * Récupère les statistiques de la classe
     */
    public function getStatistiquesClasse() {
        $classe = $this->chefClasse->getClasse();
        
        $stats = [];
        
        // Nombre total d'étudiants
        $stats['total_etudiants'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM inscriptions WHERE classe_id = :classe_id",
            ['classe_id' => $classe['id']]
        );
        
        // Nombre d'étudiants par sexe
        $stats['par_sexe'] = $this->db->fetchAll(
            "SELECT sexe, COUNT(*) as nombre 
             FROM etudiants e
             JOIN inscriptions i ON e.id = i.etudiant_id
             WHERE i.classe_id = :classe_id
             GROUP BY sexe",
            ['classe_id' => $classe['id']]
        );
        
        // Taux de réussite par matière
        $stats['reussite_par_matiere'] = $this->db->fetchAll(
            "SELECT m.nom, 
                    COUNT(*) as total_etudiants,
                    SUM(CASE WHEN n.note >= 10 THEN 1 ELSE 0 END) as admis,
                    ROUND((SUM(CASE WHEN n.note >= 10 THEN 1 ELSE 0) / COUNT(*)) * 100, 2) as taux_reussite
             FROM matieres m
             JOIN notes n ON m.id = n.matiere_id
             JOIN inscriptions i ON n.etudiant_id = i.etudiant_id
             WHERE i.classe_id = :classe_id
             GROUP BY m.id, m.nom",
            ['classe_id' => $classe['id']]
        );
        
        // Évolution des résultats
        $stats['evolution_resultats'] = $this->db->fetchAll(
            "SELECT s.numero as semestre,
                    AVG(n.note) as moyenne_classe
             FROM notes n
             JOIN matieres m ON n.matiere_id = m.id
             JOIN semestres s ON m.semestre_id = s.id
             JOIN inscriptions i ON n.etudiant_id = i.etudiant_id
             WHERE i.classe_id = :classe_id
             GROUP BY s.id, s.numero
             ORDER BY s.numero",
            ['classe_id' => $classe['id']]
        );
        
        return $stats;
    }
    
    /**
     * Génère un rapport de classe
     */
    public function genererRapportClasse() {
        $classe = $this->chefClasse->getClasse();
        $etudiants = $this->getListeEtudiantsComplet();
        $stats = $this->getStatistiquesClasse();
        
        // Préparer les données pour l'export
        $rapport = [
            'classe' => $classe,
            'date_generation' => date('Y-m-d H:i:s'),
            'effectif' => count($etudiants),
            'statistiques' => $stats,
            'etudiants' => []
        ];
        
        // Ajouter les données de chaque étudiant
        foreach ($etudiants as $etudiant) {
            $rapport['etudiants'][] = [
                'matricule' => $etudiant['matricule'],
                'nom' => $etudiant['nom'],
                'prenom' => $etudiant['prenom'],
                'moyenne' => $etudiant['moyenne_generale'],
                'nb_absences' => $etudiant['nb_absences'],
                'statut' => $etudiant['moyenne_generale'] >= 10 ? 'Admis' : 'Ajourné'
            ];
        }
        
        return $rapport;
    }
    
    /**
     * Exporte le rapport de classe au format CSV
     */
    public function exporterRapportCSV() {
        $rapport = $this->genererRapportClasse();
        
        // En-têtes HTTP pour forcer le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rapport_classe_' . $rapport['classe']['code'] . '.csv');
        
        // Création du fichier de sortie
        $output = fopen('php://output', 'w');
        
        // En-têtes du CSV
        fputcsv($output, ['Rapport de classe - ' . $rapport['classe']['intitule']]);
        fputcsv($output, ['Généré le', $rapport['date_generation']]);
        fputcsv($output, ['Effectif', $rapport['effectif']]);
        fputcsv($output, []); // Ligne vide
        
        // Statistiques
        fputcsv($output, ['Statistiques de la classe']);
        fputcsv($output, ['Matière', 'Taux de réussite']);
        
        foreach ($rapport['statistiques']['reussite_par_matiere'] as $matiere) {
            fputcsv($output, [
                $matiere['nom'],
                $matiere['taux_reussite'] . '%'
            ]);
        }
        
        fputcsv($output, []); // Ligne vide
        
        // Liste des étudiants
        fputcsv($output, ['Liste des étudiants']);
        fputcsv($output, ['Matricule', 'Nom', 'Prénom', 'Moyenne', 'Absences', 'Statut']);
        
        foreach ($rapport['etudiants'] as $etudiant) {
            fputcsv($output, [
                $etudiant['matricule'],
                $etudiant['nom'],
                $etudiant['prenom'],
                number_format($etudiant['moyenne'], 2, ',', ' '),
                $etudiant['nb_absences'],
                $etudiant['statut']
            ]);
        }
        
        fclose($output);
        exit();
    }
}
